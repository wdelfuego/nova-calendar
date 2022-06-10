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
 
namespace Wdelfuego\NovaCalendar\DataProvider;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource as NovaResource;

use Wdelfuego\Nova\DateTime\Filters\NotBeforeDate;
use Wdelfuego\Nova\DateTime\Filters\NotAfterDate;
use Wdelfuego\NovaCalendar\Interface\MonthDataProviderInterface;

use Wdelfuego\NovaCalendar\NovaCalendar;
use Wdelfuego\NovaCalendar\CalendarDay;
use Wdelfuego\NovaCalendar\Event;

abstract class MonthCalendar implements MonthDataProviderInterface
{
    const N_CALENDAR_WEEKS = 6;
    
    protected $firstDayOfWeek;
    protected $year;
    protected $month;
    
    private $allEvents = null;
    
    public function __construct(int $year = null, int $month = null)
    {
        $this->firstDayOfWeek = NovaCalendar::MONDAY;
        $this->year = $year ?? now()->year;
        $this->month = $month ?? now()->month;
    }
    
    abstract public function novaResources();

    public function setYearAndMonth(int $year, int $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function startWeekOn(int $dayOfWeekIso)
    {
        $this->firstDayOfWeek = min(NovaCalendar::SUNDAY, max($dayOfWeekIso, NovaCalendar::MONDAY));
    }

    public function startWeekOnSunday()
    {
        $this->startWeekOn(NovaCalendar::SUNDAY);
    }

    public function title() : string
    {
        return ucfirst($this->firstDayOfMonth()->translatedFormat('F \'y'));
    }

    public function daysOfTheWeek() : array
    {
        $out = [];
        $currentDay = new Carbon(Carbon::getDays()[$this->firstDayOfWeek % 7]);
        for($i = 0; $i < 7; $i++)
        {
            $out[] = $currentDay->dayName;
            $currentDay = $currentDay->addDay();
        }
        return $out;
    }

    public function calendarWeeks() : array
    {
        $out = [];
        $dateCursor = $this->firstDayOfCalendar();

        for($i = 0; $i < self::N_CALENDAR_WEEKS; $i++)
        {
            $week = [];
            for($j = 0; $j < 7; $j++)
            {
                $calendarDay = CalendarDay::forDateInYearAndMonth($dateCursor, $this->year, $this->month, $this->firstDayOfWeek);
                $week[] = $calendarDay->withEvents($this->eventDataForDate($dateCursor))->toArray();
                
                $dateCursor = $dateCursor->addDay();
            }
            $out[] = $week;
        }
        
        return $out;
    }
    
    public function eventStyles() : array
    {
        return [];
    }
    
    protected function customizeEvent(Event $event) : Event
    {
        return $event;
    }
    
    protected function nonNovaEvents() : array
    {
        return [];
    }
    
    protected function urlForResource(NovaResource $resource)
    {
        return '/resources/' .$resource::uriKey() .'/' .$resource->id;
    }
    
    private function firstDayOfMonth() : Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $this->year.'-'.$this->month.'-1');
    }

    protected function firstDayOfCalendar(): Carbon
    {
        $firstOfMonth = $this->firstDayOfMonth();
        return $firstOfMonth->subDays($firstOfMonth->dayOfWeekIso - $this->firstDayOfWeek);
    }
    
    protected function lastDayOfCalendar(): Carbon
    {
        return $this->firstDayOfCalendar()->addDays(7 * self::N_CALENDAR_WEEKS);
    }

    private function eventDataForDate(Carbon $date) : array
    {
        $date->setTime(0,0,0);
        
        // Get events that start today, and if the date is the first day of the week
        // also get all multiday events that started before today and end on or after it
        // ('running multiday events')
        $events = array_filter($this->allEvents(), function($e) use ($date) {
            return $e->start()->isSameDay($date)
                    ||
                    (($date->dayOfWeekIso == $this->firstDayOfWeek)
                        && $e->end() 
                        && $e->start()->isBefore($date) 
                        && $e->end()->isAfter($date));
        });

        // Sort by event start date as a heuristic (CSS doesn't always match event order perfectly due to 'column dense')
        usort($events, function($a, $b) use ($date) { 

            $aDays = min(7,$a->spansDaysFrom($date));
            $bDays = min(7,$b->spansDaysFrom($date));

            // Longer events first
            if($a != $b)
            {
                // return $bDays - $aDays;
            }
            
            // Events span the same amount of days
            
            // So both are 7 here
            if($aDays == 7 && $bDays == 7)
            {
                if(!$a->startsEvent($date)) { return -1 ;}
                if(!$b->startsEvent($date)) { return 1 ;}
                return 0;
            }

            // By start time
            return $b->start()->diffInMinutes($a->start(), false); 
        });

        // At least, always move events that span the full week to the top
        // This works because usort re-assigns numeric keys
        // foreach($events as $index => $event) {
        //     if($event->spansDaysFrom($date) >= 7) {
        //         array_unshift($events, array_splice($events, $index, 1)[0]);
        //     }
        // }


        return array_map(fn($e): array => $e->toArray($date, $this->firstDayOfWeek), $events);
    }
    
    private function resourceToEvent(NovaResource $resource, string $dateAttribute) : Event
    {
        $out = Event::fromResource($resource, $dateAttribute);
        $out->url($this->urlForResource($resource));
        return $this->customizeEvent($out);
    }
    
    private function allEvents() : array
    {
        if(is_null($this->allEvents))
        {
            $this->allEvents = [];
            $firstDayOfCalendar = $this->firstDayOfCalendar();
            $lastDayOfCalendar = $this->lastDayOfCalendar();
        
            foreach($this->novaResources() as $novaResourceClass => $dateAttribute)
            {
                if(!is_subclass_of($novaResourceClass, NovaResource::class))
                {
                    throw new \Exception("Only Nova Resources can be automatically fetched for event generation ($novaResourceClass is not a Nova Resource)");
                }
            
                $notBefore = new NotBeforeDate('', $dateAttribute);
                $notAfter = new NotAfterDate('', $dateAttribute);
            
                $eloquentModelClass = $novaResourceClass::$model;
                $models = $eloquentModelClass::orderBy($dateAttribute);
                $models = $notBefore->modulateQuery($models, $firstDayOfCalendar);
                $models = $notAfter->modulateQuery($models, $lastDayOfCalendar);

                foreach($models->cursor() as $model)
                {
                    $this->allEvents[] = $this->resourceToEvent(new $novaResourceClass($model), $dateAttribute);
                }
            }
            
            $this->allEvents = array_merge($this->allEvents, $this->nonNovaEvents());
        }
        
        return $this->allEvents;
    }
}
