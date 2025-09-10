<script setup>
import { ref, onMounted } from 'vue'
import Papa from 'papaparse'
import * as XLSX from 'xlsx'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Container from '@/Components/Container.vue'

const file = ref(null)
const parsedRows = ref([])
const validationErrors = ref([])
const isValid = ref(false)
const isLoading = ref(false)
const loadingPercent = ref(0)
let loadingInterval = null

// Instruction modal state
const showInstructionModal = ref(false)
const dontShowAgain = ref(false)

// Check localStorage and show modal on mount
onMounted(() => {
  const hideInstructions = localStorage.getItem('hideBatchUploadInstructions')
  if (!hideInstructions) {
    showInstructionModal.value = true
  }
})

// Close instruction modal
function closeInstructionModal() {
  if (dontShowAgain.value) {
    localStorage.setItem('hideBatchUploadInstructions', 'true')
  }
  showInstructionModal.value = false
}


function handleFileUpload(e) {
  file.value = e.target.files[0]
  parsedRows.value = []
  validationErrors.value = []
  isValid.value = false

  if (!file.value) return
  
  function excelDateToString(excelDate) {
  if (typeof excelDate === 'number') {
    const date = new Date(Math.round((excelDate - 25569) * 86400 * 1000));
    return date.toISOString().slice(0, 10);
  }
  return excelDate || '';
}

  const ext = file.value.name.split('.').pop().toLowerCase()
  if (['xlsx', 'xls'].includes(ext)) {
    // Parse Excel
    const reader = new FileReader()
    reader.onload = (evt) => {
      const data = new Uint8Array(evt.target.result)
      const workbook = XLSX.read(data, { type: 'array' })
      const sheetName = workbook.SheetNames[0]
      const worksheet = workbook.Sheets[sheetName]
      const json = XLSX.utils.sheet_to_json(worksheet, { header: 1 })
      if (!json.length) return

      const headers = json[0].map(h => String(h).trim().toLowerCase())
      parsedRows.value = json.slice(1).map(rowArr => {
        const row = {}
        headers.forEach((h, i) => {
          row[h] = rowArr[i] ?? ''
        })
        console.log('Parsed job_deadline:', row.job_deadline, typeof row.job_deadline);
        return {
          job_title: row.job_title || '',
          department_name: row.department_name || '',
          job_types: row.job_types || '',
          program_name: row.program_name || '',
          location: row.location || '',
          work_environment_type: row.work_environment_type || '',
          job_description: row.job_description || '',
          job_requirements: row.job_requirements || '',
          is_negotiable: row.is_negotiable ? String(row.is_negotiable).toLowerCase() === 'yes' : false,
          salary_type: row.salary_type || '',
          job_min_salary: row.job_min_salary || '',
          job_max_salary: row.job_max_salary || '',
          job_experience_level: row.job_experience_level || '',
          skills: row.skills || '',
          job_vacancies: row.job_vacancies || '',
          job_deadline: excelDateToString(row.job_deadline || ''),
          job_application_limit: row.job_application_limit || '',
        }
      })
      validateRows()
    }
    reader.readAsArrayBuffer(file.value)
  } else {
    // Parse CSV
    Papa.parse(file.value, {
      skipEmptyLines: true,
      beforeFirstChunk: (chunk) => chunk.replace(/^\uFEFF/, ''),
      header: true,
      transformHeader: header => header.trim().toLowerCase(),
      complete: (results) => {
        parsedRows.value = results.data.map(row => ({
          job_title: row.job_title || '',
          department_name: row.department_name || '',
          job_types: row.job_types || '',
          program_name: row.program_name || '',
          location: row.location || '',
          work_environment_type: row.work_environment_type || '',
          job_description: row.job_description || '',
          job_requirements: row.job_requirements || '',
          is_negotiable: row.is_negotiable ? row.is_negotiable.toLowerCase() === 'yes' : false,
          salary_type: row.salary_type || '',
          job_min_salary: row.job_min_salary || '',
          job_max_salary: row.job_max_salary || '',
          job_experience_level: row.job_experience_level || '',
          skills: row.skills || '',
          job_vacancies: row.job_vacancies || '',
          job_deadline: excelDateToString(row.job_deadline || ''),
          job_application_limit: row.job_application_limit || '',
        }))
        validateRows()
      },
      error: (err) => {
        alert('❌ Failed to parse CSV file.')
        console.error('CSV Parsing Error:', err)
      },
    })
  }
}


function validateRows() {
  validationErrors.value = []
  isValid.value = true

  parsedRows.value.forEach((row, index) => {
    const rowErrors = []

    if (!row.job_title) rowErrors.push('Missing job title')
    if (!row.program_name) rowErrors.push('Missing program')
    if (!row.job_vacancies) rowErrors.push('Missing job vacancies')
    if (!row.job_description) rowErrors.push('Missing job description')
    if (!row.job_requirements) rowErrors.push('Missing job requirements')
    if (!row.salary_type) rowErrors.push('Missing salary type')
    if (!row.job_experience_level) rowErrors.push('Missing experience level')
    if (!row.work_environment_type) rowErrors.push('Missing work environment')
    if (!row.job_deadline) rowErrors.push('Missing job deadline')
    if (!row.skills) rowErrors.push('Missing skills')

    if (rowErrors.length) {
      isValid.value = false
      validationErrors.value.push({
        row: index + 2,
        messages: rowErrors,
      })
    }
  })
}

function submitToBackend() {
  isLoading.value = true
  loadingPercent.value = 0

  loadingInterval = setInterval(() => {
    if (loadingPercent.value < 95) {
      loadingPercent.value += Math.random() * 2
    }
  }, 200)

  const formData = new FormData()
  formData.append('csv_file', file.value)

  router.post(route('company.jobs.batch.upload'), formData, {
    forceFormData: true,
    preserveState: false,
    onFinish: () => {
      clearInterval(loadingInterval)
      loadingPercent.value = 100
      setTimeout(() => {
        isLoading.value = false
      }, 500)
    },
    onSuccess: () => {
      parsedRows.value = []
      file.value = null
      validationErrors.value = []
      isValid.value = false
      alert('🎉 Upload successful!')
    },
    onError: (errors) => {
      if (errors.response?.data?.errors) {
        validationErrors.value = errors.response.data.errors
      } else {
        alert('⚠ Backend error. Check console.')
        console.error(errors)
      }
    },
  })
}

const goBack = () => {
    window.history.back();
};
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
                    <i class="fas fa-upload text-blue-500 text-xl mr-2"></i> Batch Upload Jobs
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
              <h3 class="text-lg font-bold text-gray-800">Upload Excel/CSV File</h3>
              <p class="text-sm text-gray-600">Import jobs in bulk using Excel or CSV format</p>
            </div>
          </div>
          
          <div class="mb-6">
            <p class="text-sm text-gray-700 mb-3">
              Please download the template and fill it with your data before uploading.
            </p>
            <a 
              :href="route('company.jobs.template.download')" 
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
                <p class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">Excel/CSV file only</p>
              </div>
              <input 
                type="file" 
                class="hidden" 
                accept=".csv,.xlsx,.xls" 
                @change="handleFileUpload"
              />
            </label>
          </div>
        </div>
      </div>

      <!-- Preview Card -->
      <div v-if="parsedRows.length" class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-lg border border-gray-200 overflow-hidden transition-all duration-300 hover:shadow-xl">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="bg-white/20 rounded-lg p-2 mr-3">
                <i class="fas fa-table text-white"></i>
              </div>
              <div>
                <h3 class="text-lg font-bold text-white">Job Preview</h3>
                <p class="text-blue-100 text-sm">{{ parsedRows.length }} records found</p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="p-6">
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead>
                <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>#
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Job Title
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Department
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Sector
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Category
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Programs
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Location
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Work Environment
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Description
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Requirements
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Is Negotiable?
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Salary Type
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Min Salary
                  </th>
                   <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Max Salary
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Experience Level
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Skills
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Vacancies
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Deadline
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Application Limit
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="(row, idx) in parsedRows.slice(0, 5)" :key="idx" class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ idx + 1 }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.job_title }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.department_name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.sector_name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.category_name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.program_name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.location }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.location }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.work_environment_type }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.job_description }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.job_requirements }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.is_negotiable ? 'Yes' : 'No' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.salary_type }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.job_min_salary }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.job_max_salary }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.job_experience_level }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.skills }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.job_vacancies }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.job_deadline }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ row.job_application_limit }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        
        <div class="flex justify-end p-6 bg-gradient-to-r from-gray-50 to-gray-100 border-t border-gray-200">
          <button 
            @click="submitToBackend" 
            :disabled="!isValid"
            class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center shadow-md hover:shadow-lg font-medium">
            <i class="fas fa-upload mr-2"></i>
            Submit Job Data
          </button>
        </div>
      </div>

      <!-- Error Card -->
      <div v-if="validationErrors && validationErrors.length" class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl shadow-lg border border-red-200 overflow-hidden transition-all duration-300 hover:shadow-xl">
        <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4">
          <div class="flex items-center">
            <div class="bg-white/20 rounded-lg p-2 mr-3">
              <i class="fas fa-exclamation-circle text-white"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-white">Upload Errors</h3>
              <p class="text-red-100 text-sm">{{ validationErrors.length }} errors found</p>
            </div>
          </div>
        </div>
        
        <div class="p-6">
          <ul class="space-y-4">
            <li v-for="(error, idx) in validationErrors" :key="idx" class="bg-white rounded-lg p-4 border-l-4 border-red-400 shadow-sm">
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


    <!-- Loading Overlay -->
    <div v-if="isLoading" class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-black bg-opacity-60">
      <div class="bg-white rounded-lg p-8 flex flex-col items-center shadow-lg">
        <svg class="animate-spin h-10 w-10 text-blue-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
        <div class="mb-2 text-lg font-semibold text-gray-700">Uploading jobs...</div>
        <div class="w-64 bg-gray-200 rounded-full h-4 mb-2">
          <div class="bg-blue-600 h-4 rounded-full transition-all duration-200" :style="{ width: loadingPercent + '%' }"></div>
        </div>
        <div class="text-sm text-gray-500">{{ Math.floor(loadingPercent) }}% complete</div>
        <div class="text-xs text-gray-400 mt-2">Please do not close or refresh this page.</div>
      </div>
    </div>

    <!-- Instruction Modal -->
    <div v-if="showInstructionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
      <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[80vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4 rounded-t-xl">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-white flex items-center">
              <i class="fas fa-info-circle mr-2"></i>
              Batch Upload Instructions
            </h3>
            <button 
              @click="closeInstructionModal"
              class="text-white hover:text-gray-200 transition-colors duration-200">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>
        </div>

        <!-- Modal Content -->
        <div class="p-6">
          <!-- Instructions Block -->
          <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded">
            <h2 class="font-bold text-lg mb-2 flex items-center">
              How to Batch Upload Jobs (Step-by-Step Guide)
            </h2>
            <ol class="list-decimal pl-6 space-y-2 text-sm text-gray-800">
              <li>
                <span class="font-semibold">Download the Excel Template:</span>
                <br>
                Click the <span class="font-mono bg-green-100 px-2 py-1 rounded">Download Excel Template</span> button below.
              </li>
              <li>
                <span class="font-semibold">Fill Out the Template:</span>
                <ul class="list-disc pl-6 mt-1">
                  <li>
                    Open the downloaded file. You will see <span class="font-semibold">four sheets</span> at the bottom:
                    <ul class="list-disc pl-6">
                      <li><span class="font-mono">job_template</span> – Enter the job details here.</li>
                      <li><span class="font-mono">sector</span> – Reference list of sector codes and sector names.</li>
                      <li><span class="font-mono">categories</span> – Detailed list of categories (division names) and their sectors.</li>
                      <li><span class="font-mono">other_info</span> – Explains what to write in each column of <span class="font-mono">job_template</span>.</li>
                    </ul>
                  </li>
                  <li>
                    In the <span class="font-mono">job_template</span> sheet, go to the <span class="font-mono">category_name</span> column.
                  </li>
                  <li>
                    Copy a <span class="font-mono">division_name</span> from the <span class="font-mono">categories</span> sheet and paste it here.
                  </li>
                  <li>
                    The corresponding <span class="font-mono">sector_name</span> will be filled in automatically.
                  </li>
                </ul>
              </li>
              <li>
                <span class="font-semibold">Save the File:</span>
                <br>
                Save your filled-out file as <span class="font-mono">.xlsx</span> or <span class="font-mono">.csv</span>.
              </li>
              <li>
                <span class="font-semibold">Upload the File:</span>
                <br>
                Go back to this page and upload your Excel file below.
                <br>
                A preview will appear—<span class="font-semibold">check carefully</span> that all information is correct.
                <br>
                <span class="text-yellow-700 font-semibold">⚠️ Note:</span> You cannot edit the data in the preview. If you find any mistakes, update your Excel file and re-upload it.
              </li>
              <li>
                <span class="font-semibold">Submit the Data:</span>
                <br>
                Once everything looks correct in the preview, click <span class="font-mono bg-blue-100 px-2 py-1 rounded">Submit Data</span> to complete the upload.
              </li>
            </ol>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-xl border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input 
                type="checkbox" 
                id="dontShowAgain" 
                v-model="dontShowAgain"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
              <label for="dontShowAgain" class="ml-2 text-sm text-gray-700">
                Don't show this again
              </label>
            </div>
            <div class="flex space-x-3">
              <button 
                @click="closeInstructionModal"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 font-medium">
                Got it!
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    </Container>
  </AppLayout>
</template>