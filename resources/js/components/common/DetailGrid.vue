<template>
    <ComponentCard v-if="cardTitle" :title="cardTitle" :desc="cardDesc">
        <template #actions>
            <slot name="actions" />
        </template>

        <div class="space-y-6">
            <div v-for="header in headerItems" :key="header.key ?? header.title"
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div
                        class="h-24 w-24 overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                        <AppImage v-if="headerImageSrc(header)" :src="headerImageSrc(header)"
                            :placeholder="headerImagePlaceholder(header)" :alt="headerTitle(header)"
                            containerClass="h-full w-full rounded-xl"
                            imgClass="h-full w-full object-cover rounded-xl" />
                        <svg v-else class="h-12 w-12 text-gray-400 dark:text-gray-600" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M5 5C4.44772 5 4 5.44772 4 6V18C4 18.5523 4.44772 19 5 19H19C19.5523 19 20 18.5523 20 18V6C20 5.44772 19.5523 5 19 5H5ZM6 7H18V13.5858L15.7071 11.2929C15.3166 10.9024 14.6834 10.9024 14.2929 11.2929L11 14.5858L9.70711 13.2929C9.31658 12.9024 8.68342 12.9024 8.29289 13.2929L6 15.5858V7Z"
                                fill="currentColor" />
                        </svg>
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                            {{ headerTitle(header) }}
                        </h2>
                        <p v-if="headerSubtitle(header)" class="text-sm text-gray-500 dark:text-gray-400">
                            {{ headerSubtitle(header) }}
                        </p>
                        <div v-if="header.badges && header.badges.length" class="mt-3 flex flex-wrap gap-2">
                            <span v-for="badge in header.badges" :key="badge.label" :class="headerBadgeClasses(badge)">
                                {{ badge.label }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="detailItems.length" :class="wrapperClass">
                <div v-for="item in detailItems" :key="item.key ?? item.label" class="space-y-1">
                    <p v-if="item.label" class="mb-1 text-xs leading-normal text-gray-500 dark:text-gray-400">
                        {{ item.label }}
                    </p>
                    <div v-if="item.type === 'image'" class="h-20 w-20 overflow-hidden rounded-lg">
                        <AppImage v-if="imageSrc(item)" :src="imageSrc(item)" :placeholder="imagePlaceholder(item)"
                            :alt="item.imageAlt || item.label" containerClass="h-20 w-20 rounded-lg"
                            imgClass="rounded-lg" />
                    </div>
                    <div v-else-if="item.type === 'user'" class="flex items-center gap-3">
                        <div
                            class="h-8 w-8 overflow-hidden rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                            <AppImage v-if="userAvatarSrc(item)" :src="userAvatarSrc(item)"
                                :placeholder="userAvatarPlaceholder(item)" :alt="userName(item) || item.label"
                                containerClass="h-8 w-8 rounded-full" imgClass="rounded-full" />
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">
                                {{ userName(item) || '-' }}
                            </p>
                            <p v-if="userSecondary(item)" class="truncate text-xs text-gray-500 dark:text-gray-400">
                                {{ userSecondary(item) }}
                            </p>
                        </div>
                    </div>
                    <span v-else-if="item.type === 'badge'" :class="badgeClasses(item)">
                        {{ formatValue(item.display ?? item.value) }}
                    </span>
                    <p v-else class="text-sm font-medium text-gray-800 dark:text-white/90">
                        {{ formatValue(item.value) }}
                    </p>
                </div>
            </div>

            <div v-for="note in noteItems" :key="note.key ?? note.label ?? 'note'" class="pt-2">
                <p v-if="note.label" class="mb-1 text-xs leading-normal text-gray-500 dark:text-gray-400">
                    {{ note.label }}
                </p>
                <p
                    class="w-full rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    {{ formatValue(note.value) }}
                </p>
            </div>
        </div>
    </ComponentCard>

    <div v-else class="space-y-6">
        <div v-for="header in headerItems" :key="header.key ?? header.title"
            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-4">
                <div v-if="headerImageSrc(header)"
                    class="h-14 w-14 overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-800">
                    <AppImage :src="headerImageSrc(header)" :placeholder="headerImagePlaceholder(header)"
                        :alt="headerTitle(header)" containerClass="h-14 w-14 rounded-xl"
                        imgClass="rounded-xl object-cover" />
                </div>
                <div v-else
                    class="flex h-14 w-14 items-center justify-center rounded-xl bg-brand-50 text-lg font-semibold text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                    {{ headerInitial(header) }}
                </div>

                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        {{ headerTitle(header) }}
                    </h2>
                    <p v-if="headerSubtitle(header)" class="text-sm text-gray-500 dark:text-gray-400">
                        {{ headerSubtitle(header) }}
                    </p>
                    <div v-if="header.badges && header.badges.length" class="mt-3 flex flex-wrap gap-2">
                        <span v-for="badge in header.badges" :key="badge.label" :class="headerBadgeClasses(badge)">
                            {{ badge.label }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="detailItems.length" :class="wrapperClass">
            <div v-for="item in detailItems" :key="item.key ?? item.label" class="space-y-1">
                <p v-if="item.label" class="mb-1 text-xs leading-normal text-gray-500 dark:text-gray-400">
                    {{ item.label }}
                </p>
                <div v-if="item.type === 'image'" class="h-20 w-20 overflow-hidden rounded-lg">
                    <AppImage v-if="imageSrc(item)" :src="imageSrc(item)" :placeholder="imagePlaceholder(item)"
                        :alt="item.imageAlt || item.label" containerClass="h-20 w-20 rounded-lg"
                        imgClass="rounded-lg" />
                </div>
                <div v-else-if="item.type === 'user'" class="flex items-center gap-3">
                    <div
                        class="h-8 w-8 overflow-hidden rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500">
                        <AppImage v-if="userAvatarSrc(item)" :src="userAvatarSrc(item)"
                            :placeholder="userAvatarPlaceholder(item)" :alt="userName(item) || item.label"
                            containerClass="h-8 w-8 rounded-full" imgClass="rounded-full" />
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">
                            {{ userName(item) || '-' }}
                        </p>
                        <p v-if="userSecondary(item)" class="truncate text-xs text-gray-500 dark:text-gray-400">
                            {{ userSecondary(item) }}
                        </p>
                    </div>
                </div>
                <span v-else-if="item.type === 'badge'" :class="badgeClasses(item)">
                    {{ formatValue(item.display ?? item.value) }}
                </span>
                <p v-else class="text-sm font-medium text-gray-800 dark:text-white/90">
                    {{ formatValue(item.value) }}
                </p>
            </div>
        </div>

        <div v-for="note in noteItems" :key="note.key ?? note.label ?? 'note'" class="pt-2">
            <p v-if="note.label" class="mb-1 text-xs leading-normal text-gray-500 dark:text-gray-400">
                {{ note.label }}
            </p>
            <p class="w-full rounded-lg bg-gray-50 px-4 py-3 text-sm text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                {{ formatValue(note.value) }}
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import AppImage from '@/components/common/AppImage.vue';
import ComponentCard from '@/components/common/ComponentCard.vue';

type DetailItemType = 'text' | 'image' | 'user' | 'badge' | 'header' | 'note';

interface HeaderBadge {
    label: string;
    class?: string;
}

interface DetailItem {
    key?: string | number;
    type?: DetailItemType;
    label?: string;
    value?: unknown;
    badgeClass?: string;
    display?: string | number | null | undefined;
    imageAlt?: string;
    title?: string;
    subtitle?: string;
    image?: unknown;
    initial?: string;
    badges?: HeaderBadge[];
}

const props = defineProps<{
    items: DetailItem[];
    columns?: 1 | 2 | 3;
    cardTitle?: string;
    cardDesc?: string;
}>();

const headerItems = computed(() => props.items.filter((item) => item.type === 'header'));
const noteItems = computed(() => props.items.filter((item) => item.type === 'note'));
const detailItems = computed(() => props.items.filter((item) => !item.type || (item.type !== 'header' && item.type !== 'note')));

const wrapperClass = computed(() => {
    const cols = props.columns ?? 2;

    if (cols === 1) {
        return 'grid grid-cols-1 gap-5';
    }

    if (cols === 3) {
        return 'grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3';
    }

    return 'grid grid-cols-1 gap-5 sm:grid-cols-2';
});

const headerTitle = (item: DetailItem): string => {
    if (item.title && item.title !== '') {
        return item.title;
    }

    if (typeof item.value === 'string' && item.value !== '') {
        return item.value;
    }

    return '';
};

const headerSubtitle = (item: DetailItem): string => {
    if (item.subtitle && item.subtitle !== '') {
        return item.subtitle;
    }

    return '';
};

const headerInitial = (item: DetailItem): string => {
    if (item.initial && item.initial !== '') {
        return item.initial;
    }

    const title = headerTitle(item);

    if (title) {
        return title.charAt(0).toUpperCase();
    }

    return '';
};

const imageValueSrc = (value: unknown): string => {
    if (!value) {
        return '';
    }

    if (typeof value === 'string') {
        return value;
    }

    if (typeof value === 'object' && 'url' in value) {
        return (value as any).url || '';
    }

    return '';
};

const imageValuePlaceholder = (value: unknown): string => {
    if (!value || typeof value !== 'object' || !('placeholder' in value)) {
        return '';
    }

    return (value as any).placeholder || '';
};

const headerImageSrc = (item: DetailItem): string => {
    return imageValueSrc(item.image);
};

const headerImagePlaceholder = (item: DetailItem): string => {
    return imageValuePlaceholder(item.image);
};

const formatValue = (value: DetailItem['value']) => {
    if (value === null || value === undefined || value === '') {
        return '-';
    }

    if (typeof value === 'number' || typeof value === 'string') {
        return String(value);
    }

    return '-';
};

const imageSrc = (item: DetailItem): string => {
    return imageValueSrc(item.value);
};

const imagePlaceholder = (item: DetailItem): string => {
    return imageValuePlaceholder(item.value);
};

const userAvatarSrc = (item: DetailItem): string => {
    const value = item.value as any;

    if (!value) {
        return '';
    }

    if (value.avatar && typeof value.avatar === 'object') {
        return value.avatar.url || '';
    }

    if (typeof value.avatar === 'string') {
        return value.avatar;
    }

    return '';
};

const userAvatarPlaceholder = (item: DetailItem): string => {
    const value = item.value as any;

    if (!value || !value.avatar || typeof value.avatar !== 'object') {
        return '';
    }

    return value.avatar.placeholder || '';
};

const userName = (item: DetailItem): string => {
    const value = item.value as any;

    if (!value) {
        return '';
    }

    if (typeof value.name === 'string' && value.name !== '') {
        return value.name;
    }

    if (typeof value.email === 'string' && value.email !== '') {
        return value.email;
    }

    return '';
};

const userSecondary = (item: DetailItem): string => {
    const value = item.value as any;

    if (!value) {
        return '';
    }

    if (typeof value.email === 'string' && value.email !== '' && value.email !== userName(item)) {
        return value.email;
    }

    if (typeof value.role === 'string' && value.role !== '') {
        return value.role;
    }

    return '';
};

const badgeClasses = (item: DetailItem): string => {
    const base = 'inline-flex rounded-full px-3 py-1 text-xs font-medium';

    if (item.badgeClass && item.badgeClass !== '') {
        return `${base} ${item.badgeClass}`;
    }

    return `${base} bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300`;
};

const headerBadgeClasses = (badge: HeaderBadge): string => {
    const base = 'inline-flex rounded-full px-3 py-1 text-xs font-medium';

    if (badge.class && badge.class !== '') {
        return `${base} ${badge.class}`;
    }

    return `${base} bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300`;
};
</script>
