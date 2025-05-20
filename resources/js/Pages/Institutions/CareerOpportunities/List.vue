<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
  opportunities: Array,
  programs: Array,
  status: String,
});

const selectedProgram = ref('');
const selectedStatus = ref(props.status || 'all');

const filteredOpportunities = computed(() => {
  return props.opportunities.filter((opp) => {
    const matchesProgram = !selectedProgram.value || String(opp.program_id) === String(selectedProgram.value);
    const isActive = !opp.deleted_at;
    const matchesStatus =
      selectedStatus.value === 'all' ||
      (selectedStatus.value === 'active' && isActive) ||
      (selectedStatus.value === 'inactive' && !isActive);
    return matchesProgram && matchesStatus;
  });
});

function applyFilter() {
  router.get(route('careeropportunities.list', { user: usePage().props.auth.user.id }), {
    status: selectedStatus.value,
    program_id: selectedProgram.value,
  });
}
</script>

<template>
  <AppLayout title="Career Opportunities List">
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Career Opportunities</h2>
    </template>

    <Container class="py-8 space-y-6">
      <!-- Filters -->
      <div class="flex gap-4 flex-wrap items-center bg-white p-4 rounded-md shadow-sm">
        <div>
          <label class="block text-sm font-medium text-gray-700">Filter by Program:</label>
          <select v-model="selectedProgram" class="mt-1 border-gray-300 rounded-md">
            <option value="">All Programs</option>
            <option v-for="program in programs" :key="program.id" :value="program.id">
              {{ program.program?.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Filter by Status:</label>
          <select v-model="selectedStatus" class="mt-1 border-gray-300 rounded-md">
            <option value="all">All</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <PrimaryButton @click="applyFilter">Apply Filter</PrimaryButton>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-700">
          <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
            <tr>
              <th class="px-6 py-4">Program</th>
              <th class="px-6 py-4">Career Title</th>
              <th class="px-6 py-4">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="opp in filteredOpportunities"
              :key="opp.id"
              class="border-t hover:bg-gray-50 transition"
            >
              <td class="px-6 py-4">{{ opp.program?.name }}</td>
              <td class="px-6 py-4">{{ opp.career_opportunity?.title }}</td>
              <td class="px-6 py-4 font-semibold" :class="opp.deleted_at ? 'text-red-600' : 'text-green-600'">
                {{ opp.deleted_at ? 'Inactive' : 'Active' }}
              </td>
            </tr>

            <tr v-if="filteredOpportunities.length === 0">
              <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                No career opportunities found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </Container>
  </AppLayout>
</template>
