<?php

/*
 * © Copyright 2022 · Willem Vervuurt, Studio Delfuego
 * 
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, 
 * and/or sell copies of the Software, and to permit persons to whom the 
 * Software is furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included 
 * in all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL 
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN 
 * THE SOFTWARE.
 * 
 * YOU ASSUME ALL RISK ASSOCIATED WITH THE INSTALLATION AND USE OF THE SOFTWARE. 
 * LICENSE HOLDERS ARE SOLELY RESPONSIBLE FOR DETERMINING THE APPROPRIATENESS OF 
 * USE AND ASSUME ALL RISKS ASSOCIATED WITH ITS USE, INCLUDING BUT NOT LIMITED TO
 * THE RISKS OF PROGRAM ERRORS, DAMAGE TO EQUIPMENT, LOSS OF DATA OR SOFTWARE 
 * PROGRAMS, OR UNAVAILABILITY OR INTERRUPTION OF OPERATIONS.
 */
 
namespace Wdelfuego\NovaCalendar;

use Wdelfuego\NovaCalendar\NovaCalendar;
use Wdelfuego\NovaCalendar\Interface\CalendarDayInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CalendarDay implements CalendarDayInterface
{
    public static function forDateInYearAndMonth(Carbon $date, int $year, int $month, int $firstDayOfWeek = null) : self
    {
        $firstDayOfWeek = $firstDayOfWeek ?? NovaCalendar::MONDAY;

        return new self(
            self::weekdayColumn($date, $firstDayOfWeek),
            $date->format('j'),
            $date->year == $year && $date->month == $month,
            $date->isToday(),
            $date->isWeekend(),
        );
    }
    
    public static function weekdayColumn(Carbon $date, int $firstDayOfWeek = 1) : int
    {
        $absDay = $date->dayOfWeekIso;
        return ($absDay - $firstDayOfWeek) % 7 + 1;
    }
    
    protected $weekdayColumn;
    protected $label;
    protected $isWithinRange;
    protected $isToday;
    protected $isWeekend;
    protected $events;
    
    public function __construct(
        int $weekdayColumn,
        string $label = '',
        bool $isWithinRange = true,
        bool $isToday = false,
        bool $isWeekend = false,
        array $events = [], 
    )
    {
        $this->weekdayColumn = $weekdayColumn;
        $this->label = $label;
        $this->isWithinRange = $isWithinRange;
        $this->isToday = $isToday;
        $this->isWeekend = $isWeekend;
        $this->events = $events;
    }
    
    public function withEvents(array $events) : self
    {
        $this->events = $events;
        return $this;
    }
    
    public function toArray() : array
    {
        return [
            'weekdayColumn' => $this->weekdayColumn,
            'label' => $this->label,
            'isWithinRange' => $this->isWithinRange ? 1 : 0,
            'isToday' => $this->isToday ? 1 : 0,
            'isWeekend' => $this->isWeekend ? 1 : 0,
            'eventsSingleDay' => $this->eventsSingleDay(),
            'eventsMultiDay' => $this->eventsMultiDay(),
        ];
    }
    
    private function eventsSingleDay() : array
    {
        return array_filter($this->events, fn($e): bool => !!$e['single_day']);
    }
    
    private function eventsMultiDay() : array
    {
        
        return array_filter($this->events, fn($e): bool => !$e['single_day']);
    }
}
