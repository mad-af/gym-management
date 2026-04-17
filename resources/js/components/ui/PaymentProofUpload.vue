<template>
    <div class="space-y-2">
        <label
            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
        >
            Bukti Bayar
            <span class="text-xs font-normal text-gray-400"
                >(opsional)</span
            >
        </label>
        <div class="flex items-center gap-3">
            <button
                type="button"
                :disabled="props.disabled"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                @click="openCameraModal"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                </svg>
                Ambil Foto
            </button>

            <span
                v-if="displayName"
                class="text-sm text-gray-500 dark:text-gray-400"
                >{{ displayName }}</span
            >

            <button
                v-if="hasFile"
                type="button"
                :disabled="props.disabled"
                class="inline-flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600 disabled:cursor-not-allowed disabled:opacity-50 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                @click="clearFile"
            >
                <svg
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>
        </div>
        <p v-if="hasPreview" class="mt-2">
            <img
                :src="currentPreview"
                class="h-20 w-auto rounded-lg border border-gray-200 dark:border-gray-700"
                alt="Bukti bayar preview"
            />
        </p>
    </div>

    <Teleport to="body">
        <Modal
            v-if="isCameraModalOpen"
            :fullScreenBackdrop="true"
            @close="closeCameraModal"
        >
            <template #body>
                <div
                    class="relative flex w-full max-w-[480px] flex-col items-center rounded-3xl bg-white p-6 dark:bg-gray-900"
                >
                    <h4
                        class="mb-5 text-title-sm font-semibold text-gray-800 dark:text-white/90"
                    >
                        Ambil Foto Bukti Bayar
                    </h4>
                    <button
                        type="button"
                        class="absolute top-3 right-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                        @click="closeCameraModal"
                    >
                        <svg
                            class="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>

                    <div
                        class="mb-4 w-full overflow-hidden rounded-xl bg-black"
                        :class="capturedImage ? 'aspect-[4/3]' : 'aspect-video'"
                    >
                        <video
                            v-if="!capturedImage"
                            ref="videoRef"
                            autoplay
                            muted
                            playsinline
                            class="h-full w-full object-cover"
                        ></video>
                        <img
                            v-else
                            :src="capturedImage"
                            class="h-full w-full object-contain"
                            alt="Captured"
                        />
                    </div>

                    <p v-if="cameraError" class="mb-3 text-sm text-error-500">
                        {{ cameraError }}
                    </p>

                    <div class="flex w-full gap-3">
                        <button
                            v-if="!capturedImage"
                            type="button"
                            class="flex-1 rounded-lg bg-brand-500 py-3 text-sm font-medium text-white transition-colors hover:bg-brand-600 disabled:opacity-50"
                            :disabled="!cameraStreamActive || cameraCapturing"
                            @click="capturePhoto"
                        >
                            {{
                                !cameraStreamActive
                                    ? 'Memuat kamera...'
                                    : cameraCapturing
                                      ? 'Mengambil foto...'
                                      : '📸 Ambil Foto'
                            }}
                        </button>
                        <button
                            v-if="capturedImage"
                            type="button"
                            class="flex-1 rounded-lg bg-gray-100 py-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                            @click="retakePhoto"
                        >
                            🔄 Foto Ulang
                        </button>
                        <button
                            v-if="capturedImage"
                            type="button"
                            class="flex-1 rounded-lg bg-brand-500 py-3 text-sm font-medium text-white transition-colors hover:bg-brand-600"
                            @click="confirmPhoto"
                        >
                            ✓ Simpan
                        </button>
                        <button
                            type="button"
                            class="flex-1 rounded-lg border border-gray-300 bg-white py-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                            @click="openFilePicker"
                        >
                            📁 Upload dari Galeri
                        </button>
                    </div>
                </div>
            </template>
        </Modal>
    </Teleport>

    <input
        ref="fileInputRef"
        type="file"
        accept="image/*"
        capture="environment"
        class="hidden"
        @change="onFileChange"
    />
</template>

<script setup lang="ts">
import { computed, ref, watch, onUnmounted } from 'vue';
import Modal from '@/components/ui/Modal.vue';

interface Props {
    modelValue?: File | null;
    previewUrl?: string | null;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: null,
    previewUrl: null,
    disabled: false,
});

const emit = defineEmits<{
    (e: 'update:modelValue', file: File | null): void;
    (e: 'change', file: File | null): void;
}>();

const fileInputRef = ref<HTMLInputElement | null>(null);
const videoRef = ref<HTMLVideoElement | null>(null);
const localPreviewUrl = ref<string | null>(null);
const capturedImage = ref<string | null>(null);
const isCameraModalOpen = ref(false);
const cameraStreamActive = ref(false);
const cameraCapturing = ref(false);
const cameraError = ref('');
let mediaStream: MediaStream | null = null;

const hasFile = computed(
    () => !!props.modelValue || !!localPreviewUrl.value,
);
const hasPreview = computed(() => !!currentPreview.value);

const displayName = computed(() => {
    if (localPreviewUrl.value) {
        return props.modelValue?.name ?? 'Foto dipilih';
    }
    return null;
});

const currentPreview = computed(() => {
    if (localPreviewUrl.value) {
        return localPreviewUrl.value;
    }
    return props.previewUrl ?? null;
});

const openCameraModal = () => {
    isCameraModalOpen.value = true;
    capturedImage.value = null;
    cameraError.value = '';
    cameraCapturing.value = false;
};

const closeCameraModal = () => {
    stopCameraStream();
    isCameraModalOpen.value = false;
    capturedImage.value = null;
    cameraError.value = '';
};

const stopCameraStream = () => {
    if (mediaStream) {
        mediaStream.getTracks().forEach((track) => track.stop());
        mediaStream = null;
    }
    cameraStreamActive.value = false;
};

const startCamera = async () => {
    cameraError.value = '';
    try {
        if (!navigator.mediaDevices?.getUserMedia) {
            cameraError.value =
                'Perangkat tidak mendukung akses kamera.';
            return;
        }

        mediaStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment' },
        });

        if (videoRef.value) {
            (videoRef.value as HTMLVideoElement).srcObject = mediaStream;
        }
        cameraStreamActive.value = true;
    } catch {
        cameraError.value =
            'Tidak dapat mengakses kamera. Periksa izin kamera.';
        cameraStreamActive.value = false;
    }
};

const stopCameraBeforeCapture = () => {
    if (mediaStream) {
        mediaStream.getTracks().forEach((track) => track.stop());
        mediaStream = null;
    }
    cameraStreamActive.value = false;
};

const capturePhoto = () => {
    if (!videoRef.value) return;

    const video = videoRef.value;

    if (video.readyState < 2) {
        cameraError.value = 'Kamera belum siap. Tunggu sebentar...';
        return;
    }

    if (video.videoWidth === 0 || video.videoHeight === 0) {
        cameraError.value = 'Kamera belum siap. Tunggu sebentar...';
        return;
    }

    cameraCapturing.value = true;

    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    if (!ctx) {
        cameraError.value = 'Gagal membuat canvas.';
        cameraCapturing.value = false;
        return;
    }

    ctx.drawImage(video, 0, 0);
    capturedImage.value = canvas.toDataURL('image/jpeg', 0.85);
    cameraCapturing.value = false;
    stopCameraStream();
};

const retakePhoto = () => {
    capturedImage.value = null;
    startCamera();
};

const confirmPhoto = () => {
    if (!capturedImage.value || !capturedImage.value.startsWith('data:image')) {
        cameraError.value = 'Gagal memproses foto. Coba foto ulang.';
        return;
    }

    const byteString = atob(capturedImage.value.split(',')[1]);
    const mimeType =
        capturedImage.value
            .split(',')[0]
            .match(/:(.*?);/)?.[1] ?? 'image/jpeg';
    const ab = new ArrayBuffer(byteString.length);
    const ia = new Uint8Array(ab);
    for (let i = 0; i < byteString.length; i++) {
        ia[i] = byteString.charCodeAt(i);
    }
    const blob = new Blob([ab], { type: mimeType });
    const filename = `payment_proof_${Date.now()}.jpg`;
    const file = new File([blob], filename, { type: mimeType });

    if (localPreviewUrl.value) {
        URL.revokeObjectURL(localPreviewUrl.value);
    }
    localPreviewUrl.value = capturedImage.value;
    emit('update:modelValue', file);
    emit('change', file);
    closeCameraModal();
};

const openFilePicker = () => {
    closeCameraModal();
    if (fileInputRef.value) {
        fileInputRef.value.click();
    }
};

const clearFile = () => {
    if (localPreviewUrl.value) {
        URL.revokeObjectURL(localPreviewUrl.value);
        localPreviewUrl.value = null;
    }
    emit('update:modelValue', null);
    emit('change', null);
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

const onFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] ?? null;

    if (!file) {
        return;
    }

    if (localPreviewUrl.value) {
        URL.revokeObjectURL(localPreviewUrl.value);
        localPreviewUrl.value = null;
    }

    localPreviewUrl.value = URL.createObjectURL(file);
    emit('update:modelValue', file);
    emit('change', file);
};

watch(
    () => props.modelValue,
    (newVal) => {
        if (!newVal && localPreviewUrl.value) {
            URL.revokeObjectURL(localPreviewUrl.value);
            localPreviewUrl.value = null;
        }
    },
);

watch(isCameraModalOpen, (open) => {
    if (open) {
        setTimeout(() => {
            startCamera();
        }, 100);
    } else {
        stopCameraStream();
    }
});

onUnmounted(() => {
    stopCameraStream();
    if (localPreviewUrl.value) {
        URL.revokeObjectURL(localPreviewUrl.value);
    }
});
</script>
