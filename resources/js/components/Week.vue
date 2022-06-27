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

          <div class="nc-header">
            <div class="hour border-gray-200 dark:border-gray-900 dark:text-gray-300"></div>
            <div v-for="column in $data.columns" class="border-gray-200 dark:border-gray-900 dark:text-gray-300"><span>{{ column }}</span></div>
          </div>

          <div class="nc-content">

            <!-- week wrapper -->
            <div class="week">
            
              <!-- a cell per day, background -->

              <template v-for="day in $data.weekData">
                <div class="day multi dark:bg-gray-800 dark:border-gray-800 withinRange" :class="['nc-col-'+day.weekdayColumn]" v-bind:class="{'today': day.isToday }">
                  <div class="dayheader text-gray-400 noselect"><span class="daylabel">{{ day.label }}</span></div>
                </div>
              </template>
              
              <!-- events, overlaid -->
              <div class="week-events multi">
                
                <!-- multi day events for all days first -->
                <div class="day single-day hour-label dark:bg-gray-900 dark:border-gray-800 withinRange nc-col-0">
                  {{ __('multi-day') }}
                </div>
                <template v-for="day in $data.weekData">
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
                
              </div>

            </div>

            <div class="week" v-for="slot in $data.timeslots">
            
              <!-- a cell per day, background -->
              <div class="day single-day hour-label dark:bg-gray-900 dark:border-gray-800 withinRange nc-col-0">
                {{ slot.hour_minute }}
              </div>
              <template v-for="day in $data.weekData">
                <div class="day single-day dark:bg-gray-900 dark:border-gray-800" :class="['nc-col-'+day.weekdayColumn]" v-bind:class="{'withinRange' : slot.is_open, 'today': day.isToday }"></div>
              </template>

              

            </div>

            <div class="week">
            
              <!-- events, overlaid -->
              <div class="week-events">
                
                <!-- then all single day events -->
                <template v-for="day in $data.weekData">
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
    
  </div>
</template>

<script>
export default {
        
  mounted() {
    this.reset();
    
    Nova.addShortcut('alt+right', event => {  this.nextWeek(); });
    Nova.addShortcut('alt+left', event => {   this.prevWeek(); });
    Nova.addShortcut('alt+h', event =>    {   this.reset(); });
  },

  methods: {

    reset() {
      this.week = null;
      this.year = null;
      this.reload();
    },

    prevWeek() {
      this.week -= 1;
      this.reload();
    },
  
    nextWeek() {
      this.week += 1;
      this.reload();
    },

    reload() {
      let vue = this;
      Nova.request().get('/nova-vendor/wdelfuego/nova-calendar/calendar-data/week/'+vue.year+'/'+vue.week)
        .then(response => {
            vue.year = response.data.year;
            vue.week = response.data.week;
            vue.title = response.data.title;
            vue.columns = response.data.columns;
            vue.weekData = response.data.week_data;
            vue.styles = response.data.styles;
            vue.timeslots = response.data.timeslots;
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

  data () {
      return {
          year: null,
          week: null,
          title: '',
          columns: Array(7).fill('-'),
          weekData: (Array(7).fill({})),
          timeslots: Array(),
          styles: {
            default: { color: '#fff', 'background-color': 'rgba(var(--colors-primary-500), 0.9)' }
          },
      }
  }

}
</script>

<style>
/* Scoped Styles */
</style>