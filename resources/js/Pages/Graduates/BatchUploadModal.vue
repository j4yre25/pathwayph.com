<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-xl p-6 relative">
      <!-- Close button -->
      <button @click="$emit('close')" class="absolute top-3 right-4 text-gray-500 hover:text-red-600 text-2xl">&times;</button>

      <h2 class="text-xl font-semibold mb-4">Batch Graduates Accounts Creation</h2>

      <p class="text-sm text-gray-600 mb-6">
        Step 1: Download the CSV template and fill it with the required graduate data.<br>
        Step 2: Upload the filled CSV below and click Upload.
      </p>

      <div class="flex justify-between items-center mb-4">
        <button @click="downloadTemplate" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
          Download CSV Template
        </button>
      </div>

      <form @submit.prevent="submitUpload" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Upload CSV File<span class="text-red-500">*</span></label>
          <input type="file" accept=".csv" @change="handleFileChange" class="w-full p-2 border rounded" required />
          <p v-if="error" class="text-red-500 text-sm mt-1">{{ error }}</p>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <button type="button" @click="$emit('close')" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
          <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Upload</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({ show: Boolean })
const emit = defineEmits(['close'])

const file = ref(null)
const error = ref('')

function handleFileChange(e) {
  const selectedFile = e.target.files[0]
  if (selectedFile && selectedFile.type !== 'text/csv' && !selectedFile.name.endsWith('.csv')) {
    error.value = 'Only CSV files are allowed.'
    file.value = null
  } else {
    error.value = ''
    file.value = selectedFile
  }
}

function submitUpload() {
  if (!file.value) {
    error.value = 'Please select a CSV file to upload.'
    return
  }

  const formData = new FormData()
  formData.append('csv_file', file.value)

  router.post('/graduate/batch-upload', formData, {
    forceFormData: true,
    onSuccess: () => {
      emit('close')
      file.value = null
      error.value = ''
    },
    onError: (err) => {
      error.value = 'An error occurred during upload. Please ensure your CSV is properly formatted.'
    }
  })
}

function downloadTemplate() {
  window.location.href = '/graduate/download-template'
}
</script>
