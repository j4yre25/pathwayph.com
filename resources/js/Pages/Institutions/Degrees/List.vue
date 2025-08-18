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
        <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
          <i class="fas fa-chevron-left"></i>
        </button>
        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
          <i class="fas fa-graduation-cap text-blue-500 text-xl mr-2"></i> Manage Degrees
        </h2>
      </div>
    </template>

    <link rel="stylesheet" :href="faCSS" />

    <Container class="py-6 space-y-6">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500 relative overflow-hidden">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">Total Degrees</h3>
              <p class="text-3xl font-bold text-gray-800">{{ stats.total }}</p>
            </div>
            <div class="bg-blue-100 rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-graduation-cap text-blue-600"></i>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500 relative overflow-hidden">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">Active</h3>
              <p class="text-3xl font-bold text-gray-800">{{ stats.active }}</p>
            </div>
            <div class="bg-green-100 rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-check-circle text-green-600"></i>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500 relative overflow-hidden">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">Inactive</h3>
              <p class="text-3xl font-bold text-gray-800">{{ stats.inactive }}</p>
            </div>
            <div class="bg-red-100 rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-times-circle text-red-600"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter and Search -->
      <div class="bg-white p-4 rounded-lg shadow-sm flex flex-wrap items-center justify-between gap-4">
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
            <option value="inactive">Inactive</option>
          </select>
        </div>
        
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
          </div>
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search degrees..."
            class="pl-10 border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none w-full md:w-64"
          />
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-100 text-xs uppercase tracking-wider text-gray-600">
              <tr>
                <th class="px-6 py-4"><i class="fas fa-graduation-cap mr-2"></i> Degree</th>
                <th class="px-6 py-4"><i class="fas fa-hashtag mr-2"></i> Degree Code</th>
                <th class="px-6 py-4"><i class="fas fa-info-circle mr-2"></i> Status</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="degree in filteredDegrees"
                :key="degree.id"
                class="border-t hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4 font-medium">{{ degree.degree?.type }}</td>
                <td class="px-6 py-4">{{ degree.degree_code }}</td>
                <td class="px-6 py-4">
                  <span 
                    class="px-2 py-1 rounded-full text-xs font-semibold" 
                    :class="degree.deleted_at ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                  >
                    <i :class="degree.deleted_at ? 'fas fa-times-circle mr-1' : 'fas fa-check-circle mr-1'"></i>
                    {{ degree.deleted_at ? 'Inactive' : 'Active' }}
                  </span>
                </td>
              </tr>
              <tr v-if="filteredDegrees.length === 0">
                <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                  <div class="flex flex-col items-center">
                    <i class="fas fa-search text-gray-400 text-3xl mb-2"></i>
                    <p>No degrees found matching your criteria</p>
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
