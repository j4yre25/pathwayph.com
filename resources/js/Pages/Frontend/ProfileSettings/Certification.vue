<script setup>
import { ref, computed, watch, reactive } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import Datepicker from 'vue3-datepicker';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Inertia } from '@inertiajs/inertia'


// Props
const props = defineProps({
  activeSection: { type: String, default: 'certifications' },
  certificationsEntries: Array,
  archivedCertificationsEntries: Array,
});

// Map backend fields to frontend expected fields
function mapCertificationFields(entry) {
  return {
    ...entry,
    name: entry.name,
    issuer: entry.issuer,
    issue_date: entry.issue_date ? new Date(entry.issue_date) : null,
    expiry_date: entry.expiry_date ? new Date(entry.expiry_date) : null,
    credential_url: entry.credential_url,
    credential_id: entry.credential_id, // only if you have this column
    file_path: entry.file_path,
    noExpiryDate: entry.expiry_date === null,
    noCredentialUrl: !entry.credential_url,
    id: entry.id,
  };
}

const certificationsEntries = ref((props.certificationsEntries || []).map(mapCertificationFields));
const archivedCertificationsEntries = ref((props.archivedCertificationsEntries || []).map(mapCertificationFields));

const filteredCertificationsEntries = computed(() => certificationsEntries.value);
const filteredArchivedCertificationsEntries = computed(() => archivedCertificationsEntries.value);

// State
const isAddCertificationModalOpen = ref(false);
const isUpdateCertificationModalOpen = ref(false);
const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const isDuplicateModalOpen = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const showArchivedCertifications = ref(false);
// Removed expand functionality - sections are now always visible

// Form
const form = useForm({
  name: '',
  issuer: '',
  issue_date: null,
  expiry_date: null,
  credential_url: '',
  credential_id: '', // only if you have this column
  noExpiryDate: false,
  noCredentialUrl: false,
  file: null,
  id: null,
});

// Helpers
const formatDate = (date) => {
  if (!date) return '';
  if (typeof date === 'string') return date;
  const d = new Date(date);
  if (isNaN(d.getTime())) return '';
  return d.toISOString().split('T')[0];
};

// Reset form
const resetForm = () => {
  form.reset();
  form.issue_date = null;
  form.expiry_date = null;
};

// Modal handlers
const openAddCertificationModal = () => {
  resetForm();
  isAddCertificationModalOpen.value = true;
};
const closeAddCertificationModal = () => {
  isAddCertificationModalOpen.value = false;
  resetForm();
};
const openUpdateCertificationModal = (entry) => {
  resetForm();
  form.id = entry.id;
  form.name = entry.name;
  form.issuer = entry.issuer;
  form.issue_date = entry.issue_date ? new Date(entry.issue_date) : null;
  form.expiry_date = entry.noExpiryDate ? null : (entry.expiry_date ? new Date(entry.expiry_date) : null);
  form.credential_url = entry.credential_url || '';
  form.credential_id = entry.credential_id || '';
  form.noExpiryDate = entry.noExpiryDate;
  form.noCredentialUrl = entry.noCredentialUrl;
  form.file = null;
  isUpdateCertificationModalOpen.value = true;
};
const closeUpdateCertificationModal = () => {
  isUpdateCertificationModalOpen.value = false;
  resetForm();
};
const closeSuccessModal = () => {
  isSuccessModalOpen.value = false;
  resetForm();
  router.reload();
  isAddCertificationModalOpen.value = false;
  isUpdateCertificationModalOpen.value = false;
};
const closeErrorModal = () => { isErrorModalOpen.value = false; };
const closeDuplicateModal = () => { isDuplicateModalOpen.value = false; };

const handleFileUpload = (event) => {
  form.file = event.target.files[0];
};

const toggleArchivedCertifications = () => {
  showArchivedCertifications.value = !showArchivedCertifications.value;
};

// Add Certification
const addCertification = () => {
  form.issue_date = formatDate(form.issue_date);
  form.expiry_date = form.noExpiryDate ? null : formatDate(form.expiry_date);
  form.credential_url = form.noCredentialUrl ? '' : form.credential_url;
  form.post(route('profile.certifications.add'), {
    forceFormData: true,
    onSuccess: (response) => {
      closeAddCertificationModal();
      successMessage.value = 'Certification added successfully!';
      isSuccessModalOpen.value = true;
      resetForm();
      Inertia.reload();

    },
    onError: (errors) => {
      if (errors.duplicate) {
        isDuplicateModalOpen.value = true;
      } else {
        errorMessage.value = 'Failed to add certification. Please check the form and try again.';
        isErrorModalOpen.value = true;
      }
    },
  });
};

// Update Certification
const updateCertification = () => {
  form.issue_date = formatDate(form.issue_date);
  form.expiry_date = form.noExpiryDate ? null : formatDate(form.expiry_date);
  form.credential_url = form.noCredentialUrl ? '' : form.credential_url;
  form.post(route('profile.certifications.update', form.id), {
    forceFormData: true,
    _method: 'PUT',
    onSuccess: (response) => {
      isUpdateCertificationModalOpen.value = false;
      successMessage.value = 'Certification updated successfully!';
      isSuccessModalOpen.value = true;
      resetForm();
      Inertia.reload();

    },
    onError: (errors) => {
      if (errors.duplicate) {
        isDuplicateModalOpen.value = true;
      } else {
        errorMessage.value = 'Failed to update certification. Please check the form and try again.';
        isErrorModalOpen.value = true;
        isUpdateCertificationModalOpen.value = false; // <-- Ensure modal closes on error
      }
    },

  });
};


// Remove Certification
const removeCertification = (entry) => {
  if (confirm('Are you sure you want to delete this certification?')) {
    useForm({}).delete(route('profile.certifications.remove', entry.id), {
      onSuccess: () => {
        successMessage.value = 'Certification deleted successfully!';
        isSuccessModalOpen.value = true;
      },
      onError: () => {
        errorMessage.value = 'Failed to delete certification. Please try again.';
        isErrorModalOpen.value = true;
      }
    });
  }
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
    confirmModal.message = 'Are you sure you want to delete this certification? This action cannot be undone.';
    confirmModal.confirmLabel = 'Delete';
    confirmModal.confirmAction = () => {
      useForm({}).delete(route('profile.certifications.remove', entry.id), {
        onSuccess: () => {
          successMessage.value = 'Certification deleted successfully!';
          isSuccessModalOpen.value = true;
          closeConfirm();
          Inertia.reload();

        },
        onError: () => {
          errorMessage.value = 'Failed to delete certification. Please try again.';
          isErrorModalOpen.value = true;
          closeConfirm();
        }
      });
    };
  } else if (type === 'archive') {
    confirmModal.message = 'Are you sure you want to archive this certification?';
    confirmModal.confirmLabel = 'Archive';
    confirmModal.confirmAction = () => {
      useForm({}).put(route('profile.certifications.archive', entry.id), {
        onSuccess: () => {
          successMessage.value = 'Certification archived successfully!';
          isSuccessModalOpen.value = true;
          closeConfirm();
          Inertia.reload();
        },
        onError: () => {
          errorMessage.value = 'Failed to archive certification. Please try again.';
          isErrorModalOpen.value = true;
          closeConfirm();
        }
      });
    };
  } else if (type === 'unarchive') {
    confirmModal.message = 'Restore this archived certification?';
    confirmModal.confirmLabel = 'Restore';
    confirmModal.confirmAction = () => {
      useForm({}).post(route('profile.certifications.unarchive', entry.id), {
        onSuccess: () => {
          successMessage.value = 'Certification restored successfully!';
          isSuccessModalOpen.value = true;
          closeConfirm();
          Inertia.reload();

        },
        onError: () => {
          errorMessage.value = 'Failed to restore certification. Please try again.';
          isErrorModalOpen.value = true;
          closeConfirm();
        }
      });
    };
  }
}

function deleteCertification(entry) {
  openConfirm('delete', entry);
}

function archiveCertification(entry) {
  openConfirm('archive', entry);
}

function unarchiveCertification(entry) {
  openConfirm('unarchive', entry);
}

function closeConfirm() {
  confirmModal.show = false;
  confirmModal.type = '';
  confirmModal.entry = null;
  confirmModal.message = '';
  confirmModal.confirmLabel = '';
  confirmModal.confirmAction = null;
}
</script>

<template>
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
        {{ confirmModal.confirmLabel }} Certification
      </h3>
      <p class="text-center text-gray-600 mb-4">{{ confirmModal.message }}</p>
      <div class="mt-6 flex justify-center space-x-2">
        <button type="button" @click="closeConfirm"
          class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition duration-200">
          Cancel
        </button>
        <button v-if="confirmModal.type === 'delete'" type="button" @click="confirmModal.confirmAction"
          class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition duration-200">Delete</button>
        <button v-else type="button"
          :class="confirmModal.type === 'archive' ? 'bg-amber-500 hover:bg-amber-600' : 'bg-green-600 hover:bg-green-700'"
          class="text-white px-4 py-2 rounded transition duration-200" @click="confirmModal.confirmAction">{{
            confirmModal.confirmLabel }}</button>
      </div>
    </div>
  </Modal>
  <!-- Success Modal -->
  <Modal :modelValue="isSuccessModalOpen" @close="closeSuccessModal">
    <div class="p-6">
      <div class="flex items-center justify-center mb-4 bg-green-100 rounded-full w-16 h-16 mx-auto">
        <i class="fas fa-check text-2xl text-green-600"></i>
      </div>
      <h2 class="text-xl font-bold text-center mb-4">Success</h2>
      <p class="text-center mb-6">{{ successMessage }}</p>
      <div class="flex justify-center">
        <button @click="closeSuccessModal"
          class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition duration-200">
          Close
        </button>
      </div>
    </div>
  </Modal>

  <!-- Error Modal -->
  <Modal :modelValue="isErrorModalOpen" @close="closeErrorModal">
    <div class="p-6">
      <div class="flex items-center justify-center mb-4 bg-red-100 rounded-full w-16 h-16 mx-auto">
        <i class="fas fa-times text-2xl text-red-600"></i>
      </div>
      <h2 class="text-xl font-bold text-center mb-4">Error</h2>
      <p class="text-center mb-6">{{ errorMessage }}</p>
      <div class="flex justify-center">
        <button @click="closeErrorModal"
          class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition duration-200">
          Close
        </button>
      </div>
    </div>
  </Modal>

  <!-- Duplicate Modal -->
  <Modal :modelValue="isDuplicateModalOpen" @close="closeDuplicateModal">
    <div class="p-6">
      <div class="flex items-center justify-center mb-4 bg-amber-100 rounded-full w-16 h-16 mx-auto">
        <i class="fas fa-exclamation-triangle text-2xl text-amber-600"></i>
      </div>
      <h2 class="text-xl font-bold text-center mb-4">Duplicate Entry</h2>
      <p class="text-center mb-6">A certification with similar details already exists.</p>
      <div class="flex justify-center">
        <button @click="closeDuplicateModal"
          class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition duration-200">
          Close
        </button>
      </div>
    </div>
  </Modal>

  <div v-if="activeSection === 'certifications'" class="flex flex-col lg:flex-row">
    <div class="w-full mb-6">
      <!-- Active Certifications Card -->
      <div
        class="bg-white rounded-lg shadow-sm border border-blue-100 overflow-hidden transition-all duration-300 mb-6">
        <!-- Card Header -->
        <div
          class="flex justify-between items-center p-4 bg-gradient-to-r from-blue-50 to-white border-b border-blue-100 text-white">
          <div class="flex items-center">
            <div class="bg-white/20 p-2 rounded-full mr-3">
              <i class="fas fa-certificate"></i>
            </div>
            <h3 class="text-lg font-semibold">Certifications</h3>
          </div>
          <div class="flex space-x-2">
            <PrimaryButton class="bg-blue-700 text-white px-4 py-2 rounded flex items-center hover:bg-blue-800"
              @click="openAddCertificationModal">
              <i class="fas fa-plus mr-2"></i> Add Certification
            </PrimaryButton>
            <button
              class="bg-blue-700/80 hover:bg-blue-800 text-white px-4 py-2 rounded flex items-center transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 cursor-pointer"
              @click="toggleArchivedCertifications">
              <i class="fas" :class="showArchivedCertifications ? 'fa-eye-slash' : 'fa-eye'"></i>
              <span class="ml-2">{{ showArchivedCertifications ? 'Hide Archived' : 'Show Archived' }}</span>
            </button>
          </div>
        </div>

        <!-- Card Body -->
        <div class="p-6 transition-all duration-300">
          <p class="text-gray-600 mb-6">Showcase your certifications</p>

          <!-- Certification Entries -->
          <div v-if="filteredCertificationsEntries.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div v-for="entry in filteredCertificationsEntries" :key="entry?.id"
              class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 border border-gray-200 relative">
              <div class="space-y-2 w-full">
                <div class="border-b border-blue-100 pb-2">
                  <h2 class="text-xl font-bold text-blue-900">{{ entry.name }}</h2>
                  <p class="text-sm text-gray-600"><i class="fas fa-certificate text-blue-600 mr-2"></i>{{ entry.issuer
                  }}</p>
                </div>
                <div class="flex items-center text-gray-600 mt-2 bg-blue-50 px-3 py-1 rounded-full inline-block">
                  <i class="far fa-calendar-alt mr-2 text-blue-600"></i>
                  <span>
                    {{ formatDate(entry.issue_date) }} -
                    {{ entry.expiry_date === null ? 'No Expiry' :
                      formatDate(entry.expiry_date) }}
                  </span>
                </div>
                <div class="mt-2">
                  <strong>
                    <i class="fas fa-link text-blue-600 mr-2"></i> Credential URL:
                  </strong>
                  <span v-if="entry.credential_url">
                    <a :href="entry.credential_url" target="_blank" class="text-blue-600 hover:underline break-all">
                      {{ entry.credential_url }}
                    </a>
                  </span>
                  <span v-else class="text-gray-500">No credential URL provided</span>
                </div>
                <p class="mt-2 bg-gray-50 p-2 rounded-md">
                  <strong>
                    <i class="fas fa-id-badge text-blue-600 mr-2"></i> Credential Number:
                  </strong>
                  <span class="font-mono">{{ entry.credential_id || 'No credential number provided' }}</span>
                </p>
                <div v-if="entry.file_path" class="mt-3">
                  <a :href="`/storage/${entry.file_path}`" target="_blank"
                    class="text-blue-600 hover:underline bg-gray-50 p-2 rounded-md border border-blue-100 inline-block">
                    <i class="fas fa-file-pdf mr-2"></i> View Certificate
                  </a>
                </div>
              </div>
              <div class="absolute top-6 right-4 flex space-x-4">
                <button
                  class="inline-flex items-center px-2 py-1 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition ease-in-out duration-150"
                  @click="openUpdateCertificationModal(entry)">
                  <i class="fas fa-edit"></i>
                </button>
                <button
                  class="inline-flex items-center px-2 py-1 bg-amber-100 border border-amber-300 rounded-md font-semibold text-xs text-amber-700 hover:bg-amber-200 focus:outline-none focus:ring-2 focus:ring-amber-500 transition ease-in-out duration-150"
                  @click="archiveCertification(entry)">
                  <i class="fas fa-archive"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- If no certifications exist -->
          <div v-else class="bg-gray-50 p-8 rounded-lg border border-gray-200 text-center">
            <i class="fas fa-certificate text-gray-400 text-4xl mb-4"></i>
            <p class="text-gray-600">No certification entries added yet.</p>
            <button @click="openAddCertificationModal" class="mt-4 text-blue-600 hover:text-blue-800 font-medium">
              <i class="fas fa-plus mr-2"></i> Add your first certification
            </button>
          </div>
        </div>
      </div>

      <!-- Archived Certifications -->
      <div v-if="showArchivedCertifications" class="mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-300">
          <!-- Card Header -->
          <div
            class="flex justify-between items-center p-4 bg-gradient-to-r from-gray-200 to-white border-b border-gray-200">
            <div class="flex items-center">
              <div class="bg-gray-100 p-2 rounded-full mr-3">
                <i class="fas fa-archive text-gray-600"></i>
              </div>
              <h3 class="text-lg font-semibold text-gray-800">Archived Certifications</h3>
            </div>
          </div>

          <!-- Card Body -->
          <div class="p-6 transition-all duration-300">
            <div v-if="filteredArchivedCertificationsEntries.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div v-for="entry in filteredArchivedCertificationsEntries" :key="entry?.id"
                class="bg-gray-50 p-6 rounded-lg shadow-sm relative border border-gray-200">
                <div class="opacity-75">
                  <div class="border-b border-gray-200 pb-2">
                    <h2 class="text-xl font-bold text-gray-800">{{ entry.name }}</h2>
                    <p class="text-sm text-gray-600">{{ entry.issuer }}</p>
                  </div>
                  <div class="flex items-center text-gray-600 mt-2 bg-gray-100 px-3 py-1 rounded-full inline-block">
                    <i class="far fa-calendar-alt mr-2 text-gray-500"></i>
                    <span>
                      {{ formatDate(entry.issue_date) }} -
                      {{ entry.expiry_date === null ? 'No Expiry' :
                        formatDate(entry.expiry_date) }}
                    </span>
                  </div>
                  <div class="mt-2">
                    <strong>
                      <i class="fas fa-link text-gray-500 mr-2"></i> Credential URL:
                    </strong>
                    <span v-if="entry.credential_url">
                      <a :href="entry.credential_url" target="_blank" class="text-blue-600 hover:underline break-all">
                        {{ entry.credential_url }}
                      </a>
                    </span>
                    <span v-else class="text-gray-500">No credential URL provided</span>
                  </div>
                  <p class="mt-2 bg-gray-100 p-2 rounded-md">
                    <strong>
                      <i class="fas fa-id-badge text-gray-500 mr-2"></i> Credential Number:
                    </strong>
                    <span class="font-mono">{{ entry.credential_id || 'No credential number provided' }}</span>
                  </p>
                  <div v-if="entry.file_path" class="mt-3">
                    <a :href="`/storage/${entry.file_path}`" target="_blank"
                      class="text-blue-600 hover:underline bg-gray-100 p-2 rounded-md border border-gray-200 inline-block">
                      <i class="fas fa-file-pdf mr-2"></i> View Certificate
                    </a>
                  </div>
                </div>
                <div class="absolute top-6 right-4 flex space-x-4">
                  <!-- Archive Button (for active certifications) -->

                  <button
                    class="inline-flex items-center px-2 py-1 bg-green-100 border border-green-300 rounded-md font-semibold text-xs text-green-700 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-green-500 transition ease-in-out duration-150"
                    @click="unarchiveCertification(entry)">
                    <i class="fas fa-undo"></i>
                  </button>
                  <button
                    class="inline-flex items-center px-2 py-1 bg-red-100 border border-red-300 rounded-md font-semibold text-xs text-red-700 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-red-500 transition ease-in-out duration-150"
                    @click="deleteCertification(entry)">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
                <div class="absolute top-2 left-2 bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded">
                  Archived
                </div>
              </div>
            </div>
            <!-- If no archived certification entries exist -->
            <div v-else class="bg-gray-50 p-8 rounded-lg border border-gray-200 text-center">
              <i class="fas fa-archive text-gray-400 text-4xl mb-4"></i>
              <p class="text-gray-600">No archived certification entries found.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Certification Modal -->
    <div v-if="isAddCertificationModalOpen"
      class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">Add Certification</h2>
          <button class="text-gray-500 hover:text-gray-700" @click="closeAddCertificationModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="max-h-96 overflow-y-auto">
          <form @submit.prevent="addCertification">
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Certification Name <span
                  class="text-red-500">*</span></label>
              <input type="text" v-model="form.name"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. AWS Certified Solutions Architect" required />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Issuer <span class="text-red-500">*</span></label>
              <input type="text" v-model="form.issuer"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. Amazon Web Services" required />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Issue Date <span class="text-red-500">*</span></label>
              <Datepicker v-model="form.issue_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Select issue date" required />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Expiry Date</label>
              <Datepicker v-model="form.expiry_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Select expiry date" :disabled="form.noExpiryDate" />
              <div class="mt-2">
                <input type="checkbox" v-model="form.noExpiryDate" id="noExpiryDate" />
                <label for="noExpiryDate" class="text-sm text-gray-700 ml-2">No Expiry Date</label>
              </div>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Credential URL</label>
              <input type="url" v-model="form.credential_url"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. https://verify.aws.amazon.com/cert/123456" :disabled="form.noCredentialUrl" />
            </div>
            <div class="mb-4">
              <input type="checkbox" v-model="form.noCredentialUrl" id="noCredentialUrl" />
              <label for="noCredentialUrl" class="text-sm text-gray-700 ml-2">No Credential URL</label>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Credential Number</label>
              <input type="text" v-model="form.credential_id"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. 123456" />
            </div>
            <div class="mb-4">
              <label for="certification-file" class="block text-sm font-medium text-gray-700">Upload Certificate</label>
              <input type="file" id="certification-file" @change="handleFileUpload"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <PrimaryButton type="submit" class="w-full">Add
              Certification</PrimaryButton>
          </form>
        </div>
      </div>
    </div>

    <!-- Update Certification Modal -->
    <div v-if="isUpdateCertificationModalOpen"
      class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">Update Certification</h2>
          <button class="text-gray-500 hover:text-gray-700" @click="closeUpdateCertificationModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="max-h-96 overflow-y-auto">
          <form @submit.prevent="updateCertification">
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Certification Name <span
                  class="text-red-500">*</span></label>
              <input type="text" v-model="form.name"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. AWS Certified Solutions Architect" required />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Issuer <span class="text-red-500">*</span></label>
              <input type="text" v-model="form.issuer"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. Amazon Web Services" required />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Issue Date <span class="text-red-500">*</span></label>
              <Datepicker v-model="form.issue_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Select issue date" required />
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Expiry Date</label>
              <Datepicker v-model="form.expiry_date"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="Select expiry date" :disabled="form.noExpiryDate" />
              <div class="mt-2">
                <input type="checkbox" v-model="form.noExpiryDate" id="noExpiryDateUpdate" />
                <label for="noExpiryDateUpdate" class="text-sm text-gray-700 ml-2">No Expiry Date</label>
              </div>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Credential URL</label>
              <input type="url" v-model="form.credential_url"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. https://verify.aws.amazon.com/cert/123456" :disabled="form.noCredentialUrl" />
            </div>
            <div class="mb-4">
              <input type="checkbox" v-model="form.noCredentialUrl" id="noCredentialUrlUpdate" />
              <label for="noCredentialUrlUpdate" class="text-sm text-gray-700 ml-2">No Credential URL</label>
            </div>
            <div class="mb-4">
              <label class="block text-gray-700 font-medium mb-2">Credential Number</label>
              <input type="text" v-model="form.credential_id"
                class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600"
                placeholder="e.g. 123456" />
            </div>
            <div class="mb-4">
              <label for="certification-file-update" class="block text-sm font-medium text-gray-700">Upload
                Certificate</label>
              <input type="file" id="certification-file-update" @change="handleFileUpload"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>
            <PrimaryButton type="submit" class="w-full">Update
              Certification
            </PrimaryButton>
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