# Support
For any problems you might run into, please [open an issue](https://github.com/wdelfuego/nova-calendar/issues). For feature requests, please upvote or open a [feature request discussion](https://github.com/wdelfuego/nova-calendar/discussions/categories/ideas-feature-requests). Developers who are interested in working together on this tool are highly welcomed.


## Documentation

- [Installation](/nova-calendar/installation.html)
- [Usage](/nova-calendar/usage.html)
- Customization
  - [Customizing events](/nova-calendar/customizing-events.html)
  - [Customizing the calendar](/nova-calendar/customizing-the-calendar.html)
  - [Hiding events from the calendar](/nova-calendar/hiding-events.html)
- [Release log](#release-log)
- [License](#license)


# Release log
## v1.5
- The calendar timezone can now be [customized](/nova-calendar/customizing-the-calendar.html)

### v1.4
- Badges can now be added to calendar day cells

### v1.3
- Calendar events for Nova resources the user isn't authorized to see are now automatically hidden from the calendar
- Calendar events for Nova resources can now be excluded from the calendar by implementing `exclude(NovaResource $resource) : bool` in your `CalendarDataProvider`

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

