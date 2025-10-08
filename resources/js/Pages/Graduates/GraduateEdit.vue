<script setup>
import { ref, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import '@fortawesome/fontawesome-free/css/all.css' // Ensure icons are available

const props = defineProps({
  show: Boolean,
  graduate: Object,
  programs: Array,
  years: Array,
  terms: Array,
  companies: Array,
  sectors: Array,
})

const emit = defineEmits(['close'])

const form = ref({
  id: null,
  first_name: '',
  last_name: '',
  middle_name: '',
  email: '',
  graduate_school_graduated_from: '',
  program_id: '',
  graduate_year_graduated: '',
  graduate_term: '',
  employment_status: 'Employed',
  current_job_title: '',
  gender: '',
  dob: '',
  contact_number: '',
  company_id: '',
  company_name: '',
  company_not_found: false,
  other_company_name: '',
  other_company_sector: '',
})

const errors = ref({})
const processing = ref(false)

watch(() => props.graduate, (grad) => {
  if (grad) {
    form.value = {
      id: grad.graduate_id || grad.id,
      first_name: grad.first_name || '',
      last_name: grad.last_name || '',
      middle_name: grad.middle_name || '',
      email: grad.email || '',
      graduate_school_graduated_from: grad.graduate_school_graduated_from || grad.institution_id || '',
      program_id: grad.program_id || '',
      graduate_year_graduated: grad.year_graduated || '',
      graduate_term: grad.term || '',
      employment_status: grad.employment_status || 'Employed',
      current_job_title: grad.current_job_title || '',
      gender: grad.gender || '',
      dob: grad.dob || '',
      contact_number: grad.contact_number || '',
      company_id: grad.company_id || '',
      company_name: grad.company_name || '',
      company_not_found: false,
      other_company_name: '',
      other_company_sector: '',
    }
    // Pre-fill the company search field with the existing company name
    companySearch.value = grad.company_name || '';
  }
}, { immediate: true })

function handleEmploymentStatus() {
  if (form.value.employment_status === 'Unemployed') {
    form.value.current_job_title = '';
    form.value.company_id = '';
    form.value.company_name = '';
    form.value.company_not_found = false;
    form.value.other_company_name = '';
    form.value.other_company_sector = '';
  }
}

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
  form.value.company_id = company.id;
  companySearch.value = company.company_name;
  showCompanyDropdown.value = false;
  form.value.company_not_found = false;
}

function clearCompany() {
  form.value.company_name = '';
  form.value.company_id = '';
  companySearch.value = '';
}

function handleCompanyBlur() {
  setTimeout(() => {
    showCompanyDropdown.value = false;
  }, 200);
}

function submitForm() {
  errors.value = {};
  // Check for company name if employed or underemployed
  if (
    (form.value.employment_status === 'Employed' || form.value.employment_status === 'Underemployed') &&
    !form.value.company_name &&
    !form.value.company_not_found
  ) {
    errors.value.company_name = 'Company name is required for employed or underemployed graduates.';
    processing.value = false;
    return;
  }
  // If company not found, check other company name and sector
  if (
    (form.value.employment_status === 'Employed' || form.value.employment_status === 'Underemployed') &&
    form.value.company_not_found &&
    (!form.value.other_company_name || !form.value.other_company_sector)
  ) {
    if (!form.value.other_company_name) {
      errors.value.other_company_name = 'Other company name is required.';
    }
    if (!form.value.other_company_sector) {
      errors.value.other_company_sector = 'Sector is required.';
    }
    processing.value = false;
    return;
  }

  processing.value = true;
  const routeName = form.value.id ? 'graduates.update' : 'graduates.manual.store';
  const method = form.value.id ? 'put' : 'post';
  const url = form.value.id ? route(routeName, { graduate: form.value.id }) : route(routeName);
  router[method](url, form.value, {
    onSuccess: () => {
      emit('close');
      processing.value = false;
    },
    onError: (validationErrors) => {
      errors.value = validationErrors;
      processing.value = false;
    }
  });
}
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-gray-900 bg-opacity-70 z-50 flex items-center justify-center p-4 transition-opacity duration-300"
  >
    <div
      class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl relative overflow-hidden flex flex-col"
      style="max-height: 90vh;"
    >
      <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50 flex-shrink-0">
        <div class="flex items-center">
          <i class="fas fa-user-edit text-blue-600 text-xl mr-3"></i>
          <h2 class="text-xl font-bold text-gray-800">
            {{ form.id ? 'Edit Graduate Profile' : 'Add New Graduate' }}
          </h2>
        </div>
        <button
          @click="$emit('close')"
          class="text-gray-500 hover:text-red-600 transition p-2 rounded-full hover:bg-gray-100"
          aria-label="Close"
        >
          <i class="fas fa-times text-lg"></i>
        </button>
      </div>

      <form @submit.prevent="submitForm" class="p-6 overflow-y-auto space-y-8 flex-grow">
        
        <div class="space-y-4 p-4 border border-gray-200 rounded-xl shadow-inner bg-white">
          <div class="flex items-center text-lg font-semibold text-gray-800 pb-2 border-b border-gray-100">
             <i class="fas fa-id-badge text-blue-500 mr-3"></i>
             Personal Details
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">
                First Name<span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                v-model="form.first_name"
                class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500"
                required
              />
              <p v-if="errors.first_name" class="text-red-500 text-xs mt-1">{{ errors.first_name }}</p>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">
                Last Name<span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                v-model="form.last_name"
                class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500"
                required
              />
              <p v-if="errors.last_name" class="text-red-500 text-xs mt-1">{{ errors.last_name }}</p>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">Middle Name</label>
              <input
                type="text"
                v-model="form.middle_name"
                maxlength="50"
                class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500"
              />
              <p v-if="errors.middle_name" class="text-red-500 text-xs mt-1">{{ errors.middle_name }}</p>
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">
                Email<span class="text-red-500">*</span>
              </label>
              <input
                type="email"
                v-model="form.email"
                class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500"
                required
              />
              <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">
                Contact Number<span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                v-model="form.contact_number"
                class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500"
                required
              />
              <p v-if="errors.contact_number" class="text-red-500 text-xs mt-1">{{ errors.contact_number }}</p>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">
                Gender<span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.gender"
                class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                required
              >
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
              <p v-if="errors.gender" class="text-red-500 text-xs mt-1">{{ errors.gender }}</p>
            </div>
          </div>
        </div>

        <div class="space-y-4 p-4 border border-gray-200 rounded-xl shadow-inner bg-white">
          <div class="flex items-center text-lg font-semibold text-gray-800 pb-2 border-b border-gray-100">
             <i class="fas fa-graduation-cap text-green-500 mr-3"></i>
             Academic Details
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">
                Program<span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.program_id"
                class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                required
              >
                <option value="">Select Program</option>
                <option v-for="program in programs" :key="program.id" :value="program.id">
                  {{ program.name }}
                </option>
              </select>
              <p v-if="errors.program_id" class="text-red-500 text-xs mt-1">{{ errors.program_id }}</p>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">
                Year Graduated<span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.graduate_year_graduated"
                class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                required
              >
                <option value="">Select Year</option>
                <option v-if="form.graduate_year_graduated && !years.includes(form.graduate_year_graduated)" :value="form.graduate_year_graduated">
                  {{ form.graduate_year_graduated }} (Current)
                </option>
                <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
              </select>
              <p v-if="errors.graduate_year_graduated" class="text-red-500 text-xs mt-1">{{ errors.graduate_year_graduated }}</p>
            </div>

            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">
                Term<span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.graduate_term"
                class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                required
              >
                <option value="">Select Term</option>
                <option v-if="form.graduate_term && !terms.includes(form.graduate_term)" :value="form.graduate_term">
                  {{ form.graduate_term }} (Current)
                </option>
                <option v-for="term in terms" :key="term" :value="term">{{ term }}</option>
              </select>
              <p v-if="errors.graduate_term" class="text-red-500 text-xs mt-1">{{ errors.graduate_term }}</p>
            </div>
          </div>
        </div>

        <div class="space-y-4 p-4 border border-gray-200 rounded-xl shadow-inner bg-white">
          <div class="flex items-center text-lg font-semibold text-gray-800 pb-2 border-b border-gray-100">
             <i class="fas fa-briefcase text-purple-500 mr-3"></i>
             Employment Status
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">
                Employment Status<span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.employment_status"
                @change="handleEmploymentStatus"
                class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                required
              >
                <option value="">Select Status</option>
                <option value="Employed">Employed</option>
                <option value="Underemployed">Underemployed</option>
                <option value="Unemployed">Unemployed</option>
              </select>
              <p v-if="errors.employment_status" class="text-red-500 text-xs mt-1">{{ errors.employment_status }}</p>
            </div>
            
            <div v-if="form.employment_status !== 'Unemployed'">
              <label class="block text-xs font-medium text-gray-600 mb-1">
                Current Job Title<span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                v-model="form.current_job_title"
                class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500"
                required
              />
              <p v-if="errors.current_job_title" class="text-red-500 text-xs mt-1">{{ errors.current_job_title }}</p>
            </div>
          </div>
        </div>

        <div v-if="form.employment_status === 'Employed' || form.employment_status === 'Underemployed'"
             class="space-y-4 p-4 border border-gray-200 rounded-xl shadow-inner bg-white">
          <div class="flex items-center text-lg font-semibold text-gray-800 pb-2 border-b border-gray-100">
             <i class="fas fa-building text-orange-500 mr-3"></i>
             Company Details
          </div>

          <div>
            <label class="inline-flex items-center text-sm font-medium text-gray-700">
              <input type="checkbox" v-model="form.company_not_found"
                class="form-checkbox text-blue-600 h-4 w-4 border-gray-300 rounded focus:ring-blue-500" />
              <span class="ml-2">Company is not listed</span>
            </label>
          </div>

          <div v-if="!form.company_not_found" class="relative">
            <label class="block text-xs font-medium text-gray-600 mb-1">
              Company Name (Search)<span class="text-red-500">*</span>
            </label>
            <input
              type="text"
              v-model="companySearch"
              class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Type to search company..." autocomplete="off"
              @focus="showCompanyDropdown = true"
              @blur="handleCompanyBlur"
              required
            />
            
            <p v-if="form.company_name" class="text-xs text-green-600 mt-1 flex items-center">
              <i class="fas fa-check-circle mr-1"></i> Selected: {{ form.company_name }}
              <button type="button" @click="clearCompany" class="ml-2 text-red-500 hover:text-red-700">
                <i class="fas fa-times-circle"></i>
              </button>
            </p>

            <div v-if="showCompanyDropdown && filteredCompanies.length"
              class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-xl max-h-48 overflow-y-auto">
              <div v-for="company in filteredCompanies" :key="company.id"
                class="px-4 py-2 text-sm text-gray-800 hover:bg-blue-50 cursor-pointer transition-colors duration-200"
                @mousedown.prevent="selectCompany(company)">
                {{ company.company_name || company.name }}
              </div>
            </div>
            <p v-if="errors.company_name" class="text-red-500 text-xs mt-1">{{ errors.company_name }}</p>
          </div>

          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">
                Other Company Name<span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                v-model="form.other_company_name"
                class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500"
                required
              />
              <p v-if="errors.other_company_name" class="text-red-500 text-xs mt-1">{{ errors.other_company_name }}</p>
            </div>
            
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">
                Sector Name<span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.other_company_sector"
                class="w-full border-gray-300 rounded-lg px-3 py-2 text-sm bg-white shadow-sm focus:ring-blue-500 focus:border-blue-500"
                required
              >
                <option value="">Select Sector</option>
                <option v-for="sector in props.sectors" :key="sector.id" :value="sector.id">
                  {{ sector.name }}
                </option>
              </select>
              <p v-if="errors.other_company_sector" class="text-red-500 text-xs mt-1">{{ errors.other_company_sector }}</p>
            </div>
          </div>
        </div>
      </form>

      <div class="px-6 py-4 border-t border-gray-100 flex justify-end bg-gray-50 flex-shrink-0">
        <button
          type="submit"
          @click="submitForm"
          class="inline-flex items-center px-6 py-2 text-sm font-semibold rounded-lg shadow-md text-white bg-blue-600 hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-[1.01]"
          :disabled="processing"
        >
          <i v-if="processing" class="fas fa-spinner fa-spin mr-2"></i>
          <i v-else class="fas fa-save mr-2"></i>
          {{ form.id ? 'Save Changes' : 'Create Graduate' }}
        </button>
      </div>
    </div>
  </div>
</template>