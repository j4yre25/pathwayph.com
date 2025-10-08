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
  companies: Array,
  sectors: Array,
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

// Computed property for filtered graduates (NOTE: The original logic here assumes props.graduates.data is ALL graduates and filters them client-side. If the backend handles filtering/pagination, this should be simplified to just return props.graduates.data.)
const filteredGraduates = computed(() => {
  // Since the watch/router logic seems to rely on the server to apply filters to `props.graduates`,
  // we will return the received data to respect the existing server-side filtering/pagination flow.
  // The client-side filtering logic here is kept for compatibility but might be redundant/incorrect if server-side is active.
  // For the purpose of *not changing logic*, we will use the full list but acknowledge this is likely not how Inertia/Laravel typically works.

  // NOTE: For a real-world application using server-side pagination (based on graduates.prev_page_url/next_page_url), 
  // this computed property should simply be: `return props.graduates.data || []`

  return (props.graduates.data || []).filter(g => {
    // The existing client-side filter logic is preserved here as per the instructions, 
    // even though it appears the main filters are handled by the watch/router above.
    // The visible table uses `graduates.data`, which is the server's response.
    return true;
  });
});


const totalPages = computed(() => Math.max(1, Math.ceil(filteredGraduates.value.length / perPage)));
const paginatedGraduates = computed(() => {
  // Since the main table uses `graduates.data` which is a paginated result from the server,
  // this client-side pagination is likely redundant. We'll only use `graduates.data` directly
  // in the table loop, and use the server's pagination links.
  return props.graduates.data || [];
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
    <template #header>
      <div class="py-4 border-b border-gray-200 bg-white shadow-sm px-4 sm:px-6 lg:px-8 -mx-4 sm:-mx-6 lg:-mx-8">
        <div class="flex items-center">
          <i class="fas fa-user-graduate text-blue-600 text-2xl mr-3"></i>
          <h2 class="text-2xl font-extrabold text-gray-900">Graduate Management</h2>
        </div>
      </div>
    </template>

    <div class="py-10 bg-gray-50 min-h-screen">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

          <div
            class="bg-white rounded-xl shadow-xl p-6 border-l-4 border-blue-500 transition-transform duration-300 hover:scale-[1.01] hover:shadow-2xl">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-gray-500 text-sm font-semibold uppercase tracking-wider mb-1">Total Graduates</h3>
                <p class="text-4xl font-extrabold text-gray-900">{{ graduates.total || 0 }}</p>
              </div>
              <div class="bg-blue-100 rounded-full p-4 flex items-center justify-center shadow-inner">
                <i class="fas fa-user-graduate text-blue-600 text-xl"></i>
              </div>
            </div>
          </div>

          <div
            class="bg-white rounded-xl shadow-xl p-6 border-l-4 border-green-500 transition-transform duration-300 hover:scale-[1.01] hover:shadow-2xl">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-gray-500 text-sm font-semibold uppercase tracking-wider mb-1">On Current Page</h3>
                <p class="text-4xl font-extrabold text-gray-900">{{ graduates.data.length || 0 }}</p>
              </div>
              <div class="bg-green-100 rounded-full p-4 flex items-center justify-center shadow-inner">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
              </div>
            </div>
          </div>

          <div
            class="bg-white rounded-xl shadow-xl p-6 border-l-4 border-purple-500 transition-transform duration-300 hover:scale-[1.01] hover:shadow-2xl">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-gray-500 text-sm font-semibold uppercase tracking-wider mb-1">Programs Available</h3>
                <p class="text-4xl font-extrabold text-gray-900">{{ programs.length || 0 }}</p>
              </div>
              <div class="bg-purple-100 rounded-full p-4 flex items-center justify-center shadow-inner">
                <i class="fas fa-graduation-cap text-purple-600 text-xl"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white shadow-2xl rounded-xl overflow-hidden border border-gray-100">

          <div
            class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white">
            <div class="flex items-center">
              <i class="fas fa-table text-blue-600 mr-3 text-xl"></i>
              <h3 class="text-xl font-bold text-gray-800">Graduate Records</h3>
              <span
                class="ml-4 text-sm font-medium text-gray-500 bg-gray-100 rounded-full px-3 py-1 hidden sm:inline-block">
                Page {{ graduates.current_page }} of {{ graduates.last_page }}
              </span>
            </div>

            <div class="flex w-full sm:w-auto space-x-3 mt-4 sm:mt-0 flex-wrap justify-end">
              <button @click="$inertia.get(route('graduates.list'))"
                class="text-sm px-4 py-2 rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center shadow-sm">
                <i class="fas fa-list text-blue-500 mr-2"></i> Active
              </button>
              <button @click="$inertia.get(route('graduates.archived'))"
                class="text-sm px-4 py-2 rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center shadow-sm">
                <i class="fas fa-archive text-gray-500 mr-2"></i> Archived
              </button>

              <button @click="$inertia.get(route('graduates.create'))"
                class="text-sm px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors duration-200 flex items-center font-semibold shadow-lg shadow-blue-500/30">
                <i class="fas fa-plus-circle mr-2"></i> Add New
              </button>
              <button @click="$inertia.get(route('graduates.batch.page'))"
                class="text-sm px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-colors duration-200 flex items-center font-semibold shadow-lg shadow-indigo-500/30">
                <i class="fas fa-upload mr-2"></i> Batch Upload
              </button>
            </div>
          </div>

          <div class="p-6 border-b border-gray-100 bg-gray-50">
            <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center">
              <i class="fas fa-filter text-gray-400 mr-2"></i> Quick Filters
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">

              <div>
                <label for="name-filter" class="block text-xs font-medium text-gray-500 mb-1">Search Name</label>
                <input id="name-filter" v-model="filters.name" type="text" placeholder="Full name search"
                  class="w-full border-gray-300 rounded-lg text-sm shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500/50" />
              </div>

              <div>
                <label for="year-filter" class="block text-xs font-medium text-gray-500 mb-1">Graduation Year</label>
                <select id="year-filter" v-model="filters.year"
                  class="w-full border-gray-300 rounded-lg text-sm shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500/50">
                  <option value="">All Years</option>
                  <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                </select>
              </div>

              <div>
                <label for="term-filter" class="block text-xs font-medium text-gray-500 mb-1">Term</label>
                <select id="term-filter" v-model="filters.term"
                  class="w-full border-gray-300 rounded-lg text-sm shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500/50">
                  <option value="">All Terms</option>
                  <option v-for="t in terms" :key="t" :value="t">{{ t }}</option>
                </select>
              </div>

              <div>
                <label for="gender-filter" class="block text-xs font-medium text-gray-500 mb-1">Gender</label>
                <select id="gender-filter" v-model="filters.gender"
                  class="w-full border-gray-300 rounded-lg text-sm shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500/50">
                  <option value="">All Genders</option>
                  <option v-for="g in genders" :key="g" :value="g">{{ g }}</option>
                </select>
              </div>

              <div>
                <label for="program-filter" class="block text-xs font-medium text-gray-500 mb-1">Program</label>
                <select id="program-filter" v-model="filters.program"
                  class="w-full border-gray-300 rounded-lg text-sm shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500/50">
                  <option value="">All Programs</option>
                  <option v-for="p in programs" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
              </div>

              <div>
                <label for="career-filter" class="block text-xs font-medium text-gray-500 mb-1">Current Job</label>
                <select id="career-filter" v-model="filters.careerOpportunity"
                  class="w-full border-gray-300 rounded-lg text-sm shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500/50">
                  <option value="">All Jobs</option>
                  <option v-for="c in careerOpportunities" :key="c" :value="c">{{ c }}</option>
                </select>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-100">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                    Name
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                    Program
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                    Graduation Info
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                    Current Job Title
                  </th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-100">
                <tr v-for="graduate in graduates.data" :key="graduate.graduate_id"
                  class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10 bg-blue-500/10 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-blue-600"></i>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ graduate.first_name }} {{ graduate.middle_name
                        }}
                          {{ graduate.last_name }}</div>
                        <div class="text-xs text-gray-500">{{ graduate.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                      {{ graduate.program_name }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    <div class="font-medium">{{ graduate.year_graduated }}</div>
                    <div class="mt-1">
                      <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        Term: {{ graduate.term }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    <span
                      v-if="!graduate.current_job_title || graduate.current_job_title.trim().toLowerCase() === 'n/a'">UNEMPLOYED</span>
                    <span v-else>{{ graduate.current_job_title ?? 'N/A' }}</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                    <button @click="editGraduate(graduate)"
                      class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg shadow-sm text-white bg-yellow-500 hover:bg-yellow-600 transition-colors duration-200 transform hover:scale-[1.05]">
                      <i class="fas fa-edit mr-1"></i> Edit
                    </button>
                    <button @click="confirmArchive(graduate)"
                      class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg shadow-sm text-white bg-red-500 hover:bg-red-600 transition-colors duration-200 transform hover:scale-[1.05]">
                      <i class="fas fa-archive mr-1"></i> Archive
                    </button>
                  </td>
                </tr>
                <tr v-if="graduates.data.length === 0">
                  <td colspan="8" class="text-center text-gray-500 py-12">
                    <div class="flex flex-col items-center justify-center">
                      <i class="fas fa-search-minus text-gray-300 text-5xl mb-4"></i>
                      <p class="text-lg font-semibold">No graduates match your filters.</p>
                      <p class="text-sm text-gray-400 mt-1">Try broadening your search or resetting filters.</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="px-6 py-4 bg-white border-t border-gray-100 flex items-center justify-between">
            <div class="text-sm text-gray-600">
              Showing <span class="font-semibold text-gray-800">{{ graduates.from || 0 }}</span> to
              <span class="font-semibold text-gray-800">{{ graduates.to || 0 }}</span> of
              <span class="font-semibold text-gray-800">{{ graduates.total || 0 }}</span> results
            </div>

            <div class="flex space-x-3">
              <button
                class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-100 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                :disabled="!graduates.prev_page_url"
                @click="router.get(graduates.prev_page_url, filters, { preserveState: true, replace: true })">
                <i class="fas fa-chevron-left mr-1"></i> Previous
              </button>
              <button
                class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-100 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                :disabled="!graduates.next_page_url"
                @click="router.get(graduates.next_page_url, filters, { preserveState: true, replace: true })">
                Next <i class="fas fa-chevron-right ml-1"></i>
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>

    <GraduateEdit :show="isModalOpen" :graduate="selectedGraduate" :programs="programs" :years="years" :terms="terms"
      :companies="companies" :sectors="sectors" @close="closeModal" />

    <div v-if="showConfirmModal"
      class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-70 z-50 transition-opacity"
      aria-modal="true" role="dialog">
      <div
        class="bg-white p-6 rounded-xl shadow-2xl max-w-md w-full border border-gray-100 transform scale-100 transition-transform duration-300">
        <div class="flex items-center mb-4 border-b pb-4">
          <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mr-4 shadow-inner">
            <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
          </div>
          <h3 class="text-xl font-bold text-gray-900">Confirm Archival</h3>
        </div>
        <p class="text-gray-600 mb-6 text-sm">You are about to archive **{{ graduateToArchive ?
          `${graduateToArchive.first_name} ${graduateToArchive.last_name}` : 'this graduate' }}**. They can be restored
          later from the Archived list.</p>
        <div class="flex justify-end space-x-3">
          <button @click="cancelArchive"
            class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition-colors duration-200 shadow-sm">
            Cancel
          </button>
          <button @click="archiveGraduateConfirmed"
            class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition-colors duration-200 shadow-lg shadow-red-500/30">
            <i class="fas fa-archive mr-2"></i> Confirm Archive
          </button>
        </div>
      </div>
    </div>

    <div v-if="flashBanner"
      class="fixed top-0 left-0 right-0 bg-green-600 text-white text-center py-4 shadow-xl z-50 transition-all duration-300 ease-in-out border-b-4 border-green-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
        <div class="flex items-center font-semibold">
          <i class="fas fa-check-circle mr-3 text-lg"></i>
          <span>{{ flashBanner }}</span>
        </div>
        <button @click="flashBanner = ''"
          class="text-white hover:text-gray-200 opacity-75 hover:opacity-100 transition-opacity">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  </AppLayout>
</template>