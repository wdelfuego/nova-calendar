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
      
      <a @click="prevWeek" class="left" href="#">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" /></svg>
      </a>
  
      <a @click="nextWeek" class="right" href="#">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" /></svg>
      </a>
            
    </div>
    
    <div style="width:100%;overflow:scroll">
      <Card
        class="flex flex-col items-center justify-center dark:bg-gray-800"
        style="min-height: 100px;min-width:800px;background-color:var(--bg-gray-800)"
      >

        <div class="nova-calendar noselect">

            <div class="week">
              <template v-for="(column, index) in $data.columns">
                <div class="nc-header border-gray-200 dark:border-gray-900 dark:text-gray-300" :class="['nc-col-'+(index+1)]">
                    <span>{{ column }}</span>
                </div>
              </template>     

              <!-- multi-day background -->
              <template v-for="day in $data.weekData">
                <div class="day multi dark:border-gray-800 withinRange" :class="['nc-col-'+day.weekdayColumn]" v-bind:class="{'today': day.isToday }">

                </div>
              </template>

              <!-- events, overlaid -->
              <div class="week-events">
        
                <!-- col with events -->
                <template v-for="day in $data.weekData">

                  <div :class="['nc-col-'+day.weekdayColumn]" v-bind:class="{'today': day.isToday }">
                    <div class="dayheader text-gray-400 noselect">
                      <button v-if="this.dayViewEnabled" @click="$emit('set-active-view', 'day', this.year, this.month, this.week, day.label)"><span class="daylabel">{{ day.label }}</span></button>
                      <div v-else><span class="daylabel">{{ day.label }}</span></div>
                    </div>
                  </div>

                  <!-- multi-day events -->
                  <template v-for="event in day.eventsMultiDay">
                    <div :class="['nc-event','multi','nc-col-'+day.weekdayColumn,'span-'+event.spansDaysN]" @click="open(event.url)" :style="this.stylesForEvent(event)" v-bind:class="{'clickable': event.url, 'starts': event.startsEvent, 'ends': event.endsEvent, 'withinRange': event.isWithinRange }">
                      <div class="name noscrollbar">{{ event.name }}</div>
                      <div class="badges noscrollbar">
                        <span class="badge-bg text-gray-200" v-for="badge in event.badges"><span class="badge">{{ badge }}</span></span>
                      </div>
                      <div class="content noscrollbar">
                        <template v-if="event.options.displayTime">
                          <span class="time">{{ event.startDate }} {{ event.startTime }}</span>
                        </template>
                        <span class="notes">{{ event.notes }}</span>
                      </div>
                    </div>
                  </template>
                
                </template>
              </div>

            </div>

            <!-- row with single-day events -->  
            <div class="week" :style="['grid-template-rows: repeat('+this.gridRows+', 10px);']">

              <!-- col with timeline labels -->
              <template v-for="slot in $data.timeline">
                <template v-if="slotIsShown(slot.hour, slot.minute)">
                  <div class="hour-label dark:bg-gray-900 border-b dark:border-gray-800" 
                    :style="['grid-row: '+rowForTime(slot.hour, slot.minute)+' / '+rowForTime(slot.hour, (slot.minute + this.layout.timelineInterval))+';']">{{ slot.hour_minute }}</div>
                </template>
              </template>

              <!-- col with single-day events -->
              <template v-for="day in $data.weekData">

                <template v-for="slot in day.timeline">
                  <template v-if="slotIsShown(slot.hour, slot.minute)">
                    <div class="slot border dark:border-gray-800 dark:bg-gray-900" :class="['nc-col-'+day.weekdayColumn]" v-bind:class="{'withinRange': slot.is_open}" :style="['grid-row: '+rowForTime(slot.hour, slot.minute)+' / '+rowForTime(slot.hour, (slot.minute + this.layout.timelineInterval))+';']"></div>
                  </template>
                </template>

                <div id="hour-events-container" :class="['nc-col-'+day.weekdayColumn]" :style="['grid-row: 1 / span '+this.gridRows+';', 'display: grid;', 'grid-template-rows: repeat('+this.gridRows+', 10px);']">
                  <template v-for="event in day.eventsSingleDay">
                    <div :class="['nc-event']" 
                      @click="open(event.url)" 
                      :style="[
                        this.stylesForEvent(event), 
                        'grid-row-start: '+rowForTime(event.startHour, event.startMinute)+';', 
                        'grid-row-end: '+rowForTime(event.startHour, (event.startMinute + event.durationInMinutes))+';'
                        ]" 
                      v-bind:class="{'clickable': event.url, 'starts': event.startsEvent, 'ends': event.endsEvent, 'withinRange': event.isWithinRange }">
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
        
  mounted() {
    this.year = this.proxyYear,
    this.month = this.proxyMonth,
    this.week = this.proxyWeek,
    this.day = this.proxyDay,
    this.reload();
    
    Nova.addShortcut('alt+right', event => {  this.nextWeek(); });
    Nova.addShortcut('alt+left', event => {   this.prevWeek(); });
    Nova.addShortcut('alt+h', event =>    {   this.reset(); });
  },

  methods: {

    reset() {
      this.loading = true;
      this.week = null;
      this.year = null;
      this.reload();
    },

    prevWeek() {
      this.loading = true;
      this.week -= 1;
      this.reload();
    },
  
    nextWeek() {
      this.loading = true;
      this.week += 1;
      this.reload();
    },

    reload() {
      let vue = this;

      Nova.request().get('/nova-vendor/wdelfuego/nova-calendar/calendar-data/week/'+vue.year+'/'+vue.week)
        .then(response => {
            vue.year = response.data.year;
            vue.month = response.data.month;
            vue.week = response.data.week;
            vue.day = response.data.day;
            vue.title = response.data.title;
            vue.columns = response.data.columns;
            vue.layout = response.data.layout;
            vue.weekData = response.data.week_data;
            vue.styles = response.data.styles;
            vue.timeline = response.data.timeline;

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
    },

    rowForTime(hour, minute) {
      return Math.round(((hour * 60) + minute) / 10) - this.morningOffsetRows + 1;
    },

    slotIsShown(hour, minute) {
      let slotStart = ((hour * 60) + minute);

      return ((slotStart >= this.morningOffset) && (slotStart < this.eveningOffset));
    }
  
  },

  data () {
      return {
          year: null,
          month: null,
          week: null,
          day: null,
          title: '',
          loading: null,
          columns: Array(7).fill('-'),
          layout: Array(4),
          weekData: (Array(7).fill({})),
          timeline: Array(),
          styles: {
            default: { color: '#fff', 'background-color': 'rgba(var(--colors-primary-500), 0.9)' }
          },
      }
  },

  computed: {
    morningOffset() {
      return this.layout.openingHour * 60 - 30; /* 30 minutes margin for UI purposes */
    },

    eveningOffset() {
      return this.layout.closingHour * 60 + 30; /* 30 minutes margin for UI purposes */
    },

    gridRows() {
      return 144 - ((this.morningOffset + (1440 - this.eveningOffset)) / 10);
    },

    morningOffsetRows() {
      return this.morningOffset / 10;
    },

    dayViewEnabled() {
      return this.calendarViews.includes('day');
    },
  }

}
</script>

<style>
/* Scoped Styles */
</style>