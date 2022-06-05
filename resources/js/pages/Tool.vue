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
        
    <Card
      class="flex flex-col items-center justify-center"
      style="min-height: 300px"
    >

      <div class="nova-calendar noselect">

        <div class="nc-header">
          <div v-for="column in $data.columns" class="border-l border-gray-200 dark:border-gray-900 dark:text-gray-300"><span>{{ column }}</span></div>
        </div>

        <div class="nc-content">
          <div v-for="(week, weekIndex) in $data.days" class="week">
            <template v-for="(day, dayIndex) in week">

              <div class="day dark:bg-gray-900 border-t border-l dark:border-gray-800">
                <span v-if="day.isWithinRange" class="daylabel text-gray-400 noselect">{{ day.label }}</span>
                <template v-for="eventBlock in day.eventBlocks">
                  <template v-if="eventBlock.event">
                    <div v-if="day.isWithinRange" @click="open(eventBlock.event.url)" class="nc-event" :style="this.stylesForEvent(eventBlock.event, weekIndex, dayIndex)" v-bind:class="{'clickable': eventBlock.event.url, 'first-day': eventBlock.isFirstDayOfEvent, 'last-day': eventBlock.isLastDayOfEvent }">
                        <span class="name">{{ eventBlock.event.name }}</span>

                        <template v-if="eventBlock.event.options.displayTime">
                          <span class="time" v-if="eventBlock.event.end_time">{{ eventBlock.event.start_time }} - {{ eventBlock.event.end_time }}</span>
                          <span class="time" v-else>{{ eventBlock.event.start_time }}</span>
                        </template>

                        <span class="notes">{{ eventBlock.event.notes }}</span>
                        <div class="badges">
                          <span class="badge bg-gray-100 text-gray-500 dark:text-white" v-for="badge in eventBlock.event.badges">{{ badge }}</span>
                        </div>
                    </div>
                  </template>
                </template>
              </div>

            </template>
          </div>
        </div>

      </div>

<!--      <br/><br/>

        <table class="nova-calendar noselect w-full table py-31 px-6">
            <thead class="bg-gray-100">
                <tr>
                    <th v-for="column in $data.columns" class="border-r border-l border-t border-white dark:border-gray-800 dark:text-gray-300 dark:bg-gray-800"><span>{{ column }}</span></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="week in $data.days" >
                    <td valign="top" v-for="day in week" class="text-center border-r border-t dark:border-gray-800 dark:bg-gray-900" v-bind:class="{'today':day.isToday, 'withinRange':day.isWithinRange, 'weekend':day.isWeekend}">
                      <div>
                          <span v-if="day.isWithinRange" class="daylabel text-gray-400 noselect">{{ day.label }}</span>

                          <template v-for="event in day.events">
                            <div v-if="day.isWithinRange" @click="open(event.url)" class="nc-event" :style="this.stylesForEvent(event)" v-bind:class="{'clickable':event.url}">
                                <span class="name">{{ event.name }}</span>

                                <template v-if="event.options.displayTime">
                                  <span class="time" v-if="event.end_time">{{ event.start_time }} - {{ event.end_time }}</span>
                                  <span class="time" v-else>{{ event.start_time }}</span>
                                </template>

                                <span class="notes">{{ event.notes }}</span>
                                <div class="badges">
                                  <span class="badge bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-white" v-for="badge in event.badges">{{ badge }}</span>
                                </div>
                            </div>
                          </template>
                          
                      </div>
                    </td>
                </tr>
            </tbody>
        </table>
-->

    </Card>
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
            vue.year = response.data.year;
            vue.month = response.data.month;
            vue.title = response.data.title;
            vue.columns = response.data.columns;
            vue.days = response.data.days;
            vue.styles = response.data.styles;
        });
    },
    
    open(url) {
      Nova.visit(url);
    },

    styleForGridPosition(weekIndex, dayIndex)
    { 
      return {
        'grid-row-start': weekIndex + 1,
        'grid-column-start': dayIndex + 1
      };
    },
  
    stylesForEvent(event, weekIndex, dayIndex) {
      return (event.options.style) ? [this.styles.default, this.styles[event.options.style], this.styleForGridPosition(weekIndex, dayIndex)] : this.styles.default;
    }
  
  },

  data () {
      return {
          year: null,
          month: null,
          title: '',
          columns: Array(7).fill('-'),
          days: Array(6).fill(Array(7).fill({})),
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
