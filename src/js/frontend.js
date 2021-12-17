import {register as timeagoRegister, render as timeagoRender} from 'timeago.js';
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

(function (config) {

    const {selectors} = config;

    let {isFetchUpdatesActive} = config;

    const root = document.getElementById( selectors.rootId);
    const list = document.getElementById( selectors.listId);
    const loadMore = document.getElementById(selectors.loadMoreId);

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
    if (!root || !list) {
        console.error("Missing root and list ids", selectors);
        return;
    }

    //------------------------------------------------------------------------
    // objects and vars
    //------------------------------------------------------------------------
    const api = publicApi(config);
    let particlePool = [];
    let numberOfVisibleParticles = list.childNodes.length;

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
    // load more button
    //------------------------------------------------------------------------
    if(loadMore){
        loadMore.addEventListener("click", (e)=>{
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

        document.body.classList.add("live-news-status__is-fetching-update");

        api.fetchParticlesUpdate({
            output: "html",
        }).then(function (particles) {

            document.body.classList.remove("live-news-status__is-fetching-update");

            document.body.classList.add("live-news-status__fetched-update");
            setTimeout(function () {
                document.body.classList.remove("live-news-status__fetched-update");
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
    document.body.classList.add("live-news-status__is-fetching");
    api.fetchParticles({
        output: "html",
    }).then((particles)=>{
        document.body.classList.remove("live-news-status__is-fetching");
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

        let position = -1;
        for (let p of particles) {

            position++;

            const {id, html} = p;
            const particleNode = list.querySelector(`[data-particle-id="${id}"]`);

            if(p.is_deleted){
                // DELETE
                particleNode.remove();
                position--;
                continue;
            }

            const particleFactory = document.createElement("div");
            particleFactory.innerHTML = html;
            let newParticle = particleFactory.firstChild;
            newParticle.setAttribute("data-update-timestamp", updateTimestamp);
            newParticle.setAttribute("data-particle-modified", p.modified);

            // add or modify content
            if(particleNode) {
                if(particleNode.getAttribute("data-particle-modified") !== newParticle.getAttribute("data-particle-modified")){
                    // UPDATE
                    particleNode.replaceWith(newParticle);
                } else {
                    particleNode.setAttribute("data-update-timestamp", updateTimestamp);
                    newParticle = particleNode;
                }
            } else {
                // INSERT
                list.append(newParticle);

                triggerAddParticle(newParticle);
            }

            // sync order
            const elementPosition = Array.from(newParticle.parentNode.childNodes).indexOf(newParticle);
            if(elementPosition !== position) {
                if(position === 0){
                    list.prepend(newParticle);
                } else {
                    list.insertBefore(newParticle, list.children[position]);
                }

            }

            // start timeago
            timeagoize(newParticle.querySelector(".timeago"));
        }

        let visibleCount = 0;
        for (const child of list.children) {
            if(child.getAttribute("data-update-timestamp") !== updateTimestamp+""){
                child.style.display = "none";
            } else {
                child.style.display = "inherit";
                visibleCount++;
            }
        }

        if( hooks.filterHideShowMoreButton(visibleCount >= particlePool.length) ){
            document.body.classList.add("live-news-status__all-visible");
        } else {
            document.body.classList.remove("live-news-status__all-visible");
        }

        return true;
    }


    //------------------------------------------------------------------------
    // update view with particle modifications
    //------------------------------------------------------------------------
    function timeagoize(node) {
        timeagoRender(node, 'de_DE');
    }

    // initial timeago for php rendered elements
    document.querySelectorAll(".timeago").forEach(timeagoize);

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


})(LiveNews);