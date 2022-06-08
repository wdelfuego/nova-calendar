<?php

namespace App\Providers;

use Wdelfuego\NovaCalendar\DataProvider\MonthCalendar;

class CalendarDataProvider extends MonthCalendar
{
    //
    // Add the Nova resources that should be displayed on the calendar to this method
    //
    // Must return an array with string keys and string values;
    // - each key is a Nova resource class name (eg: 'App/Nova/User::class')
    // - each value is the attribute name of a DateTime casted property   
    //   of the underlying Eloquent model (eg: 'created_at')
    //
    // See https://github.com/wdelfuego/nova-calendar to find out
    // how to customize the way the events are displayed
    // 
    public function novaResources() : array
    {
        return [
            'App/Nova/User::class' => 'created_at',
        ];
    }
}
