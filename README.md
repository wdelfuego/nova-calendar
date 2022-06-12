<h1 align="center">Event calendar for Laravel Nova 4</h1>

![The design of the calendar in both clear and dark mode](https://github.com/wdelfuego/nova-calendar/blob/main/resources/doc/screenshot.jpg?raw=true)

<p align="center">An event calendar that displays Nova resources or other time-related data in your Nova 4 project on a monthly calendar view that adapts nicely to clear and dark mode.</p>

# Release 1.1 • june '22
- Adds support for multi-day events
- Improved visual design
- Better support for mobile usage
- Fixes bug where badges could overlap the event title
- View now uses css grid instead of table
- New dual licensing model (see the end of this file)


# Installation
```sh
composer require wdelfuego/nova-calendar
```

## What can it do?
This calendar tool for Nova 4 shows existing Nova resources and, if you want, dynamically generated events, but comes without database migrations or Eloquent models itself. This is considered a feature. Your project is expected to already contain certain Nova resources for Eloquent models with `DateTime` fields or some other source of time-related data that can be used to generate the calendar events displayed to the end user.

The following features are supported:

- Automatically display Nova resources on a monthly calendar view
- Display events that are not related to Nova resources but come from other sources
- Completely customize visual style and content of each event
- Add badges to events to indicate status or attract attention
- Mix multiple types of Nova resources on the same calendar
- Supports single and multi-day events
- Supports clear and dark mode
- Allows end users to navigate through the calendar with hotkeys
- Allows end users to navigate to the resources' Detail or Edit views by clicking events
- Month and day names are automatically presented in your app's locale

## What can it not do (yet)?
The following features are not (yet) supported:

- Integration with external calendar services
- Creating new events directly from the calendar view
- Drag and drop to change event dates

Please create or upvote [feature request discussions](https://github.com/wdelfuego/nova-calendar/discussions/categories/ideas-feature-requests) in the GitHub repo for the features you think would be most valuable to have.

## What can you do?
Developers who are interested in working together on this tool are highly welcomed. Take a look at the [open issues](https://github.com/wdelfuego/nova-calendar/issues) (those labelled 'good first issue' are great for new contributors) or at the [feature request discussions](https://github.com/wdelfuego/nova-calendar/discussions/categories/ideas-feature-requests) and we'll get you going quickly.

## What can we do?

For any problems you might run into, please [open an issue](https://github.com/wdelfuego/nova-calendar/issues) on GitHub.

For feature requests, please upvote or open a [feature request discussion](https://github.com/wdelfuego/nova-calendar/discussions/categories/ideas-feature-requests) on GitHub.


# Usage

The calendar just needs a single data provider class that supplies event data to the frontend, and for the data provider and tool to be added to your `NovaServiceProvider`:

1. Create a data provider class with a name of your choice anywhere you like in your project, or run the following artisan command to create the default data provider:

    ```sh
    php artisan create:default-calendar-data-provider
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

## Hotkeys
You can navigate through the months using the hotkeys `Alt + arrow right` or `Alt + arrow left` and jump back to the current month using `Alt + H` (or by clicking the month name that's displayed above the calendar).


# Customization
You can customize the display of your events and add badges and notes to them to make the calendar even more usable for your end users.

## Event customization
You can customize event info (name, start time, end time, notes, badges) and customize the CSS styles applied to the event div by implementing the `customizeEvent(Event $event)` method in your calendar data provider. Every event gets passed through this method before it's delivered to the frontend. The method must return the customized event. 

By default, your events get the title that the Nova resource's `title()` method returns and the start time is set to the value of the attribute specified in your data provider's `novaResources()` method. Other event fields are left empty by default but can easily be loaded from the event's associated model:
```php
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

```php
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

The following customization methods with regard to the display of the `Event` in the calendar view are available:

### Chainable customization methods
All of these methods return the `Event` itself so you can chain them in the `customizeEvent` method:
- `hideTime()` hides start and end times in the calendar view. 
- `displayTime()` enables the display of start and (if available) end times.
- `withTimeFormat(string $v)` sets the format in which times are displayed, using PHP's [DateTime format](https://www.php.net/manual/en/datetime.format.php).
- `withName(string $v)` updates the name of the event.
- `withStart(DateTimeInterface $v)` updates the date and start time of the event.
- `withEnd(DateTimeInterface $v)` updates the end date and time of the event.
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
```php
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

A default style is not required; if you don't define it, the default style will use your app's primary color as defined in `config/nova.php` under `brand` => `colors` => `500`. 

### Adding custom event styles
To add custom event styles, add them to the same array: 
```php
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

After you defined your styles in the `eventStyles` array, call `style` or `withStyle` in your `customizeEvent` method using the name of the style (in this example, 'special' or 'warning') to apply them to individual events, for example:

```php
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
```php
public function tools()
{
    return [
        (new NovaCalendar)->withMenuLabel('Label')->withMenuIcon('HeroIcon'),
    ];
}    
```

### Changing the first day of the week
In your calendar data provider, implement the `initialize` method and make a call to `startWeekOn()` to let the weeks start on the day of your choice. You can use the constants defined in `NovaCalendar` to specify the day.

For example, to start your weeks on wednesday:
```php
use Wdelfuego\NovaCalendar\NovaCalendar;

public function initialize(): void
{
    $this->startWeekOn(NovaCalendar::WEDNESDAY);
}
    
```

### Changing what happens when an event is clicked
Implement the following method in your calendar data provider to change the URL that the user is sent to when they click the event:

```php
protected function urlForResource(NovaResource $resource)
{
    return '/resources/' .$resource::uriKey() .'/' .$resource->id;
}
```
This example shows the default behavior. If you append `/edit` to the string, users will be sent directly to the resource's Edit view, instead of to its Detail view.

### Adding events from other sources
If the events you want to show don't have a related Nova resource, you can still add them to the calendar. In your calendar data provider, implement the `nonNovaEvents` method to push any kind of event data you want to the frontend.

The method should return an array of `Event` objects:

```php
use Wdelfuego\NovaCalendar\Event;

protected function nonNovaEvents() : array
{
    return [
        (new Event("Now until tomorrow", now(), now()->addDays(1)))
            ->addBadges('D')
            ->withNotes('This is a dynamically created event')
    ];
}
    
```

If you are going to return a long list of events here, or do a request to an external service, you can use the `firstDayOfCalendar()` and `lastDayOfCalendar()` methods inherited from `Wdelfuego\NovaCalendar\DataProvider\MonthCalendar` to limit the scope of your event generation to the date range that is currently being requested by the frontend. 

Any events you return here that fall outside that date range are never displayed, so it's a waste of your and your users' resources if you still generate them.

# License
Copyright © 2022 • Willem Vervuurt, Studio Delfuego

This entire copyright and license notice must be included with any copy, back-up, 
fork or otherwise modified version of this package.

You can use this package under one of the follwing two licenses:

1. GNU AGPLv3 for GPLv3-or-newer compatible open source projects. Note that this license 
   is not compatible with usage in Nova, so this package can't be used under this license
   until a version exists that can be included in Laravel/Vue3 projects without 
   depending on Nova. You can find the full terms of this license in LICENSE-agpl-3.0.txt 
   in this repository and can also find a copy on https://www.gnu.org/licenses/.
    
2. A perpetual, non-revocable and 100% free (as in beer) do-what-you-want license 
   that allows both non-commercial and commercial use, under the following 6 conditions:
   
  - You can use this package to implement and/or use as many calendars in as many 
    applications on as many servers with as many users as you want and charge for 
    that what you want, as long as you and/or your organization are either
      a) the developer(s) responsible for implementing the calendar(s), or
      b) the end user(s) of the implemented calendar(s), or
      c) both.
    
  - Sublicensing, relicensing, reselling or charging for the redistribution of this 
    package (or a modified version of it) to other developers for them to implement 
    calendar views with is not allowed under this license.
    
  - You are free to make any modifications you want and are not required to make 
    your modifications public or announce them.
    
  - You are free to make and distribute modified versions of this package publicly 
    as long as you distribute it for free, as a stand-alone package and under the 
    same dual licensing model. 
    
  - Embedding this package (or a modified version of it) in free or paid-for software
    libraries or frameworks that are available to developers not within your 
    organization is expressly not allowed under this license. If the software library
    or framework is GPLv3-or-newer compatible, you are free to do so under the 
    GNU AGPLv3 license.
    
  - The following 2 disclaimers apply:

	  - THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
      IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
      FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL 
      THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
      LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
      OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN 
      THE SOFTWARE.
      
    - YOU ASSUME ALL RISK ASSOCIATED WITH THE INSTALLATION AND USE OF THE SOFTWARE. 
      LICENSE HOLDERS ARE SOLELY RESPONSIBLE FOR DETERMINING THE APPROPRIATENESS OF 
      USE AND ASSUME ALL RISKS ASSOCIATED WITH ITS USE, INCLUDING BUT NOT LIMITED TO
      THE RISKS OF PROGRAM ERRORS, DAMAGE TO EQUIPMENT, LOSS OF DATA OR SOFTWARE 
      PROGRAMS, OR UNAVAILABILITY OR INTERRUPTION OF OPERATIONS.

