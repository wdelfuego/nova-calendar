
[⬅️ Back to Documentation overview](/nova-calendar)

---

#  Upgrade Guide

This upgrade guide shows you how to upgrade from v1 (any release) to v2 of this package.

Follow these steps in order. 

If you encounter *any* issues or unclarities, **please** 🙏,[ open a GitHub issue](https://github.com/wdelfuego/nova-calendar/issues) and let me know so I can make the upgrade process smoother for the next reader.

Creating, maintaining and supporting this package is a lot of work, but I want to deliver the best quality code and support that I can. 
If you are using this package commercially and find it helpful, please consider [sponsoring](https://github.com/sponsors/wdelfuego) me to help me make time for maintaining this project and supporting the users.

## Table of Contents
1. [Create or update the config file](#1-create-or-update-the-config-file)
1. [Update the `NovaServiceProvider`](#2-update-the-novaserviceprovider)
1. [Update your calendar data provider](#3-update-your-calendar-data-provider)
1. [Update your custom event generators](#4-update-your-custom-event-generators)
1. [Namespace change for interfaces](#5-namespace-change-for-interfaces)
1. [Do the update](#6-do-the-update)

## 1. Create or update the config file

Config file `config/nova-calender.php` was optional in v1 but is required in v2, and its structure has changed to accomodate configuring multiple calendar instances.

Check if `config/nova-calender.php` already exists in your project.

If it doesn't exist yet; publish it first by running the following command.

    ```sh
    php artisan vendor:publish --provider="Wdelfuego\NovaCalendar\ToolServiceProvider" --tag="config"
    ```

Now that you're guaranteed to have a config file, upgrade the structure as follows:

    1. If it contains an entry with key `title`, change that key to `windowTitle`.
    1. Add a key `dataProvider` and set it to the full class name of your `CalendarDataProvider`. 

       If you don't know where to find your `CalendarDataProvider`, it's the class that is currently being bound to `CalendarDataProviderInterface` in the `register` method of your `NovaServiceProvider`. You can move the `use` statement from your `NovaServiceProvider` to the config file and set the `dataProvider` entry to `CalendarDataProvider::class`.

    1. Finally, to accomodate multiple calendar instances, the existing top-level config now turns into a specific calendar config; wrap another array around the current contents of the config file and use a string key as a uniquely identifying *calendar key*, to point to this config. 

        If you're not going to add more calendars, `my-calendar` will do just fine as a calendar key. The calendar key is for internal use and is never exposed to the end user.

    1. You should end up with a `config/nova-calendar.php` file that looks something like this:

        ```php
        use App\Providers\CalendarDataProvider;

        return [
            // This key `my-calendar` is the calendar key. You can use whatever.
            'my-calendar' => [
                'dataProvider' => CalendarDataProvider::class,
                'uri' => 'calendar/my-calendar',
                'windowTitle' => 'My calendar'
            ]
        ];
        ```
        Adding more calendar views to your app will be as simple as adding an entry to this file under a new calendar key and implementing its calendar data provider.

## 2. Update the `NovaServiceProvider`
1. Get rid of the binding between `CalendarDataProviderInterface` and your `CalendarDataProvider` in the `register` method of your `NovaServiceProvider`:

    ```php
    // Delete this binding from NovaServiceProvider::register
    $this->app->bind(CalendarDataProviderInterface::class, function($app) {
        return new CalendarDataProvider();
    });
    ```

1. Confirm that you are not using the `CalendarDataProviderInterface` class anywhere else in your `NovaServiceProvider`, then remove this use statement from the top of the file:

    ```php
    use Wdelfuego\NovaCalendar\Interface\CalendarDataProviderInterface;
    ```

    If you find that you are still referencing that class somewhere in the `NovaServiceProvider`, don't remove it but update the `use` statement to:

    ```php
    use Wdelfuego\NovaCalendar\Contracts\CalendarDataProviderInterface;
    ```

1. In the `tools` method of your `NovaServiceProvider`, where you create a `new NovaCalendar`, supply the calendar key to the constructor (the key used in the config file, in this example: `my-calendar`).

    ```php
    public function tools()
    {
        return [
           // Supply the calendar key to the tool constructor
           new NovaCalendar('my-calendar'),
        ];
    }
    ```

1. If you are creating your own menu items in the `boot` method of your `NovaServiceProvider`, update the path that is currently either hardcoded or a call to `config('nova-calendar.uri')` to use the `NovaCalendar::pathToCalendar` helper instead, like so:

    ```php
    // Change this:
    MenuItem::link(__('Calendar'), config('nova-calendar.uri', 'wdelfuego/calendar'),

    // Into this (supply the calendar key to the pathToCalendar helper):
    MenuItem::link(__('Calendar'), NovaCalendar::pathToCalendar('my-calendar')),
    ```

## 3. Update your calendar data provider
In your calendar data provider, perform the following changes:
1. The class used to extend `Wdelfuego\NovaCalendar\DataProvider\MonthCalendar`, change it to extend `Wdelfuego\NovaCalendar\DataProvider\AbstractCalendarDataProvider` instead.
1. Make sure the method signature of your `novaResources()` method is this:

    ```php
    public function novaResources() : array
    ```
1. If your calendar data provider implements the `exclude()` method, rename it to `excludeResource()`.

1. If your calendar data provider implements the `title()` method, rename it to `titleForView` and add a `string $viewSpecifier` argument:

    ```php
	// Change this:
    public function title() : string
    ```
    ```php
	// Into this:
    public function titleForView(string $viewSpecifier) : string
    ```

    This is an infrastructural preparation that will allow you to render different calendar titles for different views in the future. You can leave the implementation of the method unchanged, since there is currently only a month view, so the supplied `$viewSpecifier` is currently always `'month'`.

1. Methods `firstDayOfCalendar()` and `lastDayOfCalendar()` have been removed; update any calls to them to `startOfCalendar()` and `endOfCalendar()`, respectively.


    Search your project for the removed method names to see if you depend on them anywhere else.

1. Finally, method visibility has changed for a few methods.

    Check if your calendar data provider implements any of these methods and update their visibility if it does:
    * `customizeCalendarDay()` must now be `public`
    * `allEvents()` must now be `public`

### Other low-impact changes to the calendar data provider

These changes most probably do not affect you, but are mentioned in any case so that you can be sure:

  1. Method `eventDataForDate()` no longer exists. That logic has been moved to the View layer in `src/View/Month.php`. If your calendar data provider implements that method, remove it and find another way to reach what you want. Create a GitHub issue for some guidance; updates to this package to restore missing functionality will be released promptly if required.
  1. Method `calendarWeeks()` has been renamed to `calendarData()`

## 4. Update your custom event generators

You can skip this step if you are not using custom event generators in your project.

1. The `resourceToEvents` method of custom event generators now takes `$startOfCalendar` and `$endOfCalendar` arguments;

    ```php
	// Change this:
    protected function resourceToEvents(NovaResource $resource) : array;
    ```
    ```php
	// Into this:
    protected function resourceToEvents(NovaResource $resource, Carbon $startOfCalendar, Carbon $endOfCalendar) : array;
    ```
    Add a `use Illuminate\Support\Carbon;` statement to the file if it isn't there yet.

1.   This probably doesn't affect you, but class `Wdelfuego\NovaCalendar\EventGenerator\EventGenerator` has been renamed to `Wdelfuego\NovaCalendar\EventGenerator\NovaEventGenerator`; update the class name if you extend the existing `EventGenerator` somewhere.


## 5. Namespace change for interfaces
To extend backwards compatibility to PHP7.4, the interface namespace `Wdelfuego\NovaCalendar\Interface` has been renamed to `Wdelfuego\NovaCalendar\Contracts`. You can do a global search and replace in your project to reflect this. It is possible and quite likely you don't need to make any changes.


## 6. Do the update
That's it, your project is now ready to update to version 2.0 :).

Update the minimal version of `wdelfuego/nova-calendar` to `^2.0` in your project's `composer.json` file and run `composer update`.

If everything went well, your calendar is now visible and functional running the new version. The 'previous month' and 'next month' buttons are now grouped together on the left side above the calendar. If you see that and your calendar works, the upgrade was successful.

You can now make use of new features such as [adding event filters](/nova-calendar/event-filters.html) and [adding more calendar views](/nova-calendar/adding-more-calendar-views.html) to your application.

It's been a lot of work to finalize this release and write the documentation, such as this Upgrade Guide. If you are using this package commercially and find it helpful, please consider [sponsoring](https://github.com/sponsors/wdelfuego) me to help me make time for maintaining this project and supporting the users. Thanks and happy calendaring ;)!

---

[⬅️ Back to Documentation overview](/nova-calendar)
