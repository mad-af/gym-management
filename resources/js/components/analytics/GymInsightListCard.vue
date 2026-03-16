<template>
    <div
        class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-white/[0.03]"
    >
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ title }}
            </h3>
            <p
                v-if="subtitle"
                class="mt-1 text-sm text-gray-500 dark:text-gray-400"
            >
                {{ subtitle }}
            </p>
        </div>

        <div
            v-if="!items.length"
            class="rounded-lg border border-dashed border-gray-200 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400"
        >
            {{ emptyText }}
        </div>

        <div v-else class="space-y-2">
            <div
                v-for="item in items"
                :key="item.id"
                class="rounded-xl border border-gray-100 p-3 dark:border-gray-800"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p
                            class="truncate text-sm font-medium text-gray-900 dark:text-white"
                        >
                            {{ item.title }}
                        </p>
                        <p
                            v-if="item.description"
                            class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400"
                        >
                            {{ item.description }}
                        </p>
                    </div>

                    <span
                        v-if="item.badgeLabel"
                        class="inline-flex shrink-0 items-center rounded-full px-2 py-1 text-xs font-medium"
                        :class="item.badgeClass || defaultBadgeClass"
                    >
                        {{ item.badgeLabel }}
                    </span>
                </div>

                <p
                    v-if="item.valueLabel"
                    class="mt-2 text-sm font-semibold text-gray-800 dark:text-gray-200"
                >
                    {{ item.valueLabel }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
interface InsightListItem {
    id: string | number;
    title: string;
    description?: string;
    valueLabel?: string;
    badgeLabel?: string;
    badgeClass?: string;
}

interface GymInsightListCardProps {
    title: string;
    subtitle?: string;
    items: InsightListItem[];
    emptyText?: string;
}

withDefaults(defineProps<GymInsightListCardProps>(), {
    subtitle: '',
    emptyText: 'Belum ada data.',
});

const defaultBadgeClass =
    'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
</script>
