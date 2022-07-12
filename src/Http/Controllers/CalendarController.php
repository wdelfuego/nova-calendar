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

namespace Wdelfuego\NovaCalendar\Http\Controllers;

use Illuminate\Support\Carbon;
use Laravel\Nova\Http\Requests\NovaRequest;
use Wdelfuego\NovaCalendar\DataProvider\Calendar;
use Illuminate\Routing\Controller as BaseController;
use Wdelfuego\NovaCalendar\Interface\CalendarDataProviderInterface;

class CalendarController extends BaseController
{
    private $request;
    private $dataProvider;

    public function __construct(NovaRequest $request, CalendarDataProviderInterface $dataProvider)
    {
        $this->request = $request;
        $this->dataProvider = $dataProvider;
    }

    public function getCalendarViews() : array
    {
        return [
            'calendar_views' => $this->sanitizeCalendarViews($this->dataProvider->calendarViews()),
        ];
    }
    
    public function getDayCalendarData($year = null, $month = null, $day = null)
    {
        $year = is_null($year) || !is_numeric($year) ? now()->year : intval($year);
        $month = is_null($month) || !is_numeric($month) || intval($month) > 13 || intval($month) < 0 ? now()->month : intval($month);
        $day = is_null($day) || !is_numeric($day) || intval($month) > 32 || intval($month) < 0 ? now()->day : intval($day);
        $week = Carbon::createFromDate($year, $month, $day)->weekOfYear;

        $monthDate = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $monthDate->daysInMonth;

        if ($day > $daysInMonth) {
            $month += 1;
            if ($month > 12) {
                $year += 1;
                $month -= 12;
            }
            $day = $day - $daysInMonth;
        }

        if ($day < 1) {
            $month -= 1;
            if ($month < 1) {
                $year -= 1;
                $month += 12;
            }
            $daysInPreviousMonth = $monthDate->subMonth()->daysInMonth;
            $day = $daysInPreviousMonth - $day;
        }

        $this->dataProvider->setRequest($this->request)->setYearAndMonthAndDay($year, $month, $day);

        return [
            'year' => $year,
            'month' => $month,
            'week' => $week,
            'day' => $day,
            'day_name' => Carbon::createFromDate($year, $month, $day)->translatedFormat('l'),
            'title' => $this->dataProvider->title(),
            'day_data' => $this->dataProvider->calendarDayData(),
            'styles' => array_replace_recursive($this->defaultStyles(), $this->dataProvider->eventStyles()),
        ];
    }

    public function getWeekCalendarData($year = null, $week = null)
    {
        $year  = is_null($year)  || !is_numeric($year)  ? now()->year  : intval($year);
        $week = is_null($week) || !is_numeric($week) ? now()->weekOfYear : intval($week);
        $month = Carbon::today()->setISODate($year, $week)->month;
        $day = Carbon::today()->setISODate($year, $week)->day;

        while ($week > 52) {
            $year += 1;
            $week -= 52;
        }
        while ($week < 1) {
            $year -= 1;
            $week += 52;
        }
        
        $this->dataProvider->setRequest($this->request)->setYearAndWeek($year, $week);

        return [
            'year' => $year,
            'week' => $week,
            'month' => $month,
            'day' => $day,
            'title' => $this->dataProvider->title(),
            'columns' => $this->dataProvider->daysOfTheWeek(),
            'layout' => $this->dataProvider->calendarDayLayout(),
            'timeline' => $this->dataProvider->timeline(),
            'week_data' => $this->dataProvider->calendarWeek(),
            'styles' => array_replace_recursive($this->defaultStyles(), $this->dataProvider->eventStyles()),
        ];
    }

    public function getMonthCalendarData($year = null, $month = null)
    {
        $year  = is_null($year)  || !is_numeric($year)  ? now()->year  : intval($year);
        $month = is_null($month) || !is_numeric($month) ? now()->month : intval($month);
        $week = Carbon::createFromDate($year, $month, 1)->weekOfYear;

        while ($month > 12) {
            $year += 1;
            $month -= 12;
        }
        while ($month < 1) {
            $year -= 1;
            $month += 12;
        }

        $this->dataProvider->setRequest($this->request)->setYearAndMonth($year, $month);
        return [
            'year' => $year,
            'month' => $month,
            'week' => $week,
            'day' => 1,
            'weekNumbers' => [],
            'title' => $this->dataProvider->title(),
            'columns' => $this->dataProvider->daysOfTheWeek(),
            'weeks' => $this->dataProvider->calendarWeeks(),
            'styles' => array_replace_recursive($this->defaultStyles(), $this->dataProvider->eventStyles()),
        ];
    }
  
    public function defaultStyles() : array
    {
        return [
            'default' => [
                'color' => '#fff',
                'background-color' => 'rgba(var(--colors-primary-500), 0.9)',
            ]
        ];
    }

    private function sanitizeCalendarViews(array $cv): array
    {
        $out = [];
        if (($cv == Calendar::A_AVAILABLE_VIEWS) || empty($cv)) {
            $out = $cv;
        } else {
            foreach ($cv as $view) {
                if (in_array($view, Calendar::A_AVAILABLE_VIEWS) && !in_array($view, $out)) {
                    $out[] = $view;
                }
            }
        }

        return $out;
    }
}
