<?php

namespace Wdelfuego\NovaCalendar\Tests\Unit;

use Tests\TestCase;

use Illuminate\Support\Carbon;
use Wdelfuego\NovaCalendar\NovaCalendar;
use Wdelfuego\NovaCalendar\DataProvider\MonthCalendar;
use Wdelfuego\NovaCalendar\Testing\CalendarDataProvider;

class CalendarDataProviderTest extends TestCase
{
    const GITHUB_ISSUE_56_EVENT_TITLE = "testMultiDayEventThatEndsOnDayZeroGitHubIssue56";
    
    public function testMultiDayEventThatEndsOnDayZeroGitHubIssue56()
    {
        // Set up the calendar for may '23 and let weeks start on monday
        $dp = new CalendarDataProvider(2023, 5);
        $dp->setYearAndMonth(2023, 5);
        $dp->startWeekOn(NovaCalendar::MONDAY);
        
        // Get event data for the 15th which is a monday
        // The testing calendardataprovider is set up to include a multi-day event that ends that day at 00:00:00
        $reflection = new \ReflectionMethod($dp, 'eventDataForDate');
        $reflection->setAccessible(true);
        $result = $reflection->invoke($dp, Carbon::create(2023,5,15, 12, 0, 0));
        
        // Check if the eventData for that date contains the test event, as it should
        $foundTestEvent = false;
        foreach($result as $eventData)
        {
            $foundTestEvent = $foundTestEvent || $eventData['name'] == self::GITHUB_ISSUE_56_EVENT_TITLE;
        }
        
        $this->assertTrue($foundTestEvent, "Multi-day test event not returned for its last day");
    }
    
    /**
     * @dataProvider provideStartOfCalendarData
     */
    public function testStartOfCalendar(int $year, int $month, int $firstDayOfWeek, string $expectedDate)
    {
        $dp = new CalendarDataProvider($year, $month);
        $dp->startWeekOn($firstDayOfWeek);
        $calendarStartsOn = $dp->startOfCalendar()->format('Y-m-d');
        $calendarStartsOnTime = $dp->startOfCalendar()->format('H:i');
        $expectedTime = '00:00';
        $this->assertEquals($expectedDate, $calendarStartsOn, "Calendar starts on $calendarStartsOn instead of on expected date $expectedDate for month $year-$month when the week starts on day $firstDayOfWeek (1 = Monday, 7 = Sunday)");
        $this->assertEquals($expectedTime, $calendarStartsOnTime, "Calendar starts at $calendarStartsOnTime instead of at expected time $expectedTime for month $year-$month when the week starts on day $firstDayOfWeek (1 = Monday, 7 = Sunday)");
    }
    
    /**
     * @dataProvider provideStartOfCalendarData
     */
    public function testEndOfCalendar(int $year, int $month, int $firstDayOfWeek, string $expectedDate)
    {
        $dp = new CalendarDataProvider($year, $month);
        $dp->startWeekOn($firstDayOfWeek);
        $calendarEndsOn = $dp->endOfCalendar()->format('Y-m-d');
        $calendarEndsOnTime = $dp->endOfCalendar()->format('H:i');
        $expectedDate = Carbon::createFromFormat('Y-m-d', $expectedDate)->addWeeks(MonthCalendar::N_CALENDAR_WEEKS)->subSeconds(1)->format('Y-m-d');
        $expectedTime = Carbon::createFromFormat('Y-m-d H:i', "$expectedDate 00:00")->addWeeks(MonthCalendar::N_CALENDAR_WEEKS)->subSeconds(1)->format('H:i');
        $this->assertEquals($expectedDate, $calendarEndsOn, "Calendar ends on $calendarEndsOn instead of on expected date $expectedDate for month $year-$month when the week starts on day $firstDayOfWeek (1 = Monday, 7 = Sunday)");
        $this->assertEquals($expectedTime, $calendarEndsOnTime, "Calendar ends at $calendarEndsOnTime instead of on expected time $expectedTime for month $year-$month when the week starts on day $firstDayOfWeek (1 = Monday, 7 = Sunday)");
    }
    
    public function provideStartOfCalendarData()
    {
        return [
            [2022, 5, NovaCalendar::SUNDAY,    '2022-05-01'],
            [2022, 7, NovaCalendar::MONDAY,    '2022-06-27'],
            [2022, 7, NovaCalendar::TUESDAY,   '2022-06-28'],
            [2022, 7, NovaCalendar::WEDNESDAY, '2022-06-29'],
            [2022, 7, NovaCalendar::THURSDAY,  '2022-06-30'],
            [2022, 7, NovaCalendar::FRIDAY,    '2022-07-01'],
            [2022, 7, NovaCalendar::SATURDAY,  '2022-06-25'],
            [2022, 7, NovaCalendar::SUNDAY,    '2022-06-26'],
            [2022, 8, NovaCalendar::MONDAY,    '2022-08-01']
        ];
    }
    
    /**
     * @dataProvider provideDayNumbers
     */
    public function testDaysOfTheWeek(int $firstDayOfWeek)
    {
        $dp = new CalendarDataProvider(2022, 6);
        $dp->startWeekOn($firstDayOfWeek);
        $days = $dp->daysOfTheWeek();

        $expected = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        for($i = 1; $i < $firstDayOfWeek; $i++)
        {
            array_push($expected, array_shift($expected));
        }
        
        $this->assertEquals(count($days), count($expected), "Unexpected amount of days returned: " .count($days) ." instead of " .count($expected));
        for($i = 0; $i < count($expected); $i++)
        {
            $this->assertEquals($expected[$i], $days[$i], "Day " .$i+1 .": wrong day name (" .$days[$i] ." instead of " .$expected[$i] .")");
        }
    }
    
    public function provideDayNumbers()
    {
        return [[NovaCalendar::MONDAY],
                [NovaCalendar::TUESDAY],
                [NovaCalendar::WEDNESDAY],
                [NovaCalendar::THURSDAY],
                [NovaCalendar::FRIDAY],
                [NovaCalendar::SATURDAY],
                [NovaCalendar::SUNDAY]];
    }
}
