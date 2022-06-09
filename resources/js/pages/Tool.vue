<template>
  <div>
    <Head title="Nova Calendar" />

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
      <Card
        class="flex flex-col items-center justify-center"
        style="min-height: 300px;min-width:800px"
      >

        <div class="nova-calendar noselect">

          <div class="nc-header">
            <div v-for="column in $data.columns" class="border-gray-200 dark:border-gray-900 dark:text-gray-300"><span>{{ column }}</span></div>
          </div>

          <div class="nc-content">

            <!-- for every week in the week data -->
            <div v-for="(week, weekIndex) in $data.weeks" class="week">

              <!-- a cell per day, background -->
              <template v-for="day in week">
                <div class="day dark:bg-gray-900 border-t border-l dark:border-gray-800"  :class="['nc-col-'+day.weekdayColumn]" v-bind:class="{'withinRange': day.isWithinRange, 'today': day.isToday }">
                  <div class="dayheader text-gray-400 noselect"><span class="daylabel">{{ day.label }}</span></div>
                </div>
              </template>
              
              <!-- events, overlaid -->
              <div class="week-events">
                
                <!-- multi day events for all days first -->
                <template v-for="day in week">
                  <div v-for="event in day.eventsMultiDay" :class="['nc-event','multi','nc-col-'+day.weekdayColumn]" v-if="day.isWithinRange" @click="open(event.url)" :style="this.stylesForEvent(event)" v-bind:class="{'clickable': event.url }">
                    <div class="badges">
                      <span class="badge bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-white" v-for="badge in event.badges">{{ badge }}</span>
                    </div>
                    <span class="name">{{ event.name }}</span>
                    <template v-if="event.options.displayTime">
                      <span class="time" v-if="event.end_time">{{ event.start_time }} - {{ event.end_time }}</span>
                      <span class="time" v-else>{{ event.start_time }}</span>
                    </template>
                    <span class="notes">{{ event.notes }}</span>
                  </div>
                </template>
                
                <!-- then all single day events -->
                <template v-for="day in week">
                  <div :class="['single-day-events','nc-col-'+day.weekdayColumn]">
                    <template v-for="event in day.eventsSingleDay">
                      <div :class="['nc-event','nc-col-'+event.weekdayColumn]" v-if="day.isWithinRange" @click="open(event.url)" :style="this.stylesForEvent(event)" v-bind:class="{'clickable': event.url }">
                        <div class="badges">
                          <span class="badge bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-white" v-for="badge in event.badges">{{ badge }}</span>
                        </div>
                        <span class="name">{{ event.name }}</span>
                        <template v-if="event.options.displayTime">
                          <span class="time" v-if="event.end_time">{{ event.start_time }} - {{ event.end_time }}</span>
                          <span class="time" v-else>{{ event.start_time }}</span>
                        </template>
                        <span class="notes">{{ event.notes }}</span>
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

    prevMonth() {
      this.month -= 1;
      this.reload();
    },
  
    nextMonth() {
      this.month += 1;
      this.reload();
    },

    reload() {
      let vue = this;
      Nova.request().get('/nova-vendor/wdelfuego/nova-calendar/calendar-data/'+vue.year+'/'+vue.month)
        .then(response => {
            console.log(response.data.weeks);
            vue.year = response.data.year;
            vue.month = response.data.month;
            vue.title = response.data.title;
            vue.columns = response.data.columns;
            vue.weeks = response.data.weeks;
            vue.styles = response.data.styles;
        });
    },
    
    open(url) {
      Nova.visit(url);
    },

    stylesForEvent(event) {
      if(event.options.style) {
        return [this.styles.default, event.options.style];
      } else {
        return this.styles.default;
      }
    }
  
  },

  data () {
      return {
          year: null,
          month: null,
          title: '',
          columns: Array(7).fill('-'),
          weeks: Array(6).fill(Array(7).fill({})),
          styles: {
            default: { color: '#fff', 'background-color': 'rgba(var(--colors-primary-500), 0.7)' }
          }
      }
  }

}
</script>

<style>
/* Scoped Styles */
</style>