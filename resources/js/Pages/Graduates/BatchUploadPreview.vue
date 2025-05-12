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
      parsedRows.value = results.data.map(row => ({
        email: row.email || '',
        first_name: row.first_name || '',
        last_name: row.last_name || '',
        middle_initial: row.middle_initial || '',
        program_completed: row.program_completed || '',
        year_graduated: row.year_graduated || '',
        dob: row.dob || '',
        gender: row.gender || '',
        contact_number: row.contact_number || '',
        employment_status: row.employment_status || '',
        current_job_title: row.current_job_title || '',
      }))

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
    if (!row.program_completed) rowErrors.push('Missing program')
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
  })
}

function submitToBackend() {
  const formData = new FormData()
  formData.append('csv_file', file.value)

  router.post(route('graduates.batch.upload'), formData, {
    forceFormData: true,
    preserveState: true,
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
              <th class="p-2 border">Last Name</th>
              <th class="p-2 border">Program</th>
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
              <td class="border p-2">{{ row.last_name }}</td>
              <td class="border p-2">{{ row.program_completed }}</td>
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
  </AppLayout>
</template>
