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

use Wdelfuego\NovaCalendar\View\AbstractView as View;

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
            // We are assuming these keys to exist since the Nova Tool 
            // Wdelfuego\NovaCalendar\NovaCalendar does all sorts of checks on initiation
            // Not sure if that assumption is completely valid but assuming valid config for now
            $dataProvider = new ($calendarConfig['dataProvider']);
            $dataProvider->setConfig($calendarConfig);
            $this->dataProviders[$calendarConfig['uri']] = $dataProvider;
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
    
    protected function getCalendarDataProviderForCurrentRequest()
    {
        if(!$this->request)
        {
            throw new \Exception("A request needs to be set in order to determine the calendar data provider");
        }
        
        $requestUri = substr($this->request->url(), strlen($this->request->schemeAndHttpHost()));

        // Get calendar URI from full request URI by ditching the prefix and the last path element (view)
        $calendarUri = substr($requestUri, strlen(self::API_PATH_PREFIX));
        $calendarUri = substr($calendarUri, 0, strrpos($calendarUri, '/'));

        return $this->getCalendarDataProviderForUri($calendarUri)->withRequest($this->request);
    }
    
    public function getCalendarData(string $view = 'month')
    {
        $dataProvider = $this->getCalendarDataProviderForCurrentRequest();

        if($this->request->query('isInitRequest'))
        {
            $dataProvider->setActiveFilterKey($dataProvider->defaultFilterKey());
        }
        else
        {
            $dataProvider->setActiveFilterKey($this->request->query('filter'));
        }
            
        $view = View::get($view);
        $view->initFromRequest($this->request);
        return $view->calendarData($this->request, $dataProvider);
    }

    /**
     * gets calendar views defined by in nova-calendar config file and checked against allowed calendar views.
     *
     * @return array
     */
    public function getCalendarViews(): array
    {
        $dataProvider = $this->getCalendarDataProviderForCurrentRequest();

        $viewSpecifiers = array_filter($dataProvider->calendarViews(), function($viewSpecifier) { 
            if(!View::isValidView($viewSpecifier))
            {
                throw new \Exception("Invalid view specifier: '$viewSpecifier'");
            }
            return true;
        });

        return [
            'calendar_views' => array_unique($viewSpecifiers),
            'windowTitle' => $dataProvider->windowTitle(),
        ];
    }

}
