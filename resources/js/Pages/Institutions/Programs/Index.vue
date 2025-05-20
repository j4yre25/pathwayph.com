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
const programs = ref([...page.props.programs]);

const selectedProgram = ref(null);
const open = ref(false);

const archiveProgram = () => {
  if (selectedProgram.value) {
    router.delete(route('programs.delete', { id: selectedProgram.value.id }), {
      preserveScroll: true,
      onSuccess: () => {
        programs.value = programs.value.filter(p => p.id !== selectedProgram.value.id);
        open.value = false;
        selectedProgram.value = null;
      },
    });
  }
};

const confirmArchive = (program) => {
  selectedProgram.value = program;
  open.value = true;
};
</script>

<template>
  <AppLayout title="Programs">
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Programs</h2>
    </template>

    <Container class="py-6 space-y-6">
      <div class="flex flex-wrap gap-4">
        <Link :href="route('programs.create', { user: page.props.auth.user.id })">
          <PrimaryButton>Add Program</PrimaryButton>
        </Link>
        <Link :href="route('programs.list', { user: page.props.auth.user.id })">
          <PrimaryButton>All Programs</PrimaryButton>
        </Link>
        <Link
          v-if="page.props.roles.isInstitution"
          :href="route('programs.archivedlist', { user: page.props.auth.user.id })"
        >
          <PrimaryButton>Archived Programs</PrimaryButton>
        </Link>
      </div>

      <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs tracking-wider">
              <tr>
                <th class="px-6 py-4">Program</th>
                <th class="px-6 py-4">Degree</th>
                <th class="px-6 py-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="prog in programs"
                :key="prog.id"
                class="border-t hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4">{{ prog.program?.name }}</td>
                <td class="px-6 py-4">{{ prog.program?.degree?.type }}</td>
                <td class="px-6 py-4 flex gap-2">
                  <Link :href="route('programs.edit', { id: prog.id })">
                    <PrimaryButton>Edit</PrimaryButton>
                  </Link>
                  <DangerButton @click="confirmArchive(prog)">
                    Archive
                  </DangerButton>
                </td>
              </tr>
              <tr v-if="programs.length === 0">
                <td colspan="3" class="px-6 py-4 text-center text-gray-500">No programs found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <ConfirmationModal :show="open" @close="open = false">
        <template #title>Are you sure?</template>
        <template #content>
          Are you sure you want to archive the program
          <strong>"{{ selectedProgram?.program?.name }}"</strong>?
        </template>
        <template #footer>
          <DangerButton @click="archiveProgram" class="mr-2">Archive</DangerButton>
          <SecondaryButton @click="open = false">Cancel</SecondaryButton>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>
