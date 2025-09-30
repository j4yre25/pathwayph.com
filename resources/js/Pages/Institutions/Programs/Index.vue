<script setup>
import { ref, computed } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import ProgramEditModal from './ProgramEditModal.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const page = usePage();
const userId = page.props.auth?.user?.id


const selectedProgram = ref(null);
const open = ref(false);
const showEditModal = ref(false);

// Stats for display
const totalPrograms = computed(() => page.props.programs.length);
const uniqueDegrees = computed(() => {
  const degrees = new Set(page.props.programs.map(p => p.program?.degree?.type).filter(Boolean));
  return degrees.size;
});
const uniqueProgramCodes = computed(() => {
  const codes = new Set(page.props.programs.map(p => p.program_code).filter(Boolean));
  return codes.size;
});

const archiveProgram = () => {
  if (selectedProgram.value) {
    router.delete(route('programs.delete', { id: selectedProgram.value.id }), {
      preserveScroll: true,
      onSuccess: () => {
        programs.value = programs.value.filter(p => p.id !== selectedProgram.value.id);
        open.value = false;
        selectedProgram.value = null;
      },
    });
  }
};

const confirmArchive = (program) => {
  selectedProgram.value = program;
  open.value = true;
};

const openEditModal = (program) => {
  selectedProgram.value = program;
  showEditModal.value = true;
};

const closeEditModal = () => {
  showEditModal.value = false;
  selectedProgram.value = null;
};

const handleProgramUpdated = () => {
  // Refresh the programs list
  router.reload({ only: ['programs'] });
};
</script>

<template>
  <AppLayout title="Programs">
    <template #header>
      <div class="flex items-center">
        <i class="fas fa-graduation-cap text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Programs</h2>
      </div>
    </template>

    <Container class="py-8">

      <!-- Navigation Menu -->
      <div class="mb-8 max-w-3xl mx-auto bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <!-- Section Header -->
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-800 text-center">Data Entries</h3>
        </div>

        <!-- Buttons -->
        <div class="flex flex-wrap gap-3 p-4 justify-center">

          <Link :href="route('school-years', { user: userId })"
            class="inline-flex items-center px-4 py-2 rounded-lg shadow-sm bg-white border border-gray-200 text-gray-700 hover:bg-green-50 hover:border-green-400 hover:text-green-600 transition-all duration-200">
          <i class="fas fa-calendar-alt mr-2 text-blue-500"></i> School Years
          </Link>

          <Link :href="route('degrees', { user: userId })"
            class="inline-flex items-center px-4 py-2 rounded-lg shadow-sm bg-white border border-gray-200 text-gray-700 hover:bg-green-50 hover:border-green-400 hover:text-green-600 transition-all duration-200">
          <i class="fas fa-graduation-cap mr-2 text-green-500"></i> Degrees
          </Link>

          <Link :href="route('careeropportunities', { user: userId })"
            class="inline-flex items-center px-4 py-2 rounded-lg shadow-sm bg-white border border-gray-200 text-gray-700 hover:bg-purple-50 hover:border-purple-400 hover:text-purple-600 transition-all duration-200">
          <i class="fas fa-briefcase mr-2 text-purple-500"></i> Career Opportunities
          </Link>

          <Link :href="route('instiskills', { user: userId })"
            class="inline-flex items-center px-4 py-2 rounded-lg shadow-sm bg-white border border-gray-200 text-gray-700 hover:bg-pink-50 hover:border-pink-400 hover:text-pink-600 transition-all duration-200">
          <i class="fas fa-cogs mr-2 text-pink-500"></i> Skills
          </Link>
        </div>
      </div>

      <!-- Stats Summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">Total Programs</h3>
              <p class="text-3xl font-bold text-blue-600">
                {{ totalPrograms }}
              </p>
            </div>
            <div class="bg-blue-100 rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-book text-blue-500"></i>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">Degrees</h3>
              <p class="text-3xl font-bold text-green-600">
                {{ uniqueDegrees }}
              </p>
            </div>
            <div class="bg-green-100 rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-award text-green-500"></i>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">Program Codes</h3>
              <p class="text-3xl font-bold text-purple-600">
                {{ uniqueProgramCodes }}
              </p>
            </div>
            <div class="bg-purple-100 rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-code text-purple-500"></i>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Main Content -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
        <!-- Action Buttons -->
        <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-200">
          <div class="flex items-center">
            <h3 class="text-lg font-semibold text-gray-800">Programs List</h3>
            <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ totalPrograms }} total</span>
          </div>
          <div class="flex w-full sm:w-auto space-x-3 mt-3 sm:mt-0">
            <Link :href="route('programs.create', { user: page.props.auth.user.id })" 
                  class="text-sm px-4 py-2 rounded-md bg-blue-500 text-white hover:bg-blue-600 transition-colors duration-200 flex items-center">
              <i class="fas fa-plus-circle mr-2"></i> Add Program
            </Link>
            <Link :href="route('programs.list', { user: page.props.auth.user.id })" 
                  class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
              <i class="fas fa-list text-blue-500 mr-2"></i> All Programs
            </Link>
            <Link v-if="page.props.roles.isInstitution"
                  :href="route('programs.archivedlist', { user: page.props.auth.user.id })" 
                  class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
              <i class="fas fa-archive text-gray-500 mr-2"></i> Archived
            </Link>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
              <tr>
                <th class="px-6 py-4">Program</th>
                <th class="px-6 py-4">Degree</th>
                <th class="px-6 py-4">Program Code</th>
                <th class="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr
                v-for="prog in page.props.programs"
                :key="prog.id"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
                      <i class="fas fa-book-open"></i>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ prog.program?.name }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                    {{ prog.program?.degree?.type || 'N/A' }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span class="text-sm text-gray-700 font-mono">{{ prog.program_code || 'N/A' }}</span>
                </td>
                <td class="px-6 py-4 text-right">
                  <button @click="openEditModal(prog)" class="text-blue-500 hover:text-blue-700 mr-3">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button @click="confirmArchive(prog)" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-archive"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="page.props.programs.length === 0">
                <td colspan="4" class="py-12 text-center">
                  <i class="fas fa-book text-gray-300 text-5xl mb-4"></i>
                  <h3 class="text-lg font-medium text-gray-700">No programs found</h3>
                  <p class="text-gray-500 mt-1">Add programs to see them listed here</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Confirmation Modal -->
      <ConfirmationModal :show="open" @close="open = false">
        <template #title>
          <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mr-4">
              <i class="fas fa-exclamation-triangle text-red-500"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800">Confirm Archive</h3>
          </div>
        </template>
        <template #content>
          <p class="text-gray-600 mb-2">Are you sure you want to archive the program:</p>
          <p class="font-medium text-gray-800">"{{ selectedProgram?.program?.name }}"?</p>
          <p class="text-sm text-gray-500 mt-2">This action can be reversed later.</p>
        </template>
        <template #footer>
          <div class="flex justify-end space-x-3">
            <button @click="open = false" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm transition-colors duration-200">
              Cancel
            </button>
            <button @click="archiveProgram" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 text-sm transition-colors duration-200">
              Archive
            </button>
          </div>
        </template>
      </ConfirmationModal>

      <!-- Edit Program Modal -->
      <ProgramEditModal 
        :show="showEditModal" 
        :institution-program="selectedProgram" 
        :degrees="page.props.degrees || []" 
        @close="closeEditModal"
        @updated="handleProgramUpdated"
      />
    </Container>
  </AppLayout>
</template>
