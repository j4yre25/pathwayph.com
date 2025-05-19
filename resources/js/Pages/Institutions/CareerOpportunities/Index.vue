<script setup>
import { ref, computed } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const page = usePage();
const userId = page.props.auth.user.id;

const opportunities = page.props.opportunities ?? [];
const programs = page.props.programs ?? [];

const selectedProgram = ref('');
const showConfirmModal = ref(false);
const opportunityToArchive = ref(null);

const filteredOpportunities = computed(() => {
  return opportunities.filter((opp) => {
    return !selectedProgram.value || String(opp.program_id) === String(selectedProgram.value);
  });
});

function applyFilter() {
  router.get(route('careeropportunities.index', { user: userId }), {
    program_id: selectedProgram.value,
    preserveState: true,
    preserveScroll: true,
  });
}

function confirmArchive(opp) {
  opportunityToArchive.value = opp;
  showConfirmModal.value = true;
}

function archiveConfirmed() {
  router.delete(route('careeropportunities.delete', opportunityToArchive.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showConfirmModal.value = false;
      opportunityToArchive.value = null;
      // No flashBanner logic here; rely on global flash banner
    }
  });
}

function cancelArchive() {
  showConfirmModal.value = false;
  opportunityToArchive.value = null;
}
</script>

<template>
  <AppLayout title="Career Opportunities">
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Career Opportunities</h2>
    </template>

    <Container class="py-6 space-y-6">
      <!-- Action Buttons -->
      <div class="flex gap-4 flex-wrap">
        <Link :href="route('careeropportunities.create', { user: userId })">
          <PrimaryButton>Add Career Opportunity</PrimaryButton>
        </Link>
        <Link :href="route('careeropportunities.list', { user: userId })">
          <PrimaryButton>All Career Opportunities</PrimaryButton>
        </Link>
        <Link :href="route('careeropportunities.archivedlist', { user: userId })">
          <PrimaryButton>Archived Career Opportunities</PrimaryButton>
        </Link>
      </div>

      <!-- Filters -->
      <div class="flex items-center gap-4 bg-white p-4 rounded shadow-sm">
        <div>
          <label class="block text-sm font-medium text-gray-700">Filter by Program</label>
          <select v-model="selectedProgram" class="mt-1 border-gray-300 rounded-md">
            <option value="">All Programs</option>
            <option v-for="program in programs" :key="program.id" :value="program.id">
              {{ program.program?.name }}
            </option>
          </select>
        </div>
        <PrimaryButton @click="applyFilter">Apply</PrimaryButton>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-700">
          <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
            <tr>
              <th class="px-6 py-4">Program</th>
              <th class="px-6 py-4">Career Title</th>
              <th class="px-6 py-4 text-right">Actions</th>
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
              <td class="px-6 py-4 text-right space-x-2">
                <Link :href="route('careeropportunities.edit', opp.id)">
                  <PrimaryButton>Edit</PrimaryButton>
                </Link>
                <DangerButton @click="() => confirmArchive(opp)">Archive</DangerButton>
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

      <!-- Confirmation Modal (same style as Programs) -->
      <ConfirmationModal :show="showConfirmModal" @close="cancelArchive">
        <template #title>Are you sure?</template>
        <template #content>
          Are you sure you want to archive the career opportunity
          <strong>"{{ opportunityToArchive?.career_opportunity?.title }}"</strong>?
        </template>
        <template #footer>
          <DangerButton @click="archiveConfirmed" class="mr-2">Archive</DangerButton>
          <SecondaryButton @click="cancelArchive">Cancel</SecondaryButton>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>
