<template>
    <ThemeProvider>
        <div class="min-h-screen xl:flex">
            <app-sidebar />
            <Backdrop />
            <div class="flex-1 transition-all duration-300 ease-in-out" :class="[
                isExpanded || isHovered ? 'lg:ml-[290px]' : 'lg:ml-[90px]',
            ]">
                <app-header />
                <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
                    <slot></slot>
                </div>
            </div>
        </div>
        <ToastContainer />
    </ThemeProvider>
</template>

<script setup lang="ts">
import ToastContainer from '@/components/ui/ToastContainer.vue';
import { useSidebarProvider } from '@/composables/useSidebar';
import AppHeader from './AppHeader.vue';
import AppSidebar from './AppSidebar.vue';
import Backdrop from './Backdrop.vue';
import ThemeProvider from './ThemeProvider.vue';

const { isExpanded, isHovered } = useSidebarProvider();
</script>
