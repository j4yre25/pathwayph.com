<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import { router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import CandidatePipeline from '@/Components/CandidatePipeline.vue';
import '@fortawesome/fontawesome-free/css/all.css';
import Modal from '@/Components/Modal.vue';
import axios from 'axios';

const page = usePage();

const props = defineProps({
  applicants: {
    type: Array,
    required: true,
  }
});

const selectedApplicant = ref(null);
const isViewDetailsModalOpen = ref(false);
const portfolioData = ref(null);
const isLoading = ref(false);

// Action loading state
const isActionLoading = ref(false);
const activeApplicantId = ref(null);
const actionError = ref(null);

// Function to get status class based on applicant status
const getStatusClass = (status) => {
  if (status === 'accepted' || status === 'hired') return 'bg-green-100 text-green-800';
  if (status === 'rejected') return 'bg-red-100 text-red-800';
  if (status === 'pending' || status === 'applied') return 'bg-yellow-100 text-yellow-800';
  if (status === 'declined') return 'bg-indigo-100 text-indigo-800';
  return 'bg-gray-100 text-gray-800';
};

// Function to get status text
const getStatusText = (status) => {
  return status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Unknown';
};

// Function to fetch graduate portfolio data
const fetchPortfolioData = async (applicantId) => {
  if (!applicantId) return;
  
  isLoading.value = true;
  try {
    // Fetch the portfolio data for the selected applicant
    const response = await axios.get(route('graduate.portfolio', { user: applicantId }));
    portfolioData.value = response.data;
  } catch (error) {
    console.error('Error fetching portfolio data:', error);
    actionError.value = 'Failed to load portfolio data. Please try again.';
  } finally {
    isLoading.value = false;
  }
};

// Function to view applicant details
const viewApplicantDetails = async (applicant) => {
  selectedApplicant.value = applicant;
  isViewDetailsModalOpen.value = true;
  
  // Fetch portfolio data for the selected applicant
  await fetchPortfolioData(applicant.user_id);
};

// Close the modal
const closeModal = () => {
  isViewDetailsModalOpen.value = false;
  selectedApplicant.value = null;
  portfolioData.value = null;
};
</script>

<template>
  <div>
    <div v-if="actionError" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
      <strong class="font-bold">Error!</strong>
      <span class="block sm:inline">{{ actionError }}</span>
      <button @click="actionError = null" class="absolute top-0 bottom-0 right-0 px-4 py-3" aria-label="Close alert">
        <i class="fas fa-times"></i>
      </button>
    </div>
    
    <!-- Applicant listings as rows -->
    <div v-if="props.applicants && props.applicants.length > 0" class="divide-y divide-gray-200">
      <div v-for="applicant in props.applicants" :key="applicant.id" 
           class="hover:bg-gray-50 transition-colors duration-150">
        
        <div class="p-4 flex flex-col md:flex-row md:items-center md:justify-between">
          <div class="flex-1 min-w-0 mb-3 md:mb-0 md:mr-4">
            <div class="flex flex-col md:flex-row md:items-center">
              <h3 class="text-lg font-semibold text-gray-800 truncate mr-2"
                  :class="{ 'opacity-50': isActionLoading && activeApplicantId === applicant.id }"
                  :aria-disabled="isActionLoading && activeApplicantId === applicant.id"
                  role="button"
                  tabindex="0"
                  :aria-label="`View details for ${applicant.name}`">
                  {{ applicant.name }}
              </h3>
              <div class="flex items-center text-sm text-gray-500 mt-1 md:mt-0">
                <i class="fas fa-envelope mr-1" aria-hidden="true"></i>
                <span>{{ applicant.email }}</span>
              </div>
            </div>
            
            <!-- Application details -->
            <div class="flex flex-wrap items-center mt-2 text-sm text-gray-600">
              <div class="mr-4 mb-1">
                <i class="fas fa-calendar mr-1" aria-hidden="true"></i>
                <span>Applied on: {{ applicant.applied_at }}</span>
              </div>
              
              <div class="mb-1">
                <i class="fas fa-comment mr-1" aria-hidden="true"></i>
                <span>{{ applicant.notes || 'No additional notes.' }}</span>
              </div>
            </div>
          </div>
          
          <!-- Pipeline, Status and actions (right side) -->
          <div class="flex flex-col md:flex-row items-center justify-between md:justify-end space-y-2 md:space-y-0 md:space-x-4">
            <!-- Pipeline (visible on larger screens) -->
            <div class="hidden md:block w-32">
              <CandidatePipeline :stage="applicant.stage" />
            </div>
            
            <!-- Status badge -->
            <span :class="[getStatusClass(applicant.status), 'text-xs font-medium px-2.5 py-0.5 rounded-full whitespace-nowrap']">
              {{ getStatusText(applicant.status) }}
            </span>
            
            <!-- Action buttons -->
            <div class="flex space-x-2">
              <!-- View details button -->
              <PrimaryButton
                  @click="() => { selectedApplicant = applicant; isViewDetailsModalOpen = true }"
                  :disabled="isActionLoading && activeApplicantId === applicant.id"
                  :class="{ 'opacity-50 cursor-not-allowed': isActionLoading && activeApplicantId === applicant.id }"
                  class="text-xs"
                  aria-label="View details">
                  View Details
              </PrimaryButton>
            </div>
          </div>
        </div>
      </div>
    </div>
      
    <!-- Empty state when no applicants are available -->
    <div v-else class="bg-white rounded-lg border border-gray-200 p-10 text-center">
      <div class="flex flex-col items-center">
        <i class="fas fa-user-slash text-gray-300 text-4xl mb-3" aria-hidden="true"></i>
        <p class="text-lg font-medium text-gray-800">No Applicants Found</p>
        <p class="text-sm text-gray-500 mt-1">There are currently no applicants for this job.</p>
      </div>
    </div>
  </div>
</template>