<?php

namespace Wdelfuego\NovaCalendar\DataProvider;

interface DataProviderInterface
{
    public function __construct();
    
    public function title() : string;
    public function daysOfTheWeek() : array;
    public function days() : array;
}
