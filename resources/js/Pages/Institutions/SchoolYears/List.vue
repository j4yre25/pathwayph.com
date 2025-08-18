<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { router } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref, computed } from 'vue';
import '@fortawesome/fontawesome-free/css/all.css';

const page = usePage();

const props = defineProps({
  school_years: Array,
});

const showModal = ref(false);
const selectedStatus = ref('all');
const appliedStatus = ref('all');
const searchQuery = ref('');

const filteredSchoolYears = computed(() => {
  let filtered = props.school_years;
  
  // Filter by status
  if (appliedStatus.value !== 'all') {
    filtered = filtered.filter(sy =>
      appliedStatus.value === 'active' ? !sy.deleted_at : sy.deleted_at
    );
  }
  
  // Filter by search query
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(sy => 
      (sy.school_year?.school_year_range || '').toLowerCase().includes(query) ||
      (sy.term || '').toString().includes(query)
    );
  }
  
  return filtered;
});

function applyFilter() {
  appliedStatus.value = selectedStatus.value;
}

const stats = computed(() => {
  const total = props.school_years.length;
  const active = props.school_years.filter(sy => !sy.deleted_at).length;
  const inactive = props.school_years.filter(sy => sy.deleted_at).length;
  
  return [
    {
      title: 'Total School Years',
      value: total,
      icon: 'fas fa-calendar-alt',
      color: 'text-blue-600',
      bgColor: 'bg-blue-100',
      border: 'border-l-4 border-blue-500'
    },
    {
      title: 'Active',
      value: active,
      icon: 'fas fa-check-circle',
      color: 'text-green-600',
      bgColor: 'bg-green-100',
      border: 'border-l-4 border-green-500'
    },
    {
      title: 'Inactive',
      value: inactive,
      icon: 'fas fa-times-circle',
      color: 'text-red-600',
      bgColor: 'bg-red-100',
      border: 'border-l-4 border-red-500'
    }
  ];
});

const goBack = () => {
  window.history.back();
};
</script>

<template>
  <AppLayout title="Manage School Years">
    <template #header>
      <div>
        <div class="flex items-center">
        <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
          <i class="fas fa-chevron-left"></i>
        </button>
          <i class="fas fa-calendar-alt text-blue-500 text-xl mr-2"></i>
          <h2 class="text-2xl font-bold text-gray-800">Manage School Years</h2>
        </div>
        <p class="text-sm text-gray-500 mb-1">View and manage all school years in the system.</p>
      </div>
    </template>

    <Container class="py-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div v-for="(stat, index) in stats" :key="index" 
             :class="[
              'bg-white rounded-lg shadow-sm p-6 relative overflow-hidden',
              stat.border
            ]">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">{{ stat.title }}</h3>
              <p class="text-3xl font-bold text-gray-800">{{ stat.value }}</p>
            </div>
            <div :class="[stat.bgColor, 'rounded-full p-3 flex items-center justify-center']">
              <i :class="[stat.icon, stat.color]"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter and Search Section -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <div class="flex items-center">
              <i class="fas fa-filter text-gray-400 mr-2"></i>
              <label for="statusFilter" class="font-medium text-gray-700">Status:</label>
            </div>
            <div class="relative">
              <select
                id="statusFilter"
                v-model="selectedStatus"
                class="appearance-none bg-white border border-gray-300 rounded-md pl-3 pr-8 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 focus:outline-none"
              >
                <option value="all">All</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
              </div>
            </div>
            <button 
              @click="applyFilter"
              class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
              <i class="fas fa-check mr-1"></i>
              Apply
            </button>
          </div>
          
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-search text-gray-400"></i>
            </div>
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Search school years..."
              class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
          </div>
        </div>
      </div>

      <!-- Table Section -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 mb-6">
        <div class="p-4 border-b border-gray-200 bg-gray-50 flex items-center">
          <i class="fas fa-list text-blue-500 mr-2"></i>
          <h2 class="font-semibold text-gray-800">School Years List</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School Year</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Term</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
              <tr
                v-for="sy in filteredSchoolYears"
                :key="sy.id"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <i class="fas fa-calendar-week text-gray-400 mr-2"></i>
                    <span class="text-sm font-medium text-gray-800">{{ sy.school_year?.school_year_range }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <i class="fas fa-list-ol text-gray-400 mr-2"></i>
                    <span class="text-sm text-gray-700">{{ sy.term }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                    :class="sy.deleted_at ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                  >
                    <i :class="sy.deleted_at ? 'fas fa-times-circle mr-1' : 'fas fa-check-circle mr-1'"></i>
                    {{ sy.deleted_at ? 'Inactive' : 'Active' }}
                  </span>
                </td>
              </tr>
              <tr v-if="filteredSchoolYears.length === 0">
                <td colspan="3" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <i class="fas fa-calendar-times text-gray-300 text-5xl mb-4"></i>
                    <p class="text-gray-500 text-lg font-medium">No school years found</p>
                    <p class="text-gray-400 text-sm mt-1">Try adjusting your search or filter criteria</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Confirmation Modal -->
      <ConfirmationModal :show="showModal" @close="showModal = false">
        <template #title>
          <div class="flex items-center">
            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
            <span>Confirm Deletion</span>
          </div>
        </template>
        <template #content>
          <p>Are you sure you want to delete this school year? This action cannot be undone.</p>
        </template>
        <template #footer>
          <button 
            @click="showModal = false"
            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <i class="fas fa-times mr-1"></i>
            Cancel
          </button>
          <button 
            class="ml-3 inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
          >
            <i class="fas fa-trash-alt mr-1"></i>
            Delete
          </button>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>
