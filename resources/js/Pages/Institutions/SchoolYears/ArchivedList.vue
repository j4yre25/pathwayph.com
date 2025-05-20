<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';

const props = defineProps({
  all_school_years: Array,
});

const showModal = ref(false);
const schoolYearToRestore = ref(null);

const restoreSchoolYear = () => {
  router.post(
    route('school-years.restore', {
      id: schoolYearToRestore.value.id,
    }),
    {},
    {
      onSuccess: () => {
        showModal.value = false;
      },
    }
  );
};

const confirmRestore = (school_years) => {
  schoolYearToRestore.value = school_years;
  showModal.value = true;
};
</script>

<template>
  <AppLayout title="Archived School Years">
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Archived School Years</h2>
    </template>

    <Container class="py-10 space-y-6">
      <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-700">
          <thead class="bg-gray-100 uppercase tracking-wider text-xs text-gray-600">
            <tr>
              <th class="px-6 py-4">School Year</th>
              <th class="px-6 py-4">Term</th>
              <th class="px-6 py-4">Status</th>
              <th class="px-6 py-4">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="sy in all_school_years"
              :key="sy.id"
              class="border-t hover:bg-gray-50 transition"
            >
              <td class="px-6 py-4">{{ sy.school_year?.school_year_range }}</td>
              <td class="px-6 py-4">{{ sy.term }}</td>
              <td class="px-6 py-4">
                <span class="text-red-600 font-semibold">Archived</span>
              </td>
              <td class="px-6 py-4">
                <DangerButton @click="confirmRestore(sy)">Restore</DangerButton>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Confirmation Modal -->
      <ConfirmationModal :show="showModal" @close="showModal = false">
        <template #title>Confirm Restore</template>

        <template #content>
          Are you sure you want to restore this school year?
        </template>

        <template #footer>
          <PrimaryButton @click="showModal = false" class="mr-2">Cancel</PrimaryButton>
          <DangerButton @click="restoreSchoolYear">Restore</DangerButton>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>
