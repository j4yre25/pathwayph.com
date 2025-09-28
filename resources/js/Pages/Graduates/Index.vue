<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import GraduateEdit from './GraduateEdit.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  graduates: Object,
  programs: Array,
  years: Array,
  terms: Array,
  genders: Array,
  careerOpportunities: Array,
});

const isModalOpen = ref(false);
const selectedGraduate = ref(null);

const showConfirmModal = ref(false)
const graduateToArchive = ref(null)
const flashBanner = ref('')

const filters = ref({
  name: props.filters?.name ?? '',
  year: props.filters?.year ?? '',
  term: props.filters?.term ?? '',
  gender: props.filters?.gender ?? '',
  careerOpportunity: props.filters?.careerOpportunity ?? '',
  program: props.filters?.program ?? '',
})

// Watch for filter changes and reload the page with filters as query params
watch(filters, (newFilters) => {
  router.get(route('graduates.index'), newFilters, { preserveState: true, replace: true });
}, { deep: true });

const page = ref(1);
const perPage = 20;

// Helper for safe string comparison (case-insensitive, trimmed)
function normalize(val) {
  return (val ?? '').toString().trim().toLowerCase();
}

// Computed property for filtered graduates
const filteredGraduates = computed(() => {
  return (props.graduates.data || []).filter(g => {
    // Name filter (searches full name)
    const name = `${g.first_name ?? ''} ${g.middle_name ?? ''} ${g.last_name ?? ''}`.toLowerCase();
    if (filters.value.name && !name.includes(filters.value.name.toLowerCase())) return false;

    // Year filter (case-insensitive, by year_graduated)
    if (filters.value.year && normalize(g.year_graduated) !== normalize(filters.value.year)) return false;

    // Term filter (case-insensitive)
    if (filters.value.term && normalize(g.term) !== normalize(filters.value.term)) return false;

    // Gender filter (case-insensitive)
    if (filters.value.gender && normalize(g.gender) !== normalize(filters.value.gender)) return false;

    // Career Opportunity filter (case-insensitive, matches current_job_title)
    if (filters.value.careerOpportunity && normalize(g.current_job_title) !== normalize(filters.value.careerOpportunity)) return false;

    // Program filter (by program_id, not name)
    if (filters.value.program && String(g.program_id) !== String(filters.value.program)) return false;

    return true;
  });
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredGraduates.value.length / perPage)));
const paginatedGraduates = computed(() => {
  const start = (page.value - 1) * perPage;
  return filteredGraduates.value.slice(start, start + perPage);
});

// Reset to page 1 when filters change
watch(filteredGraduates, () => { page.value = 1; });

function editGraduate(grad) {
  // Pre-fill the modal with all graduate info.
  selectedGraduate.value = { ...grad }
  isModalOpen.value = true;
}

function closeModal() {
  isModalOpen.value = false;
  selectedGraduate.value = null;
}

// Show confirmation modal
function confirmArchive(grad) {
  graduateToArchive.value = grad
  showConfirmModal.value = true
}

// Archive after confirmation
function archiveGraduateConfirmed() {
  router.delete(route('graduates.destroy', { graduate: graduateToArchive.value.graduate_id }), {
    onSuccess: () => {
      flashBanner.value = 'Graduate archived successfully.'
      showConfirmModal.value = false
      graduateToArchive.value = null
      setTimeout(() => flashBanner.value = '', 3000)
    }
  })
}

// Cancel confirmation
function cancelArchive() {
  showConfirmModal.value = false
  graduateToArchive.value = null
}

</script>

<template>
  <AppLayout title="Graduate Management">
    <!-- Page Header -->
    <template #header>
      <div class="flex items-center">
        <i class="fas fa-user-graduate text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Graduate Management</h2>
      </div>
    </template>
    
    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Stats Summary Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Total Graduates Card -->
          <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
            <div class="flex flex-col items-center text-center">
              <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3">
                <i class="fas fa-user-graduate text-white text-lg"></i>
              </div>
              <h3 class="text-blue-700 text-sm font-medium mb-2">Total Graduates</h3>
              <p class="text-2xl font-bold text-blue-900">{{ graduates.total || 0 }}</p>
            </div>
          </div>

          <!-- Active Graduates Card -->
          <div class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
            <div class="flex flex-col items-center text-center">
              <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-3">
                <i class="fas fa-check-circle text-white text-lg"></i>
              </div>
              <h3 class="text-green-700 text-sm font-medium mb-2">Active Graduates</h3>
              <p class="text-2xl font-bold text-green-900">{{ graduates.data.length || 0 }}</p>
            </div>
          </div>

          <!-- Programs Card -->
          <div class="bg-gradient-to-br from-purple-100 to-purple-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
            <div class="flex flex-col items-center text-center">
              <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mb-3">
                <i class="fas fa-graduation-cap text-white text-lg"></i>
              </div>
              <h3 class="text-purple-700 text-sm font-medium mb-2">Programs</h3>
              <p class="text-2xl font-bold text-purple-900">{{ programs.length || 0 }}</p>
            </div>
          </div>
        </div>

        <!-- Action Buttons Section -->
        <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-wrap items-center justify-between gap-4 border border-gray-100">
          <div class="flex items-center space-x-4">
            <button @click="$inertia.get(route('graduates.list'))" 
                    :class="[route().current('graduates.list') ? 'bg-blue-50 text-blue-600 border-blue-200' : 'text-gray-600 hover:bg-gray-50 border-gray-200',
                      'px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border']">
              <i class="fas fa-list mr-2"></i>
              <span>Active Graduates</span>
            </button>
            <button @click="$inertia.get(route('graduates.archived'))" 
                    :class="[route().current('graduates.archived') ? 'bg-blue-50 text-blue-600 border-blue-200' : 'text-gray-600 hover:bg-gray-50 border-gray-200',
                      'px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border']">
              <i class="fas fa-archive mr-2"></i>
              <span>Archived Graduates</span>
            </button>
          </div>
          <div class="flex items-center space-x-3">
            <button @click="$inertia.get(route('graduates.create'))" 
                    class="px-4 py-2 rounded-md bg-blue-500 text-white font-medium transition-colors flex items-center shadow-sm hover:bg-blue-600">
              <i class="fas fa-plus mr-2"></i> Add Graduate
            </button>
            <button @click="$inertia.get(route('graduates.batch.page'))" 
                    class="px-4 py-2 rounded-md bg-indigo-500 text-white font-medium transition-colors flex items-center shadow-sm hover:bg-indigo-600">
              <i class="fas fa-upload mr-2"></i> Batch Upload
            </button>
          </div>
        </div>

        <!-- Search and Filter Controls Section -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-filter text-blue-500 mr-2"></i>
            Filter Graduates
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Search Name</label>
              <input
                v-model="filters.name"
                type="text"
                placeholder="Search by name"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
              <select v-model="filters.year" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                <option value="">All Years</option>
                <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Term</label>
              <select v-model="filters.term" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                <option value="">All Terms</option>
                <option v-for="t in terms" :key="t" :value="t">{{ t }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
              <select v-model="filters.gender" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                <option value="">All Genders</option>
                <option v-for="g in genders" :key="g" :value="g">{{ g }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Career Opportunity</label>
              <select v-model="filters.careerOpportunity" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                <option value="">All Career Opportunities</option>
                <option v-for="c in careerOpportunities" :key="c" :value="c">{{ c }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Program</label>
              <select v-model="filters.program" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                <option value="">All Programs</option>
                <option v-for="p in programs" :key="p.id" :value="p.id">{{ p.name }}</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Main Data List Section -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
          <div class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center">
              <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-table text-white"></i>
              </div>
              <div>
                <h3 class="text-xl font-bold text-gray-800">Graduate Management</h3>
                <p class="text-sm text-gray-600">Manage graduate records and information</p>
                <span class="text-sm font-semibold text-gray-700">{{ graduates.total }} total graduates</span>
              </div>
            </div>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
              <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 text-sm font-semibold text-gray-700">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Name</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Program</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Year Graduated</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Term</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Current Job Title</th>
                  <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                    <div class="flex items-center justify-center">
                      Actions
                    </div>
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="graduate in graduates.data" :key="graduate.graduate_id" class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group">
                  <td class="px-6 py-5 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center text-blue-600 shadow-sm group-hover:shadow-md transition-shadow">
                        <i class="fas fa-user-graduate"></i>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-semibold text-gray-900">{{ graduate.first_name }} {{ graduate.middle_name }} {{ graduate.last_name }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-5 whitespace-nowrap">
                    <div class="text-sm font-semibold text-gray-900">{{ graduate.program_name }}</div>
                  </td>
                  <td class="px-6 py-5 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ graduate.year_graduated }}</div>
                  </td>
                  <td class="px-6 py-5 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ graduate.term }}</div>
                  </td>
                  <td class="px-6 py-5 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ graduate.current_job_title || 'Not specified' }}</div>
                  </td>
                  <td class="px-6 py-5 whitespace-nowrap text-center">
                    <div class="flex items-center justify-center space-x-2">
                      <button @click="editGraduate(graduate)" 
                              class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors duration-200">
                        <i class="fas fa-edit mr-1"></i>
                        Edit
                      </button>
                      <button @click="confirmArchive(graduate)" 
                              class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                        <i class="fas fa-archive mr-1"></i>
                        Archive
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div v-if="graduates.data.length === 0" class="py-12 text-center">
            <i class="fas fa-user-graduate text-gray-300 text-5xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-700">No graduates found</h3>
            <p class="text-gray-500 mt-1">Try adjusting your filters or add new graduates</p>
          </div>

          <!-- Pagination -->
          <div v-if="graduates.links && graduates.links.length > 3" class="flex items-center justify-center mt-8 mb-6 px-6">
            <div class="flex items-center space-x-2">
              <button
                v-for="(link, index) in graduates.links"
                :key="index"
                v-html="link.label"
                :class="[
                  'px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                  link.active 
                    ? 'bg-blue-600 text-white shadow-md' 
                    : link.url 
                      ? 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 hover:border-gray-400' 
                      : 'text-gray-400 bg-gray-100 cursor-not-allowed'
                ]"
                :disabled="!link.url"
                @click="link.url && router.get(link.url, filters, { preserveState: true, replace: true })"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Graduate Modal -->
    <GraduateEdit
      :show="isModalOpen"
      :graduate="selectedGraduate"
      :programs="programs"
      :years="years"
      :insti-users="instiUsers"
      @close="closeModal"
    />
    
    <!-- Confirmation Modal -->
    <div v-if="showConfirmModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full">
        <div class="flex items-center mb-4">
          <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mr-4">
            <i class="fas fa-exclamation-triangle text-red-500"></i>
          </div>
          <h3 class="text-lg font-semibold text-gray-800">Confirm Archive</h3>
        </div>
        <p class="text-gray-600 mb-6">Are you sure you want to archive this graduate? This action can be reversed later.</p>
        <div class="flex justify-end space-x-3">
          <button @click="cancelArchive" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm transition-colors duration-200">
            Cancel
          </button>
          <button @click="archiveGraduateConfirmed" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 text-sm transition-colors duration-200">
            Archive
          </button>
        </div>
      </div>
    </div>
    
    <!-- Flash Banner -->
    <div v-if="flashBanner" class="fixed top-0 left-0 right-0 bg-green-500 text-white text-center py-3 shadow-md z-50 transition-all duration-300 ease-in-out">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
        <div class="flex items-center">
          <i class="fas fa-check-circle mr-2"></i>
          <span>{{ flashBanner }}</span>
        </div>
        <button @click="flashBanner = ''" class="text-white hover:text-gray-200">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  </AppLayout>
</template>
