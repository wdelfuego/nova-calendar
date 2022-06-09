<?php

namespace Wdelfuego\NovaCalendar;

use Wdelfuego\NovaCalendar\NovaCalendar;
use Wdelfuego\NovaCalendar\Interface\CalendarDayInterface;
use Illuminate\Support\Carbon;

class CalendarDay implements CalendarDayInterface
{
    public static function forDateInYearAndMonth(Carbon $date, int $year, int $month, int $weekStartsOn = null) : self
    {
        $weekStartsOn = $weekStartsOn ?? NovaCalendar::MONDAY;

        return new self(
            self::weekdayColumn($date, $weekStartsOn),
            $date->format('j'),
            $date->year == $year && $date->month == $month,
            $date->isToday(),
            $date->isWeekend(),
        );
    }
    
    public static function weekdayColumn(Carbon $date, int $weekStartsOn = 1) : int
    {
        $absDay = $date->dayOfWeekIso;
        return ($absDay - $weekStartsOn) % 7 + 1;
    }
    
    protected $weekdayColumn;
    protected $label;
    protected $isWithinRange;
    protected $isToday;
    protected $isWeekend;
    protected $events;
    
    public function __construct(
        int $weekdayColumn,
        string $label = '',
        bool $isWithinRange = true,
        bool $isToday = false,
        bool $isWeekend = false,
        array $events = [], 
    )
    {
        $this->weekdayColumn = $weekdayColumn;
        $this->label = $label;
        $this->isWithinRange = $isWithinRange;
        $this->isToday = $isToday;
        $this->isWeekend = $isWeekend;
        $this->events = $events;
    }
    
    public function withEvents(array $events) : self
    {
        $this->events = $events;
        return $this;
    }
    
    public function toArray() : array
    {
        return [
            'weekdayColumn' => $this->weekdayColumn,
            'label' => $this->label,
            'isWithinRange' => $this->isWithinRange ? 1 : 0,
            'isToday' => $this->isToday ? 1 : 0,
            'isWeekend' => $this->isWeekend ? 1 : 0,
            'eventsSingleDay' => $this->eventsSingleDay(),
            'eventsMultiDay' => $this->eventsMultiDay(),
        ];
    }
    
    private function eventsSingleDay() : array
    {
        // return [];
        return $this->events;
    }
    
    private function eventsMultiDay() : array
    {
        return $this->events;
    }
}
