<?php

namespace Wdelfuego\NovaCalendar\Contracts;

interface CalendarDataProviderInterface
{
    public function __construct();
    
    public function title() : string;
    public function daysOfTheWeek() : array;
    public function calendarDays() : array;
    public function eventStyles() : array;
}
