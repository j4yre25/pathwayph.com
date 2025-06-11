<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Container from '@/Components/Container.vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import '@fortawesome/fontawesome-free/css/all.css';

const page = usePage()
const props = defineProps({
  jobs: Array,
  sectors: Array,
  categories: Array,
  isLoading: {
    type: Boolean,
    default: false
  }
});

const searchQuery = ref('');
const selectedSector = ref('');
const selectedCategory = ref('');

// Action loading state
const isActionLoading = ref(false);
const activeJobId = ref(null);
const actionError = ref(null);

const filteredJobs = computed(() => {
  return props.jobs.filter(job => {
    const matchesSearch = job.job_title.toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchesSector = selectedSector.value ? job.sector === selectedSector.value : true;
    const matchesCategory = selectedCategory.value ? job.category === selectedCategory.value : true;
    return matchesSearch && matchesSector && matchesCategory;
  });
});

const goToJob = (jobId) => {
  router.visit(route('company.jobs.view', jobId));
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
  <div>
    <div v-if="actionError" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
      <strong class="font-bold">Error!</strong>
      <span class="block sm:inline ml-1">{{ actionError }}</span>
      <button @click="actionError = null" class="absolute top-0 bottom-0 right-0 px-4 py-3" aria-label="Close alert">
        <i class="fas fa-times"></i>
      </button>
    </div>
    
    <!-- Job listings as rows -->
    <div v-if="jobs && jobs.length > 0" class="divide-y divide-gray-300">
      <div v-for="job in jobs" :key="job.id" 
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
                  :aria-label="`View details for ${job.job_title}`">
                  {{ job.job_title }}
              </h3>
              
             <div class="flex flex-wrap items-center gap-x-4 text-sm text-gray-500">
              <div v-if="job.posted_by" class="flex items-center">
                <i class="fas fa-user mr-1" aria-hidden="true"></i>
                <span>{{ job.posted_by }}</span>
              </div> 
              <div v-if="job.locations.length" class="flex items-center">
                <i class="fas fa-map-marker-alt mr-1" aria-hidden="true"></i>
                <span>{{ job.locations.map(loc => loc.address).join(', ') }}</span>
              </div> 
            </div>
            </div>
            
            <!-- Job type and details -->
            <div class="flex flex-wrap items-center mt-2 text-sm text-gray-600">
              <div class="mr-4 mb-1">
                <span class="inline-block bg-gray-100 rounded-full px-2 py-0.5 text-xs font-medium text-gray-800">
                  {{ job.job_types.map(type => type.type).join(', ') }}
                </span>
              </div>
              <div v-if="job.work_environments.length" class="flex items-center">
                <i class="fas fa-briefcase mr-1" aria-hidden="true"></i>
                <span>{{ job.work_environments.map(env => env.environment_type).join(', ') }}</span>
              </div>
              <div v-if="job.salary" class="mr-4 mb-1 flex items-center text-sm text-gray-500">
                <i class="fas fa-money-bill-wave mr-1" aria-hidden="true"></i>
                <span>
                  <template v-if="job.salary.job_min_salary && job.salary.job_max_salary">
                    ₱{{ job.salary.job_min_salary.toLocaleString() }} - ₱{{ job.salary.job_max_salary.toLocaleString() }}
                  </template>
                  <template v-else-if="job.salary.job_min_salary">
                    ₱{{ job.salary.job_min_salary.toLocaleString() }}
                  </template>
                  <template v-else>
                    Negotiable
                  </template>
                  <template v-if="job.salary.salary_type">
                    /{{ job.salary.salary_type }}
                  </template>
                </span>
              </div>
              
              <div class="mb-1">
                <i class="fas fa-users mr-1" aria-hidden="true"></i>
                <span>{{ job.applicants_count || 0 }} applicants</span>
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
            <div class="flex space-x-2" @click.stop>
              <Link :href="route('jobs.view', job.id)" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                <i class="fas fa-eye mr-1" aria-hidden="true"></i>
                View Details
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
      
    <!-- Empty state when no jobs are available -->
    <div v-else class="bg-white rounded-lg border border-gray-300 p-10 text-center">
      <div class="flex flex-col items-center">
        <i class="fas fa-briefcase text-gray-300 text-4xl mb-3" aria-hidden="true"></i>
        <p class="text-lg font-medium text-gray-800">No job positions found</p>
        <p class="text-sm text-gray-500 mt-1">Create your first job posting to start receiving applications</p>
      </div>
    </div>
  </div>
</template>   