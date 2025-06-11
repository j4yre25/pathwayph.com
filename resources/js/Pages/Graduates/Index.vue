<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import GraduateEdit from './GraduateEdit.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  graduates: Array,
  programs: Array, // Institution-specific programs
  years: Array, // Institution-specific school years
  instiUsers: Array, // Institution information (for School Graduated From dropdown)
});

const isModalOpen = ref(false);
const selectedGraduate = ref(null);

const showConfirmModal = ref(false)
const graduateToArchive = ref(null)
const flashBanner = ref('')

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
          <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
            <h3 class="text-gray-600 text-sm font-medium mb-2">Total Graduates</h3>
            <p class="text-3xl font-bold text-blue-600">
              {{ graduates.total || 0 }}
            </p>
          </div>
          <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-green-500">
            <h3 class="text-gray-600 text-sm font-medium mb-2">Active Graduates</h3>
            <p class="text-3xl font-bold text-green-600">
              {{ graduates.data.length || 0 }}
            </p>
          </div>
          <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-indigo-500">
            <h3 class="text-gray-600 text-sm font-medium mb-2">Programs</h3>
            <p class="text-3xl font-bold text-indigo-600">
              {{ programs.length || 0 }}
            </p>
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
                <i class="fas fa-plus mr-2"></i> Add Graduate
              </button>
              <button @click="$inertia.get(route('graduates.batch.page'))" 
                      class="text-sm px-4 py-2 rounded-md bg-indigo-500 text-white hover:bg-indigo-600 transition-colors duration-200 flex items-center">
                <i class="fas fa-upload mr-2"></i> Batch Upload
              </button>
            </div>
          </div>
          
          <!-- Graduate Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
              <thead class="bg-gray-50 text-xs uppercase text-gray-500 tracking-wider">
                <tr>
                  <th class="px-6 py-3 text-left">Name</th>
                  <th class="px-6 py-3 text-left">Program</th>
                  <th class="px-6 py-3 text-left">Year Graduated</th>
                  <th class="px-6 py-3 text-left">Current Job Title</th>
                  <th class="px-6 py-3 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="grad in graduates.data" :key="grad.user_id" class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
                        <i class="fas fa-user"></i>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ grad.first_name }} {{ grad.middle_name }} {{ grad.last_name }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ grad.program_name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ grad.year_graduated }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    <span v-if="grad.current_job_title" class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                      {{ grad.current_job_title }}
                    </span>
                    <span v-else class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">N/A</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button @click="editGraduate(grad)" class="text-blue-500 hover:text-blue-700 mr-3">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button @click="confirmArchive(grad)" class="text-red-500 hover:text-red-700">
                      <i class="fas fa-archive"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Empty State -->
          <div v-if="graduates.data.length === 0" class="py-12 text-center">
            <i class="fas fa-user-graduate text-gray-300 text-5xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-700">No graduates found</h3>
            <p class="text-gray-500 mt-1">Add graduates to see them listed here</p>
          </div>
          
          <!-- Pagination Controls -->
          <div class="px-6 py-4 border-t border-gray-200">
            <nav v-if="graduates.links && graduates.links.length > 3" class="flex justify-center">
              <ul class="flex items-center space-x-1">
                <li v-for="(link, i) in graduates.links" :key="i">
                  <button
                    v-html="link.label"
                    :disabled="!link.url"
                    @click="$inertia.get(link.url)"
                    :class="[
                      'px-3 py-1 rounded-md text-sm',
                      link.active ? 'bg-blue-500 text-white' : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300',
                      !link.url && 'opacity-50 cursor-not-allowed'
                    ]"
                  ></button>
                </li>
              </ul>
            </nav>
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
