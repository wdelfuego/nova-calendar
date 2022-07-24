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
      
      <a @click="prevDay" class="left" href="#">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" /></svg>
      </a>
  
      <a @click="nextDay" class="right" href="#">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" /></svg>
      </a>      
    </div>
    
    <div style="width:100%;overflow:scroll">
      <Card
        class="flex flex-col items-center justify-center dark:bg-gray-800"
        style="min-height: 100px;min-width:800px;background-color:var(--bg-gray-800)"
      >
        <div class="nova-calendar">

          <!-- row with multi-day events -->  
          <div class="timeline-view">
           
            <div class="header-container" :style="['grid-template-columns: repeat('+this.gridRows+', 1fr);']">
              <!-- col with timeline labels -->
              <template v-for="slot in dayData.timeline">
                <template v-if="slotIsShown(slot.hour, slot.minute)">
                  <div class="col-label dark:bg-gray-900 border-l border-r border-b dark:border-gray-800" 
                    :style="['grid-column: '+rowForTime(slot.hour, slot.minute)+' / '+rowForTime(slot.hour, (slot.minute + this.dayData.timelineInterval))+';']">{{ slot.hour_minute }}</div>
                  <div class="slot dark:bg-gray-900" :class="{'withinRange border-b dark:border-gray-800': slot.is_open}" :style="['grid-row: 2; grid-column: '+rowForTime(slot.hour, slot.minute)+' / '+rowForTime(slot.hour, (slot.minute + this.dayData.timelineInterval))+';']"></div>
                </template>
              </template>
            </div>

          </div>

          <div class="timeline-view">

            <div class="multi-events-container">

              <template v-for="event in dayData.eventsMultiDay">
                <div class="nc-event multi withinRange" 
                  @click="open(event.url)" 
                  :style="this.stylesForEvent(event)" 
                  v-bind:class="{'clickable': event.url, 'starts': event.startsEvent, 'ends': event.endsEvent }" 
                  v-tooltip="{
                    placement: 'bottom-start',
                    distance: 10,
                    skidding: 0,
                    content: 
                      event.name + ' - ' + 
                      event.startDate + ' ' + ' - ' + event.endDate + 
                      (event.notes ? (' - ' + event.notes) : '' )
                  }">
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
            </div>

          </div>

          <div class="timeline-view">

            <div class="group-label"></div>

            <div class="single-events-container" :style="['grid-template-columns: repeat('+this.gridRows+', 1fr);']">

              <template v-for="slot in dayData.timeline">
                <template v-if="slotIsShown(slot.hour, slot.minute)">
                  <div class="slot dark:bg-gray-900" :class="{'withinRange border-b dark:border-gray-800': slot.is_open}" :style="['grid-row: 1; grid-column: '+rowForTime(slot.hour, slot.minute)+' / '+rowForTime(slot.hour, (slot.minute + this.dayData.timelineInterval))+';']"></div>
                </template>
              </template>

              <div class="timeline-events" :style="['grid-column: 1 / -1','grid-template-columns: repeat('+this.gridRows+', 1fr);']">

                <template v-for="event in dayData.eventsSingleDay">
                <div :class="['nc-event multi']" 
                  @click="open(event.url)" 
                  :style="[
                    this.stylesForEvent(event),
                    'grid-column-start: '+rowForTime(event.startHour, event.startMinute)+';', 
                    'grid-column-end: '+rowForTime(event.startHour, (event.startMinute + event.durationInMinutes))+';'
                    ]" 
                  v-bind:class="{'clickable': event.url, 'starts': event.startsEvent, 'ends': event.endsEvent, 'withinRange': event.isWithinRange }"
                  v-tooltip="{
                    placement: 'bottom-start',
                    distance: 10,
                    skidding: 0,
                    content: 
                      event.name + ' - ' + 
                      event.startTime + ' ' + (event.endTime ? (' - ' + event.endTime) : '' ) + 
                      (event.notes ? (' - ' + event.notes) : '' )
                  }">
                  <div class="name noscrollbar">{{ event.name }}</div>
                  <div class="badges">
                    <span class="badge-bg text-gray-200" v-for="badge in event.badges"><span class="badge">{{ badge }}</span></span>
                  </div>
                  <div class="content noscrollbar">
                    <template v-if="event.options.displayTimeOnTimelineView">
                      <span class="time" v-if="event.endTime">{{ event.startTime }} - {{ event.endTime }}</span>
                      <span class="time" v-else>{{ event.startTime }}</span>
                    </template>
                    <template v-if="event.options.displayNotesOnTimelineView">
                      <span class="notes">{{ event.notes }}</span>
                    </template>
                  </div>
                </div>
              </template>
              
              </div>
            </div>

          </div>

          <div class="timeline-view">

            <div class="header-container" :style="['grid-template-columns: repeat('+this.gridRows+', 1fr);']">
              <!-- col with timeline labels -->
              <template v-for="slot in dayData.timeline">
                <template v-if="slotIsShown(slot.hour, slot.minute)">
                  <div class="col-label dark:bg-gray-900 border-l border-r dark:border-gray-800" 
                    :style="['grid-column: '+rowForTime(slot.hour, slot.minute)+' / '+rowForTime(slot.hour, (slot.minute + this.dayData.timelineInterval))+';']">{{ slot.hour_minute }}</div>
                </template>
              </template>
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
        
  mounted() {
    this.year = this.proxyYear,
    this.month = this.proxyMonth,
    this.week = this.proxyWeek,
    this.day = this.proxyDay,
    this.reload();
    
    Nova.addShortcut('alt+right', event => {  this.nextDay(); });
    Nova.addShortcut('alt+left', event => {   this.prevDay(); });
    Nova.addShortcut('alt+h', event =>    {   this.reset(); });
  },

  methods: {

    reset() {
      this.loading = true;
      this.year = null;
      this.month = null,
      this.week = null,
      this.day = null;
      this.reload();
    },

    prevDay() {
      this.loading = true;
      this.day -= 1;
      this.reload();
    },
  
    nextDay() {
      this.loading = true;
      this.day += 1;
      this.reload();
    },

    reload() {
      let vue = this;
      Nova.request().get('/nova-vendor/wdelfuego/nova-calendar/calendar-data/day/'+vue.year+'/'+vue.month+'/'+vue.day)
        .then(response => {
            vue.year = response.data.year;
            vue.month = response.data.month;
            vue.week = response.data.week;
            vue.day = response.data.day;
            vue.dayName = response.data.day_name;
            vue.title = response.data.title;
            vue.dayData = response.data.day_data;
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
        loading: null,
        dayName: '',
        title: '',
        dayData: Array(),
        styles: {
          default: { color: '#fff', 'background-color': 'rgba(var(--colors-primary-500), 0.9)' }
        },
    }
  },

  computed: {
    morningOffset() {
      return Math.min((this.dayData.openingHour * 60), this.dayData.earliestEvent) - 30; /* 30 minutes margin for UI purposes */
    },

    eveningOffset() {
      return Math.max((this.dayData.closingHour * 60), this.dayData.latestEvent) + 30; /* 30 minutes margin for UI purposes */
    },

    gridRows() {
      return 144 - ((this.morningOffset + (1440 - this.eveningOffset)) / 10);
    },

    morningOffsetRows() {
      return this.morningOffset / 10;
    }
  }
}
</script>

<style>
/* Scoped Styles */
</style>