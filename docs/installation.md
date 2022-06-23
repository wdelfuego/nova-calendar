[⬅️ Back to Documentation overview](/nova-calendar/#documentation)

---

#  Installation

Start by requiring the package:

```sh
composer require wdelfuego/nova-calendar
```

The calendar just needs a single data provider class that supplies event data to the frontend, and for the data provider and tool to be added to your `NovaServiceProvider`:

1. Create a data provider class with a name of your choice anywhere you like in your project, or run the following artisan command to create the default data provider:

    ```sh
    php artisan nova-calendar:create-default-calendar-data-provider
    ```

    If you choose to make the data provider yourself, make it a subclass of `Wdelfuego\NovaCalendar\DataProvider\MonthCalendar`.

2. Edit your `NovaServiceProvider` at `app/NovaServiceProvider.php` to add the calendar to its `tools()` method and to register your data provider class as the default calendar data provider:

    ```php
    use Wdelfuego\NovaCalendar\NovaCalendar;
    use Wdelfuego\NovaCalendar\Interface\CalendarDataProviderInterface;
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

	You would implement `novaResources()` as follows:

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

	- This method must return an array that maps Nova resource classes to attribute names (for events that only have a starting timestamp) or arrays of attribute names (for events that have both a start and an end timestamp).
	- Nova resources for which you specify a single attribute will be added as single-day events using the specified attribute to determine its date and time.
	- Nova resources for which you specify an array with two attribute names will be added as single or multi-day events for which the first attribute determines the start date and time, and the second attribute determines the end date and time. 
	- All specified attributes must be cast to a `DateTime` object by the underlying Eloquent model.
	- If you let `novaResources()` return an empty array, the calendar will work but will not contain any events.

   The `novaResources()` method is the only method that's required. You can include more types of Nova resources to be shown on the calendar by simply adding more class names and attributes.


4. If you're using Nova's default main menu, you're already done. 

    If you've defined your main menu manually in the `boot()` method of your `NovaServiceProvider`, don't forget to add a `MenuSection` that links to the calendar:

    ```php
    MenuSection::make('Calendar')
        ->path('/wdelfuego/nova-calendar')
        ->icon('calendar'),
    ````

That's it! Your calendar should now be up and running.

---

[⬅️ Back to Documentation overview](/nova-calendar/#documentation)