- Updated config file structure (multi calendar)

- Custom Event Generators: resourceToEvents method signature changed from

  abstract protected function resourceToEvents(NovaResource $resource) : array;

  to

  abstract protected function resourceToEvents(NovaResource $resource, Carbon $startOfCalendar, Carbon $endOfCalendar) : array;

- Namespace change Interface > Contracts

- Changes in NovaServiceProvider:
  - Remove binding in register()

- Removed firstDayOfCalendar and lastDayOfCalendar from calendar data providers, use startOfCalendar and endOfCalendar instead
- Removed exclude from calendar data providers, use excludeResource instead

- Low-impact changes:
  - Wdelfuego\NovaCalendar\EventGenerator\EventGenerator renamed to Wdelfuego\NovaCalendar\EventGenerator\NovaEventGenerator
  - CalendarDataProvider->calendarWeeks renamed to CalendarDataProvider->calendarData