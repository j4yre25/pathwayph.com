<script setup>
import { ref, computed, onMounted, watch, reactive } from 'vue';
import Modal from '@/Components/Modal.vue';
import { useForm, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Datepicker from 'vue3-datepicker';
import { isValid } from 'date-fns';

const props = defineProps({
  activeSection: { type: String, default: 'education' },
  user: { type: Object, default: () => ({}) },
  graduate: { type: Object, default: () => ({}) }, // <-- add this
  educationEntries: Array,

});
const emit = defineEmits(['close-all-modals', 'reset-all-states', 'refresh-education']);

const modals = reactive({
  isAddOpen: false,
  isUpdateOpen: false,
  isSuccessOpen: false,
  isErrorOpen: false,
  isDuplicateOpen: false
});

const errorMessage = ref('');

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
    onSuccess: () => {
      emit('refresh-education');
      isAddEducationModalOpen.value = false;
      resetEducation();
      errorMessage.value = 'Education added successfully!';
      modals.isSuccessOpen = true;
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
      emit('refresh-education');
      closeUpdateEducationModal();
      errorMessage.value = 'Education updated successfully!';
      modals.isSuccessOpen = true;
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




const handleNoAchievements = () => {
  if (education.value.noAchievements) {
    education.value.achievements = '';
  }
};
</script>

<template>
  <div v-if="activeSection === 'education'" class="w-full">
    <!-- Success Modal -->
    <Modal :modelValue="modals.isSuccessOpen" @close="modals.isSuccessOpen = false">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-green-100 rounded-full p-3">
            <i class="fas fa-check text-green-500 text-xl"></i>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Success!</h3>
        <p class="text-center text-gray-600">Your education information has been added successfully.</p>
        <div class="mt-6 flex justify-center">
          <PrimaryButton type="button"
            class="bg-green-500 hover:bg-green-600 transition-colors text-white px-4 py-2 rounded-md"
            @click="modals.isSuccessOpen = false">
            Close
          </PrimaryButton>
        </div>
      </div>
    </Modal>

    <!-- Error Modal -->
    <Modal :modelValue="modals.isErrorOpen" @close="modals.isErrorOpen = false">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-red-100 rounded-full p-3">
            <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Error</h3>
        <p class="text-center text-gray-600">{{ errorMessage }}</p>
        <div class="mt-6 flex justify-center">
          <DangerButton type="button" class="bg-red-500 hover:bg-red-600 transition-colors text-white px-4 py-2 rounded-md"
            @click="modals.isErrorOpen = false">
            Close
          </DangerButton>
        </div>
      </div>
    </Modal>

    <!-- Duplicate Modal -->
    <Modal :modelValue="modals.isDuplicateOpen" @close="modals.isDuplicateOpen = false">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-yellow-100 rounded-full p-3">
            <i class="fas fa-exclamation-circle text-yellow-500 text-xl"></i>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Duplicate Entry</h3>
        <p class="text-center text-gray-600">An education entry with these details already exists.</p>
        <div class="mt-6 flex justify-center">
          <SecondaryButton type="button"
            class="bg-yellow-500 hover:bg-yellow-600 transition-colors text-white px-4 py-2 rounded-md"
            @click="modals.isDuplicateOpen = false">
            Close
          </SecondaryButton>
        </div>
      </div>
    </Modal>
    
    <!-- Education Section -->
    <div class="bg-white rounded-lg shadow-sm border border-blue-100 overflow-hidden transition-all duration-300 mb-6">
      <div class="flex justify-between items-center p-4 bg-gradient-to-r from-blue-50 to-white border-b border-blue-100">
        <div class="flex items-center">
          <div class="bg-blue-100 p-2 rounded-full mr-3">
            <i class="fas fa-graduation-cap text-blue-600"></i>
          </div>
          <h3 class="text-lg font-semibold text-blue-800">Education History</h3>
        </div>
        <div class="flex space-x-2">
          <PrimaryButton class="bg-blue-600 text-white px-4 py-2 rounded flex items-center hover:bg-blue-700"
            @click="openAddEducationModal()">
            <i class="fas fa-plus mr-2"></i>
            Add Education
          </PrimaryButton>
        </div>
      </div>
      <div class="p-4">
        <!-- Active Education Entries -->
        <div>
          <div v-if="educationEntries.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div v-for="entry in educationEntries" :key="entry.id" class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200 relative">
              <div class="space-y-2">
                <div class="border-b pb-2">
                  <h2 class="text-xl font-bold text-blue-900">{{ entry.education || 'Unknown Institution' }}</h2>
                  <p class="text-gray-700">
                    <span class="font-medium">{{ entry.program }}</span> in <span class="px-2 py-1 bg-blue-50 text-blue-700 text-sm rounded-full">{{ entry.field_of_study }}</span>
                  </p>
                </div>
                <div class="flex items-center text-gray-600 mt-2">
                  <i class="far fa-calendar-alt text-blue-600 mr-2"></i>
                  <span>
                    {{ formatDisplayDate(entry.start_date) }} - {{ entry.end_date ?
                      formatDisplayDate(entry.end_date) : 'present' }}
                  </span>
                </div>
                <div class="mt-3">
                  <strong>
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i> Description:
                  </strong>
                  <p class="bg-gray-50 p-3 rounded-md border-l-4 border-blue-400 mt-1">{{ entry.description || 'No description provided' }}</p>
                </div>
                <div class="mt-3">
                  <strong>
                    <i class="fas fa-trophy text-blue-500 mr-2"></i> Achievements:
                  </strong>
                  <span v-if="entry.achievements && entry.achievements.includes(',')">
                    <ul class="list-disc list-inside mt-1">
                      <li v-for="(achievement, index) in entry.achievements.split(',')" :key="index" class="text-gray-700">
                        {{ achievement.trim() }}
                      </li>
                    </ul>
                  </span>
                  <span v-else class="block bg-gray-50 p-2 rounded mt-1">
                    {{ entry.achievements || 'None' }}
                  </span>
                </div>
              </div>
              <div class="absolute top-8 right-4 flex space-x-4">
                <button class="inline-flex items-center px-2 py-1 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition ease-in-out duration-150" @click="openUpdateModal(entry)">
                <i class="fas fa-edit"></i>
              </button>
              </div>
            </div>
          </div>
          <!-- Empty state removed as requested -->
        </div>
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
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. Harvard University" required>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Degree <span class="text-red-500">*</span></label>
              <input type="text" v-model="education.program"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. Bachelor of Science" required>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Field of Study <span
                  class="text-red-500">*</span></label>
              <input type="text" v-model="education.field_of_study"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. Computer Science" required>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Start Date <span class="text-red-500">*</span></label>
              <Datepicker v-model="education.start_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Select start date" required :enable-time-picker="false" :auto-apply="true"
                input-class-name="dp-custom-input" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">End Date</label>
              <Datepicker v-model="education.end_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
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
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                rows="3" placeholder="Describe your experience..."></textarea>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Achievements</label>
              <textarea v-model="education.achievements"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                rows="3" placeholder="List honors, awards, and scholarships..."
                :disabled="education.noAchievements"></textarea>
              <div class="mt-2">
                <input type="checkbox" id="no-achievements" v-model="education.noAchievements"
                  @change="handleNoAchievements" />
                <label for="no-achievements" class="text-sm text-gray-700 ml-2">No Achievements</label>
              </div>
            </div>
            <div class="flex justify-end">
              <PrimaryButton type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-md flex items-center justify-center">
                <i class="fas fa-save mr-2"></i>
                Add Education
              </PrimaryButton>
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
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. Harvard University" required>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Degree <span class="text-red-500">*</span></label>
              <input type="text" v-model="education.program"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. Bachelor of Science" required>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Field of Study <span
                  class="text-red-500">*</span></label>
              <input type="text" v-model="education.field_of_study"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. Computer Science" required>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Start Date <span class="text-red-500">*</span></label>
              <Datepicker v-model="education.start_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Select start date" required :enable-time-picker="false" :auto-apply="true"
                input-class-name="dp-custom-input" />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">End Date</label>
              <Datepicker v-model="education.end_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
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
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                rows="3" placeholder="Describe your experience..."></textarea>
            </div>

            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Achievements</label>
              <textarea v-model="education.achievements"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                rows="3" placeholder="List honors, awards, and scholarships..."
                :disabled="education.noAchievements"></textarea>
              <div class="mt-2">
                <input type="checkbox" id="no-achievements" v-model="education.noAchievements"
                  @change="handleNoAchievements" />
                <label for="no-achievements" class="text-sm text-gray-700 ml-2">No Achievements</label>
              </div>
            </div>

            <div class="flex justify-end">
              <PrimaryButton type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-md flex items-center justify-center">
                <i class="fas fa-save mr-2"></i>
                Update Education
              </PrimaryButton>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Graduate School Information (always visible) -->
    <div class="bg-white rounded-lg shadow-sm border border-blue-100 overflow-hidden transition-all duration-300 mb-6">
      <div class="flex justify-between items-center p-4 bg-gradient-to-r from-blue-50 to-white border-b border-blue-100">
        <div class="flex items-center">
          <div class="bg-blue-100 p-2 rounded-full mr-3">
            <i class="fas fa-university text-blue-600"></i>
          </div>
          <h3 class="text-lg font-semibold text-blue-800">Graduate Education</h3>
        </div>
      </div>
      <div class="p-4 transition-all duration-300">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-6 gap-y-4">
          <div>
            <label class="block text-gray-700 font-medium mb-1">School Graduated From</label>
            <div class="w-full border border-gray-300 rounded-md p-2 bg-gray-50 text-gray-700">
              {{ graduate?.institution?.institution_name || 'Not specified' }}
            </div>
          </div>
          <div>
            <label class="block text-gray-700 font-medium mb-1">Year Graduated</label>
            <div class="w-full border border-gray-300 rounded-md p-2 bg-gray-50 text-gray-700">
              {{ graduate?.school_year?.school_year_range || 'Not specified' }}
            </div>
          </div>
          <div>
            <label class="block text-gray-700 font-medium mb-1">Program Completed</label>
            <div class="w-full border border-gray-300 rounded-md p-2 bg-gray-50 text-gray-700">
              {{ graduate?.program?.name || 'Not specified' }}
            </div>
          </div>
          <div>
            <label class="block text-gray-700 font-medium mb-1">Degree Completed</label>
            <div class="w-full border border-gray-300 rounded-md p-2 bg-gray-50 text-gray-700">
              {{ graduate?.program?.degree?.type || graduate?.degree?.type || 'Not specified' }}
            </div>
          </div>
        </div>
      </div>
    </div>
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
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Input focus effects with glow */
input:focus, textarea:focus, select:focus {
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

.dp-custom-input {
  padding: 0.5rem 0.75rem;
  border-radius: 0.375rem;
  border-width: 1px;
  width: 100%;
  transition: all 0.3s ease;
}

.dp-custom-input:focus {
  outline: none;
  --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
  --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
  box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
  --tw-ring-color: rgb(37 99 235 / 0.6);
  animation: pulse 1.5s ease-in-out;
}
</style>