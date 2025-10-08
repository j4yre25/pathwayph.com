<script setup>
import { ref, computed, onMounted, watch, reactive } from 'vue';
import Modal from '@/Components/Modal.vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Datepicker from 'vue3-datepicker';
import { parseISO, isValid } from 'date-fns';
import '@fortawesome/fontawesome-free/css/all.css';
import { Inertia } from '@inertiajs/inertia'


// Define props
const props = defineProps({
  activeSection: { type: String, default: 'experience' },
  experienceEntries: Array,
  archivedExperienceEntries: { type: Array, default: () => [] },
  companies: { type: Array, default: () => [] },
  locations: { type: Array, default: () => [] },
})

const toDateOrNull = (val) => {
  if (!val) return null;
  if (val instanceof Date) return val;
  const d = parseISO(val);
  return isValid(d) ? d : null;
};

const successMessage = ref('');
const errorMessage = ref('');
const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const isDuplicateModalOpen = ref(false);
const emit = defineEmits(['close-all-modals', 'reset-all-states']);
const experienceForm = useForm({
  graduate_experience_title: '',
  graduate_experience_company: '', // for UI only (display)
  graduate_experience_start_date: null,
  graduate_experience_end_date: null,
  graduate_experience_address: '',
  graduate_experience_description: '',
  graduate_experience_employment_type: '',
  is_current: false,
});

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

const showArchivedExperience = ref(false); // State for showing/hiding archived experience


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

const showAddSuggestions = ref(false)
const showUpdateSuggestions = ref(false)
const companySearchAdd = ref('')    // input text for Add modal
const companySearchUpdate = ref('') // input text for Update modal

const filteredCompaniesAdd = computed(() => {
  const q = (companySearchAdd.value || '').toLowerCase().trim()
  if (!q) return props.companies.slice(0, 8)
  return props.companies.filter(c => c.name.toLowerCase().includes(q)).slice(0, 8)
})
const filteredCompaniesUpdate = computed(() => {
  const q = (companySearchUpdate.value || '').toLowerCase().trim()
  if (!q) return props.companies.slice(0, 8)
  return props.companies.filter(c => c.name.toLowerCase().includes(q)).slice(0, 8)
})

// Keep form.company_id / not_company in sync for Add modal
watch(companySearchAdd, (val) => {
  const match = props.companies.find(c => c.name.toLowerCase() === (val || '').toLowerCase().trim())
  if (match) {
    experienceForm.company_id = match.id
    experienceForm.not_company = ''
  } else {
    experienceForm.company_id = null
    experienceForm.not_company = (val || '').trim()
  }
  experienceForm.graduate_experience_company = val || ''
})

// For Update modal, we set these right before submit to avoid overwriting while user edits
function selectCompanyForAdd(c) {
  companySearchAdd.value = c.name
  experienceForm.company_id = c.id
  experienceForm.not_company = ''
  showAddSuggestions.value = false
}
function selectCompanyForUpdate(c) {
  companySearchUpdate.value = c.name
  showUpdateSuggestions.value = false
}

// When opening Add modal, clear search/ids
const openAddExperienceModal = () => {
  resetExperience()
  isAddExperienceModalOpen.value = true
  companySearchAdd.value = ''
  experienceForm.company_id = null
  experienceForm.not_company = ''
}

// When opening Update modal, seed the input with displayed company
const openUpdateExperienceModal = (experienceEntry) => {
  resetExperience();
  experience.value = {
    id: experienceEntry.id,
    title: experienceEntry.title,
    company: experienceEntry.company || experienceEntry.company_name || '',
    start_date: toDateOrNull(experienceEntry.start_date),
    end_date: experienceEntry.is_current ? null : toDateOrNull(experienceEntry.end_date),
    address: experienceEntry.address || '',
    description: experienceEntry.description || 'No description provided',
    employment_type: experienceEntry.employment_type || '',
    is_current: experienceEntry.is_current
  };
  companySearchUpdate.value = experienceEntry.company || experienceEntry.company_name || '';
  stillInRole.value = experienceEntry.is_current;
  isUpdateExperienceModalOpen.value = true;
};

const locationSearchAdd = ref('');
const showLocationSuggestionsAdd = ref(false);
const filteredLocationsAdd = computed(() => {
  const q = (locationSearchAdd.value || '').toLowerCase().trim();
  if (!q) return props.locations.slice(0, 8);
  return props.locations.filter(l => l.name.toLowerCase().includes(q)).slice(0, 8);
});
watch(locationSearchAdd, (val) => {
  const match = props.locations.find(l => l.name.toLowerCase() === (val || '').toLowerCase().trim());
  experienceForm.location_id = match ? match.id : null;
  experienceForm.graduate_experience_address = val || '';
});
function selectLocationForAdd(l) {
  locationSearchAdd.value = l.name;
  experienceForm.location_id = l.id;
  experienceForm.graduate_experience_address = l.name;
  showLocationSuggestionsAdd.value = false;
}

// Keep is_current synced with checkbox and clear end date when current
watch(stillInRole, (val) => {
  experienceForm.is_current = !!val
  if (val) {
    experienceForm.graduate_experience_end_date = null
  }
})

// Submit handlers updated to include company fields
const addExperience = () => {
  // map current flag before submit
  experienceForm.is_current = !!stillInRole.value

  // Safety: ensure company fields reflect the input
  const match = props.companies.find(c => c.name.toLowerCase() === (companySearchAdd.value || '').toLowerCase().trim())
  experienceForm.company_id = match ? match.id : null
  experienceForm.not_company = match ? '' : (companySearchAdd.value || '').trim()

  experienceForm.post(route('profile.experience.add'), {
    onSuccess: () => {
      experienceForm.reset()
      companySearchAdd.value = ''
      isAddExperienceModalOpen.value = false
      isSuccessModalOpen.value = true
      Inertia.reload()
    },
    onError: () => {
      isErrorModalOpen.value = true
      alert('An error occurred while adding the experience. Please check the form and try again.')
    },
  })
}

const updateExperience = () => {
  // Map fields
  experienceForm.graduate_experience_title = experience.value.title?.trim() || ''
  experienceForm.graduate_experience_company = companySearchUpdate.value?.trim() || ''
  experienceForm.graduate_experience_start_date = formatDate(experience.value.start_date)
  experienceForm.graduate_experience_end_date = stillInRole.value ? null : formatDate(experience.value.end_date)
  experienceForm.graduate_experience_address = experience.value.address?.trim() || ''
  experienceForm.graduate_experience_description = experience.value.description?.trim() || 'No description provided'
  experienceForm.graduate_experience_employment_type = experience.value.employment_type?.trim() || ''
  experienceForm.is_current = stillInRole.value

  // Normalize company fields for backend
  const match = props.companies.find(c => c.name.toLowerCase() === (companySearchUpdate.value || '').toLowerCase().trim())
  experienceForm.company_id = match ? match.id : null
  experienceForm.not_company = match ? '' : (companySearchUpdate.value || '').trim()

  experienceForm.put(route('profile.experience.update', experience.value.id), {
    onSuccess: () => {
      resetExperience()
      companySearchUpdate.value = ''
      isUpdateExperienceModalOpen.value = false
      successMessage.value = 'Experience updated successfully.'
      isSuccessModalOpen.value = true
      Inertia.reload()
    },
    onError: (errors) => {
      errorMessage.value = 'An error occurred while updating the experience. Please check the form and try again.'
      isErrorModalOpen.value = true
      console.error('Error updating experience:', errors)
    },
  })
};

const closeAddExperienceModal = () => {
  isAddExperienceModalOpen.value = false;
  resetExperience();
};

const closeUpdateExperienceModal = () => {
  isUpdateExperienceModalOpen.value = false;
  resetExperience();
};

// Confirmation Modal Logic
const confirmModal = reactive({
  show: false,
  type: '',      // 'archive' | 'unarchive' | 'delete'
  entry: null,
  message: '',
  confirmLabel: '',
  confirmAction: null,
});

function openConfirm(type, entry) {
  confirmModal.type = type;
  confirmModal.entry = entry;
  confirmModal.show = true;
  if (type === 'archive') {
    confirmModal.message = 'Archive this experience entry?';
    confirmModal.confirmLabel = 'Archive';
    confirmModal.confirmAction = () => doArchive(entry);
  } else if (type === 'unarchive') {
    confirmModal.message = 'Restore this archived experience entry?';
    confirmModal.confirmLabel = 'Restore';
    confirmModal.confirmAction = () => doUnarchive(entry);
  } else if (type === 'delete') {
    confirmModal.message = 'Permanently delete this experience entry? This cannot be undone.';
    confirmModal.confirmLabel = 'Delete';
    confirmModal.confirmAction = () => doDelete(entry);
  }
}

function closeConfirm() {
  confirmModal.show = false;
  confirmModal.type = '';
  confirmModal.entry = null;
  confirmModal.message = '';
  confirmModal.confirmLabel = '';
  confirmModal.confirmAction = null;
}

// Actual server actions
function doArchive(entry) {
  closeConfirm();
  useForm({}).put(route('profile.experience.archive', entry.id), {
    preserveScroll: true,
    onSuccess: () => {
      Inertia.reload();
    }
  });
}
function doUnarchive(entry) {
  closeConfirm();
  useForm({}).put(route('profile.experience.unarchive', entry.id), {
    preserveScroll: true,
    onSuccess: () => {
      Inertia.reload();
    }
  });
}
function doDelete(entry) {
  closeConfirm();
  useForm({}).delete(route('profile.experience.remove', entry.id), {
    preserveScroll: true,
    onSuccess: () => {
      Inertia.reload();
    }
  });
}

// Button handlers (used in template)
function archiveExperience(entry) { openConfirm('archive', entry); }
function unarchiveExperience(entry) { openConfirm('unarchive', entry); }
function deleteExperience(entry) { openConfirm('delete', entry); }



// Function to toggle archived experience visibility
const toggleArchivedExperience = () => {
  showArchivedExperience.value = !showArchivedExperience.value;
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

function resetExperience() {
  experienceForm.reset()
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
  }
  companySearchAdd.value = ''
  companySearchUpdate.value = ''
  stillInRole.value = false
}

function calculateYears(start, end, isCurrent) {
  if (!start) return '';
  const startDate = new Date(start);
  const endDate = isCurrent ? new Date() : (end ? new Date(end) : new Date());
  if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) return '';
  let years = endDate.getFullYear() - startDate.getFullYear();
  let months = endDate.getMonth() - startDate.getMonth();
  if (months < 0) {
    years--;
    months += 12;
  }
  // Show as "X years", "X years Y months", or "Less than 1 year"
  if (years < 1 && months < 1) return 'Less than 1 month';
  if (years < 1) return `${months} month${months > 1 ? 's' : ''}`;
  if (months === 0) return `${years} year${years > 1 ? 's' : ''}`;
  return `${years} year${years > 1 ? 's' : ''} ${months} month${months > 1 ? 's' : ''}`;
}

// Before submit (addExperience/updateExperience)
experienceForm.is_current = !!stillInRole.value;

// Selected company and location handling
const selectedCompany = props.companies.find(c => c.id === experienceForm.company_id);
experienceForm.graduate_experience_company = selectedCompany
  ? selectedCompany.name
  : (companySearchAdd.value || experienceForm.not_company || '');

const selectedLocation = props.locations.find(l => l.id === experienceForm.location_id);
experienceForm.graduate_experience_address = selectedLocation
  ? selectedLocation.name
  : (locationSearchAdd.value || experienceForm.graduate_experience_address || '');
</script>

<template>
  <!-- Success Modal -->
  <Modal :modelValue="isSuccessModalOpen" @close="isSuccessModalOpen = false">
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
  <Modal :modelValue="isErrorModalOpen" @close="isErrorModalOpen = false">
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
  <Modal :modelValue="isDuplicateModalOpen" @close="isDuplicateModalOpen = false">
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

  <!-- Confirmation Modal -->
  <Modal :show="confirmModal.show" @close="closeConfirm">
    <div class="p-6">
      <div class="flex items-center justify-center mb-4">
        <div :class="{
          'bg-amber-100': confirmModal.type === 'archive' || confirmModal.type === 'unarchive',
          'bg-red-100': confirmModal.type === 'delete'
        }" class="rounded-full p-3">
          <i v-if="confirmModal.type === 'archive'" class="fas fa-archive text-amber-500 text-xl"></i>
          <i v-else-if="confirmModal.type === 'unarchive'" class="fas fa-undo text-green-500 text-xl"></i>
          <i v-else-if="confirmModal.type === 'delete'" class="fas fa-trash text-red-500 text-xl"></i>
        </div>
      </div>
      <h3 class="text-lg font-medium text-center text-gray-900 mb-2">
        {{ confirmModal.confirmLabel }} Experience
      </h3>
      <p class="text-center text-gray-600 mb-4">{{ confirmModal.message }}</p>
      <div class="mt-6 flex justify-center space-x-2">
        <SecondaryButton type="button" @click="closeConfirm">Cancel</SecondaryButton>
        <DangerButton v-if="confirmModal.type === 'delete'" type="button" @click="confirmModal.confirmAction">Delete
        </DangerButton>
        <PrimaryButton v-else type="button"
          :class="confirmModal.type === 'archive' ? 'bg-amber-500 hover:bg-amber-600' : 'bg-green-600 hover:bg-green-700'"
          @click="confirmModal.confirmAction">{{ confirmModal.confirmLabel }}</PrimaryButton>
      </div>
    </div>
  </Modal>

  <div v-if="activeSection === 'experience'" class="flex flex-col lg:flex-row">
    <div class="w-full mb-6">
      <!-- Work Experience Card -->
      <div
        class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-300 mb-6">
        <!-- Card Header -->
        <div class="flex justify-between items-center p-4 bg-gradient-to-r from-blue-100 to-white">
          <div class="flex items-center">
            <div class="bg-blue-200 p-2 rounded-full mr-3">
              <i class="fas fa-briefcase text-blue-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-blue-700">Work Experience</h3>
          </div>
          <div class="flex space-x-2">
            <PrimaryButton
              class="bg-blue-600 text-white px-3 py-1 rounded-lg flex items-center hover:bg-blue-700 text-sm"
              @click="openAddExperienceModal">
              <i class="fas fa-plus mr-1"></i> Add Experience
            </PrimaryButton>
            <button
              class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded-lg flex items-center transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer text-sm"
              @click="toggleArchivedExperience">
              <i class="fas" :class="showArchivedExperience ? 'fa-eye-slash' : 'fa-eye'"></i>
              <span class="ml-1">{{ showArchivedExperience ? 'Hide Archived' : 'Show Archived' }}</span>
            </button>
          </div>
        </div>

        <!-- Card Body -->
        <div class="p-6 transition-all duration-300">
          <p class="text-gray-600 mb-6">Showcase your professional experience</p>

          <!-- Experience Entries -->
          <div v-if="experienceEntries.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div v-for="experienceEntry in experienceEntries" :key="experienceEntry.id"
              class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-all duration-300 space-y-3 relative group border border-gray-200">
              <div>
                <div class="border-b border-blue-100 pb-3">
                  <h2 class="text-xl font-bold text-blue-900">{{ experienceEntry.title }}</h2>
                  <div class="flex items-center text-gray-700 mt-1">
                    <i class="fas fa-building text-blue-600 mr-2"></i>
                    <span class="font-medium">
                      {{ experienceEntry.company_name }}
                    </span>
                  </div>
                </div>
                <div class="flex items-center text-gray-600 mt-2">
                  <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>
                  <span>{{ experienceEntry.address }}</span>
                </div>
                <!-- Experience Card Date & Duration Block -->
                <div class="flex items-center text-gray-600 mt-2">
                  <i class="far fa-calendar-alt text-blue-600 mr-2"></i>
                  <span>
                    {{ formatDate(experienceEntry.start_date) }}
                    -
                    {{ experienceEntry.is_current ? 'Present' : formatDate(experienceEntry.end_date) }}
                  </span>
                </div>
                <p v-if="!experienceEntry.is_current" class="text-gray-600 mt-2 flex items-center">
                  <i class="fas fa-hourglass-half text-blue-600 mr-2"></i>
                  <strong class="text-blue-900">Experience:</strong>
                  <span class="ml-1">{{ calculateYears(experienceEntry.start_date, experienceEntry.end_date, false)
                  }}</span>
                </p>
                <p class="text-gray-600 mt-2 flex items-center">
                  <i class="fas fa-briefcase text-blue-600 mr-2"></i>
                  <strong class="text-blue-900">Employment Type:</strong> {{ experienceEntry.employment_type }}
                </p>
                <div class="mt-3">
                  <div class="flex items-center mb-2">
                    <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                    <strong class="text-blue-900">Description:</strong>
                  </div>
                  <div class="bg-gray-50 p-3 rounded-md border-l-4 border-blue-400">
                    {{ experienceEntry.description || 'No description provided' }}
                  </div>
                </div>
              </div>
              <div
                class="absolute top-2 right-2 flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                <button type="button"
                  class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 p-1.5 rounded-full transition-colors duration-200"
                  @click="openUpdateExperienceModal(experienceEntry)">
                  <i class="fas fa-edit"></i>
                </button>
                <button type="button"
                  class="text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 p-1.5 rounded-full transition-colors duration-200"
                  @click="archiveExperience(experienceEntry)">
                  <i class="fas fa-archive"></i>
                </button>
                <button type="button"
                  class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 p-1.5 rounded-full transition-colors duration-200"
                  @click="deleteExperience(experienceEntry)">
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
      </div>

      <!-- Archived Experience Card -->
      <div v-if="showArchivedExperience"
        class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-300 mb-6">
        <!-- Card Header -->
        <div class="flex justify-between items-center p-4 bg-gradient-to-r from-gray-200 to-white">
          <div class="flex items-center">
            <div class="bg-gray-300 p-2 rounded-full mr-3">
              <i class="fas fa-archive text-gray-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700">Archived Experience</h3>
          </div>
        </div>

        <!-- Card Body -->
        <div class="p-6 transition-all duration-300">
          <div v-if="archivedExperienceEntries.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div v-for="experienceEntry in archivedExperienceEntries" :key="experienceEntry.id"
              class="bg-gray-50 p-5 rounded-lg border border-gray-200 relative opacity-90 hover:opacity-100 transition">
              <div class="space-y-2">
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
                  <i class="far fa-calendar-alt text-blue-600 mr-2"></i>
                  <span>
                    {{ formatDate(experienceEntry.start_date) }}
                    -
                    {{ experienceEntry.is_current ? 'Present' : formatDate(experienceEntry.end_date) }}
                  </span>
                </div>
                <p v-if="!experienceEntry.is_current" class="text-gray-600 mt-2 flex items-center">
                  <i class="fas fa-hourglass-half text-blue-600 mr-2"></i>
                  <strong class="text-blue-900">Experience:</strong>
                  <span class="ml-1">{{ calculateYears(experienceEntry.start_date, experienceEntry.end_date, false)
                  }}</span>
                </p>
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
              <div
                class="absolute top-2 left-2 bg-amber-100 text-amber-700 text-[10px] px-2 py-0.5 rounded uppercase tracking-wide">
                Archived
              </div>
              <div class="absolute top-2 right-2 flex space-x-2">
                <button type="button"
                  class="text-green-600 hover:text-green-800 bg-green-50 hover:bg-green-100 p-1.5 rounded-full transition-colors duration-200"
                  @click="unarchiveExperience(experienceEntry)">
                  <i class="fas fa-undo"></i>
                </button>
                <button type="button"
                  class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 p-1.5 rounded-full transition-colors duration-200"
                  @click="deleteExperience(experienceEntry)">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- If no archived experience entries exist -->
          <div v-else class="bg-white p-8 rounded-lg shadow">
            <p class="text-gray-600">No archived experience entries found.</p>
          </div>
        </div>
      </div>
    </div>


    <!-- Add Experience Modal -->
    <div v-if="isAddExperienceModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">Add Experience</h2>
          <SecondaryButton @click="closeAddExperienceModal">
            <i class="fas fa-times mr-1"></i> Cancel
          </SecondaryButton>
        </div>
        <div class="max-h-96 overflow-y-auto">
          <form @submit.prevent="addExperience">
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Job Title <span class="text-red-500">*</span></label>
              <input type="text" v-model="experienceForm.graduate_experience_title"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. Software Engineer" required />
              <InputError :message="experienceForm.errors.graduate_experience_title" class="mt-2" />
            </div>

            <!-- Company: suggestive dropdown -->
            <div class="mb-4 relative">
              <label class="block text-gray-700 font-medium mb-2">Company <span class="text-red-500">*</span></label>
              <input type="text" v-model="companySearchAdd" @focus="showAddSuggestions = true"
                @blur="() => setTimeout(() => showAddSuggestions = false, 150)"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Type to search or enter a new company" required />
              <ul v-if="showAddSuggestions && filteredCompaniesAdd.length"
                class="absolute z-20 mt-1 w-full bg-white border rounded shadow max-h-48 overflow-auto text-sm">
                <li v-for="c in filteredCompaniesAdd" :key="c.id" @mousedown.prevent="selectCompanyForAdd(c)"
                  class="px-3 py-2 hover:bg-blue-50 cursor-pointer">
                  {{ c.name }}
                </li>
              </ul>
              <div v-if="showAddSuggestions && !filteredCompaniesAdd.length" class="text-xs text-gray-400 mt-1">
                No matches. Press Enter to use “{{ companySearchAdd }}”.
              </div>
            </div>

            <div class="mb-4 relative">
              <label class="block text-gray-700 font-medium mb-2">Location <span class="text-red-500">*</span></label>
              <input type="text" v-model="locationSearchAdd" @focus="showLocationSuggestionsAdd = true"
                @blur="() => setTimeout(() => showLocationSuggestionsAdd = false, 150)"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Type to search or enter a new location" required />
              <ul v-if="showLocationSuggestionsAdd && filteredLocationsAdd.length"
                class="absolute z-20 mt-1 w-full bg-white border rounded shadow max-h-48 overflow-auto text-sm">
                <li v-for="l in filteredLocationsAdd" :key="l.id" @mousedown.prevent="selectLocationForAdd(l)"
                  class="px-3 py-2 hover:bg-blue-50 cursor-pointer">
                  {{ l.name }}
                </li>
              </ul>
              <div v-if="showLocationSuggestionsAdd && !filteredLocationsAdd.length" class="text-xs text-gray-400 mt-1">
                No matches. Press Enter to use “{{ locationSearchAdd }}”.
              </div>
              <InputError :message="experienceForm.errors.graduate_experience_address" class="mt-2" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium  mb-2">Employment Type <span
                  class="text-red-500">*</span></label>
              <select v-model="experienceForm.graduate_experience_employment_type"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required>
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
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Select start date" required :enable-time-picker="false" :format="'yyyy-MM-dd'"
                :preview-format="'yyyy-MM-dd'" />
              <InputError :message="experienceForm.errors.graduate_experience_start_date" class="mt-2" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">End Date</label>
              <Datepicker v-model="experienceForm.graduate_experience_end_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Select end date" :disabled="stillInRole" :enable-time-picker="false" :format="'yyyy-MM-dd'"
                :preview-format="'yyyy-MM-dd'" />
              <div class="mt-2">
                <input type="checkbox" id="still-in-role" v-model="stillInRole"
                  @change="experienceForm.graduate_experience_end_date = stillInRole ? null : experienceForm.graduate_experience_end_date"
                  class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                <label for="still-in-role" class="text-sm text-gray-700 ml-2">I currently work here</label>
              </div>
              <InputError :message="experienceForm.errors.graduate_experience_end_date" class="mt-2" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Description</label>
              <textarea v-model="experienceForm.graduate_experience_description"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" rows="3"
                placeholder="Describe your role and responsibilities..."></textarea>
              <InputError :message="experienceForm.errors.graduate_experience_description" class="mt-2" />
            </div>
            <div class="flex justify-end">
              <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-md flex items-center justify-center"
                :disabled="experienceForm.processing">
                <i class="fas fa-save mr-2"></i>
                {{ experienceForm.processing ? 'Saving...' : 'Save Experience' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Update Experience Modal -->
    <div v-if="isUpdateExperienceModalOpen"
      class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">Update Experience</h2>
          <SecondaryButton @click="closeUpdateExperienceModal">
            <i class="fas fa-times mr-1"></i> Cancel
          </SecondaryButton>
        </div>
        <div class="max-h-96 overflow-y-auto">
          <form @submit.prevent="updateExperience">
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Job Title <span class="text-red-500">*</span></label>
              <input type="text" v-model="experience.title"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. Software Engineer" required />
              <InputError :message="experienceForm.errors.graduate_experience_title" class="mt-2" />
            </div>

            <!-- Company: suggestive dropdown -->
            <div class="mb-4 relative">
              <label class="block text-gray-700 font-medium mb-2">Company <span class="text-red-500">*</span></label>
              <input type="text" v-model="companySearchUpdate" @focus="showUpdateSuggestions = true"
                @blur="setTimeout(() => showUpdateSuggestions = false, 150)"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Type to search or enter a new company" required />
              <ul v-if="showUpdateSuggestions && filteredCompaniesUpdate.length"
                class="absolute z-20 mt-1 w-full bg-white border rounded shadow max-h-48 overflow-auto text-sm">
                <li v-for="c in filteredCompaniesUpdate" :key="c.id" @mousedown.prevent="selectCompanyForUpdate(c)"
                  class="px-3 py-2 hover:bg-blue-50 cursor-pointer">
                  {{ c.name }}
                </li>
              </ul>
              <div v-if="showUpdateSuggestions && !filteredCompaniesUpdate.length" class="text-xs text-gray-400 mt-1">
                No matches. Press Enter to use “{{ companySearchUpdate }}”.
              </div>
            </div>

            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Location <span class="text-red-500">*</span></label>
              <input type="text" v-model="experience.address"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. Manila, Philippines" required />
              <InputError :message="experienceForm.errors.graduate_experience_address" class="mt-2" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Employment Type <span
                  class="text-red-500">*</span></label>
              <select v-model="experience.employment_type"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required>
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
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Select start date" required :enable-time-picker="false" :format="'yyyy-MM-dd'"
                :preview-format="'yyyy-MM-dd'" />
              <InputError :message="experienceForm.errors.graduate_experience_start_date" class="mt-2" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">End Date</label>
              <Datepicker v-model="experience.end_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Select end date" :disabled="stillInRole" />
              <div class="mt-2">
                <input type="checkbox" id="still-in-role-update" v-model="stillInRole"
                  @change="experience.end_date = stillInRole ? null : experience.end_date"
                  class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                <label for="still-in-role-update" class="text-sm text-gray-700 ml-2">
                  I currently work here
                </label>
              </div>
              <InputError :message="experienceForm.errors.graduate_experience_end_date" class="mt-2" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Description</label>
              <textarea v-model="experience.description"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" rows="3"
                placeholder="Describe your role and responsibilities..."></textarea>
              <InputError :message="experienceForm.errors.graduate_experience_description" class="mt-2" />
            </div>
            <div class="flex justify-end">
              <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-md flex items-center justify-center"
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

<style scoped>
/* Glow effects for form elements */
@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
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
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

input:focus,
textarea:focus,
select:focus {
  animation: pulse 1.5s infinite;
  transition: all 0.3s ease;
}

button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
}

.form-group {
  animation: fadeIn 0.6s ease-out;
}

.modal-content {
  animation: fadeIn 0.4s ease-out;
}
</style>