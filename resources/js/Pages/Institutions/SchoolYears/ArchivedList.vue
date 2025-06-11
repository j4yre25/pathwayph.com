<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref, computed } from 'vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  all_school_years: Array,
});

const showModal = ref(false);
const schoolYearToRestore = ref(null);
const searchQuery = ref('');

const filteredSchoolYears = computed(() => {
  if (!searchQuery.value.trim()) return props.all_school_years;
  
  const query = searchQuery.value.toLowerCase();
  return props.all_school_years.filter(sy => 
    (sy.school_year?.school_year_range || '').toLowerCase().includes(query) ||
    (sy.term || '').toString().includes(query)
  );
});

const restoreSchoolYear = () => {
  router.post(
    route('school-years.restore', {
      id: schoolYearToRestore.value.id,
    }),
    {},
    {
      onSuccess: () => {
        showModal.value = false;
      },
    }
  );
};

const confirmRestore = (school_years) => {
  schoolYearToRestore.value = school_years;
  showModal.value = true;
};

const goBack = () => {
  window.history.back();
};

const stats = computed(() => {
  return [
    {
      title: 'Total Archived',
      value: props.all_school_years.length,
      icon: 'fas fa-archive',
      color: 'text-orange-600',
      bgColor: 'bg-orange-100'
    }
  ];
});
</script>

<template>
  <AppLayout title="Archived School Years">
    <Container class="py-8">
      <!-- Back Button and Header -->
      <div class="flex items-center mb-4">
        <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
          <i class="fas fa-chevron-left"></i>
        </button>
        <div class="flex items-center">
          <i class="fas fa-archive text-orange-500 text-xl mr-2"></i>
          <h1 class="text-2xl font-bold text-gray-800">Archived School Years</h1>
        </div>
      </div>
      <p class="text-sm text-gray-500 mb-6 ml-9">View and restore archived school years.</p>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div v-for="(stat, index) in stats" :key="index" 
             class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm relative overflow-hidden">
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm text-gray-500 mb-1">{{ stat.title }}</p>
              <p class="text-2xl font-bold">{{ stat.value }}</p>
            </div>
            <div :class="[stat.bgColor, 'rounded-full p-3 flex items-center justify-center']">
              <i :class="[stat.icon, stat.color]"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Search Section -->
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
          </div>
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search archived school years..."
            class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
        </div>
      </div>

      <!-- Table Section -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200 mb-6">
        <div class="p-4 border-b border-gray-200 bg-gray-50 flex items-center">
          <i class="fas fa-list text-orange-500 mr-2"></i>
          <h2 class="font-semibold text-gray-800">Archived School Years List</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School Year</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Term</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
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
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <i class="fas fa-archive mr-1"></i>
                    Archived
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <button 
                    @click="confirmRestore(sy)"
                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                  >
                    <i class="fas fa-undo-alt mr-1"></i>
                    Restore
                  </button>
                </td>
              </tr>
              <tr v-if="filteredSchoolYears.length === 0">
                <td colspan="4" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <i class="fas fa-archive text-gray-300 text-5xl mb-4"></i>
                    <p class="text-gray-500 text-lg font-medium">No archived school years found</p>
                    <p class="text-gray-400 text-sm mt-1">Archived school years will appear here</p>
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
            <i class="fas fa-undo-alt text-blue-500 mr-2"></i>
            <span>Confirm Restore</span>
          </div>
        </template>

        <template #content>
          <p>Are you sure you want to restore this school year? This will make the school year active again.</p>
        </template>

        <template #footer>
          <button 
            @click="showModal = false"
            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-2"
          >
            <i class="fas fa-times mr-1"></i>
            Cancel
          </button>
          <button 
            @click="restoreSchoolYear"
            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <i class="fas fa-undo-alt mr-1"></i>
            Restore
          </button>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>
