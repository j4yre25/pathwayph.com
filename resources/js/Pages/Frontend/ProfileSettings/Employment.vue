<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import TextArea from '@/Components/TextArea.vue';
import SelectInput from '@/Components/SelectInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Datepicker from 'vue3-datepicker';
import { isValid } from 'date-fns';
import '@fortawesome/vue-fontawesome';

// Define props
const props = defineProps({
  activeSection: {
    type: String,
    default: 'employment'
  },
  employmentPreferences: {
    type: Object,
    default: () => ({
      job_type: '',
      salary_expectations: '',
      preferred_locations: '',
      work_environment: '',
      additional_notes: ''
    })
  }

});

console.log('Employment Reference:', props);

const emit = defineEmits(['close-all-modals', 'reset-all-states']);
// Employment Data
const employmentPreferences = ref({
  jobTypes: props.employmentPreferences?.job_type
    ? props.employmentPreferences.job_type.split(',').map(s => s.trim()).filter(Boolean)
    : [],
  salaryExpectations: {
    min: props.employmentPreferences?.employment_min_salary || '',
    max: props.employmentPreferences?.employment_max_salary || '',
    frequency: props.employmentPreferences?.salary_type || 'monthly'
  },
  preferredLocations: props.employmentPreferences?.location
    ? props.employmentPreferences.location.split(',').map(s => s.trim()).filter(Boolean)
    : [],
  workEnvironment: props.employmentPreferences?.work_environment
    ? props.employmentPreferences.work_environment.split(',').map(s => s.trim()).filter(Boolean)
    : [],
  additionalNotes: props.employmentPreferences?.additional_notes || ''
});

const employmentForm = useForm({
  job_type: [],
  employment_min_salary: '',
  employment_max_salary: '',
  salary_type: 'monthly',
  preferred_locations: [],
  work_environment: [],
  additional_notes: '',
});

const isAddLocationModalOpen = ref(false);

// Employment Preferences Handlers
const saveEmploymentPreferences = () => {
  employmentForm.job_type = employmentPreferences.value.jobTypes.join(',');
  employmentForm.employment_min_salary = employmentPreferences.value.salaryExpectations.min;
  employmentForm.employment_max_salary = employmentPreferences.value.salaryExpectations.max;
  employmentForm.salary_type = employmentPreferences.value.salaryExpectations.frequency;
employmentForm.preferred_locations = employmentPreferences.value.preferredLocations.join(',');  employmentForm.work_environment = employmentPreferences.value.workEnvironment;
  employmentForm.additional_notes = employmentPreferences.value.additionalNotes;

  employmentForm.post(route('employment.preferences.updateEmploymentPreferences'), {
    onSuccess: () => {
      emit('close-all-modals');
      router.reload({ only: ['employmentReference'] });
    },
    onError: (errors) => {
      console.error('Error saving employment preferences:', errors);
      alert('An error occurred while saving the employment preferences. Please try again.');
    },
  });
};

const toggleJobType = (type) => {
  const idx = employmentPreferences.value.jobTypes.indexOf(type);
  if (idx === -1) {
    employmentPreferences.value.jobTypes.push(type);
  } else {
    employmentPreferences.value.jobTypes.splice(idx, 1);
  }
};

const newLocation = ref('');

const addPreferredLocation = () => {
  if (!newLocation.value.trim()) {
    alert("Please enter a valid location.");
    return;
  }

  if (!employmentPreferences.value.preferredLocations.includes(newLocation.value.trim())) {
    employmentPreferences.value.preferredLocations.push(newLocation.value.trim());

    console.log('Is Add Location Modal Open:', isAddLocationModalOpen.value);

    console.log("Location added:", newLocation.value);

    alert("Location added successfully!");
  } else {
    alert("This location is already in your preferences.");
  }

  closeAddLocationModal();
};

const resetEmploymentPreferences = () => {
  employmentPreferences.value = {
    jobTypes: [],
    salaryExpectations: {
      range: 'Select expected salary range',
      frequency: 'per year'
    },
    preferredLocations: [],
    workEnvironment: [],
    additionalNotes: ''
  };
  console.log('Employment preferences reset.');
};

const closeAddLocationModal = () => {
  isAddLocationModalOpen.value = false;
  newLocation.value = '';
  console.log('Add location modal closed.');
};

// Function to initialize data on component mount
const initializeData = () => {
  // Fetch initial data or set defaults
  console.log('Initializing data...');
};

// Call initialize function on component mount
onMounted(() => {
  initializeData();
});

watch(
  () => props.employmentPreferences,
  (newVal) => {
    employmentPreferences.value = {
      jobTypes: newVal?.job_type
        ? newVal.job_type.split(',').map(s => s.trim()).filter(Boolean)
        : [],
      salaryExpectations: {
        min: newVal?.employment_min_salary || '',
        max: newVal?.employment_max_salary || '',
        frequency: newVal?.salary_type || 'monthly'
      },
      preferredLocations: newVal?.location
        ? newVal.location.split(',').map(s => s.trim()).filter(Boolean)
        : [],
      workEnvironment: newVal?.work_environment
        ? newVal.work_environment.split(',').map(s => s.trim()).filter(Boolean)
        : [],
      additionalNotes: newVal?.additional_notes || ''
    };
  },
  { immediate: true }
);
</script>

<template>
  <div v-if="activeSection === 'employment'" class="flex flex-col lg:flex-row">
    <div class="w-full lg:w-1/1 mb-6 lg:mb-0">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold mb-4">Employment Preferences</h1>
      </div>
      <p class="text-gray-600 mb-6">Set your job preferences and requirements</p>

      <!-- Success Modal -->
      <Modal :show="isSuccessModalOpen" @close="closeSuccessModal">
        <div class="p-6">
          <div class="flex items-center justify-center mb-4">
            <div class="bg-green-100 rounded-full p-2">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
          </div>
          <h3 class="text-lg font-medium text-center mb-4">{{ successMessage }}</h3>
          <div class="flex justify-center">
            <button @click="closeSuccessModal"
              class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              OK
            </button>
          </div>
        </div>
      </Modal>

      <!-- Error Modal -->
      <Modal :show="isErrorModalOpen" @close="closeErrorModal">
        <div class="p-6">
          <div class="flex items-center justify-center mb-4">
            <div class="bg-red-100 rounded-full p-2">
              <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </div>
          </div>
          <h3 class="text-lg font-medium text-center mb-4">{{ errorMessage }}</h3>
          <div class="flex justify-center">
            <button @click="closeErrorModal"
              class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              OK
            </button>
          </div>
        </div>
      </Modal>

      <!-- Saved Preferences Container -->
      <div
        v-if="employmentPreferences.jobTypes.length || employmentPreferences.salaryExpectations.range || employmentPreferences.preferredLocations.length || employmentPreferences.workEnvironment.length || employmentPreferences.additionalNotes"
        class="mb-6 p-4 border border-gray-300 rounded-lg bg-gray-50">
        <h2 class="text-xl font-semibold mb-4">Saved Employment Preferences</h2>
        <div class="space-y-3">
          <p><strong>Job Types:</strong> {{ employmentPreferences.jobTypes.join(', ') }}</p>
          <p><strong>Salary Expectations:</strong> <span
              v-if="employmentPreferences.salaryExpectations.min && employmentPreferences.salaryExpectations.max">
              {{ employmentPreferences.salaryExpectations.min }} - {{ employmentPreferences.salaryExpectations.max }}
              pesos
              {{ employmentPreferences.salaryExpectations.frequency === 'monthly' ? ', Monthly' : ', Hourly' }}
            </span>
            <span v-else>

            </span>
          </p>
          <p><strong>Preferred Locations:</strong> {{ employmentPreferences.preferredLocations.join(', ') }}</p>
          <p><strong>Work Environment:</strong> {{ employmentPreferences.workEnvironment.join(', ') }}</p>
          <p><strong>Additional Notes:</strong> {{ employmentPreferences.additionalNotes }}</p>
        </div>
      </div>
      <!-- Form Fields -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Job Types</h2>
        <div class="flex flex-wrap gap-2">
          <button v-for="type in ['Full-time', 'Part-time', 'Contract', 'Freelance', 'Internship']" :key="type"
            :class="employmentPreferences.jobTypes.includes(type) ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700'"
            class="px-4 py-2 rounded-full hover:bg-indigo-700" @click="toggleJobType(type)">
            {{ type }}
          </button>
        </div>
      </div>
      <!-- Salary Expectations -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Salary Expectations</h2>
        <div class="flex items-center gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Minimum</label>
            <input type="number" v-model="employmentPreferences.salaryExpectations.min" placeholder="Min"
              class="border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-indigo-600 w-32" min="0" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Maximum</label>
            <input type="number" v-model="employmentPreferences.salaryExpectations.max" placeholder="Max"
              class="border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-indigo-600 w-32" min="0" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select v-model="employmentPreferences.salaryExpectations.frequency"
              class="border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-indigo-600">
              <option value="monthly">Monthly</option>
              <option value="hourly">Hourly</option>
            </select>
          </div>
        </div>
      </div>
      <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Preferred Locations</h2>
        <div class="flex flex-wrap gap-2">
          <span v-for="location in employmentPreferences.preferredLocations" :key="location"
            class="bg-gray-200 text-gray-700 px-4 py-2 rounded-full flex items-center">
            {{ location }}
            <button @click="removePreferredLocation(location)" class="ml-2 text-red-500 hover:text-red-700">
              &times; <!-- This is the "X" character -->
            </button>
          </span>
          <button class="bg-indigo-600 text-white px-4 py-2 rounded-full hover:bg-indigo-700"
            @click="isAddLocationModalOpen = true">
            + Add Location
          </button>
        </div>
      </div>
      <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Work Environment</h2>
        <div class="flex flex-wrap gap-8">
          <label class="flex items-center">
            <input type="checkbox" value="Remote" v-model="employmentPreferences.workEnvironment"
              class="form-checkbox text-indigo-600 focus:ring-indigo-600" />
            <span class="ml-2 text-gray-700">Remote</span>
          </label>
          <label class="flex items-center">
            <input type="checkbox" value="Hybrid" v-model="employmentPreferences.workEnvironment"
              class="form-checkbox text-indigo-600 focus:ring-indigo-600" />
            <span class="ml-2 text-gray-700">Hybrid</span>
          </label>
          <label class="flex items-center">
            <input type="checkbox" value="On-site" v-model="employmentPreferences.workEnvironment"
              class="form-checkbox text-indigo-600 focus:ring-indigo-600" />
            <span class="ml-2 text-gray-700">On-site</span>
          </label>
        </div>
      </div>
      <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Additional Notes</h2>
        <textarea v-model="employmentPreferences.additionalNotes"
          class="border border-gray-300 rounded w-full px-4 py-2 focus:ring-2 focus:ring-indigo-600" rows="4"
          placeholder="Any other preferences or requirements..."></textarea>
      </div>
      <div class="flex space-x-4">
        <button class="bg-indigo-600 text-white px-6 py-3 rounded-lg flex items-center"
          @click="saveEmploymentPreferences">
          <i class="fas fa-save mr-2"></i> Save Preferences
        </button>
        <button class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg flex items-center hover:bg-gray-400"
          @click="resetEmploymentPreferences">
          <i class="fas fa-undo mr-2"></i> Reset Preferences
        </button>
      </div>
    </div>
    <!-- Add Location Modal -->
    <div v-if="isAddLocationModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">Add Location</h2>
          <button class="text-gray-500 hover:text-gray-700" @click="closeAddLocationModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <p class="text-gray-600 mb-4">Add a preferred location for employment</p>
        <form @submit.prevent="addPreferredLocation">
          <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Location <span class="text-red-500">*</span></label>
            <input type="text" v-model="newLocation"
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
              placeholder="e.g. New York, NY" required />
          </div>
          <div class="flex justify-end">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Add</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>