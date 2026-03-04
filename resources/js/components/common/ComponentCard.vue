<template>
    <div :class="[
        'mb-6 rounded-2xl border border-gray-100 bg-white dark:border-gray-800 dark:bg-white/3',
        className,
    ]">
        <!-- Card Header -->
        <div class="px-4 py-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                        {{ title }}
                    </h3>
                    <p v-if="desc" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ desc }}
                    </p>
                </div>

                <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2">
                    <slot name="actions"></slot>
                </div>
            </div>
        </div>

        <!-- Card Body -->
        <div class="border-t border-gray-100 p-4 sm:p-6 dark:border-gray-800">
            <div class="space-y-5">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
interface Props {
    title: string;
    className?: string;
    desc?: string;
}

defineProps<Props>();
</script>
