<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import '@fortawesome/fontawesome-free/css/all.css';
import Modal from '@/Components/Modal.vue';

const parsedRows = ref([]);
const validationErrors = ref([]);
const isValid = ref(false);
const isLoading = ref(false);
const loadingPercent = ref(0);
let loadingInterval = null;

// Text-based upload
const textInput = ref('');

// Modals
const showSuccessModal = ref(false);
const showErrorModal = ref(false);
const errorMessage = ref('');

// Removed unused TabButton component

// Removed unused upload mode configuration

const props = defineProps({
  degreeCodes: Array,
  programCodes: Array,
  companyNames: Array,
  companyNamesMap: Object,
  institutionSchoolYears: Array,
});

// Removed unused functions for file upload and validation

// Removed unused toTitleCase function

function handleTextInput() {
  parsedRows.value = [];
  validationErrors.value = [];
  isValid.value = false;
  
  if (!textInput.value.trim()) return;
  
  // Basic validation for text format
  if (!textInput.value.includes('Name:')) {
    validationErrors.value.push({
      row: 1,
      messages: ['Text must include at least a "Name:" field']
    });
    return;
  }
  
  isValid.value = true; // Text format doesn't have detailed validation in frontend
}

function submitToBackend() {
  isLoading.value = true;
  loadingPercent.value = 0;
  
  // Start loading animation
  loadingInterval = setInterval(() => {
    loadingPercent.value += Math.random() * 15;
    if (loadingPercent.value > 90) loadingPercent.value = 90;
  }, 500);

  const formData = new FormData();
  formData.append('text_data', textInput.value);
  
  router.post(route('profile.batch.upload.text'), formData, {
    forceFormData: true,
    preserveState: true,
    onFinish: () => {
      clearInterval(loadingInterval);
      loadingPercent.value = 100;
      setTimeout(() => {
        isLoading.value = false;
      }, 500);
    },
    onSuccess: () => {
      textInput.value = '';
      validationErrors.value = [];
      isValid.value = false;
      showSuccessModal.value = true;
    },
    onError: (errors) => {
      if (errors.response?.data?.errors) {
        validationErrors.value = errors.response.data.errors;
      } else {
        errorMessage.value = 'Backend error occurred. Please try again.';
        showErrorModal.value = true;
        console.error(errors);
      }
    },
  });
}

// Removed unused function for file input trigger

function goBack() {
  window.history.back();
}

const stats = computed(() => {
  return [
    {
      title: 'Total Records',
      value: parsedRows.value.length,
      icon: 'fas fa-file-alt',
      color: 'text-blue-600',
      bgColor: 'bg-blue-100'
    },
    {
      title: 'Valid Records',
      value: parsedRows.value.length - new Set(validationErrors.value.map(e => e.row)).size,
      icon: 'fas fa-check-circle',
      color: 'text-green-600',
      bgColor: 'bg-green-100'
    },
    {
      title: 'Records with Errors',
      value: new Set(validationErrors.value.map(e => e.row)).size,
      icon: 'fas fa-exclamation-triangle',
      color: 'text-red-600',
      bgColor: 'bg-red-100'
    }
  ];
});
</script>

<template>
  <div>
    <!-- Back Button and Header -->
    <div class="flex items-center mt-6 mb-4">
      <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
        <i class="fas fa-chevron-left"></i>
      </button>
      <div class="flex items-center">
        <i class="fas fa-upload text-blue-500 text-xl mr-2"></i>
        <h1 class="text-2xl font-bold text-gray-800">Batch Upload Graduates</h1>
      </div>
    </div>

    <!-- Stats Cards (only show when data is loaded) -->
    <div v-if="parsedRows.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <div v-for="(stat, index) in stats" :key="index" 
           class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 relative overflow-hidden">
        <div class="flex justify-between items-start">
          <div>
            <h3 class="text-gray-600 text-sm font-medium mb-2">{{ stat.title }}</h3>
            <p class="text-3xl font-bold text-gray-800">{{ stat.value }}</p>
          </div>
          <div :class="[stat.bgColor, 'rounded-full p-3 flex items-center justify-center']">
            <i :class="[stat.icon, stat.color]"></i>
          </div>
        </div>
      </div>
    </div>

    <div>
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
        <!-- Header with back button -->
        <div class="p-5 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <i class="fas fa-file-upload text-blue-500 text-xl mr-2"></i>
              <h2 class="text-lg font-semibold text-gray-800">Batch Upload</h2>
            </div>
          </div>
        </div>
        
        <!-- Text Input Section -->
        <div class="p-5 border-b border-gray-200">
          <div class="mb-4">
            <div class="flex items-center mb-2">
              <i class="fas fa-info-circle text-blue-500 mr-2"></i>
              <h3 class="font-medium text-gray-700">Text Format Instructions</h3>
            </div>
            <div class="bg-blue-50 p-4 rounded-lg text-sm text-gray-700 border border-blue-100">
              <p>Enter profile data in the format shown below:</p>
              <pre class="mt-2 bg-white p-2 rounded border border-gray-200 text-xs overflow-auto">
Short-term Goals: [goal text]
Long-term Goals: [goal text]
Industries of Interest: [industries]
Experience Title: [job title]
Experience Company: [company name]
Experience Start Date: [YYYY-MM-DD]
Experience End Date: [YYYY-MM-DD or "Present"]
Experience Address: [job location]
Experience Description: [job description]
Employment Type: [Full-time, Part-time, Contract, etc.]
...
              </pre>
              <p class="mt-2">The system will automatically parse fields based on the titles.</p>
            </div>
          </div>
          <textarea 
            v-model="textInput" 
            @input="handleTextInput"
            class="w-full h-64 p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
            placeholder="Name: John Doe
Short-term Goals: Gain professional experience in Software Development.
Long-term Goals: Become a senior specialist or manager in Cybersecurity.
..."></textarea>
        </div>

        <!-- Validation Errors -->
        <div v-if="validationErrors.length" class="p-5 border-b border-gray-200">
          <div class="flex items-center mb-4">
            <i class="fas fa-exclamation-circle text-red-500 text-xl mr-2"></i>
            <h3 class="font-semibold text-gray-800">Validation Issues</h3>
          </div>
          <div class="bg-red-50 rounded-lg p-4 border border-red-100 max-h-60 overflow-y-auto">
            <ul class="space-y-2">
              <li v-for="error in validationErrors" :key="error.row" class="p-2 bg-white rounded border border-red-200">
                <div class="font-medium text-red-700 flex items-center">
                  <i class="fas fa-times-circle mr-2"></i>
                  Row {{ error.row }}
                </div>
                <ul class="mt-1 pl-6 text-sm text-red-600 space-y-1">
                  <li v-for="(msg, idx) in error.messages" :key="idx" class="flex items-start">
                    <span class="inline-block w-2 h-2 rounded-full bg-red-400 mt-1.5 mr-2"></span>
                    {{ msg }}
                  </li>
                </ul>
              </li>
            </ul>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!textInput" class="p-12 flex flex-col items-center justify-center text-center">
          <i class="fas fa-file-upload text-gray-300 text-5xl mb-4"></i>
          <h3 class="text-lg font-medium text-gray-700 mb-2">No Data to Preview</h3>
          <p class="text-gray-500 mb-6">Enter text data in the format shown above</p>
        </div>

        <!-- Submit Button -->
        <div v-if="textInput" class="p-5 border-t border-gray-200 flex justify-end">
          <button
            @click="submitToBackend"
            :disabled="!textInput.trim()"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition"
          >
            <i class="fas fa-cloud-upload-alt mr-2"></i>
            Submit Text Data
          </button>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="isLoading" class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-black bg-opacity-60">
      <div class="bg-white rounded-lg p-8 flex flex-col items-center shadow-lg max-w-md w-full">
        <div class="flex items-center mb-4">
          <i class="fas fa-cloud-upload-alt text-blue-500 text-xl mr-2"></i>
          <h3 class="text-lg font-semibold text-gray-800">Uploading Graduates</h3>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
          <div class="bg-blue-600 h-4 rounded-full transition-all duration-200" :style="{ width: loadingPercent + '%' }"></div>
        </div>
        <div class="text-sm text-gray-600 flex items-center">
          <i class="fas fa-spinner fa-spin mr-2"></i>
          {{ Math.floor(loadingPercent) }}% complete
        </div>
        <div class="text-xs text-gray-400 mt-2">Please do not close or refresh this page.</div>
      </div>
    </div>

    <!-- Success Modal -->
    <Modal :show="showSuccessModal" @close="showSuccessModal = false">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-green-100 rounded-full p-3">
            <i class="fas fa-check text-green-500 text-xl"></i>
          </div>
        </div>
        <h3 class="text-lg font-semibold text-center mb-2">Upload Successful</h3>
        <p class="text-gray-600 text-center mb-4">Your graduate data has been successfully uploaded.</p>
        <div class="flex justify-center">
          <PrimaryButton @click="showSuccessModal = false">
             Close
           </PrimaryButton>
        </div>
      </div>
    </Modal>

    <!-- Error Modal -->
    <Modal :show="showErrorModal" @close="showErrorModal = false">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-red-100 rounded-full p-3">
            <i class="fas fa-times text-red-500 text-xl"></i>
          </div>
        </div>
        <h3 class="text-lg font-semibold text-center mb-2">Upload Failed</h3>
        <p class="text-gray-600 text-center mb-4">{{ errorMessage }}</p>
        <div class="flex justify-center">
          <PrimaryButton @click="showErrorModal = false">
             Close
           </PrimaryButton>
        </div>
      </div>
    </Modal>
  </div>
</template>