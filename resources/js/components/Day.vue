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
  <div>
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
        
            <div class="day-view">

              <!-- col with timeslots -->  
              <div class="hour-label dark:bg-gray-900 border-b dark:border-gray-800">multi-day</div>
              <div class="day-events-container">

                <template v-for="event in dayData.eventsMultiDay">
                  <div class="nc-event multi withinRange" @click="open(event.url)" :style="this.stylesForEvent(event)" v-bind:class="{'clickable': event.url, 'starts': event.startsEvent, 'ends': event.endsEvent }">
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
              </div>
            </div>   

            <div id="hour-view" style="display: grid; grid-template-columns: 5em 1fr;" :style="['grid-template-rows: repeat('+this.gridRows+', 10px);']">
              <template v-for="slot in dayData.timeslots">
                <template v-if="slotIsShown(slot.hour, slot.minute)">
                  <div class="hour-label dark:bg-gray-900 border-b dark:border-gray-800" 
                    :style="['grid-row: '+rowForTime(slot.hour, slot.minute)+' / '+rowForTime(slot.hour, (slot.minute + this.dayData.interval))+';']">{{ slot.hour_minute }}</div>
                  <div class="slot dark:bg-gray-900" :class="{'withinRange border-b dark:border-gray-800': slot.is_open}" :style="['grid-row: '+rowForTime(slot.hour, slot.minute)+' / '+rowForTime(slot.hour, (slot.minute + this.dayData.interval))+';']"></div>
                </template>
              </template>
              
              <div id="hour-events-container" :style="['grid-column: 2;', 'grid-row: 1 / span '+this.gridRows+';', 'display: grid;', 'grid-template-rows: repeat('+this.gridRows+', 10px);']">
                <template v-for="event in dayData.eventsSingleDay">
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
            </div>

        </div>         
        
      </Card>
    </div>
  </div>
</template>

<script>
export default {
        
  mounted() {
    this.reset();
    
    Nova.addShortcut('alt+right', event => {  this.nextDay(); });
    Nova.addShortcut('alt+left', event => {   this.prevDay(); });
    Nova.addShortcut('alt+h', event =>    {   this.reset(); });
  },

  methods: {

    reset() {
      this.day = null;
      this.year = null;
      this.reload();
    },

    prevDay() {
      this.day -= 1;
      this.reload();
    },
  
    nextDay() {
      this.day += 1;
      this.reload();
    },

    reload() {
      let vue = this;
      Nova.request().get('/nova-vendor/wdelfuego/nova-calendar/calendar-data/day/'+vue.year+'/'+vue.month+'/'+vue.day)
        .then(response => {
            vue.year = response.data.year;
            vue.month = response.data.month;
            vue.day = response.data.day;
            vue.dayName = response.data.day_name;
            vue.title = response.data.title;
            vue.dayData = response.data.day_data;
            vue.styles = response.data.styles;
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
      return ((slotStart >= this.morningOffset) && (slotStart <= this.eveningOffset));
    }

  },

  data () {
    return {
        year: null,
        month: null,
        day: null,
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
      return Math.min((this.dayData.openingHour * 60), this.dayData.earliestEvent) - 60;
    },

    eveningOffset() {
      return Math.min((this.dayData.closingHour * 60), this.dayData.latestEvent) + 60;
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