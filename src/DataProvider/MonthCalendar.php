<?php

namespace Wdelfuego\NovaCalendar\DataProvider;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use Laravel\Nova\Resource as NovaResource;

use Jenssegers\Date\Date as LocalizedDate;
use Wdelfuego\Nova\DateTime\Filters\NotBeforeDate;
use Wdelfuego\Nova\DateTime\Filters\NotAfterDate;
use Wdelfuego\NovaCalendar\Interface\MonthDataProviderInterface;

use Wdelfuego\NovaCalendar\CalendarDay;
use Wdelfuego\NovaCalendar\Event;

class MonthCalendar implements MonthDataProviderInterface
{
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 3;
    const THURSDAY = 4;
    const FRIDAY = 5;
    const SATURDAY = 6;
    const SUNDAY = 7;
    
    const CALENDAR_WEEKS = 6;
    
    protected $weekStartsOn;
    protected $year;
    protected $month;
    
    private $allEvents = null;
    
    public function __construct(int $year = null, int $month = null)
    {
        $this->weekStartsOn = self::MONDAY;
        $this->year = $year ?? now()->year;
        $this->month = $month ?? now()->month;
    }

    public function setYearAndMonth(int $year, int $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function startWeekOn(int $dayOfWeekIso)
    {
        $this->weekStartsOn = min(self::SUNDAY, max($dayOfWeekIso, self::MONDAY));
    }

    public function startWeekOnSunday()
    {
        $this->startWeekOn(self::SUNDAY);
    }

    public function title() : string
    {
        return ucfirst($this->firstDayOfMonth()->format('F \'y'));
    }

    public function daysOfTheWeek() : array
    {
        $out = [];
        $currentDay = new LocalizedDate(Carbon::getDays()[$this->weekStartsOn % 7]);
        for($i = 0; $i < 7; $i++)
        {
            $out[] = $currentDay->dayName;
            $currentDay = $currentDay->addDay();
        }
        return $out;
    }

    public function calendarDays() : array
    {
        $out = [];
        $dateCursor = $this->firstDayOfCalendar();

        for($i = 0; $i < 6; $i++)
        {
            $week = [];
            for($j = 0; $j < 7; $j++)
            {
                $calendarDay = CalendarDay::forDateInYearAndMonth($dateCursor, $this->year, $this->month);
                $week[] = $calendarDay->withEvents($this->eventDataForDate($dateCursor))->toArray();
                
                $dateCursor = $dateCursor->addDay();
            }
            $out[] = $week;
        }
        
        return $out;
    }
    
    protected function customizeEvent(Event $event) : Event
    {
        return $event;
    }
    
    protected function firstDayOfMonth() : LocalizedDate
    {
        return LocalizedDate::createFromFormat('Y-m-d', $this->year.'-'.$this->month.'-1');
    }

    protected function firstDayOfCalendar(): LocalizedDate
    {
        $firstOfMonth = $this->firstDayOfMonth();
        return $firstOfMonth->subDays($firstOfMonth->dayOfWeekIso - $this->weekStartsOn);
    }
    
    protected function lastDayOfCalendar(): LocalizedDate
    {
        return $this->firstDayOfCalendar()->addDays(7 * self::CALENDAR_WEEKS);
    }

    protected function eventDataForDate(DateTimeInterface $date) : array
    {
        $events = array_filter($this->allEvents(), function($e) use ($date) {
            return $e->start()->isSameDay($date);
        });

        return array_map(fn($e): array => $e->toArray(), $events);
    }
    
    private function resourceToEvent(NovaResource $resource, string $dateAttribute) : Event
    {
        return $this->customizeEvent(Event::fromResource($resource, $dateAttribute));
    }
    
    private function allEvents() : array
    {
        if(is_null($this->allEvents))
        {
            $this->allEvents = [];
            $firstDayOfCalendar = $this->firstDayOfCalendar();
            $lastDayOfCalendar = $this->lastDayOfCalendar();
        
            foreach($this->novaResources() as $novaResourceClass => $dateAttribute)
            {
                if(!is_subclass_of($novaResourceClass, NovaResource::class))
                {
                    throw new \Exception("Only Nova Resources can be automatically fetched for event generation ($novaResourceClass is not a Nova Resource)");
                }
            
                $notBefore = new NotBeforeDate('', $dateAttribute);
                $notAfter = new NotAfterDate('', $dateAttribute);
            
                $eloquentModelClass = $novaResourceClass::$model;
                $models = $eloquentModelClass::orderBy($dateAttribute);
                $models = $notBefore->modulateQuery($models, $firstDayOfCalendar);
                $models = $notAfter->modulateQuery($models, $lastDayOfCalendar);

                foreach($models->cursor() as $model)
                {
                    $this->allEvents[] = $this->resourceToEvent(new $novaResourceClass($model), $dateAttribute);
                }
            }
        }
        
        return $this->allEvents;
    }
}
