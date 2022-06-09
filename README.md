<h1 align="center">Event calendar for Laravel Nova 4</h1>

![The design of the calendar in both clear and dark mode](https://github.com/wdelfuego/nova-calendar/blob/main/resources/doc/screenshot.jpg?raw=true)

<p align="center">An event calendar that displays Nova resources or other time-related data in your Nova 4 project on a monthly calendar view.</p>

<p align="center">The calendar view adapts nicely to clear and dark mode:</p>

![Clear and dark mode next to each other](https://github.com/wdelfuego/nova-calendar/blob/main/resources/doc/screenshot-both.png?raw=true)


## What can it do?
This calendar tool for Nova 4 shows existing Nova resources and, if you want, dynamically generated events, but comes without database migrations or Eloquent models itself. This is considered a feature. Your project is expected to already contain certain Nova resources for Eloquent models with `DateTime` fields or some other source of time-related data that can be used to generate the calendar events displayed to the end user.

The following features are supported:

- Automatically display single-day events on a monthly calendar view in both clear and dark mode
- Completely customize both visual style and event content on a per-event basis
- Add badges to individual events to indicate status or attract attention
- Allows end users to navigate through the calendar with hotkeys
- Allows end users to navigate to the resources' Detail or Edit views by clicking events
- Mix multiple types of Nova resources on the same calendar
- Display events that are not related to Nova resources
- Month and day names are automatically presented in your app's locale

## What can it not do (yet)?
The following features are not yet supported:

- Multi-day events (first new feature in the pipeline)
- Creating new events directly from the calendar view
- Drag and drop support to change event dates
- Proper responsiveness for display on small screens
- Integration with external calendar services

Please create or upvote [feature request discussions](https://github.com/wdelfuego/nova-calendar/discussions/categories/ideas-feature-requests) in the GitHub repo for the features you think would be most valuable to have.

## What can you do?
Developers who are interested in working together on this tool are highly welcomed. Take a look at the [open issues](https://github.com/wdelfuego/nova-calendar/issues) (those labelled 'good first issue' are great for new contributors) or at the [feature request discussions](https://github.com/wdelfuego/nova-calendar/discussions/categories/ideas-feature-requests) and we'll get you going quickly.

# Installation
```sh
composer require wdelfuego/nova-calendar
```

# Usage

The calendar just needs a single data provider class that supplies event data to the frontend, and for the data provider and tool to be added to your `NovaServiceProvider`:

1. Create a data provider class with a name of your choice anywhere you like in your project, or run the following artisan command to create the default data provider:

    ```sh
    php artisan create:default-calendar-data-provider
    ```

    If you choose to make the data provider yourself, make it a subclass of `Wdelfuego\NovaCalendar\DataProvider\MonthCalendar`.

2. In the data provider, implement the `novaResources()` method to specify which Nova resources are to be included and which of their model's attributes should be used to determine the date and start time of your event. 

	The `novaResources()` method must return an array that maps Nova resource classes to attribute names. The attribute must be casted to a `DateTime` object by the underlying Eloquent model. If you return an empty array, the calendar will work but will not contain any events.

    For example, to make the calendar show your users as events on the date their accounts were created, implement `novaResources()` as follows:

    ```
    namespace App\Providers;

    use Wdelfuego\NovaCalendar\DataProvider\MonthCalendar;
    use App\Nova\User;

    class CalendarDataProvider extends MonthCalendar
    {
        public function novaResources() : array
        {
            return [
                User::class => 'created_at'
            ];
        }	
    }
    ```

   This is the only method that's required. You can include more types of Nova resources to be shown on the calendar by simply adding more class names and attributes to the `novaResources()` method.

3. Finally, edit your `NovaServiceProvider` at `app/NovaServiceProvider.php` to add the calendar to its `tools()` method and to register your data provider class as the default calendar data provider:

    ```
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

4. If you're using Nova's default main menu, you're already done. 

    If you've defined your main menu manually in the `boot()` method of your `NovaServiceProvider`, don't forget to add a `MenuSection` that links to the calendar:

    ```
    MenuSection::make('Calendar')
        ->path('/wdelfuego/nova-calendar')
        ->icon('calendar'),
    ````

That's it! Your calendar should now be up and running.

## Hotkeys
You can navigate through the months using the hotkeys `Alt + arrow right` or `Alt + arrow left` and jump back to the current month using `Alt + H` (or by clicking the month name that's displayed above the calendar).


# Customization
You can customize the display of your events and add badges and notes to them to make the calendar even more usable for your end users.

## Event customization
You can customize event info (name, start time, end time, notes, badges) and customize the CSS styles applied to the event div by implementing the `customizeEvent(Event $event)` method in your calendar data provider. Every event gets passed through this method before it's delivered to the frontend. The method must return the customized event. 

By default, your events get the title that the Nova resource's `title()` method returns and the start time is set to the value of the attribute specified in your data provider's `novaResources()` method. Other event fields are left empty by default but can easily be loaded from the event's associated model:
```
protected function customizeEvent(Event $event) : Event
{
    if($event->model())
    {
        $event->end($event->model()->datetime_end);
        $event->name($event->model()->name);
        $event->notes($event->model()->notes);
    }
    return $event;
}
```

Your customization can be a lot more complex, too:

```
use Wdelfuego\NovaCalendar\Event;

protected function customizeEvent(Event $event) : Event
{
    // Give each event a duration of 4 hours (for display only)
    $event->end($event->start()->copy()->addHour(4));

    // For events that have an underlying Eloquent model..
    if($event->model())
    {
        // Prefix each event's name with its ID
        $event->name($event->model()->id .' - ' .$event->name());

        // Add a warning emoji badge if the end user should 
        // be warned about the model's state
        if($event->model()->isInACertainState())
        {
            $event->addBadges('⚠️');
        }

        // Add a note to the event that is displayed right below
        // the event's name in the calendar view
        if($event->model()->someSpecialCase())
        {
            $event->notes('Something special is going on');
        }
    }

    // Display all events without time info
    $event->hideTime();

    return $event;
}
```

The following customization methods with regard to the display of the `Event` in the calendar view are available.

### Chainable customization methods
All of these methods return the `Event` itself so you can chain them in the `customizeEvent` method:
- `hideTime()` hides start and end times in the calendar view. 
- `displayTime()` enables the display of start and (if available) end times.
- `withTimeFormat(string $v)` sets the format in which times are displayed, using PHP's [DateTime format](https://www.php.net/manual/en/datetime.format.php).
- `withName(string $v)` updates the name of the event.
- `withStart(DateTimeInterface $v)` updates the date and start time of the event (it will be displayed on the calendar accordingly).
- `withEnd(DateTimeInterface $v)` updates the end time of the event. **Note**: if you supply an `end` timestamp, its date value is completely ignored by this package for now. All events are assumed to be single day events. Its time value will be used as the event's end time.
- `withNotes(string $v)` updates the notes displayed below the name and, if enabled, the time info of the event.
- `addBadge(string $v)` adds a badge to the event's upper right corner. You can simply set letters, short strings or emoji. The use of 'X' as a badge isn't recommended because it could be mistaken for a close button.
- `addBadges(string ...$v)` adds 1 or more badges with one call. This method doesn't expect an array but an argument for each badge you want to add.
- `removeBadge(string $v)` and `removeBadges(string ...$v)` do the same but they remove rather than add badges.
- `withStyle(string $v)` to set the CSS style applied to the div of this specific event (see 'Adding custom event styles' below).

### Non-chainable customization methods
Corresponding methods are available in non-chainable form, if you prefer to work with those. 

These function as `set`ters when you supply an argument, and as `get`ters when you don't.
- `name(string $v = null) : string`
- `start(DateTimeInterface $v = null) : DateTimeInterface`
- `end(DateTimeInterface $v = null) : ?DateTimeInterface`
- `notes(string $v = null) : string`
- `badges(array $v = null) : array`

## Customizing the CSS
You can customize the CSS that is applied to the event divs in the calendar view on a per-event basis, or on a global basis by customizing the default event style.

### Customizing the default event style
In your calendar data provider, implement the `eventStyles()` method to return the CSS that you want to apply to all events by default:

For example, to make the default style white with black text:
```
public function eventStyles() : array
{
    return [
        'default' => [
            'color' => '#000',
            'background-color' => '#fff'
        ],
    ];
}
```

### Adding custom event styles
To add custom event styles, add them to the array returned by `eventStyles` in your calendar data provider: 
```
public function eventStyles() : array
{
    return [
        'special' => [
            'color' => '#f00',
        ],
        'warning' => [
            'border' => '1px solid #f00'
        ]
    ];
}
```

Then call `style` or `withStyle` in your `customizeEvent` method using the name of the style (in this example, 'special' or 'warning') to apply them to individual events, for example:

```
use Wdelfuego\NovaCalendar\Event;
use App\Nova\SomeResourceClass;

protected function customizeEvent(Event $event) : Event
{
    // For events that have an underlying Eloquent model..
    if($event->model())
    {
        if($event->model()->isInACertainState())
        {
            $event->style('warning');
        }
    }

    // Display all events that have a specific class of Nova
    // resource with a specific style:
    if($event->hasNovaResource(SomeResourceClass::class))
    {
        $event->style('special');
    }

    // Or conversely, display all events that don't have a 
    // Nova resource with a specific style:
    if(!$event->hasNovaResource())
    {
        $event->style('special');
    }

    return $event;
}
```


## Calendar customization
### Changing the default menu icon and label
In your `NovaServiceProvider`, update the `tools()` method as follows:
```
public function tools()
{
    return [
        (new NovaCalendar)->withMenuLabel('Label')->withMenuIcon('HeroIcon'),
    ];
}    
```

### Changing the first day of the week
In your calendar data provider, implement the constructor to call its parent constructor and make a call to `startWeekOn()` to let the weeks start on the day of your choice. You can use the constants defined in `NovaCalendar` to specify the day.

For example, to start your weeks on wednesday:
```
use Wdelfuego\NovaCalendar\NovaCalendar;

public function __construct(int $year = null, int $month = null)
{
    parent::__construct($year, $month);
    $this->startWeekOn(NovaCalendar::WEDNESDAY);
}    
    
```

### Changing what happens when an event is clicked
Implement the following method in your calendar data provider to change the URL that the user is sent to when they click the event:

```
protected function urlForResource(NovaResource $resource)
{
    return '/resources/' .$resource::uriKey() .'/' .$resource->id;
}
```
This example shows the default behavior. If you append `/edit` to the string, users will be sent directly to the resource's Edit view, instead of to its Detail view.

A future release will offer a more reliable way to generate these type of URL parts based on route names such as `nova.pages.edit` and `nova.pages.detail`.

### Adding events from other sources
If the events you want to show don't have a related Nova resource, you can still add them to the calendar. In your calendar data provider, implement the `nonNovaEvents` method to push any kind of event data you want to the frontend.

The method should return an array of `Event`s:

```
use Wdelfuego\NovaCalendar\Event;

protected function nonNovaEvents() : array
{
    return [
        (new Event("This is now", now()))
            ->addBadges('D')
            ->withNotes('This is a dynamically created event')
    ];
}
    
```

If you are going to return a long list of events here, or do a request to an external service, you can use the `firstDayOfCalendar()` and `lastDayOfCalendar()` methods inherited from `Wdelfuego\NovaCalendar\DataProvider\MonthCalendar` to limit the scope of your event generation to the date range that is currently being requested by the frontend. 

Any events you return here that fall outside that date range are never displayed, so it's a waste of your and your users' resources if you still generate them.

# Support

For any problems you might run into, please [open an issue](https://github.com/wdelfuego/nova-calendar/issues) on GitHub.

For feature requests, please upvote or open a [feature request discussion](https://github.com/wdelfuego/nova-calendar/discussions/categories/ideas-feature-requests) on GitHub.