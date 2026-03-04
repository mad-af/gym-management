<template>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90" x-text="pageTitle">
            {{ pageTitle }}
        </h2>
        <nav>
            <ol class="flex items-center gap-1.5">
                <li>
                    <Link class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="/">
                        Home
                        <svg class="stroke-current" width="16" height="16" viewBox="0 0 16 16" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M6.00016 11.3333L9.3335 8.00004L6.00016 4.66671" stroke-width="1.2"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </Link>
                </li>
                <li class="text-sm text-gray-800 dark:text-white/90">
                    {{ pageTitle }}
                </li>
            </ol>
        </nav>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface BreadcrumbProps {
    pageTitle: string;
}

defineProps<BreadcrumbProps>();
</script>
