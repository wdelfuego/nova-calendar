<?php

namespace Wdelfuego\NovaCalendar\Contracts;

interface MonthDataProviderInterface extends CalendarDataProviderInterface
{
    public function __construct(int $year = null, int $month = null);
}
