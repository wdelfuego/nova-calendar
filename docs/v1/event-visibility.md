<h4>Warning: this documentation is for version 1.x of the package</h4>
<h4>Documentation for the current version 2.0 can be found [here](/nova-calendar)</h4>
[⬅️ Back to Documentation overview](/nova-calendar/v1)

---

# Event visibility

## What events are shown by default?
Events for Nova resources the current user is not authorized to see due to Laravel policies are excluded from the calendar automatically.

All instances of a Nova resource will be shown if no Laravel policy is defined for the underlying Eloquent model or if the static `authorizable` method on the Nova resource class returns `false`, unless you hide specific instances manually by implementing the `excludeResource` method on your CalendarDataProvider; see below.

## Hiding individual events
You can exclude specific instances of Nova resources from the calendar by implementing the `excludeResource` method on your CalendarDataProvider.

For example, if you want to hide events for resources with an Eloquent model that have an `is_finished` property that is `true`, you could write:

```php
use Laravel\Nova\Resource as NovaResource;
```
```php
protected function excludeResource(NovaResource $resource) : bool
{
    return $resource->model()->is_finished;
}
```

In older versions, this method was simply called `exclude`. That method still works but is deprecated now.