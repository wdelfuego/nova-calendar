- Using the config file is now a requirement; if you don't have one yet, publish it (TODO test)

- Open config file config/nova-calendar.php and your NovaServiceProvider
  - wrap config in array using string keys; if you're got going to add more calendars, 'calendar' is fine. This is the 'calendar key'.
  - add DataProvider under key 'provider'; this is the class currently in the binding in NovaServiceProvider::register()

- Then update NovaServiceProvider:
  - Remove that CalendarDataProviderInterface::class binding in register()
  - Pass the string key used in the config file as argument to the NovaCalendar constructor in tools()
  - If generating own menu items, update the path to come from NovaCalendar based on the calendar key:
      MenuItem::link(__('Calendar'), NovaCalendar::pathToCalendar('flights')),
  
- Update your CalendarDataProvider; methods deprecated in 1.0, removed in 2.0:
  - Removed firstDayOfCalendar and lastDayOfCalendar from calendar data providers, use startOfCalendar and endOfCalendar instead
  - Removed exclude from calendar data providers, use excludeResource instead

- Update your Custom Event Generators: resourceToEvents method signature changed from

  abstract protected function resourceToEvents(NovaResource $resource) : array;

  to

  abstract protected function resourceToEvents(NovaResource $resource, Carbon $startOfCalendar, Carbon $endOfCalendar) : array;

- Low-impact changes:
  - Namespace change Interface > Contracts
  - Wdelfuego\NovaCalendar\EventGenerator\EventGenerator renamed to Wdelfuego\NovaCalendar\EventGenerator\NovaEventGenerator
  - CalendarDataProvider->calendarWeeks renamed to CalendarDataProvider->calendarData
  
THEN update composer.json and run composer update


To add more calendars;
- Add an entry to config/nova-calendar.php using a different calendar key
- Add the second calendar to the tools() method in NovaServiceProvider, using the new calendar key
- Add a menu entry 