<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const page = usePage();
const userId = page.props.auth.user.id;

// Use a local ref array for opportunities
const opportunities = ref([...page.props.opportunities ?? []]);
const programs = page.props.programs ?? [];

// Add filters object
const filters = reactive({
  search: ''
});

const selectedProgram = ref('');
const showConfirmModal = ref(false);
const opportunityToArchive = ref(null);

// Pagination
const currentPage = ref(1);
const itemsPerPage = 30;
const totalPages = computed(() => Math.ceil(filteredOpportunities.value.length / itemsPerPage));
const paginatedOpportunities = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  return filteredOpportunities.value.slice(startIndex, endIndex);
});

const goToPage = (page) => {
  currentPage.value = page;
};

// Reset pagination when filters change
onMounted(() => {
  currentPage.value = 1;
});

// Stats for display
const totalOpportunities = computed(() => opportunities.value.length);
const uniquePrograms = computed(() => {
  const uniqueProgramIds = new Set(opportunities.value.map(o => o.program_id).filter(Boolean));
  return uniqueProgramIds.size;
});
const uniqueTitles = computed(() => {
  const titles = new Set(opportunities.value.map(o => o.career_opportunity?.title).filter(Boolean));
  return titles.size;
});

const filteredOpportunities = computed(() => {
  return opportunities.value.filter((opp) => {
    const matchesProgram = !selectedProgram.value || String(opp.program_id) === String(selectedProgram.value);
    const matchesSearch = !filters.search || (opp.career_opportunity?.title?.toLowerCase().includes(filters.search.toLowerCase()));
    return matchesProgram && matchesSearch;
  });
});

function applyFilter() {
  router.get(route('careeropportunities', { user: userId }), {
    program_id: selectedProgram.value,
    preserveState: true,
    preserveScroll: true,
  });
}

// Add function to apply filters including search
function applyFilters() {
  // This function can be used for debouncing search if needed
  // For now, we're using the computed property directly
}

function confirmArchive(opp) {
  opportunityToArchive.value = opp;
  showConfirmModal.value = true;
}

function archiveConfirmed() {
  router.delete(route('careeropportunities.delete', opportunityToArchive.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      // Remove the archived opportunity from the local array
      opportunities.value = opportunities.value.filter(
        (opp) => opp.id !== opportunityToArchive.value.id
      );
      showConfirmModal.value = false;
      opportunityToArchive.value = null;
    }
  });
}

function cancelArchive() {
  showConfirmModal.value = false;
  opportunityToArchive.value = null;
}
</script>

<template>
  <AppLayout title="Career Opportunities">
    <template #header>
      <div class="flex items-center">
        <i class="fas fa-briefcase text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Career Opportunities</h2>
      </div>
    </template>

    <Container class="py-8">

      <!-- Navigation Menu -->
      <div class="mb-8 max-w-3xl mx-auto bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <!-- Section Header -->
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-800 text-center">Data Entries</h3>
        </div>

        <!-- Buttons -->
        <div class="flex flex-wrap gap-3 p-4 justify-center">

          <Link :href="route('school-years', { user: userId })"
            class="inline-flex items-center px-4 py-2 rounded-lg shadow-sm bg-white border border-gray-200 text-gray-700 hover:bg-green-50 hover:border-green-400 hover:text-green-600 transition-all duration-200">
          <i class="fas fa-calendar-alt mr-2 text-blue-500"></i> School Years
          </Link>

          <Link :href="route('degrees', { user: userId })"
            class="inline-flex items-center px-4 py-2 rounded-lg shadow-sm bg-white border border-gray-200 text-gray-700 hover:bg-green-50 hover:border-green-400 hover:text-green-600 transition-all duration-200">
          <i class="fas fa-graduation-cap mr-2 text-green-500"></i> Degrees
          </Link>

          <Link :href="route('programs', { user: userId })"
            class="inline-flex items-center px-4 py-2 rounded-lg shadow-sm bg-white border border-gray-200 text-gray-700 hover:bg-indigo-50 hover:border-indigo-400 hover:text-indigo-600 transition-all duration-200">
          <i class="fas fa-book mr-2 text-indigo-500"></i> Programs
          </Link>

          <Link :href="route('instiskills', { user: userId })"
            class="inline-flex items-center px-4 py-2 rounded-lg shadow-sm bg-white border border-gray-200 text-gray-700 hover:bg-pink-50 hover:border-pink-400 hover:text-pink-600 transition-all duration-200">
          <i class="fas fa-cogs mr-2 text-pink-500"></i> Skills
          </Link>
        </div>
      </div>

      <!-- Stats Summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 transition-all duration-200 hover:shadow-md">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">Total Opportunities</h3>
              <p class="text-3xl font-bold text-blue-600">
                {{ totalOpportunities }}
              </p>
            </div>
            <div class="bg-blue-100 rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-briefcase text-blue-500"></i>
            </div>
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 transition-all duration-200 hover:shadow-md">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">Programs</h3>
              <p class="text-3xl font-bold text-green-600">
                {{ uniquePrograms }}
              </p>
            </div>
            <div class="bg-green-100 rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-graduation-cap text-green-500"></i>
            </div>
          </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 transition-all duration-200 hover:shadow-md">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">Titles</h3>
              <p class="text-3xl font-bold text-purple-600">
                {{ uniqueTitles }}
              </p>
            </div>
            <div class="bg-purple-100 rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-id-badge text-purple-500"></i>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Main Content -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
        <!-- Action Buttons -->
        <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-200">
          <div class="flex items-center">
            <h3 class="text-lg font-semibold text-gray-800">Career Opportunities</h3>
            <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ filteredOpportunities.length }} of {{ totalOpportunities }}</span>
          </div>
          <div class="flex w-full sm:w-auto space-x-3 mt-3 sm:mt-0">
            <Link :href="route('careeropportunities.create', { user: userId })" 
                  class="text-sm px-4 py-2 rounded-md bg-blue-500 text-white hover:bg-blue-600 transition-colors duration-200 flex items-center">
              <i class="fas fa-plus-circle mr-2"></i> Add Opportunity
            </Link>
            <Link :href="route('careeropportunities.list', { user: userId })" 
                  class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
              <i class="fas fa-list text-blue-500 mr-2"></i> All Opportunities
            </Link>
            <Link :href="route('careeropportunities.archivedlist', { user: userId })" 
                  class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
              <i class="fas fa-archive text-gray-500 mr-2"></i> Archived
            </Link>
          </div>
        </div>

        <!-- Filters -->
        <div class="p-4 bg-gray-50 border-b border-gray-200">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="program" class="block text-sm font-medium text-gray-700 mb-1">Filter by Program</label>
              <div class="relative">
                <select
                  id="program"
                  v-model="selectedProgram"
                  class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                  @change="applyFilter"
                >
                  <option value="">All Programs</option>
                  <option
                    v-for="program in programs"
                    :key="program.program_id"
                    :value="program.program_id"
                  >
                    {{ program.program?.name }}
                  </option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                  <i class="fas fa-chevron-down text-xs"></i>
                </div>
              </div>
            </div>
            <div>
              <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search by Title</label>
              <div class="relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <i class="fas fa-search text-gray-400"></i>
                </div>
                <input
                  id="search"
                  type="text"
                  v-model="filters.search"
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                  placeholder="Search career titles..."
                  @input="applyFilters"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
              <tr>
                <th class="px-6 py-4">Program</th>
                <th class="px-6 py-4">Career Title</th>
                <th class="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr
                v-for="opp in paginatedOpportunities"
                :key="opp.id"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-green-100 flex items-center justify-center text-green-500">
                      <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ opp.program?.name || 'N/A' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 mr-2">
                      <i class="fas fa-briefcase mr-1"></i> Career
                    </span>
                    <span class="text-sm font-medium">{{ opp.career_opportunity?.title }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 text-right">
                  <Link :href="route('careeropportunities.edit', opp.id)" class="text-blue-500 hover:text-blue-700 mr-3">
                    <i class="fas fa-edit"></i>
                  </Link>
                  <button @click="confirmArchive(opp)" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-archive"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="filteredOpportunities.length === 0">
                <td colspan="3" class="py-12 text-center">
                  <i class="fas fa-briefcase text-gray-300 text-5xl mb-4"></i>
                  <h3 class="text-lg font-medium text-gray-700">No career opportunities found</h3>
                  <p class="text-gray-500 mt-1">
                    {{ selectedProgram ? 'Try adjusting your filters' : 'Add career opportunities to see them listed here' }}
                  </p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination Controls -->
        <div v-if="totalPages > 1" class="px-6 py-4 bg-white border-t border-gray-200 flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing <span class="font-medium">{{ ((currentPage - 1) * itemsPerPage) + 1 }}</span> to
            <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredOpportunities.length) }}</span> of
            <span class="font-medium">{{ filteredOpportunities.length }}</span> results
          </div>
          <div class="flex space-x-2">
            <button 
              @click="goToPage(currentPage - 1)" 
              :disabled="currentPage === 1"
              class="px-3 py-1 rounded border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>
            <div class="flex space-x-1">
              <button 
                v-for="page in totalPages" 
                :key="page" 
                @click="goToPage(page)"
                :class="{
                  'bg-blue-600 text-white': page === currentPage,
                  'bg-white text-gray-700 hover:bg-gray-50': page !== currentPage
                }"
                class="px-3 py-1 rounded border border-gray-300 text-sm font-medium"
              >
                {{ page }}
              </button>
            </div>
            <button 
              @click="goToPage(currentPage + 1)" 
              :disabled="currentPage === totalPages"
              class="px-3 py-1 rounded border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </div>
        </div>
      </div>

      <!-- Confirmation Modal -->
      <ConfirmationModal :show="showConfirmModal" @close="cancelArchive">
        <template #title>
          <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mr-4">
              <i class="fas fa-exclamation-triangle text-red-500"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800">Confirm Archive</h3>
          </div>
        </template>
        <template #content>
          <p class="text-gray-600 mb-2">Are you sure you want to archive the career opportunity:</p>
          <p class="font-medium text-gray-800">"{{ opportunityToArchive?.career_opportunity?.title }}"?</p>
          <p class="text-sm text-gray-500 mt-2">This action can be reversed later.</p>
        </template>
        <template #footer>
          <div class="flex justify-end space-x-3">
            <button @click="cancelArchive" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm transition-colors duration-200">
              Cancel
            </button>
            <button @click="archiveConfirmed" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 text-sm transition-colors duration-200">
              Archive
            </button>
          </div>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>
