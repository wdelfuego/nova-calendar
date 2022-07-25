<?php

/*
 * © Copyright 2022 · Willem Vervuurt, Studio Delfuego, Bartosz Bujak
 * 
 * You can modify, use and distribute this package under one of two licenses:
 * 1. GNU AGPLv3
 * 2. A perpetual, non-revocable and 100% free (as in beer) do-what-you-want 
 *    license that allows both non-commercial and commercial use, under conditions.
 *    See LICENSE.md for details.
 * 
 *    (it boils down to: do what you want as long as you're building and/or
 *     using calendar views, but don't embed this package or a modified version
 *     of it in free or paid-for software libraries and packages aimed at developers).
 */

namespace Wdelfuego\NovaCalendar\DataProvider;

use Illuminate\Support\Carbon;
use Wdelfuego\NovaCalendar\NovaCalendar;
use Wdelfuego\NovaCalendar\Interface\MonthDataProviderInterface;

/* for backward compatibility purposes */

abstract class MonthCalendar extends Calendar implements MonthDataProviderInterface
{
    public function __construct(int $year = null, int $month = null)
    {
        parent::__construct();
        info('WARNING: change parent of your Calendar Data Provider class to Wdelfuego\NovaCalendar\DataProvider\Calendar instead of Wdelfuego\NovaCalendar\DataProvider\MonthCalendar');
    }
}
