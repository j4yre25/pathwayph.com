<script setup>
import { ref, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'

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
  processing.value = true
  const routeName = form.value.id ? 'graduates.update' : 'graduates.manual.store'
  const method = form.value.id ? 'put' : 'post'
  const url = form.value.id ? route(routeName, { graduate: form.value.id }) : route(routeName)
  router[method](url, form.value, {
    onSuccess: () => {
      emit('close')
      processing.value = false
    },
    onError: (validationErrors) => {
      errors.value = validationErrors
      processing.value = false
    }
  })
}
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center px-4"
  >
    <div
      class="bg-white rounded-xl shadow-2xl w-full max-w-3xl p-6 relative overflow-y-auto"
      style="max-height: 90vh"
    >
      <!-- Close Button -->
      <button
        @click="$emit('close')"
        class="absolute top-4 right-5 text-gray-400 hover:text-red-500 transition text-2xl"
      >
        &times;
      </button>

      <!-- Title -->
      <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
        Edit Graduate
      </h2>

      <form @submit.prevent="submitForm" class="space-y-5">
        <!-- Personal Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-gray-600">
              First Name<span class="text-red-500">*</span>
            </label>
            <input
              type="text"
              v-model="form.first_name"
              class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
              required
            />
            <p v-if="errors.first_name" class="text-red-500 text-xs mt-1">
              {{ errors.first_name }}
            </p>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600">
              Last Name<span class="text-red-500">*</span>
            </label>
            <input
              type="text"
              v-model="form.last_name"
              class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
              required
            />
            <p v-if="errors.last_name" class="text-red-500 text-xs mt-1">
              {{ errors.last_name }}
            </p>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600">Middle Name</label>
            <input
              type="text"
              v-model="form.middle_name"
              maxlength="50"
              class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
            />
            <p v-if="errors.middle_name" class="text-red-500 text-xs mt-1">
              {{ errors.middle_name }}
            </p>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600">
              Email<span class="text-red-500">*</span>
            </label>
            <input
              type="email"
              v-model="form.email"
              class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
              required
            />
            <p v-if="errors.email" class="text-red-500 text-xs mt-1">
              {{ errors.email }}
            </p>
          </div>
        </div>

        <!-- Contact & Gender -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-gray-600">
              Contact Number<span class="text-red-500">*</span>
            </label>
            <input
              type="text"
              v-model="form.contact_number"
              class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
              required
            />
            <p v-if="errors.contact_number" class="text-red-500 text-xs mt-1">
              {{ errors.contact_number }}
            </p>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600">
              Gender<span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.gender"
              class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none"
              required
            >
              <option value="">Select Gender</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
            <p v-if="errors.gender" class="text-red-500 text-xs mt-1">
              {{ errors.gender }}
            </p>
          </div>
        </div>

        <!-- Academic Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-medium text-gray-600">
              Program<span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.program_id"
              class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none"
              required
            >
              <option value="">Select Program</option>
              <option v-for="program in programs" :key="program.id" :value="program.id">
                {{ program.name }}
              </option>
            </select>
            <p v-if="errors.program_id" class="text-red-500 text-xs mt-1">
              {{ errors.program_id }}
            </p>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600">
              Year Graduated<span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.graduate_year_graduated"
              class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none"
              required
            >
              <option v-if="form.graduate_year_graduated" :value="form.graduate_year_graduated">
                {{ form.graduate_year_graduated }} (Current)
              </option>
              <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
            </select>
            <p v-if="errors.graduate_year_graduated" class="text-red-500 text-xs mt-1">
              {{ errors.graduate_year_graduated }}
            </p>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600">
              Term<span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.graduate_term"
              class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none"
              required
            >
              <option v-if="form.graduate_term" :value="form.graduate_term">
                {{ form.graduate_term }} (Current)
              </option>
              <option v-for="term in terms" :key="term" :value="term">{{ term }}</option>
            </select>
            <p v-if="errors.graduate_term" class="text-red-500 text-xs mt-1">
              {{ errors.graduate_term }}
            </p>
          </div>
        </div>

        <!-- Employment Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-gray-600">
              Employment Status<span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.employment_status"
              @change="handleEmploymentStatus"
              class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none"
              required
            >
              <option value="">Select Status</option>
              <option value="Employed">Employed</option>
              <option value="Underemployed">Underemployed</option>
              <option value="Unemployed">Unemployed</option>
            </select>
            <p v-if="errors.employment_status" class="text-red-500 text-xs mt-1">
              {{ errors.employment_status }}
            </p>
          </div>
          <div v-if="form.employment_status !== 'Unemployed'">
            <label class="block text-xs font-medium text-gray-600">
              Current Job Title<span class="text-red-500">*</span>
            </label>
            <input
              type="text"
              v-model="form.current_job_title"
              class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
              required
            />
            <p v-if="errors.current_job_title" class="text-red-500 text-xs mt-1">
              {{ errors.current_job_title }}
            </p>
          </div>
        </div>

        <!-- Company Info (only if employed or underemployed) -->
        <div v-if="form.employment_status === 'Employed' || form.employment_status === 'Underemployed'" class="space-y-4">
          <div>
            <label class="inline-flex items-center">
              <input type="checkbox" v-model="form.company_not_found"
                class="form-checkbox text-blue-400 focus:ring-blue-400 focus:ring-offset-0 bg-transparent border-black/20" />
              <span class="ml-3 text-slate-800 font-medium">Company not listed</span>
            </label>
          </div>
          <!-- Company Searchable Dropdown -->
          <div v-if="!form.company_not_found" class="relative">
            <label class="block text-xs font-medium text-gray-600">
              Company Name<span class="text-red-500">*</span>
            </label>
            <input
              type="text"
              v-model="companySearch"
              class="mt-2 block w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
              placeholder="Type to search company..." autocomplete="off"
              @focus="showCompanyDropdown = true"
              @blur="handleCompanyBlur"
            />
            <div v-if="showCompanyDropdown && filteredCompanies.length"
              class="absolute z-20 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 overflow-y-auto">
              <div v-for="company in filteredCompanies" :key="company.id"
                class="px-4 py-3 text-slate-800 hover:bg-gray-100 cursor-pointer transition-colors duration-200"
                @mousedown.prevent="selectCompany(company)">
                {{ company.company_name || company.name }}
              </div>
            </div>
            <p v-if="errors.company_name" class="text-red-500 text-xs mt-1">
              {{ errors.company_name }}
            </p>
          </div>
          <!-- Other Company & Sector if not found -->
          <div v-else class="space-y-4">
            <div>
              <label class="block text-xs font-medium text-gray-600">
                Other Company Name<span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                v-model="form.other_company_name"
                class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
                required
              />
              <p v-if="errors.other_company_name" class="text-red-500 text-xs mt-1">
                {{ errors.other_company_name }}
              </p>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600">
                Sector Name<span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.other_company_sector"
                class="w-full border border-gray-300 rounded-lg px-3 py-1.5 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none"
                required
              >
                <option value="">Select Sector</option>
                <option v-for="sector in props.sectors" :key="sector.id" :value="sector.id">
                  {{ sector.name }}
                </option>
              </select>
              <p v-if="errors.other_company_sector" class="text-red-500 text-xs mt-1">
                {{ errors.other_company_sector }}
              </p>
            </div>
          </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end pt-2">
          <button
            type="submit"
            class="bg-blue-600 text-white px-5 py-2 text-sm rounded-lg shadow-md hover:bg-blue-700 transition disabled:opacity-50"
            :disabled="processing"
          >
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
