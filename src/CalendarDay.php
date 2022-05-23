<?php

namespace Wdelfuego\NovaCalendar;

use Wdelfuego\NovaCalendar\Contracts\CalendarDayInterface;
use Illuminate\Support\Carbon;

class CalendarDay implements CalendarDayInterface
{
    public static function forDateInYearAndMonth(Carbon $date, int $year, int $month) : self
    {
        return new self(
            $date->format('j'),
            $date->year == $year && $date->month == $month,
            $date->isToday(),
            $date->isWeekend(),
        );
    }
    
    protected $label;
    protected $isWithinRange;
    protected $isToday;
    protected $isWeekend;
    protected $events;
    
    public function __construct(
        string $label = '',
        bool $isWithinRange = true,
        bool $isToday = false,
        bool $isWeekend = false,
        array $events = [], 
    )
    {
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
            'label' => $this->label,
            'isWithinRange' => $this->isWithinRange ? 1 : 0,
            'isToday' => $this->isToday ? 1 : 0,
            'isWeekend' => $this->isWeekend ? 1 : 0,
            'events' => $this->events
        ];
    }
}
