<template>
    <AdminLayout>

        <Head title="Pengaturan WhatsApp" />
        <PageBreadcrumb pageTitle="Pengaturan WhatsApp" />

        <div class="max-w-3xl mx-auto">
            <!-- Loading State -->
            <div v-if="loading" class="flex justify-center py-12">
                <Loader2Icon class="h-8 w-8 animate-spin text-brand-500" />
            </div>

            <template v-else>
                <!-- Configured State -->
                <ComponentCard v-if="configState?.token" title="Status WhatsApp"
                    desc="Perangkat WhatsApp Anda sudah dikonfigurasi">
                    <div class="space-y-6">
                        <!-- Status Indicator -->
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                            <div class="flex items-center gap-3">
                                <div class="rounded-full p-2"
                                    :class="configState.is_connected ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400'">
                                    <component :is="configState.is_connected ? CheckCircleIcon : UnplugIcon"
                                        class="h-6 w-6" />
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-white">{{ configState.name }}</h4>
                                    <p class="text-sm text-gray-500">{{ configState.is_connected ? 'Terhubung' :
                                        'Terputus' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Button variant="outline" size="sm" @click="checkConnection(true)" :disabled="checking">
                                    <RefreshCwIcon class="h-4 w-4 mr-2" :class="{ 'animate-spin': checking }" />
                                    Cek Status
                                </Button>
                                <Button v-if="configState.is_connected" variant="outline" size="sm"
                                    @click="openTestDrawer">
                                    Test Koneksi
                                </Button>
                                <Button variant="outline" size="sm"
                                    class="text-error-600 border-error-200 hover:bg-error-50 dark:border-error-800 dark:hover:bg-error-900/20"
                                    @click="resetConfig" :disabled="processing">
                                    <TrashIcon class="h-4 w-4 mr-2" />
                                    Reset Token
                                </Button>
                            </div>
                        </div>

                        <!-- Connection Info -->
                        <div v-if="configState.is_connected" class="mt-6">
                            <DetailGrid :items="connectionDetails" :columns="3" />
                        </div>

                        <!-- QR Code Section (if disconnected) -->
                        <div v-if="!configState.is_connected"
                            class="flex flex-col items-center space-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div v-if="qrCode" class="flex flex-col items-center space-y-4">
                                <div
                                    class="rounded-lg border-2 border-dashed border-gray-300 p-2 dark:border-gray-600 bg-white">
                                    <img :src="qrCode.startsWith('data:') ? qrCode : 'data:image/png;base64,' + qrCode"
                                        alt="WhatsApp QR Code" class="h-64 w-64 object-contain" />
                                </div>
                                <p class="text-center text-sm text-gray-500">
                                    Scan QR code di atas menggunakan WhatsApp pada ponsel Anda.
                                </p>
                            </div>

                            <div v-else class="flex flex-col items-center justify-center py-4 text-center">
                                <QrCodeIcon class="mb-4 h-12 w-12 text-gray-300" />
                                <p class="text-sm text-gray-500">Perangkat belum terhubung. Scan QR Code untuk
                                    menghubungkan.</p>
                            </div>

                            <Button variant="primary" @click="getQr" :disabled="loadingQr">
                                <span v-if="loadingQr" class="mr-2 animate-spin">
                                    <Loader2Icon class="h-4 w-4" />
                                </span>
                                {{ qrCode ? 'Refresh QR Code' : 'Tampilkan QR Code' }}
                            </Button>
                        </div>
                    </div>
                </ComponentCard>

                <!-- Initial Setup Form -->
                <ComponentCard v-else title="Konfigurasi Token"
                    desc="Masukkan token Fonnte untuk menghubungkan WhatsApp">
                    <form @submit.prevent="saveConfig" class="space-y-4">
                        <div>
                            <label for="token" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200">
                                Token Fonnte
                            </label>
                            <input id="token" v-model="form.token" type="password"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                                placeholder="Masukkan token Fonnte Anda" required />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Dapatkan token dari dashboard Fonnte.
                            </p>
                        </div>

                        <div class="flex justify-end pt-4">
                            <Button variant="primary" :disabled="processing">
                                <span v-if="processing" class="mr-2 animate-spin">
                                    <Loader2Icon class="h-4 w-4" />
                                </span>
                                Simpan Konfigurasi
                            </Button>
                        </div>
                    </form>
                </ComponentCard>
            </template>
        </div>

        <!-- Test Message Drawer -->
        <Drawer :is-open="isTestDrawerOpen" title="Test Koneksi WhatsApp" @close="isTestDrawerOpen = false">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Nomor Tujuan</label>
                    <input v-model="testForm.target" type="text" placeholder="e.g. 08123456789"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-brand-500 dark:bg-gray-800 dark:border-gray-700" />
                    <p class="text-xs text-gray-500 mt-1">Gunakan format 08... atau 62...</p>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button variant="outline" @click="isTestDrawerOpen = false">Batal</Button>
                    <Button variant="primary" @click="sendTestMessage" :disabled="testForm.processing">
                        <span v-if="testForm.processing" class="mr-2 animate-spin">
                            <Loader2Icon class="h-4 w-4" />
                        </span>
                        Kirim Pesan
                    </Button>
                </div>
            </template>
        </Drawer>
    </AdminLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import {
    QrCode as QrCodeIcon,
    Unplug as UnplugIcon,
    Loader2 as Loader2Icon,
    Trash as TrashIcon,
    RefreshCw as RefreshCwIcon
} from 'lucide-vue-next';
import { ref, onMounted, reactive, onUnmounted, computed } from 'vue';
import ComponentCard from '@/components/common/ComponentCard.vue';
import DetailGrid from '@/components/common/DetailGrid.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import Button from '@/components/ui/Button.vue';
import Drawer from '@/components/ui/Drawer.vue';
import { useToast } from '@/composables/useToast';
import { CheckCircleIcon } from '@/icons';

// State
const configState = ref<any>(null);
const loading = ref(true);
const processing = ref(false);
const qrCode = ref<string | null>(null);
const loadingQr = ref(false);
const checking = ref(false);
let checkInterval: any = null;

const { success: toastSuccess, error: toastError } = useToast();

// Computed
const connectionDetails = computed(() => {
    if (!configState.value) return [];

    const items = [
        { label: 'Nomor WhatsApp', value: configState.value.phone || '-' },
        {
            label: 'Token',
            value: configState.value.token
                ? `${configState.value.token.substring(0, 10)}...${configState.value.token.substring(configState.value.token.length - 5)}`
                : '-'
        },
        {
            label: 'Terhubung Sejak',
            value: configState.value.connected_at
                ? new Date(configState.value.connected_at).toLocaleString('id-ID')
                : '-'
        },
    ];

    if (configState.value.quota) {
        items.push({ label: 'Quota', value: configState.value.quota });
    }

    if (configState.value.expired) {
        items.push({ label: 'Expired', value: configState.value.expired });
    }

    return items;
});

// Form State
const form = reactive({
    token: ''
});

// Test Drawer State
const isTestDrawerOpen = ref(false);
const testForm = reactive({
    target: '',
    processing: false
});

// Fetch Config
const fetchConfig = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/whatsapp-config');
        if (response.data.success) {
            configState.value = response.data.data;
        }
    } catch (error) {
        console.error('Failed to fetch config:', error);
    } finally {
        loading.value = false;
    }
};

const checkConnection = async (isManual = false) => {
    // Avoid checking if already checking or resetting
    if (checking.value || processing.value) return;

    checking.value = true;
    try {
        const response = await axios.get('/api/whatsapp-config/check');
        if (response.data.success) {
            const result = response.data.data;

            if (result.data) {
                configState.value = result.data;

                if (result.connected) {
                    qrCode.value = null;
                    if (checkInterval) {
                        clearInterval(checkInterval);
                        checkInterval = null;
                    }
                    if (isManual) {
                        toastSuccess('WhatsApp terhubung!');
                    }
                } else {
                    if (isManual) {
                        toastError('WhatsApp belum terhubung.');
                    }
                }
            }
        }
    } catch (error) {
        if (isManual) {
            console.error('Connection check failed:', error);
            toastError('Gagal mengecek status koneksi.');
        }
    } finally {
        checking.value = false;
    }
};

onMounted(() => {
    fetchConfig();
});

onUnmounted(() => {
    if (checkInterval) {
        clearInterval(checkInterval);
    }
});

// Actions
const saveConfig = async () => {
    if (!form.token) return;

    processing.value = true;
    try {
        const response = await axios.post('/api/whatsapp-config', form);
        if (response.data.success) {
            configState.value = response.data.data;
            qrCode.value = null;
        }
    } catch (error) {
        console.error('Failed to save config:', error);
    } finally {
        processing.value = false;
    }
};

const resetConfig = async () => {
    if (!confirm('Apakah Anda yakin ingin menghapus konfigurasi ini? Token akan dihapus permanen.')) return;

    // Stop checking interval
    if (checkInterval) {
        clearInterval(checkInterval);
        checkInterval = null;
    }

    processing.value = true;
    try {
        const response = await axios.delete('/api/whatsapp-config');
        if (response.data.success) {
            configState.value = null;
            form.token = '';
            qrCode.value = null;
        }
    } catch (error) {
        console.error('Failed to reset config:', error);
    } finally {
        processing.value = false;
    }
};

const getQr = async () => {
    loadingQr.value = true;
    qrCode.value = null;

    try {
        const response = await axios.get('/api/whatsapp-config/qr');
        if (response.data.success) {
            const data = response.data.data;
            if (data.url) {
                qrCode.value = data.url;

                // Start polling when QR is shown
                if (!checkInterval) {
                    checkInterval = setInterval(() => {
                        checkConnection();
                    }, 5000); // Check every 5 seconds
                }
            } else if (data.already_connected) {
                // Reload data to reflect connection
                await fetchConfig();
            }
        }
    } catch (error: any) {
        console.error('Failed to get QR:', error);
    } finally {
        loadingQr.value = false;
    }
};

const openTestDrawer = () => {
    testForm.target = '';
    isTestDrawerOpen.value = true;
};

const sendTestMessage = async () => {
    if (!testForm.target) return;

    testForm.processing = true;
    try {
        const response = await axios.post('/api/whatsapp-config/test', {
            target: testForm.target,
            message: 'Pesan test dari sistem'
        });

        if (response.data.success) {
            isTestDrawerOpen.value = false;
        }
    } catch (error: any) {
        console.error('Failed to send test message:', error);
    } finally {
        testForm.processing = false;
    }
};
</script>
