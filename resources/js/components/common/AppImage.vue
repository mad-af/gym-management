<template>
    <div class="relative overflow-hidden bg-gray-100 dark:bg-gray-800" :class="containerClass">
        <!-- Skeleton Loader (Shimmer) -->
        <div v-if="!isLoaded && !placeholder" class="absolute inset-0 animate-pulse bg-gray-200 dark:bg-gray-700">
        </div>

        <!-- Placeholder Image (Blurry Base64) -->
        <img v-if="placeholder && !isLoaded" :src="placeholder" :alt="alt"
            class="absolute inset-0 h-full w-full object-cover transition-opacity duration-500 blur-sm"
            :class="[imgClass]" />

        <!-- Main Image -->
        <img :src="src" :alt="alt" @load="onLoad" class="h-full w-full object-cover transition-opacity duration-500"
            :class="[imgClass, { 'opacity-0': !isLoaded, 'opacity-100': isLoaded }]" />
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

const props = defineProps({
    src: {
        type: String,
        required: true,
    },
    placeholder: {
        type: String,
        default: '',
    },
    alt: {
        type: String,
        default: '',
    },
    containerClass: {
        type: String,
        default: '',
    },
    imgClass: {
        type: String,
        default: '',
    },
});

const isLoaded = ref(false);

const onLoad = () => {
    isLoaded.value = true;
};

// Reset loaded state if src changes
watch(() => props.src, () => {
    isLoaded.value = false;
});
</script>
