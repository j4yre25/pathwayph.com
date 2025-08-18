<script setup>
import { ref } from 'vue';
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
});

const showConfirmModal = ref(false);
const showEditModal = ref(false);
const programToArchive = ref(null);
const programToEdit = ref(null);

function applyFilters() {
  router.get(route('internship-programs.index'), filters.value, { preserveState: true, preserveScroll: true });
}

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
    },
  });
}

function restoreProgram(id) {
  if (confirm('Restore this internship program?')) {
    router.post(route('internship-programs.restore', { id }), {}, { preserveScroll: true });
  }
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
    <template #header>
      <div class="flex items-center">
        <i class="fas fa-briefcase text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Internship Programs</h2>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500 relative overflow-hidden">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-gray-600 text-sm font-medium mb-2">Total Programs</h3>
                <p class="text-3xl font-bold text-gray-800">{{ internshipPrograms.length }}</p>
              </div>
              <div class="bg-blue-100 rounded-full p-3 flex items-center justify-center">
                <i class="fas fa-briefcase text-blue-600"></i>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500 relative overflow-hidden">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-gray-600 text-sm font-medium mb-2">Active Programs</h3>
                <p class="text-3xl font-bold text-gray-800">{{ internshipPrograms.filter(ip => !ip.deleted_at).length }}</p>
              </div>
              <div class="bg-green-100 rounded-full p-3 flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600"></i>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500 relative overflow-hidden">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-gray-600 text-sm font-medium mb-2">Archived Programs</h3>
                <p class="text-3xl font-bold text-gray-800">{{ internshipPrograms.filter(ip => ip.deleted_at).length }}</p>
              </div>
              <div class="bg-purple-100 rounded-full p-3 flex items-center justify-center">
                <i class="fas fa-archive text-purple-600"></i>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Main Content -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
          <!-- Action Buttons -->
          <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-200">
            <div class="flex items-center">
              <h3 class="text-lg font-semibold text-gray-800">Internship Programs</h3>
              <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ internshipPrograms.length }} total</span>
            </div>
            <div class="flex w-full sm:w-auto space-x-3 mt-3 sm:mt-0">
              <Link :href="route('internship-programs.create')" 
                    class="text-sm px-4 py-2 rounded-md bg-blue-500 text-white hover:bg-blue-600 transition-colors duration-200 flex items-center">
                <i class="fas fa-plus-circle text-white mr-2"></i> Add Internship
              </Link>
              <Link :href="route('internship-programs.list')" 
                    class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
                <i class="fas fa-list text-blue-500 mr-2"></i> All Internship
              </Link>
              <Link :href="route('internship-programs.archivedlist', { status: 'inactive' })" 
                    class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
                <i class="fas fa-archive text-gray-500 mr-2"></i> Archived
              </Link>
              <Link :href="route('internship-programs.assign-page')" 
                    class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
                <i class="fas fa-user-plus text-indigo-500 mr-2"></i> Assign Graduates
              </Link>
            </div>
          </div>

          <!-- Filters Section -->
          <div class="p-4 border-b border-gray-200 bg-gray-50">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Program</label>
                <select v-model="filters.program_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                  <option value="">All Programs</option>
                  <option v-for="p in programs" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Career Opportunity</label>
                <select v-model="filters.career_opportunity_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                  <option value="">All Career Opportunities</option>
                  <option v-for="c in careerOpportunities" :key="c.id" :value="c.id">{{ c.title }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Skills</label>
                <input v-model="filters.skills" type="text" placeholder="e.g. Vue, Laravel" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" />
              </div>
              <div class="mt-4 flex justify-end space-x-2">
                <button @click="() => { filters.program_id = ''; filters.career_opportunity_id = ''; filters.skills = ''; applyFilters(); }" 
                        class="px-3 py-2 text-xs rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                  <i class="fas fa-times mr-1"></i> Clear
                </button>
                <button @click="applyFilters" 
                        class="px-3 py-2 text-xs rounded-md bg-blue-500 text-white hover:bg-blue-600 transition-colors duration-200">
                  <i class="fas fa-filter mr-1"></i> Apply Filter
                </button>
              </div>
            </div>
          </div>

          <!-- Internship Programs Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead>
                <tr class="bg-gray-50">
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Career Opportunity</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skills</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="ip in internshipPrograms" :key="ip.id" :class="{ 'bg-gray-50': ip.deleted_at }" class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ ip.title }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div class="flex flex-wrap gap-1">
                      <span v-for="(p, idx) in ip.programs" :key="p.id" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ p.name }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div class="flex flex-wrap gap-1">
                      <span v-for="(c, idx) in ip.career_opportunities" :key="c.id" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ c.title }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div class="flex flex-wrap gap-1">
                      <span v-for="(s, idx) in ip.skills" :key="s.id" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        {{ s.name }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <Link :href="route('internship-programs.edit', ip.id)" class="text-blue-600 hover:text-blue-900 mr-3">
                      <i class="fas fa-edit"></i>
                    </Link>
                    <button v-if="!ip.deleted_at" @click="() => confirmArchive(ip)" class="text-red-600 hover:text-red-900">
                      <i class="fas fa-archive"></i>
                    </button>
                    <button v-else @click="() => restoreProgram(ip.id)" class="text-green-600 hover:text-green-900">
                      <i class="fas fa-undo"></i>
                    </button>
                  </td>
                </tr>
                <tr v-if="internshipPrograms.length === 0">
                  <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center py-6">
                      <i class="fas fa-folder-open text-gray-400 text-4xl mb-2"></i>
                      <p>No internship programs found.</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
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
            <button @click="cancelArchive" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
              Cancel
            </button>
            <button @click="archiveConfirmed" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
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
