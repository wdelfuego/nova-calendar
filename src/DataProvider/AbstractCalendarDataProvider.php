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
use Illuminate\Http\Request;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource as NovaResource;

use Wdelfuego\NovaCalendar\Contracts\CalendarDataProviderInterface;
use Wdelfuego\NovaCalendar\EventGenerator\NovaEventGenerator;

use Wdelfuego\NovaCalendar\NovaCalendar;
use Wdelfuego\NovaCalendar\CalendarDay;
use Wdelfuego\NovaCalendar\Event;


abstract class AbstractCalendarDataProvider implements CalendarDataProviderInterface
{
    protected $firstDayOfWeek;
    protected $request = null;    
    
    // Start/end of calendar = date range that is _visible_ in the front-end (ie 42 days in month calendar)
    // Start/end of range = date range that is considered _active_ in the front-end (ie 31 days in month calendar)
    private $startOfCalendar = null;
    private $endOfCalendar = null;
    private $startOfRange = null;
    private $endOfRange = null;
        
    private $allEvents = null;
    private $config = [];
    
    public function __construct()
    {
        $this->firstDayOfWeek = NovaCalendar::MONDAY;
        $this->initialize();
    }

    abstract public function title() : string;
    abstract public function calendarData() : array;
    abstract public function novaResources() : array;
    abstract protected function updateViewRanges() : void;
    
    public function initialize(): void
    {

    }
    
    public function setConfig(array $config)
    {
        $this->config = $config;
    }
    
    public function configValue($key)
    {
        return $this->config[$key] ?? null;
    }
    
    public function windowTitle() : string
    {
        return $this->configValue('windowTitle') ?? '';
    }
    
    public function timezone(): string
    {
        return config('app.timezone') ?? 'UTC';
    }
    
    public function setRequest(Request $request) : self
    {
        $this->request = $request;
        return $this;
    }
    
    public function startOfCalendar(Carbon $v = null) : Carbon
    {
        if(!is_null($v))
        {
            $this->startOfCalendar = $v;
        }
        
        return $this->startOfCalendar;
    }
    
    public function endOfCalendar(Carbon $v = null) : Carbon
    {
        if(!is_null($v))
        {
            $this->endOfCalendar = $v;
        }
        
        return $this->endOfCalendar;
    }
    
    public function startOfRange(Carbon $v = null) : Carbon
    {
        if(!is_null($v))
        {
            $this->startOfRange = $v;
        }
        
        return $this->startOfRange;
    }
    
    public function endOfRange(Carbon $v = null) : Carbon
    {
        if(!is_null($v))
        {
            $this->endOfRange = $v;
        }
        
        return $this->endOfRange;
    }
    
    public function startWeekOn(int $dayOfWeekIso) : self
    {
        $this->firstDayOfWeek = min(NovaCalendar::SUNDAY, max($dayOfWeekIso, NovaCalendar::MONDAY));
        $this->updateViewRanges();
        return $this;
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

    public function eventStyles() : array
    {
        return [];
    }
    
    protected function customizeEvent(Event $event) : Event
    {
        return $event;
    }
    
    protected function customizeCalendarDay(CalendarDay $day) : CalendarDay
    {
        return $day;
    }
    
    protected function nonNovaEvents() : array
    {
        return [];
    }
    
    protected function urlForResource(NovaResource $resource)
    {
        return '/resources/' .$resource::uriKey() .'/' .$resource->id;
    }

    protected function excludeResource(NovaResource $resource) : bool
    {
        return false;
    }
    
    protected function eventDataForDate(Carbon $date) : array
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
                        && $e->end()->gte($date));
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
        return array_map(fn($e): array => $e->toArray($date, $this->startOfRange, $this->endOfRange, $this->firstDayOfWeek), $events);
    }

    private function allEvents() : array
    {
        if(is_null($this->allEvents))
        {
            $this->loadAllEvents();
        }
        
        return $this->allEvents;
    }
    
    private function loadAllEvents() : void
    {
        $this->allEvents = [];
    
        // First, fetch events from all Nova resources according to their toEventSpecs as specified in novaResources()
        foreach($this->novaResources() as $novaResourceClass => $toEventSpec)
        {
            $eventGenerator = null;
            if(!($eventGenerator = NovaEventGenerator::from($novaResourceClass, $toEventSpec)))
            {
                throw new \Exception("Invalid calendar event specification supplied for Nova resource $novaResourceClass");
            }
            
            foreach($eventGenerator->generateEvents($this->startOfCalendar(), $this->endOfCalendar()) as $event)
            {
                if($event->resource()->authorizedToView($this->request) && !$this->excludeResource($event->resource()))
                {
                    $this->allEvents[] = $event->withUrl($this->urlForResource($event->resource()));
                } 
            }
        }
        
        // Second, add the non-nova Events
        $this->allEvents = array_merge($this->allEvents, $this->nonNovaEvents());
        
        // Third, set all event timezones to calendar timezone
        foreach($this->allEvents as $event)
        {
            $event->timezone($this->timezone());
        }
        
        // Finally, run each event through the customizeEvent method
        $this->allEvents = array_map(fn($e) : Event => $this->customizeEvent($e), $this->allEvents);
    }

}
