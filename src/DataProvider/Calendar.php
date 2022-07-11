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
    const A_AVAILABLE_VIEWS = ['day', 'month', 'week'];
    const A_CALENDAR_LAYOUT = [
        'openingHour' => 9,
        'closingHour' => 17,
        'timelineInterval' => 30
    ];

    protected $firstDayOfWeek;
    protected $date;
    protected $year;
    protected $month;
    protected $week;
    protected $day;
    
    protected $request = null;

    private $startOfCalendar = null;
    private $endOfCalendar = null;
    private $startOfPeriod = null;
    private $endOfPeriod = null;
    private $periodDuration = null;

    private $allEvents = null;

    private $openingHour = null;
    private $closingHour = null;
    private $timelineInterval = null;
    private $timeline = null;

    protected array $views = [];
    
    public function __construct()
    {
        $this->firstDayOfWeek = NovaCalendar::MONDAY;
        $this->date = Carbon::today();
        $this->year = now()->year;
        $this->month = now()->month;
        $this->day = now()->day;
        $this->updateViewRanges();
        $this->initialize();
    }
    
    abstract public function novaResources();

    public function initialize(): void
    {
        $this->openingHour = $this->calendarDayLayout()['openingHour'];
        $this->closingHour = $this->calendarDayLayout()['closingHour'];
        $this->timelineInterval = $this->calendarDayLayout()['timelineInterval'];
        $this->timeline = $this->timeline();
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
        $this->week = Carbon::create($year, $month)->weekOfYear;
        $this->periodDuration = Carbon::createFromDate($year, $month)->daysInMonth;
        $this->updateViewRanges();
        return $this;
    }
    
    public function setYearAndWeek(int $year, int $week): self
    {
        $this->year = $year;
        $this->month = Carbon::now()->setISODate($year, $week)->month;
        $this->week = $week;
        $this->periodDuration = 7;
        $this->updateViewRanges();
        return $this;
    }

    public function setYearAndMonthAndDay(int $year, int $month, int $day): self
    {
        $this->year = $year;
        $this->month = $month;
        $this->day = $day;
        $this->week = Carbon::create($year, $month, $day)->weekOfYear;
        $this->periodDuration = 1;
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
        if ($this->periodDuration == 1) {
            return $this->startOfPeriod->translatedFormat('l, d F Y');
        }

        if ($this->periodDuration <= 7) 
        {
            return __(':from - :until', ['from' => $this->startOfPeriod->translatedFormat('d F Y'), 'until' => $this->endOfPeriod->translatedFormat('d F Y')]);
        }

        return $this->startOfPeriod->translatedFormat('F Y');
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

    public function calendarDayData(): array
    {
        $dateCursor = $this->startOfCalendar();

        $calendarDay = CalendarDay::forDateInYearAndMonth($dateCursor, $this->year, $this->month, $this->firstDayOfWeek);
        return $calendarDay->withEvents(
            $this->eventDataForDate($dateCursor),
            $this->openingHour,
            $this->closingHour,
            $this->timelineInterval,
            $this->timeline
        )->toArray();
    }

    public function calendarWeek(): array
    {
        $dateCursor = $this->startOfCalendar();

        $week = [];
        for ($j = 0; $j < 7; $j++) {
            $calendarDay = CalendarDay::forDateInYearAndMonth($dateCursor, $this->year, $this->month, $this->firstDayOfWeek);
            $week[] = $calendarDay->withEvents(
                $this->eventDataForDate($dateCursor), 
                $this->openingHour, 
                $this->closingHour, 
                $this->timelineInterval, 
                $this->timeline
            )->toArray();

            $dateCursor = $dateCursor->addDay();
        }

        return $week;
    }

    public function calendarWeeks(): array
    {
        $month = [];
        $dateCursor = $this->startOfCalendar();

        for ($i = 0; $i < self::N_CALENDAR_WEEKS; $i++) {
            $weekData = [];
            $weekNumber = $dateCursor->weekOfYear;
            for ($j = 0; $j < 7; $j++) {
                $calendarDay = CalendarDay::forDateInYearAndMonth($dateCursor, $this->year, $this->month, $this->firstDayOfWeek);
                $weekData[] = $calendarDay->withEvents($this->eventDataForDate($dateCursor))->toArray();
                $dateCursor = $dateCursor->addDay();
            }
            $month[] = ['number' => $weekNumber, 'data' => $weekData];
        }

        return $month;
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
        $isDayOnlyView = $this->periodDuration == 1;
     
        // Get all events that start today, and if the date is the first day of the week
        // also get all multiday events that started before today and end on or after it
        // ('running multiday events')
        $events = array_filter($this->allEvents(), function($e) use ($date, $isFirstDayColumn, $isDayOnlyView) {
            return $e->start()->isSameDay($date)
                    ||
                    (($isFirstDayColumn || $isDayOnlyView)
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
        // Calculate calendar range for ...

        if ($this->periodDuration == 1) {

            $this->startOfPeriod = Carbon::createFromFormat('Y-m-d H:i:s', $this->year . '-' . $this->month . '-' . $this->day . ' 00:00:00');
            $this->endOfPeriod = $this->startOfPeriod->copy()->addDays($this->periodDuration)->subSeconds(1);

            $this->startOfCalendar = $this->startOfPeriod->copy();
            $this->endOfCalendar = $this->endOfPeriod->copy();

        } else if ($this->periodDuration > 7) {

            $this->startOfPeriod = Carbon::createFromFormat('Y-m-d H:i:s', $this->year . '-' . $this->month . '-1 00:00:00');
            $this->endOfPeriod = $this->startOfPeriod->copy()->addDays($this->periodDuration)->subSeconds(1);

            $nDaysToSub = ($this->startOfPeriod->dayOfWeekIso - ($this->firstDayOfWeek % 7)) % 7;
            while ($nDaysToSub < 0) {
                $nDaysToSub += 7;
            }
            $this->startOfCalendar = $this->startOfPeriod->copy()->subDays($nDaysToSub);
            $this->endOfCalendar = $this->startOfCalendar->copy()->addDays(7 * self::N_CALENDAR_WEEKS);

        } else {

            $day = Carbon::now()->setISODate($this->year, $this->week)->day;
            $this->startOfPeriod = Carbon::createFromFormat('Y-m-d H:i:s', $this->year . '-' . $this->month . '-' . $day . ' 00:00:00');
            $this->endOfPeriod = $this->startOfPeriod->copy()->addDays($this->periodDuration)->subSeconds(1);

            $this->startOfCalendar = $this->startOfPeriod->copy();
            $this->endOfCalendar = $this->endOfPeriod->copy();
        }
    }

    public function calendarViews() : array
    {
        return self::A_AVAILABLE_VIEWS;
    }

    public function calendarDayLayout(): array
    {
        return self::A_CALENDAR_LAYOUT;
    }

    public function timeline(): array
    {
        $openingMinute = $this->openingHour * 60;
        $closingMinute = $this->closingHour * 60;

        $timeCursor = $this->date->copy();
        $end = $timeCursor->copy()->addDay()->subSecond();

        $out = [];
        while ($timeCursor->lessThanOrEqualTo($end)) {
            $h = $timeCursor->hour;
            $m = $timeCursor->minute;
            $mm = $h * 60 + $m;
            $hm = $timeCursor->format('G:i');
            $out[] = [
                'hour' => $h,
                'minute' => $m,
                'hour_minute' => $hm,
                'is_open' => (($openingMinute <= $mm) && ($mm < $closingMinute)),
            ];

            $timeCursor->addMinutes($this->timelineInterval);
        }

        return $out;
    }
}
