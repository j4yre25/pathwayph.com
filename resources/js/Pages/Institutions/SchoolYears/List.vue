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
const searchQuery = ref('');

const filteredSchoolYears = computed(() => {
  let filtered = props.school_years;
  
  // Filter by status (auto-applies on change)
  if (selectedStatus.value !== 'all') {
    filtered = filtered.filter(sy =>
      selectedStatus.value === 'active' ? !sy.deleted_at : sy.deleted_at
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

// Filters auto-apply by relying on reactive state and computed values

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
          <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
            <i class="fas fa-calendar-alt text-white"></i>
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-800">Manage School Years</h2>
            <p class="text-sm text-gray-600">View and manage all school years in the system.</p>
          </div>
        </div>
      </div>
    </template>

    <Container class="py-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div v-for="(stat, index) in stats" :key="index"
          :class="[
            'rounded-2xl p-6 transition-all duration-200 hover:shadow-lg hover:scale-105 text-center',
            index === 0 ? 'bg-gradient-to-br from-blue-100 to-blue-200' : index === 1 ? 'bg-gradient-to-br from-green-100 to-green-200' : 'bg-gradient-to-br from-red-100 to-red-200'
          ]">
          <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3"
               :class="index === 0 ? 'bg-blue-500' : index === 1 ? 'bg-green-500' : 'bg-red-500'">
            <i :class="[stat.icon, 'text-white text-lg']"></i>
          </div>
          <h3 class="text-sm font-medium mb-1"
              :class="index === 0 ? 'text-blue-700' : index === 1 ? 'text-green-700' : 'text-red-700'">
            {{ stat.title }}
          </h3>
          <p class="text-2xl font-bold"
             :class="index === 0 ? 'text-blue-900' : index === 1 ? 'text-green-900' : 'text-red-900'">
            {{ stat.value }}
          </p>
        </div>
      </div>

      <!-- Filter and Search Section (Graduates style, auto-apply) -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
          <i class="fas fa-filter text-blue-500 mr-2"></i>
          Filter School Years
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Search school years..."
              class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200"
            />
          </div>
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
        </div>
      </div>

      <!-- Table Section -->
      <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 mb-6">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white flex items-center">
          <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3">
            <i class="fas fa-list text-white text-sm"></i>
          </div>
          <div>
            <h2 class="text-lg font-bold text-gray-800">School Years List</h2>
            <span class="text-xs text-gray-600">{{ filteredSchoolYears.length }} shown</span>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-blue-50 to-indigo-50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">School Year</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Term</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Status</th>
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
                    <div class="h-8 w-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-3">
                      <i class="fas fa-calendar-week"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-800">{{ sy.school_year?.school_year_range }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-8 w-8 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mr-3">
                      <i class="fas fa-list-ol"></i>
                    </div>
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
