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
        <template #header>
            <div class="flex items-center">
                <button 
                    @click="goBack"
                    class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-upload text-blue-500 text-xl mr-2"></i> Batch Upload Preview
                </h2>
            </div>
        </template>
        
        <Container class="py-6 space-y-6">

    <div class="space-y-6">
      <!-- Upload Section -->
      <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl shadow-lg p-6 border border-blue-200 relative overflow-hidden transition-all duration-300 hover:shadow-xl">
        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-200/20 to-indigo-300/20 rounded-full -mr-16 -mt-16"></div>
        <div class="relative">
          <div class="flex items-center mb-4">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-2 mr-3 shadow-md">
              <i class="fas fa-upload text-white"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-800">Upload CSV File</h3>
              <p class="text-sm text-gray-600">Import users in bulk using CSV format</p>
            </div>
          </div>
          
          <div class="mb-6">
            <p class="text-sm text-gray-700 mb-3">
              Please download the template and fill it with your data before uploading.
            </p>
            <a 
              :href="templateUrl" 
              download 
              class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition-all duration-200 shadow-sm hover:shadow-md text-sm font-medium">
              <i class="fas fa-download mr-2"></i> 
              Download Template
            </a>
          </div>
        </div>

          <div class="flex items-center justify-center w-full">
            <label 
              class="flex flex-col items-center justify-center w-full h-40 border-2 border-blue-300 border-dashed rounded-xl cursor-pointer bg-white/50 hover:bg-white/80 transition-all duration-300 hover:border-blue-400 hover:shadow-md">
              <div class="flex flex-col items-center justify-center pt-5 pb-6">
                <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-full p-4 mb-3 shadow-sm">
                  <i class="fas fa-cloud-upload-alt text-blue-600 text-2xl"></i>
                </div>
                <p class="mb-2 text-sm text-gray-700">
                  <span class="font-semibold text-blue-600">Click to upload</span> or drag and drop
                </p>
                <p class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">CSV file only</p>
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
      </div>

      <!-- Preview Card -->
      <div v-if="csvPreview.length" class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-lg border border-gray-200 overflow-hidden transition-all duration-300 hover:shadow-xl">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="bg-white/20 rounded-lg p-2 mr-3">
                <i class="fas fa-table text-white"></i>
              </div>
              <div>
                <h3 class="text-lg font-bold text-white">CSV Preview</h3>
                <p class="text-blue-100 text-sm">{{ csvPreview.length - 1 }} records found</p>
              </div>
            </div>
            <span class="text-xs font-medium text-blue-100 bg-white/20 rounded-full px-3 py-1">
              Showing first 5 rows
            </span>
          </div>
        </div>
        
        <div class="p-6">
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead>
                <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                  <th v-for="(header, i) in csvPreview[0]" :key="i" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>{{ header }}
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="(row, idx) in csvPreview.slice(1, 6)" :key="idx" class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200">
                  <td v-for="(cell, i) in row" :key="i" class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    {{ cell }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        
        <div class="flex justify-end p-6 bg-gradient-to-r from-gray-50 to-gray-100 border-t border-gray-200">
          <button 
            @click="handleUpload" 
            :disabled="uploading"
            class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center shadow-md hover:shadow-lg font-medium">
            <i class="fas fa-upload mr-2"></i>
            {{ uploading ? 'Uploading...' : 'Upload CSV Data' }}
          </button>
        </div>
      </div>

      <!-- Error Card -->
      <div v-if="errors && errors.length" class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl shadow-lg border border-red-200 overflow-hidden transition-all duration-300 hover:shadow-xl">
        <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4">
          <div class="flex items-center">
            <div class="bg-white/20 rounded-lg p-2 mr-3">
              <i class="fas fa-exclamation-circle text-white"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-white">Upload Errors</h3>
              <p class="text-red-100 text-sm">{{ errors.length }} errors found</p>
            </div>
          </div>
        </div>
        
        <div class="p-6">
          <ul class="space-y-4">
            <li v-for="(error, idx) in errors" :key="idx" class="bg-white rounded-lg p-4 border-l-4 border-red-400 shadow-sm">
              <div class="flex items-center mb-2">
                <div class="bg-red-100 rounded-full w-6 h-6 flex items-center justify-center mr-2">
                  <span class="text-red-600 text-xs font-bold">{{ error.row }}</span>
                </div>
                <span class="font-bold text-red-700">Row {{ error.row }} Issues:</span>
              </div>
              <ul class="ml-8 space-y-1">
                <li v-for="(msg, i) in error.messages" :key="i" class="flex items-center text-sm text-red-600">
                  <i class="fas fa-times-circle text-red-400 mr-2 text-xs"></i>
                  {{ msg }}
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </div>

      <!-- Success Card -->
      <div v-if="success" class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-lg border border-green-200 overflow-hidden transition-all duration-300 hover:shadow-xl">
        <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
          <div class="flex items-center">
            <div class="bg-white/20 rounded-lg p-2 mr-3">
              <i class="fas fa-check-circle text-white"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-white">Upload Successful!</h3>
              <p class="text-green-100 text-sm">Batch upload completed successfully!</p>
            </div>
          </div>
        </div>
        
        <div class="p-6 bg-gradient-to-r from-gray-50 to-gray-100">
          <button 
            @click="goBack" 
            class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 flex items-center shadow-md hover:shadow-lg font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Back to Users
          </button>
        </div>
      </div>
 
    </Container>
  </AppLayout>
</template>
