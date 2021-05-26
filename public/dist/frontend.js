!function(){"use strict";var t=["second","minute","hour","day","week","month","year"],e=["秒","分钟","小时","天","周","个月","年"],n={},r=function(t,e){n[t]=e},o="timeago-id",i=[60,60,24,7,365/7/12,12];function a(t){return t instanceof Date?t:!isNaN(t)||/^\d+$/.test(t)?new Date(parseInt(t)):(t=(t||"").trim().replace(/\.\d+/,"").replace(/-/,"/").replace(/-/,"/").replace(/(\d)T(\d)/,"$1 $2").replace(/Z/," UTC").replace(/([+-]\d\d):?(\d\d)/," $1$2"),new Date(t))}var u={};function c(t,e,n,r){!function(t){clearTimeout(t),delete u[t]}(function(t){return parseInt(t.getAttribute(o))}(t));var s=r.relativeDate,l=r.minInterval,f=function(t,e){return(+(e?a(e):new Date)-+a(t))/1e3}(e,s);t.innerText=function(t,e){for(var n=t<0?1:0,r=t=Math.abs(t),o=0;t>=i[o]&&o<i.length;o++)t/=i[o];return(t=Math.floor(t))>(0==(o*=2)?9:1)&&(o+=1),e(t,o,r)[n].replace("%s",t.toString())}(f,n);var d=setTimeout((function(){c(t,e,n,r)}),Math.min(1e3*Math.max(function(t){for(var e=1,n=0,r=Math.abs(t);t>=i[n]&&n<i.length;n++)t/=i[n],e*=i[n];return r=(r%=e)?e-r:e,Math.ceil(r)}(f),l||1),2147483647));u[d]=0,function(t,e){t.setAttribute(o,e)}(t,d)}function s(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);e&&(r=r.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),n.push.apply(n,r)}return n}function l(t,e,n){return e in t?Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[e]=n,t}function f(t){return(f="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}r("en_US",(function(e,n){if(0===n)return["just now","right now"];var r=t[Math.floor(n/2)];return e>1&&(r+="s"),[e+" "+r+" ago","in "+e+" "+r]})),r("zh_CN",(function(t,n){if(0===n)return["刚刚","片刻后"];var r=e[~~(n/2)];return[t+" "+r+"前",t+" "+r+"后"]}));var d=encodeURIComponent,p=function(t){return function(t){return Object.keys(t).map((function(e){var n=t[e];return f(n)===f([])?n.map((function(t){return"".concat(e,"[]=").concat(t)})).join("&"):e+"="+t[e]})).join("&")}(function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{},e=t.unix_timestamp_after,n=void 0===e?null:e,r=t.unix_timestamp_before,o=void 0===r?null:r,i=t.tags,a=void 0===i?null:i,u=t.number_of_particles,c=void 0===u?null:u,s=t.order_direction,l=void 0===s?null:s,p=t.output,m=void 0===p?"json":p,v={};if(null!==n&&(v.unix_timestamp_after=parseInt(n)),null!==o&&(v.unix_timestamp_before=parseInt(o)),null!==c&&(v.number_of_particles=parseInt(c)),null!==l&&(v.order_direction=l),null!==a){if(f(a)!==f([]))throw new Error("Tags need to be an array of strings");v.tags=a.map(d)}return"html"!==m&&(m="json"),v.output=m,v}(t))},m=function(t){var e=t.routes,n=t.last_request_timestamp,r=function(t){return t.json().then((function(t){return t.particles}))},o=function(t){return t.json().then((function(t){return n=t.request_timestamp,t.particles}))};return{fetchParticles:function(t){return fetch(e.getParticles+"?"+p(t),{method:"GET"}).then(r).catch(console.error)},fetchParticlesUpdate:function(t){var r=function(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?s(Object(n),!0).forEach((function(e){l(t,e,n[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):s(Object(n)).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))}))}return t}({unix_timestamp_after:n,order_direction:"DESC"},t);return fetch(e.getParticles+"?"+p(r)).then(o).catch(console.error)},resetLastRequestTimestamp:function(){n=0}}};function v(t){return(v="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}var h={},y=function(t){return function(t,e){return"undefined"===v(h[t])&&(h[t]=[]),h[t].push(e),function(){h[t]=h[t].filter((function(t){return t!==e}))}}("particleAdd",t)},b=function(t){return n=t,void(v(h[e="particleAdd"])===v([])&&h[e].forEach((function(t){t(n)})));var e,n};function g(t){return(g="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}function w(t,e){var n;if("undefined"==typeof Symbol||null==t[Symbol.iterator]){if(Array.isArray(t)||(n=_(t))||e&&t&&"number"==typeof t.length){n&&(t=n);var r=0,o=function(){};return{s:o,n:function(){return r>=t.length?{done:!0}:{done:!1,value:t[r++]}},e:function(t){throw t},f:o}}throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}var i,a=!0,u=!1;return{s:function(){n=t[Symbol.iterator]()},n:function(){var t=n.next();return a=t.done,t},e:function(t){u=!0,i=t},f:function(){try{a||null==n.return||n.return()}finally{if(u)throw i}}}}function _(t,e){if(t){if("string"==typeof t)return S(t,e);var n=Object.prototype.toString.call(t).slice(8,-1);return"Object"===n&&t.constructor&&(n=t.constructor.name),"Map"===n||"Set"===n?Array.from(t):"Arguments"===n||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?S(t,e):void 0}}function S(t,e){(null==e||e>t.length)&&(e=t.length);for(var n=0,r=new Array(e);n<e;n++)r[n]=t[n];return r}r("de_DE",(function(t,e,n){return[["jetzt","jetzt"],["vor %s Sekunden","in %s Sekunden"],["vor 1 Minute","in 1 Minute"],["vor %s Minuten","in %s Minuten"],["vor 1 Stunde","in einer Stunde"],["vor %s Stunden","in %s Stunden"],["vor 1 Tag","in 1 Tag"],["vor %s Tagen","in %s Tagen"],["vor 1 Woche","in 1 Woche"],["vor %s Wochen","in %s Wochen"],["vor 1 Monat","in 1 Monat"],["vor %s Monaten","in %s Monaten"],["vor 1 Jahr","in 1 Jahr"],["vor %s Jahren","in %s Jahr"]][e]})),function(t,e){var r=e.selectors,o=e.isFetchUpdatesActive,i=t("body"),a=t("#"+r.rootId),u=t("#"+r.listId),s=t("#"+r.loadMoreId),l={filterVisibleParticles:function(t){return t},filterShowMoreIncrement:function(t){return t},filterHideShowMoreButton:function(t){return t}};if(1===a.length&&1===u.length){var f=m(e),d=[],p=u.children().length,v=function(t,e){return e.created-t.created},h=function(t){var e=t.filter((function(t){return!t.is_deleted})),n=e.map((function(t){return t.id})),r=t.filter((function(t){return t.is_deleted})).map((function(t){return t.id}));d=d.filter(function(t){return function(e){return!t.includes(e.id)}}(r)).filter(function(t){return function(e){return!t.includes(e.id)}}(n));var o,i=w(e);try{for(i.s();!(o=i.n()).done;){var a=o.value;d.push(a)}}catch(t){i.e(t)}finally{i.f()}d.sort(v)},j=function(){return function(t){if(Array.isArray(t))return S(t)}(t=d)||function(t){if("undefined"!=typeof Symbol&&Symbol.iterator in Object(t))return Array.from(t)}(t)||_(t)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.")}();var t},M=function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:5;return p+=t},O=function(){arguments.length>0&&void 0!==arguments[0]||j();var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:p;return l.filterVisibleParticles(j().slice(0,t))};s.length&&s.on("click",(function(t){t.preventDefault(),M(l.filterShowMoreIncrement(5)),A()}));var P=null;i.addClass("live-news-status__is-fetching"),f.fetchParticles({output:"html"}).then((function(t){i.removeClass("live-news-status__is-fetching"),h(t),T()})),I(t(".timeago")),LiveNews.api=f,LiveNews.timeagoize=I,LiveNews.listeners={onAddParticle:y},LiveNews.numberOfVisibleParticles=function(){return p},LiveNews.showMore=M,LiveNews.show=function(t){return p=t},LiveNews.getParticles=j,LiveNews.hooks=l,LiveNews.updateView=A,LiveNews.autoFetchUpdates=function(t){o=t,T()}}else console.error("Missing root and list ids",r);function T(){clearTimeout(P),o&&(i.addClass("live-news-status__is-fetching-update"),f.fetchParticlesUpdate({output:"html"}).then((function(t){i.removeClass("live-news-status__is-fetching-update"),i.addClass("live-news-status__fetched-update"),setTimeout((function(){i.removeClass("live-news-status__fetched-update")}),500),M(t.length),h(t),A()?P=setTimeout(T,5e3):console.error("something went wrong...")})))}function A(){var e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:O(),n=(new Date).getTime();if(g(e)!==g([])||e.length<1)return!1;var r,o=-1,a=w(e);try{for(a.s();!(r=a.n()).done;){var c=r.value;o++;var s=c.id,f=c.html,p=u.find("[data-particle-id=".concat(s,"]"));if(c.is_deleted)p.remove(),o--;else{var m=t(f);m.attr("data-update-timestamp",n),m.attr("data-particle-modified",c.modified),p.length?p.attr("data-particle-modified")!==m.attr("data-particle-modified")?p.replaceWith(m):(p.attr("data-update-timestamp",n),m=p):(u.append(m),b(m));var v=m.index();v!==o&&(0===o?u.prepend(m):m.insertAfter(u.children().get(o-1))),I(m.find(".timeago"))}}}catch(t){a.e(t)}finally{a.f()}var h=0;return u.children().each((function(e,r){var o=t(r);o.attr("data-update-timestamp")!==n+""?o.hide():(o.show(),h++)})),l.filterHideShowMoreButton(h>=d.length)?i.addClass("live-news-status__all-visible"):i.removeClass("live-news-status__all-visible"),!0}function I(t){var e,r,o;e=t.get(),r="de_DE",(e.length?e:[e]).forEach((function(t){c(t,function(t){return t.getAttribute("datetime")}(t),function(t){return n[t]||n.en_US}(r),o||{})}))}}(jQuery,LiveNews)}();
//# sourceMappingURL=frontend.js.map