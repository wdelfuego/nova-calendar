[⬅️ Back to Documentation overview](/nova-calendar)

---

  
# Adding more calendar views

Starting with version 2.0, the amount of different calendar views you can add to your application is unlimited.

Each calendar view gets its own entry in the config file and its own calendar data provider.

For every calendar view you want to add to your application;
1. Add an entry to the array in `config/nova-calendar.php` using a new calendar key, a unique url and a new calendar data provider class
1. Add an extra instance of the `NovaCalendar` tool to the `tools()` method in your `NovaServiceProvider`, supplying the new calendar key to its constructor
1. If you create your own menu; add a menu entry in the `boot()` method of your `NovaServiceProvider`, supplying the new calendar key to the path helper
1. Implement the new calendar data provider alongside any existing ones

### Implementing multiple calendar data providers
* You can consider subclassing an existing calendar data provider to avoid code repetition.

* Depending on the types of calendars you want to display, you might want to implement a base class, eg. `SharedCalendarDataProvider`, and have data providers for specific calendars subclass that, so that you have a place for shared code between multiple calendars. That would allow you to, for example, specify event styles only in the shared superclass, thereby guaranteeing consisting event styling throughout your application.


---

[⬅️ Back to Documentation overview](/nova-calendar)