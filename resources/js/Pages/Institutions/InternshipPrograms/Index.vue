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
        <i class="fas fa-briefcase text-blue-600 text-2xl mr-3"></i>
        <h2 class="font-extrabold text-2xl text-gray-800 leading-tight">Internship Program Management</h2>
      </div>
    </template>

    <div class="py-10 bg-gray-50 min-h-screen">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          
          <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500 hover:shadow-xl transition-shadow duration-300">
            <div class="flex justify-between items-center">
              <div>
                <h3 class="text-gray-500 text-sm font-semibold uppercase tracking-wider mb-1">Total Programs</h3>
                <p class="text-4xl font-extrabold text-gray-900">{{ internshipPrograms.length }}</p>
              </div>
              <div class="bg-blue-500/10 rounded-full p-3 flex items-center justify-center">
                <i class="fas fa-briefcase text-blue-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500 hover:shadow-xl transition-shadow duration-300">
            <div class="flex justify-between items-center">
              <div>
                <h3 class="text-gray-500 text-sm font-semibold uppercase tracking-wider mb-1">Active Programs</h3>
                <p class="text-4xl font-extrabold text-gray-900">{{ internshipPrograms.filter(ip => !ip.deleted_at).length }}</p>
              </div>
              <div class="bg-green-500/10 rounded-full p-3 flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500 hover:shadow-xl transition-shadow duration-300">
            <div class="flex justify-between items-center">
              <div>
                <h3 class="text-gray-500 text-sm font-semibold uppercase tracking-wider mb-1">Archived Programs</h3>
                <p class="text-4xl font-extrabold text-gray-900">{{ internshipPrograms.filter(ip => ip.deleted_at).length }}</p>
              </div>
              <div class="bg-purple-500/10 rounded-full p-3 flex items-center justify-center">
                <i class="fas fa-archive text-purple-600 text-xl"></i>
              </div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden">
          
          <div class="p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-100 bg-white">
            <div class="flex items-center">
              <h3 class="text-xl font-bold text-gray-800">Program Index</h3>
              <span class="ml-3 text-xs font-semibold text-blue-600 bg-blue-100 rounded-full px-3 py-1">{{ internshipPrograms.length }} Total</span>
            </div>
            
            <div class="flex flex-wrap gap-3 mt-4 sm:mt-0">
              <Link :href="route('internship-programs.create')" 
                    class="text-sm px-4 py-2.5 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors duration-200 flex items-center shadow-md hover:shadow-lg">
                <i class="fas fa-plus-circle text-white mr-2"></i> Add New
              </Link>
              
              <Link :href="route('internship-programs.list')" 
                    class="text-sm px-4 py-2.5 rounded-lg bg-white border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition-colors duration-200 flex items-center shadow-sm">
                <i class="fas fa-list text-blue-500 mr-2"></i> All Internships
              </Link>
              
              <Link :href="route('internship-programs.assign-page')" 
                    class="text-sm px-4 py-2.5 rounded-lg bg-indigo-500 text-white font-semibold hover:bg-indigo-600 transition-colors duration-200 flex items-center shadow-md hover:shadow-lg">
                <i class="fas fa-user-plus text-white mr-2"></i> Assign Graduates
              </Link>
              <Link :href="route('internship-programs.archivedlist', { status: 'inactive' })" 
                    class="text-sm px-4 py-2.5 rounded-lg bg-white border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition-colors duration-200 flex items-center shadow-sm">
                <i class="fas fa-archive text-gray-500 mr-2"></i> View Archived
              </Link>
            </div>
          </div>

          <div class="p-6 border-b border-gray-100 bg-gray-50">
            <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center">
                <i class="fas fa-search text-gray-400 mr-2"></i> Filter Programs
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Related Program</label>
                <select v-model="filters.program_id" class="w-full border-gray-300 rounded-lg text-sm shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500/50">
                  <option value="">All Academic Programs</option>
                  <option v-for="p in programs" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
              </div>
              
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Career Opportunity</label>
                <select v-model="filters.career_opportunity_id" class="w-full border-gray-300 rounded-lg text-sm shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500/50">
                  <option value="">All Career Opportunities</option>
                  <option v-for="c in careerOpportunities" :key="c.id" :value="c.id">{{ c.title }}</option>
                </select>
              </div>
              
              <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Skills (Comma Separated)</label>
                <input v-model="filters.skills" type="text" placeholder="e.g. Vue, Laravel, Python" class="w-full border-gray-300 rounded-lg text-sm shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500/50" />
              </div>
              
              <div class="pt-5 flex justify-end space-x-3">
                <SecondaryButton @click="() => { filters.program_id = ''; filters.career_opportunity_id = ''; filters.skills = ''; applyFilters(); }" 
                                 class="!py-2.5">
                  <i class="fas fa-times mr-1"></i> Clear
                </SecondaryButton>
                <PrimaryButton @click="applyFilters" 
                               class="!py-2.5">
                  <i class="fas fa-filter mr-1"></i> Apply Filter
                </PrimaryButton>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead>
                <tr class="bg-gray-100/70">
                  <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Title</th>
                  <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Program(s)</th>
                  <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Career Opportunity</th>
                  <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Skills</th>
                  <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-100">
                
                <tr v-for="ip in internshipPrograms" :key="ip.id" 
                    :class="{ 'bg-red-50/50 border-l-4 border-red-500/50 text-gray-500 italic': ip.deleted_at, 'hover:bg-gray-50 transition-colors duration-150': !ip.deleted_at }" 
                    class="transition-all duration-150">
                  
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ ip.title }}
                    <span v-if="ip.deleted_at" class="ml-2 text-xs font-semibold text-red-500 bg-red-100 px-2 py-0.5 rounded-full">Archived</span>
                  </td>
                  
                  <td class="px-6 py-4 text-sm">
                    <div class="flex flex-wrap gap-2">
                      <span v-for="(p, index) in ip.programs" :key="p.id" 
                            v-show="index < 2"
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 border border-blue-200">
                        {{ p.name }}
                      </span>
                      <span v-if="ip.programs.length > 2"
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-500 text-white">
                        +{{ ip.programs.length - 2 }} more
                      </span>
                    </div>
                  </td>
                  
                  <td class="px-6 py-4 text-sm">
                    <div class="flex flex-wrap gap-2">
                      <span v-for="(c, index) in ip.career_opportunities" :key="c.id" 
                            v-show="index < 2"
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-200">
                        {{ c.title }}
                      </span>
                      <span v-if="ip.career_opportunities.length > 2"
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-500 text-white">
                        +{{ ip.career_opportunities.length - 2 }} more
                      </span>
                    </div>
                  </td>
                  
                  <td class="px-6 py-4 text-sm">
                    <div class="flex flex-wrap gap-2">
                      <span v-for="(s, index) in ip.skills" :key="s.id" 
                            v-show="index < 2"
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700 border border-purple-200">
                        {{ s.name }}
                      </span>
                      <span v-if="ip.skills.length > 2"
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-500 text-white">
                        +{{ ip.skills.length - 2 }} more
                      </span>
                    </div>
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap text-right text-base font-medium space-x-3">
                    <Link :href="route('internship-programs.edit', ip.id)" 
                          title="Edit Program"
                          class="text-blue-500 hover:text-blue-700 transition-colors">
                      <i class="fas fa-edit"></i>
                    </Link>
                    
                    <button v-if="!ip.deleted_at" @click="() => confirmArchive(ip)" 
                            title="Archive Program"
                            class="text-red-500 hover:text-red-700 transition-colors">
                      <i class="fas fa-archive"></i>
                    </button>
                    
                    <button v-else @click="() => restoreProgram(ip.id)" 
                            title="Restore Program"
                            class="text-green-500 hover:text-green-700 transition-colors">
                      <i class="fas fa-undo-alt"></i>
                    </button>
                  </td>
                </tr>
                
                <tr v-if="internshipPrograms.length === 0">
                  <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center">
                      <i class="fas fa-folder-open text-gray-300 text-5xl mb-4"></i>
                      <p class="text-lg font-semibold">No internship programs match the current filters.</p>
                      <p class="text-sm text-gray-400 mt-1">Try clearing your filters or adding a new program.</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

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
              <span class="font-bold text-gray-900">"{{ programToArchive?.title }}"</span>?
            </p>
            <p class="text-xs text-gray-500 mt-2">
              This action will remove the program from active listings. You can restore it later if needed.
            </p>
          </div>
        </template>
        <template #footer>
          <div class="flex justify-end space-x-3">
            <SecondaryButton @click="cancelArchive">
              Cancel
            </SecondaryButton>
            <DangerButton @click="archiveConfirmed">
              Archive
            </DangerButton>
          </div>
        </template>
      </ConfirmationModal>
      
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