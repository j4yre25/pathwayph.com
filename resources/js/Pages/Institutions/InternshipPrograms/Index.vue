<script setup>
import { ref, computed, watch } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import InternshipProgramEditModal from '@/Components/InternshipProgramEditModal.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  internshipPrograms: Array,
  programs: Array,
  careerOpportunities: Array,
  skills: Array,
  graduates: Array,
});

const filters = ref({
  program_id: '',
  career_opportunity_id: '',
  skills: '',
  title: '',
});

// Watch for filter changes and reload the page with filters as query params
watch(filters, (newFilters) => {
  router.get(route('internship-programs.index'), newFilters, { preserveState: true, replace: true });
}, { deep: true });

const showConfirmModal = ref(false);
const showEditModal = ref(false);
const programToArchive = ref(null);
const programToEdit = ref(null);
const flashBanner = ref('');

const page = ref(1);
const perPage = 20;

// Helper for safe string comparison (case-insensitive, trimmed)
function normalize(val) {
  return (val ?? '').toString().trim().toLowerCase();
}

// Computed property for filtered internship programs
const filteredPrograms = computed(() => {
  return (props.internshipPrograms || []).filter(ip => {
    // Title filter
    if (filters.value.title && !normalize(ip.title).includes(normalize(filters.value.title))) return false;

    // Program filter
    if (filters.value.program_id && !ip.programs.some(p => String(p.id) === String(filters.value.program_id))) return false;

    // Career Opportunity filter
    if (filters.value.career_opportunity_id && !ip.career_opportunities.some(c => String(c.id) === String(filters.value.career_opportunity_id))) return false;

    // Skills filter
    if (filters.value.skills && !ip.skills.some(s => normalize(s.name).includes(normalize(filters.value.skills)))) return false;

    return true;
  });
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredPrograms.value.length / perPage)));
const paginatedPrograms = computed(() => {
  const start = (page.value - 1) * perPage;
  return filteredPrograms.value.slice(start, start + perPage);
});

// Reset to page 1 when filters change
watch(filteredPrograms, () => { page.value = 1; });

function confirmArchive(ip) {
  programToArchive.value = ip;
  showConfirmModal.value = true;
}

function archiveConfirmed() {
  if (!programToArchive.value) return;
  router.delete(route('internship-programs.archive', { id: programToArchive.value.id }), {
    preserveScroll: true,
    onSuccess: () => {
      showConfirmModal.value = false;
      programToArchive.value = null;
      flashBanner.value = 'Internship program archived successfully.';
      setTimeout(() => flashBanner.value = '', 3000);
    },
  });
}

function restoreProgram(id) {
  router.post(route('internship-programs.restore', { id }), {}, { 
    preserveScroll: true,
    onSuccess: () => {
      flashBanner.value = 'Internship program restored successfully.';
      setTimeout(() => flashBanner.value = '', 3000);
    }
  });
}

function cancelArchive() {
  showConfirmModal.value = false;
  programToArchive.value = null;
}

function openEditModal(ip) {
  programToEdit.value = ip;
  showEditModal.value = true;
}

function closeEditModal() {
  showEditModal.value = false;
  programToEdit.value = null;
}
</script>

<template>
  <AppLayout title="Manage Internship Programs">
    <!-- Page Header -->
    <template #header>
      <div class="flex items-center">
        <i class="fas fa-briefcase text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Internship Programs</h2>
      </div>
    </template>
    
    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <!-- Stats Summary Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Total Programs Card -->
          <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
            <div class="flex flex-col items-center text-center">
              <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3">
                <i class="fas fa-briefcase text-white text-lg"></i>
              </div>
              <h3 class="text-blue-700 text-sm font-medium mb-2">Total Programs</h3>
              <p class="text-2xl font-bold text-blue-900">{{ internshipPrograms.length || 0 }}</p>
            </div>
          </div>

          <!-- Active Programs Card -->
          <div class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
            <div class="flex flex-col items-center text-center">
              <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-3">
                <i class="fas fa-check-circle text-white text-lg"></i>
              </div>
              <h3 class="text-green-700 text-sm font-medium mb-2">Active Programs</h3>
              <p class="text-2xl font-bold text-green-900">{{ internshipPrograms.filter(ip => !ip.deleted_at).length || 0 }}</p>
            </div>
          </div>

          <!-- Archived Programs Card -->
          <div class="bg-gradient-to-br from-purple-100 to-purple-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
            <div class="flex flex-col items-center text-center">
              <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mb-3">
                <i class="fas fa-archive text-white text-lg"></i>
              </div>
              <h3 class="text-purple-700 text-sm font-medium mb-2">Archived Programs</h3>
              <p class="text-2xl font-bold text-purple-900">{{ internshipPrograms.filter(ip => ip.deleted_at).length || 0 }}</p>
            </div>
          </div>
        </div>

        <!-- Action Buttons Section -->
        <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-wrap items-center justify-between gap-4 border border-gray-100">
          <div class="flex items-center space-x-4">
            <Link :href="route('internship-programs.list')" 
                  :class="[route().current('internship-programs.list') ? 'bg-blue-50 text-blue-600 border-blue-200' : 'text-gray-600 hover:bg-gray-50 border-gray-200',
                    'px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border']">
              <i class="fas fa-list mr-2"></i>
              <span>Active Programs</span>
            </Link>
            <Link :href="route('internship-programs.archivedlist', { status: 'inactive' })" 
                  :class="[route().current('internship-programs.archivedlist') ? 'bg-blue-50 text-blue-600 border-blue-200' : 'text-gray-600 hover:bg-gray-50 border-gray-200',
                    'px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border']">
              <i class="fas fa-archive mr-2"></i>
              <span>Archived Programs</span>
            </Link>
          </div>
          <div class="flex items-center space-x-3">
            <Link :href="route('internship-programs.create')" 
                  class="px-4 py-2 rounded-md bg-blue-500 text-white font-medium transition-colors flex items-center shadow-sm hover:bg-blue-600">
              <i class="fas fa-plus mr-2"></i> Add Program
            </Link>
            <Link :href="route('internship-programs.assign-page')" 
                  class="px-4 py-2 rounded-md bg-indigo-500 text-white font-medium transition-colors flex items-center shadow-sm hover:bg-indigo-600">
              <i class="fas fa-user-plus mr-2"></i> Assign Graduates
            </Link>
          </div>
        </div>

        <!-- Search and Filter Controls Section -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-filter text-blue-500 mr-2"></i>
            Filter Internship Programs
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Search Title</label>
              <input
                v-model="filters.title"
                type="text"
                placeholder="Search by title"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Program</label>
              <select v-model="filters.program_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                <option value="">All Programs</option>
                <option v-for="p in programs" :key="p.id" :value="p.id">{{ p.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Career Opportunity</label>
              <select v-model="filters.career_opportunity_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                <option value="">All Career Opportunities</option>
                <option v-for="c in careerOpportunities" :key="c.id" :value="c.id">{{ c.title }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Skills</label>
              <input v-model="filters.skills" type="text" placeholder="e.g. Vue, Laravel" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200" />
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
                <h3 class="text-xl font-bold text-gray-800">Internship Program Management</h3>
                <p class="text-sm text-gray-600">Manage internship programs and assignments</p>
                <span class="text-sm font-semibold text-gray-700">{{ internshipPrograms.length }} total programs</span>
              </div>
            </div>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
              <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                  <th class="py-2 px-4 text-left border">Title</th>
                  <th class="py-2 px-4 text-left border">Program</th>
                  <th class="py-2 px-4 text-left border">Career Opportunity</th>
                  <th class="py-2 px-4 text-left border">Skills</th>
                  <th class="py-2 px-4 text-left border">Action</th>
                </tr>
              </thead>
              <tbody class="text-gray-600 text-sm font-light">
                <tr v-for="ip in paginatedPrograms" :key="ip.id" :class="{ 'bg-gray-50': ip.deleted_at }" class="border-b border-gray-200 hover:bg-gray-100">
                  <td class="border border-gray-200 px-6 py-4">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center text-blue-600 shadow-sm group-hover:shadow-md transition-shadow">
                        <i class="fas fa-briefcase"></i>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-semibold text-gray-900">{{ ip.title }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="border border-gray-200 px-6 py-4">
                    <div class="flex flex-wrap gap-1">
                      <span v-for="(p, idx) in ip.programs" :key="p.id" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ p.name }}
                      </span>
                    </div>
                  </td>
                  <td class="border border-gray-200 px-6 py-4">
                    <div class="flex flex-wrap gap-1">
                      <span v-for="(c, idx) in ip.career_opportunities" :key="c.id" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ c.title }}
                      </span>
                    </div>
                  </td>
                  <td class="border border-gray-200 px-6 py-4">
                    <div class="flex flex-wrap gap-1">
                      <span v-for="(s, idx) in ip.skills" :key="s.id" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        {{ s.name }}
                      </span>
                    </div>
                  </td>
                  <td class="border border-gray-200 px-6 py-4">
                    <div class="flex items-center justify-center space-x-2">
                      <Link :href="route('internship-programs.edit', ip.id)" 
                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors duration-200">
                        <i class="fas fa-edit mr-1"></i>
                        Edit
                      </Link>
                      <button v-if="!ip.deleted_at" @click="() => confirmArchive(ip)" 
                              class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                        <i class="fas fa-archive mr-1"></i>
                        Archive
                      </button>
                      <button v-else @click="() => restoreProgram(ip.id)" 
                              class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                        <i class="fas fa-undo mr-1"></i>
                        Restore
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div v-if="internshipPrograms.length === 0" class="py-12 text-center">
            <i class="fas fa-briefcase text-gray-300 text-5xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-700">No internship programs found</h3>
            <p class="text-gray-500 mt-1">Try adjusting your filters or add new programs</p>
          </div>

          <!-- Pagination -->
          <div v-if="totalPages > 1" class="flex items-center justify-center mt-8 mb-6 px-6">
            <div class="flex items-center space-x-2">
              <button
                @click="page = Math.max(1, page - 1)"
                :disabled="page === 1"
                :class="[
                  'px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                  page === 1 
                    ? 'text-gray-400 bg-gray-100 cursor-not-allowed' 
                    : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 hover:border-gray-400'
                ]"
              >
                Previous
              </button>
              <div class="flex space-x-1">
                <button 
                  v-for="pageNum in totalPages" 
                  :key="pageNum" 
                  @click="page = pageNum"
                  :class="[
                    'px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                    pageNum === page 
                      ? 'bg-blue-600 text-white shadow-md' 
                      : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 hover:border-gray-400'
                  ]"
                >
                  {{ pageNum }}
                </button>
              </div>
              <button
                @click="page = Math.min(totalPages, page + 1)"
                :disabled="page === totalPages"
                :class="[
                  'px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                  page === totalPages 
                    ? 'text-gray-400 bg-gray-100 cursor-not-allowed' 
                    : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 hover:border-gray-400'
                ]"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Confirmation Modal -->
      <ConfirmationModal :show="showConfirmModal" @close="cancelArchive">
        <template #title>
          <div class="flex items-center">
            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
            <h3 class="text-lg font-medium text-gray-900">Archive Internship Program</h3>
          </div>
        </template>
        <template #content>
          <div class="mt-2">
            <p class="text-sm text-gray-600">
              Are you sure you want to archive the internship program
              <span class="font-semibold text-gray-900">"{{ programToArchive?.title }}"</span>?
            </p>
            <p class="text-sm text-gray-500 mt-2">
              This action will remove the program from active listings. You can restore it later if needed.
            </p>
          </div>
        </template>
        <template #footer>
          <div class="flex justify-end space-x-3">
            <button @click="cancelArchive" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm transition-colors duration-200">
              Cancel
            </button>
            <button @click="archiveConfirmed" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 text-sm transition-colors duration-200">
              Archive
            </button>
          </div>
        </template>
      </ConfirmationModal>
      
      <!-- Edit Modal -->
      <InternshipProgramEditModal 
        :show="showEditModal" 
        :internship-program="programToEdit" 
        :programs="programs" 
        :career-opportunities="careerOpportunities" 
        :skills="skills" 
        @close="closeEditModal" 
      />
    </div>
  </AppLayout>
</template>
