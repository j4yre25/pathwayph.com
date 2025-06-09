<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

// Props
const props = defineProps({
  activeSection: {
    type: String,
    default: 'career-goals'
  },
  careerGoals: {
    type: Object,
    default: () => ({
      short_term_goals: '',
      long_term_goals: '',
      industries_of_interest: '',
      career_path: '',
    })
  }
});

const emit = defineEmits(['close-all-modals', 'reset-all-states']);

// State
const newIndustry = ref('');
const isAddIndustryModalOpen = ref(false);
const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Main career goals state (snake_case)
const careerGoals = ref({
  short_term_goals: props.careerGoals?.short_term_goals || '',
  long_term_goals: props.careerGoals?.long_term_goals || '',
  industries_of_interest: props.careerGoals?.industries_of_interest
    ? props.careerGoals.industries_of_interest.split(',').filter(Boolean) : [],
  career_path: props.careerGoals?.career_path || '',
});

// Watch for prop changes
watch(
  () => props.careerGoals,
  (newVal) => {
    careerGoals.value = {
      short_term_goals: newVal?.short_term_goals || '',
      long_term_goals: newVal?.long_term_goals || '',
      industries_of_interest: newVal?.industries_of_interest
        ? newVal.industries_of_interest.split(',').filter(Boolean) : [],
      career_path: newVal?.career_path || '',
    };
  },
  { immediate: true }
);

// Save handler
const saveCareerGoals = () => {
  if (!careerGoals.value.short_term_goals || !careerGoals.value.long_term_goals) {
    errorMessage.value = "Please fill in both short-term and long-term goals.";
    isErrorModalOpen.value = true;
    return;
  }

  const form = useForm({
    short_term_goals: careerGoals.value.short_term_goals,
    long_term_goals: careerGoals.value.long_term_goals,
    industries_of_interest: careerGoals.value.industries_of_interest.join(','),
    career_path: careerGoals.value.career_path,
  });

  form.post(route('career.goals.save'), {
    onSuccess: () => {
      successMessage.value = "Career goals saved successfully!";
      isSuccessModalOpen.value = true;
    },
    onError: (errors) => {
      errorMessage.value = "An error occurred while saving the career goals. Please try again.";
      isErrorModalOpen.value = true;
    }
  });
};

// Add Industry Handler
const addPreferredIndustry = () => {
  const industry = newIndustry.value.trim();
  if (!industry) {
    errorMessage.value = "Please enter a valid industry name.";
    isErrorModalOpen.value = true;
    return;
  }
  if (careerGoals.value.industries_of_interest.includes(industry)) {
    errorMessage.value = "This industry is already in your preferences.";
    isErrorModalOpen.value = true;
    return;
  }
  careerGoals.value.industries_of_interest.push(industry);

  // Save immediately to backend
  const form = useForm({
    industries_of_interest: careerGoals.value.industries_of_interest,
  });

  form.post(route('career.goals.add.industry'), {
    onSuccess: () => {
      successMessage.value = "Industry added successfully!";
      isSuccessModalOpen.value = true;
      closeAddIndustryModal();
    },
    onError: () => {
      errorMessage.value = "An error occurred while saving the industry. Please try again.";
      isErrorModalOpen.value = true;
    }
  });
};

// Reset handler
const resetCareerGoals = () => {
  careerGoals.value = {
    short_term_goals: '',
    long_term_goals: '',
    industries_of_interest: [],
    career_path: '',
  };
};

// Modal handlers
const openAddIndustryModal = () => {
  isAddIndustryModalOpen.value = true;
  newIndustry.value = '';
};
const closeAddIndustryModal = () => {
  isAddIndustryModalOpen.value = false;
  newIndustry.value = '';
};
const closeSuccessModal = () => {
  isSuccessModalOpen.value = false;
};
const closeErrorModal = () => {
  isErrorModalOpen.value = false;
};
</script>

<template>
  <div v-if="activeSection === 'career-goals'" class="flex flex-col lg:flex-row">
    <!-- Success Modal -->
    <Modal :show="isSuccessModalOpen" @close="closeSuccessModal">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-green-100 rounded-full p-2">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
    <Modal :show="isErrorModalOpen" @close="closeErrorModal">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-red-100 rounded-full p-2">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

    <div class="w-full lg:w-1/1 mb-6 lg:mb-0">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold mb-4">Career Goals</h1>
      </div>
      <p class="text-gray-600 mb-6">Define your short and long-term career aspirations</p>
      <!-- Saved Career Goals Container -->
      <div v-if="careerGoals.short_term_goals || careerGoals.long_term_goals"
        class="mb-6 p-4 border border-gray-300 rounded-lg bg-gray-50">
        <h2 class="text-xl font-semibold mb-4">Saved Career Goals</h2>
        <div class="space-y-3">
          <p><strong>Short-term Goals:</strong> {{ careerGoals.short_term_goals }}</p>
          <p><strong>Long-term Goals:</strong> {{ careerGoals.long_term_goals }}</p>
          <p><strong>Industries of Interest:</strong>
            <span v-if="careerGoals.industries_of_interest.length">
              {{ careerGoals.industries_of_interest.join(', ') }}
            </span>
            <span v-else>No industries specified</span>
          </p>
          <p><strong>Career Path:</strong> {{ careerGoals.career_path || 'Not specified' }}</p>
        </div>
      </div>
      <!-- Form Fields -->
      <!-- Short-term Goals -->
      <div class="mb-6">
        <h2 class="text-xl font-semibold mb-2">Short-term Goals (1-2 years)</h2>
        <textarea class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" rows="3"
          v-model="careerGoals.short_term_goals" placeholder="Enter your short-term career goals..."></textarea>
      </div>

      <!-- Long-term Goals -->
      <div class="mb-6">
        <h2 class="text-xl font-semibold mb-2">Long-term Goals (3-5 years)</h2>
        <textarea class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600" rows="3"
          v-model="careerGoals.long_term_goals" placeholder="Enter your long-term career goals..."></textarea>
      </div>

      <!-- Industries of Interest -->
      <div class="mb-6">
        <h2 class="text-xl font-semibold mb-2">Industries of Interest</h2>
        <div class="flex flex-wrap gap-2">
          <span v-for="industry in careerGoals.industries_of_interest" :key="industry"
            class="bg-gray-200 text-gray-700 px-4 py-2 rounded-full">
            {{ industry }}
          </span>
          <button class="bg-indigo-600 text-white px-4 py-2 rounded-full hover:bg-indigo-700"
            @click="openAddIndustryModal">
            + Add Industry
          </button>
        </div>
      </div>

      <!-- Career Path -->
      <div class="mb-6">
        <h2 class="text-xl font-semibold mb-2">Career Path</h2>
        <input type="text"
          class="w-full border border-gray-300 rounded-md p-2 outline-none focus:ring-2 focus:ring-indigo-600"
          v-model="careerGoals.career_path" placeholder="Enter your career path" />
      </div>

      <!-- Additional Notes -->
      

      <div class="flex space-x-4">
        <button class="bg-indigo-600 text-white px-6 py-3 rounded-lg flex items-center" @click="saveCareerGoals">
          <i class="fas fa-save mr-2"></i> Save Goals
        </button>
        <button class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg flex items-center hover:bg-gray-400"
          @click="resetCareerGoals">
          <i class="fas fa-undo mr-2"></i> Reset Goals
        </button>
      </div>
    </div>

    <!-- Add Industry Modal -->
    <div v-if="isAddIndustryModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold">Add Industry</h2>
          <button class="text-gray-500 hover:text-gray-700" @click="closeAddIndustryModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <p class="text-gray-600 mb-4">Add a preferred industry of interest to your profile.</p>
        <form @submit.prevent="addPreferredIndustry">
          <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Industry <span class="text-red-500">*</span></label>
            <input type="text" v-model="newIndustry"
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
              placeholder="e.g. Technology, Healthcare" required />
          </div>
          <div class="flex justify-end">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Add</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>