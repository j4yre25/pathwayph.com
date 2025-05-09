<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  show: Boolean,
  graduate: Object,
  programs: Array,    // Institution-specific programs
  years: Array,       // Institution-specific school years
  instiUsers: Array,  // Institution users for "School Graduated From" dropdown
})

const emit = defineEmits(['close'])

// Use "graduate_year_graduated" to hold the raw school year range
const form = ref({
  id: null,
  first_name: '',
  last_name: '',
  middle_initial: '',
  email: '',
  graduate_school_graduated_from: '',
  program_id: '',
  graduate_year_graduated: '', // <== using raw school year range (e.g. "2023-2024")
  employment_status: 'Employed',
  current_job_title: '',
  gender: '',
  dob: '',
  // (Optional) You might remove password fields since they are auto‑generated.
})

// Ensure mobile number binds to the correct key:
form.value.contact_number = ''

const errors = ref({})
const maxDate = ref(new Date().toISOString().split('T')[0])
const minDate = ref('1900-01-01')

// Pre-fill form if we are editing (assumes backend returns the school year range as "year_graduated")
onMounted(() => {
  if (props.graduate) {
    form.value = {
      id: props.graduate.id,
      first_name: props.graduate.first_name,
      last_name: props.graduate.last_name,
      middle_initial: props.graduate.middle_initial || '',
      email: props.graduate.email,
      graduate_school_graduated_from: props.graduate.graduate_school_graduated_from,
      program_id: props.graduate.program_id,
      graduate_year_graduated: props.graduate.year_graduated || '', 
      employment_status: props.graduate.employment_status,
      current_job_title: props.graduate.current_job_title,
      gender: props.graduate.gender,
      dob: props.graduate.dob,
      // If you stored contact_number in the user table, prefill that as well:
      contact_number: props.graduate.contact_number || '',
    }
  } else {
    resetForm()
  }
})

function resetForm() {
  form.value = {
    id: null,
    first_name: '',
    last_name: '',
    middle_initial: '',
    email: '',
    graduate_school_graduated_from: '',
    program_id: '',
    graduate_year_graduated: '',
    employment_status: 'Employed',
    current_job_title: '',
    gender: '',
    dob: '',
    contact_number: '',
  }
  errors.value = {}
}

const filteredPrograms = computed(() => {
  return props.programs.filter(
    program => Number(program.institution_id) === Number(form.value.graduate_school_graduated_from)
  )
})

const filteredSchoolYears = computed(() => {
  // Filter the years based on the selected institution
  return props.years.filter(
    year => Number(year.institution_id) === Number(form.value.graduate_school_graduated_from)
  )
})

function handleEmploymentStatus() {
  if (form.value.employment_status === 'Unemployed') {
    form.value.current_job_title = 'N/A'
  }
}

function submitForm() {
  // Choose route based on add (manual) or edit
  const routeName = form.value.id ? 'graduates.update' : 'graduates.manual.store'
  const method = form.value.id ? 'patch' : 'post'
  const url = form.value.id ? route(routeName, { graduate: form.value.id }) : route(routeName)
  router[method](url, form.value, {
    onSuccess: () => {
      emit('close')
      resetForm()
    },
    onError: (validationErrors) => {
      errors.value = validationErrors
    }
  })
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6 relative">
      <!-- Close Button -->
      <button @click="$emit('close')" class="absolute top-3 right-4 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
      <h2 class="text-xl font-semibold mb-6">
        {{ form.id ? 'Edit Graduate' : 'Add Graduate' }}
      </h2>
      <form @submit.prevent="submitForm" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- First Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              First Name<span class="text-red-500">*</span>
            </label>
            <input type="text" v-model="form.first_name" class="w-full border rounded px-3 py-2" required />
            <p v-if="errors.first_name" class="text-red-500 text-sm mt-1">{{ errors.first_name }}</p>
          </div>
          <!-- Last Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Last Name<span class="text-red-500">*</span>
            </label>
            <input type="text" v-model="form.last_name" class="w-full border rounded px-3 py-2" required />
            <p v-if="errors.last_name" class="text-red-500 text-sm mt-1">{{ errors.last_name }}</p>
          </div>
          <!-- Middle Initial -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Middle Initial<span class="text-red-500">*</span>
            </label>
            <input type="text" v-model="form.middle_initial" class="w-full border rounded px-3 py-2" maxlength="5" required />
            <p v-if="errors.middle_initial" class="text-red-500 text-sm mt-1">{{ errors.middle_initial }}</p>
          </div>
          <!-- Email Address -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Email Address<span class="text-red-500">*</span>
            </label>
            <input type="email" v-model="form.email" class="w-full border rounded px-3 py-2" required />
            <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
          </div>
          <!-- Gender -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Gender<span class="text-red-500">*</span>
            </label>
            <select v-model="form.gender" class="w-full border rounded px-3 py-2" required>
              <option disabled value="">-- Select Gender --</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
              <option value="Other">Other</option>
            </select>
            <p v-if="errors.gender" class="text-red-500 text-sm mt-1">{{ errors.gender }}</p>
          </div>
          <!-- Date of Birth -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Date of Birth<span class="text-red-500">*</span>
            </label>
            <input type="date" v-model="form.dob" class="w-full border rounded px-3 py-2" required :max="maxDate" :min="minDate" />
            <p v-if="errors.dob" class="text-red-500 text-sm mt-1">{{ errors.dob }}</p>
          </div>
          <!-- School Graduated From -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              School Graduated From<span class="text-red-500">*</span>
            </label>
            <select v-model="form.graduate_school_graduated_from" class="w-full border rounded px-3 py-2" required>
              <option disabled value="">-- Select School --</option>
              <option v-for="inst in instiUsers" :key="inst.id" :value="inst.id">
                {{ inst.institution_name }}
              </option>
            </select>
            <p v-if="errors.graduate_school_graduated_from" class="text-red-500 text-sm mt-1">
              {{ errors.graduate_school_graduated_from }}
            </p>
          </div>
          <!-- Program Completed -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Program Completed<span class="text-red-500">*</span>
            </label>
            <select v-model="form.program_id" class="w-full border rounded px-3 py-2" required>
              <option disabled value="">-- Select Program --</option>
              <option v-for="program in filteredPrograms" :key="program.id" :value="program.id">
                {{ program.name }}
              </option>
            </select>
            <p v-if="errors.program_id" class="text-red-500 text-sm mt-1">{{ errors.program_id }}</p>
          </div>
          <!-- Year Graduated -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Year Graduated<span class="text-red-500">*</span>
            </label>
            <!-- Use the raw school year range value -->
            <select id="graduate_year_graduated" v-model="form.graduate_year_graduated" class="w-full border rounded px-3 py-2" required>
              <option disabled value="">-- Select Year --</option>
              <option v-for="year in filteredSchoolYears" :key="year.id" :value="year.school_year_range">
                {{ year.school_year_range }}
              </option>
            </select>
            <p v-if="errors.graduate_year_graduated" class="text-red-500 text-sm mt-1">
              {{ errors.graduate_year_graduated }}
            </p>
          </div>
          <!-- Employment Status -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Employment Status<span class="text-red-500">*</span>
            </label>
            <select v-model="form.employment_status" @change="handleEmploymentStatus" class="w-full border rounded px-3 py-2" required>
              <option value="" disabled>Select Employment Status</option>
              <option value="Employed">Employed</option>
              <option value="Underemployed">Underemployed</option>
              <option value="Unemployed">Unemployed</option>
            </select>
            <p v-if="errors.employment_status" class="text-red-500 text-sm mt-1">{{ errors.employment_status }}</p>
          </div>
          <!-- Current Job Title -->
          <div v-if="form.employment_status !== 'Unemployed'">
            <label class="block text-sm font-medium text-gray-700">Current Job Title</label>
            <input type="text" v-model="form.current_job_title" class="w-full border rounded px-3 py-2" placeholder="e.g., Nurse, Software Engineer" />
            <p v-if="errors.current_job_title" class="text-red-500 text-sm mt-1">{{ errors.current_job_title }}</p>
          </div>
          <!-- Mobile Number -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Mobile Number<span class="text-red-500">*</span></label>
            <!-- Bind to form.contact_number -->
            <input type="text" v-model="form.contact_number" class="w-full border rounded px-3 py-2" required />
            <p v-if="errors.contact_number" class="text-red-500 text-sm mt-1">{{ errors.contact_number }}</p>
          </div>
          <!-- (Optional) Password Section: Since the password is auto-generated, you may hide these inputs -->
          <!--
          <h3 class="mt-6 mb-2 font-semibold">Set Password</h3>
          <div>
            <label for="password">Password</label>
            <TextInput id="password" v-model="form.password" type="password" required />
            <InputError :message="form.errors.password" />
          </div>
          <div>
            <label for="password_confirmation">Confirm Password</label>
            <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" required />
            <InputError :message="form.errors.password_confirmation" />
          </div>
          -->
        </div>
        <!-- Buttons -->
        <div class="flex justify-end space-x-3 mt-6">
          <button type="button" @click="$emit('close')" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">
            Cancel
          </button>
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Save
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
