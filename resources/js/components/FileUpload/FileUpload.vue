<script setup>
import { onMounted, ref, useTemplateRef } from 'vue';
const props = defineProps(["maxFileSize", "getInputRef"])

const inputRef = useTemplateRef('input-ref');
onMounted(() => props.getInputRef(inputRef))
</script>

<template>
    <input type="file" ref="input-ref" />
</template>
