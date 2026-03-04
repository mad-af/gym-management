<template>
    <ComponentCard title="Regular Modal">
        <Button size="sm" @click="openRegularModal">Open Modal</Button>
        <Modal v-if="isRegularModalOpen" :fullScreenBackdrop="true" @close="closeRegularModal">
            <template #body>
                <div class="relative w-full max-w-[600px] rounded-3xl bg-white p-6 lg:p-10 dark:bg-gray-900">
                    <h4 class="mb-7 text-title-sm font-semibold text-gray-800 dark:text-white/90">
                        Modal Heading
                    </h4>
                    <button @click="closeRegularModal"
                        class="absolute top-3 right-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 sm:top-6 sm:right-6 sm:h-11 sm:w-11 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <p class="text-sm leading-6 text-gray-500 dark:text-gray-400">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                        Pellentesque euismod est quis mauris lacinia pharetra.
                        Sed a ligula ac odio condimentum aliquet a nec nulla.
                        Aliquam bibendum ex sit amet ipsum rutrum feugiat
                        ultrices enim quam.
                    </p>
                    <p class="mt-5 text-sm leading-6 text-gray-500 dark:text-gray-400">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                        Pellentesque euismod est quis mauris lacinia pharetra.
                        Sed a ligula ac odio.
                    </p>
                    <div class="mt-8 flex w-full items-center justify-end gap-3">
                        <Button size="sm" variant="outline" @click="closeRegularModal">
                            Close
                        </Button>
                        <Button size="sm" @click="closeRegularModal">
                            Save Changes
                        </Button>
                    </div>
                </div>
            </template>
        </Modal>
    </ComponentCard>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import ComponentCard from '@/components/common/ComponentCard.vue';
import Button from '@/components/ui/Button.vue';
import Modal from '@/components/ui/Modal.vue';

const isRegularModalOpen = ref(false);

const openRegularModal = () => (isRegularModalOpen.value = true);
const closeRegularModal = () => (isRegularModalOpen.value = false);
</script>
