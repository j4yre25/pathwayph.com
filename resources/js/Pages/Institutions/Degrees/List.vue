<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();

const props = defineProps({
  degrees: Array
});

const selectedStatus = ref('all');  // Filter for degree status (all, active, inactive)
const appliedStatus = ref('all');   // Track the applied filter status

// Compute the filtered degrees based on the selected status
const filteredDegrees = computed(() => {
  if (appliedStatus.value === 'all') return props.degrees;
  return props.degrees.filter(degree =>
    appliedStatus.value === 'active' ? !degree.deleted_at : degree.deleted_at
  );
});

// Apply the selected filter
function applyFilter() {
  appliedStatus.value = selectedStatus.value;
}
</script>

<template>
  <AppLayout title="Manage Degrees">
    <template #header>
      <h2 class="text-3xl font-semibold text-gray-900">List of Degrees</h2>
    </template>

    <Container class="py-8 space-y-6">
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

      <!-- Degrees Table -->
      <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
        <table class="min-w-full text-sm text-gray-700">
          <thead>
            <tr class="bg-gray-200 text-gray-600 uppercase text-xs font-medium">
              <th class="px-6 py-4 text-left">Degree</th>
              <th class="px-6 py-4 text-left">Status</th>
            </tr>
          </thead>
          <tbody class="text-gray-600">
            <tr
              v-for="degree in filteredDegrees"
              :key="degree.id"
              class="border-t hover:bg-gray-50 transition-all"
            >
              <td class="px-6 py-4">{{ degree.type }}</td>
              <td class="px-6 py-4 text-sm font-semibold" :class="degree.deleted_at ? 'text-red-500' : 'text-green-500'">
                {{ degree.deleted_at ? 'Inactive' : 'Active' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </Container>
  </AppLayout>
</template>
