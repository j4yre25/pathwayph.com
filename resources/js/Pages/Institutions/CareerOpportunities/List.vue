<script setup>
import { ref, computed, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';

const page = usePage();
const userId = page.props.auth.user.id;

const props = defineProps({
  opportunities: Array,
  programs: Array,
  status: String,
});

const searchQuery = ref('');
const selectedStatus = ref('all');

// Pagination
const currentPage = ref(1);
const itemsPerPage = 10;

// Computed property for filtering career opportunities
const filteredOpportunities = computed(() => {
  let filtered = props.opportunities;
  
  // Filter by status
  if (selectedStatus.value !== 'all') {
    filtered = filtered.filter(opportunity => 
      selectedStatus.value === 'active' ? !opportunity.deleted_at : opportunity.deleted_at
    );
  }
  
  // Filter by search query
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase().trim();
    filtered = filtered.filter(opportunity => 
      opportunity.title.toLowerCase().includes(query) ||
      opportunity.description.toLowerCase().includes(query)
    );
  }
  
  return filtered;
});

// Computed property for paginated opportunities
const paginatedOpportunities = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredOpportunities.value.slice(start, end);
});

// Computed property for total pages
const totalPages = computed(() => {
  return Math.ceil(filteredOpportunities.value.length / itemsPerPage);
});

// Stats for cards
const stats = computed(() => {
  const total = props.opportunities.length;
  const active = props.opportunities.filter(opportunity => !opportunity.deleted_at).length;
  const archived = props.opportunities.filter(opportunity => opportunity.deleted_at).length;
  
  return { total, active, archived };
});

// Function to go back
function goBack() {
  window.history.back();
}

// Function to go to a specific page
function goToPage(page) {
  currentPage.value = page;
}

// Watch for changes to apply filters immediately
watch([selectedStatus, searchQuery], () => {
  currentPage.value = 1; // Reset to first page when filters change
});
</script>

<template>
  <AppLayout title="Career Opportunities List">
    <template #header>
      <div>
        <div class="flex items-center">
          <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
            <i class="fas fa-chevron-left"></i>
          </button>
          <i class="fas fa-briefcase text-blue-500 text-xl mr-2"></i>
          <h2 class="text-2xl font-bold text-gray-800">Career Opportunities</h2>
        </div>
        <p class="text-sm text-gray-500 mb-1">Manage career opportunities for your institution's programs.</p>
      </div>
    </template>

    <Container class="py-8 space-y-6">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div 
          v-for="(stat, index) in stats" 
          :key="index"
          class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm relative overflow-hidden flex items-center"
        >
          <div :class="`text-${stat.color}-500 text-2xl mr-4`">
            <i :class="`fas ${stat.icon}`"></i>
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500">{{ stat.title }}</h3>
            <p class="text-2xl font-semibold text-gray-800">{{ stat.value }}</p>
          </div>
          <div class="absolute -right-6 -top-6 opacity-10">
            <i :class="`fas ${stat.icon} text-${stat.color}-500 h-24 w-24`"></i>
          </div>
        </div>
      </div>
      
      <!-- Filter and Search -->
      <div class="bg-white p-4 rounded-lg shadow-sm flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
          <div class="flex items-center">
            <i class="fas fa-filter text-gray-500 mr-2"></i>
            <label for="statusFilter" class="font-medium text-gray-700">Filter by Status:</label>
          </div>
          <select
            id="statusFilter"
            v-model="selectedStatus"
            class="border border-gray-300 rounded-md px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"
          >
            <option value="all">All</option>
            <option value="active">Active</option>
            <option value="archived">Archived</option>
          </select>
        </div>
        
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
          </div>
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search opportunities..."
            class="pl-10 border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none w-full md:w-64"
          />
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full text-sm text-left text-gray-700">
          <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-600">
            <tr>
              <th class="px-6 py-4 font-medium">Program</th>
              <th class="px-6 py-4 font-medium">Career Title</th>
              <th class="px-6 py-4 font-medium">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr
              v-for="opp in paginatedOpportunities"
              :key="opp.id"
              class="hover:bg-gray-50 transition"
            >
              <td class="px-6 py-4">{{ opp.program?.name }}</td>
              <td class="px-6 py-4">{{ opp.career_opportunity?.title }}</td>
              <td class="px-6 py-4">
                <span 
                  class="px-2 py-1 rounded-full text-xs font-medium" 
                  :class="opp.deleted_at ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                >
                  {{ opp.deleted_at ? 'Inactive' : 'Active' }}
                </span>
              </td>
            </tr>

            <tr v-if="filteredOpportunities.length === 0">
              <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                No career opportunities found.
              </td>
            </tr>
          </tbody>
        </table>
        
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
    </Container>
  </AppLayout>
</template>
