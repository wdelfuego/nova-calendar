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

use Wdelfuego\NovaCalendar\Contracts\MonthDataProviderInterface;
use Wdelfuego\NovaCalendar\EventGenerator\NovaEventGenerator;

use Wdelfuego\NovaCalendar\NovaCalendar;
use Wdelfuego\NovaCalendar\CalendarDay;
use Wdelfuego\NovaCalendar\Event;


abstract class MonthCalendar extends AbstractCalendarDataProvider implements MonthDataProviderInterface
{
    const N_CALENDAR_WEEKS = 6;

    protected $year;
    protected $month;

    public function __construct(int $year = null, int $month = null)
    {
        parent::__construct();
        $this->setYearAndMonth($year ?? now()->year, $month ?? now()->month);
    }

    public function title() : string
    {
        return ucfirst($this->startOfRange()->translatedFormat('F \'y'));
    }
    
    public function calendarData() : array
    {
        $out = [];
        $dateCursor = $this->startOfCalendar();

        for($i = 0; $i < self::N_CALENDAR_WEEKS; $i++)
        {
            $week = [];
            for($j = 0; $j < 7; $j++)
            {
                $calendarDay = CalendarDay::forDateInYearAndMonth($dateCursor, $this->year, $this->month, $this->firstDayOfWeek);
                $calendarDay = $this->customizeCalendarDay($calendarDay);
                $week[] = $calendarDay->withEvents($this->eventDataForDate($dateCursor))->toArray();
                
                $dateCursor = $dateCursor->addDay();
            }
            $out[] = $week;
        }
        
        return $out;
    }
    
    private function updateViewRanges() : void
    {
        // Calculate month range
        $this->startOfRange(Carbon::createFromFormat('Y-m-d H:i:s', $this->year.'-'.$this->month.'-1 00:00:00'));
        $this->endOfRange(Carbon::createFromFormat('Y-m-d H:i:s', $this->year.'-'.(int)($this->month+1).'-1 00:00:00')->subSeconds(1));

        // Calculate calendar range
        $nDaysToSub = ($this->startOfRange()->dayOfWeekIso - ($this->firstDayOfWeek % 7)) % 7;
        while($nDaysToSub < 0) { $nDaysToSub += 7; }
        $this->startOfCalendar($this->startOfRange()->copy()->subDays($nDaysToSub)->setTime(0,0));
        $this->endOfCalendar($this->startOfCalendar()->copy()->addDays(7 * self::N_CALENDAR_WEEKS + 1)->subSeconds(1));
    }
    
    public function setYearAndMonth(int $year, int $month) : self
    {
        $this->year = $year;
        $this->month = $month;
        $this->updateViewRanges();
        return $this;
    }
}
