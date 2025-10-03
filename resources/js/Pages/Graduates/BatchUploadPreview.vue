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

// --- Pagination State ---
const currentPage = ref(1)
const itemsPerPage = ref(10) // Default items per page
// --------------------------

const props = defineProps({
    degreeCodes: Array,
    programCodes: Array,
    companyNames: Array,
    companyNamesMap: Object,
    institutionSchoolYears: Array,
})

// --- PAGINATION COMPUTED PROPERTIES ---
const totalPages = computed(() => {
    return Math.ceil(parsedRows.value.length / itemsPerPage.value)
})

const paginatedRows = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value
    const end = start + itemsPerPage.value
    return parsedRows.value.slice(start, end)
})

const startRowIndex = computed(() => {
    return (currentPage.value - 1) * itemsPerPage.value
})

// --- PAGINATION METHODS ---
function goToPage(page) {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page
    }
}

function prevPage() {
    goToPage(currentPage.value - 1)
}

function nextPage() {
    goToPage(currentPage.value + 1)
}

// ---------------------------------------

function handleFileUpload(e) {
    file.value = e.target.files[0]
    parsedRows.value = []
    validationErrors.value = []
    isValid.value = false
    currentPage.value = 1 // Reset pagination on new file upload

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
            ['EMPLOYED', 'UNDEREMPLOYED'].includes(row.employment_status) &&
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
            ['EMPLOYED', 'UNDEREMPLOYED'].includes(row.employment_status)
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
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="flex items-center mb-6">
                <button @click="goBack" class="mr-4 p-2 text-gray-500 hover:text-blue-600 transition duration-150 ease-in-out rounded-full hover:bg-gray-100">
                    <i class="fas fa-arrow-left text-xl"></i>
                </button>
                <div class="flex items-center">
                    <i class="fas fa-cloud-upload-alt text-blue-600 text-3xl mr-3"></i>
                    <h1 class="text-3xl font-extrabold text-gray-900">Batch Upload Graduates</h1>
                </div>
            </div>


            <div class="bg-white rounded-xl shadow-2xl overflow-hidden border border-gray-100 p-6">
                
                <div class="mb-8 border-b pb-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-file-csv text-blue-500 mr-2"></i>
                            1. Upload Your CSV File
                        </h2>
                        <a :href="route('graduates.template.download')" 
                           class="inline-flex items-center px-4 py-2 bg-blue-50 border border-blue-300 rounded-lg font-medium text-sm text-blue-700 hover:bg-blue-100 transition shadow-sm">
                            <i class="fas fa-download mr-2"></i>
                            Download Template
                        </a>
                    </div>

                    <div class="mt-4">
                        <input ref="fileInputRef" id="file-upload" type="file" @change="handleFileUpload" accept=".csv" class="hidden">
                        <div @click="triggerFileInput" 
                             class="border-2 border-dashed border-gray-300 rounded-xl p-10 text-center cursor-pointer hover:border-blue-600 hover:bg-blue-50 transition-colors duration-200">
                            <div class="flex flex-col items-center justify-center space-y-3">
                                <i class="fas fa-cloud-upload-alt text-5xl text-gray-400"></i>
                                <p class="text-gray-700 font-bold text-lg">Click to select or drag and drop file</p>
                                <p class="text-sm text-gray-500">Only CSV files are supported.</p>
                                
                                <div v-if="file" class="mt-3 px-4 py-2 bg-white rounded-full border border-green-300 shadow-md flex items-center text-sm text-green-700 font-medium">
                                    <i class="fas fa-check-circle mr-2 text-green-500"></i> {{ file.name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="parsedRows.length > 0">
                    <h2 class="text-xl font-semibold text-gray-800 flex items-center mb-5 border-b pb-2">
                        <i class="fas fa-chart-bar text-green-500 mr-2"></i>
                        2. Validation Summary
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div v-for="(stat, index) in stats" :key="index" 
                             class="bg-white rounded-xl p-6 border border-gray-200 hover:shadow-lg transition duration-200 ease-in-out transform hover:-translate-y-0.5">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="text-gray-500 text-sm font-medium uppercase mb-1">{{ stat.title }}</h3>
                                    <p class="text-4xl font-extrabold text-gray-900">{{ stat.value }}</p>
                                </div>
                                <div :class="[stat.bgColor, 'rounded-xl p-4 flex items-center justify-center shadow-md']">
                                    <i :class="[stat.icon, stat.color, 'text-2xl']"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-if="validationErrors.length" class="mb-8 p-6 bg-red-50 border border-red-200 rounded-xl shadow-inner">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl mr-2"></i>
                        <h3 class="text-lg font-bold text-red-700">Critical Errors Detected ({{ new Set(validationErrors.map(e => e.row)).size }} rows affected)</h3>
                    </div>
                    <p class="text-sm text-red-600 mb-4">Please correct these issues in your CSV file and re-upload before proceeding.</p>

                    <div class="max-h-80 overflow-y-auto pr-2">
                        <ul class="space-y-3">
                            <li v-for="error in validationErrors" :key="error.row" class="p-3 bg-white rounded-lg border border-red-300 shadow-sm">
                                <div class="font-bold text-red-800 flex items-center mb-1">
                                    <i class="fas fa-times-circle mr-2"></i>
                                    Row **{{ error.row }}** has the following issues:
                                </div>
                                <ul class="mt-1 pl-6 text-sm text-red-600 space-y-1 list-disc list-outside">
                                    <li v-for="(msg, idx) in error.messages" :key="idx">
                                        {{ msg }}
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>

                <div v-if="parsedRows.length">
                    <h2 class="text-xl font-semibold text-gray-800 flex items-center mb-5 border-b pb-2">
                        <i class="fas fa-table text-purple-500 mr-2"></i>
                        3. Data Preview
                    </h2>
                    
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="p-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">#</th>
                                    <th class="p-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Name & Email</th>
                                    <th class="p-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Academics</th>
                                    <th class="p-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Grad. Info</th>
                                    <th class="p-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Contact</th>
                                    <th class="p-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Employment</th>
                                    <th class="p-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Job Title & Co.</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="(row, i) in paginatedRows" :key="startRowIndex + i" 
                                    :class="{'bg-red-50/50 hover:bg-red-100': validationErrors.some(e => e.row === startRowIndex + i + 2), 'hover:bg-gray-50': !validationErrors.some(e => e.row === startRowIndex + i + 2)}">
                                    
                                    <td class="p-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ startRowIndex + i + 2 }}</td>
                                    
                                    <td class="p-3 whitespace-nowrap text-sm">
                                        <div class="font-medium text-gray-900">{{ row.first_name }} {{ row.last_name }}</div>
                                        <div class="text-gray-500 text-xs flex items-center">
                                            <i class="fas fa-envelope text-gray-400 mr-1"></i>
                                            {{ row.email }}
                                        </div>
                                    </td>

                                    <td class="p-3 whitespace-nowrap text-sm">
                                        <div class="font-medium text-gray-900">{{ row.program_code }}</div>
                                        <div class="text-gray-500 text-xs">({{ row.degree_code }})</div>
                                    </td>
                                    
                                    <td class="p-3 whitespace-nowrap text-sm">
                                        <div class="font-medium text-gray-900">{{ row.year_graduated }}</div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Term: {{ row.term }}
                                        </span>
                                    </td>
                                    
                                    <td class="p-3 whitespace-nowrap text-sm">
                                        <div class="text-gray-900 flex items-center">
                                           <i class="fas fa-phone text-gray-400 mr-1"></i> {{ row.contact_number }}
                                        </div>
                                        <div class="text-gray-500 text-xs">{{ row.gender }} | DOB: {{ row.dob }}</div>
                                    </td>
                                    
                                    <td class="p-3 whitespace-nowrap text-sm">
                                        <span :class="{
                                            'px-2.5 py-0.5 text-xs rounded-full font-medium': true,
                                            'bg-green-100 text-green-800 border border-green-300': row.employment_status === 'EMPLOYED',
                                            'bg-yellow-100 text-yellow-800 border border-yellow-300': row.employment_status === 'UNDEREMPLOYED',
                                            'bg-gray-100 text-gray-800 border border-gray-300': row.employment_status === 'UNEMPLOYED'
                                        }">
                                            {{ row.employment_status }}
                                        </span>
                                    </td>

                                    <td class="p-3 whitespace-nowrap text-sm">
                                        <div class="font-medium text-gray-900">{{ row.current_job_title || 'N/A' }}</div>
                                        <div class="text-gray-500 text-xs flex items-center">
                                            <i class="fas fa-building text-gray-400 mr-1"></i>
                                            {{ row.company_name }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div v-if="totalPages > 1" class="mt-4 flex justify-between items-center px-4 py-3 bg-gray-50 rounded-lg border border-gray-200 shadow-inner">
                        <div class="text-sm text-gray-600">
                            Showing {{ startRowIndex + 1 }} to {{ Math.min(startRowIndex + itemsPerPage, parsedRows.length) }} of {{ parsedRows.length }} records
                        </div>

                        <div class="flex items-center space-x-2">
                            <button @click="prevPage" :disabled="currentPage === 1"
                                    class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100 disabled:opacity-50 transition">
                                <i class="fas fa-chevron-left mr-1"></i> Previous
                            </button>
                            
                            <span class="text-sm font-medium text-gray-700">
                                Page {{ currentPage }} of {{ totalPages }}
                            </span>

                            <button @click="nextPage" :disabled="currentPage === totalPages"
                                    class="px-3 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-100 disabled:opacity-50 transition">
                                Next <i class="fas fa-chevron-right ml-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="!parsedRows.length && !file" class="p-12 flex flex-col items-center justify-center text-center">
                    <i class="fas fa-file-upload text-gray-300 text-7xl mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">Ready to Upload Data</h3>
                    <p class="text-gray-500 mb-6">Select a CSV file to begin the validation and upload process.</p>
                </div>

                <div v-if="parsedRows.length" class="mt-8 pt-4 border-t border-gray-200 flex justify-end">
                    <button
                        @click="submitToBackend"
                        :disabled="!isValid"
                        class="inline-flex items-center px-6 py-3 text-lg font-bold rounded-lg shadow-xl transform transition-all duration-200"
                        :class="isValid ? 'bg-green-600 text-white hover:bg-green-700 hover:scale-[1.02]' : 'bg-gray-400 text-gray-700 cursor-not-allowed'"
                    >
                        <i :class="isValid ? 'fas fa-check-circle' : 'fas fa-times-circle'" class="mr-3"></i>
                        {{ isValid ? 'Submit Valid Data' : 'Fix Errors to Submit' }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="isLoading" class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-black bg-opacity-70 backdrop-blur-sm">
            <div class="bg-white rounded-xl p-10 flex flex-col items-center shadow-2xl max-w-sm w-full transform transition-all duration-300 scale-100">
                <i class="fas fa-cloud-upload-alt text-blue-600 text-4xl mb-4 animate-bounce"></i>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Processing Upload...</h3>
                
                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4 shadow-inner">
                    <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300 ease-in-out" :style="{ width: loadingPercent + '%' }"></div>
                </div>
                
                <div class="text-base text-gray-700 font-medium flex items-center">
                    {{ Math.floor(loadingPercent) }}% complete
                </div>
                <div class="text-xs text-gray-400 mt-2 text-center">Data is being sent to the server. Please wait.</div>
            </div>
        </div>
    </AppLayout>
</template>