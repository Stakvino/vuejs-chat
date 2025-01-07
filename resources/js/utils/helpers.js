import moment from "moment";

export function throttle(fn, wait) {
    let throttled = false;
    return function(...args){
        if(!throttled){
            fn.apply(this,args);
            throttled = true;
            setTimeout(()=>{
                throttled = false;
            }, wait);
        }
    }
}

export function primeVueFormStatesToData(states) {
    const data = {};
    for (const [name, state] of Object.entries(states)) {
        data[name] = state.value;
    }
    return data;
}

export const getLocalMoment = dateTime => {
    const utcDate = moment.utc(dateTime);
    return utcDate.local();
}

export const dateTimeFormat = dateTime => {
    const utcDate = moment.utc(dateTime);
    const localDate = utcDate.local();
    const isSameDay = utcDate.isSame(moment.utc(), 'day');
    return isSameDay ? localDate.format('HH:mm') : localDate.format('DD/MMM/YY');
}
