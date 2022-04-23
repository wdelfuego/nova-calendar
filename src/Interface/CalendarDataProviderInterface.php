<?php

namespace Wdelfuego\NovaCalendar\Interface;

interface CalendarDataProviderInterface
{
    public function __construct();
    
    public function title() : string;
    public function daysOfTheWeek() : array;
    public function calendarDays() : array;
}
