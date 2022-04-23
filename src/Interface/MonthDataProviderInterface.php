<?php

namespace Wdelfuego\NovaCalendar\Interface;

interface MonthDataProviderInterface extends CalendarDataProviderInterface
{
    public function __construct(int $year = null, int $month = null);
}
