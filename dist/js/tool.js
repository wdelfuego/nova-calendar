(()=>{"use strict";var e,t={864:(e,t,n)=>{const r=Vue;var l={id:"nc-control"},o={class:"left-items"},a=[(0,r.createElementVNode)("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[(0,r.createElementVNode)("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M11 19l-7-7 7-7m8 14l-7-7 7-7"})],-1)],i=[(0,r.createElementVNode)("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[(0,r.createElementVNode)("circle",{cx:"12",cy:"12",r:"2",fill:"currentColor"})],-1)],c=[(0,r.createElementVNode)("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-6 w-6",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor","stroke-width":"2"},[(0,r.createElementVNode)("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M13 5l7 7-7 7M5 5l7 7-7 7"})],-1)],s=(0,r.createElementVNode)("div",{class:"center-items"},null,-1),d={class:"right-items"},u=["innerHTML"],m={ref:"theForm",class:"divide-y divide-gray-200 dark:divide-gray-800 divide-solid"},v={key:0,class:"bg-gray-100"},f=["innerHTML"],p=["innerHTML","onClick"],h={style:{width:"100%",overflow:"scroll"}},g={key:0,class:"nova-calendar noselect"},y={class:"nc-header"},k={class:"border-gray-200 dark:border-gray-900 dark:text-gray-300"},b={class:"nc-content"},E={class:"week"},w={class:"dayheader text-gray-400 noselect"},B={class:"daylabel"},N={class:"badges"},C={class:"badge-bg text-gray-200"},x=["innerHTML"],F=["innerHTML"],V={class:"week-events"},L=["onClick"],M={class:"name noscrollbar"},T={class:"badges noscrollbar"},S={class:"badge-bg text-gray-200"},H=["innerHTML"],D={class:"content noscrollbar"},K={key:0,class:"time"},O=["innerHTML"],_=["onClick"],j={class:"name noscrollbar"},A={key:0,class:"badges"},z={class:"badge-bg text-gray-200"},R=["innerHTML"],$={class:"content noscrollbar"},I={key:0,class:"time"},Z={key:1,class:"time"},W=["innerHTML"];const q={mounted:function(){var e=this;this.reset(!0),Nova.addShortcut("alt+right",(function(t){e.nextMonth()})),Nova.addShortcut("alt+left",(function(t){e.prevMonth()})),Nova.addShortcut("alt+h",(function(t){e.reset()}))},methods:{reset:function(){this.month=null,this.year=null,this.reload(!0)},prevMonth:function(){this.month-=1,this.reload()},nextMonth:function(){this.month+=1,this.reload()},reload:function(){var e=this,t=arguments.length>0&&void 0!==arguments[0]&&arguments[0],n=this,r="/nova-vendor/wdelfuego/nova-calendar"+window.location.pathname.substring(Nova.url("").length)+"/month?y="+n.year+"&m="+n.month;n.activeFilterKey?r+="&filter="+n.activeFilterKey:t&&(r+="&isInitRequest=1"),Nova.request().get(r).then((function(t){n.styles=t.data.styles,n.year=t.data.year,n.month=t.data.month,n.windowTitle=t.data.windowTitle,n.resetFiltersLabel=t.data.resetFiltersLabel,n.availableFilters=t.data.filters,n.activeFilterKey=t.data.activeFilterKey,n.title=t.data.title,n.columns=t.data.columns,n.weeks=t.data.weeks,e.setFilter(n.activeFilterKey),n.loading=!1}))},open:function(e,t){e.metaKey||e.ctrlKey?window.open(Nova.url(t)):Nova.visit(t)},stylesForEvent:function(e){var t=this;if(e.options.styles){var n=[this.styles.default];return e.options.styles.forEach((function(e){void 0===t.styles[e]?console.log("[nova-calendar] Unknown custom style name '"+e+"'; does the eventStyles method of your CalendarDataProvider define it properly?"):n.push(t.styles[e])})),n}return this.styles.default},chooseFilter:function(e){this.setFilter(e),this.reload()},setFilter:function(e){for(var e in this.activeFilterKey=e,this.availableFilters)this.activeFilterKey==e&&(this.activeFilterLabel=this.availableFilters[e])}},props:{},data:function(){return{loading:!0,resetFiltersLabel:"All events",availableFilters:{},activeFilterKey:null,activeFilterLabel:null,year:null,month:null,windowTitle:"",title:"",columns:Array(7).fill("-"),weeks:Array(6).fill(Array(7).fill({})),styles:{default:{color:"#fff","background-color":"rgba(var(--colors-primary-500), 0.9)"}}}}};var P=n(379),U=n.n(P),J=n(193),G={insert:"head",singleton:!1};U()(J.Z,G);J.Z.locals;const Q=(0,n(744).Z)(q,[["render",function(e,t,n,q,P,U){var J=this,G=(0,r.resolveComponent)("Head"),Q=(0,r.resolveComponent)("Icon"),X=(0,r.resolveComponent)("DropdownTrigger"),Y=(0,r.resolveComponent)("ScrollWrap"),ee=(0,r.resolveComponent)("DropdownMenu"),te=(0,r.resolveComponent)("Dropdown"),ne=(0,r.resolveComponent)("Tooltip"),re=(0,r.resolveComponent)("Card");return(0,r.openBlock)(),(0,r.createElementBlock)("div",null,[(0,r.createVNode)(G,{title:e.$data.windowTitle||e.$data.title},null,8,["title"]),(0,r.createElementVNode)("div",l,[(0,r.createElementVNode)("div",o,[(0,r.createElementVNode)("a",{onClick:t[0]||(t[0]=function(){return U.prevMonth&&U.prevMonth.apply(U,arguments)}),href:"#",class:"button hover:bg-gray-100 dark:hover:bg-gray-700",title:"Alt + ←"},a),(0,r.createElementVNode)("a",{onClick:t[1]||(t[1]=function(){return U.reset&&U.reset.apply(U,arguments)}),href:"#",class:"button hover:bg-gray-100 dark:hover:bg-gray-700",title:"Alt + H"},i),(0,r.createElementVNode)("a",{onClick:t[2]||(t[2]=function(){return U.nextMonth&&U.nextMonth.apply(U,arguments)}),href:"#",class:"button hover:bg-gray-100 dark:hover:bg-gray-700",title:"Alt + →"},c),(0,r.createElementVNode)("h1",{onClick:t[3]||(t[3]=function(){return U.reset&&U.reset.apply(U,arguments)}),class:"text-90 font-normal text-xl md:text-2xl noselect"},[(0,r.createElementVNode)("span",null,(0,r.toDisplayString)(e.$data.title),1)])]),s,(0,r.createElementVNode)("div",d,[P.availableFilters?((0,r.openBlock)(),(0,r.createBlock)(te,{key:0,"handle-internal-clicks":!0,class:(0,r.normalizeClass)([{"bg-primary-500 hover:bg-primary-600 border-primary-500":null!=P.activeFilterKey,"dark:bg-primary-500 dark:hover:bg-primary-600 dark:border-primary-500":null!=P.activeFilterKey},"flex h-9 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"]),dusk:"filter-selector"},{menu:(0,r.withCtx)((function(){return[(0,r.createVNode)(ee,{width:"260"},{default:(0,r.withCtx)((function(){return[(0,r.createVNode)(Y,{height:350,class:"bg-white dark:bg-gray-900"},{default:(0,r.withCtx)((function(){return[(0,r.createElementVNode)("div",m,[null!=P.activeFilterKey?((0,r.openBlock)(),(0,r.createElementBlock)("div",v,[(0,r.createElementVNode)("button",{class:"py-2 w-full block tracking-wide text-center text-gray-500 dark:bg-gray-800 dark:hover:bg-gray-700 focus:outline-none",onClick:t[4]||(t[4]=function(e){return U.chooseFilter(null)}),innerHTML:e.$data.resetFiltersLabel},null,8,f)])):(0,r.createCommentVNode)("",!0),(0,r.createElementVNode)("div",null,[((0,r.openBlock)(!0),(0,r.createElementBlock)(r.Fragment,null,(0,r.renderList)(e.$data.availableFilters,(function(e,t){return(0,r.openBlock)(),(0,r.createElementBlock)("button",{class:(0,r.normalizeClass)(["py-2 w-full block dark:bg-gray-800 dark:hover:bg-gray-700 hover:bg-gray-200",{"font-bold":P.activeFilterKey==t}]),innerHTML:e,onClick:function(e){return U.chooseFilter(t)}},null,10,p)})),256))])],512)]})),_:1})]})),_:1})]})),default:(0,r.withCtx)((function(){return[(0,r.createVNode)(X,{class:(0,r.normalizeClass)([{"text-white hover:text-white dark:text-gray-800 dark:hover:text-gray-800":null!=P.activeFilterKey},"toolbar-button px-2"])},{default:(0,r.withCtx)((function(){return[(0,r.createVNode)(Q,{type:"filter"}),null!=P.activeFilterKey?((0,r.openBlock)(),(0,r.createElementBlock)("span",{key:0,class:(0,r.normalizeClass)([{"text-white dark:text-gray-800":null!=P.activeFilterKey},"ml-2 font-bold"]),innerHTML:P.activeFilterLabel},null,10,u)):(0,r.createCommentVNode)("",!0)]})),_:1},8,["class"])]})),_:1},8,["class"])):(0,r.createCommentVNode)("",!0)])]),(0,r.createElementVNode)("div",h,[(0,r.createVNode)(re,{class:"flex flex-col items-center justify-center dark:bg-gray-800",style:{"min-height":"300px","min-width":"800px","background-color":"var(--bg-gray-800)"}},{default:(0,r.withCtx)((function(){return[P.loading?(0,r.createCommentVNode)("",!0):((0,r.openBlock)(),(0,r.createElementBlock)("div",g,[(0,r.createElementVNode)("div",y,[((0,r.openBlock)(!0),(0,r.createElementBlock)(r.Fragment,null,(0,r.renderList)(e.$data.columns,(function(e){return(0,r.openBlock)(),(0,r.createElementBlock)("div",k,[(0,r.createElementVNode)("span",null,(0,r.toDisplayString)(e),1)])})),256))]),(0,r.createElementVNode)("div",b,[((0,r.openBlock)(!0),(0,r.createElementBlock)(r.Fragment,null,(0,r.renderList)(e.$data.weeks,(function(e,t){return(0,r.openBlock)(),(0,r.createElementBlock)("div",E,[((0,r.openBlock)(!0),(0,r.createElementBlock)(r.Fragment,null,(0,r.renderList)(e,(function(e){return(0,r.openBlock)(),(0,r.createElementBlock)("div",{class:(0,r.normalizeClass)(["day dark:bg-gray-900 dark:border-gray-800",["nc-col-"+e.weekdayColumn],{withinRange:e.isWithinRange,today:e.isToday}])},[(0,r.createElementVNode)("div",w,[(0,r.createElementVNode)("span",B,(0,r.toDisplayString)(e.label),1),(0,r.createElementVNode)("div",N,[((0,r.openBlock)(!0),(0,r.createElementBlock)(r.Fragment,null,(0,r.renderList)(e.badges,(function(e){return(0,r.openBlock)(),(0,r.createElementBlock)("span",C,[(0,r.createVNode)(ne,null,{content:(0,r.withCtx)((function(){return[(0,r.createElementVNode)("span",{innerHTML:e.tooltip},null,8,x)]})),default:(0,r.withCtx)((function(){return[(0,r.createElementVNode)("span",{class:"badge",innerHTML:e.badge},null,8,F)]})),_:2},1024)])})),256))])])],2)})),256)),(0,r.createElementVNode)("div",V,[((0,r.openBlock)(!0),(0,r.createElementBlock)(r.Fragment,null,(0,r.renderList)(e,(function(e){return(0,r.openBlock)(),(0,r.createElementBlock)(r.Fragment,null,[((0,r.openBlock)(!0),(0,r.createElementBlock)(r.Fragment,null,(0,r.renderList)(e.eventsMultiDay,(function(t){return(0,r.openBlock)(),(0,r.createElementBlock)("div",{class:(0,r.normalizeClass)([["nc-event","multi","nc-col-"+e.weekdayColumn,"span-"+t.spansDaysN],{clickable:t.url,starts:t.startsEvent,ends:t.endsEvent,withinRange:t.isWithinRange}]),onClick:function(e){return U.open(e,t.url)},style:(0,r.normalizeStyle)(J.stylesForEvent(t))},[(0,r.createElementVNode)("div",M,(0,r.toDisplayString)(t.name),1),(0,r.createElementVNode)("div",T,[t.startsEvent?((0,r.openBlock)(!0),(0,r.createElementBlock)(r.Fragment,{key:0},(0,r.renderList)(t.badges,(function(e){return(0,r.openBlock)(),(0,r.createElementBlock)("span",S,[(0,r.createElementVNode)("span",{class:"badge",innerHTML:e},null,8,H)])})),256)):(0,r.createCommentVNode)("",!0)]),(0,r.createElementVNode)("div",D,[t.startsEvent&&t.options.displayTime?((0,r.openBlock)(),(0,r.createElementBlock)("span",K,(0,r.toDisplayString)(t.startTime),1)):(0,r.createCommentVNode)("",!0),t.startsEvent?((0,r.openBlock)(),(0,r.createElementBlock)("span",{key:1,class:"notes",innerHTML:t.notes},null,8,O)):(0,r.createCommentVNode)("",!0)])],14,L)})),256))],64)})),256)),((0,r.openBlock)(!0),(0,r.createElementBlock)(r.Fragment,null,(0,r.renderList)(e,(function(e){return(0,r.openBlock)(),(0,r.createElementBlock)("div",{class:(0,r.normalizeClass)(["single-day-events","nc-col-"+e.weekdayColumn])},[((0,r.openBlock)(!0),(0,r.createElementBlock)(r.Fragment,null,(0,r.renderList)(e.eventsSingleDay,(function(e){return(0,r.openBlock)(),(0,r.createElementBlock)("div",{class:(0,r.normalizeClass)([["nc-event"],{clickable:e.url,starts:e.startsEvent,ends:e.endsEvent,withinRange:e.isWithinRange}]),onClick:function(t){return U.open(t,e.url)},style:(0,r.normalizeStyle)(J.stylesForEvent(e))},[(0,r.createElementVNode)("div",j,(0,r.toDisplayString)(e.name),1),e.badges.length>0?((0,r.openBlock)(),(0,r.createElementBlock)("div",A,[((0,r.openBlock)(!0),(0,r.createElementBlock)(r.Fragment,null,(0,r.renderList)(e.badges,(function(e){return(0,r.openBlock)(),(0,r.createElementBlock)("span",z,[(0,r.createElementVNode)("span",{class:"badge",innerHTML:e},null,8,R)])})),256))])):(0,r.createCommentVNode)("",!0),(0,r.createElementVNode)("div",$,[e.options.displayTime?((0,r.openBlock)(),(0,r.createElementBlock)(r.Fragment,{key:0},[e.endTime?((0,r.openBlock)(),(0,r.createElementBlock)("span",I,(0,r.toDisplayString)(e.startTime)+" - "+(0,r.toDisplayString)(e.endTime),1)):((0,r.openBlock)(),(0,r.createElementBlock)("span",Z,(0,r.toDisplayString)(e.startTime),1))],64)):(0,r.createCommentVNode)("",!0),(0,r.createElementVNode)("span",{class:"notes",innerHTML:e.notes},null,8,W)])],14,_)})),256))],2)})),256))])])})),256))])]))]})),_:1})])])}]]);Nova.booting((function(e,t){Nova.inertia("NovaCalendar",Q)}))},193:(e,t,n)=>{n.d(t,{Z:()=>o});var r=n(645),l=n.n(r)()((function(e){return e[1]}));l.push([e.id,"",""]);const o=l},645:e=>{e.exports=function(e){var t=[];return t.toString=function(){return this.map((function(t){var n=e(t);return t[2]?"@media ".concat(t[2]," {").concat(n,"}"):n})).join("")},t.i=function(e,n,r){"string"==typeof e&&(e=[[null,e,""]]);var l={};if(r)for(var o=0;o<this.length;o++){var a=this[o][0];null!=a&&(l[a]=!0)}for(var i=0;i<e.length;i++){var c=[].concat(e[i]);r&&l[c[0]]||(n&&(c[2]?c[2]="".concat(n," and ").concat(c[2]):c[2]=n),t.push(c))}},t}},762:()=>{},379:(e,t,n)=>{var r,l=function(){return void 0===r&&(r=Boolean(window&&document&&document.all&&!window.atob)),r},o=function(){var e={};return function(t){if(void 0===e[t]){var n=document.querySelector(t);if(window.HTMLIFrameElement&&n instanceof window.HTMLIFrameElement)try{n=n.contentDocument.head}catch(e){n=null}e[t]=n}return e[t]}}(),a=[];function i(e){for(var t=-1,n=0;n<a.length;n++)if(a[n].identifier===e){t=n;break}return t}function c(e,t){for(var n={},r=[],l=0;l<e.length;l++){var o=e[l],c=t.base?o[0]+t.base:o[0],s=n[c]||0,d="".concat(c," ").concat(s);n[c]=s+1;var u=i(d),m={css:o[1],media:o[2],sourceMap:o[3]};-1!==u?(a[u].references++,a[u].updater(m)):a.push({identifier:d,updater:h(m,t),references:1}),r.push(d)}return r}function s(e){var t=document.createElement("style"),r=e.attributes||{};if(void 0===r.nonce){var l=n.nc;l&&(r.nonce=l)}if(Object.keys(r).forEach((function(e){t.setAttribute(e,r[e])})),"function"==typeof e.insert)e.insert(t);else{var a=o(e.insert||"head");if(!a)throw new Error("Couldn't find a style target. This probably means that the value for the 'insert' parameter is invalid.");a.appendChild(t)}return t}var d,u=(d=[],function(e,t){return d[e]=t,d.filter(Boolean).join("\n")});function m(e,t,n,r){var l=n?"":r.media?"@media ".concat(r.media," {").concat(r.css,"}"):r.css;if(e.styleSheet)e.styleSheet.cssText=u(t,l);else{var o=document.createTextNode(l),a=e.childNodes;a[t]&&e.removeChild(a[t]),a.length?e.insertBefore(o,a[t]):e.appendChild(o)}}function v(e,t,n){var r=n.css,l=n.media,o=n.sourceMap;if(l?e.setAttribute("media",l):e.removeAttribute("media"),o&&"undefined"!=typeof btoa&&(r+="\n/*# sourceMappingURL=data:application/json;base64,".concat(btoa(unescape(encodeURIComponent(JSON.stringify(o))))," */")),e.styleSheet)e.styleSheet.cssText=r;else{for(;e.firstChild;)e.removeChild(e.firstChild);e.appendChild(document.createTextNode(r))}}var f=null,p=0;function h(e,t){var n,r,l;if(t.singleton){var o=p++;n=f||(f=s(t)),r=m.bind(null,n,o,!1),l=m.bind(null,n,o,!0)}else n=s(t),r=v.bind(null,n,t),l=function(){!function(e){if(null===e.parentNode)return!1;e.parentNode.removeChild(e)}(n)};return r(e),function(t){if(t){if(t.css===e.css&&t.media===e.media&&t.sourceMap===e.sourceMap)return;r(e=t)}else l()}}e.exports=function(e,t){(t=t||{}).singleton||"boolean"==typeof t.singleton||(t.singleton=l());var n=c(e=e||[],t);return function(e){if(e=e||[],"[object Array]"===Object.prototype.toString.call(e)){for(var r=0;r<n.length;r++){var l=i(n[r]);a[l].references--}for(var o=c(e,t),s=0;s<n.length;s++){var d=i(n[s]);0===a[d].references&&(a[d].updater(),a.splice(d,1))}n=o}}}},744:(e,t)=>{t.Z=(e,t)=>{const n=e.__vccOpts||e;for(const[e,r]of t)n[e]=r;return n}}},n={};function r(e){var l=n[e];if(void 0!==l)return l.exports;var o=n[e]={id:e,exports:{}};return t[e](o,o.exports,r),o.exports}r.m=t,e=[],r.O=(t,n,l,o)=>{if(!n){var a=1/0;for(d=0;d<e.length;d++){for(var[n,l,o]=e[d],i=!0,c=0;c<n.length;c++)(!1&o||a>=o)&&Object.keys(r.O).every((e=>r.O[e](n[c])))?n.splice(c--,1):(i=!1,o<a&&(a=o));if(i){e.splice(d--,1);var s=l();void 0!==s&&(t=s)}}return t}o=o||0;for(var d=e.length;d>0&&e[d-1][2]>o;d--)e[d]=e[d-1];e[d]=[n,l,o]},r.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return r.d(t,{a:t}),t},r.d=(e,t)=>{for(var n in t)r.o(t,n)&&!r.o(e,n)&&Object.defineProperty(e,n,{enumerable:!0,get:t[n]})},r.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{var e={103:0,990:0};r.O.j=t=>0===e[t];var t=(t,n)=>{var l,o,[a,i,c]=n,s=0;if(a.some((t=>0!==e[t]))){for(l in i)r.o(i,l)&&(r.m[l]=i[l]);if(c)var d=c(r)}for(t&&t(n);s<a.length;s++)o=a[s],r.o(e,o)&&e[o]&&e[o][0](),e[o]=0;return r.O(d)},n=self.webpackChunkwdelfuego_nova_calendar=self.webpackChunkwdelfuego_nova_calendar||[];n.forEach(t.bind(null,0)),n.push=t.bind(null,n.push.bind(n))})(),r.O(void 0,[990],(()=>r(864)));var l=r.O(void 0,[990],(()=>r(762)));l=r.O(l)})();