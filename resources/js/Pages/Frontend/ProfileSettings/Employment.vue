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
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Datepicker from 'vue3-datepicker';
import { isValid } from 'date-fns';
import '@fortawesome/fontawesome-free/css/all.css';


// Define props (accept either defaultLocations or locations from controller)
const props = defineProps({
  activeSection: {
    type: String,
    default: 'employment'
  },
  employmentPreferences: {
    type: Object,
    default: () => null
  },
  // controller may pass defaultLocations or locations — accept both
  defaultLocations: {
    type: Array,
    default: () => []
  },
  locations: {
    type: Array,
    default: () => []
  }
});

// prefer defaultLocations, fallback to locations
const allDefaultLocations = computed(() => {
  if (props.defaultLocations?.length) return props.defaultLocations
  if (props.locations?.length) return props.locations
  return []
});

const emit = defineEmits(['close-all-modals', 'reset-all-states']);

// Helper to convert mixed value to array
function toArray(val) {
  if (!val) return []
  if (Array.isArray(val)) return val.filter(v => v !== null && v !== '').map(v => String(v).trim())
  if (typeof val === 'string') {
    const trimmed = val.trim()
    if (!trimmed) return []
    // Try JSON
    try {
      const parsed = JSON.parse(trimmed)
      if (Array.isArray(parsed)) return parsed.map(v => String(v).trim())
    } catch(e){}
    return trimmed.split(',').map(s => s.trim()).filter(Boolean)
  }
  return []
}

// Normalize raw employment preference object coming from server
function normalizeEP(epRaw) {
  if (!epRaw || typeof epRaw !== 'object') {
    return {
      jobTypes: [],
      salaryExpectations: { min: '', max: '', frequency: 'monthly' },
      preferredLocations: [],
      workEnvironment: [],
      additionalNotes: ''
    }
  }
  // Prefer array fields; fallback to *_string
  const jobTypes = toArray(epRaw.job_type).length
    ? toArray(epRaw.job_type)
    : toArray(epRaw.job_type_string)

  const locationsArr = toArray(epRaw.location).length
    ? toArray(epRaw.location)
    : toArray(epRaw.location_string)

  const workEnv = toArray(epRaw.work_environment).length
    ? toArray(epRaw.work_environment)
    : toArray(epRaw.work_environment_string)

  return {
    jobTypes,
    salaryExpectations: {
      min: epRaw.employment_min_salary || '',
      max: epRaw.employment_max_salary || '',
      frequency: epRaw.salary_type || 'monthly'
    },
    preferredLocations: locationsArr,
    workEnvironment: workEnv,
    additionalNotes: epRaw.additional_notes || ''
  }
}

const employmentPreferences = ref(normalizeEP(props.employmentPreferences))
const tempEmploymentPreferences = ref(JSON.parse(JSON.stringify(employmentPreferences.value)))

// Form (payload) stays arrays already
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

// Save handler
const saveEmploymentPreferences = () => {
  employmentForm.job_types = [...tempEmploymentPreferences.value.jobTypes]
  employmentForm.employment_min_salary = tempEmploymentPreferences.value.salaryExpectations.min
  employmentForm.employment_max_salary = tempEmploymentPreferences.value.salaryExpectations.max
  employmentForm.salary_type = tempEmploymentPreferences.value.salaryExpectations.frequency
  employmentForm.preferred_locations = [...tempEmploymentPreferences.value.preferredLocations]
  employmentForm.work_environment = [...tempEmploymentPreferences.value.workEnvironment]
  employmentForm.additional_notes = tempEmploymentPreferences.value.additionalNotes

  employmentForm.post(route('employment.preferences.updateEmploymentPreferences'), {
    preserveScroll: true,
    onSuccess: () => {
      employmentPreferences.value = JSON.parse(JSON.stringify(tempEmploymentPreferences.value))
      successMessage.value = "Employment preferences saved successfully!"
      isSuccessModalOpen.value = true
      closeEditModal()
      // No manual reload needed if response returns updated props
    },
    onError: (errors) => {
      console.error('Error saving employment preferences:', errors)
      errorMessage.value = "An error occurred while saving the employment preferences."
      isErrorModalOpen.value = true
    },
  })
};

const toggleJobType = (type) => {
  const idx = employmentPreferences.value.jobTypes.indexOf(type);
  if (idx === -1) {
    employmentPreferences.value.jobTypes.push(type);
  } else {
    employmentPreferences.value.jobTypes.splice(idx, 1);
  }
};

// New-location input
const newLocation = ref('');

// Autocomplete / suggestions for default locations (used in Add Location modal)
const showLocationSuggestions = ref(false);
const filteredLocationSuggestions = computed(() => {
  const q = (newLocation.value || '').toString().toLowerCase().trim();
  const all = allDefaultLocations.value || [];
  if (!q) return all.slice(0, 8);
  return all
    .filter(l => {
      const text = (l.address || l.name || '').toString().toLowerCase();
      return text.includes(q);
    })
    .slice(0, 8);
});

function onNewLocationFocus() {
  if (!allDefaultLocations.value?.length) return;
  showLocationSuggestions.value = true;
}
function onNewLocationInput() {
  if (!allDefaultLocations.value?.length) {
    showLocationSuggestions.value = false;
    return;
  }
  showLocationSuggestions.value = true;
}
function onNewLocationBlur() {
  // Delay hiding so click can register on suggestion
  setTimeout(() => { showLocationSuggestions.value = false }, 120);
}
function selectLocationSuggestion(address) {
  newLocation.value = address;
  showLocationSuggestions.value = false;
}

// Add preferred location into TEMP (used by modal submit)
const addPreferredLocationToTemp = () => {
  const val = (newLocation.value || '').toString().trim();
  if (!val) {
    errorMessage.value = "Please enter a valid location.";
    isErrorModalOpen.value = true;
    return;
  }

  if (!tempEmploymentPreferences.value.preferredLocations.includes(val)) {
    tempEmploymentPreferences.value.preferredLocations.push(val);
    closeAddLocationModal();
  } else {
    errorMessage.value = "This location is already in your preferences.";
    isErrorModalOpen.value = true;
  }
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
  showLocationSuggestions.value = false;
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

// keep reactive when parent prop changes
watch(
  () => props.employmentPreferences,
  (newVal) => {
    employmentPreferences.value = normalizeEP(newVal)
    if (!isEditModalOpen.value) {
      tempEmploymentPreferences.value = JSON.parse(JSON.stringify(employmentPreferences.value))
    }
  },
  { immediate: true, deep: true }
)

// (Optional) debug
console.debug('[Employment.vue] EP parsed:', employmentPreferences.value)

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
        <!-- Success / Error Modals omitted here for brevity (unchanged) -->

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

        <!-- Edit Employment Preferences Modal -->
        <Modal :modelValue="isEditModalOpen" @close="closeEditModal" max-width="4xl">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-bold text-gray-900">Edit Employment Preferences</h2>
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
            <div class="relative">
              <input
                type="text"
                v-model="newLocation"
                @focus="onNewLocationFocus"
                @input="onNewLocationInput"
                @blur="onNewLocationBlur"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-500"
                placeholder="Type or pick a location" required
              />

              <!-- Suggestions dropdown -->
              <ul
                v-if="showLocationSuggestions && filteredLocationSuggestions.length"
                class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-auto text-sm"
              >
                <li
                  v-for="loc in filteredLocationSuggestions"
                  :key="loc.id"
                  class="px-3 py-2 hover:bg-indigo-50 cursor-pointer flex items-start gap-2"
                  @mousedown.prevent="selectLocationSuggestion(loc.address || loc.name)"
                >
                  <i class="fas fa-map-marker-alt text-indigo-500 mt-0.5 text-xs"></i>
                  <span class="text-gray-700">{{ loc.address || loc.name }}</span>
                </li>
                <li
                  v-if="newLocation && !filteredLocationSuggestions.some(l => (l.address || l.name) === newLocation)"
                  class="px-3 py-2 text-gray-500 italic text-xs"
                >
                  Use custom location: "{{ newLocation }}"
                </li>
              </ul>
            </div>
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