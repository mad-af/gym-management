<template>
    <div>
        <OperationActionButton
            :icon="DoorOpenIcon"
            title="Visits"
            description="Check in kunjungan harian."
            iconBgClass="bg-brand-50 text-brand-600 dark:bg-brand-500/10"
            glowClass="bg-brand-400"
            @click="isOpen = true"
        />
        <Drawer :isOpen="isOpen" title="Visits / Check In" @close="closeDrawer">
            <div class="space-y-6">
                <nav
                    class="flex overflow-x-auto rounded-lg bg-gray-100 p-1 dark:bg-gray-900"
                >
                    <button
                        type="button"
                        @click="setActiveTab('member')"
                        :class="[
                            'inline-flex flex-1 items-center justify-center rounded-md px-3 py-2 text-sm font-medium transition-colors duration-200 ease-in-out',
                            activeTab === 'member'
                                ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-white/[0.03] dark:text-white'
                                : 'bg-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200',
                        ]"
                    >
                        Member
                    </button>
                    <button
                        type="button"
                        @click="setActiveTab('visitor')"
                        :class="[
                            'inline-flex flex-1 items-center justify-center rounded-md px-3 py-2 text-sm font-medium transition-colors duration-200 ease-in-out',
                            activeTab === 'visitor'
                                ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-white/[0.03] dark:text-white'
                                : 'bg-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200',
                        ]"
                    >
                        Visitor
                    </button>
                </nav>

                <div v-if="activeTab === 'member'" class="space-y-6">
                    <div class="space-y-2">
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                            >Scan QR</label
                        >
                        <div class="flex items-start gap-3">
                            <div class="shrink-0">
                                <Button
                                    variant="outline"
                                    :onClick="openScannerModal"
                                    :disabled="scannerBusy || !scannerSupported"
                                >
                                    {{
                                        scannerBusy
                                            ? 'Membuka Kamera...'
                                            : 'Buka Kamera'
                                    }}
                                </Button>
                            </div>
                            <div class="min-w-0">
                                <p
                                    v-if="!scannerSupported"
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    Perangkat tidak mendukung pemindaian.
                                </p>
                                <p
                                    v-else
                                    class="text-xs text-gray-500 dark:text-gray-400"
                                >
                                    Arahkan kamera ke QR, kode terisi otomatis.
                                </p>
                                <p
                                    v-if="scannerError"
                                    class="mt-1 text-xs text-error-500"
                                >
                                    {{ scannerError }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="border-t border-gray-200 dark:border-gray-800"
                    ></div>

                    <div class="space-y-2">
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                            >Kode Member</label
                        >
                        <input
                            type="text"
                            v-model="form.code"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                            placeholder="Masukkan kode member"
                        />
                        <p
                            v-if="errors.code"
                            class="mt-1 text-sm text-error-500"
                        >
                            {{ errors.code }}
                        </p>
                    </div>
                </div>

                <div v-else class="space-y-6">
                    <div class="space-y-2">
                        <label
                            class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                            >Customer</label
                        >
                        <Combobox
                            v-model="visitorCustomerId"
                            :options="customerOptions"
                            labelKey="name"
                            valueKey="id"
                            placeholder="Pilih customer..."
                            :loading="customerLoading"
                            remote
                            actionText="Tambah customer"
                            @action="openCreateCustomerModal"
                            @search="onCustomerSearch"
                            @load-more="onCustomerLoadMore"
                        />
                        <p
                            v-if="errors.customer_id"
                            class="mt-1 text-sm text-error-500"
                        >
                            {{ errors.customer_id }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="lookupLoading"
                    class="rounded-lg border border-gray-200 p-3 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300"
                >
                    {{
                        activeTab === 'member'
                            ? 'Mencari member...'
                            : 'Memuat customer...'
                    }}
                </div>
                <div
                    v-else-if="foundCustomer"
                    class="flex items-center gap-3 rounded-lg border border-gray-200 p-3 dark:border-gray-700"
                >
                    <div
                        class="h-12 w-12 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800"
                    >
                        <AppImage
                            v-if="foundCustomer.avatar"
                            :src="foundCustomer.avatar.url"
                            :alt="foundCustomer.name"
                            containerClass="h-12 w-12 rounded-full"
                            imgClass="rounded-full"
                        />
                        <UserCircleIcon
                            v-else
                            class="m-3 h-6 w-6 text-gray-400 dark:text-gray-500"
                        />
                    </div>
                    <div class="min-w-0">
                        <p
                            class="truncate text-sm font-medium text-gray-800 dark:text-white/90"
                        >
                            {{ foundCustomer.name }}
                        </p>
                        <p
                            class="truncate text-xs text-gray-500 dark:text-gray-400"
                        >
                            {{ foundCustomer.code || '-' }}
                        </p>
                    </div>
                </div>
                <div
                    v-else-if="
                        activeTab === 'member' && form.code && !lookupLoading
                    "
                    class="rounded-lg border border-error-200 bg-error-50 p-3 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-500"
                >
                    Member tidak ditemukan.
                </div>

                <div v-if="activeTab === 'visitor'" class="space-y-2">
                    <label
                        class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                        >Harga Harian</label
                    >
                    <p class="text-sm text-gray-800 dark:text-gray-200">
                        Bayar: {{ formatCurrencyId(form.price) }}
                    </p>
                    <p v-if="errors.price" class="mt-1 text-sm text-error-500">
                        {{ errors.price }}
                    </p>
                </div>
            </div>
            <template #footer>
                <div class="flex w-full justify-end gap-3">
                    <Button variant="outline" :onClick="closeDrawer"
                        >Batal</Button
                    >
                    <Button
                        variant="primary"
                        :onClick="submit"
                        :disabled="submitDisabled"
                    >
                        {{ processing ? 'Menyimpan...' : 'Simpan' }}
                    </Button>
                </div>
            </template>
        </Drawer>

        <Teleport to="body">
            <Modal
                v-if="isCreateCustomerOpen"
                :fullScreenBackdrop="true"
                @close="closeCreateCustomerModal"
            >
                <template #body>
                    <div
                        class="relative w-full max-w-[600px] rounded-3xl bg-white p-6 lg:p-10 dark:bg-gray-900"
                    >
                        <h4
                            class="mb-7 text-title-sm font-semibold text-gray-800 dark:text-white/90"
                        >
                            Tambah Customer
                        </h4>
                        <button
                            @click="closeCreateCustomerModal"
                            class="absolute top-3 right-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 sm:top-6 sm:right-6 sm:h-11 sm:w-11 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                            type="button"
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

                        <div class="space-y-6">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Nama <span class="text-error-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    v-model="createCustomerForm.name"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="Nama customer"
                                />
                                <p
                                    v-if="createCustomerErrors.name"
                                    class="mt-1 text-sm text-error-500"
                                >
                                    {{ createCustomerErrors.name }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Nomor Telepon
                                </label>
                                <input
                                    type="text"
                                    v-model="createCustomerForm.phone"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="Nomor telepon"
                                />
                                <p
                                    v-if="createCustomerErrors.phone"
                                    class="mt-1 text-sm text-error-500"
                                >
                                    {{ createCustomerErrors.phone }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200"
                                >
                                    Email
                                </label>
                                <input
                                    type="email"
                                    v-model="createCustomerForm.email"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                                    placeholder="Email"
                                />
                                <p
                                    v-if="createCustomerErrors.email"
                                    class="mt-1 text-sm text-error-500"
                                >
                                    {{ createCustomerErrors.email }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-8 flex w-full items-center justify-end gap-3"
                        >
                            <Button
                                size="sm"
                                variant="outline"
                                :onClick="closeCreateCustomerModal"
                            >
                                Batal
                            </Button>
                            <Button
                                size="sm"
                                variant="primary"
                                :onClick="submitCreateCustomer"
                                :disabled="createCustomerProcessing"
                            >
                                {{
                                    createCustomerProcessing
                                        ? 'Menyimpan...'
                                        : 'Simpan'
                                }}
                            </Button>
                        </div>
                    </div>
                </template>
            </Modal>
        </Teleport>

        <Teleport to="body">
            <Modal
                v-if="isScannerOpen"
                :fullScreenBackdrop="true"
                @close="closeScannerModal"
            >
                <template #body>
                    <div
                        class="relative w-full max-w-[780px] rounded-3xl bg-white p-6 dark:bg-gray-900"
                    >
                        <h4
                            class="mb-5 text-title-sm font-semibold text-gray-800 dark:text-white/90"
                        >
                            Scan QR Member
                        </h4>
                        <button
                            @click="closeScannerModal"
                            class="absolute top-3 right-3 z-999 flex h-9.5 w-9.5 items-center justify-center rounded-full bg-gray-100 text-gray-400 transition-colors hover:bg-gray-200 hover:text-gray-700 sm:top-6 sm:right-6 sm:h-11 sm:w-11 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                            type="button"
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

                        <div class="space-y-4">
                            <div
                                class="overflow-hidden rounded-xl border border-gray-200 bg-black dark:border-gray-700"
                            >
                                <video
                                    ref="videoRef"
                                    autoplay
                                    muted
                                    playsinline
                                    class="aspect-video w-full"
                                ></video>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Arahkan kamera ke QR code member. Saat
                                terdeteksi, modal akan tertutup otomatis.
                            </p>
                            <p
                                v-if="scannerError"
                                class="text-sm text-error-500"
                            >
                                {{ scannerError }}
                            </p>
                        </div>
                    </div>
                </template>
            </Modal>
        </Teleport>
    </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, onMounted, watch, computed, nextTick } from 'vue';
import AppImage from '@/components/common/AppImage.vue';
import Button from '@/components/ui/Button.vue';
import Combobox from '@/components/ui/Combobox.vue';
import Drawer from '@/components/ui/Drawer.vue';
import Modal from '@/components/ui/Modal.vue';
import OperationActionButton from '@/components/ui/OperationActionButton.vue';
import { DoorOpenIcon, UserCircleIcon } from '@/icons';
import type { AppPageProps } from '@/types';

interface OperationPageProps extends AppPageProps {
    app?: {
        daily_visit_price?: number | string | null;
    };
}

const isOpen = ref(false);
const processing = ref(false);
const activeTab = ref<'member' | 'visitor'>('member');
const form = ref({
    customer_id: '' as string | null | '',
    visit_type: '' as string | null | '',
    price: null as number | null,
    code: '' as string,
});
const errors = ref<Record<string, string>>({});
const page = usePage<OperationPageProps>();

const dailyVisitPrice = computed<number>(() => {
    const raw = page.props.app?.daily_visit_price;
    const parsed = typeof raw === 'number' ? raw : Number(raw ?? 0);

    if (!Number.isFinite(parsed) || parsed < 0) {
        return 0;
    }

    return parsed;
});

const visitorCustomerId = ref<string | null>(null);
const customerOptions = ref<any[]>([]);
const customerLoading = ref(false);
const customerSearch = ref('');
const customerPage = ref(1);
const customerHasMore = ref(true);

const isCreateCustomerOpen = ref(false);
const createCustomerProcessing = ref(false);
const createCustomerForm = ref({
    name: '',
    phone: '',
    email: '',
});
const createCustomerErrors = ref<Record<string, string>>({});

const foundCustomer = ref<any | null>(null);
const lookupLoading = ref(false);
let lookupTimer: any = null;

const lookupCustomerByCode = async () => {
    const code = form.value.code?.trim();
    if (!code) {
        foundCustomer.value = null;
        return;
    }
    lookupLoading.value = true;
    try {
        const { data } = await axios.get('/api/customers', {
            params: { per_page: 1, page: 1, search: code, is_member: 1 },
        });
        const arr = data?.data?.data || data?.data || [];
        const c = Array.isArray(arr) && arr.length ? arr[0] : null;
        if (c) {
            foundCustomer.value = {
                id: c.id,
                name: c.name,
                code: c.code || '',
                avatar: c.avatar ?? null,
            };
            form.value.customer_id = c.id;
        } else {
            foundCustomer.value = null;
        }
    } catch {
        foundCustomer.value = null;
    } finally {
        lookupLoading.value = false;
    }
};

watch(
    () => form.value.code,
    () => {
        if (activeTab.value !== 'member') return;
        if (lookupTimer) clearTimeout(lookupTimer);
        lookupTimer = setTimeout(() => {
            lookupCustomerByCode();
        }, 300);
    },
);

const fetchCustomerOptions = async (reset = false) => {
    if (reset) {
        customerPage.value = 1;
        customerOptions.value = [];
        customerHasMore.value = true;
    }
    if (!customerHasMore.value && !reset) return;
    customerLoading.value = true;
    try {
        const { data } = await axios.get('/api/customers/selection', {
            params: {
                per_page: 20,
                page: customerPage.value,
                search: customerSearch.value || undefined,
            },
        });
        const payload = data?.data;
        const arr = payload?.data || payload || [];
        customerOptions.value = reset
            ? arr
            : [...customerOptions.value, ...arr];
        customerHasMore.value = !!payload?.next_page_url;
        customerPage.value++;
    } finally {
        customerLoading.value = false;
    }
};

const onCustomerSearch = (query: string) => {
    customerSearch.value = query;
    fetchCustomerOptions(true);
};
const onCustomerLoadMore = () => {
    fetchCustomerOptions(false);
};

const openCreateCustomerModal = () => {
    isCreateCustomerOpen.value = true;
    createCustomerForm.value = { name: '', phone: '', email: '' };
    createCustomerErrors.value = {};
    createCustomerProcessing.value = false;
};
const closeCreateCustomerModal = () => {
    isCreateCustomerOpen.value = false;
};

const submitCreateCustomer = async () => {
    createCustomerProcessing.value = true;
    createCustomerErrors.value = {};
    try {
        const { data } = await axios.post('/api/customers', {
            name: createCustomerForm.value.name,
            phone: createCustomerForm.value.phone || undefined,
            email: createCustomerForm.value.email || undefined,
        });
        const c = data?.data || data;
        customerOptions.value = [
            { id: c.id, name: c.name },
            ...customerOptions.value.filter((it: any) => it.id !== c.id),
        ];
        visitorCustomerId.value = c.id;
        foundCustomer.value = {
            id: c.id,
            name: c.name,
            code: c.code || '',
            avatar: c.avatar ?? null,
        };
        form.value.customer_id = c.id;
        closeCreateCustomerModal();
    } catch (e: any) {
        if (e.response?.status === 422 && e.response.data?.errors) {
            const ers = e.response.data.errors;
            createCustomerErrors.value = Object.keys(ers).reduce(
                (acc: any, k) => {
                    acc[k] = Array.isArray(ers[k]) ? ers[k][0] : String(ers[k]);
                    return acc;
                },
                {},
            );
        } else {
            console.error(e);
            alert('Gagal membuat customer.');
        }
    } finally {
        createCustomerProcessing.value = false;
    }
};

const fetchCustomerDetail = async (customerId: string) => {
    lookupLoading.value = true;
    try {
        const { data } = await axios.get(`/api/customers/${customerId}`);
        const c = data?.data || data;
        foundCustomer.value = {
            id: c.id,
            name: c.name,
            code: c.code || '',
            avatar: c.avatar ?? null,
        };
        form.value.customer_id = c.id;
    } catch {
        foundCustomer.value = null;
    } finally {
        lookupLoading.value = false;
    }
};

watch(visitorCustomerId, (id) => {
    if (activeTab.value !== 'visitor') return;
    if (!id) {
        foundCustomer.value = null;
        form.value.customer_id = '';
        return;
    }
    fetchCustomerDetail(String(id));
});

const isScannerOpen = ref(false);
const scannerSupported = ref(false);
const scannerBusy = ref(false);
const scannerError = ref('');
const videoRef = ref<HTMLVideoElement | null>(null);
let mediaStream: MediaStream | null = null;
let detector: any = null;
let scanLoopActive = false;
let scanRafId: number | null = null;
let jsQrDecode:
    | ((
          data: Uint8ClampedArray,
          width: number,
          height: number,
          options?: { inversionAttempts?: string },
      ) => { data: string } | null)
    | null = null;
let qrCanvas: HTMLCanvasElement | null = null;
let qrContext: CanvasRenderingContext2D | null = null;

const startScanner = async () => {
    if (!scannerSupported.value || scannerBusy.value) return;
    scannerBusy.value = true;
    scannerError.value = '';
    try {
        if (!navigator.mediaDevices?.getUserMedia) {
            scannerError.value =
                'Browser tidak mendukung akses kamera pada halaman ini.';
            return;
        }

        mediaStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment' },
        });
        if (videoRef.value) {
            (videoRef.value as any).srcObject = mediaStream;
            await videoRef.value.play();
        }

        const hasNativeBarcodeDetector =
            typeof (window as any).BarcodeDetector !== 'undefined';

        if (hasNativeBarcodeDetector) {
            const BD = (window as any).BarcodeDetector;
            detector = new BD({ formats: ['qr_code'] });
        } else {
            const module = await import('jsqr');
            jsQrDecode = module.default;
            qrCanvas = document.createElement('canvas');
            qrContext = qrCanvas.getContext('2d');

            if (!jsQrDecode || !qrContext) {
                scannerError.value = 'Gagal memuat mesin pemindai QR.';
                return;
            }
        }

        scanLoopActive = true;
        const loop = async () => {
            if (!scanLoopActive || !videoRef.value) {
                return;
            }

            try {
                if (detector) {
                    const codes = await detector.detect(videoRef.value);
                    if (
                        Array.isArray(codes) &&
                        codes.length &&
                        codes[0]?.rawValue
                    ) {
                        form.value.code = String(codes[0].rawValue);
                        await closeScannerModal();
                        return;
                    }
                } else if (jsQrDecode && qrCanvas && qrContext) {
                    const video = videoRef.value;
                    if (
                        video.videoWidth > 0 &&
                        video.videoHeight > 0 &&
                        video.readyState >= HTMLMediaElement.HAVE_CURRENT_DATA
                    ) {
                        qrCanvas.width = video.videoWidth;
                        qrCanvas.height = video.videoHeight;
                        qrContext.drawImage(
                            video,
                            0,
                            0,
                            qrCanvas.width,
                            qrCanvas.height,
                        );
                        const imageData = qrContext.getImageData(
                            0,
                            0,
                            qrCanvas.width,
                            qrCanvas.height,
                        );
                        const code = jsQrDecode(
                            imageData.data,
                            imageData.width,
                            imageData.height,
                            { inversionAttempts: 'attemptBoth' },
                        );

                        if (code?.data) {
                            form.value.code = String(code.data);
                            await closeScannerModal();
                            return;
                        }
                    }
                }
            } catch (error) {
                scannerError.value =
                    error instanceof Error
                        ? error.message
                        : 'Gagal memindai QR code.';
            }

            if (scanLoopActive) {
                scanRafId = requestAnimationFrame(loop);
            }
        };

        scanRafId = requestAnimationFrame(loop);
    } catch {
        scannerError.value =
            'Tidak dapat mengakses kamera. Periksa izin kamera browser.';
    } finally {
        scannerBusy.value = false;
    }
};

const stopScanner = async () => {
    scanLoopActive = false;
    if (scanRafId !== null) {
        cancelAnimationFrame(scanRafId);
        scanRafId = null;
    }

    if (videoRef.value) {
        try {
            await videoRef.value.pause();
        } catch (error) {
            console.warn(error);
        }
        (videoRef.value as any).srcObject = null;
    }
    if (mediaStream) {
        mediaStream.getTracks().forEach((t) => t.stop());
        mediaStream = null;
    }

    detector = null;
    jsQrDecode = null;
    qrCanvas = null;
    qrContext = null;
    isScannerOpen.value = false;
};

const openScannerModal = async () => {
    if (!scannerSupported.value || scannerBusy.value) {
        if (!scannerSupported.value) {
            scannerError.value =
                'Perangkat atau browser tidak mendukung akses kamera.';
        }
        return;
    }

    isScannerOpen.value = true;
    await nextTick();
    await startScanner();
};

const closeScannerModal = async () => {
    await stopScanner();
};

const setActiveTab = (next: 'member' | 'visitor') => {
    activeTab.value = next;
    errors.value = {};
    foundCustomer.value = null;
    form.value.customer_id = '';
    form.value.code = '';
    visitorCustomerId.value = null;
    if (next === 'member') {
        form.value.visit_type = 'MEMBERSHIP';
        form.value.price = null;
        void closeScannerModal();
    } else {
        form.value.visit_type = 'DAILY';
        form.value.price = dailyVisitPrice.value;
        void closeScannerModal();
        fetchCustomerOptions(true);
    }
};

const closeDrawer = () => {
    isOpen.value = false;
    form.value = { customer_id: '', visit_type: '', price: null, code: '' };
    errors.value = {};
    processing.value = false;
    foundCustomer.value = null;
    void closeScannerModal();
    activeTab.value = 'member';
    visitorCustomerId.value = null;
    isCreateCustomerOpen.value = false;
};

const formatCurrencyId = (value: unknown): string => {
    if (value === null || value === undefined || value === '') {
        return 'Rp 0';
    }
    const numberValue = typeof value === 'number' ? value : Number(value);
    if (Number.isNaN(numberValue)) {
        return String(value);
    }
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    })
        .format(numberValue)
        .replace('Rp', 'Rp ');
};

const submitDisabled = computed(() => {
    if (processing.value) return true;
    if (!foundCustomer.value) return true;
    if (
        activeTab.value === 'visitor' &&
        (form.value.price === null || Number.isNaN(Number(form.value.price)))
    )
        return true;
    return false;
});

const submit = async () => {
    processing.value = true;
    errors.value = {};
    try {
        const payload: any = {
            customer_id:
                foundCustomer.value?.id || form.value.customer_id || null,
            checkin_method: activeTab.value === 'member' ? 'QR_CODE' : 'MANUAL',
            visit_type: activeTab.value === 'member' ? 'MEMBERSHIP' : 'DAILY',
            price: activeTab.value === 'visitor' ? dailyVisitPrice.value : null,
            code: activeTab.value === 'member' ? form.value.code || null : null,
        };
        await axios.post('/api/visits', payload);
        closeDrawer();
        emit('submitted');
    } catch (e: any) {
        if (e.response?.status === 422 && e.response.data?.errors) {
            const ers = e.response.data.errors;
            errors.value = Object.keys(ers).reduce((acc: any, k) => {
                acc[k] = Array.isArray(ers[k]) ? ers[k][0] : String(ers[k]);
                return acc;
            }, {});
        } else {
            console.error(e);
            alert('Gagal menyimpan kunjungan.');
        }
    } finally {
        processing.value = false;
    }
};

const emit = defineEmits<{ (e: 'submitted'): void }>();
onMounted(() => {
    scannerSupported.value = !!navigator.mediaDevices?.getUserMedia;
    setActiveTab('member');
});
</script>
