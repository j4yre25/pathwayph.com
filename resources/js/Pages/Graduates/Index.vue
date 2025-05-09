<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import GraduateModal from './GraduateModal.vue';

const props = defineProps({
  graduates: Array,
  programs: Array, // Institution-specific programs
  years: Array, // Institution-specific school years
  instiUsers: Array, // Institution information (for School Graduated From dropdown)
});

const isModalOpen = ref(false);
const selectedGraduate = ref(null);

function openAddModal() {
  selectedGraduate.value = null;
  isModalOpen.value = true;
}

function editGraduate(grad) {
  selectedGraduate.value = { ...grad }
  isModalOpen.value = true;
}

function closeModal() {
  isModalOpen.value = false;
  selectedGraduate.value = null;
}

function archiveGraduate(id) {
  if (confirm('Are you sure you want to archive this graduate?')) {
    router.delete(route('graduates.destroy', { graduate: id }));
  }
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
          <div class="flex justify-between items-center mb-6">
            <div class="space-x-2">
              <button @click="$inertia.get(route('graduates.list'))" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                List of Graduates
              </button>
              <button @click="$inertia.get(route('graduates.archived'))" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                Archived Graduates
              </button>
              <button @click="$inertia.get(route('graduates.batch.page'))" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                Upload CSV
              </button>
            </div>
            <div>
              <button @click="openAddModal" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Add Graduate
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
              <tr v-for="grad in graduates" :key="grad.user_id" class="border-b hover:bg-gray-50 text-sm">
                <td class="p-3">{{ grad.full_name }}</td>
                <td class="p-3">{{ grad.program_name }}</td>
                <td class="p-3">{{ grad.year_graduated }}</td>
                <td class="p-3">{{ grad.current_job_title || 'N/A' }}</td>
                <td class="p-3 text-right space-x-2">
                  <button @click="editGraduate(grad)" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                    Edit
                  </button>
                  <button @click="archiveGraduate(grad.user_id)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                    Archive
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Graduate Modal -->
          <GraduateModal
            :show="isModalOpen"
            :graduate="selectedGraduate"
            :programs="programs"
            :years="years"
            :insti-users="instiUsers"
            @close="closeModal"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
