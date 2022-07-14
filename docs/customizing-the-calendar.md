[⬅️ Back to Documentation overview](/nova-calendar/#support)

---

# Customizing the calendar

### Changing the calendar timezone
By default, calendar events are displayed in your app's timezone as specified under `timezone` in `config/app.php`.

As of version 1.5, you can override this by implementing the `timezone()` method in your `CalendarDataProvider`.

To hardcode the timezone:
```php
public function timezone(): string
{
    return 'Europe/Lisbon';
}
```

To set the calendar timezone on a per-user basis: 
```php
public function timezone(): string
{
    return $this->request->user()->timezone;
}
```

This example assumes the user has a `timezone` attribute.

As you can see, you can rely on the internal `request` attribute to get to the user. 

### Adding badges to calendar day cells
In your `CalendarDataProvider`, implement the `customizeCalendarDay()` method as follows:

```php
protected function customizeCalendarDay(CalendarDay $day) : CalendarDay
{
    if($day->start->format('d') % 2 == 0)
    {
        $day->addBadge('even');
    }
    else
    {
        $day->addBadge('odd');
    }
    
    return $day;
}
```

As badges, you could use any combination of short words or single letters, symbols or 
even emoji 🤯 to make certain calendar days stand out visually.

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

If you are going to return a long list of events here, or do a request to an external service, you can use the `startOfCalendar()` and `endOfCalendar()` methods inherited from `Wdelfuego\NovaCalendar\DataProvider\MonthCalendar` to limit the scope of your event generation to the date range that is currently being requested by the frontend. 

Any events you return here that fall outside that date range are never displayed, so it's a waste of your and your users' resources if you still generate them.

---

[⬅️ Back to Documentation overview](/nova-calendar/#support)