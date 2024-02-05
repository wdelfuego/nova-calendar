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
// use Wdelfuego\NovaCalendar\Contracts\CalendarDataProviderInterface;
// use Wdelfuego\NovaCalendar\Contracts\ViewInterface;

class CalendarController extends BaseController
{
    // Must match the hard-coded value in Tool.vue's reload() method
    const API_PATH_PREFIX = '/nova-vendor/wdelfuego/nova-calendar/';

    private $dataProviders = [];

    public function __construct(NovaRequest $request)
    {
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

    public function getCalendarData(NovaRequest $request, string $view = 'month')
    {
        $requestUri = substr($request->url(), strlen($request->schemeAndHttpHost()));

        // Get calendar URI from full request URI by ditching the prefix and the last path element (view)
        $calendarUri = substr($requestUri, strlen(self::API_PATH_PREFIX));
        $calendarUri = substr($calendarUri, 0, strrpos($calendarUri, '/'));

        $dataProvider = $this->getCalendarDataProviderForUri($calendarUri)->withRequest($request);
        if($request->query('isInitRequest'))
        {
            $dataProvider->setActiveFilterKey($dataProvider->defaultFilterKey());
        }
        else
        {
            $dataProvider->setActiveFilterKey($request->query('filter'));
        }

        $view = View::get($view);
        $view->initFromRequest($request);
        $view = $dataProvider->customizeView($view);
        return $view->calendarData($request, $dataProvider);
    }
}
