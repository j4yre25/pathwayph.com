<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref, computed } from 'vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const page = usePage()

const props = defineProps({
  jobs: Array,
  job: Object,
});

// Search functionality
const searchQuery = ref('');

// Filtered jobs based on search query
const filteredJobs = computed(() => {
  if (!searchQuery.value) return props.jobs || [];
  const query = searchQuery.value.toLowerCase();
  return (props.jobs || []).filter(job =>
    (job.job_title || '').toLowerCase().includes(query) ||
    (job.location || '').toLowerCase().includes(query) ||
    (job.job_type || '').toLowerCase().includes(query) ||
    (job.experience_level || '').toLowerCase().includes(query)
  );
});

// Modal state
const showModal = ref(false);
const jobToArchive = ref(null);

// Archive job function
const archiveJob = () => {
    if (jobToArchive.value) {
        router.delete(route('jobs.delete', { job: jobToArchive.value.id }));
        showModal.value = false;
        jobToArchive.value = null;
    }
};

const confirmArchive = (job) => {
    jobToArchive.value = job;
    showModal.value = true;
};

// Approve job function
const approveJob = (job) => {
  router.post(route('jobs.approve', { job: job.id }), {
    onSuccess: () => {
      console.log("Job approved!");
      location.reload(); // Reload the page to fetch updated data
    }
  });
};

// Disapprove job function
const disapproveJob = (job) => {
  router.post(route('jobs.disapprove', { job: job.id }), {}, {
    onSuccess: () => {
      console.log("Job disapproved!");
      location.reload(); // Reload the page to fetch updated data
    }
  });
};

// Function to get status class based on job status
const getStatusClass = (status) => {
  if (status === 1) return 'bg-green-100 text-green-800';
  if (status === 0) return 'bg-red-100 text-red-800';
  return 'bg-yellow-100 text-yellow-800';
};

// Function to get status text
const getStatusText = (status) => {
  if (status === 1) return 'Approved';
  if (status === 0) return 'Disapproved';
  return 'Pending';
};
</script>

<template>
  <AppLayout title="Manage Jobs">
    <template #header>
      <div class="flex items-center">
        <Link :href="route('company.jobs', { user: page.props.auth.user.id })" class="text-gray-500 hover:text-gray-700 mr-3">
            <i class="fas fa-chevron-left"></i>
        </Link>
        <i class="fas fa-briefcase text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Manage Posted Jobs
        </h2>
      </div>
    </template>

    <Container class="py-8">
      <!-- Job Listings Header with Container -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6">
        <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center">
          <div class="flex items-center">
            <h3 class="text-lg font-semibold text-gray-800">Job Listings</h3>
            <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ filteredJobs.length }} jobs</span>
          </div>
          <div class="flex w-full sm:w-auto space-x-3 mt-3 sm:mt-0">
            <div class="relative">
              <input 
                type="search" 
                id="search"
                v-model="searchQuery" 
                placeholder="Search by title, location, type..." 
                class="border border-gray-300 rounded-lg px-4 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500 w-64"
              />
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                <i class="fas fa-search"></i>
              </div>
            </div>
            <Link :href="route('jobs.create', { user: page.props.auth.user.id })" 
                  class="bg-blue-500 text-white text-sm font-semibold rounded-md px-4 py-2 hover:bg-blue-600 transition">
              + Add Job
            </Link>
          </div>
        </div>
      </div>

      <!-- Jobs List -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <h3 class="text-lg font-semibold p-4 border-b border-gray-200">Active Job Positions</h3>
        
        <!-- Job listings as cards -->
        <div v-if="filteredJobs && filteredJobs.length > 0" class="divide-y divide-gray-200">
          <div v-for="job in filteredJobs" :key="job.id" 
               class="hover:bg-gray-50 transition-colors duration-150">
            
            <div class="p-4 flex flex-col md:flex-row md:items-center md:justify-between">
              <div class="flex-1 min-w-0 mb-3 md:mb-0 md:mr-4">
                <div class="flex flex-col md:flex-row md:items-center">
                  <h3 class="text-lg font-semibold text-gray-800 hover:text-blue-600 truncate mr-2">
                    {{ job.job_title }}
                  </h3>
                  <div class="flex items-center text-sm text-gray-500 mt-1 md:mt-0">
                    <i class="fas fa-map-marker-alt mr-1" aria-hidden="true"></i>
                    <span>{{ job.location || 'Remote' }}</span>
                  </div>
                </div>
                
                <!-- Job type and experience level -->
                <div class="flex flex-wrap items-center mt-2 text-sm text-gray-600">
                  <div class="mr-4 mb-1">
                    <span class="inline-block bg-gray-100 rounded-full px-2 py-0.5 text-xs font-medium text-gray-800">
                      {{ job.job_type || 'Full-time' }}
                    </span>
                  </div>
                  
                  <div class="mr-4 mb-1">
                    <i class="fas fa-briefcase mr-1" aria-hidden="true"></i>
                    <span>{{ job.experience_level || 'Not specified' }}</span>
                  </div>
                </div>
              </div>
              
              <!-- Status and actions (right side) -->
              <div class="flex items-center justify-between md:justify-end space-x-4">
                <!-- Status badge -->
                <span :class="[getStatusClass(job.is_approved), 'text-xs font-medium px-2.5 py-0.5 rounded-full whitespace-nowrap']">
                  {{ getStatusText(job.is_approved) }}
                </span>
                
                <!-- Action buttons -->
                <div class="flex flex-wrap gap-2">
                  <PrimaryButton @click="approveJob(job)" v-if="page.props.roles.isPeso && job.is_approved !== 1" 
                                class="text-xs px-3 py-1 flex items-center">
                    <i class="fas fa-check mr-1"></i> Approve
                  </PrimaryButton>
                  
                  <DangerButton @click="disapproveJob(job)" v-if="page.props.roles.isPeso && job.is_approved !== 0" 
                               class="text-xs px-3 py-1 flex items-center">
                    <i class="fas fa-times mr-1"></i> Disapprove
                  </DangerButton>
                  
                  <Link :href="route('jobs.view', { job: job.id })" 
                        class="bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium rounded-md px-3 py-1 transition flex items-center">
                    <i class="fas fa-eye mr-1"></i> View
                  </Link>
                  
                  <Link :href="route('jobs.edit', { job: job.id })" 
                        class="bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium rounded-md px-3 py-1 transition flex items-center">
                    <i class="fas fa-edit mr-1"></i> Edit
                  </Link>
                  
                  <button @click="confirmArchive(job)" 
                          class="bg-red-500 hover:bg-red-600 text-white text-xs font-medium rounded-md px-3 py-1 transition flex items-center">
                    <i class="fas fa-archive mr-1"></i> Archive
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
          
        <!-- Empty state when no jobs are available -->
        <div v-else class="p-10 text-center">
          <div class="flex flex-col items-center">
            <i class="fas fa-briefcase text-gray-300 text-4xl mb-3" aria-hidden="true"></i>
            <p class="text-lg font-medium text-gray-800">No job positions found</p>
            <p class="text-sm text-gray-500 mt-1">Create your first job posting or try a different search</p>
          </div>
        </div>
      </div>

      <!-- Confirmation Modal -->
      <ConfirmationModal @close="showModal = false" :show="showModal">
        <template #title>
          <div class="flex items-center">
            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
            <span>Confirm Archive</span>
          </div>
        </template>

        <template #content>
          <p>Are you sure you want to archive this job?</p>
          <p v-if="jobToArchive" class="font-semibold mt-2">{{ jobToArchive.job_title }}</p>
        </template>

        <template #footer>
          <DangerButton @click="archiveJob" class="mr-2">
            <i class="fas fa-archive mr-1"></i> Archive Job
          </DangerButton>
          <SecondaryButton @click="showModal = false">
            <i class="fas fa-times mr-1"></i> Cancel
          </SecondaryButton>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>