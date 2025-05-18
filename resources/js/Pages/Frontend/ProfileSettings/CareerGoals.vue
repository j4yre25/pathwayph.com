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
    default: 'career-goals'
  }
});


// Career Goals Data
const newIndustry = ref('');
const careerGoalsEntries = ref(props.careerGoalsEntries || []);

const careerGoals = ref({
  shortTermGoals: '',
  longTermGoals: '',
  industriesOfInterest: [],
  careerPath: ''
});

const fetchCareerGoals = async () => {
  try {
    const response = await axios.get(route('career.goals.get'));
    if (response.data) {
      careerGoals.value = {
        shortTermGoals: response.data.shortTermGoals || '',
        longTermGoals: response.data.longTermGoals || '',
        industriesOfInterest: response.data.industriesOfInterest ? response.data.industriesOfInterest.split(',') : [],
        careerPath: response.data.careerPath || ''
      };
    }
  } catch (error) {
    console.error('Error fetching career goals:', error);
  }
};

onMounted(() => {
  fetchCareerGoals();
});


const isAddIndustryModalOpen = ref(false);

const handleSaveCareerGoals = () => {
  // Your save logic here
  router.post('/graduate/career-goals', careerGoals.value, {
    onSuccess: () => {
      savedCareerGoals.value = { ...careerGoals.value };
    },
  });
};

// Career Goals Handlers
const savedCareerGoals = () => {
  console.log("Saving career goals:", careerGoals.value); // Debugging
  if (!careerGoals.value.shortTermGoals || !careerGoals.value.longTermGoals) {
    alert("Please fill in both short-term and long-term goals.");
    return;
  }

  const careerGoalsForm = useForm({
    shortTermGoals: careerGoals.value.shortTermGoals,
    longTermGoals: careerGoals.value.longTermGoals,
    industriesOfInterest: careerGoals.value.industriesOfInterest,
    careerPath: careerGoals.value.careerPath,
  });

  console.log("Data being sent to backend for career goals:", careerGoalsForm.data());

  careerGoalsForm.post(route('career.goals.save'), {
    onSuccess: (response) => {
      console.log("Backend response for career goals:", response);
      careerGoalsEntries.value.push({ ...careerGoalsForm.data(), id: response.id });
    },
    onError: (errors) => {
      console.error("Error saving career goals:", errors);
      alert("An error occurred while saving the career goals. Please try again.");
    },
  });
};

const closeAddIndustryModal = () => {
  isAddIndustryModalOpen.value = false;
  newIndustry.value = '';
};

const addPreferredIndustry = () => {
  if (!newIndustry.value.trim()) {
    alert("Please enter a valid industry name.");
    return;
  }

  if (!careerGoals.value.industriesOfInterest.includes(newIndustry.value.trim())) {
    careerGoals.value.industriesOfInterest.push(newIndustry.value.trim());

    console.log("New Industry Added:", newIndustry.value);
    console.log("Updated Industries of Interest:", careerGoals.value.industriesOfInterest);

    const careerGoalsForm = useForm({
      industriesOfInterest: careerGoals.value.industriesOfInterest,
    });

    console.log("Data being sent to backend:", careerGoalsForm.data());

    careerGoalsForm.post(route('career.goals.add.industry'), {
      onSuccess: (response) => {
        console.log("Backend response:", response);
      },
      onError: (errors) => {
        console.error("Error saving industry:", errors);
        alert("An error occurred while saving the industry. Please try again.");
      },
    });
  } else {
    alert("This industry is already in your preferences.");
  }

  closeAddIndustryModal();
};


const resetCareerGoals = () => {
  careerGoals.value = {
    shortTermGoals: '',
    longTermGoals: '',
    industriesOfInterest: [],
    careerPath: ''
  };
  console.log('Career goals reset.');
};

const openAddIndustryModal = () => {
  isAddIndustryModalOpen.value = true;
  console.log('Add industry modal opened.');
};

// Function to initialize data on component mount
const initializeData = () => {
  // Fetch initial data or set defaults
  console.log('Initializing data...');
};

// Call initialize function on component mount
onMounted(() => {
  initializeData();
});

</script>

<template>
  <div v-if="activeSection === 'career-goals'" class="flex flex-col lg:flex-row">
  <!-- Success Modal -->
  <Modal :show="isSuccessModalOpen" @close="closeSuccessModal">
      <div class="p-6">
          <div class="flex items-center justify-center mb-4">
              <div class="bg-green-100 rounded-full p-2">
                  <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
              </div>
          </div>
          <h3 class="text-lg font-medium text-center mb-4">{{ successMessage }}</h3>
          <div class="flex justify-center">
              <button @click="closeSuccessModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
                  <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
              </div>
          </div>
          <h3 class="text-lg font-medium text-center mb-4">{{ errorMessage }}</h3>
          <div class="flex justify-center">
              <button @click="closeErrorModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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
    <div v-if="careerGoals.shortTermGoals || careerGoals.longTermGoals"
      class="mb-6 p-4 border border-gray-300 rounded-lg bg-gray-50">
      <h2 class="text-xl font-semibold mb-4">Saved Career Goals</h2>
      <div class="space-y-3">
        <p><strong>Short-term Goals:</strong> {{ careerGoals.shortTermGoals }}</p>
        <p><strong>Long-term Goals:</strong> {{ careerGoals.longTermGoals }}</p>
        <p><strong>Industries of Interest:</strong>
          <span v-if="careerGoals.industriesOfInterest.length">
            {{ careerGoals.industriesOfInterest.join(', ') }}
          </span>
          <span v-else>No industries specified</span>
        </p>
        <p><strong>Career Path:</strong> {{ careerGoals.careerPath || 'Not specified' }}</p>
      </div>
    </div>
    <!-- Form Fields -->
    <div class="mb-6">
      <h2 class="text-xl font-semibold mb-2">Short-term Goals (1-2 years)</h2>
      <textarea class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600"
        rows="3" v-model="careerGoals.shortTermGoals" placeholder="Enter your short-term career goals...">
    </textarea>
    </div>
    <div class="mb-6">
      <h2 class="text-xl font-semibold mb-2">Long-term Goals (3-5 years)</h2>
      <textarea class="w-full p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600"
        rows="3" v-model="careerGoals.longTermGoals" placeholder="Enter your long-term career goals...">
    </textarea>
    </div>
    <div class="mb-6">
      <h2 class="text-xl font-semibold mb-2">Industries of Interest</h2>
      <div class="flex flex-wrap gap-2">
        <span v-for="industry in careerGoals.industriesOfInterest" :key="industry"
          class="bg-gray-200 text-gray-700 px-4 py-2 rounded-full">
          {{ industry }}
        </span>
        <button class="bg-indigo-600 text-white px-4 py-2 rounded-full hover:bg-indigo-700"
          @click="openAddIndustryModal">
          + Add Industry
        </button>
      </div>
    </div>
    <div class="mb-6">
      <h2 class="text-xl font-semibold mb-2">Career Path</h2>
      <input type="text"
        class="w-full border border-gray-300 rounded-md p-2 outline-none focus:ring-2 focus:ring-indigo-600"
        v-model="careerGoals.careerPath" placeholder="Enter your career path" />
    </div>
    <div class="flex space-x-4">
      <button class="bg-indigo-600 text-white px-6 py-3 rounded-lg flex items-center"
        @click="savedCareerGoals">
        <i class="fas fa-save mr-2"></i> Save Goals
      </button>
      <button class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg flex items-center hover:bg-gray-400"
        @click="resetCareerGoals">
        <i class="fas fa-undo mr-2"></i> Reset Goals
      </button>
    </div>
  </div>

  <!-- Add Industry Modal -->
  <div v-if="isAddIndustryModalOpen"
    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
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
          <label class="block text-gray-700 font-medium mb-2">Industry <span
              class="text-red-500">*</span></label>
          <input type="text" v-model="newIndustry"
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
            placeholder="e.g. Technology, Healthcare" required />
        </div>
        <div class="flex justify-end">
          <button type="submit"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Add</button>
        </div>
      </form>
    </div>
  </div>
</div>

</template>