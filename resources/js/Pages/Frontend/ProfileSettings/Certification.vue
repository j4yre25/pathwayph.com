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
import { isValid, format } from 'date-fns';
import '@fortawesome/fontawesome-free/css/all.css';

// Define props
const props = defineProps({
  activeSection: {
    type: String,
    default: 'certifications'
  }
});

// Format date function
const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return format(date, 'MMM dd, yyyy');
};

// Certification state variables
const certificationEntries = ref([props.certificationEntries || []]);
const archivedCertificationEntries = ref([]);
const showArchivedCertifications = ref(false);
const isAddCertificationModalOpen = ref(false);
const isUpdateCertificationModalOpen = ref(false);
const currentCertificationId = ref(null);

// Certification form
const certificationForm = useForm({
  id: null,
  graduate_certification_name: '',
  graduate_certification_issuer: '',
  graduate_certification_issue_date: null,
  graduate_certification_expiry_date: null,
  graduate_certification_credential_url: '',
  noExpiryDate: false,
  noCredentialUrl: false,
  file: null
});

// Modal states
const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const isDuplicateModalOpen = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Toggle archived certifications
const toggleArchivedCertifications = () => {
  showArchivedCertifications.value = !showArchivedCertifications.value;
};

// File upload handling
const handleFileUpload = (event) => {
  certificationForm.file = event.target.files[0];
};

// Close add certification modal
const closeAddCertificationModal = () => {
  isAddCertificationModalOpen.value = false;
  certificationForm.reset();
  certificationForm.clearErrors();
};

// Close update certification modal
const closeUpdateCertificationModal = () => {
  isUpdateCertificationModalOpen.value = false;
  certificationForm.reset();
  certificationForm.clearErrors();
};

// Open update certification modal
const openUpdateCertificationModal = (certification) => {
  currentCertificationId.value = certification.id;
  certificationForm.id = certification.id;
  certificationForm.graduate_certification_name = certification.graduate_certification_name;
  certificationForm.graduate_certification_issuer = certification.graduate_certification_issuer;
  certificationForm.graduate_certification_issue_date = certification.graduate_certification_issue_date ? new Date(certification.graduate_certification_issue_date) : null;
  certificationForm.graduate_certification_expiry_date = certification.graduate_certification_expiry_date ? new Date(certification.graduate_certification_expiry_date) : null;
  certificationForm.graduate_certification_credential_url = certification.graduate_certification_credential_url || '';
  certificationForm.noExpiryDate = certification.graduate_certification_expiry_date === null;
  certificationForm.noCredentialUrl = !certification.graduate_certification_credential_url;
  isUpdateCertificationModalOpen.value = true;
};

// Add certification function
const addCertification = () => {
  certificationForm.post(route('profile.certifications.add'), {
    onSuccess: () => {
      closeAddCertificationModal();
      successMessage.value = 'Certification added successfully!';
      isSuccessModalOpen.value = true;
      fetchCertifications();
    },
    onError: (errors) => {
      if (errors.duplicate) {
        isDuplicateModalOpen.value = true;
      } else {
        errorMessage.value = 'Failed to add certification. Please check the form and try again.';
        isErrorModalOpen.value = true;
      }
    }
  });
};

// Update certification function
const updateCertification = () => {
  certificationForm.post(route('profile.certifications.update', currentCertificationId.value), {
    onSuccess: () => {
      closeUpdateCertificationModal();
      successMessage.value = 'Certification updated successfully!';
      isSuccessModalOpen.value = true;
      fetchCertifications();
    },
    onError: (errors) => {
      if (errors.duplicate) {
        isDuplicateModalOpen.value = true;
      } else {
        errorMessage.value = 'Failed to update certification. Please check the form and try again.';
        isErrorModalOpen.value = true;
      }
    }
  });
};

// Remove certification function
const removeCertification = (certification) => {
  if (confirm('Are you sure you want to delete this certification? This action cannot be undone.')) {
    const deleteForm = useForm({});
    deleteForm.delete(route('profile.certifications.remove', certification.id), {
      onSuccess: () => {
        successMessage.value = 'Certification deleted successfully!';
        isSuccessModalOpen.value = true;
        fetchCertifications();
      },
      onError: () => {
        errorMessage.value = 'Failed to delete certification. Please try again.';
        isErrorModalOpen.value = true;
      }
    });
  }
};

// Archive certification function
const archiveCertification = (certification) => {
  const archiveForm = useForm({
    is_archived: true
  });
  
  archiveForm.post(route('profile.certifications.archive', certification.id), {
    onSuccess: () => {
      successMessage.value = 'Certification archived successfully!';
      isSuccessModalOpen.value = true;
      fetchCertifications();
    },
    onError: () => {
      errorMessage.value = 'Failed to archive certification. Please try again.';
      isErrorModalOpen.value = true;
    }
  });
};

// Unarchive certification function
const unarchiveCertification = (certification) => {
  const unarchiveForm = useForm({
    is_archived: false
  });
  
  unarchiveForm.post(route('profile.certifications.unarchive', certification.id), {
    onSuccess: () => {
      successMessage.value = 'Certification unarchived successfully!';
      isSuccessModalOpen.value = true;
      fetchCertifications();
    },
    onError: () => {
      errorMessage.value = 'Failed to unarchive certification. Please try again.';
      isErrorModalOpen.value = true;
    }
  });
};

// Fetch certifications function
const fetchCertifications = () => {
  router.get('profile.index', {}, {
    onSuccess: (response) => {
      const data = response.props;
      // Get active certifications
      if (data.certificationEntries) {
        certificationEntries.value = data.certificationEntries;
      }
      
      // Get archived certifications
      if (data.archivedCertificationEntries) {
        archivedCertificationEntries.value = data.archivedCertificationEntries;
      }
    },
    onError: () => {
      errorMessage.value = 'Failed to fetch certifications. Please refresh the page.';
      isErrorModalOpen.value = true;
    },
    preserveState: true
  });
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

// Fetch certifications on component mount
onMounted(() => {
  fetchCertifications();
});
</script>

<template>
    <div v-if="activeSection === 'certifications'" class="flex flex-col lg:flex-row">
        <div class="w-full mb-6">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-xl font-semibold mb-4">Certifications</h1>
                <div class="flex space-x-2">
                    <PrimaryButton class="bg-indigo-600 text-white px-4 py-2 rounded flex items-center hover:bg-indigo-700"
                        @click="isAddCertificationModalOpen = true">
                        <i class="fas fa-plus mr-2"></i> Add Certification
                    </PrimaryButton>
                    <button 
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded flex items-center transition duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 cursor-pointer"
                        @click="toggleArchivedCertifications">
                        <i class="fas" :class="showArchivedCertifications ? 'fa-eye-slash' : 'fa-eye'"></i>
                        <span class="ml-2">{{ showArchivedCertifications ? 'Hide Archived' : 'Show Archived' }}</span>
                    </button>
                </div>                
            </div>
            <p class="text-gray-600 mb-6">Manage your professional certifications</p>

            <!-- Certification Entries -->
            <div>
                <h2 class="text-lg font-medium mb-4">Active Certification</h2>
                <div v-if="certificationEntries.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div v-for="certification in certificationEntries" :key="certification.id" class="bg-white p-8 rounded-lg shadow relative">
                        <div>
                            <div class="border-b pb-2">
                                <h2 class="text-xl font-bold">{{ certification.graduate_certification_name }}</h2>
                                <p class="text-gray-600">{{ certification.graduate_certification_issuer }}</p>
                            </div>
                            <div class="flex items-center text-gray-600 mt-2">
                                <i class="far fa-calendar-alt mr-2"></i>
                                <span>{{ formatDate(certification.graduate_certification_issue_date) }} - {{ certification.graduate_certification_expiry_date === null ? 'No expiry date'  : formatDate(certification.graduate_certification_expiry_date) }}</span>
                            </div>
                            <div class="text-sm">
                                <p class="mt-2">
                                    URL: 
                                    <span v-if="certification.graduate_certification_credential_url && !certification.noCredentialUrl">
                                        <a :href="certification.graduate_certification_credential_url" class="text-blue-600 hover:underline" target="_blank">
                                        {{ certification.graduate_certification_credential_url }}
                                        </a>
                                    </span>
                                    <span v-else class="text-gray-500">No Credential URL</span>
                                </p>
                            </div>
                            <p class="mt-2" v-if="certification.file_path">
                                Photo: <img :src="`/storage/${certification.file_path}`" class="w-24 h-24 object-cover rounded-md" alt="Certification Photo" />
                            </p>
                        </div>
                        <div class="absolute top-2 right-2 flex space-x-2">
                            <button class="text-gray-600 hover:text-indigo-600" @click="openUpdateCertificationModal(certification)">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="text-red-600 hover:text-red-800" @click="removeCertification(certification)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            
                <!-- If no certifications exist -->
                <div v-else class="bg-white p-8 rounded-lg shadow mb-4">
                    <p class="text-gray-600">No certifications added yet.</p>
                </div>
            </div>

            <!-- Archived Certifications -->
            <div v-if="showArchivedCertifications" class="mt-8">
                <h2 class="text-lg font-medium mb-4">Archived Certifications</h2>
                <div v-if="archivedCertificationEntries.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div v-for="certification in archivedCertificationEntries" :key="certification.id" class="bg-gray-50 p-8 rounded-lg shadow relative border border-gray-200">
                        <div class="opacity-75">
                            <div class="border-b pb-2">
                                <h2 class="text-xl font-bold">{{ certification.graduate_certification_name }}</h2>
                                <p class="text-gray-600">{{ certification.graduate_certification_issuer }}</p>
                            </div>
                            <div class="flex items-center text-gray-600 mt-2">
                                <i class="far fa-calendar-alt mr-2"></i>
                                    <span>{{ formatDate(certification.graduate_certification_issue_date) }} - {{ certification.graduate_certification_expiry_date === null ? 'No expiry date'  : formatDate(certification.graduate_certification_expiry_date) }}</span>
                            </div>
                            <div class="text-sm">
                                <p class="mt-2">
                                    URL: 
                                    <span v-if="certification.graduate_certification_credential_url && !certification.noCredentialUrl">
                                    <a :href="certification.graduate_certification_credential_url" class="text-blue-600 hover:underline" target="_blank">
                                        {{ certification.graduate_certification_credential_url }}
                                    </a>
                                    </span>
                                    <span v-else class="text-gray-500">No Credential URL</span>
                                </p>
                            </div>
                                <p class="mt-2" v-if="certification.file_path">
                                Photo: <img :src="`/storage/${certification.file_path}`" class="w-24 h-24 object-cover rounded-md" alt="Certification Photo" />
                                </p>
                        </div>
                        <div class="absolute top-2 right-2 flex space-x-2">
                            <button class="text-green-600 hover:text-green-800" @click="unarchiveCertification(certification)">
                                <i class="fas fa-box-open"></i>
                            </button>
                            <button class="text-red-600 hover:text-red-800" @click="removeCertification(certification)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="absolute top-2 left-2 bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded">
                            Archived
                        </div>
                    </div>
                </div>

                <!-- If no archived certification entries exist -->
                <div v-else class="bg-white p-8 rounded-lg shadow">
                    <p class="text-gray-600">No archived certification entries found.</p>
                </div>
            </div>
        </div>

        <!-- Add Certification Modal -->
        <div v-if="isAddCertificationModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Add Certification</h2>
                    <button class="text-gray-500 hover:text-gray-700" @click="closeAddCertificationModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form @submit.prevent="addCertification" novalidate>
                    <div class="mb-4">
                        <label for="certification-name" class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                        <input type="text" id="certification-name" v-model="certificationForm.graduate_certification_name"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required>
                    </div>
                    <div class="mb-4">
                        <label for="certification-issuer" class="block text-sm font-medium text-gray-700">Issuer <span class="text-red-500">*</span></label>
                        <input type="text" id="certification-issuer" v-model="certificationForm.graduate_certification_issuer"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required>
                    </div>
                    <div class="mb-4">
                        <label for="certification-issue-date" class="block text-sm font-medium text-gray-700">Issue Date <span class="text-red-500">*</span></label>
                        <Datepicker v-model="certificationForm.graduate_certification_issue_date"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Select issue date" required />
                    </div>
                    <div class="mb-4">
                        <label for="certification-expiry-date" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                        <Datepicker v-model="certificationForm.graduate_certification_expiry_date"
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                            placeholder="Select expiry date" :required="!certificationForm.noExpiryDate" :disabled="certificationForm.noExpiryDate" />
                        <div class="mt-2">
                            <input type="checkbox" id="no-expiry-date" v-model="certificationForm.noExpiryDate" class="mr-2" />
                            <label for="no-expiry-date" class="text-sm text-gray-700">No expiry date</label>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Credential URL</label>
                        <input type="url" v-model="certificationForm.graduate_certification_credential_url"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="e.g. https://example.com" 
                        :required="!certificationForm.noCredentialUrl" 
                        :disabled="certificationForm.noCredentialUrl" />
                        <div class="mt-2">
                            <input type="checkbox" id="no-credential-url" v-model="certificationForm.noCredentialUrl" class="mr-2" />
                            <label for="no-credential-url" class="text-sm text-gray-700">No Credential URL</label>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="certification-file" class="block text-sm font-medium text-gray-700">Upload File</label>
                        <input type="file" id="certification-file" @change="handleFileUpload"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i>
                        Save Certification
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Update Certification Modal -->
        <div v-if="isUpdateCertificationModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Update Certification</h2>
                    <button class="text-gray-500 hover:text-gray-700" @click="closeUpdateCertificationModal">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form @submit.prevent="updateCertification" novalidate>
                    <div class="mb-4">
                        <label for="update-certification-name" class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                        <input type="text" id="update-certification-name" v-model="certificationForm.graduate_certification_name"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required>
                    </div>
                    <div class="mb-4">
                        <label for="update-certification-issuer" class="block text-sm font-medium text-gray-700">Issuer <span class="text-red-500">*</span></label>
                        <input type="text" id="update-certification-issuer" v-model="certificationForm.graduate_certification_issuer"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        required>
                    </div>
                    <div class="mb-4">
                        <label for="update-certification-issue-date" class="block text-sm font-medium text-gray-700">Issue Date <span class="text-red-500">*</span></label>
                        <Datepicker v-model="certificationForm.graduate_certification_issue_date"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Select issue date" required />
                    </div>
                    <div class="mb-4">
                        <label for="update-certification-expiry-date" class="block text-sm font-medium text-gray-700">Expiry Date</label>
                        <Datepicker v-model="certificationForm.graduate_certification_expiry_date"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="Select expiry date" :required="!certificationForm.noExpiryDate" :disabled="certificationForm.noExpiryDate" />
                        <div class="mt-2">
                            <input type="checkbox" id="update-no-expiry-date" v-model="certificationForm.noExpiryDate" class="mr-2" />
                            <label for="update-no-expiry-date" class="text-sm text-gray-700">No expiry date</label>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Credential URL</label>
                        <input type="url" v-model="certificationForm.graduate_certification_credential_url"
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
                        placeholder="e.g. https://example.com" 
                        :required="!certificationForm.noCredentialUrl" 
                        :disabled="certificationForm.noCredentialUrl" />
                        <div class="mt-2">
                            <input type="checkbox" id="update-no-credential-url" v-model="certificationForm.noCredentialUrl" class="mr-2" />
                            <label for="update-no-credential-url" class="text-sm text-gray-700">No Credential URL</label>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="update-certification-file" class="block text-sm font-medium text-gray-700">Upload File</label>
                        <input type="file" id="update-certification-file" @change="handleFileUpload"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-md flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i>
                        Update Certification
                        </button>
                    </div>
                </form>
            </div>
        </div> 
    </div>

    <!-- Success Modal -->
    <Modal :show="isSuccessModalOpen" @close="closeSuccessModal">
        <div class="p-6">
            <div class="flex items-center justify-center mb-4 bg-green-100 rounded-full w-16 h-16 mx-auto">
                <i class="fas fa-check text-2xl text-green-600"></i>
            </div>
            <h3 class="text-lg font-medium text-center mb-4">Success</h3>
            <p class="text-center mb-6">{{ successMessage }}</p>
            <div class="flex justify-center">
                <button @click="closeSuccessModal"
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition-colors">
                    OK
                </button>
            </div>
        </div>
    </Modal>

    <!-- Error Modal -->
    <Modal :show="isErrorModalOpen" @close="closeErrorModal">
        <div class="p-6">
            <div class="flex items-center justify-center mb-4 bg-red-100 rounded-full w-16 h-16 mx-auto">
                <i class="fas fa-times text-2xl text-red-600"></i>
            </div>
            <h3 class="text-lg font-medium text-center mb-4">Error</h3>
            <p class="text-center mb-6">{{ errorMessage }}</p>
            <div class="flex justify-center">
                <button @click="closeErrorModal"
                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors">
                    OK
                </button>
            </div>
        </div>
    </Modal>

    <!-- Duplicate Modal -->
    <Modal :show="isDuplicateModalOpen" @close="closeDuplicateModal">
        <div class="p-6">
            <div class="flex items-center justify-center mb-4 bg-yellow-100 rounded-full w-16 h-16 mx-auto">
                <i class="fas fa-exclamation-triangle text-2xl text-yellow-600"></i>
            </div>
            <h3 class="text-lg font-medium text-center mb-4">Duplicate Entry</h3>
            <p class="text-center mb-6">A certification with the same details already exists.</p>
            <div class="flex justify-center">
                <button @click="closeDuplicateModal"
                    class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700 transition-colors">
                    OK
                </button>
            </div>
        </div>
    </Modal>
</template>