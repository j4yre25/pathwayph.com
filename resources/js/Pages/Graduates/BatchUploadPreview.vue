<script setup>
import { ref, computed } from 'vue'
import Papa from 'papaparse'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import dayjs from 'dayjs'
import '@fortawesome/fontawesome-free/css/all.css';

const file = ref(null)
const parsedRows = ref([])
const validationErrors = ref([])
const isValid = ref(false)
const isLoading = ref(false)
const loadingPercent = ref(0)
let loadingInterval = null
const fileInputRef = ref(null)

const props = defineProps({
  degreeCodes: Array,
  programCodes: Array,
  companyNames: Array,
  companyNamesMap: Object,
  institutionSchoolYears: Array,
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
      parsedRows.value = results.data.map(row => {
        let dob = row.dob || '';
        if (dob && dob.includes('/')) {
          const parsed = dayjs(dob, 'MM/DD/YYYY', true);
          if (parsed.isValid()) dob = parsed.format('YYYY-MM-DD');
        }

        // Normalize employment status and company name
        let employmentStatus = (row.employment_status || '').trim().toUpperCase();
        let inputCompanyName = (row.company_name || '').trim();
        let normalizedCompany = inputCompanyName.toLowerCase().replace(/\s+/g, ' ');
        let displayCompanyName = inputCompanyName;
        if (employmentStatus === 'UNEMPLOYED') {
          displayCompanyName = 'N/A';
        } else if (props.companyNamesMap && props.companyNamesMap[normalizedCompany]) {
          displayCompanyName = props.companyNamesMap[normalizedCompany];
        }

        // Normalize term (case-insensitive, allow string or number)
        let inputTerm = (row.term || '').toString().trim();
        let normalizedTerm = inputTerm.toLowerCase() === 'summer' ? 'summer' : inputTerm;

        return {
          email: row.email || '',
          first_name: toTitleCase(row.first_name || ''),
          last_name: toTitleCase(row.last_name || ''),
          middle_name: toTitleCase(row.middle_name || ''),
          degree_code: (row.degree_code || '').toUpperCase(),
          program_code: (row.program_code || '').toUpperCase(),
          year_graduated: row.year_graduated || '',
          dob: dob,
          gender: (row.gender || '').charAt(0).toUpperCase() + (row.gender || '').slice(1).toLowerCase(),
          contact_number: row.contact_number || '',
          employment_status: employmentStatus,
          company_name: displayCompanyName,
          raw_company_name: inputCompanyName, // for validation
          current_job_title: toTitleCase(row.current_job_title || ''),
          term: normalizedTerm,
          raw_term: row.term || '',
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

  // Normalize institution school years for case-insensitive term matching
  const validYearTermPairs = props.institutionSchoolYears.map(
    y => `${y.school_year_range}|${(y.term || '').toString().toLowerCase()}`
  );

  parsedRows.value.forEach((row, index) => {
    const rowErrors = [];

    if (!row.email) rowErrors.push('Missing email')
    if (!row.first_name) rowErrors.push('Missing first name')
    if (!row.last_name) rowErrors.push('Missing last name')
    if (!row.year_graduated) rowErrors.push('Missing year')
    if (row.dob) {
      const parsedDate = dayjs(row.dob, 'YYYY-MM-DD', true)
      if (!parsedDate.isValid()) {
        rowErrors.push('Invalid date format for DOB (expected MM/DD/YYYY or YYYY-MM-DD)')
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

    // Year Graduated + Term validation (case-insensitive for term)
    if (!row.year_graduated || !row.term) {
      rowErrors.push('Missing year graduated or term')
    } else {
      const pair = `${row.year_graduated}|${row.term.toString().toLowerCase()}`;
      if (!validYearTermPairs.includes(pair)) {
        rowErrors.push(
          `Year graduated '${row.year_graduated}' with term '${row.raw_term}' does not exist for this institution.`
        );
      }
    }

    // Degree and program code validation
    if (!props.degreeCodes.includes(row.degree_code)) {
      rowErrors.push(`Degree code '${row.degree_code}' does not exist for this institution.`)
    }
    if (!props.programCodes.includes(row.program_code)) {
      rowErrors.push(`Program code '${row.program_code}' does not exist for this institution.`)
    }

    // Company name validation
    if (
      ['Employed', 'Underemployed'].includes(row.employment_status)
    ) {
      if (!row.raw_company_name) {
        rowErrors.push('Company name is required for employed/underemployed graduates.');
      } else {
        let normalized = row.raw_company_name.toLowerCase().replace(/\s+/g, ' ');
        if (!props.companyNamesMap[normalized]) {
          rowErrors.push(`Company name '${row.raw_company_name}' does not exist in the system.`);
        }
      }
    }
    if (
      row.employment_status === 'UNEMPLOYED' &&
      row.raw_company_name &&
      row.raw_company_name.trim().toLowerCase() !== 'n/a'
    ) {
      rowErrors.push('For unemployed graduates, company name must be "N/A" (any case).');
    }

    if (rowErrors.length) {
      isValid.value = false;
      validationErrors.value.push({
        row: index + 2,
        messages: rowErrors,
      });
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

  loadingInterval = setInterval(() => {
    if (loadingPercent.value < 95) {
      loadingPercent.value += Math.random() * 2
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
function triggerFileInput() {
  fileInputRef.value.click()
}

function goBack() {
  window.history.back()
}

const stats = computed(() => {
  return [
    {
      title: 'Total Records',
      value: parsedRows.value.length,
      icon: 'fas fa-file-alt',
      color: 'text-blue-600',
      bgColor: 'bg-blue-100'
    },
    {
      title: 'Valid Records',
      value: parsedRows.value.length - new Set(validationErrors.value.map(e => e.row)).size,
      icon: 'fas fa-check-circle',
      color: 'text-green-600',
      bgColor: 'bg-green-100'
    },
    {
      title: 'Records with Errors',
      value: new Set(validationErrors.value.map(e => e.row)).size,
      icon: 'fas fa-exclamation-triangle',
      color: 'text-red-600',
      bgColor: 'bg-red-100'
    }
  ]
})
</script>


<template>
  <AppLayout title="Batch Upload Graduates">
    <!-- Back Button and Header -->
    <div class="flex items-center mt-6 mb-4 px-6">
      <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
        <i class="fas fa-chevron-left"></i>
      </button>
      <div class="flex items-center">
        <i class="fas fa-upload text-blue-500 text-xl mr-2"></i>
        <h1 class="text-2xl font-bold text-gray-800">Batch Upload Graduates</h1>
      </div>
    </div>

    <!-- Stats Cards (only show when data is loaded) -->
    <div v-if="parsedRows.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6 px-6 mb-6">
      <div v-for="(stat, index) in stats" :key="index" 
           class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 relative overflow-hidden">
        <div class="flex justify-between items-start">
          <div>
            <h3 class="text-gray-600 text-sm font-medium mb-2">{{ stat.title }}</h3>
            <p class="text-3xl font-bold text-gray-800">{{ stat.value }}</p>
          </div>
          <div :class="[stat.bgColor, 'rounded-full p-3 flex items-center justify-center']">
            <i :class="[stat.icon, stat.color]"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="px-6">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
        <!-- Header with back button -->
        <div class="p-5 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <i class="fas fa-file-csv text-blue-500 text-xl mr-2"></i>
              <h2 class="text-lg font-semibold text-gray-800">Upload & Preview CSV</h2>
            </div>
            <a :href="route('graduates.template.download')" 
               class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg font-medium text-sm text-gray-700 hover:bg-gray-200 transition">
              <i class="fas fa-download mr-2"></i>
              Download CSV Template
            </a>
          </div>
        </div>

        <!-- File Upload Section -->
        <div class="p-5 border-b border-gray-200">
          <input ref="fileInputRef" id="file-upload" type="file" @change="handleFileUpload" accept=".csv" class="hidden">
          <div @click="triggerFileInput" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-500 transition-colors">
            <div class="flex flex-col items-center justify-center space-y-2">
              <i class="fas fa-cloud-upload-alt text-4xl text-gray-400"></i>
              <p class="text-gray-700 font-medium">Click to upload CSV file</p>
              <p class="text-xs text-gray-500">or drag and drop</p>
              <p v-if="file" class="text-sm text-blue-600 font-medium mt-2">
                <i class="fas fa-file-alt mr-1"></i> {{ file.name }}
              </p>
            </div>
          </div>
        </div>

        <!-- Validation Errors -->
        <div v-if="validationErrors.length" class="p-5 border-b border-gray-200">
          <div class="flex items-center mb-4">
            <i class="fas fa-exclamation-circle text-red-500 text-xl mr-2"></i>
            <h3 class="font-semibold text-gray-800">Validation Issues</h3>
          </div>
          <div class="bg-red-50 rounded-lg p-4 border border-red-100 max-h-60 overflow-y-auto">
            <ul class="space-y-2">
              <li v-for="error in validationErrors" :key="error.row" class="p-2 bg-white rounded border border-red-200">
                <div class="font-medium text-red-700 flex items-center">
                  <i class="fas fa-times-circle mr-2"></i>
                  Row {{ error.row }}
                </div>
                <ul class="mt-1 pl-6 text-sm text-red-600 space-y-1">
                  <li v-for="(msg, idx) in error.messages" :key="idx" class="flex items-start">
                    <span class="inline-block w-2 h-2 rounded-full bg-red-400 mt-1.5 mr-2"></span>
                    {{ msg }}
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </div>

        <!-- Preview Table -->
        <div v-if="parsedRows.length" class="p-5">
          <div class="flex items-center mb-4">
            <i class="fas fa-table text-blue-500 mr-2"></i>
            <h3 class="font-semibold text-gray-800">Data Preview</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full border-collapse">
              <thead class="bg-gray-50">
                <tr>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">#</th>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Email</th>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">First Name</th>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Middle Name</th>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Last Name</th>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Degree Code</th>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Program Code</th>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Year Graduated</th>
              <th class="p-2 border">Term</th>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">DOB</th>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Gender</th>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Contact No.</th>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Employment</th>
              <th class="p-2 border">Company Name</th>
                  <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Job Title</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(row, i) in parsedRows" :key="i" 
                    :class="{'bg-red-50': validationErrors.some(e => e.row === i + 2)}">
                  <td class="p-3 whitespace-nowrap text-sm text-gray-900">{{ i + 2 }}</td>
                  <td class="p-3 whitespace-nowrap text-sm">
                    <div class="flex items-center">
                      <i class="fas fa-envelope text-gray-400 mr-2"></i>
                      <span>{{ row.email }}</span>
                    </div>
                  </td>
                  <td class="p-3 whitespace-nowrap text-sm text-gray-900">{{ row.first_name }}</td>
                  <td class="p-3 whitespace-nowrap text-sm text-gray-900">{{ row.middle_name }}</td>
                  <td class="p-3 whitespace-nowrap text-sm text-gray-900">{{ row.last_name }}</td>
                  <td class="p-3 whitespace-nowrap text-sm">
                    <div class="flex items-center">
                      <i class="fas fa-graduation-cap text-gray-400 mr-2"></i>
                      <span>{{ row.degree_code }}</span>
                    </div>
                  </td>
                  <td class="p-3 whitespace-nowrap text-sm">
                    <div class="flex items-center">
                      <i class="fas fa-book text-gray-400 mr-2"></i>
                      <span>{{ row.program_code }}</span>
                    </div>
                  </td>
                  <td class="p-3 whitespace-nowrap text-sm">
                    <div class="flex items-center">
                      <i class="fas fa-calendar-check text-gray-400 mr-2"></i>
                      <span>{{ row.year_graduated }}</span>
                    </div>
                  </td>
              <td class="border p-2">{{ row.term }}</td>
                  <td class="p-3 whitespace-nowrap text-sm">
                    <div class="flex items-center">
                      <i class="fas fa-birthday-cake text-gray-400 mr-2"></i>
                      <span>{{ row.dob }}</span>
                    </div>
                  </td>
                  <td class="p-3 whitespace-nowrap text-sm text-gray-900">{{ row.gender }}</td>
                  <td class="p-3 whitespace-nowrap text-sm">
                    <div class="flex items-center">
                      <i class="fas fa-phone text-gray-400 mr-2"></i>
                      <span>{{ row.contact_number }}</span>
                    </div>
                  </td>
                  <td class="p-3 whitespace-nowrap text-sm">
                    <span :class="{
                      'px-2 py-1 text-xs rounded-full': true,
                      'bg-green-100 text-green-800': row.employment_status === 'Employed',
                      'bg-yellow-100 text-yellow-800': row.employment_status === 'Underemployed',
                      'bg-gray-100 text-gray-800': row.employment_status === 'Unemployed'
                    }">
                      {{ row.employment_status }}
                    </span>
                  </td>
              <td class="border p-2">{{ row.company_name }}</td>
                  <td class="p-3 whitespace-nowrap text-sm text-gray-900">{{ row.current_job_title || 'N/A' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!parsedRows.length && !file" class="p-12 flex flex-col items-center justify-center text-center">
          <i class="fas fa-file-upload text-gray-300 text-5xl mb-4"></i>
          <h3 class="text-lg font-medium text-gray-700 mb-2">No Data to Preview</h3>
          <p class="text-gray-500 mb-6">Upload a CSV file to see a preview of the data</p>
        </div>

        <!-- Submit Button -->
        <div v-if="parsedRows.length" class="p-5 border-t border-gray-200 flex justify-end">
          <button
            @click="submitToBackend"
            :disabled="!isValid"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition"
          >
            <i class="fas fa-cloud-upload-alt mr-2"></i>
            {{ isValid ? 'Submit Valid Data' : 'Fix Errors to Submit' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="isLoading" class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-black bg-opacity-60">
      <div class="bg-white rounded-lg p-8 flex flex-col items-center shadow-lg max-w-md w-full">
        <div class="flex items-center mb-4">
          <i class="fas fa-cloud-upload-alt text-blue-500 text-xl mr-2"></i>
          <h3 class="text-lg font-semibold text-gray-800">Uploading Graduates</h3>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
          <div class="bg-blue-600 h-4 rounded-full transition-all duration-200" :style="{ width: loadingPercent + '%' }"></div>
        </div>
        <div class="text-sm text-gray-600 flex items-center">
          <i class="fas fa-spinner fa-spin mr-2"></i>
          {{ Math.floor(loadingPercent) }}% complete
        </div>
        <div class="text-xs text-gray-400 mt-2">Please do not close or refresh this page.</div>
      </div>
    </div>
  </AppLayout>
</template>
