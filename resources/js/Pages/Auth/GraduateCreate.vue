<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, computed } from 'vue';
import '@fortawesome/fontawesome-free/css/all.css';

// --- Mobile Number Formatting ---
function formatMobileNumber(value) {
    // Remove non-digits
    let digits = value.replace(/\D/g, '');
    // Format as +63 XXX XXX XXXX
    if (digits.startsWith('0')) digits = '63' + digits.slice(1);
    if (!digits.startsWith('63')) digits = '63' + digits;
    let formatted = '+' + digits;
    if (digits.length >= 12) {
        formatted = `+${digits.slice(0,2)} ${digits.slice(2,5)} ${digits.slice(5,8)} ${digits.slice(8,12)}`;
    }
    return formatted;
}

const props = defineProps({
    degrees: {
        type: Array,
        default: () => ([]),
    },
    programs: {
        type: Array,
        default: () => ([]),
    },
    school_years: {
        type: Array,
        default: () => ([]),
    },
    companies: {
        type: Array,
        default: () => ([]),
    },
    sectors: {
        type: Array,
        default: () => ([]),
    },
});

const form = ref({
    first_name: '',
    middle_name: '',
    last_name: '',
    email: '',
    contact_number: '',
    gender: '',
    birth_date: '',
    degree_id: '',
    program_id: '',
    school_year_id: '',
    employment_status: 'Unemployed',
    company_name: '',
    current_job_title: '',
    company_not_found: false,
    other_company_name: '',
    other_company_sector: '',
});

// --- Formatted Mobile Number ---
const formattedMobileNumber = computed({
    get() {
        return formatMobileNumber(form.value.contact_number);
    },
    set(val) {
        // Remove formatting for storage
        form.value.contact_number = val.replace(/\D/g, '').replace(/^63/, '0');
    }
});

// Step process variables
const showSuccess = ref(false);
const currentStep = ref(1);
const totalSteps = 3;

const stepTitles = [
  'Personal Information',
  'Academic Information',
  'Employment Information'
];

const stepIcons = [
  'fas fa-user',
  'fas fa-graduation-cap',
  'fas fa-briefcase'
];

const stepColors = [
  'blue',
  'green',
  'purple'
];

function nextStep() {
  if (currentStep.value < totalSteps) {
    currentStep.value++;
  }
}

function prevStep() {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
}

function goToStep(step) {
  if (step >= 1 && step <= totalSteps) {
    currentStep.value = step;
  }
}

const handleEmploymentStatusChange = () => {
    if (form.value.employment_status === 'Unemployed') {
        form.value.company_name = '';
        form.value.current_job_title = '';
    }
};

const filteredPrograms = computed(() => {
    if (!form.value.degree_id) return [];
    return props.programs.filter(program => program.degree_id === form.value.degree_id);
});

// Filter companies by search
const companySearch = ref('');
const showCompanyDropdown = ref(false);

const filteredCompanies = computed(() => {
    if (!companySearch.value) return props.companies;
    return props.companies.filter(company =>
        company.company_name.toLowerCase().includes(companySearch.value.toLowerCase())
    );
});

function selectCompany(company) {
    form.value.company_name = company.company_name;
    companySearch.value = company.company_name;
    form.value.other_company_name = '';
    form.value.other_company_sector = '';
    showCompanyDropdown.value = false;
}

function handleCompanyBlur() {
    setTimeout(() => {
        showCompanyDropdown.value = false;
    }, 200);
}

const createGraduate = () => {
    router.post(route('graduates.store'), form.value, {
        onSuccess: () => {
            showSuccess.value = true;
            form.value = {
                first_name: '',
                middle_name: '',
                last_name: '',
                email: '',
                contact_number: '',
                gender: '',
                birth_date: '',
                degree_id: '',
                program_id: '',
                school_year_id: '',
                employment_status: 'Unemployed',
                company_name: '',
                current_job_title: '',
                company_not_found: false,
                other_company_name: '',
                other_company_sector: '',
            };
            currentStep.value = 1;
        },
    });
};

const goBack = () => {
    window.history.back();
};


</script>

<template>
    <AppLayout title="Graduate Registration">
        <template #header>
            <div>
                <div class="flex items-center">
                    <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <i class="fas fa-user-graduate text-blue-500 text-xl mr-2"></i>
                    <h2 class="text-2xl font-bold text-gray-800">Graduate Registration</h2>
                </div>
                <p class="text-sm text-gray-500 mb-1">Register a new graduate in the system.</p>
            </div>
        </template>
        
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-2xl">
                    <div class="p-6 bg-white">
                        <div class="flex items-center justify-between mb-6">
                        </div>
                <div class="flex justify-center mb-8">
                    <div class="flex flex-col items-center">
                        <div class="flex items-center space-x-4">
                            <!-- Step 1: Personal Information -->
                            <div class="flex flex-col items-center">
                                <div @click="goToStep(1)"
                                    :class="[
                                        'w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg cursor-pointer transition-all duration-300',
                                        currentStep === 1 ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white scale-110' :
                                        (currentStep > 1 ? 'bg-emerald-500 text-white' : 'bg-gray-200 border-gray-300 text-gray-600')
                                    ]">
                                    <svg v-if="currentStep > 1" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <i v-else class="fas fa-user"></i>
                                </div>
                                <div class="mt-2 text-center text-sm font-medium text-gray-700">Personal Information</div>
                            </div>

                            <div class="w-12 h-0.5 bg-gray-300 mt-6"></div>

                            <!-- Step 2: Academic Information -->
                            <div class="flex flex-col items-center">
                                <div @click="goToStep(2)"
                                    :class="[
                                        'w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg cursor-pointer transition-all duration-300',
                                        currentStep === 2 ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white scale-110' :
                                        (currentStep > 2 ? 'bg-emerald-500 text-white' : 'bg-gray-200 border-gray-300 text-gray-600')
                                    ]">
                                    <svg v-if="currentStep > 2" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <i v-else class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="mt-2 text-center text-sm font-medium text-gray-700">Academic Information</div>
                            </div>

                            <div class="w-12 h-0.5 bg-gray-300 mt-6"></div>

                            <!-- Step 3: Employment Information -->
                            <div class="flex flex-col items-center">
                                <div @click="goToStep(3)"
                                    :class="[
                                        'w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg cursor-pointer transition-all duration-300',
                                        currentStep === 3 ? 'bg-gradient-to-br from-emerald-500 to-emerald-600 text-white scale-110' : 'bg-gray-200 border-gray-300 text-gray-600'
                                    ]">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                                <div class="mt-2 text-center text-sm font-medium text-gray-700">Employment Information</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <form @submit.prevent="createGraduate" class="space-y-6">
                    <!-- Step 1: Personal Information -->
                    <div v-show="currentStep === 1">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="email" class="text-sm font-medium text-gray-700 mb-1">Email <span class="text-blue-400">*</span></label>
                                <input v-model="form.email" type="email" id="email" required
                                    class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Enter your email" />
                            </div>
                            <div>
                                <label for="first_name" class="text-sm font-medium text-gray-700 mb-1">First Name <span class="text-emerald-400">*</span></label>
                                <input v-model="form.first_name" type="text" id="first_name" required
                                    class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Enter your first name" />
                            </div>
                            <div>
                                <label for="middle_name" class="text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                                <input v-model="form.middle_name" type="text" id="middle_name"
                                    class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Enter your middle name" />
                            </div>
                            <div>
                                <label for="last_name" class="text-sm font-medium text-gray-700 mb-1">Last Name <span class="text-emerald-400">*</span></label>
                                <input v-model="form.last_name" type="text" id="last_name" required
                                    class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Enter your last name" />
                            </div>
                            <div>
                                <label for="birth_date" class="text-sm font-medium text-gray-700 mb-1">Birth Date <span class="text-emerald-400">*</span></label>
                                <input v-model="form.birth_date" type="date" id="birth_date" required
                                    class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            </div>
                            <div>
                                <label for="gender" class="text-sm font-medium text-gray-700 mb-1">Gender <span class="text-emerald-400">*</span></label>
                                <select v-model="form.gender" id="gender" required
                                    class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div>
                                <label for="contact_number" class="text-sm font-medium text-gray-700 mb-1">Mobile Number <span class="text-emerald-400">*</span></label>
                                <input v-model="formattedMobileNumber" type="text" id="contact_number" required
                                    class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="+63 XXX XXX XXXX" />
                    </div>
                        </div>
                    </div>

                    <!-- Step 2: Academic Information -->
                    <div v-show="currentStep === 2">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="flex flex-col">
                                <label for="degree_id" class="text-sm font-medium text-gray-700 mb-1">Degree</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-scroll text-gray-400"></i>
                                    </div>
                                    <select v-model="form.degree_id" id="degree_id" required
                                        class="pl-10 w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 appearance-none">
                                        <option value="">Select Degree</option>
                                        <option v-for="degree in degrees" :key="degree.id" :value="degree.id">
                                            {{ degree.type }}
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <label for="program_id" class="text-sm font-medium text-gray-700 mb-1">Program</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-book text-gray-400"></i>
                                    </div>
                                    <select v-model="form.program_id" id="program_id" required
                                        class="pl-10 w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 appearance-none"
                                        :disabled="!form.degree_id">
                                        <option value="">Select Program</option>
                                        <option v-for="program in filteredPrograms" :key="program.id" :value="program.id">
                                            {{ program.name }}
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <label for="school_year_id" class="text-sm font-medium text-gray-700 mb-1">School Year</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-calendar-check text-gray-400"></i>
                                    </div>
                                    <select v-model="form.school_year_id" id="school_year_id" required
                                        class="pl-10 w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 appearance-none">
                                        <option value="">Select School Year</option>
                                        <option v-for="school_year in school_years" :key="school_year.id"
                                            :value="school_year.id">
                                            {{ school_year.school_year_range }} - {{ school_year.term }}
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Employment Information -->
                    <div v-show="currentStep === 3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex flex-col">
                                <label for="employment_status" class="text-sm font-medium text-gray-700 mb-1">Employment Status</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-user-tie text-gray-400"></i>
                                    </div>
                                    <select v-model="form.employment_status" id="employment_status" required
                                        @change="handleEmploymentStatusChange"
                                        class="pl-10 w-full py-2.5 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 appearance-none">
                                        <option value="">Select Employment Status</option>
                                        <option value="Employed">Employed</option>
                                        <option value="Underemployed">Underemployed</option>
                                        <option value="Unemployed">Unemployed</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                                        <i class="fas fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col" v-if="form.employment_status !== 'Unemployed'">
                                <label for="current_job_title" class="text-sm font-medium text-gray-700 mb-1">Job Title</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-id-badge text-gray-400"></i>
                                    </div>
                                    <input v-model="form.current_job_title" type="text" id="current_job_title" required
                                        class="pl-10 w-full py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                                </div>
                            </div>

                            <!-- Company Searchable Dropdown & Checkbox -->
                            <div class="flex flex-col relative overflow-visible" v-if="form.employment_status !== 'Unemployed'">
                                <!-- Header row: label + toggle -->
                                <div class="flex items-center justify-between mb-2">
                                    <label for="company_search" class="text-sm font-medium text-gray-700">Company</label>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" v-model="form.company_not_found" class="form-checkbox text-blue-500" />
                                        <span class="ml-2 text-sm text-gray-700 font-medium">My company is not listed</span>
                                    </label>
                                </div>

                                <!-- Company Search Dropdown -->
                                <div v-if="!form.company_not_found" class="relative">
                                    <input
                                        id="company_search"
                                        v-model="companySearch"
                                        type="text"
                                        class="block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Type to search company..."
                                        autocomplete="off"
                                        @focus="showCompanyDropdown = true"
                                        @blur="handleCompanyBlur"
                                    />
                                    <div v-if="showCompanyDropdown && filteredCompanies.length" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                        <div
                                            v-for="company in filteredCompanies"
                                            :key="company.id"
                                            class="px-4 py-3 text-gray-700 hover:bg-blue-50 cursor-pointer"
                                            @mousedown.prevent="selectCompany(company)"
                                        >
                                            {{ company.company_name }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Manual Company Name & Sector -->
                                <div v-else class="grid grid-cols-1 gap-2 mt-2">
                                    <div>
                                        <label for="other_company_name" class="text-sm font-medium text-gray-700 mb-1">Other Company Name</label>
                                        <input v-model="form.other_company_name" type="text" id="other_company_name" required
                                            class="block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Enter company name" />
                                    </div>
                                    <div>
                                        <label for="other_company_sector" class="text-sm font-medium text-gray-700 mb-1">Sector</label>
                                        <select v-model="form.other_company_sector" id="other_company_sector" required
                                            class="block w-full rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="">Select Sector</option>
                                            <option v-for="sector in sectors" :key="sector.id" :value="sector.id">
                                                {{ sector.name }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                        <div>
                            <button 
                                v-if="currentStep > 1" 
                                type="button" 
                                @click="prevStep"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
                            >
                                <i class="fas fa-chevron-left mr-2"></i>
                                Previous
                            </button>
                            <button 
                                v-else 
                                @click="goBack"
                                type="button"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
                            >
                                <i class="fas fa-times mr-2"></i>
                                Cancel
                            </button>
                        </div>
                        <div>
                            <button
                                v-if="currentStep < totalSteps"
                                type="button"
                                @click="nextStep"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
                            >
                                Next
                                <i class="fas fa-chevron-right ml-2"></i>
                            </button>
                            <button
                                v-else
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
                            >
                                <i class="fas fa-user-plus mr-2"></i>
                                <span>Register Graduate</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Success Message -->
                    <transition name="fade">
                        <div
                            v-if="showSuccess"
                            class="mt-4 p-4 bg-green-50 border border-green-200 rounded-md text-green-600 font-medium flex items-center"
                        >
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span>Graduate registered successfully!</span>
                        </div>
                    </transition>
                </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>