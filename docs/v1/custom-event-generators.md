| :exclamation:  Warning: this documentation is for version 1.x of the package.   |
|---------------------------------------------------------------------------------|

[⬅️ Back to Documentation overview](/nova-calendar/v1)

---

  
# Custom event generators

By default, a single event is generated for each instance of the Nova resource classes specified in the `novaResources()` method of the calendar data provider, using the attribute name(s) you supply there as start and optional end timestamp. Nova resources the user has no access to see are [automatically excluded](/nova-calendar/v1/event-visibility.html#what-events-are-shown-by-default) from the calendar.

You don't need custom event generators if all you want to do is customize the content or the style of these events, since you can [customize event properties](/nova-calendar/v1/customizing-events.html) and [customize event styles](/nova-calendar/v1/customizing-events.html#customizing-the-css) directly from the calendar data provider.


If you want to go beyond specifying attribute names for start and end timestamps and take full control of the way a Nova resource is translated to one or more calendar events, you can do so by implementing a custom event generator. This is especially useful if you want to be able to generate multiple calendar events from single model instances.


If you want to add events to the calendar that are not mappable to the Nova resources in your project, you can't do that with a custom event generator because custom event generators still require a Nova resource. Instead, you can implement the [`nonNovaEvents()`](/nova-calendar/v1/customizing-the-calendar.html#adding-events-from-other-sources) method in your calendar data provider and construct your non-Nova events there.


## Setting up a custom event generator
1. Create a file for your event generator class at a location of your choice.
    
	For example, if the Nova resource you want to generate events for is called `Flight`, a sensible option would be to create class `FlightEventGenerator` in `app/Nova/Calendar`, relative to your project root.

1. Place the following `use` statements at the start of that file:
    ```php
    use Illuminate\Support\Carbon;
    use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
    use Laravel\Nova\Resource as NovaResource;
    use Wdelfuego\NovaCalendar\EventGenerator\Custom as CustomEventGenerator;
    use Wdelfuego\NovaCalendar\Event;
    ```

1. Have your event generator class extend `CustomEventGenerator`:

	```php
    class FlightEventGenerator extends CustomEventGenerator
    {
    }
    ```
1. Add the `modelQuery` and `resourceToEvents` methods and implement them;

    ```php
    protected function modelQuery(EloquentBuilder $queryBuilder, Carbon $startOfCalendar, Carbon $endOfCalendar) : EloquentBuilder
    ```
    The `modelQuery` method is used to configure an Eloquent query builder that determines which models are relevant given the calendar range that is currently visible to the user (a six week period by default).
    
     You must add _where_ clauses to the query builder using those timestamps to select the proper Eloquent models, then return the query builder.

    ```php
    protected function resourceToEvents(NovaResource $resource) : array
    ```
    The `resourceToEvents` method is used to generate an array with 0 or more `Event` objects given a single Nova resource instance.
    
    In this method, you don't have to worry about end user permissions. The calendar data provider automatically [hides events](/nova-calendar/v1/event-visibility.html#what-events-are-shown-by-default) for Nova resources the user has no access to. 
        
    See the example implementation and explanations of these methods [below](#example-multiple-calendar-events-from-a-single-model) for more info.


1. Now that your custom event generator exists, it can be mapped to the proper Nova resource class in the `novaResources()` method of your calendar data provider. 

    So for our Flight example, the final `novaResources()` method could look something like this:

    ```php
    use App\Nova\User;
    use App\Nova\SomeEvent;
    use App\Nova\Flight;
    use App\Nova\Calendar\FlightEventGenerator;
    ```

    ```php
    public function novaResources() : array
    {
        return [
            User::class => 'created_at',
            SomeEvent::class => ['starts_at', 'ends_at'],
            Flight::class => new FlightEventGenerator(),
        ];
    }
    ```	

    As you can see, you can use the standard datetime attribute system for some Nova resources, and custom event generators for others.

## Example: multiple calendar events from a single model

Here is an example of a fully implemented custom event generator that generates multiple events for a single model.

```php
namespace App\Nova\Calendar;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Laravel\Nova\Resource as NovaResource;
use Wdelfuego\NovaCalendar\EventGenerator\Custom as CustomEventGenerator;
use Wdelfuego\NovaCalendar\Event;

class FlightEventGenerator extends CustomEventGenerator
{
    protected function modelQuery(EloquentBuilder $queryBuilder, Carbon $startOfCalendar, Carbon $endOfCalendar) : EloquentBuilder
    {
        // The queryBuilder supplied as the 1st argument is an Eloquent query builder that's 
        // already set up to query the Nova resources' table. You just have to add where clauses 
        // using the $startOfCalendar and $endOfCalendar values so the event generator only
        // considers resources that generate events within the current range of the calendar.

        // For example, to limit event generation to models that have 
        // property 'take_off_at' fall within the current calendar range:
        return $queryBuilder->where('take_off_at', '>=', $startOfCalendar)
                            ->where('take_off_at', '<=', $endOfCalendar);

        // You *could* just return the unmodified query builder to always generate events for 
        // all existing instances, but that'd be a waste of your server capacity, 
        // your users' time, and of bandwidth for everyone. It could also make your
        // calendar unreasonably slow depending on the number of models in your database.

    }

    protected function resourceToEvents(NovaResource $resource) : array
    {
        // Implement this method to return an array of Event objects for
        // the supplied Nova resource instance

        // You can get the resource's underlying Eloquent model as follows:
        $model = $resource->model();
    
        // Build an array of Event objects.
        $out = [];

        // Event constructor signature is:
        // __construct(string $name, DateTimeInterface $start, DateTimeInterface $end = null, string $notes = '', array $badges = [])
        // Let's consider our Flight example again, and suppose we want to create
        // separate events for take-off and landing. You'd do something like:
        $out[] = new Event("Take-off", $model->take_off_at);
        $out[] = new Event("Landing",  $model->take_off_at->addMinutes($model->flight_duration));

        foreach($out as $event)
        {
            // See the documentation for more event customization features.
            $event->notes($model->flight_number);
        }

        // Return the array of Event objects
        return $out;
    }
}
```

---

[⬅️ Back to Documentation overview](/nova-calendar/v1)