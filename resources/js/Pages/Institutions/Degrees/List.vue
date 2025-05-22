<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();

const props = defineProps({
  degrees: Array
});

const selectedStatus = ref('all');
const appliedStatus = ref('all');

const filteredDegrees = computed(() => {
  if (appliedStatus.value === 'all') return props.degrees;
  return props.degrees.filter(degree =>
    appliedStatus.value === 'active' ? !degree.deleted_at : degree.deleted_at
  );
});

function applyFilter() {
  appliedStatus.value = selectedStatus.value;
}
</script>

<template>
  <AppLayout title="Manage Degrees">
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Manage Degrees</h2>
    </template>

    <Container class="py-6 space-y-6">
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
        <button @click="applyFilter" class="bg-indigo-600 text-white px-4 py-2 rounded-md">Apply Filter</button>
      </div>

      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-100 text-xs uppercase tracking-wider text-gray-600">
              <tr>
                <th class="px-6 py-4">Degree</th>
                <th class="px-6 py-4">Degree Code</th>
                <th class="px-6 py-4">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="degree in filteredDegrees"
                :key="degree.id"
                class="border-t hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4">{{ degree.degree?.type }}</td>
                <td class="px-6 py-4">{{ degree.degree_code }}</td>
                <td
                  class="px-6 py-4 font-semibold"
                  :class="degree.deleted_at ? 'text-red-600' : 'text-green-600'"
                >
                  {{ degree.deleted_at ? 'Inactive' : 'Active' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </Container>
  </AppLayout>
</template>
