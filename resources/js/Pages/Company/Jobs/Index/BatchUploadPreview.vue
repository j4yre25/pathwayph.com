<script setup>
import { ref } from 'vue'
import Papa from 'papaparse'
import * as XLSX from 'xlsx'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const file = ref(null)
const parsedRows = ref([])
const validationErrors = ref([])
const isValid = ref(false)
const isLoading = ref(false)
const loadingPercent = ref(0)
let loadingInterval = null
const showInstructions = ref(false) // mobile fallback toggle


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
</script>

<template>
  <AppLayout title="Batch Upload Jobs">
    <div class="my-8 p-6 bg-white rounded-xl shadow space-y-6">
        <h2 class="text-xl font-semibold">📋 Upload & Preview CSV/EXCEL</h2>
        
        <!-- CSV/Excel Template Download Button -->
        <div>
            <a
            :href="route('company.jobs.template.download')"
            class="inline-block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 mb-4"
            download
            >
              ⬇ Download Excel Template
          </a>
        <!-- 📝 Instructions Block -->
        <div
  class="relative group mb-6"
  @click="showInstructions = !showInstructions"
>
  <!-- Trigger / Label -->
  <div class="flex items-center gap-2 cursor-pointer text-sm font-semibold text-blue-700">
    <span>📝 Batch Upload Instructions </span>
    <span
      class="inline-block text-xs px-2 py-0.5 rounded bg-blue-100 text-blue-600 border border-blue-300"
    >Help</span>
  </div>

  <!-- Hidden Panel -->
  <div
    class="absolute left-0 mt-2 w-[52rem] max-w-[90vw] bg-white border border-blue-300 rounded-lg shadow-lg p-5 text-gray-800 text-sm leading-relaxed z-30
           opacity-0 invisible group-hover:opacity-100 group-hover:visible
           transition duration-200 ease-out
           md:pointer-events-auto"
    :class="{
      'opacity-100 visible': showInstructions
    }"
  >
    <div class="flex justify-between items-start mb-2">
      <h2 class="font-bold text-base">How to Batch Upload Jobs</h2>
      <button
        type="button"
        class="text-gray-400 hover:text-gray-600 text-xs"
        @click.stop="showInstructions = false"
      >✕</button>
    </div>
    <ol class="list-decimal pl-5 space-y-3">
      <li>
        <span class="font-semibold">Download the Excel Template.</span><br>
        Click the
        <span class="font-mono bg-green-100 px-2 py-0.5 rounded">Download Excel Template</span>
        button.
      </li>
      <li>
        <span class="font-semibold">Fill out the <code>job_template</code> sheet.</span><br>
        Enter data under the columns exactly as shown in the preview.<br>
        ➤ For detailed guidance, check the <code>other_info</code> sheet.<br>
        ➤ For an example format, refer to the <code>example</code> sheet included in the template.<br>
        ⚠️ <strong>Do not rename or remove any column headers</strong>.<br>
        <span class="text-xs text-gray-600">
          Only optional column: <code>job_application_limit</code>. If <code>is_negotiable</code> = yes, leave min/max salary blank.
        </span>
      </li>
      <li>
        <span class="font-semibold">Save the file</span> as <code>.xlsx</code> or <code>.csv</code>.
      </li>
      <li>
        <span class="font-semibold">Upload the file.</span><br>
        A preview will be displayed. If you see errors, correct your file and re-upload.
      </li>
      <li>
        <span class="font-semibold">Submit.</span><br>
        Only valid rows will be saved. You will receive a summary of jobs posted and graduates invited.
      </li>
    </ol>
    <div class="mt-3 text-right">
      <button
        type="button"
        class="text-xs text-blue-600 hover:underline"
        @click.stop="showInstructions = false"
      >Hide</button>
    </div>
  </div>
</div>

      </div>

      <!-- Upload CSV/Excel -->
      <div>
        <input type="file" @change="handleFileUpload" accept=".csv,.xlsx,.xls" />
      </div>

      <!-- Validation Errors -->
      <div v-if="validationErrors.length" class="bg-red-100 text-red-800 p-3 rounded">
        <p class="font-bold mb-2">❌ Issues detected:</p>
        <ul class="list-disc pl-5 space-y-1">
          <li v-for="error in validationErrors" :key="error.row">
            Row {{ error.row }}:
            <span v-for="msg in error.messages" :key="msg">{{ msg }}; </span>
          </li>
        </ul>
      </div>

       <!-- Submit Button -->
      <div v-if="parsedRows.length" class="flex justify-end">
        <button
          @click="submitToBackend"
          :disabled="!isValid"
          class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
        >
          Submit Data
        </button>
      </div>

      <!-- Preview Table -->
      <div v-if="parsedRows.length" class="overflow-x-auto">
        <table class="w-full border text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-2 border">#</th>
              <th class="p-2 border">Job Title</th>
              <th class="p-2 border">Department</th>
              <th class="p-2 border">Job Types</th>
              <th class="p-2 border">Program</th>
              <th class="p-2 border">Location</th>
              <th class="p-2 border">Work Environment</th>
              <th class="p-2 border">Description</th>
              <th class="p-2 border">Requirements</th>
              <th class="p-2 border">Is Negotiable?</th>
              <th class="p-2 border">Salary Type</th>
              <th class="p-2 border">Min Salary</th>
              <th class="p-2 border">Max Salary</th>
              <th class="p-2 border">Experience Level</th>
              <th class="p-2 border">Skills</th>
              <th class="p-2 border">Vacancies</th>
              <th class="p-2 border">Deadline</th>
              <th class="p-2 border">Application Limit</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, i) in parsedRows" :key="i">
              <td class="border p-2">{{ i + 2 }}</td>
              <td class="border p-2">{{ row.job_title }}</td>
              <td class="border p-2">{{ row.department_name }}</td>
              <td class="border p-2">{{ row.job_types }}</td>
              <td class="border p-2">{{ row.program_name }}</td>
              <td class="border p-2">{{ row.location }}</td>
              <td class="border p-2">{{ row.work_environment_type }}</td>
              <td class="border p-2">{{ row.job_description }}</td>
              <td class="border p-2">{{ row.job_requirements }}</td>
              <td class="border p-2">{{ row.is_negotiable ? 'Yes' : 'No' }}</td>
              <td class="border p-2">{{ row.salary_type }}</td>
              <td class="border p-2">{{ row.job_min_salary }}</td>
              <td class="border p-2">{{ row.job_max_salary }}</td>
              <td class="border p-2">{{ row.job_experience_level }}</td>
              <td class="border p-2">{{ row.skills }}</td>
              <td class="border p-2">{{ row.job_vacancies }}</td>
              <td class="border p-2">{{ row.job_deadline }}</td>
              <td class="border p-2">{{ row.job_application_limit }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Submit Button -->
      <div v-if="parsedRows.length" class="flex justify-end">
        <button
          @click="submitToBackend"
          :disabled="!isValid"
          class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
        >
          Submit Data
        </button>
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
  </AppLayout>
</template>