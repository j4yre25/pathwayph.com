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
  graduate: { type: Object, default: () => ({}) },
  educationEntries: { type: Array, default: () => [] },
  institutions: { type: Array, default: () => [] },
  educationLevels: { type: Array, default: () => [] }, // [{id,name,order_rank}]
})

// Combo-box local state (unchanged labels but mapped to new columns)
const institutionInput = ref('') // -> school_name
const degreeInput = ref('')      // -> level_of_education
const fieldInput = ref('')       // -> program
const levelInput = ref('')            // -> level_of_education (text)

const showInstSug = ref(false)
const showDegSug = ref(false)
const showProgSug = ref(false)
const showLevelSug = ref(false)

const selectedInstitution = ref(null) // {id,name}
const selectedDegree = ref(null)      // {id,type}
const selectedLevel = ref(null)       // -> sets education_id

// Helpers
const norm = s => (s || '').toString().toLowerCase().trim()

// Filtered suggestions
const filteredInstitutions = computed(() => {
  const q = norm(institutionInput.value)
  const list = props.institutions || []
  if (!q) return list.slice(0, 8)
  return list.filter(i => norm(i.name).includes(q)).slice(0, 8)
})

const filteredDegrees = computed(() => {
  if (!selectedInstitution.value) return []
  const q = norm(degreeInput.value)
  const degs = selectedInstitution.value.degrees || []
  const out = q ? degs.filter(d => norm(d.type).includes(q) || norm(d.code).includes(q)) : degs
  return out.slice(0, 8)
})

const filteredPrograms = computed(() => {
  if (!selectedInstitution.value || !selectedDegree.value) return []
  const q = norm(fieldInput.value)
  const progs = (selectedInstitution.value.programs || []).filter(p => p.degree_id === selectedDegree.value.id)
  const out = q ? progs.filter(p => norm(p.name).includes(q)) : progs
  return out.slice(0, 10)
})

const filteredLevels = computed(() => {
  const q = norm(levelInput.value)
  const list = props.educationLevels || []
  if (!q) return list.slice(0, 10)
  return list.filter(l => norm(l.name).includes(q)).slice(0, 10)
})

// Keep form fields in sync with combo inputs (UPDATED mappings)
watch(institutionInput, (val) => {
  education.value.school_name = val || ''              // was education
  // clear dependant selections if text no longer matches selected
  const sel = selectedInstitution.value
  if (!sel || norm(sel.name) !== norm(val)) {
    selectedInstitution.value = null
    degreeInput.value = ''
    fieldInput.value = ''
    selectedDegree.value = null
    education.value.level_of_education = ''            // was program
    education.value.program = ''                       // was field_of_study
  }
})

watch(degreeInput, (val) => {
  education.value.level_of_education = val || ''       // was program
  const sel = selectedDegree.value
  if (!sel || norm(sel.type) !== norm(val)) {
    selectedDegree.value = null
    fieldInput.value = ''
    education.value.program = ''                       // was field_of_study
  }
})

watch(fieldInput, (val) => {
  education.value.program = val || ''                  // was field_of_study
})

watch(levelInput, (val) => {
  education.value.level_of_education = val || ''
  const sel = selectedLevel.value
  if (!sel || norm(sel.name) !== norm(val)) {
    selectedLevel.value = null
    education.value.education_id = null
  }
})

// Selection handlers (update mapped fields)
function selectInstitution(inst) {
  selectedInstitution.value = inst
  institutionInput.value = inst.name
  selectedDegree.value = null
  degreeInput.value = ''
  fieldInput.value = ''
  education.value.level_of_education = ''
  education.value.program = ''
  showInstSug.value = false
}

function selectDegree(deg) {
  selectedDegree.value = deg
  degreeInput.value = deg.type           // stored as level_of_education text
  fieldInput.value = ''
  education.value.program = ''
  showDegSug.value = false
}

function selectProgram(prog) {
  fieldInput.value = prog.name           // stored as program
  education.value.program = prog.name
  showProgSug.value = false
}

function selectLevel(lvl) {
  selectedLevel.value = lvl
  levelInput.value = lvl.name
  education.value.level_of_education = lvl.name
  education.value.education_id = lvl.id
  showLevelSug.value = false
}

// Initialize inputs when opening modals
const openAddEducationModal = () => {
  resetEducation()
  isAddEducationModalOpen.value = true
  levelInput.value = ''
  selectedLevel.value = null
  institutionInput.value = ''
  degreeInput.value = ''
  fieldInput.value = ''
  selectedInstitution.value = null
  selectedDegree.value = null
}

const openUpdateEducationModal = (entry) => {
  education.value = {
    id: entry.id,
    education_id: entry.education_id || null,
    school_name: entry.school_name || '',               // UPDATED
    level_of_education: entry.level_of_education || '', // UPDATED
    program: entry.program || '',                       // UPDATED
    start_date: entry.start_date ? new Date(entry.start_date) : null,
    end_date: entry.end_date ? new Date(entry.end_date) : null,
    description: entry.description || '',
    is_current: !entry.end_date ? true : !!entry.is_current,
    achievement: entry.achievement || '',               // UPDATED (singular)
    noAchievements: !entry.achievement
  }
  levelInput.value = education.value.level_of_education
  selectedLevel.value = null
  institutionInput.value = education.value.school_name
  degreeInput.value = ''   // UI helper only
  fieldInput.value = education.value.program
  selectedInstitution.value = null
  selectedDegree.value = null
  isUpdateEducationModalOpen.value = true
}

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

// State for form (UPDATED keys)
const education = ref({
  education_id: null,            // optional FK to educations.id (levels), keep null if not used
  school_name: '',               // from Institution combo
  level_of_education: '',        // from Degree combo
  program: '',                   // from Field of Study combo
  start_date: null,
  end_date: null,
  description: '',
  is_current: false,
  achievement: '',               // singular
  noAchievements: false,
})

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

// Add Education (UPDATED payload and validation)
const addEducation = () => {
  if (
    !education.value.school_name?.trim() ||
    !education.value.level_of_education?.trim() ||
    !education.value.program?.trim() ||
    !education.value.start_date
  ) {
    errorMessage.value = 'Please fill in all required fields.'
    modals.isErrorOpen = true
    return
  }

  const startDate = education.value.start_date instanceof Date
    ? education.value.start_date
    : new Date(education.value.start_date)
  if (isNaN(startDate.getTime())) {
    errorMessage.value = 'Please enter a valid start date.'
    modals.isErrorOpen = true
    return
  }

  if (!education.value.is_current && education.value.end_date) {
    const endDate = education.value.end_date instanceof Date
      ? education.value.end_date
      : new Date(education.value.end_date)
    if (isNaN(endDate.getTime()) || endDate < startDate) {
      errorMessage.value = 'End date must be a valid date and not before start date.'
      modals.isErrorOpen = true
      return
    }
  }

  // Duplicate check with new fields
  const isDuplicate = educationEntries.value.some(entry =>
    (entry.school_name || '').trim().toLowerCase() === education.value.school_name.trim().toLowerCase() &&
    (entry.level_of_education || '').trim().toLowerCase() === education.value.level_of_education.trim().toLowerCase() &&
    (entry.program || '').trim().toLowerCase() === education.value.program.trim().toLowerCase()
  )
  if (isDuplicate) {
    modals.isDuplicateOpen = true
    return
  }

  const educationForm = useForm({
    education_id: education.value.education_id, // optional; keep null if not selecting from levels
    school_name: education.value.school_name.trim(),
    level_of_education: education.value.level_of_education.trim(),
    program: education.value.program.trim(),
    start_date: formatDate(education.value.start_date),
    end_date: education.value.is_current ? null : formatDate(education.value.end_date),
    description: education.value.description,
    is_current: education.value.is_current,
    achievement: education.value.noAchievements ? null : education.value.achievement,
  })

  educationForm.post(route('profile.education.add'), {
    onSuccess: () => {
      emit('refresh-education')
      isAddEducationModalOpen.value = false
      resetEducation()
      errorMessage.value = 'Education added successfully!'
      modals.isSuccessOpen = true
    },
    onError: (errors) => {
      errorMessage.value = errors.message || 'An error occurred while adding education. Please try again.'
      modals.isErrorOpen = true
    },
  })
}

// Update Education (UPDATED payload and validation)
const updateEducation = () => {
  if (!education.value.id) {
    errorMessage.value = 'Education ID is missing. Please try again.'
    modals.isErrorOpen = true
    return
  }

  if (
    !education.value.school_name?.trim() ||
    !education.value.level_of_education?.trim() ||
    !education.value.program?.trim() ||
    !education.value.start_date
  ) {
    errorMessage.value = 'Please fill in all required fields.'
    modals.isErrorOpen = true
    return
  }

  const startDate = education.value.start_date instanceof Date
    ? education.value.start_date
    : new Date(education.value.start_date)
  if (isNaN(startDate.getTime())) {
    errorMessage.value = 'Please enter a valid start date.'
    modals.isErrorOpen = true
    return
  }

  if (!education.value.is_current && education.value.end_date) {
    const endDate = education.value.end_date instanceof Date
      ? education.value.end_date
      : new Date(education.value.end_date)
    if (isNaN(endDate.getTime()) || endDate < startDate) {
      errorMessage.value = 'End date must be a valid date and not before start date.'
      modals.isErrorOpen = true
      return
    }
  }

  const isDuplicate = educationEntries.value.some(entry =>
    entry.id !== education.value.id &&
    (entry.school_name || '').trim().toLowerCase() === education.value.school_name.trim().toLowerCase() &&
    (entry.level_of_education || '').trim().toLowerCase() === education.value.level_of_education.trim().toLowerCase() &&
    (entry.program || '').trim().toLowerCase() === education.value.program.trim().toLowerCase()
  )
  if (isDuplicate) {
    modals.isDuplicateOpen = true
    return
  }

  const educationForm = useForm({
    education_id: education.value.education_id,
    school_name: education.value.school_name.trim(),
    level_of_education: education.value.level_of_education.trim(),
    program: education.value.program.trim(),
    start_date: formatDate(education.value.start_date),
    end_date: education.value.is_current ? null : formatDate(education.value.end_date),
    description: education.value.description,
    is_current: education.value.is_current,
    achievement: education.value.noAchievements ? null : education.value.achievement,
  })

  educationForm.put(route('profile.education.update', { id: education.value.id }), {
    onSuccess: () => {
      emit('refresh-education')
      closeUpdateEducationModal()
      errorMessage.value = 'Education updated successfully!'
      modals.isSuccessOpen = true
    },
    onError: (errors) => {
      errorMessage.value = errors.message || 'An error occurred while updating the education entry. Please try again.'
      modals.isErrorOpen = true
    },
  })
}

// Reset (UPDATED keys)
const resetEducation = () => {
  education.value = {
    education_id: null,
    school_name: '',
    level_of_education: '',
    program: '',
    start_date: null,
    end_date: null,
    description: '',
    is_current: false,
    achievement: '',
    noAchievements: false,
  }
  institutionInput.value = ''
  degreeInput.value = ''
  fieldInput.value = ''
  levelInput.value = ''
}

// Close handlers
const closeAddEducationModal = () => {
  isAddEducationModalOpen.value = false;
  resetEducation();
};

const closeUpdateEducationModal = () => {
  isUpdateEducationModalOpen.value = false;
  resetEducation();
};

// Open update modal
const openUpdateModal = (entry) => {
  openUpdateEducationModal(entry);
};

// Handle achievements toggle
const handleNoAchievements = () => {
  if (education.value.noAchievements) {
    education.value.achievement = '';
  }
};
</script>

<template>
  <div v-if="activeSection === 'education'" class="w-full">
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
          <PrimaryButton type="button"
            class="bg-green-500 hover:bg-green-600 transition-colors text-white px-4 py-2 rounded-md"
            @click="modals.isSuccessOpen = false">
            Close
          </PrimaryButton>
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
          <DangerButton type="button" class="bg-red-500 hover:bg-red-600 transition-colors text-white px-4 py-2 rounded-md"
            @click="modals.isErrorOpen = false">
            Close
          </DangerButton>
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
                <!-- Replace title and subtitle block: -->
                <div class="border-b pb-2">
                  <h2 class="text-xl font-bold text-blue-900">{{ entry.school_name || 'Unknown Institution' }}</h2>
                  <p class="text-gray-700">
                    <span class="font-medium">{{ entry.level_of_education || '—' }}</span>
                    <span v-if="entry.program"> — <span class="px-2 py-1 bg-blue-50 text-blue-700 text-sm rounded-full">{{ entry.program }}</span></span>
                  </p>
                </div>
                <div class="flex items-center text-gray-600 mt-2">
                  <i class="far fa-calendar-alt text-blue-600 mr-2"></i>
                  <span>
                    {{ formatDisplayDate(entry.start_date) }} - {{ entry.end_date ?
                      formatDisplayDate(entry.end_date) : 'present' }}
                  </span>
                </div>
                <!-- Description and Achievements updated -->
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
                  <span v-if="entry.achievement && entry.achievement.includes(',')">
                    <ul class="list-disc list-inside mt-1">
                      <li v-for="(a, idx) in entry.achievement.split(',')" :key="idx" class="text-gray-700">
                        {{ a.trim() }}
                      </li>
                    </ul>
                  </span>
                  <span v-else class="block bg-gray-50 p-2 rounded mt-1">
                    {{ entry.achievement || 'None' }}
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
            <!-- Level of Education: new combo box -->
            <div class="mb-4 relative">
              <label class="block text-gray-700 font-medium mb-2">Level of Education <span class="text-red-500">*</span></label>
              <input
                type="text"
                v-model="levelInput"
                @focus="showLevelSug = true"
                @blur="() => setTimeout(() => showLevelSug = false, 150)"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Select or type level (e.g., Bachelor's, Master's)"
                required
              />
              <ul
                v-if="showLevelSug && filteredLevels.length"
                class="absolute z-20 mt-1 w-full bg-white border rounded shadow max-h-48 overflow-auto text-sm"
              >
                <li
                  v-for="lvl in filteredLevels"
                  :key="lvl.id"
                  @mousedown.prevent="selectLevel(lvl)"
                  class="px-3 py-2 hover:bg-blue-50 cursor-pointer"
                >
                  {{ lvl.name }}
                </li>
              </ul>
              <div v-if="showLevelSug && !filteredLevels.length" class="text-xs text-gray-400 mt-1">
                No matches. Press Enter to use “{{ levelInput }}”.
              </div>
            </div>

            <!-- Institution: suggestive dropdown -->
            <div class="mb-4 relative">
              <label class="block text-gray-700 font-medium mb-2">Institution <span class="text-red-500">*</span></label>
              <input
                type="text"
                v-model="institutionInput"
                @focus="showInstSug = true"
                @blur="() => setTimeout(() => showInstSug = false, 150)"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Type to search or enter a new institution"
                required
              />
              <ul
                v-if="showInstSug && filteredInstitutions.length"
                class="absolute z-20 mt-1 w-full bg-white border rounded shadow max-h-48 overflow-auto text-sm"
              >
                <li
                  v-for="i in filteredInstitutions"
                  :key="i.id"
                  @mousedown.prevent="selectInstitution(i)"
                  class="px-3 py-2 hover:bg-blue-50 cursor-pointer"
                >
                  {{ i.name }}
                </li>
              </ul>
              <div v-if="showInstSug && !filteredInstitutions.length" class="text-xs text-gray-400 mt-1">
                No matches. Press Enter to use “{{ institutionInput }}”.
              </div>
            </div>

            <!-- Degree: suggestive dropdown filtered by institution (or free text) -->
            <div class="mb-4 relative">
              <label class="block text-gray-700 font-medium mb-2">Degree <span class="text-red-500">*</span></label>
              <input
                type="text"
                v-model="degreeInput"
                @focus="showDegSug = true"
                @blur="() => setTimeout(() => showDegSug = false, 150)"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                :placeholder="selectedInstitution ? 'Type to search degree' : 'Type a degree (select institution to see suggestions)'"
                required
              />
              <ul
                v-if="showDegSug && filteredDegrees.length"
                class="absolute z-20 mt-1 w-full bg-white border rounded shadow max-h-48 overflow-auto text-sm"
              >
                <li
                  v-for="d in filteredDegrees"
                  :key="d.id"
                  @mousedown.prevent="selectDegree(d)"
                  class="px-3 py-2 hover:bg-blue-50 cursor-pointer"
                >
                  {{ d.type }}
                </li>
              </ul>
            </div>

            <!-- Field of Study: suggestive dropdown filtered by degree (or free text) -->
            <div class="mb-4 relative">
              <label class="block text-gray-700 font-medium mb-2">Field of Study <span class="text-red-500">*</span></label>
              <input
                type="text"
                v-model="fieldInput"
                @focus="showProgSug = true"
                @blur="() => setTimeout(() => showProgSug = false, 150)"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                :placeholder="selectedDegree ? 'Type to search program' : 'Type a field (select degree to see suggestions)'"
                required
              />
              <ul
                v-if="showProgSug && filteredPrograms.length"
                class="absolute z-20 mt-1 w-full bg-white border rounded shadow max-h-48 overflow-auto text-sm"
              >
                <li
                  v-for="p in filteredPrograms"
                  :key="p.id"
                  @mousedown.prevent="selectProgram(p)"
                  class="px-3 py-2 hover:bg-blue-50 cursor-pointer"
                >
                  {{ p.name }}
                </li>
              </ul>
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
              <textarea v-model="education.achievement"
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
            <!-- Institution: suggestive dropdown -->
            <div class="mb-4 relative">
              <label class="block text-gray-700 font-medium mb-2">Institution <span class="text-red-500">*</span></label>
              <input
                type="text"
                v-model="institutionInput"
                @focus="showInstSug = true"
                @blur="() => setTimeout(() => showInstSug = false, 150)"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Type to search or enter a new institution"
                required
              />
              <ul
                v-if="showInstSug && filteredInstitutions.length"
                class="absolute z-20 mt-1 w-full bg-white border rounded shadow max-h-48 overflow-auto text-sm"
              >
                <li
                  v-for="i in filteredInstitutions"
                  :key="i.id"
                  @mousedown.prevent="selectInstitution(i)"
                  class="px-3 py-2 hover:bg-blue-50 cursor-pointer"
                >
                  {{ i.name }}
                </li>
              </ul>
              <div v-if="showInstSug && !filteredInstitutions.length" class="text-xs text-gray-400 mt-1">
                No matches. Press Enter to use “{{ institutionInput }}”.
              </div>
            </div>

            <!-- Degree: suggestive dropdown filtered by institution (or free text) -->
            <div class="mb-4 relative">
              <label class="block text-gray-700 font-medium mb-2">Degree <span class="text-red-500">*</span></label>
              <input
                type="text"
                v-model="degreeInput"
                @focus="showDegSug = true"
                @blur="() => setTimeout(() => showDegSug = false, 150)"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                :placeholder="selectedInstitution ? 'Type to search degree' : 'Type a degree (select institution to see suggestions)'"
                required
              />
              <ul
                v-if="showDegSug && filteredDegrees.length"
                class="absolute z-20 mt-1 w-full bg-white border rounded shadow max-h-48 overflow-auto text-sm"
              >
                <li
                  v-for="d in filteredDegrees"
                  :key="d.id"
                  @mousedown.prevent="selectDegree(d)"
                  class="px-3 py-2 hover:bg-blue-50 cursor-pointer"
                >
                  {{ d.type }}
                </li>
              </ul>
            </div>

            <!-- Field of Study: suggestive dropdown filtered by degree (or free text) -->
            <div class="mb-4 relative">
              <label class="block text-gray-700 font-medium mb-2">Field of Study <span class="text-red-500">*</span></label>
              <input
                type="text"
                v-model="fieldInput"
                @focus="showProgSug = true"
                @blur="() => setTimeout(() => showProgSug = false, 150)"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                :placeholder="selectedDegree ? 'Type to search program' : 'Type a field (select degree to see suggestions)'"
                required
              />
              <ul
                v-if="showProgSug && filteredPrograms.length"
                class="absolute z-20 mt-1 w-full bg-white border rounded shadow max-h-48 overflow-auto text-sm"
              >
                <li
                  v-for="p in filteredPrograms"
                  :key="p.id"
                  @mousedown.prevent="selectProgram(p)"
                  class="px-3 py-2 hover:bg-blue-50 cursor-pointer"
                >
                  {{ p.name }}
                </li>
              </ul>
            </div>

            <!-- Level of Education: new combo box -->
            <div class="mb-4 relative">
              <label class="block text-gray-700 font-medium mb-2">Level of Education <span class="text-red-500">*</span></label>
              <input
                type="text"
                v-model="levelInput"
                @focus="showLevelSug = true"
                @blur="() => setTimeout(() => showLevelSug = false, 150)"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Select or type level (e.g., Bachelor's, Master's)"
                required
              />
              <ul
                v-if="showLevelSug && filteredLevels.length"
                class="absolute z-20 mt-1 w-full bg-white border rounded shadow max-h-48 overflow-auto text-sm"
              >
                <li
                  v-for="lvl in filteredLevels"
                  :key="lvl.id"
                  @mousedown.prevent="selectLevel(lvl)"
                  class="px-3 py-2 hover:bg-blue-50 cursor-pointer"
                >
                  {{ lvl.name }}
                </li>
              </ul>
              <div v-if="showLevelSug && !filteredLevels.length" class="text-xs text-gray-400 mt-1">
                No matches. Press Enter to use “{{ levelInput }}”.
              </div>
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
              <textarea v-model="education.achievement"
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