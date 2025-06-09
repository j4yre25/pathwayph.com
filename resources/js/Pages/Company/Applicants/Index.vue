<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, usePage, Link } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import Container from '@/Components/Container.vue'
import ListOfJobs from './ListOfJobs.vue'


const page = usePage()

const props = defineProps({
    jobs: Array,
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
    {
        title: 'Total Active Jobs',
        value: props.stats.total_jobs.toString(),
        period: props.stats.this_month_label,
        icon: 'fas fa-briefcase',
        iconBg: 'bg-[#DBEAFE]',
        iconColor: 'text-[#4338CA]'
    }
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
      </div>

      <!-- Job Listings Header with Container -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6"> <!-- Added container styling -->
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
            <Link :href="route('company.jobs.create', { user: page.props.auth.user.id })"
              class="bg-blue-500 text-white text-sm font-semibold rounded-md px-4 py-2 hover:bg-blue-600 transition">
              + Add Job
            </Link>
          </div>
        </div>
      </div>

      <!-- Active Job Positions -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <h3 class="text-lg font-semibold p-4 border-b border-gray-200">Active Job Positions</h3>
        <ListOfJobs :jobs="paginatedJobs" />
        
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