import {render as timeagoRender, register as timeagoRegister} from 'timeago.js';
import {publicApi} from './lib/api';
import {onAddParticle, triggerAddParticle} from './lib/utils/events';


// the local dict example is below.
const localeFunc = (number, index, total_sec) => {
    // number: the timeago / timein number;
    // index: the index of array below;
    // total_sec: total seconds between date to be formatted and today's date;
    return [
        ['jetzt', 'jetzt'],
        ['vor %s Sekunden', 'in %s Sekunden'],
        ['vor 1 Minute', 'in 1 Minute'],
        ['vor %s Minuten', 'in %s Minuten'],
        ['vor 1 Stunde', 'in einer Stunde'],
        ['vor %s Stunden', 'in %s Stunden'],
        ['vor 1 Tag', 'in 1 Tag'],
        ['vor %s Tagen', 'in %s Tagen'],
        ['vor 1 Woche', 'in 1 Woche'],
        ['vor %s Wochen', 'in %s Wochen'],
        ['vor 1 Monat', 'in 1 Monat'],
        ['vor %s Monaten', 'in %s Monaten'],
        ['vor 1 Jahr', 'in 1 Jahr'],
        ['vor %s Jahren', 'in %s Jahr']
    ][index];
};
// register your locale with timeago
timeagoRegister('de_DE', localeFunc);

(function ($, config) {

    const {selectors} = config;

    let {isFetchUpdatesActive} = config;

    const $root = $("#" + selectors.rootId);
    const $list = $("#" + selectors.listId);

    //------------------------------------------------------------------------
    // if important elements are missing in frontend, we cannot help
    //------------------------------------------------------------------------
    if ($root.length !== 1 || $list.length !== 1) {
        console.error("Missing root and list ids", selectors);
        return;
    }

    const api = publicApi(config);
    LiveNews.api = api;
    LiveNews.timeagoize = timeagoize;
    LiveNews.listeners = {
        onAddParticle,
    };
    /**
     * @param {boolean} isActive
     */
    LiveNews.autoFetchUpdates = (isActive)=>{
        isFetchUpdatesActive = isActive;
        fetchParticlesUpdate();
    };

    //------------------------------------------------------------------------
    // start pulling news
    //------------------------------------------------------------------------
    let fetchParticlesTimeout = null;
    function fetchParticlesUpdate() {
        clearTimeout(fetchParticlesTimeout);
        if(!isFetchUpdatesActive) return;

        api.fetchParticlesUpdate({output: "html"}).then(function (particles) {

            // TODO: refactore
            $(".live-news-status__indicator").addClass("live-news-status__indicator--update");
            setTimeout(function () {
                $(".live-news-status__indicator").removeClass("live-news-status__indicator--update");
            }, 500);

            // TODO: watch for problems
            const success = updateView(particles);
            if (success) {
                fetchParticlesTimeout = setTimeout(fetchParticlesUpdate, 5000);
            } else {
                console.error("something went wrong...");
            }
        });
    }
    fetchParticlesUpdate();

    //------------------------------------------------------------------------
    // update view with particle modifications
    //------------------------------------------------------------------------
    function updateView(particles) {
        for (let p of particles.reverse()) {
            const {id, html} = p;
            const $particle = $list.find(`[data-particle-id=${id}]`);
            const $newParticle = $(html);

            if(p.is_deleted){
                // DELETE
                $particle.remove();
                continue;
            }

            if($particle.length) {
                // UPDATE
                $particle.replaceWith($newParticle);
            } else {
                // INSERT
                $newParticle.data("particle", p);
                $list.prepend($newParticle);
                triggerAddParticle($newParticle);
            }

            // start timeago
            timeagoize($newParticle.find(".timeago"));
        }

        return true;
    }

    //------------------------------------------------------------------------
    // update view with particle modifications
    //------------------------------------------------------------------------
    function timeagoize($dates) {
        timeagoRender($dates, 'de_DE');
    }

    // initial timeago for php rendered elements
    timeagoize($('.timeago'));


})(jQuery, LiveNews);