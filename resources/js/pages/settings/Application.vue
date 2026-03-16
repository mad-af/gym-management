<template>
    <Head title="Setting Aplikasi" />

    <AdminLayout>
        <PageBreadcrumb page-title="Setting Aplikasi" />

        <div
            class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]"
        >
            <form class="space-y-6" @submit.prevent="handleSubmit">
                <div>
                    <label
                        for="app_name"
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >
                        Nama Aplikasi
                    </label>
                    <input
                        id="app_name"
                        v-model="form.app_name"
                        type="text"
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 text-sm text-gray-900 placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                        placeholder="Masukkan nama aplikasi"
                    />
                    <p v-if="errors.app_name" class="mt-2 text-sm text-red-500">
                        {{ errors.app_name }}
                    </p>
                </div>

                <div>
                    <p
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"
                    >
                        Logo Aplikasi
                    </p>

                    <div class="flex items-center gap-4">
                        <AvatarInput
                            v-model="logoFile"
                            :src="currentLogoSrc"
                            :placeholder="currentLogoPlaceholder"
                            :alt="form.app_name"
                            variant="square"
                        />
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Format: JPG, PNG, WEBP. Maksimal 10MB.
                        </p>
                    </div>

                    <p v-if="errors.logo" class="mt-2 text-sm text-red-500">
                        {{ errors.logo }}
                    </p>
                </div>

                <div class="flex justify-end">
                    <Button type="submit" :disabled="processing">
                        {{ processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AvatarInput from '@/components/forms/FormElements/AvatarInput.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import Button from '@/components/ui/Button.vue';

interface MediaItem {
    url: string;
    placeholder?: string | null;
}

interface AppSettingData {
    id: string;
    app_name: string;
    logo: MediaItem | null;
}

interface ApiResponse<T> {
    success: boolean;
    message: string;
    data: T;
    errors?: Record<string, string[]>;
}

interface ValidationErrors {
    app_name?: string;
    logo?: string;
}

const props = defineProps<{
    settings: AppSettingData;
}>();

const form = ref({
    app_name: props.settings.app_name,
});

const logoFile = ref<File | null>(null);
const errors = ref<ValidationErrors>({});
const processing = ref(false);
const currentSetting = ref<AppSettingData>(props.settings);

const currentLogoSrc = computed(() => currentSetting.value.logo?.url ?? null);
const currentLogoPlaceholder = computed(
    () => currentSetting.value.logo?.placeholder ?? '',
);

const handleSubmit = async () => {
    processing.value = true;
    errors.value = {};

    const formData = new FormData();
    formData.append('app_name', form.value.app_name);
    formData.append('_method', 'PUT');

    if (logoFile.value) {
        formData.append('logo', logoFile.value);
    }

    try {
        const response = await axios.post<ApiResponse<AppSettingData>>(
            '/api/app-settings',
            formData,
        );
        currentSetting.value = response.data.data;
        form.value.app_name = response.data.data.app_name;
        logoFile.value = null;
        router.reload();
    } catch (error: unknown) {
        if (axios.isAxiosError(error) && error.response?.status === 422) {
            const validationErrors = (error.response.data.errors ??
                {}) as Record<string, string[]>;

            errors.value = {
                app_name: validationErrors.app_name?.[0],
                logo: validationErrors.logo?.[0],
            };

            return;
        }

        console.error('Failed to save app setting', error);
    } finally {
        processing.value = false;
    }
};
</script>
