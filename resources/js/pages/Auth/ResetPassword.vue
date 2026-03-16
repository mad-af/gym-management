<template>
    <FullScreenLayout>
        <div class="relative z-1 bg-white p-6 sm:p-0 dark:bg-gray-900">
            <div
                class="relative flex h-screen w-full flex-col justify-center lg:flex-row dark:bg-gray-900"
            >
                <div class="flex w-full flex-1 flex-col lg:w-1/2">
                    <!-- Form -->
                    <div
                        class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center"
                    >
                        <div class="mb-5 sm:mb-8">
                            <h1
                                class="mb-2 text-title-sm font-semibold text-gray-800 sm:text-title-md dark:text-white/90"
                            >
                                Atur Ulang Kata Sandi
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Masukkan kata sandi baru Anda untuk mengatur
                                ulang kata sandi akun Anda.
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
                                            readonly
                                            class="dark:bg-dark-900 font-noraml h-11 w-full cursor-not-allowed rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-left text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                        />
                                        <div
                                            v-if="form.errors.email"
                                            class="mt-1.5 text-sm text-error-500"
                                        >
                                            {{ form.errors.email }}
                                        </div>
                                    </div>

                                    <!-- Password -->
                                    <div>
                                        <label
                                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                        >
                                            Kata Sandi<span
                                                class="text-error-500"
                                                >*</span
                                            >
                                        </label>
                                        <div class="relative">
                                            <input
                                                :type="
                                                    showPassword
                                                        ? 'text'
                                                        : 'password'
                                                "
                                                id="password"
                                                v-model="form.password"
                                                name="password"
                                                placeholder="Masukkan kata sandi baru"
                                                class="dark:bg-dark-900 font-noraml h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-left text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                            />
                                            <button
                                                type="button"
                                                @click="
                                                    showPassword = !showPassword
                                                "
                                                class="absolute top-1/2 right-4 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                            >
                                                <EyeIcon
                                                    v-if="!showPassword"
                                                    class="size-5"
                                                />
                                                <EyeOffIcon
                                                    v-else
                                                    class="size-5"
                                                />
                                            </button>
                                        </div>
                                        <div
                                            v-if="form.errors.password"
                                            class="mt-1.5 text-sm text-error-500"
                                        >
                                            {{ form.errors.password }}
                                        </div>
                                    </div>

                                    <!-- Confirm Password -->
                                    <div>
                                        <label
                                            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                        >
                                            Konfirmasi Kata Sandi<span
                                                class="text-error-500"
                                                >*</span
                                            >
                                        </label>
                                        <div class="relative">
                                            <input
                                                :type="
                                                    showConfirmPassword
                                                        ? 'text'
                                                        : 'password'
                                                "
                                                id="password_confirmation"
                                                v-model="
                                                    form.password_confirmation
                                                "
                                                name="password_confirmation"
                                                placeholder="Konfirmasi kata sandi baru"
                                                class="dark:bg-dark-900 font-noraml h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-left text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                            />
                                            <button
                                                type="button"
                                                @click="
                                                    showConfirmPassword =
                                                        !showConfirmPassword
                                                "
                                                class="absolute top-1/2 right-4 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                            >
                                                <EyeIcon
                                                    v-if="!showConfirmPassword"
                                                    class="size-5"
                                                />
                                                <EyeOffIcon
                                                    v-else
                                                    class="size-5"
                                                />
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Button -->
                                    <div>
                                        <button
                                            :disabled="form.processing"
                                            class="flex w-full items-center justify-center rounded-lg bg-brand-500 px-4 py-3 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:opacity-50"
                                        >
                                            <span v-if="form.processing"
                                                >Sedang Mengatur Ulang...</span
                                            >
                                            <span v-else
                                                >Atur Ulang Kata Sandi</span
                                            >
                                        </button>
                                    </div>
                                </div>
                            </form>
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
                                class="text-center text-gray-400 dark:text-white/60"
                            >
                                {{ appName }}
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
import { computed, ref } from 'vue';
import ApplicationLogo from '@/components/common/ApplicationLogo.vue';
import CommonGridShape from '@/components/common/CommonGridShape.vue';
import FullScreenLayout from '@/components/layout/FullScreenLayout.vue';
import { EyeIcon, EyeOffIcon } from '@/icons';
import type { AppPageProps } from '@/types';

interface BrandingPageProps extends AppPageProps {
    app?: {
        name?: string;
    };
}

const page = usePage<BrandingPageProps>();
const appName = computed(
    () => page.props.app?.name ?? page.props.name ?? 'Gym Management',
);

const props = defineProps<{
    email: string;
    token: string;
    status?: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    form.post('/reset-password', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>
