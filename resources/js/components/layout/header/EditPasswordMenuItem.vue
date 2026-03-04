<template>
    <li>
        <button @click="openModal"
            class="group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-theme-sm font-medium text-gray-700 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
            <SettingsIcon class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300" />
            Ubah Kata Sandi
        </button>

        <Teleport to="body">
            <Modal v-if="isOpen" :fullScreenBackdrop="true" @close="closeModal">
                <template #body>
                    <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-theme-lg dark:bg-gray-900">
                        <!-- Close button -->
                        <button @click="closeModal"
                            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <CloseIcon class="h-5 w-5" />
                        </button>

                        <h3 class="mb-6 text-lg font-semibold text-gray-800 dark:text-white">
                            Perbarui Kata Sandi
                        </h3>

                        <form @submit.prevent="updatePassword">
                            <div class="space-y-4">
                                <!-- Current Password -->
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Kata Sandi Saat Ini <span class="text-error-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input v-model="form.current_password"
                                            :type="showCurrentPassword ? 'text' : 'password'"
                                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-10 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                            :class="{ 'border-red-500': form.errors.current_password }"
                                            placeholder="Masukkan kata sandi saat ini" />
                                        <button type="button" @click="showCurrentPassword = !showCurrentPassword"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400">
                                            <EyeIcon v-if="!showCurrentPassword" class="h-4 w-4" />
                                            <EyeOffIcon v-else class="h-4 w-4" />
                                        </button>
                                    </div>
                                    <p v-if="form.errors.current_password" class="mt-1 text-xs text-red-500">
                                        {{ form.errors.current_password }}
                                    </p>
                                </div>

                                <!-- New Password -->
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Kata Sandi Baru <span class="text-error-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input v-model="form.password" :type="showNewPassword ? 'text' : 'password'"
                                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-10 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                            :class="{ 'border-red-500': form.errors.password }"
                                            placeholder="Masukkan kata sandi baru" />
                                        <button type="button" @click="showNewPassword = !showNewPassword"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400">
                                            <EyeIcon v-if="!showNewPassword" class="h-4 w-4" />
                                            <EyeOffIcon v-else class="h-4 w-4" />
                                        </button>
                                    </div>
                                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">
                                        {{ form.errors.password }}
                                    </p>
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                        Konfirmasi Kata Sandi <span class="text-error-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input v-model="form.password_confirmation"
                                            :type="showConfirmPassword ? 'text' : 'password'"
                                            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-10 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                                            :class="{ 'border-red-500': form.errors.password_confirmation }"
                                            placeholder="Konfirmasi kata sandi baru" />
                                        <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 dark:text-gray-400">
                                            <EyeIcon v-if="!showConfirmPassword" class="h-4 w-4" />
                                            <EyeOffIcon v-else class="h-4 w-4" />
                                        </button>
                                    </div>
                                    <p v-if="form.errors.password_confirmation" class="mt-1 text-xs text-red-500">
                                        {{ form.errors.password_confirmation }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" @click="closeModal"
                                    class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">
                                    Batal
                                </button>
                                <button type="submit" :disabled="form.processing"
                                    class="flex items-center justify-center rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span v-if="form.processing">Menyimpan...</span>
                                    <span v-else>Simpan Perubahan</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </template>
            </Modal>
        </Teleport>
    </li>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/components/ui/Modal.vue';
import { SettingsIcon, CloseIcon, EyeIcon, EyeOffIcon } from '@/icons';

const isOpen = ref(false);
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const emit = defineEmits(['click']);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const openModal = () => {
    isOpen.value = true;
    emit('click');
};

const closeModal = () => {
    isOpen.value = false;
    form.reset();
    form.clearErrors();
};

const updatePassword = () => {
    form.put('/settings/password', {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
        },
    });
};
</script>
