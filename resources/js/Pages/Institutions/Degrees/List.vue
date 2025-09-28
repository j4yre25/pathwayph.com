<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

// Add FontAwesome CSS
const faCSS = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';

const page = usePage();

const props = defineProps({
  degrees: Array
});

const selectedStatus = ref('all');
const searchQuery = ref('');

// Computed property for filtering degrees by status and search query
const filteredDegrees = computed(() => {
  let filtered = props.degrees;
  
  // Filter by status
  if (selectedStatus.value !== 'all') {
    filtered = filtered.filter(degree =>
      selectedStatus.value === 'active' ? !degree.deleted_at : degree.deleted_at
    );
  }
  
  // Filter by search query
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase().trim();
    filtered = filtered.filter(degree => 
      (degree.degree?.type && degree.degree.type.toLowerCase().includes(query)) ||
      (degree.degree_code && degree.degree_code.toLowerCase().includes(query))
    );
  }
  
  return filtered;
});

// Stats for cards
const stats = computed(() => {
  const total = props.degrees.length;
  const active = props.degrees.filter(degree => !degree.deleted_at).length;
  const inactive = props.degrees.filter(degree => degree.deleted_at).length;
  
  return { total, active, inactive };
});

// Function to go back to dashboard
const goBack = () => {
  window.history.back();
};

// Watch for changes to apply filters immediately
watch(selectedStatus, () => {
  // No need for explicit apply function
});

watch(searchQuery, () => {
  // No need for explicit apply function
});
</script>

<template>
  <AppLayout title="Manage Degrees">
    <template #header>
      <div class="flex items-center">
        <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
          <i class="fas fa-chevron-left"></i>
        </button>
        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
          <i class="fas fa-graduation-cap text-white"></i>
        </div>
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Degrees</h2>
          <p class="text-sm text-gray-600">Browse and filter degrees by status</p>
        </div>
      </div>
    </template>

    <link rel="stylesheet" :href="faCSS" />

    <Container class="py-8 space-y-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-graduation-cap text-white text-lg"></i>
            </div>
            <h3 class="text-blue-700 text-sm font-medium mb-2">Total Degrees</h3>
            <p class="text-2xl font-bold text-blue-900">{{ stats.total }}</p>
          </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-check-circle text-white text-lg"></i>
            </div>
            <h3 class="text-green-700 text-sm font-medium mb-2">Active</h3>
            <p class="text-2xl font-bold text-green-900">{{ stats.active }}</p>
          </div>
        </div>
        
        <div class="bg-gradient-to-br from-red-100 to-red-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-times-circle text-white text-lg"></i>
            </div>
            <h3 class="text-red-700 text-sm font-medium mb-2">Inactive</h3>
            <p class="text-2xl font-bold text-red-900">{{ stats.inactive }}</p>
          </div>
        </div>
      </div>

      <!-- Filter and Search -->
      <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center">
          <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3">
            <i class="fas fa-filter text-white text-sm"></i>
          </div>
          <div>
            <h2 class="text-lg font-bold text-gray-800">Filter Degrees</h2>
            <span class="text-xs text-gray-600">Search and filter by status</span>
          </div>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              id="statusFilter"
              v-model="selectedStatus"
              class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200"
            >
              <option value="all">All</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
              </div>
              <input
                type="text"
                v-model="searchQuery"
                placeholder="Search degrees..."
                class="w-full pl-10 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center">
          <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3">
            <i class="fas fa-list text-white text-sm"></i>
          </div>
          <div>
            <h2 class="text-lg font-bold text-gray-800">Degrees List</h2>
            <span class="text-xs text-gray-600">{{ filteredDegrees.length }} shown</span>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-blue-50 to-indigo-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Degree</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Degree Code</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Status</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
              <tr
                v-for="degree in filteredDegrees"
                :key="degree.id"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-8 w-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-3">
                      <i class="fas fa-graduation-cap"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-800">{{ degree.degree?.type }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-8 w-8 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mr-3">
                      <i class="fas fa-hashtag"></i>
                    </div>
                    <span class="text-sm text-gray-700">{{ degree.degree_code }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                    :class="degree.deleted_at ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                  >
                    <i :class="degree.deleted_at ? 'fas fa-times-circle mr-1' : 'fas fa-check-circle mr-1'"></i>
                    {{ degree.deleted_at ? 'Inactive' : 'Active' }}
                  </span>
                </td>
              </tr>
              <tr v-if="filteredDegrees.length === 0">
                <td colspan="3" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <i class="fas fa-search text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-700">No degrees found</h3>
                    <p class="text-gray-500 mt-1">Adjust filters to see results here</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </Container>
  </AppLayout>
</template>
