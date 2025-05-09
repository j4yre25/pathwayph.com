<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { router } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref, computed } from 'vue';

const page = usePage();

const props = defineProps({
  school_years: Array,
});

const showModal = ref(false);
const selectedStatus = ref('all');
const appliedStatus = ref('all');

const filteredSchoolYears = computed(() => {
  if (appliedStatus.value === 'all') return props.school_years;
  return props.school_years.filter(sy =>
    appliedStatus.value === 'active' ? !sy.deleted_at : sy.deleted_at
  );
});

function applyFilter() {
  appliedStatus.value = selectedStatus.value;
}
</script>

<template>
  <AppLayout title="Manage School Years">
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Manage School Years</h2>
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
        <PrimaryButton @click="applyFilter">
          Apply Filter
        </PrimaryButton>
      </div>

      <!-- Table Section -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-100 text-xs uppercase tracking-wider text-gray-600">
              <tr>
                <th class="px-6 py-4">School Year</th>
                <th class="px-6 py-4">Term</th>
                <th class="px-6 py-4">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="sy in filteredSchoolYears"
                :key="sy.id"
                class="border-t hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4">{{ sy.school_year_range }}</td>
                <td class="px-6 py-4">{{ sy.term }}</td>
                <td
                  class="px-6 py-4 font-semibold"
                  :class="sy.deleted_at ? 'text-red-600' : 'text-green-600'"
                >
                  {{ sy.deleted_at ? 'Inactive' : 'Active' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Confirmation Modal -->
      <ConfirmationModal :show="showModal" @close="showModal = false">
        <template #title>Confirm Deletion</template>
        <template #content>Are you sure you want to delete this item?</template>
        <template #footer>
          <PrimaryButton @click="showModal = false">Cancel</PrimaryButton>
          <DangerButton @click="handleDelete">Delete</DangerButton>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>
