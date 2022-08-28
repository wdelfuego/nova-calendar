<?php

namespace Wdelfuego\NovaCalendar\Tests\Unit;

use Tests\TestCase;

use Illuminate\Support\Carbon;

use Wdelfuego\NovaCalendar\Event;

class EventTest extends TestCase
{
    public function testSingleDayEventTouchesRangeCorrectly()
    {
        $monthStart = Carbon::createSafe(2022, 1, 1, 0, 0, 0);
        $monthEnd = Carbon::createSafe(2022, 2, 1, 0, 0, 0)->subSeconds(1);

        // Around beginning of range
        $event = new Event('test', $monthStart->copy()->addSeconds(1));
        $this->assertTrue($event->touchesRange($monthStart, $monthEnd), 
            "Single day event that starts within range wrongly indicates it is not touching the range");

        $event = new Event('test', $monthStart->copy());
        $this->assertTrue($event->touchesRange($monthStart, $monthEnd), 
            "Single day event that starts exactly on range start wrongly indicates it is not touching the range");

        $event = new Event('test', $monthStart->copy()->addSeconds(-1));
        $this->assertFalse($event->touchesRange($monthStart, $monthEnd), 
            "Single day event that starts before range start wrongly indicates it is touching the range");

        // Around end of range
        $event = new Event('test', $monthEnd->copy()->addSeconds(1));
        $this->assertFalse($event->touchesRange($monthStart, $monthEnd), 
            "Single day event that starts after range end wrongly indicates it is touching the range");

        $event = new Event('test', $monthEnd->copy());
        $this->assertTrue($event->touchesRange($monthStart, $monthEnd), 
            "Single day event that starts exactly on range end wrongly indicates it is not touching the range");

        $event = new Event('test', $monthEnd->copy()->addSeconds(-1));
        $this->assertTrue($event->touchesRange($monthStart, $monthEnd), 
            "Single day event that starts within range wrongly indicates it is not touching the range");
    }
    
    public function testMultiDayEventTouchesRangeCorrectly()
    {
        $monthStart = Carbon::createSafe(2022, 1, 1, 0, 0, 0);
        $monthEnd = Carbon::createSafe(2022, 2, 1, 0, 0, 0)->subSeconds(1);

        // Should touch range
        $event = new Event('test', $monthStart->copy()->addSeconds(1), $monthEnd->copy()->addSeconds(-1));
        $this->assertTrue($event->touchesRange($monthStart, $monthEnd), 
            "Multi day event that falls completely within range wrongly indicates it is not touching the range");

        $event = new Event('test', $monthStart->copy()->addSeconds(-1), $monthEnd->copy()->addSeconds(-1));
        $this->assertTrue($event->touchesRange($monthStart, $monthEnd), 
            "Multi day event that ends within range wrongly indicates it is not touching the range");

        $event = new Event('test', $monthEnd->copy()->addSeconds(-1), $monthEnd->copy()->addSeconds(1));
        $this->assertTrue($event->touchesRange($monthStart, $monthEnd), 
            "Multi day event that starts within range wrongly indicates it is not touching the range");

        $event = new Event('test', $monthStart->copy(), $monthEnd->copy());
        $this->assertTrue($event->touchesRange($monthStart, $monthEnd), 
            "Multi day event that coincides exactly with range wrongly indicates it is not touching the range");

        $event = new Event('test', $monthStart->copy()->addSeconds(-1), $monthStart->copy());
        $this->assertTrue($event->touchesRange($monthStart, $monthEnd), 
            "Multi day event that ends exactly on range start wrongly indicates it is not touching the range");

        $event = new Event('test', $monthEnd->copy(), $monthStart->copy()->addSeconds(1));
        $this->assertTrue($event->touchesRange($monthStart, $monthEnd), 
            "Multi day event that starts exactly on range end wrongly indicates it is not touching the range");


        // Should not touch range
        $event = new Event('test', $monthStart->copy()->addSeconds(-10), $monthStart->copy()->addSeconds(-1));
        $this->assertFalse($event->touchesRange($monthStart, $monthEnd), 
            "Multi day event that falls completely before range wrongly indicates it is touching the range");

        $event = new Event('test', $monthEnd->copy()->addSeconds(1), $monthEnd->copy()->addSeconds(10));
        $this->assertFalse($event->touchesRange($monthStart, $monthEnd), 
            "Multi day event that falls completely after range wrongly indicates it is touching the range");
        
    }
}
