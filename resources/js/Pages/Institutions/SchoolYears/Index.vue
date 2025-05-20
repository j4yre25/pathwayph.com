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
const school_years = page.props.school_years;

const selectedSchoolYear = ref(null);
const open = ref(false);

const archiveSchoolYear = () => {
  if (selectedSchoolYear.value) {
    router.delete(route('school-years.delete', { id: selectedSchoolYear.value.id }), {
      onSuccess: () => {
        router.reload({ preserveScroll: true });
      },
    });
    open.value = false;
    selectedSchoolYear.value = null;
  }
};

const confirmArchive = (schoolYear) => {
  selectedSchoolYear.value = schoolYear;
  open.value = true;
};
</script>

<template>
  <AppLayout title="School Years">
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">School Years</h2>
    </template>

    <Container class="py-6 space-y-6">
      <!-- Action buttons -->
      <div class="flex flex-wrap gap-4">
        <Link :href="route('school-years.create', { user: page.props.auth.user.id })">
          <PrimaryButton>Add School Year</PrimaryButton>
        </Link>

        <Link :href="route('school-years.list', { user: page.props.auth.user.id })">
          <PrimaryButton>All School Years</PrimaryButton>
        </Link>

        <Link
          v-if="page.props.roles.isInstitution"
          :href="route('school-years.archivedlist', { user: page.props.auth.user.id })"
        >
          <PrimaryButton>Archived School Years</PrimaryButton>
        </Link>
      </div>

      <!-- School Years Table -->
      <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs tracking-wider">
              <tr>
                <th class="px-6 py-4">School Year</th>
                <th class="px-6 py-4">Term</th>
                <th class="px-6 py-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="sy in school_years"
                :key="sy.id"
                class="border-t hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4">{{ sy.school_year?.school_year_range }}</td>
                <td class="px-6 py-4">{{ sy.term }}</td>
                <td class="px-6 py-4 flex gap-2">
                  <Link :href="route('school-years.edit', { id: sy.id })">
                    <PrimaryButton>Edit</PrimaryButton>
                  </Link>
                  <DangerButton @click="confirmArchive(sy)">
                    Archive
                  </DangerButton>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Confirmation Modal -->
      <ConfirmationModal :show="open" @close="open = false">
        <template #title>
          Are you sure?
        </template>

        <template #content>
          Are you sure you want to archive the school year
          <strong>"{{ selectedSchoolYear?.school_year?.school_year_range }}"</strong>?
        </template>

        <template #footer>
          <DangerButton @click="archiveSchoolYear" class="mr-2">
            Archive
          </DangerButton>
          <SecondaryButton @click="open = false">Cancel</SecondaryButton>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>
