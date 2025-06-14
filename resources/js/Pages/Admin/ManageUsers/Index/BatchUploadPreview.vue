<script setup>
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'
import Papa from 'papaparse'

const csvFile = ref(null)
const csvPreview = ref([])
const errors = ref([])
const success = ref(false)
const uploading = ref(false)
const templateUrl = '/admin/manage-users/download'

function onFileChange(e) {
  errors.value = []
  success.value = false
  csvFile.value = e.target.files[0] || null
  csvPreview.value = []

  if (csvFile.value) {
    Papa.parse(csvFile.value, {
      complete: (results) => {
        csvPreview.value = results.data
      },
      skipEmptyLines: true,
    })
  }
}

async function handleUpload() {
  if (!csvFile.value) return
  errors.value = []
  success.value = false
  uploading.value = true

  const formData = new FormData()
  formData.append('csv_file', csvFile.value)

  try {
    const res = await axios.post('/admin/manage-users/batch-upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    if (res.data.status === 'success') {
      success.value = true
      csvFile.value = null
      csvPreview.value = []
    }
  } catch (err) {
    if (err.response && err.response.data && err.response.data.errors) {
      errors.value = err.response.data.errors
    } else {
      errors.value = [{ row: '-', messages: ['An unexpected error occurred.'] }]
    }
  } finally {
    uploading.value = false
  }
}

</script>


<template>
  <AppLayout title="Batch Company Upload">
    <div class="max-w-2xl mx-auto py-10">
      <h1 class="text-2xl font-bold mb-6">Batch Upload Companies</h1>

      <form @submit.prevent="handleUpload" class="mb-8">
        <label class="block mb-2 font-semibold">CSV File</label>
        <input type="file" accept=".csv,.txt" @change="onFileChange" class="mb-4" />
        <button
          type="submit"
          :disabled="!csvFile || uploading"
          class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50"
        >
          {{ uploading ? 'Uploading...' : 'Upload' }}
        </button>
        <a
          :href="templateUrl"
          class="ml-4 text-blue-600 underline"
          download
        >Download CSV Template</a>
      </form>

      <div v-if="csvPreview.length" class="mb-8">
        <h2 class="font-semibold mb-2">CSV Preview:</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm border">
            <thead>
              <tr>
                <th v-for="(header, i) in csvPreview[0]" :key="i" class="px-2 py-1 border-b">{{ header }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, idx) in csvPreview.slice(1, 6)" :key="idx">
                <td v-for="(cell, i) in row" :key="i" class="px-2 py-1 border-b">{{ cell }}</td>
              </tr>
            </tbody>
          </table>
          <div v-if="csvPreview.length > 6" class="text-gray-500 text-xs mt-1">Showing first 5 rows...</div>
        </div>
      </div>

      <div v-if="errors && errors.length" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <h2 class="font-semibold mb-2">Upload Errors:</h2>
        <ul>
          <li v-for="(error, idx) in errors" :key="idx">
            <span class="font-bold">Row {{ error.row }}:</span>
            <ul class="ml-4 list-disc">
              <li v-for="(msg, i) in error.messages" :key="i">{{ msg }}</li>
            </ul>
          </li>
        </ul>
      </div>

      <div v-if="success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        <span>Batch upload successful!</span>
      </div>
    </div>
  </AppLayout>
</template>
