import {render as timeagoRender, register as timeagoRegister} from 'timeago.js';
import {publicApi} from './lib/api';
import {listen, trigger, onAddParticle, triggerAddParticle} from './lib/utils/events';

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

    const $body = $("body");
    const $root = $("#" + selectors.rootId);
    const $list = $("#" + selectors.listId);
    const $loadMore = $("#" + selectors.loadMoreId);

    //------------------------------------------------------------------------
    // hooks
    //------------------------------------------------------------------------
    const hooks = {
        filterVisibleParticles: (particles) => particles,
        filterShowMoreIncrement: (increment) => increment,
        filterHideShowMoreButton: (hide) => hide,
    };

    //------------------------------------------------------------------------
    // if important elements are missing in frontend, we cannot help
    //------------------------------------------------------------------------
    if ($root.length !== 1 || $list.length !== 1) {
        console.error("Missing root and list ids", selectors);
        return;
    }

    //------------------------------------------------------------------------
    // objects and vars
    //------------------------------------------------------------------------
    const api = publicApi(config);
    let particlePool = [];
    let numberOfVisibleParticles = $list.children().length;

    //------------------------------------------------------------------------
    // particle pool functions
    //------------------------------------------------------------------------
    const isNotInDeleted = (deletedIds) => {
        return (particle) => !deletedIds.includes(particle.id);
    };
    const isNotInUpdate = (updateIds) => {
        return (particle) => !updateIds.includes(particle.id);
    };
    const sortByCreatedDate = (a,b) => b.created - a.created;

    const merge = (particles) => {
        const updateParticles = particles.filter(p => !p.is_deleted);
        const updateIds = updateParticles.map(p => p.id);
        const deletedIds = particles.filter(p => p.is_deleted).map(p=> p.id);
        particlePool = particlePool
            .filter(isNotInDeleted(deletedIds))
            .filter(isNotInUpdate(updateIds));
        for(let particle of updateParticles){
            particlePool.push(particle);
        }
        particlePool.sort(sortByCreatedDate);
    };

    const getParticles = () => [...particlePool];

    const show = (num) => numberOfVisibleParticles = num;
    const showMore = (increase = 5)=> numberOfVisibleParticles += increase;

    const getVisibleParticles = (particles = getParticles(), numberOf = numberOfVisibleParticles) => {
        return hooks.filterVisibleParticles(
            getParticles().slice(0, numberOf)
        );
    };

    //------------------------------------------------------------------------
    // loadmore button
    //------------------------------------------------------------------------
    if($loadMore.length){
        $loadMore.on("click", function(e){
            e.preventDefault();
            showMore(hooks.filterShowMoreIncrement(5));
            updateView();
        });
    }

    //------------------------------------------------------------------------
    // start pulling news
    //------------------------------------------------------------------------
    let fetchParticlesTimeout = null;
    function fetchParticlesUpdate() {
        clearTimeout(fetchParticlesTimeout);
        if(!isFetchUpdatesActive) return;

        $body.addClass("live-news-status__is-fetching-update");

        api.fetchParticlesUpdate({
            output: "html",
        }).then(function (particles) {

            $body.removeClass("live-news-status__is-fetching-update");

            $body.addClass("live-news-status__fetched-update");
            setTimeout(function () {
                $body.removeClass("live-news-status__fetched-update");
            }, 500);

            showMore(particles.length);

            // merge into pool
            merge(particles);

            // TODO: watch for problems
            const success = updateView();
            if (success) {
                fetchParticlesTimeout = setTimeout(fetchParticlesUpdate, 5000);
            } else {
                console.error("something went wrong...");
            }
        });
    }

    //------------------------------------------------------------------------
    // load all particles once
    //------------------------------------------------------------------------
    $body.addClass("live-news-status__is-fetching");
    api.fetchParticles({
        output: "html",
    }).then((particles)=>{
        $body.removeClass("live-news-status__is-fetching");
        merge(particles);
        // start fetching updates
        fetchParticlesUpdate();
    });

    //------------------------------------------------------------------------
    // update view with particle modifications
    // 1. take list of particles
    // 2. creates, updates or adds particle to list
    // hides all elements that are in dom but no in particles list
    //------------------------------------------------------------------------
    function updateView(particles = getVisibleParticles()) {

        const updateTimestamp = new Date().getTime();

        if(typeof particles !== typeof [] || particles.length < 1) return false;

        let position = particles.length;
        for (let p of particles.reverse()) {

            position--;

            const {id, html} = p;
            const $particle = $list.find(`[data-particle-id=${id}]`);

            if(p.is_deleted){
                // DELETE
                $particle.remove();
                continue;
            }

            let $newParticle = $(html);
            $newParticle.attr("data-update-timestamp", updateTimestamp);
            $newParticle.attr("data-particle-modified", p.modified);

            // add or modify content
            if($particle.length) {
                if($particle.attr("data-particle-modified") !== $newParticle.attr("data-particle-modified")){
                    // UPDATE
                    $particle.replaceWith($newParticle);
                } else {
                    $particle.attr("data-update-timestamp", updateTimestamp);
                    $newParticle = $particle;
                }
            } else {
                // INSERT
                $list.append($newParticle);

                triggerAddParticle($newParticle);
            }

            // sync order
            const elementPosition = $newParticle.index();
            if(elementPosition !== position) {
                if(position === 0){
                    $list.prepend($newParticle);
                } else {
                    $newParticle.insertAfter($list.children().get(position-1));
                }

            }

            // start timeago
            timeagoize($newParticle.find(".timeago"));
        }

        let visibleCount = 0;
        $list.children().each((index, el)=>{
            const $el = $(el);
            if($el.attr("data-update-timestamp") !== updateTimestamp+""){
                $el.hide();
            } else {
                $el.show();
                visibleCount++;
            }
        });

        console.log(visibleCount, numberOfVisibleParticles)

        if( hooks.filterHideShowMoreButton(visibleCount >= particlePool.length) ){
            $body.addClass("live-news-status__all-visible");
        } else {
            $body.removeClass("live-news-status__all-visible");
        }

        return true;
    }


    //------------------------------------------------------------------------
    // update view with particle modifications
    //------------------------------------------------------------------------
    function timeagoize($dates) {
        timeagoRender($dates.get(), 'de_DE');
    }

    // initial timeago for php rendered elements
    timeagoize($('.timeago'));

    //------------------------------------------------------------------------
    // public object
    //------------------------------------------------------------------------
    LiveNews.api = api;
    LiveNews.timeagoize = timeagoize;
    LiveNews.listeners = {
        onAddParticle,
    };

    LiveNews.numberOfVisibleParticles = ()=> numberOfVisibleParticles;
    LiveNews.showMore = showMore;
    LiveNews.show = show;
    LiveNews.getParticles = getParticles;

    LiveNews.hooks = hooks;
    LiveNews.updateView = updateView;

    /**
     * @param {boolean} isActive
     */
    LiveNews.autoFetchUpdates = (isActive)=>{
        isFetchUpdatesActive = isActive;
        fetchParticlesUpdate();
    };


})(jQuery, LiveNews);