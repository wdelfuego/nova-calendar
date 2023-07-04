[⬅️ Back to Documentation overview](/nova-calendar)

---

## Table of Contents
- [Customizing events](#customizing-events)
  - [The `customizeEvent` method](#the-customizeevent-method)
  - [Adding badges to events](#adding-badges-to-events)
  - [Chainable customization methods](#chainable-customization-methods)
  - [Non-chainable customization methods](#non-chainable-customization-methods)
  - [Changing what happens when an event is clicked](#changing-what-happens-when-an-event-is-clicked)
- [Customizing the CSS](#customizing-the-css)
  - [Customizing the default event style](#customizing-the-default-event-style)
  - [Adding custom event styles](#adding-custom-event-styles)
  - [Adding multiple custom event styles to a single event](#adding-multiple-custom-event-styles-to-a-single-event)

  
# Customizing Events

## The `customizeEvent` method
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

## Adding badges to events
As you can see in the example above, the `addBadge` and `addBadges` methods let you add letters, short strings or emoji to events as badges that will be shown in the upper right corner of the event box.

You can use html in a badge, so you can use mark-up or include hero icons using svg tags:

```php
    // Html mark-up
    $event->addBadge($count .'/<b>' .$total .'</b>');
    
    // Hero icon
    $event->addBadge('<svg xmlns="http://www.w3.org/2000/svg" style="display:inline-block" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>');
```

The use of 'X' as a badge isn't recommended because it could be mistaken for a close button.

Event badges do not currently support tooltips; [calendar day badges](/nova-calendar/customizing-the-calendar.html#adding-badges-to-calendar-day-cells) do.

## Chainable customization methods
The following customization methods with regard to the display of the `Event` in the calendar view are available:

All of these methods return the `Event` itself so you can chain them in the `customizeEvent` method:
- `hideTime()` hides start and end times in the calendar view. 
- `displayTime()` enables the display of start and (if available) end times.
- `withTimeFormat(string $v)` sets the format in which times are displayed, using PHP's [DateTime format](https://www.php.net/manual/en/datetime.format.php).
- `withName(string $v)` updates the name of the event.
- `withStart(DateTimeInterface $v)` updates the date and start time of the event.
- `withEnd(DateTimeInterface $v)` updates the end date and time of the event.
- `withUrl(string $v)` updates the url opened when the end user clicks the event.
- `withNotes(string $v)` updates the notes displayed below the name and, if enabled, the time info of the event.
- `addBadge(string $v)` adds a badge to the event's upper right corner. 
- `addBadges(string ...$v)` adds 1 or more badges with one call. This method doesn't expect an array but an argument for each badge you want to add.
- `removeBadge(string $v)` and `removeBadges(string ...$v)` do the same but they remove rather than add badges.
- `addStyle(string $v)` adds a CSS style to be applied to the event. The style needs to be defined in your `eventStyles` method (see 'Adding custom event styles' below).
- `addStyles(string ...$v)` adds 1 or more styles with one call. This method doesn't expect an array but an argument for each CSS style you want to add.
- `removeStyle(string $v)` and `removeStyles(string ...$v)` do the same but they remove rather than add styles.
- `withStyle(string $v)` - deprecated. Used to set the CSS style applied to the div of this specific event. Starting from release 1.2, multiple styles per event are supported; you should now use the `addStyle`, `addStyles`, `removeStyle` and `removeStyles` methods to manage event styles.

## Non-chainable customization methods
Corresponding methods are available in non-chainable form, if you prefer to work with those. 

These function as simple setters when you supply an argument, and as getters when you don't.
- `name(string $v = null) : string`
- `start(DateTimeInterface $v = null) : DateTimeInterface`
- `end(DateTimeInterface $v = null) : ?DateTimeInterface`
- `url(string $v = null)`
- `notes(string $v = null) : string`
- `badges(array $v = null) : array`
- `styles(array $v = null) : array`


## Changing what happens when an event is clicked
Implement the following method in your calendar data provider to change the URL that the user is sent to when they click the event:

```php
protected function urlForResource(NovaResource $resource)
{
    return '/resources/' .$resource::uriKey() .'/' .$resource->id;
}
```
This example shows the default behavior. If you append `/edit` to the string, users will be sent directly to the resource's Edit view, instead of to its Detail view.

# Customizing the CSS
You can customize the CSS that is applied to the event divs in the calendar view on a per-event basis, or on a global basis by customizing the default event style.

## Customizing the default event style
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

Defining a default style is not required; if you don't define it, the default default style will use white text on a background in your app's primary color as defined in `config/nova.php` under `brand` => `colors` => `500`. 

## Adding custom event styles
To add custom event styles, add them to the same array: 
```php
public function eventStyles() : array
{
    return [
        'default' => [
            'color' => '#000',
            'background-color' => '#fff'
        ],
        'special' => [
            'color' => '#f00',
        ],
        'warning' => [
            'border' => '1px solid #f00'
        ]
    ];
}
```

After you defined your styles in the `eventStyles` array, call `addStyle` or `addStyles` in your `customizeEvent` method using the names of the styles (in this example, 'special' or 'warning') to apply them to individual events.

The original `withStyle` and `style` methods are deprecated; `withStyle` is now an alias for `addStyle` and `style` is only supported on events that have at most one custom style assigned to it.

For example:

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
            $event->addStyle('warning');
        }
    }

    // Display all events that have a specific class of Nova
    // resource with a specific style:
    if($event->hasNovaResource(SomeResourceClass::class))
    {
        $event->addStyle('special');
    }

    // Or conversely, display all events that don't have a 
    // Nova resource with a specific style:
    if(!$event->hasNovaResource())
    {
        $event->addStyle('special');
    }

    return $event;
}
```

## Adding multiple custom event styles to a single event
You are free to assign multiple styles to a single event using the `addStyles` method.

Their CSS specifications will be merged in the order that they were added to the event, with styles added later overruling the ones added before it. Other, non-conflicting CSS properties defined by styles added before it will still be applied to the event as expected.

For example:

```php
protected function customizeEvent(Event $event) : Event
{
    if($event->model())
    {
        if($event->model()->isInACertainState())
        {
            // 'warning' takes precedence over 'special'
            $event->addStyles('special', 'warning');
        }
    }

    return $event;
}
```

---

[⬅️ Back to Documentation overview](/nova-calendar)