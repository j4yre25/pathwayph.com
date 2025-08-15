<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Container from '@/Components/Container.vue'
import ListOfApplicants from './ListOfApplicants.vue'
import StatCard from '@/Components/StatsCard.vue'
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  job: {
    type: Object,
    required: true,
  },
  applicants: {
    type: Array,
    default: () => [],
  },
  stats: {
    type: Object,
    default: () => ({
      total: 0,
      hired: 0,
      rejected: 0,
      declined: 0,
      interviews: 0,
      pending: 0,
    }),
  },
})

const statusFilter = ref(''); // '' means show all, 'hired' means show only hired

function filterByStatus(status) {
  statusFilter.value = statusFilter.value === status ? '' : status; // toggle
  currentPage.value = 1; // reset to first page when filter changes
}

const filters = ref({
  keywords: '',
  stage: '',
  date_from: '',
  date_to: '',
});

const searchQuery = ref('')
function applyFilters() {
  console.log('Applying filters:', filters.value); // Log the filter values
  router.get(window.location.pathname, {
    ...filters.value,
  }, { 
    preserveState: true,
    onSuccess(page) {
      console.log('Filter result applicants:', page.props.applicants); // Log the returned applicants
    }
  });
}
watch(filters, () => {
  applyFilters();
}, { deep: true });


function resetFilters() {
  filters.value = {
    keywords: '',
    stage: '',
    date_from: '',
    date_to: '',
    status: '',
  };
  statusFilter.value = '';
  currentPage.value = 1;
  applyFilters();
}
const filteredApplications = computed(() => {
  let apps = props.applicants;
  if (statusFilter.value) {
    apps = apps.filter(application => application.status === statusFilter.value);
  }
  if (!searchQuery.value) return apps;
  return apps.filter(application =>
    application.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const applicantsListLabel = computed(() => {
  if (statusFilter.value === 'hired') return 'Hired Applicants';
  if (statusFilter.value === 'pending') return 'Pending Applicants';
  if (statusFilter.value === 'interviews') return 'Interview Applicants';
  if (statusFilter.value === 'rejected') return 'Rejected Applicants';
  if (statusFilter.value === 'declined') return 'Declined Applicants';
  return 'Applicants List';
});

// Pagination
const currentPage = ref(1);
const itemsPerPage = 10;

// Paginated applicants
const paginatedApplicants = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  return filteredApplications.value.slice(startIndex, endIndex);
});

// Total pages
const totalPages = computed(() => {
  return Math.ceil(filteredApplications.value.length / itemsPerPage);
});

// Page navigation
const goToPage = (page) => {
  currentPage.value = page;
};

const goBack = () => {
    window.history.back();
};
</script>

<template>
  <AppLayout title="Manage Applicants">
    <template #header>
      <div class="flex items-center">
        <button @click="goBack" class="flex items-center text-blue-600 hover:text-blue-800 transition-colors mr-4">
              <i class="fas fa-chevron-left mr-2"></i>
          </button>
        <i class="fas fa-users-cog text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ job.job_title }} - Applicants
        </h2>
      </div>
    </template>

    <Container class="py-8">
      <!-- Stats cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white rounded-lg p-4 border border-gray-200 relative overflow-hidden">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm text-gray-500 mb-1">Total Applicants</p>
              <p class="text-2xl font-bold">{{ filteredApplications.length }}</p>
              <p class="text-xs text-gray-400" v-if="filteredApplications.length !== stats.total">
                (Filtered from {{ stats.total }})
              </p>
            </div>
            <div class="bg-[#DBEAFE] rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-users text-[#4338CA]"></i>
            </div>
          </div>
        </div>
        
       <div class="bg-white rounded-lg p-4 border border-gray-200 relative overflow-hidden cursor-pointer transition ring-2"
          :class="{
            'ring-2 ring-yellow-400': statusFilter === 'pending',
            'hover:ring-2 hover:ring-yellow-200': statusFilter !== 'pending'
          }"
          @click="filterByStatus('pending')"
        >
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm text-gray-500 mb-1">Pending</p>
              <p class="text-2xl font-bold">
                {{ statusFilter === 'pending' ? filteredApplications.length : stats.pending }}
              </p>
            </div>
            <div class="bg-[#F3F4F6] rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-clock text-[#6B7280]"></i>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg p-4 border border-gray-200 relative overflow-hidden">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm text-gray-500 mb-1">Interviews</p>
              <p class="text-2xl font-bold">{{ stats.interviews }}</p>
            </div>
            <div class="bg-[#FEF3C7] rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-calendar-alt text-[#B45309]"></i>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg p-4 border border-gray-200 relative overflow-hidden cursor-pointer transition ring-2"
          :class="{
            'ring-2 ring-green-400': statusFilter === 'hired',
            'hover:ring-2 hover:ring-green-200': statusFilter !== 'hired'
          }"
          @click="filterByStatus('hired')"
        >
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm text-gray-500 mb-1">Hired</p>
              <p class="text-2xl font-bold">{{ stats.hired }}</p>
            </div>
            <div class="bg-[#D1FAE5] rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-check-circle text-[#059669]"></i>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg p-4 border border-gray-200 relative overflow-hidden cursor-pointer transition ring-2"
            :class="{
              'ring-2 ring-red-400': statusFilter === 'rejected',
              'hover:ring-2 hover:ring-red-200': statusFilter !== 'rejected'
            }"
            @click="filterByStatus('rejected')"
          >
            <div class="flex justify-between items-start">
              <div>
                <p class="text-sm text-gray-500 mb-1">Rejected</p>
                <p class="text-2xl font-bold">
                  {{ statusFilter === 'rejected' ? filteredApplications.length : stats.rejected }}
                </p>
              </div>
              <div class="bg-[#FECACA] rounded-full p-3 flex items-center justify-center">
                <i class="fas fa-times-circle text-[#DC2626]"></i>
              </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg p-4 border border-gray-200 relative overflow-hidden cursor-pointer transition ring-2"
            :class="{
              'ring-2 ring-indigo-400': statusFilter === 'declined',
              'hover:ring-2 hover:ring-indigo-200': statusFilter !== 'declined'
            }"
            @click="filterByStatus('declined')"
          >
            <div class="flex justify-between items-start">
              <div>
                <p class="text-sm text-gray-500 mb-1">Declined</p>
                <p class="text-2xl font-bold">
                  {{ statusFilter === 'declined' ? filteredApplications.length : stats.declined }}
                </p>
              </div>
              <div class="bg-[#E0E7FF] rounded-full p-3 flex items-center justify-center">
                <i class="fas fa-user-slash text-[#4F46E5]"></i>
              </div>
            </div>
        </div>
      </div>

      <!-- Applicants Header with Container -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6">
        <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center">
          <div class="flex items-center">
            <h3 class="text-lg font-semibold text-gray-800">{{ applicantsListLabel }}</h3>
            <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">
              {{ filteredApplications.length }} {{ statusFilter ? applicantsListLabel.toLowerCase() : 'applicants' }}
            </span>
          </div>
          <div class="flex flex-wrap gap-2 justify-end">
            <input v-model="filters.keywords" placeholder="Search by name, skill, degree..." class="w-60 border px-2 py-1 rounded" />
            <select v-model="filters.status" class="w-40 border px-2 py-1 rounded">
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="hired">Hired</option>
              <option value="rejected">Rejected</option>
              <option value="declined">Declined</option>
              <option value="screening">Screening</option>
              <option value="interview">Interviewing</option>
            </select>
           
            <!-- Stage filter -->
            <select v-model="filters.stage" class="w-40 border px-2 py-1 rounded">
              <option value="">All Stages</option>
              <option value="screening">Screening</option>
              <option value="interview">Interviewing</option>
              <option value="onboarding">Onboarding</option>
            </select>

            <!-- Date range filters -->
            <!-- <input v-model="filters.date_from" type="date" class="border px-2 py-1 rounded" placeholder="From date" />
            <input v-model="filters.date_to" type="date" class="border px-2 py-1 rounded" placeholder="To date" /> -->

            <PrimaryButton class="bg-gray-200 text-gray-700" @click="resetFilters">Reset</PrimaryButton>
          </div>
        </div>
        <!-- Applicants List -->
        <ListOfApplicants :applicants="paginatedApplicants" />
        
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
