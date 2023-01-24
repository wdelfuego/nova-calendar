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

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Carbon;
use Laravel\Nova\Http\Requests\NovaRequest;

use Wdelfuego\NovaCalendar\Interface\CalendarDataProviderInterface;

class CalendarController extends BaseController
{
    // Must match the hard-coded value in Tool.vue's reload() method
    const API_PATH_PREFIX = '/nova-vendor/wdelfuego/nova-calendar/';
    
    private $request;
    private $dataProviders = [];

    public function __construct(NovaRequest $request)
    {
        $this->request = $request;

        // Load data providers, keyed by uri
        foreach(config('nova-calendar', []) as $calendarKey => $calendarConfig)
        {
            $this->dataProviders[$calendarConfig['uri']] = new ($calendarConfig['provider']);
        }
    }

    protected function getCalendarDataProviderForUri(string $calendarUri)
    {
        if(!isset($this->dataProviders[$calendarUri]))
        {
            throw new \Exception("Unknown calendar uri: $calendarUri");
        }
        
        return $this->dataProviders[$calendarUri];
    }
            
    public function getMonthCalendarData($year = null, $month = null)
    {
        $requestUri = substr($this->request->url(), strlen($this->request->schemeAndHttpHost()));
        $calendarUri = substr($requestUri, strlen(self::API_PATH_PREFIX));
        $dataProvider = $this->getCalendarDataProviderForUri($calendarUri);
        
        $year = $this->request->query('y');
        $month = $this->request->query('m');
        $year  = is_null($year)  || !is_numeric($year)  ? now()->year  : intval($year);
        $month = is_null($month) || !is_numeric($month) ? now()->month : intval($month);
        
        while($month > 12) { $year += 1; $month -= 12; }
        while($month < 1)  { $year -= 1; $month += 12; }
        
        $dataProvider->setRequest($this->request)->setYearAndMonth($year, $month);
        
        return [
            'year' => $year,
            'month' => $month,
            'title' => $dataProvider->title(),
            'columns' => $dataProvider->daysOfTheWeek(),
            'weeks' => $dataProvider->calendarWeeks(),
            'styles' => array_replace_recursive($this->defaultStyles(), $dataProvider->eventStyles()),
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
}
