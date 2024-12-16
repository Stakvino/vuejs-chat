<script setup>
import Button from "primevue/button";
import NavBar from "../views/layout/NavBar.vue";
import Select from 'primevue/select';
import { ref } from "vue";
import { RouterView } from "vue-router";

</script>

<template>
    <div>
        <NavBar />
        <RouterView></RouterView>
    </div>
</template>
