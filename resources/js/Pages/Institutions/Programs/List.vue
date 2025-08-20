<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { ref, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
  programs: Array
});

const selectedStatus = ref('all');

const filteredPrograms = computed(() => {
  if (selectedStatus.value === 'all') return props.programs;
  return props.programs.filter(program =>
    selectedStatus.value === 'active' ? !program.deleted_at : program.deleted_at
  );
});

// Watch for changes to apply filters immediately
watch(selectedStatus, () => {
  // No need for explicit apply function
});
</script>

<template>
  <AppLayout title="Manage Programs">
    <Container class="py-6 space-y-6">
      <!-- Header Section -->
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
          <i class="fas fa-book text-blue-500 text-xl mr-2"></i>
          <h1 class="text-2xl font-bold text-gray-800">Manage Programs</h1>
        </div>

      </div>
      <p class="text-sm text-gray-500 mb-6">View and manage all programs in the system.</p>

      <!-- Filter Section -->
      <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 flex flex-wrap items-center gap-4">
        <label for="statusFilter" class="font-medium text-gray-700">Filter by Status:</label>
        <select
          id="statusFilter"
          v-model="selectedStatus"
          class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
        >
          <option value="all">All</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>

      <!-- Table Section -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-600">
              <tr>
                <th class="px-6 py-4">Program</th>
                <th class="px-6 py-4">Degree</th>
                <th class="px-6 py-4">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr
                v-for="prog in filteredPrograms"
                :key="prog.id"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4 whitespace-nowrap">{{ prog.program?.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ prog.program?.degree?.type }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    class="px-2 py-1 text-xs font-medium rounded-full" 
                    :class="prog.deleted_at ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'"
                  >
                    {{ prog.deleted_at ? 'Inactive' : 'Active' }}
                  </span>
                </td>
              </tr>
              <tr v-if="filteredPrograms.length === 0">
                <td colspan="3" class="px-6 py-4 text-center text-gray-500">No programs found</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </Container>
  </AppLayout>
</template>
