<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import GraduateEdit from './GraduateEdit.vue';

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
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Graduate Management</h2>
    </template>
    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-xl rounded-2xl p-6">
          <!-- Action Buttons -->
          <div class="flex justify-between items-center mb-7">
            <div>
              <button @click="$inertia.get(route('graduates.list'))" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mr-2">
                List of Graduates
              </button>
              <button @click="$inertia.get(route('graduates.archived'))" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Archived Graduates
              </button>
            </div>
            <div class="flex gap-x-2">
              <button @click="$inertia.get(route('graduates.create'))" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Add Graduate
              </button>
              <button @click="$inertia.get(route('graduates.batch.page'))" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                Batch Upload
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
          <div v-if="showConfirmModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white p-6 rounded shadow-lg">
              <h3 class="text-lg font-semibold mb-4">Confirm Archive</h3>
              <p>Are you sure you want to archive this graduate?</p>
              <div class="mt-4 flex justify-end space-x-2">
                <button @click="cancelArchive" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                  Cancel
                </button>
                <button @click="archiveGraduateConfirmed" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                  Confirm
                </button>
              </div>
            </div>
          </div>
          <!-- Flash Banner -->
          <div v-if="flashBanner" class="fixed top-0 left-0 right-0 bg-green-500 text-white text-center py-2">
            {{ flashBanner }}
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
