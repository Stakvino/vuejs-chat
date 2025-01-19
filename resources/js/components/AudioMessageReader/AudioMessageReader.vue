<script setup>
import { Button } from 'primevue';
import { onMounted, ref, useTemplateRef } from 'vue';
import WaveSurfer from 'wavesurfer.js';

const props = defineProps(["filePath"]);

const container = useTemplateRef('container');
let wavesurfer = null;
onMounted(() => {
    wavesurfer = WaveSurfer.create({
        container: container.value,
        waveColor: 'rgb(168 129 175)',
        progressColor: 'rgb(100, 0, 100)',
        height: 40,
        barHeight: 3,
        url: props.filePath,
  });

  wavesurfer.on('finish', () => isPlaying.value = false);
})

const isPlaying = ref(false);
const onAudioPlay = () => {
    if ( wavesurfer.isPlaying() ) {
        wavesurfer.pause();
        isPlaying.value = false;
    }
    else {
        wavesurfer.play();
        isPlaying.value = true;
    }
}

</script>

<template>
    <div class="flex items-center">
        <div>
            <Button @click="onAudioPlay" class="action-button" raised
                :icon="isPlaying ? 'pi pi-pause text-xs' : 'pi pi-play text-xs'" title="Play audio" size="small"
                style="width: 42px; height: 42px; border-radius: 8px 0 0 8px; border: solid 1px #a881af"
            />
        </div>
        <div class="bg-white w-40" style="border-radius: 0 8px 8px 0; border: solid 1px #a881af">
            <div ref="container">

            </div>
        </div>
    </div>
</template>
