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

use DateTimeInterface;
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
            $date->copy()->setTime(0,0),
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
    
    public $date;
    protected $weekdayColumn;
    protected $label;
    protected $badges;
    protected $isWithinRange;
    protected $isToday;
    protected $isWeekend;
    protected $events;
    private $openingHour;
    private $closingHour;
    private $timelineInterval;
    private $timeline;
    
    public function __construct(Carbon $date, int $weekdayColumn, string $label = '', bool $isWithinRange = true, bool $isToday = false, bool $isWeekend = false, array $events = [])
    {
        $this->date = $date;
        $this->weekdayColumn = $weekdayColumn;
        $this->label = $label;
        $this->badges = [];
        $this->isWithinRange = $isWithinRange;
        $this->isToday = $isToday;
        $this->isWeekend = $isWeekend;
        $this->events = $events;
    }
    
    public function withEvents(array $events, int $openingHour = 8, int $closingHour = 20, int $timelineInterval = 30, array $timeline = []) : self
    {
        $this->events = $events;
        $this->openingHour = $openingHour;
        $this->closingHour = $closingHour;
        $this->timelineInterval = $timelineInterval;
        $this->timeline = $timeline;
        return $this;
    }

    public function toArray() : array
    {
        return [
            'weekdayColumn' => $this->weekdayColumn,
            'label' => $this->label,
            'badges' => $this->badges,
            'isWithinRange' => $this->isWithinRange ? 1 : 0,
            'isToday' => $this->isToday ? 1 : 0,
            'isWeekend' => $this->isWeekend ? 1 : 0,
            'openingHour' => $this->openingHour,
            'closingHour' => $this->closingHour,
            'timelineInterval' => $this->timelineInterval,
            'timeline' => $this->timeline,
            'earliestEvent' => $this->earliestEventStart(),
            'latestEvent' => $this->latestEventEnd(),
            'eventsSingleDay' => $this->eventsSingleDay(),
            'eventsMultiDay' => $this->eventsMultiDay(),
        ];
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
        $events = array_filter($this->eventsSingleDay(), fn($e): bool => !is_null($e['endTime']));
        $lastEvent = end($events);
        return $lastEvent ? (intval($lastEvent['startHour']) * 60 + intval($lastEvent['durationInMinutes'])) : $this->closingHour * 60;
    }
    
    public function badges(array $v = null) : array
    {
        if(!is_null($v)) 
        {
            $this->badges = $v;
        }
        
        return $this->badges;
    }
    
    public function addBadge(string $v) : self
    {
        return $this->addBadges($v);
    }
    
    public function addBadges(string ...$v) : self
    {
        foreach($v as $badge)
        {
            $this->badges[] = $badge;            
        }
        
        return $this;
    }
    
    public function removeBadge(string $v) : self
    {
        return $this->removeBadges($v);
    }
    
    public function removeBadges(string ...$v) : self
    {
        foreach($v as $badge)
        {
            $this->badges = array_filter($this->badges, function($b) use ($badge) {
                return $b != $badge;
            });
        }
        return $this;
    }
}
