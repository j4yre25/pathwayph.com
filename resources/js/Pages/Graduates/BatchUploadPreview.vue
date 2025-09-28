<script setup>
import { ref, computed } from 'vue'
import Papa from 'papaparse'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Container from '@/Components/Container.vue'
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
    <template #header>
      <div class="flex items-center">
        <button 
          @click="goBack"
          class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
          <i class="fas fa-chevron-left"></i>
        </button>
        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
          <i class="fas fa-upload text-blue-500 text-xl mr-2"></i> Batch Upload Graduates
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
              <p class="text-sm text-gray-600">Import graduates in bulk using CSV format</p>
            </div>
          </div>
          
          <div class="mb-6">
            <p class="text-sm text-gray-700 mb-3">
              Please download the template and fill it with your data before uploading.
            </p>
            <a 
              :href="route('graduates.template.download')" 
              class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition-all duration-200 shadow-sm hover:shadow-md text-sm font-medium">
              <i class="fas fa-download mr-2"></i> 
              Download Template
            </a>
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
                <p v-if="file" class="text-sm text-blue-600 font-medium mt-2">
                  <i class="fas fa-file-alt mr-1"></i> {{ file.name }}
                </p>
              </div>
              <input 
                ref="fileInputRef"
                type="file" 
                class="hidden" 
                accept=".csv" 
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
                <h3 class="text-lg font-bold text-white">CSV Preview</h3>
                <p class="text-blue-100 text-sm">{{ parsedRows.length }} records found</p>
              </div>
            </div>
            <span class="text-xs font-medium text-blue-100 bg-white/20 rounded-full px-3 py-1">
              Showing first 5 rows
            </span>
          </div>
        </div>
        
        <!-- Validation Errors -->
        <div v-if="validationErrors.length" class="p-6 border-b border-gray-200">
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

        <div class="p-6">
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead>
                <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>#
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Email
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>First Name
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Middle Name
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Last Name
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Degree Code
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Program Code
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Year Graduated
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-tag text-blue-500 mr-2"></i>Term
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-birthday-cake text-blue-500 mr-2"></i>DOB
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-venus-mars text-blue-500 mr-2"></i>Gender
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-phone text-blue-500 mr-2"></i>Contact No.
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-briefcase text-blue-500 mr-2"></i>Employment
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-building text-blue-500 mr-2"></i>Company Name
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <i class="fas fa-user-tie text-blue-500 mr-2"></i>Job Title
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(row, i) in parsedRows" :key="i" 
                    :class="{'bg-red-50': validationErrors.some(e => e.row === i + 2)}">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ i + 2 }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex items-center">
                      <i class="fas fa-envelope text-gray-400 mr-2"></i>
                      <span>{{ row.email }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ row.first_name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ row.middle_name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ row.last_name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex items-center">
                      <i class="fas fa-graduation-cap text-gray-400 mr-2"></i>
                      <span>{{ row.degree_code }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex items-center">
                      <i class="fas fa-book text-gray-400 mr-2"></i>
                      <span>{{ row.program_code }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex items-center">
                      <i class="fas fa-calendar-check text-gray-400 mr-2"></i>
                      <span>{{ row.year_graduated }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex items-center">
                      <i class="fas fa-tag text-gray-400 mr-2"></i>
                      <span>{{ row.term }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex items-center">
                      <i class="fas fa-birthday-cake text-gray-400 mr-2"></i>
                      <span>{{ row.dob }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ row.gender }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex items-center">
                      <i class="fas fa-phone text-gray-400 mr-2"></i>
                      <span>{{ row.contact_number }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span :class="{
                      'px-2 py-1 text-xs rounded-full': true,
                      'bg-green-100 text-green-800': row.employment_status === 'Employed',
                      'bg-yellow-100 text-yellow-800': row.employment_status === 'Underemployed',
                      'bg-gray-100 text-gray-800': row.employment_status === 'Unemployed'
                    }">
                      {{ row.employment_status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <div class="flex items-center">
                      <i class="fas fa-building text-gray-400 mr-2"></i>
                      <span>{{ row.company_name }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ row.current_job_title || 'N/A' }}</td>
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

    </Container>

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
