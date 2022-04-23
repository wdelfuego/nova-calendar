<?php

namespace Wdelfuego\NovaCalendar\DataProvider;

use Illuminate\Support\Carbon;
use Jenssegers\Date\Date as LocalizedDate;

class MonthCalendar implements MonthDataProviderInterface
{
    const MONDAY = 1;
    const SUNDAY = 7;
    
    private $weekStartsOn;
    private $year;
    private $month;
    
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

    private function firstDayOfMonth() : LocalizedDate
    {
        return LocalizedDate::createFromFormat('Y-m-d', $this->year.'-'.$this->month.'-1');
    }

    private function firstDayOfCalendar(): LocalizedDate
    {
        $firstOfMonth = $this->firstDayOfMonth();
        return $firstOfMonth->subDays($firstOfMonth->dayOfWeekIso - $this->weekStartsOn);
    }

    public function days() : array
    {
        $out = [];
        $currentDay = $this->firstDayOfCalendar();

        // 6 weeks
        for($i = 0; $i < 6; $i++)
        {
            $week = [];
            // 7 days per week
            for($j = 0; $j < 7; $j++)
            {
                $week[] = [
                    'dayNum' => $currentDay->format('j'),
                    'isWithinMonth' => $currentDay->month == $this->month,
                    'isToday' => $currentDay->isToday(),
                    'isWeekend' => $currentDay->isWeekend(),
                    'events' => [
                        new class { public $time = '10:00'; public $name = 'Event X'; public $badges = ['🔸']; },
                        new class { public $name = 'Event Y'; public $badges = ['✓', '🔸', 'X']; },
                    ]
                ];
                
                $currentDay = $currentDay->addDay();
            }
            $out[] = $week;
        }
        return $out;
    }
        
    public function title() : string
    {
        return ucfirst($this->firstDayOfMonth()->format('F \'y'));
    }

}
