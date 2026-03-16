<template>
    <FullScreenLayout>
        <div class="relative z-1 bg-white p-6 sm:p-0 dark:bg-gray-900">
            <div
                class="relative flex h-screen w-full flex-col justify-center lg:flex-row dark:bg-gray-900"
            >
                <div class="flex w-full flex-1 flex-col lg:w-1/2">
                    <div class="mx-auto w-full max-w-md pt-10">
                        <Link
                            href="/"
                            class="inline-flex items-center text-sm text-gray-500 transition-colors hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                        >
                            <svg
                                class="stroke-current"
                                xmlns="http://www.w3.org/2000/svg"
                                width="20"
                                height="20"
                                viewBox="0 0 20 20"
                                fill="none"
                            >
                                <path
                                    d="M12.7083 5L7.5 10.2083L12.7083 15.4167"
                                    stroke=""
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            Back to dashboard
                        </Link>
                    </div>
                    <!-- Form -->
                    <div
                        class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center"
                    >
                        <div class="mb-5 sm:mb-8">
                            <h1
                                class="mb-2 text-title-sm font-semibold text-gray-800 sm:text-title-md dark:text-white/90"
                            >
                                Two Step Verification
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                A verification code has been sent to your
                                mobile. Please enter it in the field below.
                            </p>
                        </div>
                        <div>
                            <form>
                                <div class="space-y-5">
                                    <!-- Email -->
                                    <div>
                                        <label
                                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                        >
                                            Type your 6 digits security code
                                        </label>
                                        <div
                                            class="flex gap-2 sm:gap-4"
                                            id="otp-container"
                                        >
                                            <input
                                                type="text"
                                                maxlength="1"
                                                class="dark:bg-dark-900 otp-input h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-center text-xl font-semibold text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                            />
                                            <input
                                                type="text"
                                                maxlength="1"
                                                class="dark:bg-dark-900 otp-input h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-center text-xl font-semibold text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                            />
                                            <input
                                                type="text"
                                                maxlength="1"
                                                class="dark:bg-dark-900 otp-input h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-center text-xl font-semibold text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                            />
                                            <input
                                                type="text"
                                                maxlength="1"
                                                class="dark:bg-dark-900 otp-input h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-center text-xl font-semibold text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                            />
                                            <input
                                                type="text"
                                                maxlength="1"
                                                class="dark:bg-dark-900 otp-input h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-center text-xl font-semibold text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                            />
                                            <input
                                                type="text"
                                                maxlength="1"
                                                class="dark:bg-dark-900 otp-input h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-center text-xl font-semibold text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                            />
                                        </div>
                                    </div>

                                    <!-- Button -->
                                    <div>
                                        <button
                                            class="flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600"
                                        >
                                            Verify My Account
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <div class="mt-5">
                                <p
                                    class="text-center text-sm font-normal text-gray-700 sm:text-start dark:text-gray-400"
                                >
                                    Didn’t get the code?
                                    <a
                                        href="#"
                                        class="text-brand-500 hover:text-brand-600 dark:text-brand-400"
                                        >Resend</a
                                    >
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="relative hidden h-full w-full items-center bg-brand-950 lg:grid lg:w-1/2 dark:bg-white/5"
                >
                    <div class="z-1 flex items-center justify-center">
                        <common-grid-shape />
                        <div class="flex max-w-xs flex-col items-center">
                            <Link href="/" class="mb-4 block">
                                <ApplicationLogo />
                            </Link>
                            <p
                                class="text-center text-base font-semibold text-white"
                            >
                                {{ appName }}
                            </p>
                            <p
                                class="mt-2 text-center text-sm leading-6 text-white/85"
                            >
                                {{ appDescription }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </FullScreenLayout>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ApplicationLogo from '@/components/common/ApplicationLogo.vue';
import CommonGridShape from '@/components/common/CommonGridShape.vue';
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue';
import type { AppPageProps } from '@/types';

interface BrandingPageProps extends AppPageProps {
    app?: {
        name?: string;
        description?: string;
    };
}

const page = usePage<BrandingPageProps>();
const appName = computed(
    () => page.props.app?.name ?? page.props.name ?? 'Gym Management',
);
const appDescription = computed(
    () =>
        page.props.app?.description ??
        'Kelola operasional gym Anda dengan lebih efisien.',
);
</script>
