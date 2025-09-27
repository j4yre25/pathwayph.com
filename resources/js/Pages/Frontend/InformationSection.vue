<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { useFormattedMobileNumber } from '@/Composables/useFormattedMobileNumber.js';

const props = defineProps({
  email: String,
  companies: Array,
  sectors: Array,
  institutions: Array,
  programs: Array,
  schoolYears: Array,
  degrees: Array,
  institutionDegrees: Array,
  institutionPrograms: Array,
});

const form = useForm({
  email: props.email || '',
  first_name: '',
  middle_name: '',
  last_name: '',
  dob: '',
  gender: '',
  mobile_number: '',
  graduate_school_graduated_from: '',
  graduate_program_completed: '',
  graduate_year_graduated: '',
  graduate_degree: '',
  employment_status: '',
  current_job_title: '',
  company_not_found: false,
  company_name: '',
  other_company_name: '',
  other_company_sector: '',
});

const { formattedMobileNumber } = useFormattedMobileNumber(form, 'mobile_number');

const showModal = ref(false);
const currentStep = ref(1);
const totalSteps = 4;

// Step navigation
const nextStep = () => {
  if (currentStep.value < totalSteps) {
    currentStep.value++;
  }
};

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
};

const goToStep = (step) => {
  currentStep.value = step;
};

// Step validation
const isStepValid = (step) => {
  switch (step) {
    case 1:
      return form.first_name && form.last_name && form.dob && form.gender && form.mobile_number;
    case 2:
      return form.graduate_school_graduated_from && form.graduate_degree && form.graduate_program_completed && form.graduate_year_graduated;
    case 3:
      return form.employment_status && (form.employment_status === 'Unemployed' || form.current_job_title);
    case 4:
      return form.employment_status === 'Unemployed' ||
        (!form.company_not_found && form.company_name) ||
        (form.company_not_found && form.other_company_name && form.other_company_sector);
    default:
      return false;
  }
};

const canProceed = computed(() => isStepValid(currentStep.value));

// Auto-advance for employment status
const handleEmploymentStatusChange = () => {
  if (form.employment_status === 'Unemployed') {
    // Skip company information step
    setTimeout(() => {
      if (currentStep.value === 3) {
        currentStep.value = 4;
      }
    }, 500);
  }
};

onMounted(() => {
  // Add reveal animation to elements
  const elements = document.querySelectorAll('.reveal');
  elements.forEach((el, index) => {
    setTimeout(() => {
      el.classList.add('active');
    }, index * 100);
  });
});

// Degrees filtered by selected school
const filteredDegrees = computed(() => {
  if (!form.graduate_school_graduated_from) return [];
  const degreeIds = props.institutionDegrees
    .filter(idg => Number(idg.institution_id) === Number(form.graduate_school_graduated_from))
    .map(idg => Number(idg.degree_id));
  return props.degrees.filter(degree => degreeIds.includes(Number(degree.id)));
});

const sectors = props.sectors;

console.log('Available sectors:', sectors);

// Programs filtered by selected school and degree
const filteredPrograms = computed(() => {
  if (!form.graduate_school_graduated_from || !form.graduate_degree) return [];
  const programIds = props.institutionPrograms
    .filter(ip => Number(ip.institution_id) === Number(form.graduate_school_graduated_from))
    .map(ip => Number(ip.program_id));
  return props.programs.filter(
    program =>
      programIds.includes(Number(program.id)) &&
      Number(program.degree_id) === Number(form.graduate_degree)
  );
});

// Only show the first year from school_year_range
const filteredYears = computed(() =>
  props.schoolYears.map(year => {
    const firstYear = year.school_year_range.split('-')[0];
    return { id: year.id, year: firstYear };
  })
);

const companySearch = ref('');
const showCompanyDropdown = ref(false);
function handleCompanyBlur() {
  setTimeout(() => {
    showCompanyDropdown.value = false;
  }, 200);
}

const filteredCompanies = computed(() => {
  if (!companySearch.value) return props.companies;
  return props.companies.filter(company =>
    company.company_name.toLowerCase().includes(companySearch.value.toLowerCase())
  );
});

function selectCompany(company) {
  form.company_name = company.company_name;
  companySearch.value = company.company_name;
  showCompanyDropdown.value = false;
}

function submit() {
  if (currentStep.value === totalSteps && canProceed.value)
    if (form.company_not_found && form.other_company_sector) {
      const sectorObj = props.sectors.find(s => s.id == form.other_company_sector);
      form.other_company_sector = sectorObj ? sectorObj.name : '';
    } {
    form.post(route('graduate.information.save'), {
      onSuccess: () => {
        showModal.value = true;
      }
    });
  }
}

function goToProfile() {
  window.location.href = route('graduate.profile');
}
</script>

<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 relative overflow-hidden">

      <div class="max-w-3xl mx-auto py-10 relative z-10">
        <!-- Header with Step Progress -->
        <div class="text-center mb-8 reveal">
          <h1 class="text-4xl font-bold text-slate-800 mb-4">
            Graduate Information
          </h1>
          <p class="text-slate-600 text-lg mb-8">Complete your profile in {{ totalSteps }} easy steps</p>

          <!-- Step Progress Bar -->
          <div class="flex justify-center items-center space-x-4 mb-8">
            <div v-for="step in totalSteps" :key="step" class="flex items-center">
              <div @click="goToStep(step)"
                :class="['w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold cursor-pointer transition-all duration-300',
                  currentStep >= step ? 'bg-emerald-500 text-white shadow-lg' : 'bg-gray-200 text-gray-500 hover:bg-gray-300']">
                {{ step }}
              </div>
              <div v-if="step < totalSteps" :class="['w-16 h-1 mx-2 rounded transition-all duration-300',
                currentStep > step ? 'bg-emerald-500' : 'bg-gray-300']">
              </div>
            </div>
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-8">
          <!-- Step 1: Personal Details -->
          <div v-show="currentStep === 1" class="bg-white rounded-2xl p-8 shadow-lg border border-gray-200 reveal">
            <div class="flex items-center mb-6">
              <div
                class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
              </div>
              <div>
                <h2 class="text-2xl font-bold text-slate-800">Personal Details</h2>
                <p class="text-slate-600">Tell us about yourself</p>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="md:col-span-2">
                <InputLabel for="email" class="text-slate-800 font-medium">Email Address <span
                    class="text-red-500">*</span></InputLabel>
                <TextInput id="email" v-model="form.email" type="email" required
                  class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-emerald-500 focus:ring-emerald-500"
                  readonly />
                <InputError :message="form.errors.email" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="first_name" class="text-slate-800 font-medium">First Name <span
                    class="text-red-500">*</span></InputLabel>
                <TextInput id="first_name" v-model="form.first_name" type="text" required
                  class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-emerald-500 focus:ring-emerald-500" />
                <InputError :message="form.errors.first_name" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="middle_name" class="text-slate-800 font-medium">Middle Name</InputLabel>
                <TextInput id="middle_name" v-model="form.middle_name" type="text"
                  class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-emerald-500 focus:ring-emerald-500" />
                <InputError :message="form.errors.middle_name" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="last_name" class="text-slate-800 font-medium">Last Name <span
                    class="text-red-500">*</span></InputLabel>
                <TextInput id="last_name" v-model="form.last_name" type="text" required
                  class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-emerald-500 focus:ring-emerald-500" />
                <InputError :message="form.errors.last_name" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="dob" class="text-slate-800 font-medium">Date of Birth <span
                    class="text-red-500">*</span></InputLabel>
                <TextInput id="dob" v-model="form.dob" type="date" required
                  class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-emerald-500 focus:ring-emerald-500" />
                <InputError :message="form.errors.dob" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="gender" class="text-slate-800 font-medium">Gender <span class="text-red-500">*</span>
                </InputLabel>
                <select id="gender" v-model="form.gender" required
                  class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                  <option value="" class="bg-white">Select Gender</option>
                  <option value="Male" class="bg-white">Male</option>
                  <option value="Female" class="bg-white">Female</option>
                </select>
                <InputError :message="form.errors.gender" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="mobile_number" class="text-slate-800 font-medium">Mobile Number <span
                    class="text-red-500">*</span></InputLabel>
                <TextInput id="mobile_number" v-model="formattedMobileNumber" type="text" required
                  class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-emerald-500 focus:ring-emerald-500"
                  placeholder="+63 XXX XXX XXXX" />
                <InputError :message="form.errors.mobile_number" class="text-red-600" />
              </div>
            </div>
          </div>

          <!-- Step 2: Educational Background -->
          <div v-show="currentStep === 2" class="bg-white rounded-2xl p-8 shadow-lg border border-gray-200 reveal">
            <div class="flex items-center mb-6">
              <div
                class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z">
                  </path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z">
                  </path>
                </svg>
              </div>
              <div>
                <h2 class="text-2xl font-bold text-slate-800">Educational Background</h2>
                <p class="text-slate-600">Tell us about your academic journey</p>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <InputLabel for="graduate_school_graduated_from" class="text-slate-800 font-medium">School Graduated
                  From <span class="text-red-500">*</span></InputLabel>
                <select id="graduate_school_graduated_from" v-model="form.graduate_school_graduated_from" required
                  class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                  <option value="" class="bg-white">Select School</option>
                  <option v-for="school in props.institutions" :key="school.id" :value="school.id" class="bg-white">{{
                    school.institution_name }}</option>
                </select>
                <InputError :message="form.errors.graduate_school_graduated_from" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="graduate_degree" class="text-slate-800 font-medium">Degree <span
                    class="text-red-500">*</span></InputLabel>
                <select id="graduate_degree" v-model="form.graduate_degree" required
                  class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                  <option value="" class="bg-white">Select Degree</option>
                  <option v-for="degree in filteredDegrees" :key="degree.id" :value="degree.id" class="bg-white">{{
                    degree.type }}</option>
                </select>
                <InputError :message="form.errors.graduate_degree" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="graduate_program_completed" class="text-slate-800 font-medium">Program Completed <span
                    class="text-red-500">*</span></InputLabel>
                <select id="graduate_program_completed" v-model="form.graduate_program_completed" required
                  class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                  <option value="" class="bg-white">Select Program</option>
                  <option v-for="program in filteredPrograms" :key="program.id" :value="program.id" class="bg-white">{{
                    program.name }}</option>
                </select>
                <InputError :message="form.errors.graduate_program_completed" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="graduate_year_graduated" class="text-slate-800 font-medium">Year Graduated <span
                    class="text-red-500">*</span></InputLabel>
                <select id="graduate_year_graduated" v-model="form.graduate_year_graduated" required
                  class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                  <option value="" class="bg-white">Select Year</option>
                  <option v-for="year in filteredYears" :key="year.id" :value="year.id" class="bg-white">{{ year.year }}
                  </option>
                </select>
                <InputError :message="form.errors.graduate_year_graduated" class="text-red-600" />
              </div>
            </div>
          </div>

          <!-- Step 3: Employment Information -->
          <div v-show="currentStep === 3" class="bg-white rounded-2xl p-8 shadow-lg border border-gray-200 reveal">
            <div class="flex items-center mb-6">
              <div
                class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6">
                  </path>
                </svg>
              </div>
              <div>
                <h2 class="text-2xl font-bold text-slate-800">Employment Information</h2>
                <p class="text-slate-600">Tell us about your current work status</p>
              </div>
            </div>
            <div class="space-y-6">
              <div>
                <InputLabel for="employment_status" class="text-slate-800 font-medium">Employment Status <span
                    class="text-red-500">*</span></InputLabel>
                <select id="employment_status" v-model="form.employment_status" @change="handleEmploymentStatusChange"
                  required
                  class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                  <option value="" class="bg-white">Select Status</option>
                  <option value="Employed" class="bg-white">Employed</option>
                  <option value="Unemployed" class="bg-white">Unemployed</option>
                  <option value="Underemployed" class="bg-white">Underemployed</option>
                </select>
                <InputError :message="form.errors.employment_status" class="text-red-600" />
              </div>
              <div v-if="form.employment_status !== 'Unemployed'" class="transition-all duration-300">
                <InputLabel for="current_job_title" class="text-slate-800 font-medium">Current Job Title <span
                    class="text-red-500">*</span></InputLabel>
                <TextInput id="current_job_title" v-model="form.current_job_title" type="text" required
                  class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-emerald-500 focus:ring-emerald-500" />
                <InputError :message="form.errors.current_job_title" class="text-red-600" />
              </div>
            </div>
          </div>

          <!-- Step 4: Company Information -->
          <div
            v-show="currentStep === 4 && (form.employment_status === 'Employed' || form.employment_status === 'Underemployed')"
            class="bg-white rounded-2xl p-8 shadow-lg border border-gray-200 reveal">
            <div class="flex items-center mb-6">
              <div
                class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                  </path>
                </svg>
              </div>
              <div>
                <h2 class="text-2xl font-bold text-slate-800">Company Information</h2>
                <p class="text-slate-600">Tell us about your workplace</p>
              </div>
            </div>
            <div class="space-y-6">
              <div>
                <label class="inline-flex items-center">
                  <input type="checkbox" v-model="form.company_not_found"
                    class="form-checkbox text-blue-400 focus:ring-blue-400 focus:ring-offset-0 bg-transparent border-white/20" />
                  <span class="ml-3 text-slate-800 font-medium">My company is not listed</span>
                </label>
              </div>
              <!-- Company Searchable Dropdown -->
              <div v-if="!form.company_not_found" class="relative">
                <InputLabel for="company_name" class="text-slate-800 font-medium">Company Name <span
                    class="text-red-500">*</span></InputLabel>
                <TextInput id="company_search" v-model="companySearch" type="text"
                  class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-emerald-500 focus:ring-emerald-500"
                  placeholder="Type to search company..." autocomplete="off" @focus="showCompanyDropdown = true"
                  @blur="handleCompanyBlur" />
                <div v-if="showCompanyDropdown && filteredCompanies.length"
                  class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                  <div v-for="company in filteredCompanies" :key="company.id"
                    class="px-4 py-3 text-slate-800 hover:bg-gray-100 cursor-pointer transition-colors duration-200"
                    @mousedown.prevent="selectCompany(company)">
                    {{ company.company_name }}
                  </div>
                </div>
                <InputError :message="form.errors.company_name" class="text-red-600" />
              </div>
              <div v-else class="space-y-4">
                <div>
                  <InputLabel for="other_company_name" class="text-slate-800 font-medium">Other Company Name <span
                      class="text-red-500">*</span></InputLabel>
                  <TextInput id="other_company_name" v-model="form.other_company_name" type="text" required
                    class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-emerald-500 focus:ring-emerald-500" />
                  <InputError :message="form.errors.other_company_name" class="text-red-600" />
                </div>
                <div>
                  <InputLabel for="other_company_sector" class="text-slate-800 font-medium">Sector <span
                      class="text-red-500">*</span></InputLabel>
                  <select id="other_company_sector" v-model="form.other_company_sector" required
                    class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 focus:border-emerald-500 focus:ring-emerald-500 rounded-lg">
                    <option value="" class="bg-white">Select Sector</option>
                    <option v-for="sector in sectors" :key="sector.id" :value="sector.id" class="bg-white">
                      {{ sector.name }}
                    </option>
                  </select>
                  <InputError :message="form.errors.other_company_sector" class="text-red-600" />
                  <!-- Show selected sector name -->
                  <div v-if="form.other_company_sector && selectedSectorName" class="mt-2 text-emerald-700 text-sm">
                    Selected Sector: <span class="font-semibold">{{ selectedSectorName }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 4: Final Review (for unemployed users) -->
          <div v-show="currentStep === 4 && form.employment_status === 'Unemployed'"
            class="bg-white rounded-2xl p-8 shadow-lg border border-gray-200 reveal">
            <div class="flex items-center mb-6">
              <div
                class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div>
                <h2 class="text-2xl font-bold text-slate-800">Ready to Submit</h2>
                <p class="text-slate-600">Review your information and submit your profile</p>
              </div>
            </div>
            <div class="text-center py-8">
              <p class="text-slate-700 text-lg mb-4">Your profile information is complete!</p>
              <p class="text-slate-500">Click the button below to save your information.</p>
            </div>
          </div>

          <!-- Navigation Buttons -->
          <div class="flex justify-between items-center mt-8">
            <button v-if="currentStep > 1" @click="prevStep" type="button"
              class="bg-gray-100 border-gray-300 text-slate-700 px-6 py-3 rounded-lg font-medium hover:bg-gray-200 transition-all duration-300 flex items-center space-x-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
              <span>Previous</span>
            </button>
            <div v-else></div>

            <div class="flex space-x-4">
              <button v-if="currentStep < totalSteps" type="button" @click="nextStep" :disabled="!canProceed"
                class="gradient-cta px-6 py-3 rounded-xl font-medium text-white hover:scale-105 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2">
                <span>Next Step</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </button>

              <button v-if="currentStep === totalSteps" type="submit" :disabled="form.processing || !canProceed"
                class="gradient-cta hover-rainbow px-8 py-3 rounded-xl font-bold text-white transform hover:scale-105 animate-pulse-glow disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2">
                <svg v-if="form.processing" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor"
                    d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                  </path>
                </svg>
                <span v-if="form.processing">Saving...</span>
                <span v-else>Save Information</span>
              </button>
            </div>
          </div>
        </form>

        <!-- Success Modal -->
        <Modal :modelValue="showModal" @close="showModal = false">
          <div class="p-6">
            <div class="flex items-center justify-center mb-4">
              <div class="bg-green-100 rounded-full p-2">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
            </div>
            <h3 class="text-lg font-medium text-center mb-4 text-emerald-700">Profile Saved!</h3>
            <p class="mb-6 text-gray-700 text-center">
              Your personal information has been saved.<br>
              You will now be redirected to your profile page.
            </p>
            <div class="flex justify-center">
              <PrimaryButton @click="goToProfile">
                Go to Profile
              </PrimaryButton>
            </div>
          </div>
        </Modal>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Modern Design Styles */
.gradient-bg {
  background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #1d4ed8 100%);
  min-height: 100vh;
  position: relative;
  overflow: hidden;
}

.gradient-bg::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(45deg, #1e40af, #3b82f6, #60a5fa, #93c5fd, #dbeafe, #bfdbfe, #2563eb);
  background-size: 400% 400%;
  animation: gradientShift 15s ease infinite;
  opacity: 0.1;
  z-index: 0;
}

@keyframes gradientShift {
  0% {
    background-position: 0% 50%;
  }

  50% {
    background-position: 100% 50%;
  }

  100% {
    background-position: 0% 50%;
  }
}



.gradient-card {
  background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
}

.gradient-feature {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}

.gradient-cta {
  background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
}



.reveal {
  animation: reveal 0.6s ease-out;
}

@keyframes reveal {
  from {
    opacity: 0;
    transform: translateY(30px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.step-indicator {
  transition: all 0.3s ease;
}

.step-indicator.active {
  background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
  transform: scale(1.1);
}

.step-indicator.completed {
  background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
}



/* Checkbox Styles */
input[type="checkbox"] {
  accent-color: #3b82f6;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
  .gradient-bg {
    padding: 1rem;
  }
}
</style>