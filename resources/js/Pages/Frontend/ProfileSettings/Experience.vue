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
    default: 'experience'
  },

  experienceEntries: Array,
});
const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const isDuplicateModalOpen = ref(false);
const emit = defineEmits(['close-all-modals', 'reset-all-states']);
const experienceForm = useForm({
  graduate_experience_title: '',
  graduate_experience_company: '',
  graduate_experience_start_date: null,
  graduate_experience_end_date: null,
  graduate_experience_address: '',
  graduate_experience_description: '',
  graduate_experience_employment_type: '',
  is_current: false,
});

console.log(props.experienceEntries);
const datepickerConfig = {
  format: 'YYYY-MM-DD',
  enableTime: false,
  parseDate: (dateStr) => {
    if (!dateStr) return null;
    const date = new Date(dateStr);
    return isNaN(date.getTime()) ? null : date;
  },
  formatDate: (date) => {
    if (!date || isNaN(date.getTime())) return '';
    return date.toISOString().split('T')[0];
  }
};

const formatDate = (date) => {
  if (!date) return '';
  const parsedDate = new Date(date);
  if (isNaN(parsedDate.getTime())) return '';
  parsedDate.setHours(0, 0, 0, 0);
  return parsedDate.toISOString().split('T')[0];
};

const formatDisplayDate = (date) => {
  if (!date) return '';
  const d = new Date(date);
  if (!isValid(d)) return '';
  try {
    return d.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  } catch (error) {
    console.error('Date formatting error:', error);
    return '';
  }
};

// State Management
const stillInRole = ref(false);
const isDeleteConfirmationModalOpen = ref(false);
const itemToDelete = ref(null);


// Experience Data
const experienceEntries = computed(() => props.experienceEntries || []);
const archivedExperienceEntries = computed(() => props.archivedExperienceEntries || []);
const isAddExperienceModalOpen = ref(false);
const isUpdateExperienceModalOpen = ref(false);
const experience = ref({
  title: '',
  company: '',
  start_date: null,
  end_date: null,
  address: '',
  description: '',
  employment_type: '',
  is_current: false,
  id: null
});

const openAddExperienceModal = () => {
  console.log('openAddExperienceModal called');
  resetExperience();
  isAddExperienceModalOpen.value = true;
};

const closeAddExperienceModal = () => {
  isAddExperienceModalOpen.value = false;
  resetExperience();
};

const resetExperience = () => {
  experience.value = {
    title: '',
    company: '',
    start_date: null,
    end_date: null,
    address: '',
    description: '',
    employment_type: '',
    is_current: false,
    id: null
  };
  stillInRole.value = false;
  console.log('Experience reset.');
};




const openUpdateExperienceModal = (experienceEntry) => {
  resetExperience();
  experience.value = {
    id: experienceEntry.id,
    title: experienceEntry.title,
    company: experienceEntry.company,
    start_date: experienceEntry.start_date ? formatDate(experienceEntry.start_date) : null,
    end_date: experienceEntry.is_current ? null : (experienceEntry.end_date ? formatDate(experienceEntry.end_date) : null),
    address: experienceEntry.address || '',
    description: experienceEntry.description || 'No description provided',
    employment_type: experienceEntry.employment_type || '',
    is_current: experienceEntry.is_current
  };
  stillInRole.value = experienceEntry.is_current;
  isUpdateExperienceModalOpen.value = true;
};

const addExperience = () => {
  // Log the form data for debugging
  console.log('Submitting experience form:', { ...experienceForm });

  experienceForm.post(route('profile.experience.add'), {
    onSuccess: (response) => {
      emit('close-all-modals');      // Optionally update local experience entries if needed
      if (response?.props?.experienceEntries) {
        experienceEntries.value = response.props.experienceEntries;
      }
      // Reset the form fields
      experienceForm.reset();
      // Close the modal
      isAddExperienceModalOpen.value = false;
      // Optionally show a success message/modal
      isSuccessModalOpen.value = true;
    },
    onError: (errors) => {
      // Log errors for debugging
      console.error('Error adding experience:', errors);
      // Optionally show an error message/modal
      isErrorModalOpen.value = true;
      // Or use alert for quick feedback
      alert('An error occurred while adding the experience. Please check the form and try again.');
    },
  });
};

const updateExperience = () => {
  experienceForm.graduate_experience_title = experience.value.title?.trim() || '';
  experienceForm.graduate_experience_company = experience.value.company?.trim() || '';
  experienceForm.graduate_experience_start_date = formatDate(experience.value.start_date);
  experienceForm.graduate_experience_end_date = stillInRole.value ? null : formatDate(experience.value.end_date);
  experienceForm.graduate_experience_address = experience.value.address?.trim() || '';
  experienceForm.graduate_experience_description = experience.value.description?.trim() || 'No description provided';
  experienceForm.graduate_experience_employment_type = experience.value.employment_type?.trim() || '';
  experienceForm.is_current = stillInRole.value;

  experienceForm.put(route('profile.experience.update', experience.value.id), {
    onSuccess: () => {
      resetExperience();
      isUpdateExperienceModalOpen.value = false;
    },
    onError: (errors) => {
      console.error('Error updating experience:', errors);
    },
  });
};

const closeUpdateExperienceModal = () => {
  isUpdateExperienceModalOpen.value = false;
  resetExperience();
};

const deleteExperience = (experienceId) => {
  itemToDelete.value = { id: experienceId, type: 'experience' };
  isDeleteConfirmationModalOpen.value = true;
};

const formattedExperienceStartDate = computed(() => {
  return experience.start_date
    ? new Date(experience.start_date).toISOString().split('T')[0]
    : null;
});

const formattedExperienceEndDate = computed(() => {
  if (experience.is_current) return null;
  return experience.end_date
    ? new Date(experience.end_date).toISOString().split('T')[0]
    : null;
});

watch(() => experience.is_current, (newValue) => {
  stillInRole.value = newValue;
  if (newValue) {
    experience.end_date = null;
  } else {
    experience.end_date = '';
  }
}, { immediate: true });

watch(() => experience.end_date, (newValue) => {
  if (newValue && isNaN(new Date(newValue).getTime())) {
    experience.end_date = null;
  }
});


// Modal State
let currentExperienceIndex = ref(null);

const removeExperience = (experience) => {
  if (confirm(`Are you sure you want to remove this experience entry: ${experience.title}?`)) {
    deleteForm.delete(route('experience.remove', experience.id), {
      onSuccess: () => {
        const index = experienceEntries.findIndex(e => e.id === experience.id);
        if (index !== -1) {
          experienceEntries.splice(index, 1);
        }
      }
    });
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
</script>

<template>
  <!-- Success Modal -->
  <Modal :show="isSuccessModalOpen" @close="isSuccessModalOpen = false">
    <div class="p-6">
      <div class="flex items-center justify-center mb-4 bg-green-100 rounded-full w-12 h-12 mx-auto">
        <i class="fas fa-check text-green-500 text-xl"></i>
      </div>
      <h2 class="text-lg font-medium text-center text-gray-900 mb-2">Success</h2>
      <p class="text-center text-gray-600">{{ successMessage }}</p>
      <div class="mt-6 flex justify-center">
        <PrimaryButton @click="isSuccessModalOpen = false">OK</PrimaryButton>
      </div>
    </div>
  </Modal>

  <!-- Error Modal -->
  <Modal :show="isErrorModalOpen" @close="isErrorModalOpen = false">
    <div class="p-6">
      <div class="flex items-center justify-center mb-4 bg-red-100 rounded-full w-12 h-12 mx-auto">
        <i class="fas fa-times text-red-500 text-xl"></i>
      </div>
      <h2 class="text-lg font-medium text-center text-gray-900 mb-2">Error</h2>
      <p class="text-center text-gray-600">{{ errorMessage }}</p>
      <div class="mt-6 flex justify-center">
        <PrimaryButton @click="isErrorModalOpen = false">OK</PrimaryButton>
      </div>
    </div>
  </Modal>

  <!-- Duplicate Modal -->
  <Modal :show="isDuplicateModalOpen" @close="isDuplicateModalOpen = false">
    <div class="p-6">
      <div class="flex items-center justify-center mb-4 bg-yellow-100 rounded-full w-12 h-12 mx-auto">
        <i class="fas fa-exclamation-triangle text-yellow-500 text-xl"></i>
      </div>
      <h2 class="text-lg font-medium text-center text-gray-900 mb-2">Duplicate Entry</h2>
      <p class="text-center text-gray-600">This experience already exists in your profile.</p>
      <div class="mt-6 flex justify-center">
        <PrimaryButton @click="isDuplicateModalOpen = false">OK</PrimaryButton>
      </div>
    </div>
  </Modal>



  <div v-if="activeSection === 'experience'" class="flex flex-col lg:flex-row">
    <div class="w-full mb-6">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold mb-4">Work Experience</h1>
        <div class="flex space-x-4">
          <PrimaryButton class="bg-indigo-600 text-white px-4 py-2 rounded flex items-center"
            @click="openAddExperienceModal">
            <i class="fas fa-plus mr-2"></i>
            Add Experience
          </PrimaryButton>
          <button
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded flex items-center transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 cursor-pointer"
            @click="toggleArchivedExperience">
            <i class="fas" :class="showArchivedExperience ? 'fa-eye-slash' : 'fa-eye'"></i>
            <span class="ml-2">{{ showArchivedExperience ? 'Hide Archived' : 'Show Archived' }}</span>
          </button>
        </div>
      </div>
      <p class="text-gray-600 mb-6">Showcase your professional experience</p>

      <!-- Experience Entries -->
      <div>
        <h2 class="text-lg font-medium mb-4">Active Experience</h2>
        <div v-if="experienceEntries.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div v-for="experienceEntry in experienceEntries" :key="experienceEntry.id"
            class="bg-white rounded-lg shadow-md p-4 space-y-3 relative">
            <div>
              <div class="border-b pb-2">
                <h2 class="text-xl font-bold">{{ experienceEntry.title }}</h2>
                <p class="text-gray-600">{{ experienceEntry.company }}</p>
              </div>
              <div class="flex items-center text-gray-600 mt-2">
                <i class="fas fa-map-marker-alt mr-2"></i>
                <span>{{ experienceEntry.address }}</span>
              </div>
              <div class="flex items-center text-gray-600 mt-2">
                <i class="far fa-calendar-alt mr-2"></i>
                <span>
                  {{ formatDate(experienceEntry.start_date) }} - {{ experienceEntry.is_current ? 'Present' :
                    formatDate(experienceEntry.end_date) }}
                </span>
              </div>
              <p class="text-gray-600 mt-2 flex items-center">
                <i class="fas fa-briefcase text-gray-500 mr-2"></i>
                <strong>Employment Type:</strong> {{ experienceEntry.employment_type }}
              </p>
              <p class="mt-2">
                <strong>
                  <i class="fas fa-info-circle text-gray-500 mr-2"></i> Description:
                </strong>
                {{ experienceEntry.description || 'No description provided' }}
              </p>
            </div>
            <div class="absolute top-2 right-2 flex space-x-2">
              <button class="text-gray-600 hover:text-indigo-600" @click="openUpdateExperienceModal(experienceEntry)">
                <i class="fas fa-pen"></i>
              </button>
              <button class="text-amber-600 hover:text-amber-800" @click="archiveExperience(experienceEntry)">
                <i class="fas fa-archive"></i>
              </button>
              <button class="text-red-600 hover:text-red-800" @click="deleteExperience(experienceEntry.id)">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- If no experience entries exist -->
        <div v-else class="bg-white p-8 rounded-lg shadow">
          <p class="text-gray-600">No experience entries added yet.</p>
        </div>
      </div>

      <!-- Archived Experience Entries -->
      <div v-if="showArchivedExperience" class="mt-8">
        <h2 class="text-lg font-medium mb-4">Archived Experience</h2>
        <div v-if="archivedExperienceEntries.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div v-for="experienceEntry in archivedExperienceEntries" :key="experienceEntry.id"
            class="bg-gray-50 p-4 rounded-lg shadow-md space-y-3 relative border border-gray-200">
            <div class="opacity-75">
              <div class="border-b pb-2">
                <h2 class="text-xl font-bold">{{ experienceEntry.title }}</h2>
                <p class="text-gray-600">
                  {{ experienceEntry.company }}
                </p>
              </div>
              <div class="flex items-center text-gray-600 mt-2">
                <i class="fas fa-map-marker-alt mr-2"></i>
                <span>
                  {{ experienceEntry.address }}
                </span>
              </div>
              <div class="flex items-center text-gray-600 mt-2">
                <i class="far fa-calendar-alt mr-2"></i>
                <span>
                  {{ formatDate(experienceEntry.start_date) }} - {{ experienceEntry.is_current ? 'Present' :
                    formatDate(experienceEntry.end_date) }}
                </span>
              </div>
              <p class="text-gray-600 mt-2 flex items-center">
                <i class="fas fa-briefcase text-gray-500 mr-2"></i>
                <strong>Employment Type:</strong> {{ experienceEntry.employment_type }}
              </p>
              <p class="mt-2">
                <strong>
                  <i class="fas fa-info-circle text-gray-500 mr-2"></i> Description:
                </strong>
                {{ experienceEntry.description || 'No description provided' }}
              </p>
            </div>
            <div class="absolute top-2 right-2 flex space-x-2">
              <button class="text-green-600 hover:text-green-800" @click="unarchiveExperience(experienceEntry)">
                <i class="fas fa-box-open"></i>
              </button>
              <button class="text-red-600 hover:text-red-800" @click="deleteExperience(experienceEntry.id)">
                <i class="fas fa-trash"></i>
              </button>
            </div>
            <div class="absolute top-2 left-2 bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded">
              Archived
            </div>
          </div>
        </div>

        <!-- If no archived experience entries exist -->
        <div v-else class="bg-white p-8 rounded-lg shadow">
          <p class="text-gray-600">No archived experience entries found.</p>
        </div>
      </div>
    </div>


    <!-- Add Experience Modal -->
    <div v-if="isAddExperienceModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">Add Experience</h2>
          <button class="text-gray-500 hover:text-gray-700" @click="closeAddExperienceModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="max-h-96 overflow-y-auto">
          <form @submit.prevent="addExperience">
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Job Title <span class="text-red-500">*</span></label>
              <input type="text" v-model="experienceForm.graduate_experience_title"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                placeholder="e.g. Software Engineer" required />
              <InputError :message="experienceForm.errors.graduate_experience_title" class="mt-2" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium  mb-2">Company <span class="text-red-500">*</span></label>
              <input type="text" v-model="experienceForm.graduate_experience_company"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                placeholder="e.g. Tech Corp" required />
              <InputError :message="experienceForm.errors.graduate_experience_company" class="mt-2" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Location <span class="text-red-500">*</span></label>
              <input type="text" v-model="experienceForm.graduate_experience_address"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                placeholder="e.g. New York, NY" required />
              <InputError :message="experienceForm.errors.graduate_experience_address" class="mt-2" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Employment Type <span
                  class="text-red-500">*</span></label>
              <select v-model="experienceForm.graduate_experience_employment_type"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                required>
                <option value="" disabled>Select employment type</option>
                <option value="Full-time">Full-time</option>
                <option value="Part-time">Part-time</option>
                <option value="Contract">Contract</option>
                <option value="Freelance">Freelance</option>
                <option value="Internship">Internship</option>
              </select>
              <InputError :message="experienceForm.errors.graduate_experience_employment_type" class="mt-2" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Start Date <span class="text-red-500">*</span></label>
              <Datepicker v-model="experienceForm.graduate_experience_start_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                placeholder="Select start date" required :enable-time-picker="false" :format="'yyyy-MM-dd'"
                :preview-format="'yyyy-MM-dd'" />
              <InputError :message="experienceForm.errors.graduate_experience_start_date" class="mt-2" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">End Date</label>
              <Datepicker v-model="experienceForm.graduate_experience_end_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                placeholder="Select end date" :disabled="stillInRole" :enable-time-picker="false" :format="'yyyy-MM-dd'"
                :preview-format="'yyyy-MM-dd'" />
              <div class="mt-2">
                <input type="checkbox" id="still-in-role" v-model="stillInRole"
                  @change="experience.end_date = stillInRole ? null : experience.end_date" />
                <label for="still-in-role" class="text-sm text-gray-700 ml-2">I currently work here</label>
              </div>
              <InputError :message="experienceForm.errors.graduate_experience_end_date" class="mt-2" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Description</label>
              <textarea v-model="experienceForm.graduate_experience_description"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                rows="3" placeholder="Describe your role and responsibilities..."></textarea>
              <InputError :message="experienceForm.errors.graduate_experience_description" class="mt-2" />
            </div>
            <div class="flex justify-end">
              <button type="submit"
                class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center"
                :disabled="experienceForm.processing">
                <i class="fas fa-save mr-2"></i>
                {{ experienceForm.processing ? 'Saving...' : 'Save Experience' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- UpdateExperience Modal -->
    <div v-if="isUpdateExperienceModalOpen"
      class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">Update Experience</h2>
          <button class="text-gray-500 hover:text-gray-700" @click="closeUpdateExperienceModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="max-h-96 overflow-y-auto">
          <form @submit.prevent="updateExperience">
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Job Title <span class="text-red-500">*</span></label>
              <input type="text" v-model="experience.title"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                placeholder="e.g. Software Engineer" required />
              <InputError :message="experienceForm.errors.graduate_experience_title" class="mt-2" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Company <span class="text-red-500">*</span></label>
              <input type="text" v-model="experience.company"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                placeholder="e.g. Tech Corp" required />
              <InputError :message="experienceForm.errors.graduate_experience_company" class="mt-2" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Location <span class="text-red-500">*</span></label>
              <input type="text" v-model="experience.address"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                placeholder="e.g. New York, NY" required />
              <InputError :message="experienceForm.errors.graduate_experience_address" class="mt-2" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Employment Type <span
                  class="text-red-500">*</span></label>
              <select v-model="experience.employment_type"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                required>
                <option value="" disabled>Select employment type</option>
                <option value="Full-time">Full-time</option>
                <option value="Part-time">Part-time</option>
                <option value="Contract">Contract</option>
                <option value="Freelance">Freelance</option>
                <option value="Internship">Internship</option>
              </select>
              <InputError :message="experienceForm.errors.graduate_experience_employment_type" class="mt-2" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Start Date <span class="text-red-500">*</span></label>
              <Datepicker v-model="experience.start_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                placeholder="Select start date" required :enable-time-picker="false" :format="'yyyy-MM-dd'"
                :preview-format="'yyyy-MM-dd'" />
              <InputError :message="experienceForm.errors.graduate_experience_start_date" class="mt-2" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">End Date</label>
              <Datepicker v-model="experience.end_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                placeholder="Select end date" :disabled="stillInRole" />
              <div class="mt-2">
                <input type="checkbox" id="still-in-role-update" v-model="stillInRole"
                  @change="experience.end_date = stillInRole ? null : experience.end_date" />
                <label for="still-in-role-update" class="text-sm text-gray-700 ml-2">
                  I currently work here
                </label>
              </div>
              <InputError :message="experienceForm.errors.graduate_experience_end_date" class="mt-2" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Description</label>
              <textarea v-model="experience.description"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                rows="3" placeholder="Describe your role and responsibilities..."></textarea>
              <InputError :message="experienceForm.errors.graduate_experience_description" class="mt-2" />
            </div>
            <div class="flex justify-end">
              <button type="submit"
                class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center"
                :disabled="experienceForm.processing">
                <i class="fas fa-save mr-2"></i>
                {{ experienceForm.processing ? 'Updating...' : 'Update Experience' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>