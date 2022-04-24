Event calendar for Laravel Nova 4
=================================

An event calendar that displays Nova resources or other time-related data in your [Nova 4](https://nova.laravel.com) project on a monthly calendar view. This package requires that you use Laravel Nova 4.2.4 or newer.

-- insert nice screenshot --


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


## What can it not do (yet)?
The following features are not yet supported:

- Multi-day events
- Creating new events directly from the calendar view
- Drag and drop support to change event dates
- Proper responsiveness for display on small screens
- Integration with external calendar services

Please create or upvote [feature request discussions](https://github.com/wdelfuego/nova-calendar/discussions/categories/ideas-feature-requests) in the GitHub repo for the features you think would be most valuable to have.

## What can you do?
Developers who are interested in working together on this Tool are highly welcomed. Respond to or open a [feature request discussion](https://github.com/wdelfuego/nova-calendar/discussions/categories/ideas-feature-requests) and we'll get you going quickly.

# Installation
```sh
composer require wdelfuego/nova-calendar
```

# Usage

The calendar just needs a single data provider class that supplies event data to the frontend, and for the data provider and tool to be added to your `NovaServiceProvider`:

1. Create a data provider class with a name of your choice anywhere you like in your project. 

	These instructions will assume you created class `App/Providers/CalendarDataProvider` in `App/Providers/CalendarDataProvider.php`, but you can choose any location and any name. If you have no idea, just create that class at that location.

2. Make the data provider a subclass of `Wdelfuego\NovaCalendar\DataProvider\MonthCalendar`.


3. In the data provider, implement the `novaResources()` method to specify which Nova resources are to be included and which of their model's attributes should be used to determine the date and start time of your event. 

	The `novaResources()` method must return an array that maps Nova resource classes to attribute names. The attribute must be casted to a `DateTime` object by the underlying Eloquent model.

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

4. Finally, edit your `NovaServiceProvider` at `app/NovaServiceProvider.php` to add the calendar to its `tools()` method and to register your data provider class as the default calendar data provider:

    ```
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

5. If you're using Nova's default main menu, you're now done. If you've defined your main menu manually in the `boot()` method of your `NovaServiceProvider`, don't forget to add a `MenuSection` that links to the calendar:

    ```
    MenuSection::make('Calendar')
        ->path('/wdelfuego/nova-calendar')
        ->icon('calendar'),
    ````

That's it! Your calendar should now be up and running.

You can navigate through the months using the hotkeys `Alt + arrow right` or `Alt + arrow left` and jump back to the current month using `Alt + H` or by clicking the month name that's displayed above the calendar.

Read the section on Customization below to find out how to completely customize the display of your events and how to add badges and notes to them to make the calendar even more usable for your end users.

# Customization

## Event customization
You can customize event info (name, start time, end time, notes, badges) and customize the CSS styles applied to the event div by implementing the `customizeEvent(Event $event)` method in your calendar data provider. Every event gets passed through this method before it's delivered to the frontend. The method must return the customized event. 

For example:

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
- `hideTime()` hides start and (if available) end times in the calendar view. 
- `displayTime()` enables the display of start and end times.
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
- `name(string $v = null)`
- `start(DateTimeInterface $v = null)`
- `end(DateTimeInterface $v = null)`
- `notes(string $v = null)`
- `badges(array $v = null)`

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
### Changing the first day of the week
In your calendar data provider, implement the constructor to call its parent constructor and make a call to `startWeekOn()` to let the weeks start on the day of your choice. You can use the constants defined in MonthCalendar to specify the day.

For example, to start your weeks on wednesday:
```
use Wdelfuego\NovaCalendar\DataProvider\MonthCalendar;

public function __construct(int $year = null, int $month = null)
{
    parent::__construct($year, $month);
    $this->startWeekOn(MonthCalendar::WEDNESDAY);
}    
    
```

### Changing what happens when the end user clicks an event
Implement the following method in your calendar data provider to change the URL that the user is sent to when they click the event:

```
protected function urlForResource(NovaResource $resource)
{
    return route('nova.pages.detail', [
        'resource' => $resource::uriKey(),
        'resourceId' => $resource->resource->id
    ]);
}
```
This example shows the default behavior. If you change `nova.pages.detail` into `nova.pages.edit` users will be sent directly to the resource's Edit view, instead of to its Detail view.

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