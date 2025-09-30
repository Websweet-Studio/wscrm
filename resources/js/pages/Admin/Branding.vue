<template>
  <AppLayout>
    <div class="max-w-6xl mx-auto p-6">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
          <h1 class="text-2xl font-semibold text-gray-900">Pengaturan Branding</h1>
          <p class="text-sm text-gray-600 mt-1">
            Kelola logo, warna, dan identitas visual aplikasi
          </p>
        </div>

        <form @submit.prevent="submitSettings" class="p-6">
          <div class="space-y-8">
            <!-- Identitas Aplikasi -->
            <div v-if="settings.text?.length" class="space-y-6">
              <h2 class="text-lg font-medium text-gray-900">Identitas Aplikasi</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="setting in settings.text" :key="setting.key" class="space-y-2">
                  <label :for="setting.key" class="block text-sm font-medium text-gray-700">
                    {{ getSettingLabel(setting.key) }}
                  </label>
                  <input
                    :id="setting.key"
                    v-model="form.settings[setting.key]"
                    type="text"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    :placeholder="setting.description"
                  />
                </div>
              </div>
            </div>

            <!-- Textarea Settings -->
            <div v-if="settings.textarea?.length" class="space-y-6">
              <h2 class="text-lg font-medium text-gray-900">Informasi Perusahaan</h2>
              <div class="space-y-6">
                <div v-for="setting in settings.textarea" :key="setting.key" class="space-y-2">
                  <label :for="setting.key" class="block text-sm font-medium text-gray-700">
                    {{ getSettingLabel(setting.key) }}
                  </label>
                  <textarea
                    :id="setting.key"
                    v-model="form.settings[setting.key]"
                    rows="3"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                    :placeholder="setting.description"
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- Logo dan Gambar -->
            <div v-if="settings.image?.length" class="space-y-6">
              <h2 class="text-lg font-medium text-gray-900">Logo dan Gambar</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div v-for="setting in settings.image" :key="setting.key" class="space-y-4">
                  <label class="block text-sm font-medium text-gray-700">
                    {{ getSettingLabel(setting.key) }}
                  </label>
                  <p class="text-sm text-gray-500">{{ setting.description }}</p>

                  <!-- Current Image Preview (dari database) -->
                  <div v-if="setting.value && !imagePreviews[setting.key]" class="space-y-3">
                    <div class="relative w-32 h-32 border-2 border-gray-200 rounded-lg overflow-hidden">
                      <img
                        :src="setting.value"
                        :alt="getSettingLabel(setting.key)"
                        class="w-full h-full object-contain"
                      />
                    </div>
                    <div class="text-xs text-gray-600">
                      Gambar saat ini
                    </div>
                    <button
                      type="button"
                      @click="deleteImage(setting.key)"
                      :disabled="form.processing"
                      class="text-sm text-red-600 hover:text-red-800 disabled:opacity-50"
                    >
                      Hapus Gambar
                    </button>
                  </div>

                  <!-- New Image Preview -->
                  <div v-if="imagePreviews[setting.key]" class="space-y-3">
                    <div class="relative w-32 h-32 border-2 border-blue-300 rounded-lg overflow-hidden">
                      <img
                        :src="imagePreviews[setting.key]"
                        :alt="`Preview ${getSettingLabel(setting.key)}`"
                        class="w-full h-full object-contain"
                      />
                      <div class="absolute top-1 right-1">
                        <button
                          type="button"
                          @click="clearImagePreview(setting.key)"
                          class="bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs"
                        >
                          ×
                        </button>
                      </div>
                    </div>
                    <div class="flex items-center justify-between">
                      <div class="text-xs text-blue-600">
                        Preview gambar baru - belum disimpan
                      </div>
                      <button
                        type="button"
                        @click="uploadPreviewImage(setting.key)"
                        :disabled="form.processing"
                        class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded disabled:opacity-50"
                      >
                        Upload
                      </button>
                    </div>
                  </div>

                  <!-- Upload Input -->
                  <div class="space-y-2">
                    <input
                      type="file"
                      :id="`upload_${setting.key}`"
                      @change="(event) => handleImageUpload(event, setting.key)"
                      accept="image/*"
                      class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Palet Warna -->
            <div v-if="settings.color?.length" class="space-y-6">
              <h2 class="text-lg font-medium text-gray-900">Palet Warna</h2>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="setting in settings.color" :key="setting.key" class="space-y-2">
                  <label :for="setting.key" class="block text-sm font-medium text-gray-700">
                    {{ getSettingLabel(setting.key) }}
                  </label>
                  <div class="flex items-center space-x-3">
                    <input
                      :id="setting.key"
                      v-model="form.settings[setting.key]"
                      type="color"
                      class="h-10 w-20 rounded border border-gray-300 cursor-pointer"
                    />
                    <input
                      v-model="form.settings[setting.key]"
                      type="text"
                      class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                      placeholder="#000000"
                    />
                  </div>
                  <p class="text-xs text-gray-500">{{ setting.description }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 mt-8">
            <button
              type="button"
              @click="resetForm"
              class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Reset
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="form.processing">Menyimpan...</span>
              <span v-else>Simpan Perubahan</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { useToast } from '@/composables/useToast'

interface BrandingSetting {
  id: number
  key: string
  value: string | null
  type: string
  description: string
  is_active: boolean
}

interface Props {
  settings: {
    text?: BrandingSetting[]
    textarea?: BrandingSetting[]
    color?: BrandingSetting[]
    image?: BrandingSetting[]
  }
}

const props = defineProps<Props>()
const { success, error } = useToast()

// Form setup
const form = useForm({
  settings: {} as Record<string, string | null>
})

// Preview functionality
const imagePreviews = ref<Record<string, string>>({})
const pendingFiles = ref<Record<string, File>>({})

const createImagePreview = (file: File, key: string) => {
  const reader = new FileReader()
  reader.onload = (e) => {
    if (e.target?.result) {
      imagePreviews.value[key] = e.target.result as string
      pendingFiles.value[key] = file
    }
  }
  reader.readAsDataURL(file)
}

const clearImagePreview = (key: string) => {
  if (imagePreviews.value[key]) {
    delete imagePreviews.value[key]
  }
  if (pendingFiles.value[key]) {
    delete pendingFiles.value[key]
  }
}

// Initialize form with current settings
onMounted(() => {
  Object.values(props.settings).flat().forEach((setting) => {
    form.settings[setting.key] = setting.value
  })
})

// Setting labels mapping
const settingLabels: Record<string, string> = {
  app_name: 'Nama Aplikasi',
  app_slogan: 'Slogan',
  app_logo: 'Logo Utama',
  app_logo_dark: 'Logo Mode Gelap',
  app_favicon: 'Favicon',
  primary_color: 'Warna Utama',
  secondary_color: 'Warna Sekunder',
  accent_color: 'Warna Aksen',
  footer_text: 'Teks Footer',
  company_address: 'Alamat Perusahaan',
  company_phone: 'Nomor Telepon',
  company_email: 'Email Perusahaan',
}

const getSettingLabel = (key: string): string => {
  return settingLabels[key] || key
}

const submitSettings = () => {
  const settingsArray = Object.values(props.settings).flat().map((setting) => ({
    key: setting.key,
    value: form.settings[setting.key],
    type: setting.type,
  }))

  form.transform((data) => ({
    settings: settingsArray,
  })).patch('/admin/branding', {
    preserveScroll: true,
    onSuccess: () => {
      success('Berhasil', 'Pengaturan branding berhasil diperbarui')
    },
    onError: (errors) => {
      console.error('Form submission errors:', errors)
      if (Object.keys(errors).length > 0) {
        const firstError = Object.values(errors)[0]
        error('Gagal menyimpan', Array.isArray(firstError) ? firstError[0] : firstError)
      } else {
        error('Terjadi kesalahan', 'Gagal menyimpan pengaturan branding')
      }
    },
    onFinish: () => {
      // Reset transform to avoid stale data
      form.transform(() => form.data())
    }
  })
}

const resetForm = () => {
  Object.values(props.settings).flat().forEach((setting) => {
    form.settings[setting.key] = setting.value
  })
}

const handleImageUpload = (event: Event, key: string) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]

  if (!file) {
    clearImagePreview(key)
    return
  }

  // Validate file type
  if (!file.type.startsWith('image/')) {
    error('File tidak valid', 'File yang dipilih harus berupa gambar')
    target.value = ''
    return
  }

  // Validate file size (max 5MB)
  const maxSize = 5 * 1024 * 1024 // 5MB in bytes
  if (file.size > maxSize) {
    error('Ukuran file terlalu besar', 'Ukuran file tidak boleh lebih dari 5MB')
    target.value = ''
    return
  }

  // Create preview immediately
  createImagePreview(file, key)

  // Clear the input so same file can be selected again
  target.value = ''
}

const uploadPreviewImage = async (key: string) => {
  const file = pendingFiles.value[key]
  if (!file) {
    error('File tidak ditemukan', 'Silakan pilih file lagi')
    return
  }

  const formData = new FormData()
  formData.append('image', file)
  formData.append('key', key)

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (!csrfToken) {
      error('Token tidak valid', 'Silakan refresh halaman dan coba lagi')
      return
    }

    const response = await fetch('/admin/branding/upload-image', {
      method: 'POST',
      body: formData,
      headers: {
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      },
    })

    if (!response.ok) {
      if (response.status === 419) {
        error('Sesi telah berakhir', 'Silakan refresh halaman dan coba lagi')
      } else if (response.status === 422) {
        const errorData = await response.json()
        const firstError = Object.values(errorData.errors || {})[0]
        error('Validasi gagal', Array.isArray(firstError) ? firstError[0] : 'Data tidak valid')
      } else {
        error('Terjadi kesalahan', `Server error: ${response.status}`)
      }
      return
    }

    const data = await response.json()

    if (data.success) {
      // Update the setting value and form
      form.settings[key] = data.path

      // Update the settings prop for reactive display
      const settingGroup = props.settings.image
      if (settingGroup) {
        const setting = settingGroup.find(s => s.key === key)
        if (setting) {
          setting.value = data.path
        }
      }

      // Clear preview after successful upload
      clearImagePreview(key)
      success('Upload berhasil', 'Gambar telah berhasil diupload')
    } else {
      error('Upload gagal', data.message || 'Gagal mengupload gambar')
    }
  } catch (uploadError) {
    console.error('Upload error:', uploadError)
    error('Terjadi kesalahan', 'Terjadi kesalahan saat mengupload gambar')
  }
}

const deleteImage = async (key: string) => {
  if (!confirm('Yakin ingin menghapus gambar ini?')) return

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    if (!csrfToken) {
      error('Token tidak valid', 'Silakan refresh halaman dan coba lagi')
      return
    }

    const response = await fetch('/admin/branding/delete-image', {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ key }),
    })

    if (!response.ok) {
      if (response.status === 419) {
        error('Sesi telah berakhir', 'Silakan refresh halaman dan coba lagi')
      } else if (response.status === 422) {
        const errorData = await response.json()
        const firstError = Object.values(errorData.errors || {})[0]
        error('Validasi gagal', Array.isArray(firstError) ? firstError[0] : 'Data tidak valid')
      } else {
        error('Terjadi kesalahan', `Server error: ${response.status}`)
      }
      return
    }

    const data = await response.json()

    if (data.success) {
      // Update the setting value and form
      form.settings[key] = null

      // Update the settings prop for reactive display
      const settingGroup = props.settings.image
      if (settingGroup) {
        const setting = settingGroup.find(s => s.key === key)
        if (setting) {
          setting.value = null
        }
      }

      // Clear any preview for this key
      clearImagePreview(key)
      success('Hapus berhasil', 'Gambar telah berhasil dihapus')
    } else {
      error('Hapus gagal', data.message || 'Gagal menghapus gambar')
    }
  } catch (deleteError) {
    console.error('Delete error:', deleteError)
    error('Terjadi kesalahan', 'Terjadi kesalahan saat menghapus gambar')
  }
}
</script>