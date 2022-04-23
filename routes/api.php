<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Wdelfuego\NovaCalendar\Http\Controllers\CalendarController;

Route::get('/calendar-data/{year?}/{month?}', [CalendarController::class, 'getMonthCalendarData']);
