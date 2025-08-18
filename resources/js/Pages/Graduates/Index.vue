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
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500 relative overflow-hidden">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-gray-600 text-sm font-medium mb-2">Total Graduates</h3>
                <p class="text-3xl font-bold text-gray-800">{{ graduates.total || 0 }}</p>
              </div>
              <div class="bg-blue-100 rounded-full p-3 flex items-center justify-center">
                <i class="fas fa-user-graduate text-blue-600"></i>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500 relative overflow-hidden">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-gray-600 text-sm font-medium mb-2">Active Graduates</h3>
                <p class="text-3xl font-bold text-gray-800">{{ graduates.data.length || 0 }}</p>
              </div>
              <div class="bg-green-100 rounded-full p-3 flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600"></i>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500 relative overflow-hidden">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-gray-600 text-sm font-medium mb-2">Programs</h3>
                <p class="text-3xl font-bold text-gray-800">{{ programs.length || 0 }}</p>
              </div>
              <div class="bg-purple-100 rounded-full p-3 flex items-center justify-center">
                <i class="fas fa-graduation-cap text-purple-600"></i>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Main Content -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
          <!-- Action Buttons -->
          <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-200">
            <div class="flex items-center">
              <h3 class="text-lg font-semibold text-gray-800">Graduates List</h3>
              <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ graduates.total }} total</span>
            </div>
            <div class="flex w-full sm:w-auto space-x-3 mt-3 sm:mt-0">
              <button @click="$inertia.get(route('graduates.list'))" 
                      class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
                <i class="fas fa-list text-blue-500 mr-2"></i> Active
              </button>
              <button @click="$inertia.get(route('graduates.archived'))" 
                      class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
                <i class="fas fa-archive text-gray-500 mr-2"></i> Archived
              </button>
              <button @click="$inertia.get(route('graduates.create'))" 
                      class="text-sm px-4 py-2 rounded-md bg-blue-500 text-white hover:bg-blue-600 transition-colors duration-200 flex items-center">
                <i class="fas fa-plus-circle mr-2"></i> Add Graduate
              </button>
              <button @click="$inertia.get(route('graduates.batch.page'))" 
                      class="text-sm px-4 py-2 rounded-md bg-indigo-500 text-white hover:bg-indigo-600 transition-colors duration-200 flex items-center">
                <i class="fas fa-upload mr-2"></i> Batch Upload
              </button>
            </div>
          </div>
          <!-- Search and Filter -->
          <div class="mb-4 grid grid-cols-1 md:grid-cols-6 gap-4">
            <input
              v-model="filters.name"
              type="text"
              placeholder="Search by name"
              class="rounded-lg border-gray-300"
            />
            <select v-model="filters.year" class="rounded-lg border-gray-300">
              <option value="">All Years</option>
              <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
            </select>
            <select v-model="filters.term" class="rounded-lg border-gray-300">
              <option value="">All Terms</option>
              <option v-for="t in terms" :key="t" :value="t">{{ t }}</option>
            </select>
            <select v-model="filters.gender" class="rounded-lg border-gray-300">
              <option value="">All Genders</option>
              <option v-for="g in genders" :key="g" :value="g">{{ g }}</option>
            </select>
            <select v-model="filters.careerOpportunity" class="rounded-lg border-gray-300">
              <option value="">All Career Opportunities</option>
              <option v-for="c in careerOpportunities" :key="c" :value="c">{{ c }}</option>
            </select>
            <select v-model="filters.program" class="rounded-lg border-gray-300">
              <option value="">All Programs</option>
              <option v-for="p in programs" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>
          <!-- Graduate Table -->
          <table class="min-w-full table-auto border rounded-lg overflow-hidden">
            <thead class="bg-gray-100 text-sm text-left">
              <tr>
                <th class="p-3">Name</th>
                <th class="p-3">Program</th>
                <th class="px-6 py-4 text-left">Year Graduated</th>
                <th class="px-6 py-4 text-left">Term</th>
                <th class="p-3">Current Job Title</th>
                <th class="p-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="graduate in graduates.data" :key="graduate.graduate_id">
                <td>{{ graduate.first_name }} {{ graduate.middle_name }} {{ graduate.last_name }}</td>
                <td>{{ graduate.program_name }}</td>
                <td>{{ graduate.year_graduated }}</td>
                <td>{{ graduate.term }}</td>
                <td>{{ graduate.current_job_title }}</td>
                <td class="p-3 text-right space-x-2">
                  <button @click="editGraduate(graduate)" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                    Edit
                  </button>
                  <button @click="confirmArchive(graduate)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                    Archive
                  </button>
                </td>
              </tr>
              <tr v-if="paginatedGraduates.length === 0">
                <td colspan="8" class="text-center text-gray-400 py-6">No graduates found.</td>
              </tr>
            </tbody>
          </table>
          <!-- Pagination Controls -->
          <div class="mt-6 flex justify-center gap-2">
            <button
              class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300"
              :disabled="!graduates.prev_page_url"
              @click="router.get(graduates.prev_page_url, filters, { preserveState: true, replace: true })"
            >Prev</button>
            <span class="px-3 py-1">{{ graduates.current_page }} / {{ graduates.last_page }}</span>
            <button
              class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300"
              :disabled="!graduates.next_page_url"
              @click="router.get(graduates.next_page_url, filters, { preserveState: true, replace: true })"
            >Next</button>
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
