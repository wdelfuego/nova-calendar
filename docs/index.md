# Support
For any problems you might run into, please [open an issue](https://github.com/wdelfuego/nova-calendar/issues). For feature requests, please upvote or open a [feature request discussion](https://github.com/wdelfuego/nova-calendar/discussions/categories/ideas-feature-requests). Developers who are interested in working together on this tool are highly welcomed.


## Documentation

### General
- [Installation](/nova-calendar/installation.html)
  - [Requirements](/nova-calendar/installation.html#requirements)
  - [Adding the calendar to Nova](/nova-calendar/installation.html#adding-the-calendar-to-nova)
  - [Publishing the config file](/nova-calendar/installation.html#publishing-the-config-file)
  
- [Upgrading from v1.x](/nova-calendar/upgrading.html)

- [Calendar usage](/nova-calendar/usage.html)
  - [Navigating the calendar](/nova-calendar/usage.html#navigating-the-calendar)
  - [Clicking events](/nova-calendar/usage.html#clicking-events)
  
- [Adding more calendars to your app](/nova-calendar/adding-more-calendar-views.html)

- [Contributing to this package](/nova-calendar/contributing-to-this-package.html)
    - [Running the test suite](/nova-calendar/contributing-to-this-package.html#running-tests)
    
- [Release log](#release-log)

- [License](#license)

### Calendar customization
- [Event visibility](/nova-calendar/event-visibility.html)
  - [What events are shown by default?](/nova-calendar/event-visibility.html#what-events-are-shown-by-default)
  - [Hiding individual events](/nova-calendar/event-visibility.html#hiding-individual-events)
  
- [Event filters](/nova-calendar/event-filters.html)
  - [Adding event filters to the calendar](/nova-calendar/event-filters.html#adding-event-filters-to-the-calendar)
  - [Available filter types](/nova-calendar/event-filters.html#available-filter-types)
  - [Customization options](#customization-options)
    - [Setting a default event filter](#setting-a-default-event-filter)
    - [Customizing the 'Show all' label](#customizing-the-show-all-label)

- [Customizing the calendar](/nova-calendar/customizing-the-calendar.html)
  - [Changing the calendar timezone](/nova-calendar/customizing-the-calendar.html#changing-the-calendar-timezone)
  - [Adding badges to calendar day cells](/nova-calendar/customizing-the-calendar.html#adding-badges-to-calendar-day-cells)
  - [Changing the calendar URI](/nova-calendar/customizing-the-calendar.html#changing-the-calendar-uri)
  - [Changing the default menu icon and label](/nova-calendar/customizing-the-calendar.html#changing-the-default-menu-icon-and-label)
  - [Changing the first day of the week](/nova-calendar/customizing-the-calendar.html#changing-the-first-day-of-the-week)
  - [Adding events from other sources](/nova-calendar/customizing-the-calendar.html#adding-events-from-other-sources)
  
- [Customizing events](/nova-calendar/customizing-events.html)
  - [The `customizeEvent` method](/nova-calendar/customizing-events.html#the-customizeevent-method)
  - [Adding badges to events](/nova-calendar/customizing-events.html#adding-badges-to-events)
  - [Chainable customization methods](/nova-calendar/customizing-events.html#chainable-customization-methods)
  - [Non-chainable customization methods](/nova-calendar/customizing-events.html#non-chainable-customization-methods)
  - [Changing what happens when an event is clicked](/nova-calendar/customizing-events.html#changing-what-happens-when-an-event-is-clicked)

- [Customizing event styles](/nova-calendar/customizing-events.html#customizing-the-css)
  - [Customizing the default event style](/nova-calendar/customizing-events.html#customizing-the-default-event-style)
  - [Adding custom event styles](/nova-calendar/customizing-events.html#adding-custom-event-styles)
  - [Adding multiple custom event styles to a single event](/nova-calendar/customizing-events.html#adding-multiple-custom-event-styles-to-a-single-event)

- [Custom event generators](/nova-calendar/custom-event-generators.html)
  - [Example: multiple calendar events from a single model](/nova-calendar/custom-event-generators.html#example-multiple-calendar-events-from-a-single-model)


# Release log
## v2.0
- Adds support for [multiple instances](/nova-calendar/adding-more-calendar-views.html) of the calendar, each with their own calendar data provider and configuration
- Adds support for [Event filters](/nova-calendar/event-filters.html), allowing the end user to show different subsets of events within a calendar
- The calendar now restores its previous view state on reloading
- Minor UI and UX improvements
- Package infrastructure has been prepared for multiple front-end views (weekly, daily, etc.)
- Package can now be installed under PHP 7.4 (was previously 8.0+ only)

For the 1.x release log, see the [documentation for the previous version](/nova-calendar/v1).


# License
Copyright © 2022 • Willem Vervuurt, Studio Delfuego, @wdelfuego

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

