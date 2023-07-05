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
  <LoadingView :loading="loading">
    <Head :title="$data.windowTitle" />
    
    <component 
      :is="activeView"
      :calendar-views="calendarViews"
      @set-active-view="setActiveView">
    </component>

  </LoadingView>
</template>

<script>

import Month from '../components/Month'
import Week from '../components/Week'

export default {
  components: {
    Month, Week
  },

  data() {
    return {
      loading: true,
      calendarViews: Array(),
      windowTitle: '',
      activeView: null,
    }
  },

  mounted() {
    this.init();
  },

  methods: {
    init() {
      if (this.hasStoredSettings()) {
        this.restoreSettings();
        this.reload(false);
      }
      else {
        this.reload(true);
      }
    },

    reload(isInitRequest = false) {
      let vue = this;
      vue.loading = true;

      let apiUrl = '/nova-vendor/wdelfuego/nova-calendar' + this.calendarUrl() + '/calendar-views/'

      Nova.request().get(apiUrl)
        .then(response => {
          vue.calendarViews = response.data.calendar_views;
          vue.windowTitle = response.data.windowTitle;

          if (isInitRequest) {
            this.setActiveView(vue.calendarViews[0]);
          } else {
            this.setActiveView(this.activeView);
          }

          vue.loading = false;
        });
    },

    setActiveView(view) {
      this.activeView = view;
      this.storeSettings();
    },

    calendarUrl() {
      const url = window.location.pathname.substring(Nova.url('').length);
      return url.startsWith('/') ? url : '/' + url;
    },

    storageKey() {
      return 'wdelfuego-nova-calendar-' + this.calendarUrl() + '-views';
    },

    hasStoredSettings() {
      return (localStorage.getItem(this.storageKey()) !== null);
    },

    storeSettings() {
      localStorage.setItem(this.storageKey(), JSON.stringify({
        activeView: this.activeView
      }));
    },

    restoreSettings() {
      const storedData = JSON.parse(localStorage.getItem(this.storageKey()));
      if (storedData) {
        this.activeView = storedData.activeView;
      }
    },

  }
}

</script>

<style>
/* Scoped Styles */
</style>