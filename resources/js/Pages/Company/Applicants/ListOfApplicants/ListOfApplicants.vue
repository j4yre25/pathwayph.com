<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import GraduatePortfolio from '@/Pages/Frontend/GraduatePortfolio.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

import CandidatePipeline from '@/Components/CandidatePipeline.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  applicants: Array,
  filters: Object,
  pipelineStages: Array,
  statuses: Array,
});

// Reactive variables
const selectedApplicant = ref(null);
const isViewDetailsModalOpen = ref(false);
const portfolioData = ref(null);
const isLoading = ref(false);
const actionError = ref('');
const isActionLoading = ref(false);
const activeApplicantId = ref(null);
const isScheduleModalOpen = ref(false);
const isRejectModalOpen = ref(false);
const isUpdateStatusModalOpen = ref(false);
const selectedStatus = ref('');
const selectedPipelineStage = ref('');
const interviewDate = ref('');
const interviewTime = ref('');
const interviewLocation = ref('');
const interviewNotes = ref('');
const rejectionReason = ref('');
const formErrors = ref({});

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
    const response = await axios.get(route('applicants.portfolio', { user: applicantId }));
    portfolioData.value = response.data;
  } catch (error) {
    console.error('Error fetching portfolio data:', error);
    actionError.value = 'Failed to load portfolio data. Please try again.';
  } finally {
    isLoading.value = false;
  }
};

// Function to view applicant details

const viewApplicantDetails = (applicant) => {
  router.get(route('applicants.show', applicant.id));
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

        <div class="p-4 flex flex-col md:flex-row md:items-start md:justify-between md:space-x-4">
          <!-- Applicant details (left side) -->
          <div class="w-full md:w-1/3 mb-3 md:mb-0 md:mr-4 flex items-start space-x-4">
            <!-- Profile Image -->
            <img
              :src="applicant.profile_picture || '/default-avatar.png'"
              alt="Profile"
              class="w-14 h-14 mr-2 rounded-full object-cover border border-gray-300"
            />

            <!-- Name, Email, etc. -->
            <div class="min-w-0">
              <div>
                <h3
                  class="text-lg font-semibold text-gray-800 truncate mr-2"
                  :class="{ 'opacity-50': isActionLoading && activeApplicantId === applicant.id }"
                  :aria-disabled="isActionLoading && activeApplicantId === applicant.id"
                  role="button"
                  tabindex="0"
                  :aria-label="`View details for ${applicant.name}`"
                >
                  {{ applicant.name }}
                </h3>
                <div class="flex items-center text-sm text-gray-500 mt-1">
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
          </div>
          
          <!-- Pipeline and Details Button -->
          <div class="w-full md:w-2/3 mt-4 md:mt-0">
            <div class="rounded-lg p-4">
              <div class="flex justify-between items-center">
                <div class="flex-1">
                  <p class="text-sm font-semibold text-gray-800 mb-2">Hiring Process</p>
                  <CandidatePipeline :stage="applicant.stage" />
                </div>
                <PrimaryButton
                  @click="() => viewApplicantDetails(applicant)"
                  :disabled="isActionLoading && activeApplicantId === applicant.id"
                  :class="{ 'opacity-50 cursor-not-allowed': isActionLoading && activeApplicantId === applicant.id }"
                  class="text-xs"
                  aria-label="View details">
                  View More &gt;
                </PrimaryButton>
              </div>
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
  
  <!-- Portfolio Modal -->  
  <Modal :show="isViewDetailsModalOpen" @close="closeModal" max-width="6xl">
    <div class="p-6">
      <div v-if="isLoading" class="flex justify-center items-center py-8">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900"></div>
      </div>
      <div v-else-if="actionError" class="text-center text-red-600 py-8">
        <p>{{ actionError || 'Unable to load portfolio data.' }}</p>
      </div>
      <div v-else-if="portfolioData">
        <GraduatePortfolio :graduate-data="portfolioData" />
      </div>
      <div v-else class="text-center text-gray-600 py-8">
        <p>No portfolio data available for this applicant.</p>
      </div>
    </div>
  </Modal>
</template>