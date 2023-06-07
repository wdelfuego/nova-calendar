[⬅️ Back to Documentation overview](/nova-calendar)

---

#  Installation

Installing this package will not add any Eloquent models, Nova resources or database migrations to your project.

Rather, it will add a configurable, plug-and-play [Nova Tool](https://nova.laravel.com/docs/4.0/customization/tools.html) that you can use to present existing model data to the end user on a powerful calendar view.

## Requirements

This package requires:

- [Laravel Nova](https://nova.laravel.com) 4.2.4 or newer
- [PHP](https://www.php.net) 7.4 or newer


1. If your installation meets the requirements, start by requiring the package:

    ```sh
    composer require wdelfuego/nova-calendar
    ```


2. Publish this project's config file by running the following command:

    ```sh
    php artisan vendor:publish --provider="Wdelfuego\NovaCalendar\ToolServiceProvider" --tag="config"
    ```

	The published config file `config/nova-calendar.php` contains a working starting config.

	Two important config values for every calendar view are:
	- the *calendar key*, which is the string array key in the config file (`my-calendar`)
	- *dataProvider*, which is the class that will generate the calendar event data (`App\Providers\CalendarDataProvider`)

	You are free to change both to your liking, but it is not required.

    Read the comments in the published config file or [look at the example config](#config-file-structure) at the end of this page to learn more.


## Adding your first calendar to Nova

You only need to create a single calendar data provider class to get a working calendar in your Nova app. 

1. Create the data provider class:

	The default starting config assumes `App\Providers\CalendarDataProvider`; you can run the following artisan command to create it for you.

    ```sh
    php artisan nova-calendar:create-default-calendar-data-provider
    ```

    If you choose to make the data provider yourself, be sure to make it a subclass of `Wdelfuego\NovaCalendar\DataProvider\AbstractDataProvider`.

	An absolutely minimal data provider implementation that generates an empty calendar looks like this:

	```php
    namespace App\Providers;

    use Wdelfuego\NovaCalendar\DataProvider\AbstractDataProvider;

    class CalendarDataProvider extends AbstractDataProvider
    {
        public function novaResources() : array
        {
            return [];
        }
    }

2. Edit your `NovaServiceProvider` at `app/NovaServiceProvider.php` to add the calendar Tool to its `tools()` method.

    Supply the calendar key to the constructor:

    ```php
    use Wdelfuego\NovaCalendar\NovaCalendar;

    public function tools()
    {
        return [
           new NovaCalendar('my-calendar'),
        ];
    }

    ```

3. In the data provider created under step 1, implement the `novaResources()` method to specify which Nova resources are to be included and which of their model's `DateTime` attributes define when the event starts and, optionally, when it ends. 

	This `novaResources()` method **must** return an array that maps Nova resource class names to either:
	- a single attribute name, for single day events that only have a start timestamp, or
	- an array with two attribute names, for events that have a start and possibly an end timestamp (which may be _nullable_, events with a _null_ end timestamp will be treated as single day events), or
	- for advanced usage, a custom event generator.
	
    For example, let's say you: 
	- want to show all Nova users as single-day events on the date their accounts were created, and
	- want to show a SomeEvent resource that has both `starts_at` and `ends_at` timestamps in its underlying Eloquent model

	You would implement your `CalendarDataProvider` as follows:

    ```php
    namespace App\Providers;

    use Wdelfuego\NovaCalendar\DataProvider\MonthCalendar;
    use App\Nova\User;
    use App\Nova\SomeEvent;

    class CalendarDataProvider extends MonthCalendar
    {
        public function novaResources() : array
        {
            return [
                User::class => 'created_at',
                SomeEvent::class => ['starts_at', 'ends_at']
            ];
        }	
    }
    ```

    **Note**: any attributes specified here must be cast to a `DateTime` object by the underlying Eloquent model. 

    This is the only method that is required in your calendar data provider. If you return an empty array, the calendar will work but will not contain any events. You can add more Nova resources to the calendar by simply adding more class names and attributes to the array returned by `novaResources()`.

    For more complex scenarios like generating multiple calendar events from a single Nova resource instance, you can implement your own mapping from resource to calendar event(s) using [custom event generators](/nova-calendar/custom-event-generators.html).

4. If you're using Nova's default main menu, you're already done :). 

    If you've defined your main menu manually in the `boot()` method of your `NovaServiceProvider`, don't forget to add a `MenuSection` or `MenuLink` that links to the calendar, passing the calendar key to `NovaCalendar::pathToCalendar`:

    ```php
	use Wdelfuego\NovaCalendar\NovaCalendar;
    ```
    ```php
    MenuItem::link(__('Calendar'), NovaCalendar::pathToCalendar('my-calendar'))
    ````

That's it! Your calendar should now be up and running. 

[Go back to the documentation overview](/nova-calendar) to learn how you can customize the calendar, the events on it, their CSS styles and much more.


## Config file structure
Most calendar configuration is done at runtime directly from your `CalendarDataProvider`, but some basic configuration needs to be done through the config file.

Every calendar instance in your Nova app gets an entry in this config file under its own calendar key, the string keys at the top level in the config array.
The calendar keys must be unique, but are never exposed to the end user, so you can use whatever.

Every calendar entry in the config file contains an array which may contain the following options:

- `dataProvider` - The class of the data provider that will supply the event data for this calendar.
    
    You can specify the full class name as a string or, preferably, put a `use` statement at the beginning of the file and the `::class` accessor. See the [example config file](#example-config-file) below.
 
- `uri` - The URI under which the calendar is available to your users. 

    For example, if you set the value to `calendar/flights` and your Nova installation is available on `domain.com/nova`, the calendar tool will be available on `domain.com/nova/calendar/flights`. 

	Each calendar entry in the config file must get a unique `uri`.


- `windowTitle` - A fixed browser window/tab title for this calendar's page.

    This value is optional; if left unspecified or empty, the dynamic calendar title generated by your calendar data provider's `titleForView` method will be used.


### Example config file

In a Nova app with three calendar views, the config file could look like this:

```php
use App\Calendar\Providers\FlightDataProvider;
use App\Calendar\Providers\BirthdayDataProvider;
use App\Calendar\Providers\HolidayDataProvider;

return [
    'flights' => [
        'dataProvider' => FlightDataProvider::class,
        'uri' => 'calendar/flights',
    ],
    'birthdays' => [
        'dataProvider' => BirthdayDataProvider::class,
        'uri' => 'calendar/birthdays',
        'windowTitle' => 'Birthdays'
    ],
    'holidays' => [
        'dataProvider' => HolidayDataProvider::class,
        'uri' => 'calendar/holidays',
        'windowTitle' => 'Holidays'
    ]
];
````

---

[⬅️ Back to Documentation overview](/nova-calendar)