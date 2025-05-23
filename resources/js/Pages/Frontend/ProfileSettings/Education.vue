<script setup>
import { ref, computed, onMounted, watch, reactive } from 'vue';
import Modal from '@/Components/Modal.vue';
import { useForm, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Datepicker from 'vue3-datepicker';
import { isValid } from 'date-fns';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  activeSection: {
    type: String,
    default: 'education'
  },
  user: {
    type: Object,
    default: () => ({})
  },
  educationEntries: Array,
  archivedEducationEntries: {
    type: Array,
    default: () => []
  },
});
const emit = defineEmits(['close-all-modals', 'reset-all-states']);

const modals = reactive({
  isAddOpen: false,
  isUpdateOpen: false,
  isSuccessOpen: false,
  isErrorOpen: false,
  isDuplicateOpen: false
});

const errorMessage = ref('');
const showArchivedEducation = ref(false);
const isEducationAddedModalOpen = ref(false);
const isEducationUpdatedModalOpen = ref(false);

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

const formattedStartDate = computed(() => {
  return education.value.start_date
    ? new Date(education.value.start_date).toISOString().split('T')[0]
    : null;
});

const formattedEndDate = computed(() => {
  if (education.value.is_current) return null;
  return education.value.end_date
    ? new Date(education.value.end_date).toISOString().split('T')[0]
    : null;
});

const educationEntries = computed(() => props.educationEntries || []);
const archivedEducationEntries = computed(() => props.archivedEducationEntries || []);

onMounted(() => {
  // Optionally log data
});

watch(() => props.activeSection, (newValue) => {
  if (newValue === 'education') {
    router.reload();
  }
});

const education = ref({
  education: '', // institution name
  program: '',
  field_of_study: '',
  start_date: null,
  end_date: null,
  description: '',
  is_current: false,
  achievements: '',
  noAchievements: false,
});

const isAddEducationModalOpen = ref(false);
const isUpdateEducationModalOpen = ref(false);

watch(() => education.value.start_date, (newValue) => {
  if (newValue && isNaN(new Date(newValue).getTime())) {
    education.value.start_date = null;
  }
});

watch(() => education.value.is_current, (newValue) => {
  if (newValue) {
    education.value.end_date = null;
  } else {
    education.value.end_date = '';
  }
});

watch(() => education.value.end_date, (newValue) => {
  if (newValue && isNaN(new Date(newValue).getTime())) {
    education.value.end_date = null;
  }
});

const addEducation = () => {
  // Validate all required fields
  if (
    !education.value.education ||
    education.value.education.trim() === '' ||
    !education.value.program ||
    education.value.program.trim() === '' ||
    !education.value.field_of_study ||
    education.value.field_of_study.trim() === '' ||
    !education.value.start_date
  ) {
    errorMessage.value = 'Please fill in all required fields.';
    modals.isErrorOpen = true;
    return;
  }

  // Validate start date
  const startDate = education.value.start_date instanceof Date
    ? education.value.start_date
    : new Date(education.value.start_date);

  if (isNaN(startDate.getTime())) {
    errorMessage.value = 'Please enter a valid start date.';
    modals.isErrorOpen = true;
    return;
  }

  // Validate end date if not current education
  if (!education.value.is_current && education.value.end_date) {
    const endDate = education.value.end_date instanceof Date
      ? education.value.end_date
      : new Date(education.value.end_date);

    if (isNaN(endDate.getTime())) {
      errorMessage.value = 'Please enter a valid end date.';
      modals.isErrorOpen = true;
      return;
    }

    // Check if end date is after start date
    if (endDate < startDate) {
      errorMessage.value = 'End date must be after start date.';
      modals.isErrorOpen = true;
      return;
    }
  }

  // Check for duplicates - only check non-archived entries
  const isDuplicate = educationEntries.value.some(entry =>
    entry.education === education.value.education &&
    entry.program === education.value.program &&
    entry.field_of_study === education.value.field_of_study
  );

  if (isDuplicate) {
    modals.isDuplicateOpen = true;
    return;
  }

  // Format dates properly for submission
  const formattedStartDate = formatDate(education.value.start_date);
  const formattedEndDate = education.value.is_current ? null : formatDate(education.value.end_date);

  const educationForm = useForm({
    education: education.value.education.trim(),
    program: education.value.program.trim(),
    field_of_study: education.value.field_of_study.trim(),
    start_date: formattedStartDate,
    end_date: formattedEndDate,
    description: education.value.description,
    is_current: education.value.is_current,
    achievements: education.value.noAchievements ? null : education.value.achievements,
  });

  educationForm.post(route('profile.education.add'), {
    onSuccess: (response) => {
      emit('close-all-modals');
      isAddEducationModalOpen.value = false;
      resetEducation();
      errorMessage.value = 'Education added successfully!';
      modals.isSuccessOpen = true;
      router.reload();
    },
    onError: (errors) => {
      if (errors.duplicate) {
        modals.isDuplicateOpen = true;
      } else {
        errorMessage.value = errors.message || 'An error occurred while adding education. Please try again.';
        modals.isErrorOpen = true;
      }
    },
  });
};

const updateEducation = () => {
  if (!education.value.id) {
    errorMessage.value = 'Education ID is missing. Please try again.';
    modals.isErrorOpen = true;
    return;
  }

  // Validate all required fields
  if (
    !education.value.education ||
    education.value.education.trim() === '' ||
    !education.value.program ||
    education.value.program.trim() === '' ||
    !education.value.field_of_study ||
    education.value.field_of_study.trim() === '' ||
    !education.value.start_date
  ) {
    errorMessage.value = 'Please fill in all required fields.';
    modals.isErrorOpen = true;
    return;
  }

  // Validate start date
  const startDate = education.value.start_date instanceof Date
    ? education.value.start_date
    : new Date(education.value.start_date);

  if (isNaN(startDate.getTime())) {
    errorMessage.value = 'Please enter a valid start date.';
    modals.isErrorOpen = true;
    return;
  }

  // Validate end date if not current education
  if (!education.value.is_current && education.value.end_date) {
    const endDate = education.value.end_date instanceof Date
      ? education.value.end_date
      : new Date(education.value.end_date);

    if (isNaN(endDate.getTime())) {
      errorMessage.value = 'Please enter a valid end date.';
      modals.isErrorOpen = true;
      return;
    }

    // Check if end date is after start date
    if (endDate < startDate) {
      errorMessage.value = 'End date must be after start date.';
      modals.isErrorOpen = true;
      return;
    }
  }

  // Check for duplicates - only check non-archived entries, excluding the current entry
  const isDuplicate = educationEntries.value.some(entry =>
    entry.id !== education.value.id &&
    entry.education === education.value.education &&
    entry.program === education.value.program &&
    entry.field_of_study === education.value.field_of_study
  );

  if (isDuplicate) {
    modals.isDuplicateOpen = true;
    return;
  }

  const educationForm = useForm({
    education: education.value.education.trim(),
    program: education.value.program.trim(),
    field_of_study: education.value.field_of_study.trim(),
    start_date: formatDate(education.value.start_date),
    end_date: education.value.is_current ? null : formatDate(education.value.end_date),
    description: education.value.description,
    is_current: education.value.is_current,
    achievements: education.value.noAchievements ? null : education.value.achievements,
    no_achievements: education.value.noAchievements,
  });

  educationForm.put(route('profile.education.update', { id: education.value.id }), {
    onSuccess: () => {
      closeUpdateEducationModal();
      errorMessage.value = 'Education updated successfully!';
      modals.isSuccessOpen = true;
      router.reload();
    },
    onError: (errors) => {
      if (errors.duplicate) {
        modals.isDuplicateOpen = true;
      } else {
        errorMessage.value = errors.message || 'An error occurred while updating the education entry. Please try again.';
        modals.isErrorOpen = true;
      }
    },
  });
};

const handleIsCurrent = () => {
  if (education.value.is_current) {
    education.value.end_date = null;
  } else {
    education.value.end_date = '';
  }
};

const resetEducation = () => {
  education.value = {
    education: '',
    program: '',
    field_of_study: '',
    start_date: null,
    end_date: null,
    description: '',
    is_current: false,
    achievements: '',
    noAchievements: false,
  };
};

const closeAddEducationModal = () => {
  isAddEducationModalOpen.value = false;
  resetEducation();
};

const closeUpdateEducationModal = () => {
  isUpdateEducationModalOpen.value = false;
  resetEducation();
};

const closeEducationAddedModal = () => {
  isEducationAddedModalOpen.value = false;
};

const closeEducationUpdatedModal = () => {
  isEducationUpdatedModalOpen.value = false;
};

const openAddEducationModal = () => {
  resetEducation();
  isAddEducationModalOpen.value = true;
};

const openUpdateModal = (entry) => {
  openUpdateEducationModal(entry);
};

const openUpdateEducationModal = (entry) => {
  education.value = {
    id: entry.id,
    education: entry.education,
    program: entry.program,
    field_of_study: entry.field_of_study,
    start_date: new Date(entry.start_date),
    end_date: entry.end_date ? new Date(entry.end_date) : null,
    description: entry.description || '',
    is_current: !entry.end_date,
    achievements: entry.achievements || '',
    noAchievements: !entry.achievements
  };
  isUpdateEducationModalOpen.value = true;
};

const deleteForm = useForm({});

const removeEducation = (education) => {
  if (confirm(`Are you sure you want to remove this education entry: ${education.program}?`)) {
    deleteForm.delete(route('profile.education.remove', education.id), {
      onSuccess: () => {
        router.reload();
        errorMessage.value = 'Education entry removed successfully';
        modals.isSuccessOpen = true;
      },
      onError: (error) => {
        errorMessage.value = 'Failed to remove education entry';
        modals.isErrorOpen = true;
      }
    });
  }
};

const archiveEducation = (id) => {
  if (confirm('Are you sure you want to archive this education entry?')) {
    const archiveForm = useForm({});
    archiveForm.post(route('profile.education.archive', { id }), {
      onSuccess: () => {
        router.reload();
        errorMessage.value = 'Education entry archived successfully';
        modals.isSuccessOpen = true;
      },
      onError: (error) => {
        errorMessage.value = 'Failed to archive education entry';
        modals.isErrorOpen = true;
      }
    });
  }
};

const restoreEducation = (id) => {
  if (confirm('Are you sure you want to restore this education entry?')) {
    const restoreForm = useForm({});
    restoreForm.post(route('profile.education.restore', { id }), {
      onSuccess: () => {
        router.reload();
        errorMessage.value = 'Education entry restored successfully';
        modals.isSuccessOpen = true;
      },
      onError: (error) => {
        errorMessage.value = 'Failed to restore education entry';
        modals.isErrorOpen = true;
      }
    });
  }
};

const deleteEducation = (id) => {
  if (confirm('Are you sure you want to permanently delete this education entry? This action cannot be undone.')) {
    const deleteForm = useForm({});
    deleteForm.delete(route('profile.education.delete', { id }), {
      onSuccess: () => {
        router.reload();
        errorMessage.value = 'Education entry deleted successfully';
        modals.isSuccessOpen = true;
      },
      onError: (error) => {
        errorMessage.value = 'Failed to delete education entry';
        modals.isErrorOpen = true;
      }
    });
  }
};

const toggleArchivedEducation = () => {
  showArchivedEducation.value = !showArchivedEducation.value;
  router.reload();
};

const handleNoAchievements = () => {
  if (education.value.noAchievements) {
    education.value.achievements = '';
  }
};
</script>

<template>
  <div v-if="activeSection === 'education'" class="flex flex-col">
    <!-- Success Modal -->
    <Modal :show="modals.isSuccessOpen" @close="modals.isSuccessOpen = false">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-green-100 rounded-full p-3">
            <i class="fas fa-check text-green-500 text-xl"></i>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Success!</h3>
        <p class="text-center text-gray-600">Your education information has been added successfully.</p>
        <div class="mt-6 flex justify-center">
          <button type="button"
            class="bg-green-500 hover:bg-green-600 transition-colors text-white px-4 py-2 rounded-md"
            @click="modals.isSuccessOpen = false">
            Close
          </button>
        </div>
      </div>
    </Modal>

    <!-- Error Modal -->
    <Modal :show="modals.isErrorOpen" @close="modals.isErrorOpen = false">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-red-100 rounded-full p-3">
            <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Error</h3>
        <p class="text-center text-gray-600">{{ errorMessage }}</p>
        <div class="mt-6 flex justify-center">
          <button type="button" class="bg-red-500 hover:bg-red-600 transition-colors text-white px-4 py-2 rounded-md"
            @click="modals.isErrorOpen = false">
            Close
          </button>
        </div>
      </div>
    </Modal>

    <!-- Duplicate Modal -->
    <Modal :show="modals.isDuplicateOpen" @close="modals.isDuplicateOpen = false">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-yellow-100 rounded-full p-3">
            <i class="fas fa-exclamation-circle text-yellow-500 text-xl"></i>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Duplicate Entry</h3>
        <p class="text-center text-gray-600">An education entry with these details already exists.</p>
        <div class="mt-6 flex justify-center">
          <button type="button"
            class="bg-yellow-500 hover:bg-yellow-600 transition-colors text-white px-4 py-2 rounded-md"
            @click="modals.isDuplicateOpen = false">
            Close
          </button>
        </div>
      </div>
    </Modal>
    <div class="w-full mb-6">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold mb-4">Education</h1>
        <div class="flex space-x-4">
          <PrimaryButton class="bg-indigo-600 text-white px-4 py-2 rounded flex items-center"
            @click="openAddEducationModal()">
            <i class="fas fa-plus mr-2"></i>
            Add Education
          </PrimaryButton>
          <button
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded flex items-center transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 cursor-pointer"
            @click="toggleArchivedEducation">
            <i class="fas" :class="showArchivedEducation ? 'fa-eye-slash' : 'fa-eye'"></i>
            <span class="ml-2">{{ showArchivedEducation ? 'Hide Archived' : 'Show Archived' }}</span>
          </button>
        </div>
      </div>
      <p class="text-gray-600 mb-6">Manage your educational background</p>

      <!-- Active Education Entries -->
      <div>
        <h2 class="text-lg font-medium mb-4">Active Education</h2>
        <div v-if="educationEntries.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div v-for="entry in educationEntries" :key="entry.id" class="bg-white p-8 rounded-lg shadow relative">
            <div>
              <div class="border-b pb-2">
                <h2 class="text-xl font-bold">{{ entry.education || 'Unknown Institution' }}</h2>
                <p class="text-gray-600">
                  {{ entry.program }} in {{ entry.field_of_study }}
                </p>
              </div>
              <div class="flex items-center text-gray-600 mt-2">
                <i class="far fa-calendar-alt mr-2"></i>
                <span>
                  {{ formatDisplayDate(entry.start_date) }} - {{ entry.end_date ?
                    formatDisplayDate(entry.end_date) : 'present' }}
                </span>
              </div>
              <p class="mt-2">
                <strong>
                  <i class="fas fa-info-circle text-gray-500 mr-2"></i> Description:
                </strong>
                {{ entry.description || 'No description provided' }}
              </p>
              <p class="mt-2">
                <strong>
                  <i class="fas fa-trophy text-gray-500 mr-2"></i> Achievements:
                </strong>
                <span v-if="entry.achievements && entry.achievements.includes(',')">
                  <ul class="list-disc list-inside">
                    <li v-for="(achievement, index) in entry.achievements.split(',')" :key="index">
                      {{ achievement.trim() }}
                    </li>
                  </ul>
                </span>
                <span v-else>
                  {{ entry.achievements || 'None' }}
                </span>
              </p>
            </div>
            <div class="absolute top-8 right-4 flex space-x-4">
              <button class="text-gray-600 hover:text-indigo-600" @click="openUpdateModal(entry)">
                <i class="fas fa-pen"></i>
              </button>
              <button class="text-amber-600 hover:text-amber-800" @click="archiveEducation(entry.id)">
                <i class="fas fa-archive"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- If no education entries exist -->
        <div v-else class="bg-white p-8 rounded-lg shadow">
          <p class="text-gray-600">No education entries added yet.</p>
        </div>
      </div>
    </div>

    <!-- Archived Education Entries -->
    <div v-if="showArchivedEducation" class="mt-8">
      <h2 class="text-lg font-medium mb-4">Archived Education</h2>
      <div v-if="archivedEducationEntries && archivedEducationEntries.length > 0"
        class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div v-for="education in archivedEducationEntries" :key="education.id"
          class="bg-gray-50 p-8 rounded-lg shadow relative border border-gray-200">
          <div class="opacity-75">
            <div class="border-b pb-2">
              <h2 class="text-xl font-bold">{{ education.education || 'Unknown Institution' }}</h2>
              <p class="text-gray-600">
                {{ education.program || 'Unknown Program' }} in {{
                  education.field_of_study || 'Unknown Field' }}
              </p>
            </div>
            <div class="flex items-center text-gray-600 mt-2">
              <i class="far fa-calendar-alt mr-2"></i>
              <span>
                {{ formatDisplayDate(education.start_date) }} - {{
                  education.end_date ? formatDisplayDate(education.end_date) :
                    'present' }}
              </span>
            </div>
            <div v-if="education.term" class="flex items-center text-gray-600 mt-2">
              <i class="far fa-calendar-alt mr-2"></i>
              <span>
                {{ education.term }}
              </span>
            </div>
          </div>
          <div class="absolute top-8 right-4 flex space-x-4">
            <button class="text-green-600 hover:text-green-800" @click="restoreEducation(education.id)">
              <i class="fas fa-box-open"></i>
            </button>
            <button class="text-red-600 hover:text-red-800" @click="deleteEducation(education.id)">
              <i class="fas fa-trash"></i>
            </button>
          </div>
          <div class="absolute top-2 left-2 bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded">
            Archived
          </div>
        </div>
      </div>

      <!-- If no archived education entries exist -->
      <div v-else class="bg-white p-8 rounded-lg shadow">
        <p class="text-gray-600">No archived education entries found.</p>
      </div>
    </div>

    <!-- Add Education Modal -->
    <div v-if="isAddEducationModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">Add Education</h2>
          <button class="text-gray-500 hover:text-gray-700" @click="closeAddEducationModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <p class="text-gray-600 mb-4">Add details about your educational background</p>
        <div class="max-h-96 overflow-y-auto">
          <form @submit.prevent="addEducation">
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Institution <span
                  class="text-red-500">*</span></label>
              <input type="text" v-model="education.education"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                placeholder="e.g. Harvard University" required>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Degree <span class="text-red-500">*</span></label>
              <input type="text" v-model="education.program"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                placeholder="e.g. Bachelor of Science" required>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Field of Study <span
                  class="text-red-500">*</span></label>
              <input type="text" v-model="education.field_of_study"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                placeholder="e.g. Computer Science" required>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Start Date <span class="text-red-500">*</span></label>
              <Datepicker v-model="education.start_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                placeholder="Select start date" required :enable-time-picker="false" :auto-apply="true"
                input-class-name="dp-custom-input" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">End Date</label>
              <Datepicker v-model="education.end_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                placeholder="Select end date" :disabled="education.is_current" :enable-time-picker="false"
                :auto-apply="true" input-class-name="dp-custom-input" />
              <div class="mt-2">
                <input type="checkbox" id="is-current" v-model="education.is_current" />
                <label for="is-current" class="text-sm text-gray-700 ml-2">I currently study here</label>
              </div>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Description</label>
              <textarea v-model="education.description"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                rows="3" placeholder="Describe your experience..."></textarea>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Achievements</label>
              <textarea v-model="education.achievements"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                rows="3" placeholder="List honors, awards, and scholarships..."
                :disabled="education.noAchievements"></textarea>
              <div class="mt-2">
                <input type="checkbox" id="no-achievements" v-model="education.noAchievements"
                  @change="handleNoAchievements" />
                <label for="no-achievements" class="text-sm text-gray-700 ml-2">No Achievements</label>
              </div>
            </div>
            <div class="flex justify-end">
              <button type="submit"
                class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
                <i class="fas fa-save mr-2"></i>
                Add Education
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Update Education Modal -->
    <div v-if="isUpdateEducationModalOpen"
      class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">Update Education</h2>
          <button class="text-gray-500 hover:text-gray-700" @click="closeUpdateEducationModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <p class="text-gray-600 mb-4">Update details about your educational background</p>
        <div class="max-h-96 overflow-y-auto">
          <form @submit.prevent="updateEducation">
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Institution <span
                  class="text-red-500">*</span></label>
              <input type="text" v-model="education.education"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                placeholder="e.g. Harvard University" required>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Degree <span class="text-red-500">*</span></label>
              <input type="text" v-model="education.program"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                placeholder="e.g. Bachelor of Science" required>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Field of Study <span
                  class="text-red-500">*</span></label>
              <input type="text" v-model="education.field_of_study"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                placeholder="e.g. Computer Science" required>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Start Date <span class="text-red-500">*</span></label>
              <Datepicker v-model="education.start_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                placeholder="Select start date" required :enable-time-picker="false" :auto-apply="true"
                input-class-name="dp-custom-input" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">End Date</label>
              <Datepicker v-model="education.end_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                placeholder="Select end date" :disabled="education.is_current" :enable-time-picker="false"
                :auto-apply="true" input-class-name="dp-custom-input" />
              <div class="mt-2">
                <input type="checkbox" id="is-current-update" v-model="education.is_current"
                  @change="handleIsCurrent" />
                <label for="is-current-update" class="text-sm text-gray-700 ml-2">I currently study here</label>
              </div>
            </div>

            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Description</label>
              <textarea v-model="education.description"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                rows="3" placeholder="Describe your experience..."></textarea>
            </div>

            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Achievements</label>
              <textarea v-model="education.achievements"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                rows="3" placeholder="List honors, awards, and scholarships..."
                :disabled="education.noAchievements"></textarea>
              <div class="mt-2">
                <input type="checkbox" id="no-achievements" v-model="education.noAchievements"
                  @change="handleNoAchievements" />
                <label for="no-achievements" class="text-sm text-gray-700 ml-2">No Achievements</label>
              </div>
            </div>

            <div class="flex justify-end">
              <button type="submit"
                class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
                <i class="fas fa-save mr-2"></i>
                Update Education
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
.dp-custom-input {
  padding: 0.5rem 0.75rem;
  border-radius: 0.375rem;
  border-width: 1px;
  width: 100%;
}

.dp-custom-input:focus {
  outline: none;
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
  --tw-ring-color: rgb(79 70 229 / 0.6);
}
</style>