<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import '@fortawesome/fontawesome-free/css/all.css';

const page = usePage()
const props = defineProps({
  jobs: Array,
  isLoading: {
    type: Boolean,
    default: false
  },
  role: {
    type: String,
    default: 'company',
    validator: (value) => ['company', 'admin', 'viewer'].includes(value)
  }
});

// Action loading state
const isActionLoading = ref(false);
const activeJobId = ref(null);
const actionError = ref(null);

// Computed permissions based on role
const canViewApplicants = computed(() => ['company', 'admin'].includes(props.role));

const goToJob = (jobId) => {
  router.get(route('company.job.applicants.show', { job: jobId }));
};

// Function to get status class based on job status
const getStatusClass = (status) => {
  if (status === 1 || status === 'open') return 'bg-green-100 text-green-800';
  if (status === 0 || status === 'closed') return 'bg-red-100 text-red-800';
  if (status === 'draft') return 'bg-yellow-100 text-yellow-800';
  return 'bg-gray-100 text-gray-800';
};

// Function to get status text
const getStatusText = (status) => {
  if (status === 1 || status === 'open') return 'Active';
  if (status === 0 || status === 'closed') return 'Closed';
  if (status === 'draft') return 'Draft';
  return 'Unknown';
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
    
    <!-- Job listings as rows -->
    <div v-if="props.jobs && props.jobs.length > 0" class="divide-y divide-gray-200">
      <div v-for="job in props.jobs" :key="job.id" 
           class="hover:bg-gray-50 transition-colors duration-150 cursor-pointer"
           @click="goToJob(job.id)">
        
        <div class="p-4 flex flex-col md:flex-row md:items-center md:justify-between">
          <div class="flex-1 min-w-0 mb-3 md:mb-0 md:mr-4">
            <div class="flex flex-col md:flex-row md:items-center">
              <h3 class="text-lg font-semibold text-gray-800 hover:text-blue-600 truncate mr-2"
                  :class="{ 'opacity-50': isActionLoading && activeJobId === job.id }"
                  :aria-disabled="isActionLoading && activeJobId === job.id"
                  role="button"
                  tabindex="0"
                  :aria-label="`View details for ${job.job_title || job.title}`">
                  {{ job.job_title || job.title }}
              </h3>
              <div class="flex items-center text-sm text-gray-500 mt-1 md:mt-0">
                <i class="fas fa-map-marker-alt mr-1" aria-hidden="true"></i>
                <span>{{ job.locations.map(loc => loc.address).join(', ') }}</span>
              </div>
            </div>
            
            <!-- Job type and salary -->
            <div class="flex flex-wrap items-center mt-2 text-sm text-gray-600">
              <div class="mr-4 mb-1">
                <span class="inline-block bg-gray-100 rounded-full px-2 py-0.5 text-xs font-medium text-gray-800">
                  {{ job.job_types.map(type => type.type).join(', ') }}
                </span>
              </div>
              
              <div v-if="job.salary_min || job.salary_max || job.min_salary || job.max_salary" class="mr-4 mb-1">
                <i class="fas fa-money-bill-wave mr-1" aria-hidden="true"></i>
                <span>
                  {{ job.salary_min || job.min_salary ? `₱${(job.salary_min || job.min_salary)}` : 'Negotiable' }} 
                  {{ (job.salary_min || job.min_salary) && (job.salary_max || job.max_salary) ? '-' : '' }}
                  {{ job.salary_max || job.max_salary ? `₱${(job.salary_max || job.max_salary)}` : '' }}
                  {{ job.salary_period ? `/${job.salary_period}` : '' }}
                </span>
              </div>
              
              <div class="mb-1">
                <i class="fas fa-users mr-1" aria-hidden="true"></i>
                <span>{{ job.applicants_count || job.applications_count || 0 }} applicants</span>
              </div>
            </div>
          </div>
          
          <!-- Status and actions (right side) -->
          <div class="flex items-center justify-between md:justify-end space-x-4">
            <!-- Status badge -->
            <span :class="getStatusClass(job.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                {{ getStatusText(job.status) }}
              </span>
            
            <!-- Action buttons -->
            <div class="flex space-x-2" @click.stop>
              
              <!-- View applicants button -->
              <button 
                  v-if="canViewApplicants"
                  :key="job.id" 
                  @click="goToJob(job.id, $event)" 
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center"
                  :disabled="isActionLoading && activeJobId === job.id"
                  :class="{ 'opacity-50 cursor-not-allowed': isActionLoading && activeJobId === job.id }"
                  aria-label="View applicants">
                  <i class="fas fa-eye mr-1" aria-hidden="true"></i>
                  <Link :href="route('company.job.applicants.show', { job: job.id })" class="text-blue-600 hover:underline">
                    View Applicants
                  </Link>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
      
    <!-- Empty state when no jobs are available -->
    <div v-else class="bg-white rounded-lg border border-gray-200 p-10 text-center">
      <div class="flex flex-col items-center">
        <i class="fas fa-briefcase text-gray-300 text-4xl mb-3" aria-hidden="true"></i>
        <p class="text-lg font-medium text-gray-800">No job positions found</p>
        <p class="text-sm text-gray-500 mt-1">Create your first job posting to start receiving applications</p>
      </div>
    </div>
  </div>
</template>