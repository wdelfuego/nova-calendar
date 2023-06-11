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
 
namespace Wdelfuego\NovaCalendar\View;

use Illuminate\Support\Carbon;
use Laravel\Nova\Http\Requests\NovaRequest;

use Wdelfuego\NovaCalendar\Contracts\CalendarDataProviderInterface;
use Wdelfuego\NovaCalendar\CalendarDay;

class Month extends AbstractView
{
    const N_CALENDAR_WEEKS = 6;

    protected $year = null;
    protected $month = null;
    
    public function specifier() : string
    {
        return self::MONTH;
    }
    
    public function initFromRequest(NovaRequest $request)
    {
        $this->setYearAndMonth($request->query('y'), $request->query('m'));
    }
    
    public function viewData(CalendarDataProviderInterface $dataProvider) : array
    {
        return [
            'shouldShowWeekNumbers' => $dataProvider->shouldShowWeekNumbers(),
            'year' => $this->year,
            'month' => $this->month,
            'columns' => $dataProvider->daysOfTheWeek(),
            'weeks' => $this->eventsByWeek($dataProvider)
        ];
    }
    
    public function setYearAndMonth($year, $month) : self
    {
        $year  = is_null($year)  || !is_numeric($year)  ? now()->year  : intval($year);
        $month = is_null($month) || !is_numeric($month) ? now()->month : intval($month);
        while($month > 12) { $year += 1; $month -= 12; }
        while($month < 1)  { $year -= 1; $month += 12; }
        
        $this->year = $year;
        $this->month = $month;

        return $this;
    }
    
    public function eventsByWeek(CalendarDataProviderInterface $dataProvider) : array
    {
        $out = [];
        $dateCursor = $dataProvider->startOfCalendar();

        for($i = 0; $i < self::N_CALENDAR_WEEKS; $i++)
        {
            $weekYear = $dateCursor->year;
            $weekNumber = $dateCursor->weekOfYear;

            $week = [];
            for($j = 0; $j < 7; $j++)
            {
                $calendarDay = CalendarDay::forDateInYearAndMonth($dateCursor, $this->year, $this->month, $this->firstDayOfWeek());
                $calendarDay = $dataProvider->customizeCalendarDay($calendarDay);
                $week[] = $calendarDay->withEvents($this->eventDataForDate($dataProvider, $dateCursor))->toArray();
                
                $dateCursor = $dateCursor->addDay();
            }
            $out[] = [
                'year' => $weekYear,
                'number' => $weekNumber,
                'days' => $week
            ];
            // $out[] = $week;
        }
        
        return $out;
    }
    
    
    protected function startOfRange() : Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->year.'-'.$this->month.'-1 00:00:00');
    }
    
    protected function endOfRange() : Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->year.'-'.(int)($this->month+1).'-1 00:00:00')->subSeconds(1);
    }
    
    protected function startOfCalendar() : Carbon
    {
        $nDaysToSub = ($this->startOfRange()->dayOfWeekIso - ($this->firstDayOfWeek() % 7)) % 7;
        while($nDaysToSub < 0) { $nDaysToSub += 7; }
        return $this->startOfRange()->copy()->subDays($nDaysToSub)->setTime(0,0);
    }
    
    protected function endOfCalendar() : Carbon
    {
        return $this->startOfCalendar()->copy()->addDays(7 * self::N_CALENDAR_WEEKS + 1)->subSeconds(1);
    }
    
    protected function eventDataForDate(CalendarDataProviderInterface $dataProvider, Carbon $date) : array
    {
        $date->setTime(0,0,0);
        $isFirstDayOfWeek = ($date->dayOfWeekIso == $this->firstDayOfWeek());
        
        // Get all events that start today, and if the date is the first day of the week
        // also get all multiday events that started before today and end on or after it
        // ('running multiday events')
        $events = array_filter($dataProvider->allEvents(), function($e) use ($date, $isFirstDayOfWeek) {
            return $e->start()->isSameDay($date)
                    ||
                    ($isFirstDayOfWeek
                        && $e->end() 
                        && $e->start()->isBefore($date) 
                        && $e->end()->gte($date));
        });

        // Sort events (as a heuristic, since CSS won't always match event order 
        // between different week rows perfectly due to 'column dense')
        usort($events, function($a, $b) use ($date, $isFirstDayOfWeek) { 

            $aDays = min(7,$a->spansDaysFrom($date));
            $bDays = min(7,$b->spansDaysFrom($date));

            // Longer events first
            if($aDays != $bDays) { return $bDays - $aDays; }

            // If we're in the first day column and both events span 7 days,
            // let running multi-day events precede events that start today
            if($isFirstDayOfWeek && $aDays == 7 && $bDays == 7)
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
        return array_map(fn($e): array => $e->toArray(
            $date, 
            $dataProvider->startOfRange(), 
            $dataProvider->endOfRange(), 
            $dataProvider->firstDayOfWeek()
        ), $events);
    }

}
