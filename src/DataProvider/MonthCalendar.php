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
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource as NovaResource;

use Wdelfuego\Nova\DateTime\Filters\BeforeOrOnDate;
use Wdelfuego\Nova\DateTime\Filters\AfterOrOnDate;
use Wdelfuego\NovaCalendar\Interface\MonthDataProviderInterface;

use Wdelfuego\NovaCalendar\NovaCalendar;
use Wdelfuego\NovaCalendar\CalendarDay;
use Wdelfuego\NovaCalendar\Event;

abstract class MonthCalendar implements MonthDataProviderInterface
{
    const N_CALENDAR_WEEKS = 6;
    
    protected $firstDayOfWeek;
    protected $year;
    protected $month;
    
    private $allEvents = null;
    
    public function __construct(int $year = null, int $month = null)
    {
        $this->firstDayOfWeek = NovaCalendar::MONDAY;
        $this->year = $year ?? now()->year;
        $this->month = $month ?? now()->month;
        $this->initialize();
    }
    
    abstract public function novaResources();

    public function initialize(): void
    {
        
    }

    public function setYearAndMonth(int $year, int $month): void
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function startWeekOn(int $dayOfWeekIso)
    {
        $this->firstDayOfWeek = min(NovaCalendar::SUNDAY, max($dayOfWeekIso, NovaCalendar::MONDAY));
    }

    public function startWeekOnSunday()
    {
        $this->startWeekOn(NovaCalendar::SUNDAY);
    }

    public function title() : string
    {
        return ucfirst($this->firstDayOfMonth()->translatedFormat('F \'y'));
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
        $dateCursor = $this->firstDayOfCalendar();

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
    
    private function firstDayOfMonth() : Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $this->year.'-'.$this->month.'-1');
    }

    protected function firstDayOfCalendar(): Carbon
    {
        $firstOfMonth = $this->firstDayOfMonth();
        return $firstOfMonth->subDays($firstOfMonth->dayOfWeekIso - $this->firstDayOfWeek);
    }
    
    protected function lastDayOfCalendar(): Carbon
    {
        return $this->firstDayOfCalendar()->addDays(7 * self::N_CALENDAR_WEEKS);
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

        // Sort events as a heuristic (CSS doesn't always match event order perfectly due to 'column dense')
        usort($events, function($a, $b) use ($date, $isFirstDayColumn) { 

            $aDays = min(7,$a->spansDaysFrom($date));
            $bDays = min(7,$b->spansDaysFrom($date));

            // Longer events first
            if($aDays != $bDays)
            {
                return $bDays - $aDays;
            }

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
        return array_map(fn($e): array => $e->toArray($date, $this->firstDayOfWeek), $events);
    }
    
    private function resourceToEvent(NovaResource $resource, string $dateAttributeStart, string $dateAttributeEnd = null) : Event
    {
        $out = Event::fromResource($resource, $dateAttributeStart, $dateAttributeEnd);
        $out->url($this->urlForResource($resource));
        return $out;
    }
    
    protected function eloquentClassHasDateCastableAttribute(string $class, string $attribute)
    {
        $testObj = new $class;
        
        return $testObj instanceof EloquentModel 
            && (in_array($attribute, $testObj->getDates(), true)
                || 
                $testObj->hasCast($attribute, ['date', 'datetime', 'immutable_date', 'immutable_datetime']));
    }
    
    private function allEvents() : array
    {
        if(is_null($this->allEvents))
        {
            $this->allEvents = [];
            $firstDayOfCalendar = $this->firstDayOfCalendar();
            $lastDayOfCalendar = $this->lastDayOfCalendar();
        
            foreach($this->novaResources() as $novaResourceClass => $toEventSpec)
            {
                if(!is_subclass_of($novaResourceClass, NovaResource::class))
                {
                    throw new \Exception("Only Nova Resources can be automatically fetched for event generation ($novaResourceClass is not a Nova Resource)");
                }
            
                if(is_string($toEventSpec) || (is_array($toEventSpec) && count($toEventSpec) == 1 && is_string($toEventSpec[0])))
                {
                    // Support single attributes supplied in an array, too
                    $toEventSpec = is_array($toEventSpec) ? $toEventSpec[0] : $toEventSpec;
                    
                    // If a single string is supplied as the toEventSpec, it is assumed to 
                    // be the name of a datetime attribute on the underlying Eloquent model
                    // that will be used as the starting date/time for a single-day event
                    $eloquentModelClass = $novaResourceClass::$model;
                    if(!is_subclass_of($eloquentModelClass, EloquentModel::class))
                    {
                        throw new \Exception("'$eloquentModelClass' is not an Eloquent model");
                    }
                    // else if(!$this->eloquentClassHasDateCastableAttribute($eloquentModelClass, $toEventSpec))
                    // {
                    //     throw new \Exception("Model '$eloquentModelClass' does not have a valid date attribute by the name of '$toEventSpec' (trying to extract events for Nova Resource $novaResourceClass)");
                    // }
                    
                    // Since these are single-day events by definition, we only query for the models 
                    // that have the date attribute within the current calendar range
                    $afterFilter = new AfterOrOnDate('', $toEventSpec);
                    $beforeFilter = new BeforeOrOnDate('', $toEventSpec);
                    $models = $eloquentModelClass::orderBy($toEventSpec);
                    $models = $afterFilter->modulateQuery($models, $firstDayOfCalendar);
                    $models = $beforeFilter->modulateQuery($models, $lastDayOfCalendar);

                    foreach($models->cursor() as $model)
                    {
                        $this->allEvents[] = $this->resourceToEvent(new $novaResourceClass($model), $toEventSpec);
                    }
                }
                else if(is_array($toEventSpec) && count($toEventSpec) == 2 && is_string($toEventSpec[0]) && is_string($toEventSpec[1]))
                {
                    // If an array containing two strings is supplied as the toEventSpec, they are assumed to 
                    // be the name of two datetime attributes on the underlying Eloquent model
                    // that will be used as the start and end datetime for a event
                    // that can be either single or multi-day (depending on the values of each model instance)
                    $eloquentModelClass = $novaResourceClass::$model;
                    if(!is_subclass_of($eloquentModelClass, EloquentModel::class))
                    {
                        throw new \Exception("'$eloquentModelClass' is not an Eloquent model");
                    }
                    else 
                    {
                        // if(!$this->eloquentClassHasDateCastableAttribute($eloquentModelClass, $toEventSpec[0]))
                        // {
                        //     throw new \Exception("Model '$eloquentModelClass' does not have a valid date attribute by the name of '" .$toEventSpec[0] ."' (trying to extract events for Nova Resource $novaResourceClass)");
                        // }
                        // if(!$this->eloquentClassHasDateCastableAttribute($eloquentModelClass, $toEventSpec[1]))
                        // {
                        //     throw new \Exception("Model '$eloquentModelClass' does not have a valid date attribute by the name of '" .$toEventSpec[1] ."' (trying to extract events for Nova Resource $novaResourceClass)");
                        // }
                    }
                    
                    // Since multi-day events could now be included, we have to query for all models 
                    // that end after or on the first day of the calendar range
                    // and start before or on the last day of the calendar range
                    $afterFilter = new AfterOrOnDate('', $toEventSpec[1]);
                    $beforeFilter = new BeforeOrOnDate('', $toEventSpec[0]);
                    $models = $eloquentModelClass::orderBy($toEventSpec[0]);
                    $models = $afterFilter->modulateQuery($models, $firstDayOfCalendar);
                    $models = $beforeFilter->modulateQuery($models, $lastDayOfCalendar);

                    foreach($models->cursor() as $model)
                    {
                        $this->allEvents[] = $this->resourceToEvent(new $novaResourceClass($model), $toEventSpec[0], $toEventSpec[1]);
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
}
