<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { router } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';

const props = defineProps({
    all_degrees: Array,
});

const showModal = ref(false);
const degreeToRestore = ref(null);

const restoreDegree = () => {
  router.post(
    route('degrees.restore', { id: degreeToRestore.value.id }),
    {},
    {
      onSuccess: () => {
        showModal.value = false;
      },
    }
  );
};

const confirmRestore = (degree) => {
  degreeToRestore.value = degree;
  showModal.value = true;
};
</script>

<template>
  <AppLayout title="Archived Degrees">
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Archived Degrees</h2>
    </template>

    <Container class="py-10 space-y-6">
      <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-700">
          <thead class="bg-gray-100 uppercase tracking-wider text-xs text-gray-600">
            <tr>
              <th class="px-6 py-4">Degree</th>
              <th class="px-6 py-4">Degree Code</th>
              <th class="px-6 py-4">Status</th>
              <th class="px-6 py-4">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="degree in all_degrees"
              :key="degree.id"
              class="border-t hover:bg-gray-50 transition"
            >
              <td class="px-6 py-4">{{ degree.degree?.type }}</td>
              <td class="px-6 py-4">{{ degree.degree_code }}</td>
              <td class="px-6 py-4">
                <span class="text-red-600 font-semibold">Archived</span>
              </td>
              <td class="px-6 py-4">
                <DangerButton @click="confirmRestore(degree)">Restore</DangerButton>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <ConfirmationModal :show="showModal" @close="showModal = false">
        <template #title>Confirm Restore</template>
        <template #content>
          Are you sure you want to restore this degree?
        </template>
        <template #footer>
          <PrimaryButton @click="showModal = false" class="mr-2">Cancel</PrimaryButton>
          <DangerButton @click="restoreDegree">Restore</DangerButton>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>
