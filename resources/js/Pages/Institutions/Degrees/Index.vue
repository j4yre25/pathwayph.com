<script setup>
import { ref } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const page = usePage();
const props = defineProps({ degrees: Array });

const degrees = page.props.degrees;

const selectedDegree = ref(null);
const open = ref(false);

const archiveDegree = () => {
  if (selectedDegree.value) {
    router.delete(route('degrees.delete', { id: selectedDegree.value.id }), {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ preserveScroll: true });
      },
    });
    open.value = false;
    selectedDegree.value = null;
  }
};

const confirmArchive = (degree) => {
  selectedDegree.value = degree;
  open.value = true;
};
</script>

<template>
  <AppLayout title="Degrees">
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Degrees</h2>
    </template>

    <Container class="py-6 space-y-6">
      <!-- Action Buttons -->
      <div class="flex flex-wrap gap-4">
        <Link :href="route('degrees.create', { user: page.props.auth.user.id })">
          <PrimaryButton>Add Degree</PrimaryButton>
        </Link>

        <Link :href="route('degrees.list', { user: page.props.auth.user.id })">
          <PrimaryButton>All Degrees</PrimaryButton>
        </Link>

        <Link
          v-if="page.props.roles.isInstitution"
          :href="route('degrees.archivedlist', { user: page.props.auth.user.id })"
        >
          <PrimaryButton>Archived Degrees</PrimaryButton>
        </Link>
      </div>

      <!-- Degrees Table -->
      <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs tracking-wider">
              <tr>
                <th class="px-6 py-4">Degree</th>
                <th class="px-6 py-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="degree in degrees"
                :key="degree.id"
                class="border-t hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4">{{ degree.type }}</td>
                <td class="px-6 py-4 flex gap-2">
                  <Link :href="route('degrees.edit', { id: degree.id })">
                    <PrimaryButton>Edit</PrimaryButton>
                  </Link>
                  <DangerButton @click="confirmArchive(degree)">
                    Archive
                  </DangerButton>
                </td>
              </tr>
              <tr v-if="degrees.length === 0">
                <td colspan="2" class="px-6 py-4 text-center text-gray-500">
                  No degrees found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Confirmation Modal -->
      <ConfirmationModal :show="open" @close="open = false">
        <template #title>Are you sure?</template>
        <template #content>
          Are you sure you want to archive the degree
          <strong>"{{ selectedDegree?.type }}"</strong>?
        </template>
        <template #footer>
          <DangerButton @click="archiveDegree" class="mr-2">Archive</DangerButton>
          <SecondaryButton @click="open = false">Cancel</SecondaryButton>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>
