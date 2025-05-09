<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
  programs: Array
});

const selectedStatus = ref('all');
const appliedStatus = ref('all');

const filteredPrograms = computed(() => {
  if (appliedStatus.value === 'all') return props.programs;
  return props.programs.filter(program =>
    appliedStatus.value === 'active' ? !program.deleted_at : program.deleted_at
  );
});

function applyFilter() {
  appliedStatus.value = selectedStatus.value;
}
</script>

<template>
  <AppLayout title="Manage Programs">
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Manage Programs</h2>
    </template>

    <Container class="py-6 space-y-6">
      <!-- Filter Section -->
      <div class="bg-white p-4 rounded-lg shadow-sm flex flex-wrap items-center gap-4">
        <label for="statusFilter" class="font-medium text-gray-700">Filter by Status:</label>
        <select
          id="statusFilter"
          v-model="selectedStatus"
          class="border border-gray-300 rounded-md px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"
        >
          <option value="all">All</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
        <button
          @click="applyFilter"
          class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm transition"
        >
          Apply Filter
        </button>
      </div>

      <!-- Table Section -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-100 text-xs uppercase tracking-wider text-gray-600">
              <tr>
                <th class="px-6 py-4">Program</th>
                <th class="px-6 py-4">Degree</th>
                <th class="px-6 py-4">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="prog in filteredPrograms"
                :key="prog.id"
                class="border-t hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4 whitespace-nowrap">{{ prog.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ prog.degree?.type }}</td>
                <td
                  class="px-6 py-4 font-semibold whitespace-nowrap"
                  :class="prog.deleted_at ? 'text-red-600' : 'text-green-600'"
                >
                  {{ prog.deleted_at ? 'Inactive' : 'Active' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </Container>
  </AppLayout>
</template>
