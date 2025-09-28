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
import '@fortawesome/fontawesome-free/css/all.css';


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
  job_types: [],
  employment_min_salary: '',
  employment_max_salary: '',
  salary_type: 'monthly',
  preferred_locations: [],
  work_environment: [],
  additional_notes: '',
});

const isAddLocationModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const successMessage = ref('');
const errorMessage = ref('');


// Temporary form data for editing
const tempEmploymentPreferences = ref({
  jobTypes: [],
  salaryExpectations: {
    min: null,
    max: null,
    frequency: 'monthly'
  },
  preferredLocations: [],
  workEnvironment: [],
  additionalNotes: ''
});

// Employment Preferences Handlers
const saveEmploymentPreferences = () => {
  employmentForm.job_types = [...tempEmploymentPreferences.value.jobTypes]; // array
  employmentForm.employment_min_salary = tempEmploymentPreferences.value.salaryExpectations.min;
  employmentForm.employment_max_salary = tempEmploymentPreferences.value.salaryExpectations.max;
  employmentForm.salary_type = tempEmploymentPreferences.value.salaryExpectations.frequency;
  employmentForm.preferred_locations = [...tempEmploymentPreferences.value.preferredLocations]; // array
  employmentForm.work_environment = [...tempEmploymentPreferences.value.workEnvironment];
  employmentForm.additional_notes = tempEmploymentPreferences.value.additionalNotes;

  employmentForm.post(route('employment.preferences.updateEmploymentPreferences'), {
    onSuccess: () => {
      // Update main data with temp data
      employmentPreferences.value = JSON.parse(JSON.stringify(tempEmploymentPreferences.value));
      successMessage.value = "Employment preferences saved successfully!";
      isSuccessModalOpen.value = true;
      closeEditModal();
      emit('close-all-modals');
      router.reload({ only: ['employmentReference'] });
    },
    onError: (errors) => {
      console.error('Error saving employment preferences:', errors);
      errorMessage.value = "An error occurred while saving the employment preferences. Please try again.";
      isErrorModalOpen.value = true;
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

// Modal handlers
const openEditModal = () => {
  // Copy current data to temp for editing
  tempEmploymentPreferences.value = JSON.parse(JSON.stringify(employmentPreferences.value));
  isEditModalOpen.value = true;
};

const closeEditModal = () => {
  isEditModalOpen.value = false;
};

const closeSuccessModal = () => {
  isSuccessModalOpen.value = false;
};

const closeErrorModal = () => {
  isErrorModalOpen.value = false;
};

const closeAddLocationModal = () => {
  isAddLocationModalOpen.value = false;
  newLocation.value = '';
  console.log('Add location modal closed.');
};

// Job type toggle for temp data
const toggleJobTypeTemp = (type) => {
  const idx = tempEmploymentPreferences.value.jobTypes.indexOf(type);
  if (idx === -1) {
    tempEmploymentPreferences.value.jobTypes.push(type);
  } else {
    tempEmploymentPreferences.value.jobTypes.splice(idx, 1);
  }
};

// Add preferred location to temp data
const addPreferredLocationToTemp = () => {
  if (!newLocation.value.trim()) {
    errorMessage.value = "Please enter a valid location.";
    isErrorModalOpen.value = true;
    return;
  }

  if (!tempEmploymentPreferences.value.preferredLocations.includes(newLocation.value.trim())) {
    tempEmploymentPreferences.value.preferredLocations.push(newLocation.value.trim());
    closeAddLocationModal();
  } else {
    errorMessage.value = "This location is already in your preferences.";
    isErrorModalOpen.value = true;
  }
};

// Remove preferred location from temp data
const removePreferredLocationFromTemp = (location) => {
  const index = tempEmploymentPreferences.value.preferredLocations.indexOf(location);
  if (index > -1) {
    tempEmploymentPreferences.value.preferredLocations.splice(index, 1);
  }
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
  <div v-if="activeSection === 'employment'">
    <div class="w-full">
      <!-- Employment Preferences Header -->
      <div
        class="flex justify-between items-center p-4 bg-gradient-to-r from-blue-50 to-white border border-blue-100 rounded-lg mb-4">
        <div class="flex items-center">
          <div class="bg-blue-100 p-2 rounded-full mr-3">
            <i class="fas fa-briefcase text-blue-600"></i>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-blue-800">Employment Preferences</h3>
            <p class="text-sm text-gray-600">Set your job preferences and requirements</p>
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <button @click="openEditModal"
            class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-sm">
            <i class="fas fa-edit mr-1"></i>Edit
          </button>
        </div>
      </div>

      <!-- Employment Preferences Content -->
      <div class="transition-all duration-300">

        <!-- Success Modal -->
        <Modal :modelValue="isSuccessModalOpen" @close="closeSuccessModal">
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
                class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                OK
              </button>
            </div>
          </div>
        </Modal>

        <!-- Error Modal -->
        <Modal :modelValue="isErrorModalOpen" @close="closeErrorModal">
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
                class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                OK
              </button>
            </div>
          </div>
        </Modal>

        <!-- Display Employment Preferences -->
        <div
          v-if="employmentPreferences.jobTypes.length || employmentPreferences.salaryExpectations.min || employmentPreferences.preferredLocations.length || employmentPreferences.workEnvironment.length || employmentPreferences.additionalNotes"
          class="mb-6 p-5 border border-blue-100 rounded-lg bg-white shadow-md hover:shadow-lg transition-all duration-300">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-blue-800 border-b border-blue-100 pb-2">Employment Preferences</h2>
            <PrimaryButton @click="openEditModal" class="flex items-center">
              <i class="fas fa-edit mr-2"></i> Edit Preferences
            </PrimaryButton>
          </div>
          <div class="space-y-4">
            <div class="bg-blue-50 p-3 rounded-md">
              <p class="flex items-start">
                <span class="bg-blue-100 text-blue-800 p-1 rounded mr-2 inline-block">
                  <i class="fas fa-briefcase"></i>
                </span>
                <span>
                  <strong class="text-blue-800">Job Types:</strong><br>
                  <span v-if="employmentPreferences.jobTypes.length" class="text-gray-700">
                    <span v-for="(type, index) in employmentPreferences.jobTypes" :key="index"
                      class="inline-block bg-white px-2 py-1 rounded-full border border-blue-200 text-sm mr-1 mb-1">
                      {{ type }}
                    </span>
                  </span>
                  <span v-else class="text-gray-500 italic">No job types specified</span>
                </span>
              </p>
            </div>

            <div class="bg-blue-50 p-3 rounded-md">
              <p class="flex items-start">
                <span class="bg-blue-100 text-blue-800 p-1 rounded mr-2 inline-block">
                  <i class="fas fa-money-bill-wave"></i>
                </span>
                <span>
                  <strong class="text-blue-800">Salary Expectations:</strong><br>
                  <span
                    v-if="employmentPreferences.salaryExpectations.min && employmentPreferences.salaryExpectations.max"
                    class="text-gray-700">
                    <span class="bg-white px-3 py-1 rounded-md border border-blue-200 inline-block">
                      {{ employmentPreferences.salaryExpectations.min }} - {{
                        employmentPreferences.salaryExpectations.max }}
                      pesos
                      {{ employmentPreferences.salaryExpectations.frequency === 'monthly' ? ', Monthly' : ', Hourly' }}
                    </span>
                  </span>
                  <span v-else class="text-gray-500 italic">No salary expectations specified</span>
                </span>
              </p>
            </div>

            <div class="bg-blue-50 p-3 rounded-md">
              <p class="flex items-start">
                <span class="bg-blue-100 text-blue-800 p-1 rounded mr-2 inline-block">
                  <i class="fas fa-map-marker-alt"></i>
                </span>
                <span>
                  <strong class="text-blue-800">Preferred Locations:</strong><br>
                  <span v-if="employmentPreferences.preferredLocations.length" class="text-gray-700">
                    <span v-for="(location, index) in employmentPreferences.preferredLocations" :key="index"
                      class="inline-block bg-white px-2 py-1 rounded-full border border-blue-200 text-sm mr-1 mb-1">
                      {{ location }}
                    </span>
                  </span>
                  <span v-else class="text-gray-500 italic">No preferred locations specified</span>
                </span>
              </p>
            </div>

            <div class="bg-blue-50 p-3 rounded-md">
              <p class="flex items-start">
                <span class="bg-blue-100 text-blue-800 p-1 rounded mr-2 inline-block">
                  <i class="fas fa-building"></i>
                </span>
                <span>
                  <strong class="text-blue-800">Work Environment:</strong><br>
                  <span v-if="employmentPreferences.workEnvironment.length" class="text-gray-700">
                    <span v-for="(env, index) in employmentPreferences.workEnvironment" :key="index"
                      class="inline-block bg-white px-2 py-1 rounded-full border border-blue-200 text-sm mr-1 mb-1">
                      {{ env }}
                    </span>
                  </span>
                  <span v-else class="text-gray-500 italic">No work environment preferences specified</span>
                </span>
              </p>
            </div>

            <div class="bg-blue-50 p-3 rounded-md">
              <p class="flex items-start">
                <span class="bg-blue-100 text-blue-800 p-1 rounded mr-2 inline-block">
                  <i class="fas fa-sticky-note"></i>
                </span>
                <span>
                  <strong class="text-blue-800">Additional Notes:</strong><br>
                  <span class="text-gray-700">{{ employmentPreferences.additionalNotes || 'No additional notes provided'
                    }}</span>
                </span>
              </p>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="mb-6 p-8 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50 text-center">
          <div class="mb-4">
            <i class="fas fa-briefcase text-4xl text-gray-400"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No Employment Preferences Set</h3>
          <p class="text-gray-600 mb-4">Set your employment preferences to help employers find you.</p>
          <PrimaryButton @click="openEditModal" class="flex items-center mx-auto">
            <i class="fas fa-plus mr-2"></i> Set Employment Preferences
          </PrimaryButton>
        </div>

        <!-- Success Modal -->
        <Modal :modelValue="isSuccessModalOpen" @close="closeSuccessModal">
          <div class="p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-green-100 rounded-full">
              <i class="fas fa-check text-green-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Success!</h3>
            <p class="text-center text-gray-600 mb-6">{{ successMessage }}</p>
            <div class="flex justify-center">
              <PrimaryButton @click="closeSuccessModal"
                class="bg-green-600 hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:ring-green-500">
                Close
              </PrimaryButton>
            </div>
          </div>
        </Modal>

        <!-- Error Modal -->
        <Modal :modelValue="isErrorModalOpen" @close="closeErrorModal">
          <div class="p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
              <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Error</h3>
            <p class="text-center text-gray-600 mb-6">{{ errorMessage }}</p>
            <div class="flex justify-center">
              <DangerButton @click="closeErrorModal">
                Close
              </DangerButton>
            </div>
          </div>
        </Modal>

        <!-- Edit Employment Preferences Modal -->
        <Modal :modelValue="isEditModalOpen" @close="closeEditModal" max-width="4xl">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-bold text-gray-900">Edit Employment Preferences</h2>
              <!-- <button @click="closeEditModal" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
              </button> -->
            </div>

            <div class="space-y-6">
              <!-- Job Types -->
              <div>
                <h3 class="text-lg font-semibold mb-3 text-gray-800">Job Types</h3>
                <div class="flex flex-wrap gap-2">
                  <button v-for="type in ['Full-time', 'Part-time', 'Contract', 'Freelance', 'Internship']" :key="type"
                    :class="tempEmploymentPreferences.jobTypes.includes(type) ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-4 py-2 rounded-full hover:bg-blue-700 transition-colors duration-200"
                    @click="toggleJobTypeTemp(type)">
                    {{ type }}
                  </button>
                </div>
              </div>

              <!-- Salary Expectations -->
              <div>
                <h3 class="text-lg font-semibold mb-3 text-gray-800">Salary Expectations</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Minimum</label>
                    <input type="number" v-model="tempEmploymentPreferences.salaryExpectations.min" placeholder="Min"
                      class="w-full border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-blue-600 focus:border-blue-500"
                      min="0" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Maximum</label>
                    <input type="number" v-model="tempEmploymentPreferences.salaryExpectations.max" placeholder="Max"
                      class="w-full border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-blue-600 focus:border-blue-500"
                      min="0" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select v-model="tempEmploymentPreferences.salaryExpectations.frequency"
                      class="w-full border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-blue-600 focus:border-blue-500">
                      <option value="monthly">Monthly</option>
                      <option value="hourly">Hourly</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Preferred Locations -->
              <div>
                <h3 class="text-lg font-semibold mb-3 text-gray-800">Preferred Locations</h3>
                <div class="flex flex-wrap gap-2 mb-2">
                  <span v-for="location in tempEmploymentPreferences.preferredLocations" :key="location"
                    class="bg-blue-50 text-blue-700 px-4 py-2 rounded-full border border-blue-100 flex items-center">
                    {{ location }}
                    <button @click="removePreferredLocationFromTemp(location)"
                      class="ml-2 text-red-500 hover:text-red-700">
                      &times;
                    </button>
                  </span>
                  <PrimaryButton class="rounded-full" @click="isAddLocationModalOpen = true">
                    + Add Location
                  </PrimaryButton>
                </div>
              </div>

              <!-- Work Environment -->
              <div>
                <h3 class="text-lg font-semibold mb-3 text-gray-800">Work Environment</h3>
                <div class="flex flex-wrap gap-8">
                  <label class="flex items-center">
                    <input type="checkbox" value="Remote" v-model="tempEmploymentPreferences.workEnvironment"
                      class="form-checkbox text-blue-600 focus:ring-blue-600" />
                    <span class="ml-2 text-gray-700">Remote</span>
                  </label>
                  <label class="flex items-center">
                    <input type="checkbox" value="Hybrid" v-model="tempEmploymentPreferences.workEnvironment"
                      class="form-checkbox text-blue-600 focus:ring-blue-600" />
                    <span class="ml-2 text-gray-700">Hybrid</span>
                  </label>
                  <label class="flex items-center">
                    <input type="checkbox" value="On-site" v-model="tempEmploymentPreferences.workEnvironment"
                      class="form-checkbox text-blue-600 focus:ring-blue-600" />
                    <span class="ml-2 text-gray-700">On-site</span>
                  </label>
                </div>
              </div>

              <!-- Additional Notes -->
              <div>
                <h3 class="text-lg font-semibold mb-3 text-gray-800">Additional Notes</h3>
                <textarea v-model="tempEmploymentPreferences.additionalNotes"
                  class="w-full border border-gray-300 rounded px-4 py-2 focus:ring-2 focus:ring-blue-600 focus:border-blue-500"
                  rows="4" placeholder="Any other preferences or requirements..."></textarea>
              </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
              <SecondaryButton @click="closeEditModal">
                Cancel
              </SecondaryButton>
              <PrimaryButton @click="saveEmploymentPreferences" class="flex items-center">
                <i class="fas fa-save mr-2"></i> Save Preferences
              </PrimaryButton>
            </div>
          </div>
        </Modal>
      </div> <!-- End Collapsible Content -->
    </div>

    <!-- Add Location Modal -->
    <Modal :modelValue="isAddLocationModalOpen" @close="closeAddLocationModal" max-width="md">
      <div class="p-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold text-gray-800">Add Location</h2>
          <button class="text-gray-500 hover:text-gray-700" @click="closeAddLocationModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <p class="text-gray-600 mb-4">Add a preferred location for employment</p>
        <form @submit.prevent="addPreferredLocationToTemp">
          <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Location <span class="text-red-500">*</span></label>
            <input type="text" v-model="newLocation"
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-500"
              placeholder="e.g. New York, NY" required />
          </div>
          <div class="flex justify-end">
            <PrimaryButton type="submit">Add</PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>

  </div>
</template>

<style scoped>
/* Glow effect animations */
@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.5);
  }

  70% {
    box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
  }

  100% {
    box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Input focus effects with glow */
input:focus,
textarea:focus,
select:focus {
  animation: pulse 1.5s ease-in-out;
  transition: all 0.3s ease;
}

/* Button hover effects */
button:not(:disabled) {
  transition: all 0.3s ease;
}

button:not(:disabled):hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Form element transitions */
form div {
  animation: fadeIn 0.3s ease-out;
}
</style>