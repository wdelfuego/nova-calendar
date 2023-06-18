<?php

/*
 * © Copyright 2022 · Willem Vervuurt, Studio Delfuego
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
 
namespace Wdelfuego\NovaCalendar\Contracts;

interface CalendarDataProviderInterface
{
    public function __construct();

    // Will be used for the browser window/tab title
    public function windowTitle() : string;
   
    // Will be displayed above the calendar
    public function titleForView(string $viewSpecifier) : string;

    // Trigers Week numbers to be displayed
    public function shouldShowWeekNumbers(): bool;

    // A 1D array of calendar views to be rendered
    public function calendarViews(): array;

    // A 1D array with the names of the seven days of the week, in order of display L -> R
    public function daysOfTheWeek() : array;
    
    // A multi-dimensional array of event styles, see documentation
    public function eventStyles() : array;
}
