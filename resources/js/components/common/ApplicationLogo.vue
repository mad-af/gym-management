<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ImageIcon } from '@/icons';
import type { AppPageProps } from '@/types';

defineProps<{
    collapsed?: boolean;
}>();

interface AppLogo {
    url: string;
    placeholder?: string | null;
}

interface BrandingPageProps extends AppPageProps {
    app?: {
        name?: string;
        logo?: AppLogo | null;
    };
}

const page = usePage<BrandingPageProps>();

const appName = computed(
    () => page.props.app?.name ?? page.props.name ?? 'Gym Management',
);
const appLogo = computed(() => page.props.app?.logo ?? null);
</script>

<template>
    <div class="flex items-center gap-3">
        <img
            v-if="appLogo?.url"
            :src="appLogo.url"
            :alt="appName"
            class="h-10 w-10 rounded-lg object-cover"
        />
        <div
            v-else
            class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400"
            :aria-label="`${appName} logo placeholder`"
            role="img"
        >
            <ImageIcon class="h-5 w-5" />
        </div>

        <span
            v-if="!collapsed"
            class="max-w-[150px] truncate text-sm font-semibold text-gray-800 dark:text-white/90"
        >
            {{ appName }}
        </span>
    </div>
</template>
