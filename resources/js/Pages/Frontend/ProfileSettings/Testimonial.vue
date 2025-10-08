<script setup>
import { ref, computed, onMounted, watch, reactive } from 'vue';
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
import axios from 'axios';
import { Inertia } from '@inertiajs/inertia';



// Define props
const props = defineProps({
  activeSection: {
    type: String,
    default: 'testimonials'
  },
  testimonialEntries: {
    type: Array,
    default: () => []
  },
  archivedTestimonialEntries: {
    type: Array,
    default: () => []
  },

  companies: { type: Array, default: () => [] },
  institutions: { type: Array, default: () => [] },
});

console.log(props.companies)
const selectedType = ref(''); // 'company' or 'institution'

const emit = defineEmits(['close-all-modals', 'reset-all-states']);
// State variables
const testimonialEntries = ref(props.testimonialEntries || []);
const archivedTestimonialEntries = ref(props.archivedTestimonialEntries || []);
const showArchivedTestimonial = ref(false);
const isAddTestimonialsModalOpen = ref(false);
const isUpdateTestimonialsModalOpen = ref(false);
const currentTestimonialId = ref(null);
const previewImage = ref(null);

// Modal states
const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const isDuplicateModalOpen = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Testimonial form
const companies = ref([]);
const institutions = ref([]);
const testimonials = useForm({
  company_id: null,
  company_name: '',
  institution_id: null,
  institution_name: '',
  content: '',
  file: null,
});


console.log('Props companies:', props.companies);
console.log('Props institutions:', props.institutions);

// File upload handling
const handleTestimonialFileUpload = (event) => {
  testimonials.file = event.target.files[0];
};

const handleRequestFileUpload = (event) => {
  testimonialRequestForm.file = event.target.files[0];
};

// Toggle archived testimonials
const toggleArchivedTestimonial = () => {
  showArchivedTestimonial.value = !showArchivedTestimonial.value;
};

// Close add testimonial modal
const closeAddTestimonialsModal = () => {
  isAddTestimonialsModalOpen.value = false;
  testimonials.reset();
  testimonials.clearErrors();
};

// Close update testimonial modal
const closeUpdateTestimonialsModal = () => {
  isUpdateTestimonialsModalOpen.value = false;
  testimonials.reset();
  testimonials.clearErrors();
};

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
  if (type === 'delete') {
    confirmModal.message = 'Are you sure you want to delete this testimonial? This action cannot be undone.';
    confirmModal.confirmLabel = 'Delete';
    confirmModal.confirmAction = () => {
      try {
        useForm({}).delete(route('profile.testimonials.remove', entry.id), {
          onSuccess: () => {
            successMessage.value = 'Testimonial deleted successfully!';
            isSuccessModalOpen.value = true;
            closeConfirm();
            fetchTestimonials();
            Inertia.reload();
          },
          onError: () => {
            errorMessage.value = 'Failed to delete testimonial. Please try again.';
            isErrorModalOpen.value = true;
            closeConfirm();
          }
        });
      } catch (err) {
        errorMessage.value = 'Unexpected error during deletion.';
        isErrorModalOpen.value = true;
        closeConfirm();
        console.error('Error in confirmAction:', err);
      }
    };
  } else if (type === 'archive') {
    confirmModal.message = 'Are you sure you want to archive this testimonial?';
    confirmModal.confirmLabel = 'Archive';
    confirmModal.confirmAction = () => {
      useForm({}).put(route('profile.testimonials.archive', entry.id), {
        onSuccess: () => {
          successMessage.value = 'Testimonial archived successfully!';
          isSuccessModalOpen.value = true;
          closeConfirm();
          fetchTestimonials();
          Inertia.reload();
        },
        onError: () => {
          errorMessage.value = 'Failed to archive testimonial. Please try again.';
          isErrorModalOpen.value = true;
          closeConfirm();
        }
      });
    };
  } else if (type === 'unarchive') {
    confirmModal.message = 'Restore this archived testimonial?';
    confirmModal.confirmLabel = 'Restore';
    confirmModal.confirmAction = () => {
      useForm({}).post(route('profile.testimonials.unarchive', entry.id), {
        onSuccess: () => {
          successMessage.value = 'Testimonial restored successfully!';
          isSuccessModalOpen.value = true;
          closeConfirm();
          fetchTestimonials();
          Inertia.reload();
        },
        onError: () => {
          errorMessage.value = 'Failed to restore testimonial. Please try again.';
          isErrorModalOpen.value = true;
          closeConfirm();
        }
      });
    };
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

function deleteTestimonial(entry) {
  openConfirm('delete', entry);
}
function archiveTestimonial(entry) {
  openConfirm('archive', entry);
}
function unarchiveTestimonial(entry) {
  openConfirm('unarchive', entry);
}

// Edit testimonial
const editTestimonial = (testimonial) => {
  testimonials.id = testimonial.id;
  testimonials.company_id = testimonial.company_id;
  testimonials.company_name = testimonial.company_name;
  testimonials.institution_id = testimonial.institution_id;
  testimonials.institution_name = testimonial.institution_name;
  testimonials.content = testimonial.content;
  testimonials.file = null;
  companyInput.value = testimonial.company_name || '';
  institutionInput.value = testimonial.institution_name || '';
  // Set selectedType for update modal
  if (testimonial.company_name) {
    selectedType.value = 'company';
  } else if (testimonial.institution_name) {
    selectedType.value = 'institution';
  } else {
    selectedType.value = '';
  }
  isUpdateTestimonialsModalOpen.value = true;
};

// Add testimonial function
const addTestimonials = () => {
  testimonials.post(route('profile.testimonials.add'), {
    forceFormData: true,
    onSuccess: () => {
      closeAddTestimonialsModal();
      successMessage.value = 'Testimonial added successfully!';
      isSuccessModalOpen.value = true;
      fetchTestimonials();
      Inertia.reload();
    },
    onError: (errors) => {
      errorMessage.value = 'Failed to add testimonial. Please check the form and try again.';
      isErrorModalOpen.value = true;
    }
  });
};

onMounted(async () => {
  const res = await axios.get('/api/companies-institutions');
  companies.value = res.data.companies;
  institutions.value = res.data.institutions;
});

// Watch for changes to set ID if the name matches, or clear ID if not
function onCompanyInput(e) {
  const match = companies.value.find(c => c.name === e.target.value);
  testimonials.company_id = match ? match.id : null;
  testimonials.company_name = e.target.value;
}
function onInstitutionInput(e) {
  const match = institutions.value.find(i => i.name === e.target.value);
  testimonials.institution_id = match ? match.id : null;
  testimonials.institution_name = e.target.value;
}

// Send request to company instead of adding testimonial
const sendTestimonialRequest = () => {
  testimonialRequestForm.post(route('profile.testimonials.request'), {
    forceFormData: true,
    onSuccess: () => {
      closeAddTestimonialsModal();
      successMessage.value = 'Testimonial request sent to company!';
      isSuccessModalOpen.value = true;
    },
    onError: (errors) => {
      errorMessage.value = 'Failed to send testimonial request. Please check the form and try again.';
      isErrorModalOpen.value = true;
    }
  });
};

// Update testimonial function
const updateTestimonials = () => {
  testimonials.company_name = companyInput.value;
  testimonials.institution_name = institutionInput.value;

  console.log('Updating testimonial:', { ...testimonials });

  testimonials.post(route('profile.testimonials.update', testimonials.id), {
    forceFormData: true,
    _method: 'PUT',
    onSuccess: () => {
      closeUpdateTestimonialsModal();
      successMessage.value = 'Testimonial updated successfully!';
      isSuccessModalOpen.value = true;
      fetchTestimonials();
      Inertia.reload();
    },
    onError: (errors) => {
      console.error(errors);
      errorMessage.value = 'Failed to update testimonial. Please check the form and try again.';
      isErrorModalOpen.value = true;
    }
  });
};

// Archive testimonial function
const archiveTestimonialForm = useForm({
  is_archived: true

});



// Unarchive testimonial function
const unarchiveTestimonialForm = useForm({
  is_archived: false
});




// Delete testimonial function
const deleteTestimonialForm = useForm({});

const removeTestimonials = (testimonialId) => {
  if (confirm('Are you sure you want to delete this testimonial?')) {
    testimonials.delete(route('profile.testimonials.remove', testimonialId), {
      onSuccess: () => {
        successMessage.value = 'Testimonial deleted successfully!';
        isSuccessModalOpen.value = true;
        fetchTestimonials();
      },
      onError: () => {
        errorMessage.value = 'Failed to delete testimonial. Please try again.';
        isErrorModalOpen.value = true;
      }
    });
  }
};

// Fetch testimonials function
const fetchTestimonialsForm = useForm({});

const fetchTestimonials = () => {
  router.reload({ only: ['testimonialsEntries', 'archivedTestimonialsEntries'] });
};

// Close success modal
const closeSuccessModal = () => {
  isSuccessModalOpen.value = false;
};

// Close error modal
const closeErrorModal = () => {
  isErrorModalOpen.value = false;
};

// Close duplicate modal
const closeDuplicateModal = () => {
  isDuplicateModalOpen.value = false;
};

const companyInput = ref('');
const institutionInput = ref('');
const showCompanySug = ref(false);
const showInstitutionSug = ref(false);

const filteredCompanies = computed(() => {
  const q = (companyInput.value || '').toLowerCase().trim();
  if (!q) return props.companies.slice(0, 8);
  return props.companies.filter(c => c.name.toLowerCase().includes(q)).slice(0, 8);
});

const filteredInstitutions = computed(() => {
  const q = (institutionInput.value || '').toLowerCase().trim();
  if (!q) return props.institutions.slice(0, 8);
  return props.institutions.filter(i => i.name.toLowerCase().includes(q)).slice(0, 8);
});

function selectCompany(company) {
  companyInput.value = company.name;
  testimonials.company_id = company.id;
  testimonials.company_name = company.name;
  showCompanySug.value = false;
}
function selectInstitution(inst) {
  institutionInput.value = inst.name;
  testimonials.institution_id = inst.id;
  testimonials.institution_name = inst.name;
  showInstitutionSug.value = false;
}

// Keep testimonial fields in sync with input
watch(companyInput, (val) => {
  if (val && val.trim() !== '') {
    selectedType.value = 'company';
    institutionInput.value = '';
    testimonials.institution_id = null;
    testimonials.institution_name = '';
  } else if (!institutionInput.value) {
    selectedType.value = '';
  }
});
watch(institutionInput, (val) => {
  if (val && val.trim() !== '') {
    selectedType.value = 'institution';
    companyInput.value = '';
    testimonials.company_id = null;
    testimonials.company_name = '';
  } else if (!companyInput.value) {
    selectedType.value = '';
  }
});
// Fetch testimonials on component mount
onMounted(() => {
  fetchTestimonials();
});
</script>

<template>
  <div v-if="activeSection === 'testimonials'" class="flex flex-col lg:flex-row">
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
            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            OK
          </button>
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
          {{ confirmModal.confirmLabel }} Testimonial
        </h3>
        <p class="text-center text-gray-600 mb-4">{{ confirmModal.message }}</p>
        <div class="mt-6 flex justify-center space-x-2">
          <button type="button" @click="closeConfirm"
            class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition duration-200">
            Cancel
          </button>
          <button v-if="confirmModal.type === 'delete'" type="button"
            @click="() => { try { confirmModal.confirmAction && confirmModal.confirmAction(); } catch (e) { errorMessage.value = 'Unexpected error.'; isErrorModalOpen.value = true; } }"
            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition duration-200">
            Delete
          </button>
          <button v-else type="button"
            :class="confirmModal.type === 'archive' ? 'bg-amber-500 hover:bg-amber-600' : 'bg-green-600 hover:bg-green-700'"
            class="text-white px-4 py-2 rounded transition duration-200" @click="confirmModal.confirmAction">{{
              confirmModal.confirmLabel }}</button>
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
            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            OK
          </button>
        </div>
      </div>
    </Modal>

    <!-- Duplicate Modal -->
    <Modal :modelValue="isDuplicateModalOpen" @close="closeDuplicateModal">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-yellow-100 rounded-full p-2">
            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
              </path>
            </svg>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center mb-4">This testimonial already exists.</h3>
        <div class="flex justify-center">
          <button @click="closeDuplicateModal"
            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            OK
          </button>
        </div>
      </div>
    </Modal>
    <div class="w-full mb-6">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold mb-4">Testimonials</h1>
        <div class="flex space-x-4">
          <PrimaryButton
            class="bg-indigo-600 text-white px-4 py-2 rounded flex items-center hover:bg-indigo-700 transition-colors"
            @click="isAddTestimonialsModalOpen = true">
            <i class="fas fa-plus mr-2"></i>
            Add Testimonial
          </PrimaryButton>
          <button
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded flex items-center transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 cursor-pointer"
            @click="toggleArchivedTestimonial">
            <i class="fas" :class="showArchivedTestimonial ? 'fa-eye-slash' : 'fa-eye'"></i>
            <span class="ml-2">{{ showArchivedTestimonial ? 'Hide Archived' : 'Show Archived' }}</span>
          </button>
        </div>
      </div>
      <p class="text-gray-600 mt-2 mb-6">My Testimonials in Institution and Companies</p>

      <!-- Testimonials Entries -->
      <div>
        <h2 class="text-lg font-medium mb-4">Active Testimonials</h2>
        <div v-if="testimonialEntries.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="entry in testimonialEntries" :key="entry.id" class="...">
            <h2 class="text-xl font-bold text-gray-900">{{ entry.author }}</h2>
            <p class="text-gray-600 font-medium">
              {{ entry.company_name || entry.institution_name || 'N/A' }}
            </p>
            <p class="mt-3 text-gray-700 italic">"{{ entry.content }}"</p>
            <div v-if="entry.file" class="md:w-1/3">
              <img :src="`/storage/${entry.file}`" :alt="entry.author" class="w-full h-auto rounded-lg shadow-sm" />
            </div>
            <div class="flex justify-end space-x-2 mt-3">
              <button class="text-gray-600 hover:text-indigo-600" @click="editTestimonial(entry)">
                <i class="fas fa-pen"></i>
              </button>
              <button
                class="inline-flex items-center px-2 py-1 bg-amber-100 border border-amber-300 rounded-md font-semibold text-xs text-amber-700 hover:bg-amber-200 focus:outline-none focus:ring-2 focus:ring-amber-500 transition ease-in-out duration-150"
                @click="archiveTestimonial(entry)">
                <i class="fas fa-archive"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- If no testimonials entries exist -->
        <div v-else class="bg-white p-8 rounded-lg shadow">
          <p class="text-gray-600">No testimonial entries added yet.</p>
        </div>
      </div>

      <!-- Archived Testimonials Entries -->
      <div v-if="showArchivedTestimonial" class="mt-8">
        <h2 class="text-lg font-medium mb-4">Archived Testimonials</h2>
        <div v-if="archivedTestimonialEntries.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="entry in archivedTestimonialEntries" :key="entry.id"
            class="bg-gray-50 rounded-lg shadow-md p-4 space-y-3 border border-gray-200 relative">
            <div class="opacity-75">
              <div class="border-b pb-2">
                <h3 class="text-lg font-semibold">{{ entry.author }}</h3>
                <span class="text-sm text-gray-600">{{ entry.company_name || entry.institution_name || 'N/A' }}</span>
              </div>
              <div class="space-y-1">
                <p class="text-sm italic">"{{ entry.content }}"</p>
              </div>
              <div v-if="entry.file" class="mt-3">
                <img :src="`/storage/${entry.file}`" :alt="entry.graduate_testimonial_author"
                  class="max-w-full h-auto rounded-lg shadow" />
              </div>
            </div>
            <div class="absolute top-2 right-2 flex space-x-2">
              <button
                class="inline-flex items-center px-2 py-1 bg-green-100 border border-green-300 rounded-md font-semibold text-xs text-green-700 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 transition ease-in-out duration-150"
                @click="unarchiveTestimonial(entry)">
                <i class="fas fa-undo"></i>
              </button>
              <button
                class="inline-flex items-center px-2 py-1 bg-red-100 border border-red-300 rounded-md font-semibold text-xs text-red-700 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 transition ease-in-out duration-150"
                @click="deleteTestimonial(entry)">
                <i class="fas fa-trash"></i>
              </button>
            </div>
            <div class="absolute top-2 left-2 bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded">
              Archived
            </div>
          </div>
        </div>

        <!-- If no archived testimonials exist -->
        <div v-else class="bg-white p-8 rounded-lg shadow">
          <p class="text-gray-600">No archived testimonials found.</p>
        </div>
      </div>
    </div>

    <!-- Add Testimonials Modal -->
    <div v-if="isAddTestimonialsModalOpen"
      class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">Add Testimonial</h2>
          <button class="text-gray-500 hover:text-gray-700" @click="closeAddTestimonialsModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <p class="text-gray-600 mb-4">Share your testimonial for a company or institution</p>
        <form @submit.prevent="addTestimonials">
          <div class="mb-4">
            <InputLabel value="Testimonial For" />
            <select v-model="selectedType" class="w-full px-3 py-2 border rounded-lg">
              <option value="">Select type</option>
              <option value="company">Company</option>
              <option value="institution">Institution</option>
            </select>
          </div>

          <div v-if="selectedType == 'company'" class="mb-4 relative">
            <InputLabel for="company" value="Company" />
            <input id="company" type="text" v-model="companyInput" @focus="showCompanySug = true"
              @blur="() => setTimeout(() => showCompanySug = false, 150)"
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
              placeholder="Type to search or enter a new company" autocomplete="off" />
            <ul v-if="showCompanySug && filteredCompanies.length"
              class="absolute z-20 mt-1 w-full bg-white border rounded shadow max-h-48 overflow-auto text-sm">
              <li v-for="c in filteredCompanies" :key="c.id" @mousedown.prevent="selectCompany(c)"
                class="px-3 py-2 hover:bg-indigo-50 cursor-pointer">
                {{ c.name }}
              </li>
            </ul>
            <div v-if="showCompanySug && !filteredCompanies.length" class="text-xs text-gray-400 mt-1">
              No matches. Press Enter to use “{{ companyInput }}”.
            </div>
          </div>

          <!-- Institution Suggestive Input -->
          <div v-if="selectedType == 'institution'" class="mb-4 relative">
            <InputLabel for="institution" value="Institution" />
            <input id="institution" type="text" v-model="institutionInput" @focus="showInstitutionSug = true"
              @blur="() => setTimeout(() => showInstitutionSug = false, 150)"
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
              placeholder="Type to search or enter a new institution" autocomplete="off" />
            <ul v-if="showInstitutionSug && filteredInstitutions.length"
              class="absolute z-20 mt-1 w-full bg-white border rounded shadow max-h-48 overflow-auto text-sm">
              <li v-for="i in filteredInstitutions" :key="i.id" @mousedown.prevent="selectInstitution(i)"
                class="px-3 py-2 hover:bg-indigo-50 cursor-pointer">
                {{ i.name }}
              </li>
            </ul>
            <div v-if="showInstitutionSug && !filteredInstitutions.length" class="text-xs text-gray-400 mt-1">
              No matches. Press Enter to use “{{ institutionInput }}”.
            </div>
          </div>
          <div class="mb-4">
            <InputLabel for="content" value="Testimonial" />
            <TextArea id="content" v-model="testimonials.content" rows="3" required
              placeholder="Write your testimonial here..." />
            <InputError :message="testimonials.errors.content" />
          </div>
          <div class="mb-4">
            <InputLabel for="testimonial-file" value="Attach File (optional)" />
            <input type="file" id="testimonial-file" @change="handleTestimonialFileUpload"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
          </div>
          <div class="flex justify-end">
            <button type="submit"
              class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
              Submit Testimonial
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Update Testimonials Modal -->
    <div v-if="isUpdateTestimonialsModalOpen"
      class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">Edit Testimonial</h2>
          <button class="text-gray-500 hover:text-gray-700" @click="closeUpdateTestimonialsModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <form @submit.prevent="updateTestimonials">
          <!-- Company Suggestive Input -->
          <div v-if="selectedType == 'company'" class="mb-4 relative">
            <InputLabel for="edit-company" value="Company" />
            <input id="edit-company" type="text" v-model="companyInput" @focus="showCompanySug = true"
              @blur="() => setTimeout(() => showCompanySug = false, 150)"
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
              placeholder="Type to search or enter a new company" autocomplete="off" />
            <ul v-if="showCompanySug && filteredCompanies.length"
              class="absolute z-20 mt-1 w-full bg-white border rounded shadow max-h-48 overflow-auto text-sm">
              <li v-for="c in filteredCompanies" :key="c.id" @mousedown.prevent="selectCompany(c)"
                class="px-3 py-2 hover:bg-indigo-50 cursor-pointer">
                {{ c.name }}
              </li>
            </ul>
            <div v-if="showCompanySug && !filteredCompanies.length" class="text-xs text-gray-400 mt-1">
              No matches. Press Enter to use “{{ companyInput }}”.
            </div>
          </div>

          <!-- Institution Suggestive Input -->
          <div v-if="selectedType == 'institution'" class="mb-4 relative">
            <InputLabel for="edit-institution" value="Institution" />
            <input id="edit-institution" type="text" v-model="institutionInput" @focus="showInstitutionSug = true"
              @blur="() => setTimeout(() => showInstitutionSug = false, 150)"
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
              placeholder="Type to search or enter a new institution" autocomplete="off" />
            <ul v-if="showInstitutionSug && filteredInstitutions.length"
              class="absolute z-20 mt-1 w-full bg-white border rounded shadow max-h-48 overflow-auto text-sm">
              <li v-for="i in filteredInstitutions" :key="i.id" @mousedown.prevent="selectInstitution(i)"
                class="px-3 py-2 hover:bg-indigo-50 cursor-pointer">
                {{ i.name }}
              </li>
            </ul>
            <div v-if="showInstitutionSug && !filteredInstitutions.length" class="text-xs text-gray-400 mt-1">
              No matches. Press Enter to use “{{ institutionInput }}”.
            </div>
          </div>

          <!-- Testimonial Content -->
          <div class="mb-4">
            <InputLabel for="edit-content" value="Testimonial" />
            <TextArea id="edit-content" v-model="testimonials.content" rows="3" required
              placeholder="Write your testimonial here..." />
            <InputError :message="testimonials.errors?.content" />
          </div>
          <!-- File Upload -->
          <div class="mb-4">
            <InputLabel for="edit-testimonial-file" value="Attach File (optional)" />
            <input type="file" id="edit-testimonial-file" @change="handleTestimonialFileUpload"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
          </div>
          <div class="flex justify-end">
            <button type="submit"
              class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
              Update Testimonial
            </button>
          </div>
        </form>
      </div>
    </div>

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
            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            OK
          </button>
        </div>
      </div>
    </Modal>

    <!-- Duplicate Modal -->
    <Modal :modelValue="isDuplicateModalOpen" @close="closeDuplicateModal">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-yellow-100 rounded-full p-2">
            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
              </path>
            </svg>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center mb-4">Duplicate Entry</h3>
        <p class="text-center mb-4">A testimonial with the same information already exists.</p>
        <div class="flex justify-center">
          <button @click="closeDuplicateModal"
            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            OK
          </button>
        </div>
      </div>
    </Modal>
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