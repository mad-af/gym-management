<template>
    <FullScreenLayout>
        <div class="relative z-1 bg-white p-6 sm:p-0 dark:bg-gray-900">
            <div
                class="relative flex h-screen w-full flex-col justify-center lg:flex-row dark:bg-gray-900"
            >
                <div class="flex w-full flex-1 flex-col lg:w-1/2">
                    <div class="hidden mx-auto w-full max-w-md pt-10">
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
                            Kembali ke beranda
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
                                Lupa Kata Sandi?
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Masukkan alamat email yang terhubung dengan akun
                                Anda, dan kami akan mengirimkan tautan untuk
                                mengatur ulang kata sandi Anda.
                            </p>
                        </div>

                        <div
                            v-if="status"
                            class="mb-4 text-sm font-medium text-green-600 dark:text-green-400"
                        >
                            {{ status }}
                        </div>

                        <div>
                            <form @submit.prevent="submit">
                                <div class="space-y-5">
                                    <!-- Email -->
                                    <div>
                                        <label
                                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                        >
                                            Email<span class="text-error-500"
                                                >*</span
                                            >
                                        </label>
                                        <input
                                            type="email"
                                            id="email"
                                            v-model="form.email"
                                            name="email"
                                            placeholder="Masukkan email Anda"
                                            class="dark:bg-dark-900 font-noraml h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-left text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                        />
                                        <div
                                            v-if="form.errors.email"
                                            class="mt-1.5 text-sm text-error-500"
                                        >
                                            {{ form.errors.email }}
                                        </div>
                                    </div>

                                    <!-- Button -->
                                    <div>
                                        <button
                                            :disabled="form.processing"
                                            class="flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:opacity-50"
                                        >
                                            <span v-if="form.processing"
                                                >Sedang Mengirim...</span
                                            >
                                            <span v-else
                                                >Kirim Tautan Atur Ulang</span
                                            >
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <div class="mt-5">
                                <p
                                    class="text-center text-sm font-normal text-gray-700 sm:text-start dark:text-gray-400"
                                >
                                    Tunggu, saya ingat kata sandi saya...
                                    <Link
                                        href="/login"
                                        class="text-brand-500 hover:text-brand-600 dark:text-brand-400"
                                    >
                                        Klik disini</Link
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
import { Link, useForm, usePage } from '@inertiajs/vue3';
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

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/forgot-password');
};
</script>
