<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import GraduateEdit from './GraduateEdit.vue';

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
          <!-- Graduate Table -->
          <table class="min-w-full table-auto border rounded-lg overflow-hidden">
            <thead class="bg-gray-100 text-sm text-left">
              <tr>
                <th class="p-3">Name</th>
                <th class="p-3">Program</th>
                <th class="p-3">Year Graduated</th>
                <th class="p-3">Current Job Title</th>
                <th class="p-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="grad in graduates.data" :key="grad.user_id" class="border-b hover:bg-gray-50 text-sm">
                <td class="p-3">
                  {{ grad.first_name }} {{ grad.middle_name }} {{ grad.last_name }}
                </td>
                <td class="p-3">{{ grad.program_name }}</td>
                <td class="p-3">{{ grad.year_graduated }}</td>
                <td class="p-3">{{ grad.current_job_title || 'N/A' }}</td>
                <td class="p-3 text-right space-x-2">
                  <button @click="editGraduate(grad)" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                    Edit
                  </button>
                  <button @click="confirmArchive(grad)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                    Archive
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          <!-- Pagination Controls -->
          <div class="mt-6 flex justify-center">
            <nav v-if="graduates.links && graduates.links.length > 3" class="inline-flex -space-x-px">
              <button
                v-for="(link, i) in graduates.links"
                :key="i"
                v-html="link.label"
                :disabled="!link.url"
                @click="$inertia.get(link.url)"
                :class="[
                  'px-3 py-1 border text-sm',
                  link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100',
                  i === 0 ? 'rounded-l' : '',
                  i === graduates.links.length - 1 ? 'rounded-r' : ''
                ]"
              ></button>
            </nav>
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
