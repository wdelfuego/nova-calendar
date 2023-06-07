| :exclamation:  Warning: this documentation is for version 1.x of the package.   |
|---------------------------------------------------------------------------------|

[⬅️ Back to Documentation overview](/nova-calendar/v1)

---

#  Installation

## Requirements

This package requires:

- [Laravel Nova](https://nova.laravel.com) 4.2.4 or newer
- [PHP](https://www.php.net) 8.1 or newer

If your installation meets the requirements, start by requiring the package:

```sh
composer require wdelfuego/nova-calendar
```

## Adding the calendar to Nova
The calendar just needs a single data provider class that supplies event data to the frontend, and for the data provider and tool to be added to your `NovaServiceProvider`:

1. Create a data provider class with a name of your choice anywhere you like in your project, or run the following artisan command to create the default data provider:

    ```sh
    php artisan nova-calendar:create-default-calendar-data-provider
    ```

    If you choose to make the data provider yourself, make it a subclass of `Wdelfuego\NovaCalendar\DataProvider\MonthCalendar`.

2. Edit your `NovaServiceProvider` at `app/NovaServiceProvider.php` to add the calendar to its `tools()` method and to register your data provider class as the default calendar data provider:

    ```php
    use Wdelfuego\NovaCalendar\NovaCalendar;
    use Wdelfuego\NovaCalendar\Contracts\CalendarDataProviderInterface;
    use App\Providers\CalendarDataProvider;

    public function tools()
    {
        return [
           new NovaCalendar,
        ];
    }

    public function register()
    {
        $this->app->bind(CalendarDataProviderInterface::class, function($app) {
            return new CalendarDataProvider();
        });
    }
    ```

3. In your data provider, implement the `novaResources()` method to specify which Nova resources are to be included and which of their model's `DateTime` attributes define when the event starts and, optionally, when it ends. 

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

	The `novaResources()` method must return an array that maps Nova resource class names to either:
	- a single attribute name, for single day events that only have a start timestamp, or
	- an array with two attribute names, for events that have a start and possibly an end timestamp (which may be _nullable_, events with a _null_ end timestamp will be treated as single day events), or
	- for advanced usage, a custom event generator.
	
    Any attributes specified here must be cast to a `DateTime` object by the underlying Eloquent model. You can add more Nova resources to the calendar by simply adding more class names and attributes to the array returned by `novaResources()`.

    This is the only method that is required in your calendar data provider. If you return an empty array, the calendar will work but will not contain any events. For more complex scenarios like generating multiple calendar events from a single Nova resource instance, you can implement your own mapping from resource to calendar event(s) using [custom event generators](/nova-calendar/v1/custom-event-generators.html).

4. If you're using Nova's default main menu, you're already done. 

    If you've defined your main menu manually in the `boot()` method of your `NovaServiceProvider`, don't forget to add a `MenuSection` that links to the calendar:

    ```php
    MenuSection::make('Calendar')
        ->path(config('nova-calendar.uri', 'wdelfuego/nova-calendar'))
        ->icon('calendar'),
    ````

That's it! Your calendar should now be up and running.


## Publishing the config file
Most calendar configuration is done at runtime directly from your `CalendarDataProvider`.

Some options need to be configured through a config file, such as the URI under which the tool is exposed to end users.

To publish the config file to your project, run the following command:
```sh
php artisan vendor:publish --provider="Wdelfuego\NovaCalendar\ToolServiceProvider" --tag="config"
```

The following options exist:
- `uri` - The URI under which the calendar is available to your users. 

    For example, if you set this option to `calendar` and your Nova installation is available on `domain.com/nova`, the calendar will be available on `domain.com/nova/calendar`.

    If you change the URI in an existing installation that doesn't use Nova's default main menu, make sure to update the menu you generate in the `boot()` method of your `NovaServiceProvider` to be as shown under step 4 above, so it will automatically respect the configured option from now on.

- `title` - The browser window title for the calendar page; the default value is 'Nova Calendar'.


---

[⬅️ Back to Documentation overview](/nova-calendar/v1)