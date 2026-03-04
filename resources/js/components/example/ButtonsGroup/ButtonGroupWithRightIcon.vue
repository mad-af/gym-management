<script setup lang="ts">
import { BoxIcon } from '@/icons';
</script>

<template>
    <div class="custom-scrollbar max-w-full overflow-x-auto pb-3 sm:pb-0">
        <div class="min-w-[393px]">
            <div class="inline-flex items-center shadow-theme-xs">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 bg-brand-500 px-4 py-3 text-sm font-medium text-white ring-1 ring-brand-500 transition ring-inset first:rounded-l-lg last:rounded-r-lg hover:bg-brand-500"
                >
                    Button Text
                    <BoxIcon />
                </button>
                <button
                    type="button"
                    class="-ml-px inline-flex items-center gap-2 bg-transparent px-4 py-3 text-sm font-medium text-brand-500 ring-1 ring-brand-500 ring-inset first:rounded-l-lg last:rounded-r-lg hover:bg-brand-500 hover:text-white"
                >
                    Button Text
                    <BoxIcon />
                </button>
                <button
                    type="button"
                    class="-ml-px inline-flex items-center gap-2 bg-transparent px-4 py-3 text-sm font-medium text-brand-500 ring-1 ring-brand-500 ring-inset first:rounded-l-lg last:rounded-r-lg hover:bg-brand-500 hover:text-white"
                >
                    Button Text
                    <BoxIcon />
                </button>
            </div>
        </div>
    </div>
</template>
