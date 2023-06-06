- Using the config file is now a requirement; if you don't have one yet, publish it first using ....

- Open config file config/nova-calendar.php and your NovaServiceProvider
  - wrap config in array using string keys; these are called 'calendar keys' from now on. 
    If you're not going to add more calendars, 'calendar' is fine. (Value is for internal use and not exposed to the user)
  - rename key 'title' to 'windowTitle' (if null, the calendar title will be used dynamically as the windowTitle)
  - add DataProvider under key 'dataProvider'; this is the class currently in the binding in NovaServiceProvider::register()
    (one can simply move the use statement to the config file and set it to CalendarDataProvider::class - rename later)

- Then update NovaServiceProvider:
  - Remove that CalendarDataProviderInterface::class binding in register()
  - Pass the string key used in the config file as argument to the NovaCalendar constructor in tools()
  - If generating own menu items, update the path to come from NovaCalendar based on the calendar key:
      MenuItem::link(__('Calendar'), NovaCalendar::pathToCalendar('flights')),
  
- Update your CalendarDataProvider; methods deprecated in 1.0, removed in 2.0:
  - No longer extends MonthCalendar but AbstractCalendarDataProvider
  - customizeCalendarDay must now be a public method
  - Removed firstDayOfCalendar and lastDayOfCalendar from calendar data providers, use startOfCalendar and endOfCalendar instead
  - Removed exclude from calendar data providers, use excludeResource instead
  - Low-impact: visibility of many methods in CalendarDataProvider changed; this probably only affects you if you did low-level customizing of the CalendarDataProvider.

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
- Implement a CalendarDataProvider for the new calendar, consider subclassing an existing one to avoid code repetition for your application.
  You might want to implement a generic SharedCalendarDataProvider and have the providers for specific calendars subclass that,
  so that you have some shared code between all of your app's calendars.  