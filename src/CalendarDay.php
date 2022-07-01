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
 
namespace Wdelfuego\NovaCalendar;

use Wdelfuego\NovaCalendar\NovaCalendar;
use Wdelfuego\NovaCalendar\Interface\CalendarDayInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CalendarDay implements CalendarDayInterface
{
    public static function forDateInYearAndMonth(Carbon $date, int $year, int $month, int $firstDayOfWeek = null) : self
    {
        $firstDayOfWeek = $firstDayOfWeek ?? NovaCalendar::MONDAY;

        return new self(
            $date,
            self::weekdayColumn($date, $firstDayOfWeek),
            $date->format('j'),
            $date->year == $year && $date->month == $month,
            $date->isToday(),
            $date->isWeekend(),
        );
    }
    
    public static function weekdayColumn(Carbon $date, int $firstDayOfWeek = 1) : int
    {
        $absDay = $date->dayOfWeekIso;
        $mod = ($absDay - $firstDayOfWeek) % 7;
        while($mod < 0) { $mod += 7; }
        return $mod + 1;
    }
    
    protected $date;
    protected $weekdayColumn;
    protected $label;
    protected $isWithinRange;
    protected $isToday;
    protected $isWeekend;
    protected $events;
    private $openingHour = 0;
    private $closingHour = 0;
    private $timeslotInterval = 0;
    protected $layout;
    
    public function __construct(
        Carbon $date,
        int $weekdayColumn,
        string $label = '',
        bool $isWithinRange = true,
        bool $isToday = false,
        bool $isWeekend = false,
        array $events = [],
    )
    {
        $this->date = $date;
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

    public function withEventsAndLayout(array $events, array $layout): self
    {
        $this->events = $events;
        $this->layout = $layout;
        $this->openingHour = $layout['openingHour'];
        $this->closingHour = $layout['closingHour'];
        $this->timeslotInterval = $layout['timeslotInterval'];
        return $this;
    }

    public function toArray() : array
    {
        $out = [
            'weekdayColumn' => $this->weekdayColumn,
            'label' => $this->label,
            'isWithinRange' => $this->isWithinRange ? 1 : 0,
            'isToday' => $this->isToday ? 1 : 0,
            'isWeekend' => $this->isWeekend ? 1 : 0,
            'openingHour' => $this->openingHour,
            'closingHour' => $this->closingHour,
            'interval' => $this->timeslotInterval,
            'eventsSingleDay' => $this->eventsSingleDay(),
            'eventsMultiDay' => $this->eventsMultiDay(),
        ];

        $layout = $this->layout 
            ? ['earliestEvent' => $this->earliestEventStart(),
                'latestEvent' => $this->latestEventEnd(),
                'timeslots' => $this->timeslots()] 
            : [];
        
        return array_merge($out, $layout);
    }
    
    private function eventsSingleDay() : array
    {
        return array_filter($this->events, fn($e): bool => !!$e['isSingleDayEvent']);
    }
    
    private function eventsMultiDay() : array
    {
        return array_filter($this->events, fn($e): bool => !$e['isSingleDayEvent']);
    }

    private function earliestEventStart(): int
    {
        $events = $this->eventsSingleDay();
        $firstEvent = reset($events);
        return $firstEvent ? intval($firstEvent['startHour']) * 60 : $this->openingHour * 60;
    }

    private function latestEventEnd(): int
    {
        $events = $this->eventsSingleDay();
        $lastEvent = end($events);
        return $lastEvent ?  (intval($lastEvent['startHour']) + intval($lastEvent['durationInMinutes'])) * 60 : $this->closingHour * 60;
    }

    private function timeslots(): array
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
                'is_open' => (($openingMinute <= $mm) && ($mm <= $closingMinute)),
            ];

            $timeCursor->addMinutes($this->timeslotInterval);
        }

        return $out;
    }
}
