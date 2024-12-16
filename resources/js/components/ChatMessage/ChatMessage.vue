<script setup>
    defineProps(["messageTime", "isSeen", "isMyMessage"])
</script>

<template>
    <div :class="isMyMessage ? 'my-message-container': 'others-message-container'">
        <p>
            <slot></slot>
        </p>
        <div class="flex">
            <div class="ml-auto text-xs message-info" :class="{seen: isSeen}">
                <span class="mr-1 message-time">{{ messageTime }}</span>
                <i v-if="isMyMessage" class="pi pi-check text-xs seen-icon"></i>
                <i v-if="isMyMessage && isSeen" class="pi pi-check text-xs seen-icon relative" style="right: 6px"></i>
            </div>
        </div>
    </div>
</template>
