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

        <table class="nova-calendar noselect w-full table py-31 px-6">
            <thead class="bg-gray-100">
                <tr>
                    <th v-for="column in $data.columns" class="border-r border-l border-t border-white dark:border-gray-800 dark:text-gray-300 dark:bg-gray-800"><span>{{ column }}</span></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="week in $data.days" >
                    <td valign="top" v-for="day in week" class="text-center border-r border-t border-gray-800 dark:border-gray-800 dark:bg-gray-900" v-bind:class="{'today':day.isToday, 'withinRange':day.isWithinRange, 'weekend':day.isWeekend}">
                      <div>
                          <span v-if="day.isWithinRange" class="daylabel text-gray-400 noselect">{{ day.label }}</span>

                          <template v-for="event in day.events">
                            <div v-if="day.isWithinRange" @click="open(event.url)" class="nc-event" :style="styles.default" v-bind:class="{'clickable':event.url}">
                                <span class="name">{{ event.name }}</span>

                                <template v-if="event.options.displayTime">
                                  <span class="time" v-if="event.end">{{ event.start }} - {{ event.end }}</span>
                                  <span class="time" v-else>{{ event.start }}</span>
                                </template>

                                <span class="notes">{{ event.notes }}</span>
                                <div class="badges">
                                  <span class="badge bg-gray-100 text-gray-400 dark:bg-gray-800" v-for="badge in event.badges">{{ badge }}</span>
                                </div>
                            </div>
                          </template>
                          
                      </div>
                    </td>
                </tr>
            </tbody>
        </table>


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
      Nova.request().get(Nova.config('base')+'/wdelfuego/nova-calendar/calendar-data/'+vue.year+'/'+vue.month)
        .then(response => {
            vue.year = response.data.year;
            vue.month = response.data.month;
            vue.title = response.data.title;
            vue.columns = response.data.columns;
            vue.days = response.data.days;
        });
    },
    
    open(url) {
      Nova.visit(url);
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
