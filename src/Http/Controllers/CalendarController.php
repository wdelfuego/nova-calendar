<?php

namespace Wdelfuego\NovaCalendar\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Carbon;

use Wdelfuego\NovaCalendar\DataProvider\MonthCalendar;

class CalendarController extends BaseController
{
    private $dataProvider;
    
    public function __construct()
    {
        $this->dataProvider = new MonthCalendar();
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
            'days' => $this->dataProvider->days(),
        ];
    }
    
}
