<?php

namespace Wdelfuego\NovaCalendar\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Carbon;
use Laravel\Nova\Http\Requests\NovaRequest;

use Wdelfuego\NovaCalendar\Contracts\CalendarDataProviderInterface;

class CalendarController extends BaseController
{
    private $request;
    private $dataProvider;

    public function __construct(NovaRequest $request, CalendarDataProviderInterface $dataProvider)
    {
        $this->request = $request;
        $this->dataProvider = $dataProvider;
    }
    
    public function getMonthCalendarData($year = null, $month = null)
    {
        $year  = is_null($year)  || !is_numeric($year)  ? now()->year  : intval($year);
        $month = is_null($month) || !is_numeric($month) ? now()->month : intval($month);
        
        while($month > 12) { $year += 1; $month -= 12; }
        while($month < 1)  { $year -= 1; $month += 12; }
        
        $this->dataProvider->setYearAndMonth($year, $month);
            
        return [
            'year' => $year,
            'month' => $month,
            'title' => $this->dataProvider->title(),
            'columns' => $this->dataProvider->daysOfTheWeek(),
            'days' => $this->dataProvider->calendarDays(),
            'styles' => array_replace_recursive($this->defaultStyles(), $this->dataProvider->eventStyles()),
        ];
    }
    
    public function defaultStyles() : array
    {
        return [
            'default' => [
                'color' => '#fff',
                'background-color' => 'rgba(var(--colors-primary-500), 0.7)',
            ]
        ];
    }
}
