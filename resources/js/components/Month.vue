/*
 * © Copyright 2022 · Willem Vervuurt, Studio Delfuego, Bartosz Bujak
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
  <LoadingView :loading="loading">

    <div id="nc-control">
    
      <h1 @click="reset" class="text-90 font-normal text-xl md:text-2xl noselect">
        <span>{{ $data.title }}</span>
      </h1>
      
      <a @click="prevMonth" class="left" href="#">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" /></svg>
      </a>
  
      <a @click="nextMonth" class="right" href="#">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" /></svg>
      </a>
            
    </div>
    
    <div style="width:100%;overflow:scroll">
      <Card class="flex flex-col items-center justify-center dark:bg-gray-800" style="min-height: 300px;min-width:800px;background-color:var(--bg-gray-800)">

        <div class="nova-calendar noselect">

          <div class="nc-content">

            <div class="week">
              <template v-for="(column, index) in $data.columns">
                <div class="nc-header border-gray-200 dark:border-gray-900 dark:text-gray-300" :class="['nc-col-'+(index+1)]">
                    <span>{{ column }}</span>
                </div>
              </template>     
            </div>

            <!-- week wrapper -->
            <div v-for="(week, weekIndex) in $data.weeks" class="week">
              <div class="week-label dark:bg-gray-900 border-b dark:border-gray-800" style="grid-row: 2 / 3;">
                <button v-if="this.weekViewEnabled" @click="$emit('set-active-view', 'week', week.year, week.month, week.number, null)"><span class="text-xs">{{ __('W') }}{{ week.number }}</span></button>
                <div v-else><span class="text-xs">{{ __('W') }}{{ week.number }}</span></div>
              </div>

              <!-- a cell per day, background -->
              <template v-for="day in week.data">
                <div class="day dark:bg-gray-900 dark:border-gray-800"  :class="['nc-col-'+day.weekdayColumn]" v-bind:class="{'withinRange': day.isWithinRange, 'today': day.isToday }">
                </div>
              </template>
              
              <!-- events, overlaid -->
              <div class="week-events">

                <!-- multi day events for all days first -->
                <template v-for="day in week.data">

                <!-- a cell per day, background -->
                <div :class="['nc-col-'+day.weekdayColumn]" v-bind:class="{'withinRange': day.isWithinRange, 'today': day.isToday }">
                  <div class="dayheader text-gray-400 noselect">
                    <button v-if="this.dayViewEnabled" @click="$emit('set-active-view', 'day', this.year, this.month, this.week, day.label)"><span class="daylabel">{{ day.label }}</span></button>
                    <div v-else><span class="daylabel">{{ day.label }}</span></div>
                  </div>
                </div>
                
                  <template v-for="event in day.eventsMultiDay">
                    <div :class="['nc-event','multi','nc-col-'+day.weekdayColumn,'span-'+event.spansDaysN]" @click="open(event.url)" :style="this.stylesForEvent(event)" v-bind:class="{'clickable': event.url, 'starts': event.startsEvent, 'ends': event.endsEvent, 'withinRange': event.isWithinRange }">
                      <div class="name noscrollbar">{{ event.name }}</div>
                      <div class="badges noscrollbar">
                        <span v-if="event.startsEvent" class="badge-bg text-gray-200" v-for="badge in event.badges"><span class="badge">{{ badge }}</span></span>
                      </div>
                      <div class="content noscrollbar">
                        <template v-if="event.startsEvent && event.options.displayTime">
                          <span class="time">{{ event.startTime }}</span>
                        </template>
                        <span v-if="event.startsEvent" class="notes">{{ event.notes }}</span>
                      </div>
                    </div>
                  </template>
                </template>
                
                <!-- then all single day events -->
                <template v-for="day in week.data">
                  <div :class="['single-day-events','nc-col-'+day.weekdayColumn]">
                    <template v-for="event in day.eventsSingleDay">
                      <div :class="['nc-event']" @click="open(event.url)" :style="this.stylesForEvent(event)" v-bind:class="{'clickable': event.url, 'starts': event.startsEvent, 'ends': event.endsEvent, 'withinRange': event.isWithinRange }">
                        <div class="name noscrollbar">{{ event.name }}</div>
                        <div class="badges">
                          <span class="badge-bg text-gray-200" v-for="badge in event.badges"><span class="badge">{{ badge }}</span></span>
                        </div>
                        <div class="content noscrollbar">
                          <template v-if="event.options.displayTime">
                            <span class="time" v-if="event.endTime">{{ event.startTime }} - {{ event.endTime }}</span>
                            <span class="time" v-else>{{ event.startTime }}</span>
                          </template>
                          <span class="notes">{{ event.notes }}</span>
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

  </LoadingView>
</template>

<script>
export default {
  props: [
    'proxyYear',
    'proxyMonth',
    'proxyWeek',
    'proxyDay',
    'calendarViews'
  ],

  data () {
      return {
          year: null,
          month: null,
          week: null,
          day: null,
          title: '',
          loading: null,
          columns: Array(7).fill('-'),
          weeks: Array(6).fill(Array(7).fill({})),
          styles: {
            default: { color: '#fff', 'background-color': 'rgba(var(--colors-primary-500), 0.9)' }
          }
      }
  },

  mounted() {
    this.year = this.proxyYear,
    this.month = this.proxyMonth,
    this.week = this.proxyWeek,
    this.day = this.proxyDay,
    this.reload();
    
    Nova.addShortcut('alt+right', event => {  this.nextMonth(); });
    Nova.addShortcut('alt+left', event => {   this.prevMonth(); });
    Nova.addShortcut('alt+h', event =>    {   this.reset(); });
  },

  methods: {

    reset() {
      this.loading = true;
      this.month = null;
      this.year = null;
      this.reload();
    },

    prevMonth() {
      this.loading = true;
      this.month -= 1;
      this.reload();
    },
  
    nextMonth() {
      this.loading = true;
      this.month += 1;
      this.reload();
    },

    reload() {
      let vue = this;
      Nova.request().get('/nova-vendor/wdelfuego/nova-calendar/calendar-data/month/'+vue.year+'/'+vue.month)
        .then(response => {
            vue.year = response.data.year;
            vue.month = response.data.month;
            vue.week = response.data.week;
            vue.day = response.data.day;
            vue.title = response.data.title;
            vue.columns = response.data.columns;
            vue.weeks = response.data.weeks;
            vue.styles = response.data.styles;
            
            vue.loading = false;
        });
    },
    
    open(url) {
      Nova.visit(url);
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
    }
  
  },

  computed: {
    dayViewEnabled() {
      return this.calendarViews.includes('day');
    },

    weekViewEnabled() {
      return this.calendarViews.includes('week');
    }
  }
}
</script>

<style>
/* Scoped Styles */
</style>