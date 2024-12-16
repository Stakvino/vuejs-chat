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
