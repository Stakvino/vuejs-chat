import moment from "moment";

export function throttle(fn, wait) {
    let throttled = false;
    return function(...args){
        if(!throttled){
            fn.apply(this,args);
            throttled = true;
            setTimeout(() => {
                throttled = false;
            }, wait);
        }
    }
}

export function debounce(func, timeout = 300){
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => { func.apply(this, args); }, timeout);
    };
}

export function primeVueFormStatesToData(states) {
    const data = {
        "_token": document.querySelector("meta[name=csrf-token]").content
    };
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
    if ( !utcDate.isValid() ) {
        return "";
    }
    const localDate = utcDate.local();
    const isSameDay = utcDate.isSame(moment.utc(), 'day');
    return isSameDay ? localDate.format('HH:mm') : localDate.format('DD/MMM/YY');
}

export const startAudioRecording = async () => {
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        await navigator.mediaDevices
            .getUserMedia( { audio: true, } )
            .then((stream) => {
                const micStream = audioMotion.audioCtx.createMediaStreamSource( stream );
                audioMotion.connectInput( micStream );
                audioMotion.volume = 0;
                return {
                    'media-recorder': new MediaRecorder(stream),
                };
                return new MediaRecorder(stream);
            })
            .catch((err) => false);
  } else {
    return false;
  }
}
