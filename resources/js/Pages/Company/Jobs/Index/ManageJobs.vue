<script setup>
import { router, usePage, Link } from '@inertiajs/vue3'; 
import { ref, computed } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Container from '@/Components/Container.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const page = usePage()

const props = defineProps({
  jobs: Array,
  job: Object,
  sectors: Array,
  categories: Array,
  isLoading: {
    type: Boolean,
    default: false
  }
});

// Search and filter functionality
const searchQuery = ref('');
const selectedSector = ref('');
const selectedCategory = ref('');

// Modal and action states
const showModal = ref(false);
const jobToArchive = ref(null);
const isActionLoading = ref(false);
const activeJobId = ref(null);
const actionError = ref(null);

// Filtered jobs based on search and filters
const filteredJobs = computed(() => {
  return props.jobs ? props.jobs.filter(job => {
    const matchesSearch = job.job_title.toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchesSector = selectedSector.value ? job.sector === selectedSector.value : true;
    const matchesCategory = selectedCategory.value ? job.category === selectedCategory.value : true;
    return matchesSearch && matchesSector && matchesCategory;
  }) : [];
});

// Navigate to job details
const goToJob = (jobId) => {
  router.visit(route('company.jobs.view', { job: jobId }));
};

// Archive job functionality
const archiveJob = () => {
    if (jobToArchive.value) {
        isActionLoading.value = true;
        activeJobId.value = jobToArchive.value.id;
        
        router.delete(route('company.jobs.delete', { job: jobToArchive.value.id }), {
          onSuccess: () => {
            showModal.value = false;
            jobToArchive.value = null;
            isActionLoading.value = false;
            activeJobId.value = null;
          },
          onError: () => {
            actionError.value = "Failed to archive job. Please try again.";
            isActionLoading.value = false;
            activeJobId.value = null;
          }
        });
    }
};

const confirmArchive = (job) => {
    jobToArchive.value = job;
    showModal.value = true;
};

// Function to get status class based on job status
const getStatusClass = (status) => {
  if (status === 'open') return 'bg-green-100 text-green-800';
  if (status === 'closed') return 'bg-red-100 text-red-800';
  return 'bg-yellow-100 text-yellow-800';
};

// Function to get status text
const getStatusText = (status) => {
  return status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Pending';
};

</script>

<template>
  <AppLayout title="Manage Jobs">
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
          <i class="fas fa-briefcase text-blue-500 mr-2"></i> Manage Jobs
        </h2>
        <Link :href="route('company.jobs.create', { user: page.props.auth.user.id })" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
          <i class="fas fa-plus mr-1"></i> Post New Job
        </Link>
      </div>
    </template>

    <Container class="py-6">
      <!-- Search and filters section -->
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Search input -->
          <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Jobs</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
              </div>
              <input
                id="search"
                v-model="searchQuery"
                type="text"
                placeholder="Search by job title"
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              />
            </div>
          </div>

          <!-- Sector filter -->
          <div v-if="props.sectors">
            <label for="sector" class="block text-sm font-medium text-gray-700 mb-1">Sector</label>
            <div class="relative">
              <select
                id="sector"
                v-model="selectedSector"
                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              >
                <option value="">All Sectors</option>
                <option v-for="sector in props.sectors" :key="sector.id" :value="sector.id">{{ sector.name }}</option>
              </select>
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <i class="fas fa-chevron-down text-gray-400"></i>
              </div>
            </div>
          </div>

          <!-- Category filter -->
          <div v-if="props.categories">
            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <div class="relative">
              <select
                id="category"
                v-model="selectedCategory"
                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              >
                <option value="">All Categories</option>
                <option v-for="category in props.categories" :key="category.id" :value="category.id">{{ category.name }}</option>
              </select>
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <i class="fas fa-chevron-down text-gray-400"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Jobs grid layout -->
      <div v-if="filteredJobs.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="job in filteredJobs" :key="job.id" class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 hover:shadow-md transition-shadow duration-300">
          <!-- Job card header -->
          <div class="bg-blue-50 p-4 border-b border-gray-200">
            <div class="flex justify-between items-start">
              <h3 class="text-lg font-semibold text-blue-800 truncate" :title="job.job_title">{{ job.job_title }}</h3>
              <span :class="getStatusClass(job.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                {{ getStatusText(job.status) }}
              </span>
            </div>
            <div class="mt-2 text-sm text-gray-600 flex items-center">
              <i class="fas fa-map-marker-alt text-gray-500 mr-1"></i>
              <span>{{ job.locations.map(loc => loc.address).join(', ') }}</span>
            </div>
          </div>
          
          <!-- Job card body -->
          <div class="p-4">
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div class="flex items-center">
                <i class="fas fa-briefcase text-blue-500 mr-2"></i>
                <span class="text-gray-700"> {{ job.job_types.map(type => type.type).join(', ') }}</span>
              </div>
              <div class="flex items-center">
                <i class="fas fa-user-graduate text-blue-500 mr-2"></i>
                <span class="text-gray-700">{{ job.job_experience_level }}</span>
              </div>
              <div class="flex items-center col-span-2">
                <i class="fas fa-users text-blue-500 mr-2"></i>
                <span class="text-gray-700">{{ job.applicants_count || 0 }} Applicant(s)</span>
              </div>
            </div>
            
            <!-- Action buttons -->
            <div class="mt-4 flex flex-wrap gap-2">
              <Link :href="route('company.jobs.view', { job: job.id })" class="inline-flex items-center px-3 py-1.5 bg-blue-600 border border-transparent rounded-md font-medium text-xs text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-150">
                <i class="fas fa-eye mr-1"></i> View
              </Link>
              <Link :href="route('company.jobs.edit', { job: job.id })" class="inline-flex items-center px-3 py-1.5 bg-green-600 border border-transparent rounded-md font-medium text-xs text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors duration-150">
                <i class="fas fa-edit mr-1"></i> Edit
              </Link>
              <button 
                @click="confirmArchive(job)" 
                :disabled="isActionLoading && activeJobId === job.id"
                class="inline-flex items-center px-3 py-1.5 bg-red-600 border border-transparent rounded-md font-medium text-xs text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-archive mr-1"></i>
                <span v-if="isActionLoading && activeJobId === job.id">Processing...</span>
                <span v-else>Archive</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else class="bg-white rounded-lg shadow-sm p-8 text-center">
        <div class="flex flex-col items-center justify-center">
          <i class="fas fa-search text-gray-300 text-5xl mb-4"></i>
          <h3 class="text-lg font-medium text-gray-900 mb-1">No jobs found</h3>
          <p class="text-gray-500 mb-4">Try adjusting your search or filter criteria</p>
          <Link :href="route('company.jobs.create', { user: page.props.auth.user.id })" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-150">
            <i class="fas fa-plus mr-1"></i> Post New Job
          </Link>
        </div>
      </div>

      <!-- Confirmation Modal -->
      <ConfirmationModal @close="showModal = false" :show="showModal">
        <template #title>
          Archive Job
        </template>

        <template #content>
          <p>Are you sure you want to archive this job?</p>
          <p v-if="actionError" class="text-red-500 mt-2">{{ actionError }}</p>
        </template>

        <template #footer>
          <DangerButton 
            @click="archiveJob" 
            class="mr-2" 
            :disabled="isActionLoading"
          >
            <i v-if="isActionLoading" class="fas fa-spinner fa-spin mr-1"></i>
            <span v-if="isActionLoading">Processing...</span>
            <span v-else>Archive Job</span>
          </DangerButton>
          <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>