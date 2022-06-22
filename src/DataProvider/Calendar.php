<?php

/*
 * © Copyright 2022 · Willem Vervuurt, Studio Delfuego
 * 
 * You can modify, use and distribute this package under one of two licenses:
 * 1. GNU AGPLv3
 * 2. A perpetual, non-revocable and 100% free (as in beer) do-what-you-want 
 *    license that allows both non-commercial and commercial use, under conditions.
 *    See LICENSE.md for details.
 * 
 *    (it boils down to: do what you want as long as you're building and/or
 *     using calendar views, but don't embed this package or a modified version
 *     of it in free or paid-for software libraries and packages aimed at developers).
 */
 
namespace Wdelfuego\NovaCalendar\DataProvider;

use DateTimeInterface;
use Laravel\Nova\Nova;
use Illuminate\Http\Request;

use Illuminate\Support\Carbon;
use Wdelfuego\NovaCalendar\Event;
use Wdelfuego\NovaCalendar\CalendarDay;

use Wdelfuego\NovaCalendar\NovaCalendar;
use Laravel\Nova\Resource as NovaResource;
use Wdelfuego\Nova\DateTime\Filters\AfterOrOnDate;

use Wdelfuego\Nova\DateTime\Filters\BeforeOrOnDate;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Wdelfuego\NovaCalendar\Interface\CalendarDataProviderInterface;

abstract class Calendar implements CalendarDataProviderInterface
{
    const N_CALENDAR_WEEKS = 6;

    protected $firstDayOfWeek;
    protected $year;
    protected $month;
    
    protected $request = null;

    private $startOfCalendar = null;
    private $endOfCalendar = null;
    private $startOfPeriod = null;
    private $endOfPeriod = null;
    private $periodDuration = null;

    private $allEvents = null;
    
    public function __construct()
    {
        $this->firstDayOfWeek = NovaCalendar::MONDAY;
        $this->year = now()->year;
        $this->month = now()->month;
        $this->updateViewRanges();
        $this->initialize();
    }
    
    abstract public function novaResources();

    public function initialize(): void
    {
        
    }
    
    public function startOfCalendar() : Carbon
    {
        return $this->startOfCalendar;
    }
    
    public function endOfCalendar() : Carbon
    {
        return $this->endOfCalendar;
    }
    
    // Deprecated as of 1.3.1, here for backwards compatibility
    public function firstDayOfCalendar() : Carbon
    {
        return $this->startOfCalendar();
    }
            
    // Deprecated as of 1.3.1, here for backwards compatibility
    public function lastDayOfCalendar() : Carbon
    {
        return $this->startOfCalendar();
    }

    public function setYearAndMonth(int $year, int $month) : self
    {
        $this->year = $year;
        $this->month = $month;
        $this->periodDuration = Carbon::createFromDate($year, $month)->daysInMonth;
        $this->updateViewRanges();
        return $this;
    }

    public function startWeekOn(int $dayOfWeekIso) : self
    {
        $this->firstDayOfWeek = min(NovaCalendar::SUNDAY, max($dayOfWeekIso, NovaCalendar::MONDAY));
        $this->updateViewRanges();
        return $this;
    }

    public function setRequest(Request $request) : self
    {
        $this->request = $request;
        return $this;
    }
    
    public function title() : string
    {
        return ucfirst($this->startOfPeriod->translatedFormat('F \'y'));
    }

    public function daysOfTheWeek() : array
    {
        $out = [];
        $currentDay = new Carbon(Carbon::getDays()[$this->firstDayOfWeek % 7]);
        for($i = 0; $i < 7; $i++)
        {
            $out[] = $currentDay->dayName;
            $currentDay = $currentDay->addDay();
        }
        return $out;
    }

    public function calendarWeeks() : array
    {
        $out = [];
        $dateCursor = $this->startOfCalendar();

        for($i = 0; $i < self::N_CALENDAR_WEEKS; $i++)
        {
            $week = [];
            for($j = 0; $j < 7; $j++)
            {
                $calendarDay = CalendarDay::forDateInYearAndMonth($dateCursor, $this->year, $this->month, $this->firstDayOfWeek);
                $week[] = $calendarDay->withEvents($this->eventDataForDate($dateCursor))->toArray();
                
                $dateCursor = $dateCursor->addDay();
            }
            $out[] = $week;
        }
        
        return $out;
    }
    
    public function eventStyles() : array
    {
        return [];
    }
    
    protected function customizeEvent(Event $event) : Event
    {
        return $event;
    }
    
    protected function nonNovaEvents() : array
    {
        return [];
    }
    
    protected function urlForResource(NovaResource $resource)
    {
        return '/resources/' .$resource::uriKey() .'/' .$resource->id;
    }

    protected function exclude(NovaResource $resource) : bool
    {
        return false;
    }
    
    private function eventDataForDate(Carbon $date) : array
    {
        $date->setTime(0,0,0);
        $isFirstDayColumn = ($date->dayOfWeekIso == $this->firstDayOfWeek);
        
        // Get all events that start today, and if the date is the first day of the week
        // also get all multiday events that started before today and end on or after it
        // ('running multiday events')
        $events = array_filter($this->allEvents(), function($e) use ($date, $isFirstDayColumn) {
            return $e->start()->isSameDay($date)
                    ||
                    ($isFirstDayColumn
                        && $e->end() 
                        && $e->start()->isBefore($date) 
                        && $e->end()->isAfter($date));
        });

        // Sort events (as a heuristic, since CSS won't always match event order 
        // between different week rows perfectly due to 'column dense')
        usort($events, function($a, $b) use ($date, $isFirstDayColumn) { 

            $aDays = min(7,$a->spansDaysFrom($date));
            $bDays = min(7,$b->spansDaysFrom($date));

            // Longer events first
            if($aDays != $bDays) { return $bDays - $aDays; }

            // If we're in the first day column and both events span 7 days,
            // let running multi-day events precede events that start today
            if($isFirstDayColumn && $aDays == 7 && $bDays == 7)
            {
                if(!$a->startsEvent($date)) { return -1 ;}
                if(!$b->startsEvent($date)) { return 1 ;}
                return 0;
            }

            // Events have the same length and don't span 7 full days
            // Let the one that starts earlier come first.
            return $b->start()->diffInMinutes($a->start(), false); 
        });
        
        // Finally return the resultant event array, but convert each event to an array
        // that the front-end can use to render the calendar
        return array_map(fn($e): array => $e->toArray($date, $this->startOfPeriod, $this->endOfPeriod, $this->firstDayOfWeek), $events);
    }
    
    private function resourceToEvent(NovaResource $resource, string $dateAttributeStart, string $dateAttributeEnd = null) : Event
    {
        $out = Event::fromResource($resource, $dateAttributeStart, $dateAttributeEnd);
        $out->url($this->urlForResource($resource));
        return $out;
    }
    
    private function allEvents() : array
    {
        if(is_null($this->allEvents))
        {
            $this->allEvents = [];
        
            foreach($this->novaResources() as $novaResourceClass => $toEventSpec)
            {
                if(!is_subclass_of($novaResourceClass, NovaResource::class))
                {
                    throw new \Exception("Only Nova Resources can be automatically fetched for event generation ($novaResourceClass is not a Nova Resource)");
                }
                
                $eloquentModelClass = $novaResourceClass::$model;
                if(!is_subclass_of($eloquentModelClass, EloquentModel::class))
                {
                    throw new \Exception("'$eloquentModelClass' is not an Eloquent model");
                }
            
                if(is_string($toEventSpec) || (is_array($toEventSpec) && count($toEventSpec) == 1 && is_string($toEventSpec[0])))
                {
                    // If a single string is supplied as the toEventSpec, it is assumed to 
                    // be the name of a datetime attribute on the underlying Eloquent model
                    // that will be used as the starting date/time for a single-day event
                    
                    // Support single attributes supplied in an array, too, since it's bound to happen
                    $toEventSpec = is_array($toEventSpec) ? $toEventSpec[0] : $toEventSpec;
                    
                    // Since these are single-day events by definition, we only query for the models 
                    // that have the date attribute within the current calendar range
                    $afterFilter = new AfterOrOnDate('', $toEventSpec);
                    $beforeFilter = new BeforeOrOnDate('', $toEventSpec);
                    $models = $eloquentModelClass::orderBy($toEventSpec);
                    $models = $afterFilter->modulateQuery($models, $this->startOfCalendar);
                    $models = $beforeFilter->modulateQuery($models, $this->endOfCalendar);

                    foreach($models->cursor() as $model)
                    {
                        $novaResource = new $novaResourceClass($model);
                        if($novaResource->authorizedToView($this->request) && !$this->exclude($novaResource))
                        {
                            $this->allEvents[] = $this->resourceToEvent($novaResource, $toEventSpec);
                        }
                    }
                }
                else if(is_array($toEventSpec) && count($toEventSpec) == 2 && is_string($toEventSpec[0]) && is_string($toEventSpec[1]))
                {
                    // If an array containing two strings is supplied as the toEventSpec, they are assumed to 
                    // be the name of two datetime attributes on the underlying Eloquent model
                    // that will be used as the start and end datetime for a event
                    // that can be either single or multi-day (depending on the values of each model instance)

                    // Since multi-day events could now be included, we have to query for all models 
                    // that end after or on the first day of the calendar range
                    // and start before or on the last day of the calendar range
                    $afterFilter = new AfterOrOnDate('', $toEventSpec[1]);
                    $beforeFilter = new BeforeOrOnDate('', $toEventSpec[0]);
                    $models = $eloquentModelClass::orderBy($toEventSpec[0]);
                    $models = $afterFilter->modulateQuery($models, $this->startOfCalendar);
                    $models = $beforeFilter->modulateQuery($models, $this->endOfCalendar);

                    foreach($models->cursor() as $model)
                    {
                        $novaResource = new $novaResourceClass($model);
                        if($novaResource->authorizedToView($this->request) && !$this->exclude($novaResource))
                        {
                            $this->allEvents[] = $this->resourceToEvent($novaResource, $toEventSpec[0], $toEventSpec[1]);
                        }

                    }
                }
                else
                {
                    throw new \Exception("Invalid toEventSpec supplied for Nova Resource $novaResourceClass");
                }
            }
            
            $this->allEvents = array_merge($this->allEvents, $this->nonNovaEvents());
            
            return array_map(fn($e) : Event => $this->customizeEvent($e), $this->allEvents);
        }
        
        return $this->allEvents;
    }
    
    protected function updateViewRanges() : void
    {
        // Calculate period range
        $this->startOfPeriod = Carbon::createFromFormat('Y-m-d H:i:s', $this->year.'-'.$this->month.'-1 00:00:00');
        $this->endOfPeriod = $this->startOfPeriod->copy()->addDays($this->periodDuration)->subSeconds(1);

        // Calculate calendar range for ...
        if ($this->periodDuration > 7) {   // ... month view
            $nDaysToSub = ($this->startOfPeriod->dayOfWeekIso - ($this->firstDayOfWeek % 7)) % 7;
            while ($nDaysToSub < 0) {
                $nDaysToSub += 7;
            }
            $this->startOfCalendar = $this->startOfPeriod->copy()->subDays($nDaysToSub);
            $this->endOfCalendar = $this->startOfCalendar->copy()->addDays(7 * self::N_CALENDAR_WEEKS);
        } else {                           // ... remaining views
            $this->startOfCalendar = $this->startOfPeriod->copy();
            $this->endOfCalendar = $this->endOfPeriod->copy();
        }
    }

}