<script setup>
import LoadingLayout from "@/views/layout/LoadingLayout.vue";
import NavBar from "@/views/layout/NavBar.vue";
import { RouterView } from "vue-router";
</script>

<template>
    <div>
        <LoadingLayout />
        <NavBar />
        <RouterView ></RouterView>
    </div>
</template>
