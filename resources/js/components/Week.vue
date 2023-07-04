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
    <div id="nc-control">

        <div class="left-items">
            <a @click="prevWeek" href="#" class="button hover:bg-gray-100 dark:hover:bg-gray-700" title="Alt + ←">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
            </a>

            <a @click="reset" href="#" class="button hover:bg-gray-100 dark:hover:bg-gray-700" title="Alt + H">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="2" fill="currentColor" />
                </svg>
            </a>

            <a @click="nextWeek" href="#" class="button hover:bg-gray-100 dark:hover:bg-gray-700" title="Alt + →">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                </svg>
            </a>

            <h1 @click="reset" class="text-90 font-normal text-xl md:text-2xl noselect">
                <span>{{ $data.title }}</span>
            </h1>

        </div>

        <div class="center-items">
        </div>

        <div class="right-items">

            <Dropdown v-if="Object.keys(availableFilters).length" :handle-internal-clicks="true" :class="{
                'bg-primary-500 hover:bg-primary-600 border-primary-500':
                    activeFilterKey != null,
                'dark:bg-primary-500 dark:hover:bg-primary-600 dark:border-primary-500':
                    activeFilterKey != null,
            }" class="flex h-9 hover:bg-gray-100 dark:hover:bg-gray-700 rounded" dusk="filter-selector">
                <DropdownTrigger :class="{
                    'text-white hover:text-white dark:text-gray-800 dark:hover:text-gray-800':
                        activeFilterKey != null,
                }" class="toolbar-button px-2">
                    <Icon type="filter" />
                    <span v-if="activeFilterKey != null" :class="{
                        'text-white dark:text-gray-800': activeFilterKey != null,
                    }" class="ml-2 font-bold" v-html="activeFilterLabel">
                    </span>
                </DropdownTrigger>

                <template #menu>
                    <DropdownMenu width="260">
                        <ScrollWrap :height="350" class="bg-white dark:bg-gray-900">
                            <div ref="theForm" class="divide-y divide-gray-200 dark:divide-gray-800 divide-solid">
                                <div v-if="activeFilterKey != null" class="bg-gray-100">
                                    <button
                                        class="py-2 w-full block tracking-wide text-center text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 focus:outline-none"
                                        @click="chooseFilter(null)" v-html="$data.resetFiltersLabel">
                                    </button>
                                </div>

                                <div>
                                    <template v-for="(filterLabel, filterKey) in $data.availableFilters">
                                        <button
                                            class="py-2 w-full block dark:bg-gray-800 dark:hover:bg-gray-700 hover:bg-gray-200"
                                            :class="{ 'font-bold': activeFilterKey == filterKey }" v-html="filterLabel"
                                            @click="chooseFilter(filterKey)">
                                        </button>
                                    </template>
                                </div>

                            </div>
                        </ScrollWrap>
                    </DropdownMenu>
                </template>
            </Dropdown>

            <div v-if="calendarViews.length > 1">
                <a v-for="view in calendarViews" href="#" @click="$emit('set-active-view', view)"
                    class="button hover:bg-gray-100 dark:hover:bg-gray-700 ml-2"
                    :class="[{ 'bg-primary-500 text-white': view === 'week' }]">
                    <span class="font-bold capitalize">
                        {{ __(view) }}
                    </span>
                </a>
            </div>
        </div>

    </div>

    <div style="width:100%;overflow:scroll">
        <Card class="flex flex-col items-center justify-center dark:bg-gray-800"
            style="min-height: 100px;min-width:800px;background-color:var(--bg-gray-800)">

            <div class="nova-calendar week-view noselect">

                <div class="nc-header">
                    <div class="border-gray-200 dark:border-gray-900 dark:text-gray-300 nc-col-0">
                        <span>{{ __('W') }}{{ this.week }}</span>
                    </div>
                    <div v-for="(column, index) in $data.columns"
                        class="border-gray-200 dark:border-gray-900 dark:text-gray-300" :class="['nc-col-' + (index + 1)]">
                        <span>{{ column }}</span>
                    </div>
                </div>

                <!-- multi-day events part -->
                <div class="week">
                    <!-- multi-day background -->
                    <div v-for="day in $data.weekData" class="day multi dark:border-gray-800 withinRange"
                        :class="['nc-col-' + day.weekdayColumn]" v-bind:class="{ 'today': day.isToday }">
                    </div>

                    <!-- multi-da events, overlaid -->
                    <div class="week-events">

                        <div class="rotated dark:bg-gray-900 nc-col-0">
                            <div class="rotated-header text-gray-400 noselect relative">
                                <div class="rotated-label dark:bg-gray-900 nc-col-0">{{ __('All day') }}</div>
                            </div>
                        </div>

                        <!-- col with events -->
                        <template v-for="day in $data.weekData">

                            <div :class="['nc-col-' + day.weekdayColumn]" v-bind:class="{ 'today': day.isToday }">
                                <button v-if="this.dayViewEnabled"
                                    @click="$emit('set-active-view', 'day')">
                                    <div class="dayheader text-gray-400 noselect">
                                        <span class="daylabel">{{ day.label }}</span>
                                        <div class="badges noscrollbar">
                                            <span class="badge-bg text-gray-200" v-for="badge in day.badges">
                                                <Tooltip><template #content><span v-html="badge.tooltip"></span></template>
                                                    <span class="badge" v-html="badge.badge"></span>
                                                </Tooltip>
                                            </span>
                                        </div>
                                    </div>
                                </button>
                                <div v-else class="dayheader text-gray-400 noselect">
                                    <span class="daylabel">{{ day.label }}</span>
                                    <div class="badges">
                                        <span class="badge-bg text-gray-200" v-for="badge in day.badges">
                                            <Tooltip><template #content><span v-html="badge.tooltip"></span></template>
                                                <span class="badge" v-html="badge.badge"></span>
                                            </Tooltip>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- multi-day events -->
                            <template v-for="event in day.eventsMultiDay">
                                <div :class="['nc-event', 'multi', 'nc-col-' + day.weekdayColumn, 'span-' + event.spansDaysN]"
                                    @click="open($event, event.url)" :style="this.stylesForEvent(event)"
                                    v-bind:class="{ 'clickable': event.url, 'starts': event.startsEvent, 'ends': event.endsEvent, 'withinRange': event.isWithinRange }">
                                    <div class="name noscrollbar">{{ event.name }}</div>
                                    <div class="badges noscrollbar">
                                        <span class="badge-bg text-gray-200" v-for="badge in event.badges"><span
                                                class="badge" v-html="badge"></span></span>
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

                <!-- single-day events part -->
                <div class="week" :style="['grid-template-rows: repeat(' + this.gridRows + ', 10px);']">

                    <!-- col with hour labels -->
                    <template v-for="slot in $data.timeline">
                        <template v-if="slotIsShown(slot.hour, slot.minute)">
                            <div class="hour-label dark:bg-gray-900 dark:border-gray-800 nc-col-0"
                                :style="['grid-row: ' + rowForTime(slot.hour, slot.minute) + ' / ' + rowForTime(slot.hour, (slot.minute + this.layout.timelineInterval)) + ';']">
                                <div v-if="withinTimeline(slot.hour, slot.minute)">{{ slot.hour_minute }}</div>
                            </div>
                        </template>
                    </template>

                    <!-- cols with single-day events -->
                    <template v-for="day in $data.weekData">

                        <template v-for="slot in $data.timeline">
                            <template v-if="slotIsShown(slot.hour, slot.minute)">
                                <div class="slot dark:border-gray-800 dark:bg-gray-900"
                                    :class="['nc-col-' + day.weekdayColumn]" v-bind:class="{ 'withinRange': slot.is_open }"
                                    :style="['grid-row: ' + rowForTime(slot.hour, slot.minute) + ' / ' + rowForTime(slot.hour, (slot.minute + this.layout.timelineInterval)) + ';']">
                                </div>
                            </template>
                        </template>

                        <div :class="['nc-col-' + day.weekdayColumn]"
                            :style="['grid-row: 1 / span ' + this.gridRows + ';', 'display: grid;', 'grid-template-rows: repeat(' + this.gridRows + ', 10px);']">
                            <template v-for="event in day.eventsSingleDay">
                                <div :class="['nc-event']" @click="open($event, event.url)" 
                                    :style="[
                                        this.stylesForEvent(event),
                                        'grid-row-start: ' + eventStartRow(event.startHour, event.startMinute) + ';',
                                        'grid-row-end: ' + eventEndRow(event.startHour, (event.startMinute + event.durationInMinutes)) + ';'
                                        
                                    ]"
                                    v-bind:class="{ 'clickable': event.url, 'starts': withinView(event.startHour, event.startMinute), 'ends': withinView(event.startHour, event.startMinute + event.durationInMinutes), 'withinRange': event.isWithinRange }">
                                    <div class="name noscrollbar">{{ event.name }}</div>
                                    <div class="badges">
                                        <span class="badge-bg text-gray-200" v-for="badge in event.badges"><span
                                                class="badge" v-html="badge"></span></span>
                                    </div>
                                    <div class="content noscrollbar">
                                        <template v-if="event.options.displayTime">
                                            <span class="time" v-if="event.endTime">{{ event.startTime }}-{{ event.endTime
                                            }}</span>
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
</template>

<script>
export default {

    props: [
        'calendarViews'
    ],

    emits: [
        'set-active-view'
    ],

    mounted() {
        this.init();

        Nova.addShortcut('alt+right', event => { this.nextWeek(); });
        Nova.addShortcut('alt+left', event => { this.prevWeek(); });
        Nova.addShortcut('alt+h', event => { this.reset(); });
    },

    methods: {

        reset() {
            this.year = null;
            this.month = null;
            this.week = null;
            this.reload();
        },

        init() {
            if (this.hasStoredSettings()) {
                this.restoreSettings();
                this.reload(false);
            }
            else {
                this.reload(true);
            }
        },

        prevWeek() {
            this.week -= 1;
            this.reload();
        },

        nextWeek() {
            this.week += 1;
            this.reload();
        },

        reload(isInitRequest = false) {
            let vue = this;
            vue.loading = true;

            // Work out the apiPath from the current Tool path, this works
            // because the ToolServiceProvider enforces that both use the same configurable uri part
            let apiUrl = '/nova-vendor/wdelfuego/nova-calendar' + this.calendarUrl() + '/week?y=' + vue.year + '&w=' + vue.week;
            if (vue.activeFilterKey) {
                apiUrl += '&filter=' + vue.activeFilterKey;
            }
            else if (isInitRequest) {
                apiUrl += '&isInitRequest=1';
            }
            Nova.request().get(apiUrl)
                .then(response => {
                    vue.styles = response.data.styles;
                    vue.year = response.data.year;
                    vue.month = response.data.month;
                    vue.week = response.data.week;
                    vue.resetFiltersLabel = response.data.resetFiltersLabel;
                    vue.availableFilters = response.data.filters;
                    vue.activeFilterKey = response.data.activeFilterKey;
                    vue.shouldShowWeekNumbers = response.data.shouldShowWeekNumbers;
                    vue.title = response.data.title;
                    vue.columns = response.data.columns;
                    vue.layout = response.data.layout;
                    vue.weekData = response.data.weekData;
                    vue.timeline = response.data.timeline;

                    this.setFilter(vue.activeFilterKey);
                    vue.loading = false;
                    this.storeSettings();
                });
        },

        open(e, url) {
            if (e.metaKey || e.ctrlKey) {
                window.open(Nova.url(url))
            } else {
                Nova.visit(url);
            }
        },

        stylesForEvent(event) {
            if (event.options.styles) {
                let out = [this.styles.default];
                event.options.styles.forEach(style => {
                    if (this.styles[style] === undefined) {
                        console.log("[nova-calendar] Unknown custom style name '" + style + "'; does the eventStyles method of your CalendarDataProvider define it properly?");
                    }
                    else {
                        out.push(this.styles[style]);
                    }
                })
                return out;
            } else {
                return this.styles.default;
            }
        },

        chooseFilter(filterKey) {
            this.setFilter(filterKey);
            this.reload();
        },

        setFilter(filterKey) {
            this.activeFilterKey = filterKey;
            for (var filterKey in this.availableFilters) {
                if (this.activeFilterKey == filterKey) {
                    this.activeFilterLabel = this.availableFilters[filterKey];
                }
            }
        },

        calendarUrl() {
            return window.location.pathname.substring(Nova.url('').length);
        },

        storageKey() {
            return 'wdelfuego-nova-calendar-' + this.calendarUrl();
        },

        hasStoredSettings() {
            return (localStorage.getItem(this.storageKey()) !== null);
        },

        storeSettings() {
            localStorage.setItem(this.storageKey(), JSON.stringify({
                year: this.year,
                month: this.month,
                week: this.week,
                activeFilterKey: this.activeFilterKey
            }));
        },

        restoreSettings() {
            const storedData = JSON.parse(localStorage.getItem(this.storageKey()));
            if (storedData) {
                this.year = storedData.year;
                this.month = storedData.month;
                this.week = storedData.week;
                this.activeFilterKey = storedData.activeFilterKey;
            }
        },

        minuteForTime(hour, minute) {
            return hour * 60 + minute;
        },

        rowForTime(hour, minute) {
            return Math.round(this.minuteForTime(hour, minute) / 10) - this.morningOffsetRow + 1;
        },

        eventStartRow(hour, minute) {
            let row = this.rowForTime(hour, minute);

            if (row < 1) {
                return 1;
            }

            if (row > this.gridRows - 2) {
                return this.gridRows - 2;
            }

            return row;
        },

        eventEndRow(hour, minute) {
            let row = this.rowForTime(hour, minute);

            if (row < 3) {
                return 3;
            }

            if (row > this.gridRows) {
                return -1;
            }
            return row;
        },

        withinTimeline(hour, minute) {
            return ((this.minuteForTime(hour, minute) >= this.openingMinute) && (this.minuteForTime(hour, minute) <= this.closingMinute));
        },

        withinView(hour, minute) {
            return ((this.minuteForTime(hour, minute) > this.morningOffset) && (this.minuteForTime(hour, minute) < this.eveningOffset));
        },

        slotIsShown(hour, minute) {
            let slot = this.minuteForTime(hour, minute);
            return ((slot >= this.morningOffset) && (slot < this.eveningOffset));
        }

    },

    data() {
        return {
            loading: true,
            resetFiltersLabel: 'All events',
            availableFilters: {},
            activeFilterKey: null,
            activeFilterLabel: null,
            shouldShowWeekNumbers: false,
            title: '',
            year: null,
            month: null,
            week: null,
            columns: Array(7).fill('-'),
            layout: Array(),
            weekData: (Array(7).fill({})),
            timeline: Array(),
            styles: {
                default: { color: '#fff', 'background-color': 'rgba(var(--colors-primary-500), 0.9)' }
            }
        }
    },

    computed: {
        openingMinute() {
            return this.layout.openingHour * 60
        },

        closingMinute() {
            return this.layout.closingHour * 60
        },

        morningOffset() {
            return this.openingMinute - 60;
        },

        eveningOffset() {
            return this.closingMinute + 60;
        },

        morningOffsetRow() {
            return this.morningOffset / 10;
        },

        eveningOffsetRow() {
            return this.eveningOffset / 10;
        },

        gridRows() {
            return 145 - (this.morningOffsetRow + 145 - this.eveningOffsetRow);
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