<script setup>
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Container from '@/Components/Container.vue'
import axios from 'axios'
import Papa from 'papaparse'
import { router } from '@inertiajs/vue3'
import '@fortawesome/fontawesome-free/css/all.css';

const csvFile = ref(null)
const csvPreview = ref([])
const errors = ref([])
const success = ref(false)
const uploading = ref(false)
const templateUrl = '/admin/manage-users/download'

  // Function to go back to previous page
const goBack = () => {
  window.history.back()
}

function handleFileChange(e) {
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
    <AppLayout title="Batch Upload Preview">
        <Container>
            <!-- Header with back button -->
            <div class="flex items-center mb-6">
                <button @click="goBack" class="mr-4 text-blue-600 hover:text-blue-800 focus:outline-none">
                    <i class="fas fa-chevron-left text-lg"></i>
                </button>
                <h1 class="text-2xl font-semibold text-gray-800">Batch Upload Preview</h1>
            </div>

    <div class="space-y-6">
      <!-- Upload Section -->
      <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Upload CSV File</h3>
        
        <div class="mb-4">
          <p class="text-sm text-gray-600 mb-2">
            Please download the template and fill it with your data before uploading.
          </p>
          <a 
            :href="templateUrl" 
            download 
            class="text-blue-600 hover:text-blue-800 underline text-sm flex items-center"
          >
            <i class="fas fa-download mr-1"></i> Download Template
          </a>
        </div>

        <div class="flex items-center justify-center w-full">
          <label 
            class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors duration-300"
          >
            <div class="flex flex-col items-center justify-center pt-5 pb-6">
              <i class="fas fa-cloud-upload-alt text-blue-500 text-3xl mb-2"></i>
              <p class="mb-2 text-sm text-gray-500">
                <span class="font-semibold">Click to upload</span> or drag and drop
              </p>
              <p class="text-xs text-gray-500">CSV file only</p>
            </div>
            <input 
              type="file" 
              class="hidden" 
              accept=".csv" 
              @change="handleFileChange"
            />
          </label>
        </div>
      </div>

      <!-- Preview Card -->
      <div v-if="csvPreview.length" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
        <div class="p-4 flex items-center justify-between border-b border-gray-200">
          <div class="flex items-center">
            <i class="fas fa-table text-blue-500 mr-2"></i>
            <h3 class="text-lg font-semibold text-gray-800">CSV Preview</h3>
          </div>
          <span class="text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">
            Showing first 5 rows
          </span>
        </div>
        
        <div class="p-4">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th v-for="(header, i) in csvPreview[0]" :key="i" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ header }}
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(row, idx) in csvPreview.slice(1, 6)" :key="idx" class="hover:bg-gray-50 transition-colors duration-150">
                  <td v-for="(cell, i) in row" :key="i" class="px-3 py-2 whitespace-nowrap text-sm text-gray-700">
                    {{ cell }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        
        <div class="mt-6 flex justify-end p-4 border-t border-gray-200">
          <button 
            @click="handleUpload" 
            :disabled="uploading"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
          >
            <i class="fas fa-upload mr-2"></i>
            {{ uploading ? 'Uploading...' : 'Upload' }}
          </button>
        </div>
      </div>

      <!-- Error Card -->
      <div v-if="errors && errors.length" class="bg-white rounded-lg shadow-sm border border-red-200 overflow-hidden transition-all duration-200 hover:shadow-md">
        <div class="p-4 flex items-center justify-between border-b border-red-200 bg-red-50">
          <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
            <h3 class="text-lg font-semibold text-red-700">Upload Errors</h3>
          </div>
        </div>
        
        <div class="p-4 bg-white">
          <ul class="space-y-3">
            <li v-for="(error, idx) in errors" :key="idx" class="border-l-4 border-red-400 pl-3 py-2">
              <span class="font-bold text-red-700">Row {{ error.row }}:</span>
              <ul class="ml-4 list-disc text-sm text-red-600 mt-1">
                <li v-for="(msg, i) in error.messages" :key="i">{{ msg }}</li>
              </ul>
            </li>
          </ul>
        </div>
      </div>

      <!-- Success Card -->
      <div v-if="success" class="bg-white rounded-lg shadow-sm border border-green-200 overflow-hidden transition-all duration-200 hover:shadow-md">
        <div class="p-4 flex items-center bg-green-50">
          <i class="fas fa-check-circle text-green-500 mr-2"></i>
          <span class="font-medium text-green-700">Batch upload completed successfully!</span>
        </div>
        
        <div class="mt-4 p-4 border-t border-gray-200">
          <button 
            @click="goBack" 
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50 transition-colors duration-200 flex items-center"
          >
            <i class="fas fa-arrow-left mr-2"></i> Back to Users
          </button>
        </div>
      </div>
    </Container>
  </AppLayout>
</template>
