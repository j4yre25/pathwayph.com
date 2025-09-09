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


// Action loading state
const isActionLoading = ref(false);
const activeJobId = ref(null);
const actionError = ref(null);



const goToJob = (jobId) => {
  router.visit(route('company.jobs.view', jobId));
};

// Function to get status class based on job status
const getStatusClass = (status) => {
  if (status === 'open') return 'bg-green-100 text-green-800';
  if (status === 'closed') return 'bg-red-100 text-red-800';
  if (status === 'expired') return 'bg-gray-200 text-gray-600';
  if (status === 'full') return 'bg-yellow-100 text-yellow-800';
  return 'bg-gray-100 text-gray-500';
};

const getStatusText = (status) => {
  if (status === 'open') return 'Open';
  if (status === 'closed') return 'Closed';
  if (status === 'expired') return 'Expired';
  if (status === 'full') return 'Full';
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
            <!-- Job Title and Job Type beside each other -->
            <div class="flex items-center space-x-2">
              <h3
                class="text-lg font-semibold text-gray-800 hover:text-blue-600 truncate mr-2"
                :class="{ 'opacity-50': isActionLoading && activeJobId === job.id }"
                :aria-disabled="isActionLoading && activeJobId === job.id"
                role="button"
                tabindex="0"
                :aria-label="`View details for ${job.job_title}`">
                {{ job.job_title }}
              </h3>

              <!-- Job Type beside title -->
                <div v-if="job.job_types.length" class="flex items-center space-x-1">
                <span
                  v-for="type in job.job_types"
                  :key="type.id"
                  class="inline-block rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="{
                  'bg-blue-100 text-blue-800': type.type === 'Full-time',
                  'bg-green-100 text-green-800': type.type === 'Part-time',
                  'bg-purple-100 text-purple-800': type.type === 'Contract',
                  'bg-pink-100 text-pink-800': type.type === 'Internship',
                  'bg-yellow-100 text-yellow-800': type.type === 'Freelance',
                  'bg-gray-200 text-gray-700': !['Full-time','Part-time','Contract','Internship','Freelance'].includes(type.type)
                  }"
                >
                  {{ type.type }}
                </span>
                </div>
            </div>

            <!-- Job Details Row: Location, Work Environment, Salary, Vacancies -->
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500 mt-1">
              <!-- Location -->
              <div v-if="job.locations.length" class="flex items-center">
                <i class="fas fa-map-marker-alt mr-1" aria-hidden="true"></i>
                <span>{{ job.locations.map(loc => loc.address).join(', ') }}</span>
              </div>

              <!-- Work Environment -->
              <div v-if="job.work_environments.length" class="flex items-center">
                <i class="fas fa-briefcase mr-1" aria-hidden="true"></i>
                <span>{{ job.work_environments.map(env => env.environment_type).join(', ') }}</span>
              </div>

              <!-- Salary -->
              <div v-if="job.salary" class="flex items-center text-sm text-gray-500">
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

              <!-- Vacancies -->
              <div v-if="typeof job.vacancies === 'number' && job.vacancies >= 0" class="flex items-center">
                <i class="fas fa-users mr-1" aria-hidden="true"></i>
                <span>
                  {{ job.vacancies }} 
                  <span v-if="job.vacancies === 1">vacancy</span>
                  <span v-else>vacancies</span>
                </span>
              </div>
            </div>
          </div>
          
          <!-- Status and actions (right side) -->
          <div class="flex items-center justify-between md:justify-end space-x-4">
            <!-- Status badge -->
            <span :class="[getStatusClass(job.status), 'text-xs font-medium px-2.5 py-0.5 rounded-full whitespace-nowrap']">
              {{ getStatusText(job.status) }}
            </span>
            
            <!-- Action buttons -->
            <div class="flex space-x-2" @click.stop>
              <Link :href="route('company.jobs.view', job.id)" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
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