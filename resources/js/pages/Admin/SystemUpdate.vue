<template>
    <AppLayout>
        <div class="mx-auto max-w-4xl p-6">
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-6">
                    <h1 class="text-2xl font-semibold text-gray-900">Update Sistem</h1>
                    <p class="mt-1 text-sm text-gray-600">Kelola update aplikasi dari GitHub releases</p>
                </div>

                <div class="space-y-6 p-6">
                    <!-- Current Version Info -->
                    <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Versi Saat Ini</h3>
                                <div class="mt-1 text-sm text-blue-700">
                                    <span class="font-mono">{{ currentVersion || 'Loading...' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Update Check Section -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-medium text-gray-900">Cek Update</h2>
                            <button
                                @click="checkForUpdates"
                                :disabled="isChecking"
                                class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-gray-400"
                            >
                                <svg v-if="isChecking" class="mr-2 -ml-1 h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                {{ isChecking ? 'Checking...' : 'Cek Update' }}
                            </button>
                        </div>

                        <!-- Update Available -->
                        <div v-if="updateInfo && updateInfo.has_update" class="rounded-lg border border-green-200 bg-green-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <h3 class="text-sm font-medium text-green-800">Update Tersedia!</h3>
                                    <div class="mt-2 text-sm text-green-700">
                                        <p>
                                            Versi terbaru: <span class="font-mono font-medium">{{ updateInfo.latest_version }}</span>
                                        </p>
                                        <p class="mt-1">Dirilis: {{ formatDate(updateInfo.published_at) }}</p>
                                    </div>
                                    <div v-if="updateInfo.release_notes" class="mt-3">
                                        <h4 class="text-sm font-medium text-green-800">Release Notes:</h4>
                                        <div class="mt-1 text-sm whitespace-pre-line text-green-700">
                                            {{ updateInfo.release_notes }}
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <button
                                            @click="performUpdate"
                                            :disabled="isUpdating"
                                            class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 disabled:cursor-not-allowed disabled:bg-gray-400"
                                        >
                                            <svg v-if="isUpdating" class="mr-2 -ml-1 h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path
                                                    class="opacity-75"
                                                    fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                                ></path>
                                            </svg>
                                            {{ isUpdating ? 'Updating...' : 'Install Update' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- No Update Available -->
                        <div v-else-if="updateInfo && !updateInfo.has_update" class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-gray-800">Sistem Up-to-Date</h3>
                                    <div class="mt-1 text-sm text-gray-600">Anda sudah menggunakan versi terbaru dari aplikasi.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Backup & Restore Section -->
                    <div class="border-t border-gray-200 pt-6">
                        <h2 class="mb-4 text-lg font-medium text-gray-900">Backup & Restore</h2>
                        <div class="space-y-3">
                            <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">Penting!</h3>
                                        <div class="mt-1 text-sm text-yellow-700">
                                            Backup otomatis dibuat sebelum update. Jika ada masalah, Anda dapat restore backup.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button
                                @click="restoreBackup"
                                :disabled="isRestoring"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 disabled:cursor-not-allowed disabled:bg-gray-100"
                            >
                                <svg v-if="isRestoring" class="mr-2 -ml-1 h-4 w-4 animate-spin text-gray-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                {{ isRestoring ? 'Restoring...' : 'Restore Backup Terakhir' }}
                            </button>
                        </div>
                    </div>

                    <!-- Update Progress -->
                    <div v-if="updateProgress" class="border-t border-gray-200 pt-6">
                        <h3 class="mb-4 text-lg font-medium text-gray-900">Progress Update</h3>
                        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                            <div class="flex items-center">
                                <svg class="mr-3 -ml-1 h-5 w-5 animate-spin text-blue-600" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                                <span class="text-sm font-medium text-blue-800">{{ updateProgress }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Error Messages -->
                    <div v-if="errorMessage" class="rounded-lg border border-red-200 bg-red-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Error</h3>
                                <div class="mt-1 text-sm text-red-700">
                                    {{ errorMessage }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { onMounted, ref } from 'vue';

// State
const currentVersion = ref<string>('');
const updateInfo = ref<any>(null);
const isChecking = ref(false);
const isUpdating = ref(false);
const isRestoring = ref(false);
const updateProgress = ref<string>('');
const errorMessage = ref<string>('');

// Methods
const checkForUpdates = async () => {
    isChecking.value = true;
    errorMessage.value = '';

    try {
        const response = await fetch('/admin/system/check-updates');
        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Failed to check updates');
        }

        updateInfo.value = data;
        currentVersion.value = data.current_version;
    } catch (error: any) {
        errorMessage.value = error.message || 'Gagal mengecek update';
    } finally {
        isChecking.value = false;
    }
};

const performUpdate = async () => {
    if (!updateInfo.value?.download_url) return;

    isUpdating.value = true;
    errorMessage.value = '';
    updateProgress.value = 'Memulai update...';

    try {
        updateProgress.value = 'Downloading update...';

        const response = await fetch('/admin/system/perform-update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                download_url: updateInfo.value.download_url,
                version: updateInfo.value.latest_version,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Failed to perform update');
        }

        updateProgress.value = 'Update berhasil! Memuat ulang halaman...';

        // Reload page after successful update
        setTimeout(() => {
            window.location.reload();
        }, 2000);
    } catch (error: any) {
        errorMessage.value = error.message || 'Gagal melakukan update';
        updateProgress.value = '';
    } finally {
        isUpdating.value = false;
    }
};

const restoreBackup = async () => {
    if (!confirm('Yakin ingin restore backup? Ini akan mengembalikan sistem ke kondisi sebelum update terakhir.')) {
        return;
    }

    isRestoring.value = true;
    errorMessage.value = '';

    try {
        const response = await fetch('/admin/system/restore-backup', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Failed to restore backup');
        }

        alert('Backup berhasil direstore! Halaman akan dimuat ulang.');
        window.location.reload();
    } catch (error: any) {
        errorMessage.value = error.message || 'Gagal restore backup';
    } finally {
        isRestoring.value = false;
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Initialize
onMounted(() => {
    checkForUpdates();
});
</script>
