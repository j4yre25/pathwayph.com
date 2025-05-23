<script setup>
import { ref } from 'vue'
import Papa from 'papaparse'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import dayjs from 'dayjs'


const file = ref(null)
const parsedRows = ref([])
const validationErrors = ref([])
const isValid = ref(false)
const isLoading = ref(false)
const loadingPercent = ref(0)
let loadingInterval = null

const props = defineProps({
  degreeCodes: Array,
  programCodes: Array,
})

function handleFileUpload(e) {
  file.value = e.target.files[0]
  parsedRows.value = []
  validationErrors.value = []
  isValid.value = false

  if (!file.value) return

  Papa.parse(file.value, {
    skipEmptyLines: true,
    beforeFirstChunk: (chunk) => chunk.replace(/^\uFEFF/, ''),
    header: true,
    transformHeader: header => header.trim().toLowerCase(),
    complete: (results) => {
      // Log headers to debug
      console.log('CSV headers:', Object.keys(results.data[0] || {}))

      // Normalize field keys to expected camelCase
      parsedRows.value = results.data.map(row => {
        let dob = row.dob || ''
        if (dob && dob.includes('/')) {
          const parsed = dayjs(dob, 'MM/DD/YYYY', true)
          if (parsed.isValid()) dob = parsed.format('YYYY-MM-DD')
        }
        return {
          email: row.email || '',
          first_name: toTitleCase(row.first_name || ''), // Apply title case transformation
          last_name: toTitleCase(row.last_name || ''),   // Apply title case transformation
          middle_name: toTitleCase(row.middle_name || ''), // Apply title case transformation
          degree_code: (row.degree_code || '').toUpperCase(),
          program_code: (row.program_code || '').toUpperCase(),
          year_graduated: row.year_graduated || '',
          dob: dob,
          gender: (row.gender || '').charAt(0).toUpperCase() + (row.gender || '').slice(1).toLowerCase(),
          contact_number: row.contact_number || '',
          employment_status: (row.employment_status || '').charAt(0).toUpperCase() + (row.employment_status || '').slice(1).toLowerCase(),
          current_job_title: toTitleCase(row.current_job_title || ''), // Apply title case transformation
        }
      })

      validateRows()
    },
    error: (err) => {
      alert('❌ Failed to parse CSV file.')
      console.error('CSV Parsing Error:', err)
    },
  })
}


function validateRows() {
  validationErrors.value = []
  isValid.value = true

  parsedRows.value.forEach((row, index) => {
    const rowErrors = []

    if (!row.email) rowErrors.push('Missing email')
    if (!row.first_name) rowErrors.push('Missing first name')
    if (!row.last_name) rowErrors.push('Missing last name')
    if (!row.year_graduated) rowErrors.push('Missing year')
    if (row.dob) {
      const parsedDate = dayjs(row.dob, 'MM/DD/YYYY', true)
      if (parsedDate.isValid()) {
        row.dob = parsedDate.format('YYYY-MM-DD') // Update the row with the correct format
      } else {
        rowErrors.push('Invalid date format for DOB (expected MM/DD/YYYY)')
      }
    } else {
      rowErrors.push('Missing date of birth')
    }
    if (!row.gender) rowErrors.push('Missing gender')
    if (!row.contact_number) rowErrors.push('Missing contact number')
    if (!row.employment_status) rowErrors.push('Missing employment status')

    if (
      ['Employed', 'Underemployed'].includes(row.employment_status) &&
      !row.current_job_title
    ) {
      rowErrors.push('Missing job title for employed graduate')
    }

    const emailRegex = /\S+@\S+\.\S+/
    if (row.email && !emailRegex.test(row.email)) {
      rowErrors.push('Invalid email format')
    }

    if (rowErrors.length) {
      isValid.value = false
      validationErrors.value.push({
        row: index + 2, // +2 to account for header and zero index
        messages: rowErrors,
      })
    }

    // Degree and program code validation
    if (!props.degreeCodes.includes(row.degree_code)) {
      isValid.value = false
      validationErrors.value.push({
        row: index + 2,
        messages: [`Degree code '${row.degree_code}' does not exist for this institution.`],
      })
    }
    if (!props.programCodes.includes(row.program_code)) {
      isValid.value = false
      validationErrors.value.push({
        row: index + 2,
        messages: [`Program code '${row.program_code}' does not exist for this institution.`],
      })
    }
  })
}

function toTitleCase(str) {
  if (!str) return ''
  return str
    .toLowerCase()
    .split(' ')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
}

function submitToBackend() {
  isLoading.value = true
  loadingPercent.value = 0

  // Simulate progress (since we can't get real progress from backend)
  loadingInterval = setInterval(() => {
    if (loadingPercent.value < 95) {
      loadingPercent.value += Math.random() * 2 // random slow increment
    }
  }, 200)

  const formData = new FormData()
  formData.append('csv_file', file.value)

  router.post(route('graduates.batch.upload'), formData, {
    forceFormData: true,
    preserveState: true,
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
  <AppLayout title="Batch Upload Graduates">
    <div class="my-8 p-6 bg-white rounded-xl shadow space-y-6">
      <h2 class="text-xl font-semibold">📋 Upload & Preview CSV</h2>

      <!-- CSV Template Download Button -->
      <div>
        <a
          :href="route('graduates.template.download')"
          class="inline-block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 mb-4"
          download
        >
          ⬇ Download CSV Template
        </a>
      </div>

      <!-- Upload CSV -->
      <div>
        <input type="file" @change="handleFileUpload" accept=".csv" />
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

      <!-- Preview Table -->
      <div v-if="parsedRows.length" class="overflow-x-auto">
        <table class="w-full border text-sm">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-2 border">#</th>
              <th class="p-2 border">Email</th>
              <th class="p-2 border">First Name</th>
              <th class="p-2 border">Middle Name</th>
              <th class="p-2 border">Last Name</th>
              <th class="p-2 border">Degree Code</th>
              <th class="p-2 border">Program Code</th>
              <th class="p-2 border">Year Graduated</th>
              <th class="p-2 border">DOB</th>
              <th class="p-2 border">Gender</th>
              <th class="p-2 border">Contact No.</th>
              <th class="p-2 border">Employment</th>
              <th class="p-2 border">Job Title</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, i) in parsedRows" :key="i">
              <td class="border p-2">{{ i + 2 }}</td>
              <td class="border p-2">{{ row.email }}</td>
              <td class="border p-2">{{ row.first_name }}</td>
              <td class="border p-2">{{ row.middle_name }}</td>
              <td class="border p-2">{{ row.last_name }}</td>
              <td class="border p-2">{{ row.degree_code }}</td>
              <td class="border p-2">{{ row.program_code }}</td>
              <td class="border p-2">{{ row.year_graduated }}</td>
              <td class="border p-2">{{ row.dob }}</td>
              <td class="border p-2">{{ row.gender }}</td>
              <td class="border p-2">{{ row.contact_number }}</td>
              <td class="border p-2">{{ row.employment_status }}</td>
              <td class="border p-2">{{ row.current_job_title || 'N/A' }}</td>
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
          Submit Valid Data
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
        <div class="mb-2 text-lg font-semibold text-gray-700">Uploading graduates...</div>
        <div class="w-64 bg-gray-200 rounded-full h-4 mb-2">
          <div class="bg-blue-600 h-4 rounded-full transition-all duration-200" :style="{ width: loadingPercent + '%' }"></div>
        </div>
        <div class="text-sm text-gray-500">{{ Math.floor(loadingPercent) }}% complete</div>
        <div class="text-xs text-gray-400 mt-2">Please do not close or refresh this page.</div>
      </div>
    </div>
  </AppLayout>
</template>
