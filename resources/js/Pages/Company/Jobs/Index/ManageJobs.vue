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

// Modal and action states
const showModal = ref(false);
const jobToArchive = ref(null);
const isActionLoading = ref(false);
const activeJobId = ref(null);
const actionError = ref(null);


// Filters
const searchQuery = ref('');
const selectedJobType = ref('');
const selectedWorkEnvironment = ref('');
const selectedStatus = ref('');
const selectedExperienceLevel = ref('');
const selectedOpenDate = ref('');
const customFromDate = ref('');
const customToDate = ref('');


const workEnvironmentOptions = [
    { value: '', label: 'All Work Environments' },
    { value: '1', label: 'On-site' },
    { value: '2', label: 'Remote' },
    { value: '3', label: 'Hybrid' }
];

const statusOptions = [
    { value: '', label: 'All Statuses' },
    { value: 'open', label: 'Open' },
    { value: 'closed', label: 'Closed' },
    { value: 'expired', label: 'Expired' },
    { value: 'full', label: 'Full' }
];

const experienceLevelOptions = [
    { value: '', label: 'All Experience Levels' },
    { value: 'Entry-level', label: 'Entry-level' },
    { value: 'Intermediate', label: 'Intermediate' },
    { value: 'Mid-level', label: 'Mid-level' },
    { value: 'Senior/Executive', label: 'Senior/Executive-level' }
];

const filteredJobs = computed(() => {
    return props.jobs.filter(job => {
        const matchesSearch = job.job_title.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesJobType = selectedJobType.value
            ? job.job_type == selectedJobType.value
            : true;
        const matchesWorkEnv = selectedWorkEnvironment.value
            ? String(job.work_environment) === selectedWorkEnvironment.value
            : true;
        const matchesStatus = selectedStatus.value
            ? job.status === selectedStatus.value
            : true;
        const matchesExperience = selectedExperienceLevel.value
            ? job.job_experience_level === selectedExperienceLevel.value
            : true;

        // --- NEW Posted Date Filter ---
        const createdDate = new Date(job.created_at); // assumes job.created_at exists
        const today = new Date();
        let matchesOpenDate = true;

        if (selectedOpenDate.value === 'today') {
            matchesOpenDate = createdDate.toDateString() === today.toDateString();
        } else if (selectedOpenDate.value === 'week') {
            const weekAgo = new Date();
            weekAgo.setDate(today.getDate() - 7);
            matchesOpenDate = createdDate >= weekAgo;
        } else if (selectedOpenDate.value === 'month') {
            const monthAgo = new Date();
            monthAgo.setMonth(today.getMonth() - 1);
            matchesOpenDate = createdDate >= monthAgo;
        } else if (selectedOpenDate.value === '3months') {
            const threeMonthsAgo = new Date();
            threeMonthsAgo.setMonth(today.getMonth() - 3);
            matchesOpenDate = createdDate >= threeMonthsAgo;
        } else if (selectedOpenDate.value === 'custom') {
            if (customFromDate.value && customToDate.value) {
                const from = new Date(customFromDate.value);
                const to = new Date(customToDate.value);
                // normalize "to" to include whole day
                to.setHours(23, 59, 59, 999);
                matchesOpenDate = createdDate >= from && createdDate <= to;
            } else {
                matchesOpenDate = true; // if no range yet, don't filter
            }
        }

        return (
            matchesSearch &&
            matchesJobType &&
            matchesWorkEnv &&
            matchesStatus &&
            matchesExperience &&
            matchesOpenDate
        );
    });
});

function resetFilters() {
    searchQuery.value = '';
    selectedJobType.value = '';
    selectedWorkEnvironment.value = '';
    selectedStatus.value = '';
    selectedExperienceLevel.value = '';
    selectedOpenDate.value = '';
    customFromDate.value = '';
    customToDate.value = '';
}

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

const goTo = (url) => {
    router.get(url); // Use Inertia's router to navigate to the next/previous page
};

const goBack = () => {
    window.history.back();
};

</script>

<template>
  <AppLayout title="Manage Jobs">
    <template #header>
      <div class="flex items-center">
        <button 
            @click="goBack"
            class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
            <i class="fas fa-chevron-left"></i>
        </button>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
          <i class="fas fa-briefcase text-blue-500 mr-2"></i> Manage Jobs
        </h2>
        
      </div>
    </template>

    <Container class="py-6 ">
      <!-- Search and filters section -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex items-center">
          <i class="fas fa-filter text-blue-500 mr-2"></i>
        <div>
            <h3 class="font-medium text-gray-700">Search & Filter</h3>
            <p class="text-sm text-gray-500">Manage jobs by criteria below</p>
        </div>
        <div class="ml-auto">
        <Button
            class="px-6 py-3 bg-white text-gray-700 rounded-xl text-sm font-medium flex items-center hover:bg-gray-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 shadow-sm border border-gray-300"
            @click="resetFilters">
            <i class="fas fa-undo mr-2"></i> Reset Filters
        </Button>
        </div>
        </div>
        <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Search Input -->
            <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input 
                id="search"
                v-model="searchQuery" 
                type="text" 
                placeholder="Search jobs..." 
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm"
            />
            </div>
            </div>

            <!-- Job Type Dropdown -->
            <div>
            <label for="jobType" class="block text-sm font-medium text-gray-700 mb-1">Employment Type</label>
            <div class="relative">
            <select 
                id="jobType"
                v-model="selectedJobType" 
                class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10"
            >
                <option value="">All Employment Types</option>
                <option value="1">Full-time</option>
                <option value="2">Part-time</option>
                <option value="3">Contract</option>
                <option value="4">Freelance</option>
                <option value="5">Internship</option>
            </select>
            </div>
            </div>

            <!-- Work Environment Dropdown -->
            <div>
            <label for="workEnv" class="block text-sm font-medium text-gray-700 mb-1">Work Environment</label>
            <div class="relative">
            <select 
                id="workEnv"
                v-model="selectedWorkEnvironment" 
                class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10"
            >
                <option v-for="option in workEnvironmentOptions" :key="option.value" :value="option.value">
                {{ option.label }}
                </option>
            </select>
            </div>
            </div>

            <!-- Status Dropdown -->
            <div>
            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <div class="relative">
            <select 
                id="status"
                v-model="selectedStatus" 
                class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10"
            >
                <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                {{ option.label }}
                </option>
            </select>
            </div>
            </div>

            <!-- Experience Level Dropdown -->
            <div>
            <label for="expLevel" class="block text-sm font-medium text-gray-700 mb-1">Experience Level</label>
            <div class="relative">
            <select 
                id="expLevel"
                v-model="selectedExperienceLevel" 
                class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10"
            >
                <option v-for="option in experienceLevelOptions" :key="option.value" :value="option.value">
                {{ option.label }}
                </option>
            </select>
            </div>
            </div>
            <div>
            <label for="openDate" class="block text-sm font-medium text-gray-700 mb-1">
            Posted Date
            </label>
            <div class="relative">
            <select 
            id="openDate"
            v-model="selectedOpenDate" 
            class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none 
                focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10"
            >
                <option value="">All Dates</option>
                <option value="today">Opened Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="3months">In Last 3 Months</option>
                <option value="custom">Custom Range…</option>
            </select>
            </div>
            <!-- Custom Range Fields -->
            <div v-if="selectedOpenDate === 'custom'" class="flex gap-2 mt-2">
            <div>
                <label class="block text-xs font-medium text-gray-600">From</label>
                <input 
                type="date" 
                v-model="customFromDate" 
                class="border border-gray-300 rounded px-2 py-1 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600">To</label>
                <input 
                type="date" 
                v-model="customToDate" 
                :min="customFromDate || undefined"
                class="border border-gray-300 rounded px-2 py-1 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>
            </div>
            </div>
        </div>
        </div>
        </div>

      <!-- Spacing between filter and job list -->
      <div class="h-8"></div>

      <!-- Jobs grid layout -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden 8xl" v-if="filteredJobs.length > 0">
        <div class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
          <div class="flex items-center">
        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
          <i class="fas fa-table text-white"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-800">Job List</h3>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 text-sm font-semibold text-gray-700">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Title</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Experience</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Work Environment</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Location</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Vacancies</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Posted</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
              <tr v-for="job in filteredJobs" :key="job.id" class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <span class="text-sm font-semibold text-gray-900 truncate" :title="job.job_title">{{ job.job_title }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-700">{{ job.job_types.map(type => type.type).join(', ') }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-700">{{ job.job_experience_level }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-700">
                    {{
                      job.work_environment == 1 ? 'On-site'
                      : job.work_environment == 2 ? 'Remote'
                      : job.work_environment == 3 ? 'Hybrid'
                      : '—'
                    }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-700">
                    <template v-if="String(job.work_environment) === '2'">
                      Remote
                    </template>
                    <template v-else>
                      {{ job.locations && job.locations.length > 0 ? job.locations.map(loc => loc.address).join(', ') : '—' }}
                    </template>
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-700">
                    {{ job.no_of_vacancies !== undefined ? job.no_of_vacancies : '—' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-600">{{ new Date(job.created_at).toLocaleDateString() }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(job.status)" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border shadow-sm">
                    <i :class="job.status === 'open' ? 'fas fa-check-circle mr-1.5' : job.status === 'closed' ? 'fas fa-times-circle mr-1.5' : 'fas fa-clock mr-1.5'"></i>
                    {{ getStatusText(job.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex justify-center gap-2">
                    <Link
                      :href="route('company.jobs.view', { job: job.id })"
                      class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-full font-medium text-xs text-gray-700 hover:bg-blue-600 hover:text-white hover:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-150"
                    >
                      <i class="fas fa-eye mr-1"></i>
                    </Link>
                    <Link
                      :href="route('company.jobs.edit', { job: job.id })"
                      class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-full font-medium text-xs text-gray-700 hover:bg-green-600 hover:text-white hover:border-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors duration-150"
                    >
                      <i class="fas fa-edit mr-1"></i>
                    </Link>
                    <button
                      @click="confirmArchive(job)"
                      :disabled="isActionLoading && activeJobId === job.id"
                      class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-full font-medium text-xs text-gray-700 hover:bg-red-600 hover:text-white hover:border-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <i class="fas fa-archive mr-1"></i>
                      <span v-if="isActionLoading && activeJobId === job.id">Processing...</span>
                      <span v-else></span>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="filteredJobs.length === 0">
                <td colspan="9" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                      <i class="fas fa-search text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500 text-lg font-medium">No jobs found</p>
                    <p class="text-gray-400 text-sm mt-1">Try adjusting your search criteria</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
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