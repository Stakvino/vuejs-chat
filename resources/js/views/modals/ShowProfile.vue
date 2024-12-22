<script setup>
import Dialog from 'primevue/dialog';
import { ref } from "vue";

const modalIsVisible = ref(false);
</script>

<template>
    <div>
        <Dialog v-model:visible="modalIsVisible" modal header="Edit Profile" :style="{ width: '25rem' }">

        </Dialog>
    </div>
</template>
