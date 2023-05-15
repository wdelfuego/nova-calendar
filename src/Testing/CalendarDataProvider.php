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
 
namespace Wdelfuego\NovaCalendar\Testing;

use Wdelfuego\NovaCalendar\DataProvider\MonthCalendar;
use Wdelfuego\NovaCalendar\Event;
use Wdelfuego\NovaCalendar\Tests\Unit\CalendarDataProviderTest;

use Illuminate\Support\Carbon;

class CalendarDataProvider extends MonthCalendar
{
    public function novaResources()
    {
        return [];
    }
    
    public function appendEvent(Event $event) 
    {
        $reflection = new \ReflectionProperty(MonthCalendar::class, 'allEvents');
        $reflection->setAccessible(true);
        $allEvents = $reflection->getValue($this);
        if(!is_array($allEvents)) {
            $allEvents = [];
        }
        $allEvents[] = $event;
        $reflection->setValue($this, $allEvents);
    }
    
    public function initialize(): void
    {
        parent::initialize();
        
        // See CalendarDataProviderTest::testMultiDayEventThatEndsOnDayZeroGitHubIssue56
        $this->appendEvent(new Event(
            CalendarDataProviderTest::GITHUB_ISSUE_56_EVENT_TITLE,
            Carbon::create(2023, 5, 10, 6, 0, 0), // start
            Carbon::create(2023, 5, 15, 0, 0, 0),  // end
        ));
    }
}
