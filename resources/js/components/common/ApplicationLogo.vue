<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
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
        <img
            v-else
            src="/images/logo/logo.webp"
            :alt="appName"
            class="h-10 w-auto object-contain"
        />

        <span
            v-if="!collapsed"
            class="max-w-[150px] truncate text-sm font-semibold text-gray-800 dark:text-white/90"
        >
            {{ appName }}
        </span>
    </div>
</template>
