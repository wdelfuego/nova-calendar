[⬅️ Back to Documentation overview](/nova-calendar)

---

# Usage

## Navigating the calendar
End users can navigate through the months using the arrow links above the calendar or using hotkeys `Alt` + `←` / `Alt` + `→`.
To jump back to the current month, use `Alt` + `H` or click the month name that's displayed above the calendar.

## Clicking events
When users click events generated from Nova resources, they are sent to the URL returned by the `urlForResource` method in your CalendarDataProvider.
When that method returns `null` for a resource, the event is simply not clickable.

When a clickable event is clicked while holding the Control/Windows key (Windows) or the Command key (Mac), the url is opened in a new browser window.