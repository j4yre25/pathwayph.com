<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed } from 'vue'
import { router, usePage, Link } from '@inertiajs/vue3'
import Container from '@/Components/Container.vue'
import ListOfApplicants from './ListOfApplicants.vue'
import '@fortawesome/fontawesome-free/css/all.css';

const page = usePage()

const props = defineProps({
    jobs: Array,
    applicants: Array,
    statuses: Array,
    employmentTypes: Array,
    filters: {
      type: Object,
      default: () => ({}),
    },
    stats: {
        type: Object,
        default: () => ({
            this_month: 0,
            this_month_label: '',
            hired: 0,
            rejected: 0,
            interviews: 0,
            total_jobs: 0,
            total_applicants: 0,
        }),
    },
})

// Use jobs directly from props
const allJobs = ref(props.jobs || []);

// Stats data for UI display
const statsDisplay = computed(() => [
    {
        title: 'Total Applicants',
        value: props.stats.total_applicants.toString(),
        period: props.stats.this_month_label,
        icon: 'fas fa-user-friends',
        iconBg: 'bg-[#E9D5FF]',
        iconColor: 'text-[#7C3AED]'
    },
    {
        title: 'Total Hired',
        value: props.stats.hired.toString(),
        period: props.stats.this_month_label,
        icon: 'fas fa-check-circle',
        iconBg: 'bg-[#D1FAE5]',
        iconColor: 'text-[#059669]'
    },
    {
        title: 'Total Rejected',
        value: props.stats.rejected.toString(),
        period: props.stats.this_month_label,
        icon: 'fas fa-times-circle',
        iconBg: 'bg-[#FECACA]',
        iconColor: 'text-[#DC2626]'
    },
    {
        title: 'Interviews Scheduled',
        value: props.stats.interviews.toString(),
        period: props.stats.this_month_label,
        icon: 'fas fa-calendar-alt',
        iconBg: 'bg-[#FEF3C7]',
        iconColor: 'text-[#B45309]'
    },

]);

// Reactive search query
const searchQuery = ref('')

// Pagination
const currentPage = ref(1);
const itemsPerPage = 10;

// Computed filtered jobs based on search query (case insensitive)
const filteredJobs = computed(() => {
  if (!searchQuery.value) return allJobs.value;
  const query = searchQuery.value.toLowerCase();
  return allJobs.value.filter(job =>
    (job.job_title || job.title || '').toLowerCase().includes(query) ||
    (job.location || '').toLowerCase().includes(query) ||
    (job.job_type || job.type || '').toLowerCase().includes(query) ||
    (job.description && job.description.toLowerCase().includes(query))
  );
});

// Paginated jobs
const paginatedJobs = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  return filteredJobs.value.slice(startIndex, endIndex);
});

// Total pages
const totalPages = computed(() => {
  return Math.ceil(filteredJobs.value.length / itemsPerPage);
});

// Page navigation
const goToPage = (page) => {
  currentPage.value = page;
};

// Function to navigate to job details
const goToJob = (jobId) => {
  router.get(route('company.job.applicants.show', { job: jobId }));
};

const filters = ref({ ...props.filters, keywords: '' })

const filteredApplicants = computed(() => {
  let apps = props.applicants;
  if (filters.value.job_id) {
    apps = apps.filter(a => a.job_id == filters.value.job_id);
  }
  if (filters.value.status) {
    apps = apps.filter(a => a.status === filters.value.status);
  }
  if (filters.value.employment_type) {
    apps = apps.filter(a => a.employment_type?.includes(filters.value.employment_type));
  }
  if (filters.value.date_from) {
    apps = apps.filter(a => new Date(a.applied_at) >= new Date(filters.value.date_from));
  }
  if (filters.value.date_to) {
    apps = apps.filter(a => new Date(a.applied_at) <= new Date(filters.value.date_to));
  }
  if (filters.value.keywords) {
    const kw = filters.value.keywords.toLowerCase();
    apps = apps.filter(a =>
      (a.name && a.name.toLowerCase().includes(kw)) ||
      (a.job_title && a.job_title.toLowerCase().includes(kw)) ||
      (a.status && a.status.toLowerCase().includes(kw))
    );
  }
  // Match % Range filter
  if (filters.value.match_range) {
    const minMatch = parseInt(filters.value.match_range, 10);
    apps = apps.filter(a => (a.match_percentage || 0) >= minMatch);
  }
  // Pipeline Stage filter
  if (filters.value.stage) {
    apps = apps.filter(a => a.stage === filters.value.stage);
  }
  return apps;
});

const averageMatchPercent = computed(() => {
  if (!props.applicants.length) return 0;
  const total = props.applicants.reduce((sum, a) => sum + (a.match_percentage || 0), 0);
  return Math.round(total / props.applicants.length);
});

function applyFilters() {
  router.get(window.location.pathname, filters.value, { preserveState: true })
}
</script>

<template>
  <AppLayout title="Manage Applicants">
    <template #header>
      <div class="flex items-center">
        <i class="fas fa-users-cog text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Manage Applicants
        </h2>
      </div>
    </template>

    <Container class="py-8">
      <!-- Stats cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div v-for="(stat, index) in statsDisplay" :key="index" 
             class="bg-white rounded-lg p-4 border border-gray-200 relative overflow-hidden">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm text-gray-500 mb-1">{{ stat.title }}</p>
              <p class="text-2xl font-bold">{{ stat.value }}</p>
              <p class="text-xs text-gray-400 mt-1">{{ stat.period }}</p>
            </div>
            <div :class="[stat.iconBg, 'rounded-full p-3 flex items-center justify-center']">
              <i :class="[stat.icon, stat.iconColor]"></i>
            </div>

          </div>
        </div>
        <div class="flex gap-6 mb-6">
          <div class="bg-white rounded-lg shadow p-4 flex-1 text-center">
            <div class="text-lg font-bold">Average Match %</div>
            <div class="text-3xl font-extrabold text-indigo-600">
              {{ averageMatchPercent }}%
            </div>
            <div class="text-xs text-gray-500">Across all applicants</div>
          </div>
        </div>
      </div>

      <!-- Job Listings Header with Container -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6"> <!-- Added container styling -->
        <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center">
          <div class="flex w-full sm:w-auto space-x-3 mt-3 sm:mt-0">
            <div class="relative">
              <div class="flex flex-wrap gap-4 mb-4">
                <!-- Keyword Search -->
                <div class="flex flex-col">
                  <label for="keywordSearch" class="text-sm font-medium mb-1">Search</label>
                  <input
                    id="keywordSearch"
                    type="text"
                    v-model="filters.keywords"
                    @input="applyFilters"
                    placeholder="Applicants, job title, or status"
                    class="border px-2 py-1 rounded w-64"
                  />
                </div>

                <!-- Job Position -->
                <div class="flex flex-col">
                  <label for="jobSelect" class="text-sm font-medium mb-1">Job Position</label>
                  <select
                    id="jobSelect"
                    v-model="filters.job_id"
                    @change="applyFilters"
                    class="border px-2 py-1 rounded w-48"
                  >
                    <option value="">All Positions</option>
                    <option
                      v-for="job in jobs || []"
                      :key="job.id"
                      :value="job.id"
                    >
                      {{ job.job_title || job.title }}
                    </option>
                  </select>
                </div>

                <!-- Status -->
                <div class="flex flex-col">
                  <label for="statusSelect" class="text-sm font-medium mb-1">Status</label>
                    <select v-model="filters.status" @change="applyFilters" class="border px-2 py-1 rounded">
                      <option value="">All Statuses</option>
                      <option value="shortlisted">Shortlisted</option>
                      <option value="under_review">Under Review</option>
                      <option value="not_recommended">Not Recommended</option>
                    </select>
                </div>

                  <!-- Match % Range -->
                <div class="flex flex-col">
                  <label for="employmentTypeSelect" class="text-sm font-medium mb-1">Match % Range</label>
                  <select v-model="filters.match_range" @change="applyFilters" class="border px-2 py-1 rounded">
                    <option value="">All Match %</option>
                    <option value="75">75%+</option>
                    <option value="50">50%+</option>
                    <option value="30">30%+</option>
                  </select>
                </div>

                <!-- Date From -->
                <div class="flex flex-col">
                  <label for="dateFrom" class="text-sm font-medium mb-1">From</label>
                  <input
                    id="dateFrom"
                    type="date"
                    v-model="filters.date_from"
                    @change="applyFilters"
                    class="border px-2 py-1 rounded w-40"
                  />
                </div>

                <!-- Date To -->
                <div class="flex flex-col">
                  <label for="dateTo" class="text-sm font-medium mb-1">To</label>
                  <input
                    id="dateTo"
                    type="date"
                    v-model="filters.date_to"
                    @change="applyFilters"
                    class="border px-2 py-1 rounded w-40"
                  />
                </div>

                <div class="flex flex-col">
                  <label for="dateTo" class="text-sm font-medium mb-1">Stages</label>
                  <select v-model="filters.stage" @change="applyFilters" class="border px-2 py-1 rounded">
                    <option value="">All Stages</option>
                    <option value="applied">Applied</option>
                    <option value="shortlisted">Shortlisted</option>
                    <option value="interview">Interview</option>
                    <option value="offer">Offer</option>
                    <option value="hired">Hired</option>
                    <option value="rejected">Rejected</option>
                  </select>
                </div>
              </div>

            </div>
            
          </div>
        </div>
      </div>

      
      <!-- Active Job Positions -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="flex items-center">
          <h3 class="text-lg font-semibold p-4 border-b border-gray-200">List of Applicants</h3>
            <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ filteredJobs.length }} jobs</span>
          </div>
        <ListOfApplicants
          :jobs="jobs"
          :applicants="filteredApplicants"
          :statuses="statuses"
          :employmentTypes="employmentTypes"
          :filters="filters"
        />
        
        <!-- Pagination Controls -->
        <div v-if="totalPages > 1" class="flex justify-center items-center p-4 border-t border-gray-200">
          <nav class="flex items-center space-x-1" aria-label="Pagination">
            <!-- Previous page button -->
            <button 
              @click="goToPage(currentPage - 1)" 
              :disabled="currentPage === 1"
              :class="{
                'text-gray-400 cursor-not-allowed': currentPage === 1,
                'text-blue-600 hover:text-blue-800': currentPage !== 1
              }"
              class="px-2 py-1 rounded-md"
              aria-label="Previous page">
              <i class="fas fa-chevron-left"></i>
            </button>
            
            <!-- Page numbers -->
            <template v-for="page in totalPages" :key="page">
              <button 
                @click="goToPage(page)" 
                :class="{
                  'bg-blue-500 text-white': currentPage === page,
                  'text-gray-700 hover:bg-gray-100': currentPage !== page
                }"
                class="px-3 py-1 rounded-md text-sm font-medium"
                :aria-current="currentPage === page ? 'page' : undefined"
                :aria-label="`Page ${page}`">
                {{ page }}
              </button>
            </template>
            
            <!-- Next page button -->
            <button 
              @click="goToPage(currentPage + 1)" 
              :disabled="currentPage === totalPages"
              :class="{
                'text-gray-400 cursor-not-allowed': currentPage === totalPages,
                'text-blue-600 hover:text-blue-800': currentPage !== totalPages
              }"
              class="px-2 py-1 rounded-md"
              aria-label="Next page">
              <i class="fas fa-chevron-right"></i>
            </button>
          </nav>
        </div>
      </div>
    </Container>
  </AppLayout>
</template>