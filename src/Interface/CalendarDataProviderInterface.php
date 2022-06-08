<?php

namespace Wdelfuego\NovaCalendar\Interface;

interface CalendarDataProviderInterface
{
    public function __construct();
    
    // Will be displayed above the calendar
    public function title() : string;
    
    // A 1D array with the names of the seven days of the week, in order of display L -> R
    public function daysOfTheWeek() : array;
    
    // A multi-dimensional array with all display data for 1 week in the calendar
    public function calendarWeeks() : array;
    
    // A multi-dimensional array of event styles, see documentation
    public function eventStyles() : array;
}
