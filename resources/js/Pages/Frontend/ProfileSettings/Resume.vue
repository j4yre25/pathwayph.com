<script setup>
import { ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import { useForm, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
  activeSection: { type: String, default: 'resume' },
  resume: Object
});

const currentResume = ref(props.resume);

watch(() => props.resume, (val) => {
  currentResume.value = val;
});

const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const form = useForm({ resume: null });

const uploadResume = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.resume = file;
  }
};

const saveResume = () => {
  if (!form.resume) {
    errorMessage.value = 'Please select a resume file first.';
    isErrorModalOpen.value = true;
    return;
  }
  form.post(route('resume.upload'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: (response) => {
      // If using Inertia, response is not available here, so use onFinish
    },
    onFinish: () => {
      // Fetch the latest resume via AJAX (optional, but best for instant update)
      fetch(route('profile.resume.settings'), { headers: { 'Accept': 'application/json' } })
        .then(res => res.json())
        .then(data => {
          if (data.resume) {
            currentResume.value = data.resume;
          }
          successMessage.value = 'Resume uploaded successfully!';
          isSuccessModalOpen.value = true;
          form.reset();
        });
    },
    onError: () => {
      errorMessage.value = 'Failed to upload resume. Please try again.';
      isErrorModalOpen.value = true;
    }
  });
};

const removeResume = () => {
  if (confirm('Are you sure you want to delete your resume?')) {
    form.delete(route('resume.delete'), {
      onSuccess: () => {
        currentResume.value = null;
        successMessage.value = 'Resume deleted successfully!';
        isSuccessModalOpen.value = true;
      },
      onError: () => {
        errorMessage.value = 'Failed to delete resume. Please try again.';
        isErrorModalOpen.value = true;
      }
    });
  }
};
</script>

<template>
  <Modal :show="isSuccessModalOpen" @close="isSuccessModalOpen = false">
    <div class="p-6">
      <div class="flex items-center justify-center mb-4 bg-green-100 rounded-full w-12 h-12 mx-auto">
        <i class="fas fa-check text-green-500 text-xl"></i>
      </div>
      <h2 class="text-lg font-medium text-center text-gray-900 mb-2">Success</h2>
      <p class="text-center text-gray-600">{{ successMessage }}</p>
      <div class="mt-6 flex justify-center">
        <button @click="isSuccessModalOpen = false" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
          OK
        </button>
      </div>
    </div>
  </Modal>
  <Modal :show="isErrorModalOpen" @close="isErrorModalOpen = false">
    <div class="p-6">
      <div class="flex items-center justify-center mb-4 bg-red-100 rounded-full w-12 h-12 mx-auto">
        <i class="fas fa-times text-red-500 text-xl"></i>
      </div>
      <h2 class="text-lg font-medium text-center text-gray-900 mb-2">Error</h2>
      <p class="text-center text-gray-600">{{ errorMessage }}</p>
      <div class="mt-6 flex justify-center">
        <button @click="isErrorModalOpen = false" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
          OK
        </button>
      </div>
    </div>
  </Modal>

  <div v-if="activeSection === 'resume'" class="space-y-6">
    <div class="bg-white p-6 rounded-lg shadow-md border border-blue-100">
      <div class="flex items-center mb-4">
        <span class="bg-blue-100 text-blue-800 p-2 rounded mr-3 inline-block">
          <i class="fas fa-file-alt"></i>
        </span>
        <h2 class="text-xl font-semibold text-blue-800">Resume</h2>
      </div>
      <p class="text-gray-600 mb-6 border-b border-blue-100 pb-4">Upload your resume to showcase your skills and experience to potential employers.</p>

      <!-- Display Uploaded Resume -->
      <div v-if="currentResume && currentResume.file_name" class="flex items-center justify-between border border-blue-100 rounded-lg p-6 mb-6 bg-white shadow-sm hover:shadow-md transition-all duration-300">
        <div class="flex items-center">
          <span class="bg-blue-100 text-blue-800 p-2 rounded mr-3 inline-block">
            <i class="fas fa-file-pdf"></i>
          </span>
          <div>
            <a :href="currentResume.file_url" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium flex items-center">
              <i class="fas fa-external-link-alt mr-1"></i>
              {{ currentResume.file_name }}
            </a>
            <p class="text-gray-500 text-sm mt-1">Click to view</p>
          </div>
        </div>
        <button class="text-red-600 hover:text-red-800 p-2 rounded-full hover:bg-red-50 transition-colors duration-300" @click="removeResume">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>
      <!-- If No Resume Uploaded -->
      <div v-else class="border-2 border-dashed border-blue-200 rounded-lg p-6 mb-6 text-center bg-blue-50 hover:bg-blue-100 transition-colors duration-300">
        <svg class="mx-auto h-12 w-12 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
        </svg>
        <p class="text-gray-600 mt-2">No resume uploaded yet</p>
        <p class="text-gray-500 text-sm mt-1">Upload your resume to share with employers</p>
      </div>
      <!-- Upload Resume Button -->
      <div class="mt-6">
        <label for="resume-upload"
          class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 text-center cursor-pointer block transition-colors duration-300 shadow-sm">
          <i class="fas fa-upload mr-2"></i> Upload New Resume
        </label>
        <input type="file" id="resume-upload" class="hidden" accept=".pdf,.doc,.docx" @change="uploadResume">
        <button v-if="form.resume" @click="saveResume"
          class="w-full bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-600 text-center mt-4 transition-colors duration-300 shadow-sm">
          <i class="fas fa-save mr-2"></i> Save Resume
        </button>
      </div>
      <p class="text-gray-500 text-sm mt-4 text-center">Accepted formats: PDF, DOC, DOCX</p>
    </div>
  </div>
</template>