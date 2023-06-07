<h1 align="center">Event calendar for Laravel Nova 4</h1>

<p align="center">An event calendar that displays Nova resources or other time-related data in your Nova 4 project on a monthly calendar view that adapts nicely to clear and dark mode.</p>

![The design of the calendar in both clear and dark mode](https://github.com/wdelfuego/nova-calendar/blob/main/resources/doc/screenshot.jpg?raw=true)

# Installation
```sh
composer require wdelfuego/nova-calendar
```

For help implementing and using the calendar, take a look at the [documentation](https://wdelfuego.github.io/nova-calendar).

# License summary
Anyone can use and modify this package in any way they want, including commercially, as long as the commercial use is a) creating implemented calendar views and/or b) using the implemented calendar views.
Basically the only condition is that you can't sublicense the package or embed it in a framework (unless you do so under the AGPLv3 license).
Usage in Nova is not compatible with the AGPLv3 license. More details [below](#license).

# Support & Documentation

For any problems or doubts you might run into, please [open an issue](https://github.com/wdelfuego/nova-calendar/issues). For feature requests, please upvote or open a [feature request discussion](https://github.com/wdelfuego/nova-calendar/discussions/categories/ideas-feature-requests). Developers who are interested in working together on this tool are highly welcomed.

# What can it do?
This calendar tool for Nova 4 shows existing Nova resources and, if you want, dynamically generated events, but comes without database migrations or Eloquent models itself. This is considered a feature. Your project is expected to already contain certain Nova resources for Eloquent models with `DateTime` fields or some other source of time-related data that can be used to generate the calendar events displayed to the end user.

The following features are supported:

- Automatically display Nova resources on a monthly calendar view
- Mix multiple types of Nova resources on the same calendar
- Display events that are not related to Nova resources
- Use event filters to limit the amount of events shown on the calendar
- Add badges to events and calendar days to indicate status or attract attention
- Customize visual style and content of each individual event
- Laravel policies are respected to exclude events from the calendar automatically
- Allows end users to navigate through the calendar with hotkeys
- Allows end users to navigate to the resources' Detail or Edit views by clicking events

# What can it not do (yet)?
The following features are not (yet) supported:

- Integration with external calendar services
- Creating new events directly from the calendar view
- Drag and drop to change event dates

Please create or upvote [feature request discussions](https://github.com/wdelfuego/nova-calendar/discussions/categories/ideas-feature-requests) in the GitHub repo for the features you think would be most valuable to have.

# Release log
## v2.0
- Adds support for [multiple instances](https://wdelfuego.github.io/nova-calendar/multiple-calendars.html) of the calendar, each with their own calendar data provider and configuration
- Adds support for [Event filters](https://wdelfuego.github.io/nova-calendar/event-filters.html), allowing the end user to show different subsets of events within a calendar
- The calendar now restores its previous view state on reloading
- Minor UI and UX improvements
- Package infrastructure has been prepared for multiple front-end views (weekly, daily, etc.)
- Package can now be installed under PHP 7.4 (was previously 8.0+ only)

### v1.8
- Added support for Laravel 10, thanks @pcorrick!
- Fixed issue where some multi-day events were not properly shown on the calendar in all cases, thanks @SamMakesCode!

### v1.7
- Holding Ctrl or Meta key while clicking an Event now opens the target URL in a new browser window, thanks @vesper8!
- Event notes now support HTML content

### v1.6
- The URI of the calendar tool is now configurable, thanks @kitchetof!
- Adds support for [custom event generators](https://wdelfuego.github.io/nova-calendar/custom-event-generators.html) to define your own mapping from Nova resource to calendar event(s)
- For developers of this package: added first set of unit tests

### v1.5
- The calendar timezone can now be [customized](https://wdelfuego.github.io/nova-calendar/customizing-the-calendar.html)

### v1.4
- Badges can now be added to calendar day cells

### v1.3
- Calendar events for Nova resources the user isn't authorized to see are now automatically hidden from the calendar
- Calendar events for Nova resources can now be excluded from the calendar on an individual basis

### v1.2
- Adds support for customizing non-Nova events
- Adds support for applying multiple custom styles to events

### v1.1
- Adds support for multi-day events
- Improved visual design
- Better support for mobile usage
- Fixes bug where badges could overlap the event title
- View now uses css grid instead of table
- New dual licensing model (see the end of this file)

### v1.0
- Initial release with support for single-day events only

# License
Copyright © 2022 • Willem Vervuurt, Studio Delfuego, wdelfuego

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

