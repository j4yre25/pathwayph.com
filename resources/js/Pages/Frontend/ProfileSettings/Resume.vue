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
import '@fortawesome/fontawesome-free/css/all.css';

// Define props
const props = defineProps({
  activeSection: {
    type: String,
    default: 'resume'
  }
});

// Resume form
const form = useForm({
  resume_file: null,
});

// Resume state management
const resumeData = ref(null);
const isUploading = ref(false);

// Resume state
const resume = ref({
  file: null,
  fileName: ''
});

// Upload resume function
const uploadResume = (event) => {
  const file = event.target.files[0];
  if (file) {
    resume.value.file = file;
    resume.value.fileName = file.name;
  }
};

// Remove resume function
const removeResume = () => {
  resume.value.file = null;
  resume.value.fileName = '';
};

// Modal states
const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Show success modal
const showSuccessModal = (message = 'Operation completed successfully!') => {
  successMessage.value = message;
  isSuccessModalOpen.value = true;
  setTimeout(() => {
    isSuccessModalOpen.value = false;
  }, 3000);
};

// Show error modal
const showErrorModal = (message = 'An error occurred. Please try again.') => {
  errorMessage.value = message;
  isErrorModalOpen.value = true;
  setTimeout(() => {
    isErrorModalOpen.value = false;
  }, 3000);
};

// Save resume function
const saveResume = () => {
  if (!resume.value.file) {
    showErrorModal('Please select a resume file first.');
    return;
  }
  
  const formData = new FormData();
  formData.append('resume', resume.value.file);
  
  // Use Inertia form to handle the upload
  const resumeForm = useForm({
    resume: resume.value.file
  });
  
  resumeForm.post(route('resume.upload'), {
    onSuccess: () => {
      showSuccessModal('Resume uploaded successfully!');
    },
    onError: (errors) => {
      console.error('Error uploading resume:', errors);
      showErrorModal('Failed to upload resume. Please try again.');
    }
  });
};

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

        <div v-if="activeSection === 'resume'" class="flex flex-col lg:flex-row">
            <div class="w-full lg:w-1/1 mb-6 lg:mb-0">
              <h2 class="text-xl font-semibold mb-4">Resume</h2>
              <p class="text-gray-600 mb-4">Upload and manage your resume</p>
              <div class="bg-white p-6 rounded-lg shadow-md border border-gray-300">
                <!-- Display Uploaded Resume -->
                <div v-if="resume.file"
                  class="flex items-center justify-between border border-gray-300 rounded-lg p-8 mb-4">
                  <div class="flex items-center">
                    <i class="fas fa-file-alt text-gray-500 text-2xl mr-4"></i>
                    <span class="text-gray-700 font-medium">{{ resume.fileName }}</span>
                  </div>
                  <button class="text-red-600 hover:text-red-800" @click="removeResume">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>

                <!-- If No Resume Uploaded -->
                <div v-else class="flex items-center justify-between border border-gray-300 rounded-lg p-8 mb-4">
                  <div class="flex items-center">
                    <i class="fas fa-file-alt text-gray-500 text-2xl mr-4"></i>
                    <span class="text-gray-500">No resume uploaded yet</span>
                  </div>
                </div>

                <!-- Upload Resume Button -->
                <label for="resume-upload"
                  class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-600 text-center cursor-pointer block">
                  <i class="fas fa-upload mr-2"></i> Upload New Resume
                </label>
                <input type="file" id="resume-upload" class="hidden" accept=".pdf,.doc,.docx" @change="uploadResume">

                <button v-if="resume.file" @click="saveResume"
                  class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-600 text-center mt-4">
                  <i class="fas fa-save mr-2"></i> Save Resume
                </button>
              </div>
            </div>
          </div>
</template>