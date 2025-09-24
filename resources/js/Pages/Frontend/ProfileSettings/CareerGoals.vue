<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import '@fortawesome/fontawesome-free/css/all.css';

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
const isEditModalOpen = ref(false);
const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const successMessage = ref('');
const errorMessage = ref('');


// Temporary form data for editing
const tempCareerGoals = ref({
  short_term_goals: '',
  long_term_goals: '',
  industries_of_interest: [],
  career_path: '',
});

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
  if (!tempCareerGoals.value.short_term_goals || !tempCareerGoals.value.long_term_goals) {
    errorMessage.value = "Please fill in both short-term and long-term goals.";
    isErrorModalOpen.value = true;
    return;
  }

  const form = useForm({
    short_term_goals: tempCareerGoals.value.short_term_goals,
    long_term_goals: tempCareerGoals.value.long_term_goals,
    industries_of_interest: tempCareerGoals.value.industries_of_interest.join(','),
    career_path: tempCareerGoals.value.career_path,
  });

  form.post(route('career.goals.save'), {
    onSuccess: () => {
      // Update main data with temp data
      careerGoals.value = { ...tempCareerGoals.value };
      successMessage.value = "Career goals saved successfully!";
      isSuccessModalOpen.value = true;
      closeEditModal();
    },
    onError: (errors) => {
      errorMessage.value = "An error occurred while saving the career goals. Please try again.";
      isErrorModalOpen.value = true;
    }
  });
};

// Add industry to temp data
const addIndustryToTemp = () => {
  const industry = newIndustry.value.trim();
  if (!industry) {
    errorMessage.value = "Please enter a valid industry name.";
    isErrorModalOpen.value = true;
    return;
  }
  if (tempCareerGoals.value.industries_of_interest.includes(industry)) {
    errorMessage.value = "This industry is already in your preferences.";
    isErrorModalOpen.value = true;
    return;
  }
  tempCareerGoals.value.industries_of_interest.push(industry);
  closeAddIndustryModal();
};

// Remove industry from temp data
const removeIndustryFromTemp = (industry) => {
  const index = tempCareerGoals.value.industries_of_interest.indexOf(industry);
  if (index > -1) {
    tempCareerGoals.value.industries_of_interest.splice(index, 1);
  }
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
const openEditModal = () => {
  // Copy current data to temp for editing
  tempCareerGoals.value = {
    short_term_goals: careerGoals.value.short_term_goals,
    long_term_goals: careerGoals.value.long_term_goals,
    industries_of_interest: [...careerGoals.value.industries_of_interest],
    career_path: careerGoals.value.career_path,
  };
  isEditModalOpen.value = true;
};
const closeEditModal = () => {
  isEditModalOpen.value = false;
};
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
  <div v-if="activeSection === 'career-goals'">
    <!-- Success Modal -->
    <Modal :modelValue="isSuccessModalOpen" @close="closeSuccessModal">
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
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
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
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center mb-4">{{ errorMessage }}</h3>
        <div class="flex justify-center">
          <button @click="closeErrorModal"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            OK
          </button>
        </div>
      </div>
    </Modal>

    <div class="w-full">
      <!-- Career Goals Header -->
      <div class="flex justify-between items-center p-4 bg-gradient-to-r from-blue-50 to-white border border-blue-100 rounded-lg mb-4">
        <div class="flex items-center">
          <div class="bg-blue-100 p-2 rounded-full mr-3">
            <i class="fas fa-bullseye text-blue-600"></i>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-blue-800">Career Goals</h3>
            <p class="text-sm text-gray-600">Define your short and long-term career aspirations</p>
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <button @click="openEditModal" 
                  class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-sm">
            <i class="fas fa-edit mr-1"></i>Edit
          </button>
        </div>
      </div>

      <!-- Career Goals Content -->
      <div class="transition-all duration-300">
      
      <!-- Career Goals Display -->
      <div v-if="careerGoals.short_term_goals || careerGoals.long_term_goals || careerGoals.industries_of_interest.length || careerGoals.career_path"
        class="mb-6 p-5 border border-blue-100 rounded-lg bg-white shadow-md hover:shadow-lg transition-all duration-300">
        <h2 class="text-lg font-semibold mb-4 text-blue-800 border-b border-blue-100 pb-2">Your Career Goals</h2>
        <div class="space-y-4">
          <div v-if="careerGoals.short_term_goals" class="bg-blue-50 p-3 rounded-md">
            <p class="flex items-start">
              <span class="bg-blue-100 text-blue-800 p-1 rounded mr-2 inline-block">
                <i class="fas fa-flag"></i>
              </span>
              <span>
                <strong class="text-blue-800">Short-term Goals:</strong><br>
                <span class="text-gray-700">{{ careerGoals.short_term_goals }}</span>
              </span>
            </p>
          </div>
          <div v-if="careerGoals.long_term_goals" class="bg-blue-50 p-3 rounded-md">
            <p class="flex items-start">
              <span class="bg-blue-100 text-blue-800 p-1 rounded mr-2 inline-block">
                <i class="fas fa-road"></i>
              </span>
              <span>
                <strong class="text-blue-800">Long-term Goals:</strong><br>
                <span class="text-gray-700">{{ careerGoals.long_term_goals }}</span>
              </span>
            </p>
          </div>
          <div v-if="careerGoals.industries_of_interest.length" class="bg-blue-50 p-3 rounded-md">
            <p class="flex items-start">
              <span class="bg-blue-100 text-blue-800 p-1 rounded mr-2 inline-block">
                <i class="fas fa-industry"></i>
              </span>
              <span>
                <strong class="text-blue-800">Industries of Interest:</strong><br>
                <span class="text-gray-700">
                  <span v-for="(industry, index) in careerGoals.industries_of_interest" :key="index" 
                    class="inline-block bg-white px-2 py-1 rounded-full border border-blue-200 text-sm mr-1 mb-1">
                    {{ industry }}
                  </span>
                </span>
              </span>
            </p>
          </div>
          <div v-if="careerGoals.career_path" class="bg-blue-50 p-3 rounded-md">
            <p class="flex items-start">
              <span class="bg-blue-100 text-blue-800 p-1 rounded mr-2 inline-block">
                <i class="fas fa-map-signs"></i>
              </span>
              <span>
                <strong class="text-blue-800">Career Path:</strong><br>
                <span class="text-gray-700">{{ careerGoals.career_path }}</span>
              </span>
            </p>
          </div>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-else class="text-center py-12 bg-gray-50 rounded-lg">
        <div class="mb-4">
          <i class="fas fa-bullseye text-4xl text-gray-400"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No Career Goals Set</h3>
        <p class="text-gray-500 mb-4">Define your career aspirations to help guide your professional journey.</p>
        <button @click="openEditModal" 
                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
          <i class="fas fa-plus mr-2"></i>Set Career Goals
        </button>
      </div>
      </div> <!-- End Career Goals Content -->
    </div>

    <!-- Edit Career Goals Modal -->
    <div v-if="isEditModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-semibold text-gray-800">Edit Career Goals</h2>
          <button class="text-gray-500 hover:text-gray-700" @click="closeEditModal">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
        
        <div class="space-y-6">
          <!-- Short-term Goals -->
          <div>
            <label for="edit_short_term_goals" class="block text-sm font-medium text-gray-700 mb-2">
              Short-term Goals (1-2 years) *
            </label>
            <textarea
              id="edit_short_term_goals"
              v-model="tempCareerGoals.short_term_goals"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Describe your career goals for the next 1-2 years..."
            ></textarea>
          </div>

          <!-- Long-term Goals -->
          <div>
            <label for="edit_long_term_goals" class="block text-sm font-medium text-gray-700 mb-2">
              Long-term Goals (5+ years) *
            </label>
            <textarea
              id="edit_long_term_goals"
              v-model="tempCareerGoals.long_term_goals"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Describe your long-term career aspirations..."
            ></textarea>
          </div>

          <!-- Industries of Interest -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Industries of Interest
            </label>
            <div class="flex flex-wrap gap-2 mb-3" v-if="tempCareerGoals.industries_of_interest.length > 0">
              <span v-for="(industry, index) in tempCareerGoals.industries_of_interest" :key="index" 
                    class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                {{ industry }}
                <button @click="removeIndustryFromTemp(industry)" class="ml-2 text-blue-600 hover:text-blue-800">
                  ×
                </button>
              </span>
            </div>
            <button @click="openAddIndustryModal" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
              Add Industry
            </button>
          </div>

          <!-- Career Path -->
          <div>
            <label for="edit_career_path" class="block text-sm font-medium text-gray-700 mb-2">
              Preferred Career Path
            </label>
            <textarea
              id="edit_career_path"
              v-model="tempCareerGoals.career_path"
              rows="2"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Describe your preferred career progression path..."
            ></textarea>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end space-x-4 pt-4 border-t">
            <button @click="closeEditModal" 
                    class="px-6 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
              Cancel
            </button>
            <button @click="saveCareerGoals" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
              Save Career Goals
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Industry Modal -->
    <div v-if="isAddIndustryModalOpen" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold text-gray-800">Add Industry</h2>
          <button class="text-gray-500 hover:text-gray-700" @click="closeAddIndustryModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <p class="text-gray-600 mb-4">Add a preferred industry of interest to your profile.</p>
        <form @submit.prevent="addIndustryToTemp">
          <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-2">Industry <span class="text-red-500">*</span></label>
            <input 
              type="text" 
              v-model="newIndustry"
              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-500"
              placeholder="e.g. Technology, Healthcare" 
              required />
          </div>
          <div class="flex justify-end">
            <button 
              type="submit" 
              class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors duration-200">
              Add
            </button>
          </div>
        </form>
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
</style>