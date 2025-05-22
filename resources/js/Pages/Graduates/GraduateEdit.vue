<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-6 relative">
      <!-- Close Button -->
      <button @click="$emit('close')" class="absolute top-3 right-4 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
      <h2 class="text-xl font-semibold mb-6">
        Edit Graduate
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
          <!-- Middle Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Middle Name
            </label>
            <input type="text" v-model="form.middle_name" class="w-full border rounded px-3 py-2" maxlength="50" />
            <p v-if="errors.middle_name" class="text-red-500 text-sm mt-1">{{ errors.middle_name }}</p>
          </div>
          <!-- Email Address -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Email<span class="text-red-500">*</span>
            </label>
            <input type="email" v-model="form.email" class="w-full border rounded px-3 py-2" required />
            <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
          </div>
          <!-- Contact Number -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Contact Number<span class="text-red-500">*</span>
            </label>
            <input type="text" v-model="form.contact_number" class="w-full border rounded px-3 py-2" required />
            <p v-if="errors.contact_number" class="text-red-500 text-sm mt-1">{{ errors.contact_number }}</p>
          </div>
          <!-- Gender -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Gender<span class="text-red-500">*</span>
            </label>
            <select v-model="form.gender" class="w-full border rounded px-3 py-2" required>
              <option value="">Select Gender</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
            <p v-if="errors.gender" class="text-red-500 text-sm mt-1">{{ errors.gender }}</p>
          </div>
          <!-- Date of Birth -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Date of Birth<span class="text-red-500">*</span>
            </label>
            <input type="date" v-model="form.dob" class="w-full border rounded px-3 py-2" required />
            <p v-if="errors.dob" class="text-red-500 text-sm mt-1">{{ errors.dob }}</p>
          </div>
        </div>
        <!-- Academic Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Program -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Program<span class="text-red-500">*</span>
            </label>
            <select v-model="form.program_id" class="w-full border rounded px-3 py-2" required>
              <option value="">Select Program</option>
              <option v-for="program in programs" :key="program.id" :value="program.id">{{ program.name }}</option>
            </select>
            <p v-if="errors.program_id" class="text-red-500 text-sm mt-1">{{ errors.program_id }}</p>
          </div>
          <!-- Year Graduated -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Year Graduated<span class="text-red-500">*</span>
            </label>
            <select v-model="form.graduate_year_graduated" class="w-full border rounded px-3 py-2" required>
              <option value="">Select Year</option>
              <option v-for="year in years" :key="year.id" :value="year.school_year_range">{{ year.school_year_range }}</option>
            </select>
            <p v-if="errors.graduate_year_graduated" class="text-red-500 text-sm mt-1">{{ errors.graduate_year_graduated }}</p>
          </div>
        </div>
        <!-- Employment Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Employment Status -->
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Employment Status<span class="text-red-500">*</span>
            </label>
            <select v-model="form.employment_status" @change="handleEmploymentStatus" class="w-full border rounded px-3 py-2" required>
              <option value="">Select Status</option>
              <option value="Employed">Employed</option>
              <option value="Underemployed">Underemployed</option>
              <option value="Unemployed">Unemployed</option>
            </select>
            <p v-if="errors.employment_status" class="text-red-500 text-sm mt-1">{{ errors.employment_status }}</p>
          </div>
          <!-- Current Job Title -->
          <div v-if="form.employment_status !== 'Unemployed'">
            <label class="block text-sm font-medium text-gray-700">
              Current Job Title<span class="text-red-500">*</span>
            </label>
            <input
              type="text"
              v-model="form.current_job_title"
              class="w-full border rounded px-3 py-2"
              :required="form.employment_status === 'Employed' || form.employment_status === 'Underemployed'"
            />
            <p v-if="errors.current_job_title" class="text-red-500 text-sm mt-1">{{ errors.current_job_title }}</p>
          </div>
        </div>
        <!-- Submit -->
        <div class="flex justify-end pt-4">
          <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700" :disabled="processing">
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  show: Boolean,
  graduate: Object,
  programs: Array,
  years: Array,
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
  employment_status: 'Employed',
  current_job_title: '',
  gender: '',
  dob: '',
  contact_number: '',
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
      employment_status: grad.employment_status || 'Employed',
      current_job_title: grad.current_job_title || '',
      gender: grad.gender || '',
      dob: grad.dob || '',
      contact_number: grad.contact_number || '',
    }
  }
}, { immediate: true })

function handleEmploymentStatus() {
  if (form.value.employment_status === 'Unemployed') {
    form.value.current_job_title = '';
  }
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