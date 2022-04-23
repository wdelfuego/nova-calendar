<?php

namespace Wdelfuego\NovaCalendar\DataProvider;

interface MonthDataProviderInterface extends DataProviderInterface
{
    public function __construct(int $year = null, int $month = null);
}
