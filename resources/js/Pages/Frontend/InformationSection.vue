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
  if (currentStep.value === totalSteps && canProceed.value) {
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
    <div class="relative min-h-screen gradient-bg overflow-hidden">
      <!-- Floating Background Elements -->
      <div class="absolute inset-0">
        <div class="absolute top-20 left-10 w-64 h-64 gradient-card rounded-full opacity-20 animate-float"></div>
        <div class="absolute top-40 right-20 w-48 h-48 gradient-feature rounded-full opacity-30 animate-float-reverse"></div>
        <div class="absolute bottom-20 left-1/4 w-72 h-72 gradient-cta rounded-full opacity-15 animate-morph"></div>
        <div class="absolute top-1/3 right-1/3 w-32 h-32 bg-white rounded-full opacity-10 animate-pulse-glow"></div>
        <div class="absolute bottom-1/3 right-10 w-40 h-40 gradient-card rounded-full opacity-25 animate-float"></div>
      </div>

      <div class="relative z-10 max-w-4xl mx-auto py-12 px-6">
        <!-- Header with Step Progress -->
        <div class="text-center mb-8 reveal">
          <h1 class="text-4xl font-bold text-white mb-4 neon-text">Graduate Information</h1>
          <p class="text-white/80 text-lg mb-8">Complete your profile in {{ totalSteps }} easy steps</p>
          
          <!-- Step Progress Bar -->
          <div class="flex justify-center items-center space-x-4 mb-8">
            <div 
              v-for="step in totalSteps" 
              :key="step"
              class="flex items-center"
            >
              <div 
                @click="goToStep(step)"
                :class="[
                  'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold cursor-pointer transition-all duration-300',
                  currentStep >= step 
                    ? 'glass text-white shadow-lg animate-pulse-glow' 
                    : 'bg-white/20 text-white/60 hover:bg-white/30'
                ]"
              >
                {{ step }}
              </div>
              <div 
                v-if="step < totalSteps"
                :class="[
                  'w-16 h-1 mx-2 rounded transition-all duration-300',
                  currentStep > step ? 'bg-white' : 'bg-white/30'
                ]"
              ></div>
            </div>
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-8">
        <!-- Step 1: Personal Details -->
        <div v-show="currentStep === 1" class="glass rounded-2xl p-8 reveal">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 gradient-card rounded-xl flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
            </div>
            <div>
              <h2 class="text-2xl font-bold text-white neon-text">Personal Details</h2>
              <p class="text-white/70">Tell us about yourself</p>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
              <InputLabel for="email" class="text-white font-medium">Email Address <span class="text-pink-400">*</span></InputLabel>
              <TextInput
                id="email"
                v-model="form.email"
                type="email"
                required
                class="mt-2 block w-full glass border-white/20 text-white placeholder-white/50 focus:border-cyan-400 focus:ring-cyan-400"
                readonly
              />
              <InputError :message="form.errors.email" class="text-pink-300" />
            </div>
            <div>
              <InputLabel for="first_name" class="text-white font-medium">First Name <span class="text-pink-400">*</span></InputLabel>
              <TextInput id="first_name" v-model="form.first_name" type="text" required class="mt-2 block w-full glass border-white/20 text-white placeholder-white/50 focus:border-cyan-400 focus:ring-cyan-400" />
              <InputError :message="form.errors.first_name" class="text-pink-300" />
            </div>
            <div>
              <InputLabel for="middle_name" class="text-white font-medium">Middle Name</InputLabel>
              <TextInput id="middle_name" v-model="form.middle_name" type="text" class="mt-2 block w-full glass border-white/20 text-white placeholder-white/50 focus:border-cyan-400 focus:ring-cyan-400" />
              <InputError :message="form.errors.middle_name" class="text-pink-300" />
            </div>
            <div>
              <InputLabel for="last_name" class="text-white font-medium">Last Name <span class="text-pink-400">*</span></InputLabel>
              <TextInput id="last_name" v-model="form.last_name" type="text" required class="mt-2 block w-full glass border-white/20 text-white placeholder-white/50 focus:border-cyan-400 focus:ring-cyan-400" />
              <InputError :message="form.errors.last_name" class="text-pink-300" />
            </div>
            <div>
              <InputLabel for="dob" class="text-white font-medium">Date of Birth <span class="text-pink-400">*</span></InputLabel>
              <TextInput id="dob" v-model="form.dob" type="date" required class="mt-2 block w-full glass border-white/20 text-white placeholder-white/50 focus:border-cyan-400 focus:ring-cyan-400" />
              <InputError :message="form.errors.dob" class="text-pink-300" />
            </div>
            <div>
              <InputLabel for="gender" class="text-white font-medium">Gender <span class="text-pink-400">*</span></InputLabel>
              <select id="gender" v-model="form.gender" required class="mt-2 block w-full glass border-white/20 text-white bg-transparent focus:border-cyan-400 focus:ring-cyan-400 rounded-lg">
                <option value="" class="bg-gray-800">Select Gender</option>
                <option value="Male" class="bg-gray-800">Male</option>
                <option value="Female" class="bg-gray-800">Female</option>
              </select>
              <InputError :message="form.errors.gender" class="text-pink-300" />
            </div>
            <div>
              <InputLabel for="mobile_number" class="text-white font-medium">Mobile Number <span class="text-pink-400">*</span></InputLabel>
              <TextInput
                id="mobile_number"
                v-model="formattedMobileNumber"
                type="text"
                required
                class="mt-2 block w-full glass border-white/20 text-white placeholder-white/50 focus:border-cyan-400 focus:ring-cyan-400"
                placeholder="+63 XXX XXX XXXX"
              />
              <InputError :message="form.errors.mobile_number" class="text-pink-300" />
            </div>
          </div>
        </div>

        <!-- Step 2: Educational Background -->
        <div v-show="currentStep === 2" class="glass rounded-2xl p-8 reveal">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 gradient-feature rounded-xl flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
              </svg>
            </div>
            <div>
              <h2 class="text-2xl font-bold text-white neon-text">Educational Background</h2>
              <p class="text-white/70">Tell us about your academic journey</p>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <InputLabel for="graduate_school_graduated_from" class="text-white font-medium">School Graduated From <span class="text-pink-400">*</span></InputLabel>
              <select id="graduate_school_graduated_from" v-model="form.graduate_school_graduated_from" required class="mt-2 block w-full glass border-white/20 text-white bg-transparent focus:border-cyan-400 focus:ring-cyan-400 rounded-lg">
                <option value="" class="bg-gray-800">Select School</option>
                <option v-for="school in props.institutions" :key="school.id" :value="school.id" class="bg-gray-800">{{ school.institution_name }}</option>
              </select>
              <InputError :message="form.errors.graduate_school_graduated_from" class="text-pink-300" />
            </div>
            <div>
              <InputLabel for="graduate_degree" class="text-white font-medium">Degree <span class="text-pink-400">*</span></InputLabel>
              <select id="graduate_degree" v-model="form.graduate_degree" required class="mt-2 block w-full glass border-white/20 text-white bg-transparent focus:border-cyan-400 focus:ring-cyan-400 rounded-lg">
                <option value="" class="bg-gray-800">Select Degree</option>
                <option v-for="degree in filteredDegrees" :key="degree.id" :value="degree.id" class="bg-gray-800">{{ degree.type }}</option>
              </select>
              <InputError :message="form.errors.graduate_degree" class="text-pink-300" />
            </div>
            <div>
              <InputLabel for="graduate_program_completed" class="text-white font-medium">Program Completed <span class="text-pink-400">*</span></InputLabel>
              <select id="graduate_program_completed" v-model="form.graduate_program_completed" required class="mt-2 block w-full glass border-white/20 text-white bg-transparent focus:border-cyan-400 focus:ring-cyan-400 rounded-lg">
                <option value="" class="bg-gray-800">Select Program</option>
                <option v-for="program in filteredPrograms" :key="program.id" :value="program.id" class="bg-gray-800">{{ program.name }}</option>
              </select>
              <InputError :message="form.errors.graduate_program_completed" class="text-pink-300" />
            </div>
            <div>
              <InputLabel for="graduate_year_graduated" class="text-white font-medium">Year Graduated <span class="text-pink-400">*</span></InputLabel>
              <select id="graduate_year_graduated" v-model="form.graduate_year_graduated" required class="mt-2 block w-full glass border-white/20 text-white bg-transparent focus:border-cyan-400 focus:ring-cyan-400 rounded-lg">
                <option value="" class="bg-gray-800">Select Year</option>
                <option v-for="year in filteredYears" :key="year.id" :value="year.id" class="bg-gray-800">{{ year.year }}</option>
              </select>
              <InputError :message="form.errors.graduate_year_graduated" class="text-pink-300" />
            </div>
          </div>
        </div>

        <!-- Step 3: Employment Information -->
        <div v-show="currentStep === 3" class="glass rounded-2xl p-8 reveal">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 gradient-cta rounded-xl flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
              </svg>
            </div>
            <div>
              <h2 class="text-2xl font-bold text-white neon-text">Employment Information</h2>
              <p class="text-white/70">Tell us about your current work status</p>
            </div>
          </div>
          <div class="space-y-6">
            <div>
              <InputLabel for="employment_status" class="text-white font-medium">Employment Status <span class="text-pink-400">*</span></InputLabel>
              <select id="employment_status" v-model="form.employment_status" @change="handleEmploymentStatusChange" required class="mt-2 block w-full glass border-white/20 text-white bg-transparent focus:border-cyan-400 focus:ring-cyan-400 rounded-lg">
                <option value="" class="bg-gray-800">Select Status</option>
                <option value="Employed" class="bg-gray-800">Employed</option>
                <option value="Unemployed" class="bg-gray-800">Unemployed</option>
                <option value="Underemployed" class="bg-gray-800">Underemployed</option>
              </select>
              <InputError :message="form.errors.employment_status" class="text-pink-300" />
            </div>
            <div v-if="form.employment_status !== 'Unemployed'" class="transition-all duration-300">
              <InputLabel for="current_job_title" class="text-white font-medium">Current Job Title <span class="text-pink-400">*</span></InputLabel>
              <TextInput id="current_job_title" v-model="form.current_job_title" type="text" required class="mt-2 block w-full glass border-white/20 text-white placeholder-white/50 focus:border-cyan-400 focus:ring-cyan-400" />
              <InputError :message="form.errors.current_job_title" class="text-pink-300" />
            </div>
          </div>
        </div>

        <!-- Step 4: Company Information -->
        <div v-show="currentStep === 4 && (form.employment_status === 'Employed' || form.employment_status === 'Underemployed')" class="glass rounded-2xl p-8 reveal">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 gradient-card rounded-xl flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
              </svg>
            </div>
            <div>
              <h2 class="text-2xl font-bold text-white neon-text">Company Information</h2>
              <p class="text-white/70">Tell us about your workplace</p>
            </div>
          </div>
          <div class="space-y-6">
            <div>
              <label class="inline-flex items-center">
                <input type="checkbox" v-model="form.company_not_found" class="form-checkbox text-cyan-400 focus:ring-cyan-400 focus:ring-offset-0 bg-transparent border-white/20" />
                <span class="ml-3 text-white font-medium">My company is not listed</span>
              </label>
            </div>
            <!-- Company Searchable Dropdown -->
            <div v-if="!form.company_not_found" class="relative">
              <InputLabel for="company_name" class="text-white font-medium">Company Name <span class="text-pink-400">*</span></InputLabel>
              <TextInput
                id="company_search"
                v-model="companySearch"
                type="text"
                class="mt-2 block w-full glass border-white/20 text-white placeholder-white/50 focus:border-cyan-400 focus:ring-cyan-400"
                placeholder="Type to search company..."
                autocomplete="off"
                @focus="showCompanyDropdown = true"
                @blur="handleCompanyBlur"
              />
              <div v-if="showCompanyDropdown && filteredCompanies.length" class="absolute z-20 w-full mt-1 glass border border-white/20 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                <div
                  v-for="company in filteredCompanies"
                  :key="company.id"
                  class="px-4 py-3 text-white hover:bg-white/10 cursor-pointer transition-colors duration-200"
                  @mousedown.prevent="selectCompany(company)"
                >
                  {{ company.company_name }}
                </div>
              </div>
              <InputError :message="form.errors.company_name" class="text-pink-300" />
            </div>
            <div v-else class="space-y-4">
              <div>
                <InputLabel for="other_company_name" class="text-white font-medium">Other Company Name <span class="text-pink-400">*</span></InputLabel>
                <TextInput id="other_company_name" v-model="form.other_company_name" type="text" required class="mt-2 block w-full glass border-white/20 text-white placeholder-white/50 focus:border-cyan-400 focus:ring-cyan-400" />
                <InputError :message="form.errors.other_company_name" class="text-pink-300" />
              </div>
              <div>
                <InputLabel for="other_company_sector" class="text-white font-medium">Sector <span class="text-pink-400">*</span></InputLabel>
                <select id="other_company_sector" v-model="form.other_company_sector" required class="mt-2 block w-full glass border-white/20 text-white bg-transparent focus:border-cyan-400 focus:ring-cyan-400 rounded-lg">
                  <option value="" class="bg-gray-800">Select Sector</option>
                  <option v-for="sector in props.sectors" :key="sector.id" :value="sector.id" class="bg-gray-800">{{ sector.name }}</option>
                </select>
                <InputError :message="form.errors.other_company_sector" class="text-pink-300" />
              </div>
            </div>
          </div>
        </div>

        <!-- Step 4: Final Review (for unemployed users) -->
        <div v-show="currentStep === 4 && form.employment_status === 'Unemployed'" class="glass rounded-2xl p-8 reveal">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 gradient-feature rounded-xl flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div>
              <h2 class="text-2xl font-bold text-white neon-text">Ready to Submit</h2>
              <p class="text-white/70">Review your information and submit your profile</p>
            </div>
          </div>
          <div class="text-center py-8">
            <p class="text-white/80 text-lg mb-4">Your profile information is complete!</p>
            <p class="text-white/60">Click the button below to save your information.</p>
          </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between items-center mt-8">
          <button
            v-if="currentStep > 1"
            @click="prevStep"
            type="button"
            class="glass px-6 py-3 text-white font-medium rounded-lg hover:bg-white/20 transition-all duration-300 flex items-center space-x-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            <span>Previous</span>
          </button>
          <div v-else></div>

          <div class="flex space-x-4">
            <button
              v-if="currentStep < totalSteps"
              @click="nextStep"
              :disabled="!canProceed"
              type="button"
              :class="[
                'px-8 py-3 font-medium rounded-lg transition-all duration-300 flex items-center space-x-2',
                canProceed 
                  ? 'gradient-cta text-white hover:shadow-lg hover:scale-105 animate-pulse-glow' 
                  : 'bg-gray-600 text-gray-400 cursor-not-allowed'
              ]"
            >
              <span>Next Step</span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>
            <PrimaryButton
              v-else
              :disabled="form.processing || !canProceed"
              :class="[
                'px-8 py-3 font-medium rounded-lg transition-all duration-300',
                canProceed 
                  ? 'gradient-cta text-white hover:shadow-lg hover:scale-105 animate-pulse-glow' 
                  : 'bg-gray-600 text-gray-400 cursor-not-allowed'
              ]"
            >
              <span v-if="form.processing">Saving...</span>
              <span v-else>Save Information</span>
            </PrimaryButton>
          </div>
        </div>
      </form>

      <!-- Success Modal -->
      <Modal v-model="showModal">
        <template #header>
          <h2 class="text-2xl font-bold text-green-600">Profile Saved!</h2>
        </template>
        <template #body>
          <p class="mb-6 text-gray-700">
            Your institution information has been saved.<br>
            You will now be redirected to your profile page.
          </p>
        </template>
        <template #footer>
          <PrimaryButton @click="goToProfile">Go to Profile</PrimaryButton>
        </template>
      </Modal>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Modern Design Styles */
.gradient-bg {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
  background: linear-gradient(45deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4, #feca57, #ff9ff3, #54a0ff);
  background-size: 400% 400%;
  animation: gradientShift 15s ease infinite;
  opacity: 0.1;
  z-index: 0;
}

@keyframes gradientShift {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

.floating-elements {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 1;
}

.floating-element {
  position: absolute;
  border-radius: 50%;
  background: linear-gradient(45deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
  backdrop-filter: blur(10px);
}

.animate-float {
  animation: float 6s ease-in-out infinite;
}

.animate-float-reverse {
  animation: float-reverse 8s ease-in-out infinite;
}

.animate-morph {
  animation: morph 10s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(180deg); }
}

@keyframes float-reverse {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(20px) rotate(-180deg); }
}

@keyframes morph {
  0%, 100% { border-radius: 50%; transform: scale(1); }
  25% { border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; transform: scale(1.1); }
  50% { border-radius: 70% 30% 30% 70% / 70% 70% 30% 30%; transform: scale(0.9); }
  75% { border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; transform: scale(1.05); }
}

.glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.gradient-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.gradient-feature {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.gradient-cta {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.neon-text {
  text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
}

.animate-pulse-glow {
  animation: pulse-glow 2s ease-in-out infinite alternate;
}

@keyframes pulse-glow {
  from { box-shadow: 0 0 20px rgba(79, 172, 254, 0.4); }
  to { box-shadow: 0 0 30px rgba(79, 172, 254, 0.8); }
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
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  transform: scale(1.1);
}

.step-indicator.completed {
  background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

/* Input Styles */
input[type="text"], input[type="email"], input[type="tel"], input[type="date"], select, textarea {
  background: rgba(255, 255, 255, 0.1) !important;
  border: 1px solid rgba(255, 255, 255, 0.2) !important;
  color: white !important;
  transition: all 0.3s ease !important;
}

input[type="text"]:focus, input[type="email"]:focus, input[type="tel"]:focus, input[type="date"]:focus, select:focus, textarea:focus {
  border-color: #4facfe !important;
  box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1) !important;
  background: rgba(255, 255, 255, 0.15) !important;
}

input::placeholder {
  color: rgba(255, 255, 255, 0.5) !important;
}

/* Checkbox Styles */
input[type="checkbox"] {
  accent-color: #4facfe;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
  .glass {
    margin: 1rem;
    padding: 1.5rem !important;
  }
  
  .gradient-bg {
    padding: 1rem;
  }
  
  .floating-element {
    display: none;
  }
}
</style>