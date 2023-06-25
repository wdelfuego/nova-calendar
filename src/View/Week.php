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

class Week extends AbstractView
{
    const LAYOUT = [
        'openingHour' => 6,         // calendar not rendered prior this hour on timeline, day, week views unless there are Events before this hour
        'closingHour' => 20,        // calendar not rendered after this hour on timeline, day, week views unless there are Events after this hour
        'timelineInterval' => 30    // for UI purposes, sets granulation of timeslots
    ];

    protected $year = null;
    protected $month = null;
    protected $week = null;

    private $openingHour = 9;
    private $closingHour = 17;
    private $timelineInterval = 30;
    
    public function specifier() : string
    {
        return self::WEEK;
    }
    
    public function initFromRequest(NovaRequest $request)
    {
        $this->setYearAndWeek($request->query('y'), $request->query('w'));
        $this->openingHour = $this->calendarDayLayout()['openingHour'];
        $this->closingHour = $this->calendarDayLayout()['closingHour'];
        $this->timelineInterval = $this->calendarDayLayout()['timelineInterval'];
        $this->timeline = $this->timeline();
    }
    
    public function viewData(CalendarDataProviderInterface $dataProvider) : array
    {
        return [
            'shouldShowWeekNumbers' => $dataProvider->shouldShowWeekNumbers(),
            'year' => $this->year,
            'month' => $this->month,
            'week' => $this->week,
            'columns' => $dataProvider->daysOfTheWeek(),
            'layout' => $this->calendarDayLayout(),
            'weekData' => $this->eventsByWeek($dataProvider),
            'timeline' => $this->timeline(),
        ];
    }

    /**
     * Sets Year and Week for week view
     *
     * @param  int $year
     * @param  int $week
     * @return self
     */
    public function setYearAndWeek($year, $week) : self
    {
        $year  = is_null($year)  || !is_numeric($year)  ? now()->year  : intval($year);
        $week = is_null($week) || !is_numeric($week) ? now()->weekOfYear : intval($week);
        $month = Carbon::today()->setISODate($year, $week)->month;
        while ($week > 52) { $year += 1; $week -= 52; }
        while ($week < 1)  { $year -= 1; $week += 52; }
        
        $this->year = $year;
        $this->month = $month;
        $this->week = $week;
        
        return $this;
    }
    
    public function eventsByWeek(CalendarDataProviderInterface $dataProvider) : array
    {
        $dateCursor = $dataProvider->startOfCalendar();

        $week = [];
        for($j = 0; $j < 7; $j++)
        {
            $calendarDay = CalendarDay::forDateInYearAndWeek($dateCursor, $this->year, $this->week, $this->firstDayOfWeek());
            $calendarDay = $dataProvider->customizeCalendarDay($calendarDay);
            $week[] = $calendarDay->withEvents($this->eventDataForDate($dataProvider, $dateCursor))->toArray();
            
            $dateCursor = $dateCursor->addDay();
        }
        
        return $week;
    }
    
    
    protected function startOfRange() : Carbon
    {
        $day = Carbon::now()->setISODate($this->year, $this->week)->day;
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->year . '-' . $this->month . '-' . $day . ' 00:00:00');
    }
    
    protected function endOfRange() : Carbon
    {
        return $this->startOfRange()->addWeek()->subSecond();
    }
    
    protected function startOfCalendar() : Carbon
    {
        return $this->startOfRange();
    }
    
    protected function endOfCalendar() : Carbon
    {
        return $this->endOfRange();
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

    /**
     * Gets defualt calendar layout 
     *
     * @return array
     */
    public function calendarDayLayout(): array
    {
        return self::LAYOUT;
    }

    /**
     * Gets default hours timeline for rendering purposes. 
     * ToDo: add time locale support
     *
     * @return array
     */
    public function timeline(): array
    {
        $openingMinute = $this->openingHour * 60;
        $closingMinute = $this->closingHour * 60;

        $timeCursor = Carbon::today()->copy();
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
