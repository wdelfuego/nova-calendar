# Hiding events

## What events are shown on the calendar? 
Events for Nova resources the current user is not authorized to see due to Laravel policies are excluded from the calendar automatically.

All instances of a Nova resource will be shown if no Laravel policy is defined for the underlying Eloquent model or if the static `authorizable` method on the Nova resource class returns `false`, unless you hide them manually by implementing the `exclude` method on your CalendarDataProvider.

## Hiding events from the calendar manually
You can exclude Nova resources from the calendar by implementing the `exclude` method on your CalendarDataProvider.

For example, if you want to hide events for resources with an Eloquent model that have an `is_finished` property that is `true`, you could write:

```php
use Laravel\Nova\Resource as NovaResource;
```
```php
protected function exclude(NovaResource $resource) : bool
{
    return $resource->model()->is_finished;
}

```