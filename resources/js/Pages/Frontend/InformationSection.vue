<script setup>
import { ref, computed } from 'vue';
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
  form.post(route('graduate.information.save'), {
    onSuccess: () => {
      showModal.value = true;
    }
  });
}

function goToProfile() {
  window.location.href = route('graduate.profile');
}
</script>

<template>
  <AppLayout>
    <div class="max-w-3xl mx-auto py-10">
      <h1 class="text-3xl font-bold mb-6">Graduate Information</h1>
      <form @submit.prevent="submit" class="space-y-8">
        <div class="bg-white rounded shadow p-6">
          <h2 class="text-xl font-semibold mb-4">Personal Details</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <InputLabel for="email">Email Address <span class="text-red-500">*</span></InputLabel>
              <TextInput
                id="email"
                v-model="form.email"
                type="email"
                required
                class="mt-1 block w-full bg-gray-100"
                readonly
              />
              <InputError :message="form.errors.email" />
            </div>
            <div>
              <InputLabel for="first_name">First Name <span class="text-red-500">*</span></InputLabel>
              <TextInput id="first_name" v-model="form.first_name" type="text" required class="mt-1 block w-full" />
              <InputError :message="form.errors.first_name" />
            </div>
            <div>
              <InputLabel for="middle_name">Middle Name</InputLabel>
              <TextInput id="middle_name" v-model="form.middle_name" type="text" class="mt-1 block w-full" />
              <InputError :message="form.errors.middle_name" />
            </div>
            <div>
              <InputLabel for="last_name">Last Name <span class="text-red-500">*</span></InputLabel>
              <TextInput id="last_name" v-model="form.last_name" type="text" required class="mt-1 block w-full" />
              <InputError :message="form.errors.last_name" />
            </div>
            <div>
              <InputLabel for="dob">Date of Birth <span class="text-red-500">*</span></InputLabel>
              <TextInput id="dob" v-model="form.dob" type="date" required class="mt-1 block w-full" />
              <InputError :message="form.errors.dob" />
            </div>
            <div>
              <InputLabel for="gender">Gender <span class="text-red-500">*</span></InputLabel>
              <select id="gender" v-model="form.gender" required class="mt-1 block w-full border-gray-300 rounded">
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
              <InputError :message="form.errors.gender" />
            </div>
            <div>
              <InputLabel for="mobile_number">Mobile Number <span class="text-red-500">*</span></InputLabel>
              <TextInput
                id="mobile_number"
                v-model="formattedMobileNumber"
                type="text"
                required
                class="mt-1 block w-full"
                placeholder="+63 XXX XXX XXXX"
              />
              <InputError :message="form.errors.mobile_number" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded shadow p-6">
          <h2 class="text-xl font-semibold mb-4">Educational Background</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <InputLabel for="graduate_school_graduated_from">School Graduated From <span class="text-red-500">*</span></InputLabel>
              <select id="graduate_school_graduated_from" v-model="form.graduate_school_graduated_from" required class="mt-1 block w-full border-gray-300 rounded">
                <option value="">Select School</option>
                <option v-for="school in props.institutions" :key="school.id" :value="school.id">{{ school.institution_name }}</option>
              </select>
              <InputError :message="form.errors.graduate_school_graduated_from" />
            </div>
            <div>
              <InputLabel for="graduate_degree">Degree <span class="text-red-500">*</span></InputLabel>
              <select id="graduate_degree" v-model="form.graduate_degree" required class="mt-1 block w-full border-gray-300 rounded">
                <option value="">Select Degree</option>
                <option v-for="degree in filteredDegrees" :key="degree.id" :value="degree.id">{{ degree.type }}</option>
              </select>
              <InputError :message="form.errors.graduate_degree" />
            </div>
            <div>
              <InputLabel for="graduate_program_completed">Program Completed <span class="text-red-500">*</span></InputLabel>
              <select id="graduate_program_completed" v-model="form.graduate_program_completed" required class="mt-1 block w-full border-gray-300 rounded">
                <option value="">Select Program</option>
                <option v-for="program in filteredPrograms" :key="program.id" :value="program.id">{{ program.name }}</option>
              </select>
              <InputError :message="form.errors.graduate_program_completed" />
            </div>
            <div>
              <InputLabel for="graduate_year_graduated">Year Graduated <span class="text-red-500">*</span></InputLabel>
              <select id="graduate_year_graduated" v-model="form.graduate_year_graduated" required class="mt-1 block w-full border-gray-300 rounded">
                <option value="">Select Year</option>
                <option v-for="year in filteredYears" :key="year.id" :value="year.id">{{ year.year }}</option>
              </select>
              <InputError :message="form.errors.graduate_year_graduated" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded shadow p-6">
          <h2 class="text-xl font-semibold mb-4">Employment Information</h2>
          <div class="mb-4">
            <InputLabel for="employment_status">Employment Status <span class="text-red-500">*</span></InputLabel>
            <select id="employment_status" v-model="form.employment_status" required class="mt-1 block w-full border-gray-300 rounded">
              <option value="">Select Status</option>
              <option value="Employed">Employed</option>
              <option value="Unemployed">Unemployed</option>
              <option value="Underemployed">Underemployed</option>
            </select>
            <InputError :message="form.errors.employment_status" />
          </div>
          <div v-if="form.employment_status !== 'Unemployed'">
            <InputLabel for="current_job_title">Current Job Title <span class="text-red-500">*</span></InputLabel>
            <TextInput id="current_job_title" v-model="form.current_job_title" type="text" required class="mt-1 block w-full" />
            <InputError :message="form.errors.current_job_title" />
          </div>
        </div>

        <div class="bg-white rounded shadow p-6"
            v-if="form.employment_status === 'Employed' || form.employment_status === 'Underemployed'"
          >
          <h2 class="text-xl font-semibold mb-4">Company Information</h2>
          <div class="mb-4">
            <label class="inline-flex items-center">
              <input type="checkbox" v-model="form.company_not_found" class="form-checkbox" />
              <span class="ml-2">My company is not listed</span>
            </label>
          </div>
          <!-- Company Searchable Dropdown -->
          <div v-if="!form.company_not_found">
            <InputLabel for="company_name">Company Name <span class="text-red-500">*</span></InputLabel>
            <TextInput
              id="company_search"
              v-model="companySearch"
              type="text"
              class="mt-1 block w-full"
              placeholder="Type to search company..."
              autocomplete="off"
              @focus="showCompanyDropdown = true"
              @blur="handleCompanyBlur"
            />
            <div v-if="showCompanyDropdown && filteredCompanies.length" class="border rounded bg-white shadow max-h-48 overflow-y-auto absolute z-10 w-full">
              <div
                v-for="company in filteredCompanies"
                :key="company.id"
                class="px-4 py-2 hover:bg-blue-100 cursor-pointer"
                @mousedown.prevent="selectCompany(company)"
              >
                {{ company.company_name }}
              </div>
            </div>
            <InputError :message="form.errors.company_name" />
          </div>
          <div v-else>
            <InputLabel for="other_company_name">Other Company Name <span class="text-red-500">*</span></InputLabel>
            <TextInput id="other_company_name" v-model="form.other_company_name" type="text" required class="mt-1 block w-full" />
            <InputError :message="form.errors.other_company_name" />

            <InputLabel for="other_company_sector">Sector <span class="text-red-500">*</span></InputLabel>
            <select id="other_company_sector" v-model="form.other_company_sector" required class="mt-1 block w-full border-gray-300 rounded">
              <option value="">Select Sector</option>
              <option v-for="sector in props.sectors" :key="sector.id" :value="sector.id">{{ sector.name }}</option>
            </select>
            <InputError :message="form.errors.other_company_sector" />
          </div>
        </div>

        <div class="flex justify-end">
          <PrimaryButton :disabled="form.processing">Save Information</PrimaryButton>
        </div>
      </form>

      <!-- Success Modal -->
      <Modal v-model="showModal">
        <template #header>
          <h2 class="text-2xl font-bold text-green-600">Profile Saved!</h2>
        </template>
        <template #body>
          <p class="mb-6 text-gray-700">
            Your graduate information has been saved.<br>
            You will now be redirected to your profile page.
          </p>
        </template>
        <template #footer>
          <PrimaryButton @click="goToProfile">Go to Profile</PrimaryButton>
        </template>
      </Modal>
    </div>
  </AppLayout>
</template>