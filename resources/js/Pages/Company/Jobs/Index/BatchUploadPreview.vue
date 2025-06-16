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
          sector_name: row.sector_name || '',
          category_name: row.category_name || '',
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
          sector_name: row.sector_name || '',
          category_name: row.category_name || '',
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
    if (!row.sector_name) rowErrors.push('Missing sector')
    if (!row.category_name) rowErrors.push('Missing category')
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
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded">
          <h2 class="font-bold text-lg mb-2 flex items-center">
            📝 How to Batch Upload Jobs (Step-by-Step Guide)
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
              <th class="p-2 border">Sector</th>
              <th class="p-2 border">Category</th>
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
              <td class="border p-2">{{ row.sector_name }}</td>
              <td class="border p-2">{{ row.category_name }}</td>
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