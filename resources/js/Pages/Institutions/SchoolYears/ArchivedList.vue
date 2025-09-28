<script setup>
// Layout & Components
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

// Inertia router for navigation and actions
import { router } from '@inertiajs/vue3';

// Vue utilities
import { ref, computed } from 'vue';

// Icons
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  all_school_years: Array,
});

// Modal and local state
const showModal = ref(false);
const schoolYearToRestore = ref(null);

// Local search query for client-side filtering (UI consistency)
const searchQuery = ref('');

// Filtered list based on search query (kept client-side for responsiveness)
const filteredSchoolYears = computed(() => {
  if (!searchQuery.value.trim()) return props.all_school_years;

  const query = searchQuery.value.toLowerCase();
  return props.all_school_years.filter((sy) =>
    (sy.school_year?.school_year_range || '').toLowerCase().includes(query) ||
    (sy.term || '').toString().toLowerCase().includes(query)
  );
});

// Restore action with confirmation modal
const restoreSchoolYear = () => {
  router.post(
    route('school-years.restore', { id: schoolYearToRestore.value.id }),
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

const goBack = () => window.history.back();

// Stats card (styled to match Graduates/ArchivedList.vue)
const stats = computed(() => [
  {
    title: 'Total Archived School Years',
    value: props.all_school_years.length,
    icon: 'fas fa-archive',
    color: 'text-white',
    bgGradient: 'bg-gradient-to-br from-orange-100 to-orange-200',
    iconBg: 'bg-orange-500',
    textColor: 'text-orange-700',
    valueColor: 'text-orange-900',
  },
]);
</script>

<template>
  <AppLayout title="Archived School Years">
    <template #header>
      <div class="flex items-center">
        <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
          <i class="fas fa-chevron-left"></i>
        </button>
        <h2 class="font-semibold text-xl text-gray-800 flex items-center">
          <i class="fas fa-archive text-orange-500 text-xl mr-2"></i>
          Archived School Years
        </h2>
      </div>
    </template>

    <Container class="py-6 space-y-6">
      <!-- Stats Cards (match Graduates visual style) -->
      <div class="grid grid-cols-1 gap-6 mb-6">
        <div
          v-for="(stat, index) in stats"
          :key="index"
          :class="[
            stat.bgGradient,
            'rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105',
          ]"
        >
          <div class="flex flex-col items-center text-center">
            <div :class="[stat.iconBg, 'w-12 h-12 rounded-full flex items-center justify-center mb-3']">
              <i :class="[stat.icon, stat.color, 'text-lg']"></i>
            </div>
            <h3 :class="[stat.textColor, 'text-sm font-medium mb-2']">{{ stat.title }}</h3>
            <p :class="[stat.valueColor, 'text-2xl font-bold']">{{ stat.value }}</p>
          </div>
        </div>
      </div>

      <!-- Filter/Search Section (styled like Graduates) -->
      <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
          <i class="fas fa-filter text-blue-500 mr-2"></i>
          Search Archived School Years
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
          <!-- Search Filter -->
          <div class="flex flex-col">
            <label for="search" class="text-sm font-medium text-gray-700 mb-1">Search</label>
            <div class="relative">
              <input
                v-model="searchQuery"
                type="text"
                id="search"
                placeholder="Search archived school years..."
                class="w-full pl-3 pr-10 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
              />
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                <i class="fas fa-search"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Table Card (match Graduates header and layout) -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
              <i class="fas fa-table text-white"></i>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-800">Archived School Years</h3>
              <p class="text-sm text-gray-600">Manage archived school years</p>
              <span class="text-sm font-semibold text-gray-700">{{ filteredSchoolYears.length }} total</span>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full table-auto">
            <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 text-sm font-semibold text-gray-700">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                  <div class="flex items-center">School Year</div>
                </th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                  <div class="flex items-center">Term</div>
                </th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                  <div class="flex items-center">Status</div>
                </th>
                <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                  <div class="flex items-center justify-end">Actions</div>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
              <tr
                v-for="sy in filteredSchoolYears"
                :key="sy.id"
                class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group"
              >
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="text-sm font-semibold text-gray-900">
                    {{ sy.school_year?.school_year_range }}
                  </div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="text-sm text-gray-700">{{ sy.term }}</div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 shadow-sm">
                    <i class="fas fa-archive mr-1.5"></i>
                    Archived
                  </span>
                </td>
                <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <button
                      @click="confirmRestore(sy)"
                      class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-2 rounded-full hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md"
                      title="Restore"
                    >
                      <i class="fas fa-undo"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="filteredSchoolYears.length === 0" class="py-12 text-center">
          <i class="fas fa-archive text-gray-300 text-5xl mb-4"></i>
          <h3 class="text-lg font-medium text-gray-700">No archived school years found</h3>
          <p class="text-gray-500 mt-1">When school years are archived, they will appear here</p>
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
