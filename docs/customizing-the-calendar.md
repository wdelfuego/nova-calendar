[⬅️ Back to Documentation overview](/nova-calendar/#support)

---

# Customizing the calendar

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