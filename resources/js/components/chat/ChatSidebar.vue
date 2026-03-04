<template>
    <div
        class="w-full flex-col rounded-2xl border border-gray-200 bg-white xl:flex xl:w-1/4 dark:border-gray-800 dark:bg-white/[0.03]">
        <div v-if="isOpen" class="fixed inset-0 z-999999 bg-gray-900/50 transition-all duration-300"
            @click="toggleSidebar"></div>

        <chat-header @toggle="toggleSidebar" />
        <chat-list :is-open="isOpen" @toggle="toggleSidebar" />
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import ChatHeader from './ChatHeader.vue';
import ChatList from './ChatList.vue';

const isOpen = ref(false);

const toggleSidebar = () => {
    isOpen.value = !isOpen.value;
};
</script>
