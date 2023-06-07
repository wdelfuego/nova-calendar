
[⬅️ Back to Documentation overview](/nova-calendar)

---

#  Upgrading

This upgrade guide shows you how to upgrade from v1 (any release) to v2 of this package.

Follow these steps in order. 

If you encounter *any* issues or unclarities, [**please** open a GitHub issue](https://github.com/wdelfuego/nova-calendar/issues) and let me know so I can make the upgrade process go smoother for the next reader.


## 1. Create or update the config file

Config file `config/nova-calender.php` was optional in v1 but is required in v2, and its structure has changed to accomodate configuring multiple calendar instances.

Check if `config/nova-calender.php` already exists in your project.
* If it doesn't exist yet; publish it now by running the following command, then continue with step 2:

    ```sh
    php artisan vendor:publish --provider="Wdelfuego\NovaCalendar\ToolServiceProvider" --tag="config"
    ```

* If it does already exist, upgrade the structure as follows:
    1. If it contains an entry with key `title`, change that key to `windowTitle`.
    1. Add a key `dataProvider` and set it to the class of your `CalendarDataProvider`. 

       If you don't know where to find your `CalendarDataProvider`, it's the class that is currently being bound to `CalendarDataProviderInterface` in the `register` method of your `NovaServiceProvider`. 

       You can move the `use` statement from your `NovaServiceProvider` to the config file and set the `dataProvider` entry to `CalendarDataProvider::class`.

    1. Finally, to accomodate multiple calendar instances, the existing top-level config now turns into a specific calendar config; wrap another array around the current contents of the config file and use a string key as a uniquely identifying *calendar key*, to point to this config. 

        If you're not going to add more calendars, `calendar` will do just fine as a calendar key. The calendar key is for internal use and is never exposed to the end user.

    1. You should end up with a `config/nova-calendar.php` file that looks something like this:

        ```php
        use App\Providers\CalendarDataProvider;

        return [
            // This key `calendar` is the 'calendar key'. You can use whatever.
            'calendar' => [
                'dataProvider' => CalendarDataProvider::class,
                'uri' => 'calendar/my-calendar',
                'windowTitle' => 'My calendar'
            ]
        ];
        ```
## 2. Update the `NovaServiceProvider`
1. Get rid of the binding between `CalendarDataProviderInterface` and your `CalendarDataProvider` in the `register` method of your `NovaServiceProvider`:

    ```php
    // Delete this from NovaServiceProvider::register
    $this->app->bind(CalendarDataProviderInterface::class, function($app) {
        return new CalendarDataProvider();
    });
    ```

1. In the `tools` method of your `NovaServiceProvider`, where you create a `new NovaCalendar`, supply the calendar key you used in the config file to the constructor (in this example: `calendar`).

    ```php
    public function tools()
    {
        return [
           // Supply the calendar key to the tool constructor
           new NovaCalendar('calendar'),
        ];
    }
    ```

1. If you are creating your own menu items in the `boot` method of your `NovaServiceProvider`, update the path that is currently either hardcoded or a call to `config('nova-calendar.uri')` to use the `NovaCalendar::pathToCalendar` helper instead, like so:

    ```php
    // Supply the calendar key to the pathToCalendar helper
    MenuItem::link(__('Calendar'), NovaCalendar::pathToCalendar('calendar')),
    ```

## 3. Update your calendar data provider
To make your calendar data provider compatible with v2, perform the following changes:
1. The class used to extend `Wdelfuego\NovaCalendar\DataProvider\MonthCalendar`, change that to `Wdelfuego\NovaCalendar\DataProvider\AbstractCalendarDataProvider`
1. If your calendar data provider implements the `customizeCalendarDay` method, that must now be a `public` method
1. Methods `firstDayOfCalendar` and `lastDayOfCalendar` have been removed; update any calls to them to `startOfCalendar` and `endOfCalendar`, respectively.

    Search your project for the removed method names to see if you depend on them anywhere else.
1. If your calendar data provider implements the `exclude` method, rename it to `excludeResource`.

1. Finally, method visibility of several methods have changed. Check that they match:



## 4. Update your custom event generators

- Update your Custom Event Generators: resourceToEvents method signature changed from

  abstract protected function resourceToEvents(NovaResource $resource) : array;

  to

  abstract protected function resourceToEvents(NovaResource $resource, Carbon $startOfCalendar, Carbon $endOfCalendar) : array;


## 5. Other low-impact changes

- Low-impact changes:
  - Namespace change Interface > Contracts
  - Wdelfuego\NovaCalendar\EventGenerator\EventGenerator renamed to Wdelfuego\NovaCalendar\EventGenerator\NovaEventGenerator
  - CalendarDataProvider->calendarWeeks renamed to CalendarDataProvider->calendarData
  
  
## 6. Do the update
THEN update composer.json and run composer update

# Adding extra calendar views

To add more calendars;
- Add an entry to config/nova-calendar.php using a different calendar key
- Add the second calendar to the tools() method in NovaServiceProvider, using the new calendar key
- Add a menu entry 
- Implement a CalendarDataProvider for the new calendar, consider subclassing an existing one to avoid code repetition for your application.
  You might want to implement a generic SharedCalendarDataProvider and have the providers for specific calendars subclass that,
  so that you have some shared code between all of your app's calendars.  

---

[⬅️ Back to Documentation overview](/nova-calendar)
