<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import { useForm, usePage,  router  } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
  activeSection: {
    type: String,
    default: 'resume'
  },
  resume: Object // Pass the current resume from backend if available
});


const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const form = useForm({
  resume: null
});

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
    onSuccess: () => {
      successMessage.value = 'Resume uploaded successfully!';
      isSuccessModalOpen.value = true;
      form.reset();
      router.reload({ only: ['resume'] });
      // Optionally reload the page or fetch the new resume
    },
    onError: () => {
      errorMessage.value = 'Failed to upload resume. Please try again.';
      isErrorModalOpen.value = true;
    }
  });
};

const removeResume = () => {
  // You may want to confirm before deleting
  if (confirm('Are you sure you want to delete your resume?')) {
    form.delete(route('resume.delete'), {
      onSuccess: () => {
        successMessage.value = 'Resume deleted successfully!';
        isSuccessModalOpen.value = true;
        router.reload({ only: ['resume'] });
        // Optionally reload the page or clear resume data
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
        <PrimaryButton @click="isSuccessModalOpen = false">OK</PrimaryButton>
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
        <div v-if="props.resume && props.resume.file_name" class="flex items-center justify-between border border-gray-300 rounded-lg p-8 mb-4">
          <div class="flex items-center">
            <i class="fas fa-file-alt text-gray-500 text-2xl mr-4"></i>
            <a :href="props.resume.file_url" target="_blank" class="text-indigo-600 hover:underline">
              {{ props.resume.file_name }}
            </a>
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
        <button v-if="form.resume" @click="saveResume"
          class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-600 text-center mt-4">
          <i class="fas fa-save mr-2"></i> Save Resume
        </button>
      </div>
    </div>
  </div>
</template>