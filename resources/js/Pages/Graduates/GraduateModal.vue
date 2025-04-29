<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 relative">
      <!-- Close Button -->
      <button @click="$emit('close')" class="absolute top-3 right-4 text-gray-500 hover:text-red-500 text-2xl">&times;</button>
      
      <h2 class="text-xl font-semibold mb-6">{{ form.id ? 'Edit Graduate' : 'Add Graduate' }}</h2>

      <form @submit.prevent="submitForm" class="space-y-4">
        <!-- First Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700">First Name<span class="text-red-500">*</span></label>
          <input type="text" v-model="form.first_name" class="w-full border rounded px-3 py-2" required />
          <p v-if="errors.first_name" class="text-red-500 text-sm mt-1">{{ errors.first_name }}</p>
        </div>

        <!-- Last Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Last Name<span class="text-red-500">*</span></label>
          <input type="text" v-model="form.last_name" class="w-full border rounded px-3 py-2" required />
          <p v-if="errors.last_name" class="text-red-500 text-sm mt-1">{{ errors.last_name }}</p>
        </div>

        <!-- Middle Initial -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Middle Initial</label>
          <input type="text" v-model="form.middle_initial" class="w-full border rounded px-3 py-2" maxlength="5" />
        </div>

        <!-- Program -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Program<span class="text-red-500">*</span></label>
          <select v-model="form.program_id" class="w-full border rounded px-3 py-2" required>
            <option disabled value="">-- Select Program --</option>
            <option v-for="program in programs" :key="program.id" :value="program.id">
              {{ program.name }}
            </option>
          </select>
          <p v-if="errors.program_id" class="text-red-500 text-sm mt-1">{{ errors.program_id }}</p>
        </div>

        <!-- Year Graduated -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Year Graduated<span class="text-red-500">*</span></label>
          <select v-model="form.school_year_id" class="w-full border rounded px-3 py-2" required>
            <option disabled value="">-- Select Year --</option>
            <option v-for="year in years" :key="year.id" :value="year.id">
              {{ year.school_year_range }}
            </option>
          </select>
          <p v-if="errors.school_year_id" class="text-red-500 text-sm mt-1">{{ errors.school_year_id }}</p>
        </div>

        <!-- Employment Status -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Employment Status<span class="text-red-500">*</span></label>
          <select v-model="form.employment_status" @change="handleEmploymentStatus" class="w-full border rounded px-3 py-2" required>
            <option value="Employed">Employed</option>
            <option value="Underemployed">Underemployed</option>
            <option value="Unemployed">Unemployed</option>
          </select>
          <p v-if="errors.employment_status" class="text-red-500 text-sm mt-1">{{ errors.employment_status }}</p>
        </div>

        <!-- Current Job Title -->
        <div v-if="form.employment_status !== 'Unemployed'">
          <label class="block text-sm font-medium text-gray-700">Current Job Title</label>
          <input type="text" v-model="form.current_job_title" class="w-full border rounded px-3 py-2" placeholder="Ex. Nurse, Software Engineer" />
        </div>

        <!-- Buttons -->
        <div class="flex justify-end space-x-3 mt-6">
          <button type="button" @click="$emit('close')" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save</button>
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
  years: Array
})

const emit = defineEmits(['close'])

const form = ref({
  first_name: '',
  last_name: '',
  middle_initial: '',
  program_id: '',
  school_year_id: '',
  employment_status: 'Employed',
  current_job_title: ''
})

const errors = ref({})

// Prefill data when editing
watch(() => props.graduate, (grad) => {
  if (grad) {
    form.value = {
      id: grad.id,
      first_name: grad.first_name,
      last_name: grad.last_name,
      middle_initial: grad.middle_initial || '',
      program_id: grad.program_id,
      school_year_id: grad.school_year_id,
      employment_status: grad.employment_status,
      current_job_title: grad.current_job_title
    }
  } else {
    resetForm()
  }
}, { immediate: true })

function resetForm() {
  form.value = {
    first_name: '',
    last_name: '',
    middle_initial: '',
    program_id: '',
    school_year_id: '',
    employment_status: 'Employed',
    current_job_title: ''
  }
  errors.value = {}
}

function handleEmploymentStatus() {
  if (form.value.employment_status === 'Unemployed') {
    form.value.current_job_title = 'N/A'
  }
}

function submitForm() {
  const routeName = form.value.id ? 'graduates.update' : 'graduates.store'
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
