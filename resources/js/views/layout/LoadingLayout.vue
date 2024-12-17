<script setup>
import { useAppStore } from '@/stores/useApp'
import { Transition, watchEffect } from 'vue';
import ProgressSpinner from 'primevue/progressspinner';
import { storeToRefs } from 'pinia'

const appStore = useAppStore();
const { getPageIsLoading } = storeToRefs(appStore);
</script>


<template>
    <Transition>
        <div v-if="getPageIsLoading" id="page_loading_layout">
            <ProgressSpinner class="page-loading-spinner" strokeWidth="4" fill="transparent"
            animationDuration="2.3s" aria-label="Custom ProgressSpinner" />
        </div>
    </Transition>
</template>

<style>
#page_loading_layout {
    z-index: 999;
    position: fixed;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: white;
    opacity: 1;
}
.page-loading-spinner {
    width: 150px;
    height: 150px;
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
}

</style>
