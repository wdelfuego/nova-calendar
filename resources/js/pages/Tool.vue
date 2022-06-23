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
    <Head title="Calendar" />

    <div v-if="calendarViews.length > 1" class="flex w-full items-center justify-end mb-4">
        <div class="flex ml-auto items-center">
            <DefaultButton v-for="view in calendarViews" 
                @click="setActiveView(view)" 
                class="ml-2 capitalize" 
                :class="[{ 'bg-nc-primary-400' : view === activeView }]">
                   {{ __(view) }}
            </DefaultButton>
        </div>
    </div>
    
    <component :is="activeView"></component>
  </div>
</template>

<script>

import Month from '../components/Month'

export default {
  components: {
    Month
  },

  props: [
    'tool'
  ],

  data() {
    return {
      calendarViews: Array(),
      activeView: 'month',
    }
  },

  mounted() {
    this.getCalndarViews();
  },

  methods: {
    getCalndarViews() {
        let vue = this;
        Nova.request().get('/nova-vendor/wdelfuego/nova-calendar/calendar-views')
            .then(response => {
                vue.calendarViews = response.data.calendar_views;
        });
    },
    
    setActiveView(view) {
      this.activeView = view;
    }
  }
}
</script>

<style>
/* Scoped Styles */

</style>