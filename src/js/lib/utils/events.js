
// ----------------------
// basics
// ----------------------
const _events = {};

export const listen = (on,fn)=>{
    if(typeof _events[on] === typeof undefined){
        _events[on] = [];
    }
    _events[on].push(fn);

    // off function
    return ()=>{
        _events[on] = _events[on].filter((_fn)=> _fn !== fn);
    };
};

export const trigger = (on, payload)=>{
    if(typeof _events[on] !== typeof []) return;
    _events[on].forEach((fn)=>{
        fn(payload);
    });
};

export const onAddParticle = (fn)=> listen("particleAdd", fn);
export const triggerAddParticle = ($particle)=> trigger("particleAdd", $particle);

export const onUpdateParticle = (fn)=> listen("particleUpdate", fn);
export const triggerUpdateParticle = ($particle)=> trigger("particleUpdate", $particle);

export const onRemoveParticle = (fn)=> listen("particleRemove", fn);
export const triggerRemoveParticle = ($particle)=> trigger("particleRemove", $particle);

// ----------------------
// for public free usage
// ----------------------
export default {
    listen,
    trigger,

    onAddParticle,
    triggerAddParticle,

    onUpdateParticle,
    triggerUpdateParticle,

    onRemoveParticle,
    triggerRemoveParticle,
}