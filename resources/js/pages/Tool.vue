/*
 * © Copyright 2022 · Willem Vervuurt, Studio Delfuego
 * 
 * You can modify, use and distribute this package under one of two licenses:
 * 1. GNU AGPLv3
 * 2. A perpetual, non-revocable and 100% free (as in beer) do-what-you-want 
 *    license that allows both non-commercial and commercial use, under conditions.
 *    See LICENSE.md for details.
 * 
 *    (it boils down to: do what you want as long as you're building and/or
 *     using calendar views, but don't embed this package or a modified version
 *     of it in free or paid-for software libraries and packages aimed at developers).
 */
 
<template>
  <div>
    <Head :title="$data.windowTitle || $data.title" />

    <div id="nc-control">
    
      <div class="left-items">
        <a @click="prevMonth" href="#" class="button hover:bg-gray-100 dark:hover:bg-gray-700" title="Alt + ←">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" /></svg>
        </a>

        <a @click="reset" href="#" class="button hover:bg-gray-100 dark:hover:bg-gray-700" title="Alt + H">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="2" fill="currentColor"/></svg>
        </a>
          
        <a @click="nextMonth" href="#" class="button hover:bg-gray-100 dark:hover:bg-gray-700" title="Alt + →">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" /></svg>
        </a>
        
        <Dropdown
          :handle-internal-clicks="true"
          class="flex h-9 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
          dusk="month-picker"
        >
          <DropdownTrigger
            class="toolbar-button px-2"
            style="padding-left:0"
          >
          </DropdownTrigger>

          <template #menu>
            <DropdownMenu width="220">

              <ScrollWrap :height="350" class="bg-white dark:bg-gray-900">
                <div
                  ref="theForm"
                  class="divide-y divide-gray-200 dark:divide-gray-800 divide-solid"
                >

                <div class="p-3 text-center">
                  <select name="month" class="mr-3 dark:bg-gray-900" v-model="month" @change="reload()" @click.stop>
                    <option v-for="(monthLabel, monthNum) in $data.monthLabels" :value="monthNum">
                    {{ monthLabel }}
                    </option>
                  </select>
                
                  <select name="year" class="dark:bg-gray-900" v-model="year" @change="reload()" @click.stop>
                    <template v-for="index in 25">
                      <option :value="year + (25 - index)">{{ year + (25 - index) }}</option>
                    </template>
                    <template v-for="index in 100">
                      <option :value="year - index">{{ year - index }}</option>
                    </template>
                  </select>
                </div>
              
                </div>
              </ScrollWrap>
            </DropdownMenu>
          </template>
        </Dropdown>
        
        <h1 class="text-90 font-normal text-xl md:text-2xl noselect">
          <span>{{ $data.title }}</span>
        </h1>
        
      </div>

      <div class="center-items">


      
      </div>
      
      <div class="right-items">

        <Dropdown
          v-if="Object.keys(availableFilters).length"
          :handle-internal-clicks="true"
          :class="{
            'bg-primary-500 hover:bg-primary-600 border-primary-500':
              activeFilterKey != null,
            'dark:bg-primary-500 dark:hover:bg-primary-600 dark:border-primary-500':
              activeFilterKey != null,
          }"
          class="flex h-9 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
          dusk="filter-selector"
        >
          <DropdownTrigger
            :class="{
              'text-white hover:text-white dark:text-gray-800 dark:hover:text-gray-800':
                activeFilterKey != null,
            }"
            class="toolbar-button px-2"
          >
            <Icon type="filter" />
            <span
              v-if="activeFilterKey != null"
              :class="{
                'text-white dark:text-gray-800': activeFilterKey != null,
              }"
              class="ml-2 font-bold"
              v-html="activeFilterLabel"
            >
            </span>
          </DropdownTrigger>

          <template #menu>
            <DropdownMenu width="260">

              <ScrollWrap :height="350" class="bg-white dark:bg-gray-900">
                <div
                  ref="theForm"
                  class="divide-y divide-gray-200 dark:divide-gray-800 divide-solid"
                >
                  <div v-if="activeFilterKey != null" class="bg-gray-100">
                    <button
                      class="py-2 w-full block tracking-wide text-center text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 focus:outline-none"
                      @click="chooseFilter(null)"
                      v-html="$data.resetFiltersLabel"
                    >
                    </button>
                  </div>

                  <div>
                    <template v-for="(filterLabel, filterKey) in $data.availableFilters">
                      <button
                        class="py-2 w-full block dark:bg-gray-800 dark:hover:bg-gray-700 hover:bg-gray-200"
                        :class="{'font-bold': activeFilterKey == filterKey}"
                        v-html="filterLabel"
                        @click="chooseFilter(filterKey)"
                      >
                      </button>
                    </template>
                  </div>

                </div>
              </ScrollWrap>
            </DropdownMenu>
          </template>
        </Dropdown>
  

      </div>
      
    </div>
    
    <div style="width:100%;overflow:scroll">
      <Card
        class="flex flex-col items-center justify-center dark:bg-gray-800"
        style="min-height: 300px;min-width:800px;background-color:var(--bg-gray-800)"
      >

        <div class="nova-calendar noselect" v-if="title.length">

          <div class="nc-header">
            <div v-for="column in $data.columns" class="border-gray-200 dark:border-gray-900 dark:text-gray-300"><span>{{ column }}</span></div>
          </div>

          <div class="nc-content">

            <!-- week wrapper -->
            <div v-for="(week, weekIndex) in $data.weeks" class="week">

              <!-- a cell per day, background -->
              <template v-for="day in week">
                <div class="day dark:bg-gray-900 dark:border-gray-800"  :class="['nc-col-'+day.weekdayColumn]" v-bind:class="{'withinRange': day.isWithinRange, 'today': day.isToday }">
                  <div class="dayheader text-gray-400 noselect">
                    <span class="daylabel">{{ day.label }}</span>
                    <div class="badges">
                      <span class="badge-bg text-gray-200" v-for="badge in day.badges">
                        <Tooltip><template #content><span v-html="badge.tooltip"></span></template>
                          <span class="badge" v-html="badge.badge"></span>
                        </Tooltip>
                      </span>
                    </div>
                  </div>
                </div>
              </template>
              
              <!-- events, overlaid -->
              <div class="week-events">
                
                <!-- multi day events for all days first -->
                <template v-for="day in week">
                  <template v-for="event in day.eventsMultiDay">
                    <div :class="['nc-event','multi','nc-col-'+day.weekdayColumn,'span-'+event.spansDaysN]" @click="open($event, event.url)" :style="this.stylesForEvent(event)" v-bind:class="{'clickable': event.url, 'starts': event.startsEvent, 'ends': event.endsEvent, 'withinRange': event.isWithinRange }">
                      <div class="name noscrollbar">{{ event.name }}</div>
                      <div class="badges noscrollbar">
                        <span v-if="event.startsEvent" class="badge-bg text-gray-200" v-for="badge in event.badges"><span class="badge" v-html="badge"></span></span>
                      </div>
                      <div class="content noscrollbar">
                        <template v-if="event.startsEvent && event.options.displayTime">
                          <span class="time">{{ event.startTime }}</span>
                        </template>
                        <span v-if="event.startsEvent" class="notes" v-html="event.notes"></span>
                      </div>
                    </div>
                  </template>
                </template>
                
                <!-- then all single day events -->
                <template v-for="day in week">
                  <div :class="['single-day-events','nc-col-'+day.weekdayColumn]">
                    <template v-for="event in day.eventsSingleDay">
                      <div :class="['nc-event']" @click="open($event, event.url)" :style="this.stylesForEvent(event)" v-bind:class="{'clickable': event.url, 'starts': event.startsEvent, 'ends': event.endsEvent, 'withinRange': event.isWithinRange }">
                        <div class="name noscrollbar">{{ event.name }}</div>
                        <div class="badges" v-if="event.badges.length > 0">
                          <span class="badge-bg text-gray-200" v-for="badge in event.badges"><span class="badge" v-html="badge"></span></span>
                        </div>
                        <div class="content noscrollbar">
                          <template v-if="event.options.displayTime">
                            <span class="time" v-if="event.endTime">{{ event.startTime }} - {{ event.endTime }}</span>
                            <span class="time" v-else>{{ event.startTime }}</span>
                          </template>
                          <span class="notes" v-html="event.notes"></span>
                        </div>
                      </div>
                    </template>
                  </div>
                </template>
                
              </div>

            </div>
          </div>

        </div>

      </Card>
    </div>
    
  </div>
</template>

<script>
export default {
        
  mounted() {
    this.init();
    
    Nova.addShortcut('alt+right', event => {  this.nextMonth(); });
    Nova.addShortcut('alt+left', event => {   this.prevMonth(); });
    Nova.addShortcut('alt+h', event =>    {   this.reset(); });
  },

  methods: {

    reset() {
      this.month = null;
      this.year = null;
      this.reload();
    },
        
    init() {
      if(this.hasStoredSettings()) {
        this.restoreSettings();
        this.reload(false);
      }
      else
      {
        this.reload(true);
      }
    },

    prevMonth() {
      this.month -= 1;
      this.reload();
    },
  
    nextMonth() {
      this.month += 1;
      this.reload();
    },

    reload(isInitRequest = false) {
      let vue = this;
      vue.loading = true;
      
      // Work out the apiPath from the current Tool path, this works
      // because the ToolServiceProvider enforces that both use the same configurable uri part
      let apiUrl = '/nova-vendor/wdelfuego/nova-calendar' + this.calendarUrl() + '/month?y='+vue.year+'&m='+vue.month;
      if(vue.activeFilterKey) {
        apiUrl += '&filter='+vue.activeFilterKey;
      }
      else if(isInitRequest) {
        apiUrl += '&isInitRequest=1';
      }
      Nova.request().get(apiUrl)
        .then(response => {
            vue.styles = response.data.styles;
            vue.year = response.data.year;
            vue.month = response.data.month;
            vue.monthLabels = response.data.monthLabels;
            vue.windowTitle = response.data.windowTitle;
            vue.resetFiltersLabel = response.data.resetFiltersLabel;
            vue.availableFilters = response.data.filters;
            vue.activeFilterKey = response.data.activeFilterKey;
            vue.title = response.data.title;
            vue.columns = response.data.columns;
            vue.weeks = response.data.weeks;
            
            this.setFilter(vue.activeFilterKey);
            vue.loading = false;
            this.storeSettings();
        });
    },
    
    open(e, url) {
      if (e.metaKey || e.ctrlKey) {
        window.open(Nova.url(url))
      } else {
        Nova.visit(url);
      }
    },

    stylesForEvent(event) {
      if(event.options.styles) {
        let out = [this.styles.default];
        event.options.styles.forEach(style => {
          if(this.styles[style] === undefined)
          {
            console.log("[nova-calendar] Unknown custom style name '" + style + "'; does the eventStyles method of your CalendarDataProvider define it properly?");
          }
          else
          {
            out.push(this.styles[style]);
          }
        })
        return out;
      } else {
        return this.styles.default;
      }
    },
    
    chooseFilter(filterKey) {
      this.setFilter(filterKey);
      this.reload();
    },
    
    setFilter(filterKey) {
      this.activeFilterKey = filterKey;
      for(var filterKey in this.availableFilters)
      {
        if(this.activeFilterKey == filterKey)
        {
          this.activeFilterLabel = this.availableFilters[filterKey];
        }
      }
    },
    
    calendarUrl() {
      const url = window.location.pathname.substring(Nova.url('').length);
      return url.startsWith('/') ? url : '/' + url;
    },
    
    storageKey() {
      return 'wdelfuego-nova-calendar-' + this.calendarUrl();
    },
    
    hasStoredSettings() { 
      return (localStorage.getItem(this.storageKey()) !== null);
    },
    
    storeSettings() {
      localStorage.setItem(this.storageKey(), JSON.stringify({
        year: this.year,
        month: this.month,
        activeFilterKey: this.activeFilterKey
      }));
    },
    
    restoreSettings() {
      const storedData = JSON.parse(localStorage.getItem(this.storageKey()));
      if (storedData) {
        this.year = storedData.year;
        this.month = storedData.month;
        this.activeFilterKey = storedData.activeFilterKey;
      }
    },
    
  },

  props: {

  },

  data () {
      return {
          loading: true,
          resetFiltersLabel: 'All events',
          availableFilters: {},
          activeFilterKey: null,
          activeFilterLabel: null,
          year: null,
          month: null,
          monthLabels: Array(12).fill(''),
          windowTitle: '',
          title: '',
          columns: Array(7).fill('-'),
          weeks: Array(6).fill(Array(7).fill({})),
          styles: {
            default: { color: '#fff', 'background-color': 'rgba(var(--colors-primary-500), 0.9)' }
          }
      }
  }

}
</script>

<style>
/* Scoped Styles */
</style>