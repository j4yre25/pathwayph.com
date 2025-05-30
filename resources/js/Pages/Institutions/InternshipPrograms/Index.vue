<script setup>
import { ref } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';

import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
  internshipPrograms: Array,
  programs: Array,
  careerOpportunities: Array,
});

const filters = ref({
  program_id: '',
  career_opportunity_id: '',
  skills: '',
});

const showConfirmModal = ref(false);
const programToArchive = ref(null);

function applyFilters() {
  router.get(route('internship-programs.index'), filters.value, { preserveState: true, preserveScroll: true });
}

function confirmArchive(ip) {
  programToArchive.value = ip;
  showConfirmModal.value = true;
}

function archiveConfirmed() {
  if (!programToArchive.value) return;
  router.delete(route('internship-programs.archive', { id: programToArchive.value.id }), {
    preserveScroll: true,
    onSuccess: () => {
      showConfirmModal.value = false;
      programToArchive.value = null;
    },
  });
}

function restoreProgram(id) {
  if (confirm('Restore this internship program?')) {
    router.post(route('internship-programs.restore', { id }), {}, { preserveScroll: true });
  }
}

function cancelArchive() {
  showConfirmModal.value = false;
  programToArchive.value = null;
}
</script>

<template>
  <AppLayout title="Manage Internship Programs">
    <template #header>
      <h2 class="text-3xl font-semibold text-gray-800 tracking-tight">Manage Internship Programs</h2>
    </template>

    <Container class="py-8 space-y-10">
      <!-- Action Buttons -->
      <div class="flex flex-wrap gap-3">
        <Link :href="route('internship-programs.create')">
          <PrimaryButton>Add Internship</PrimaryButton>
        </Link>
        <Link :href="route('internship-programs.list')">
          <PrimaryButton>All Internship</PrimaryButton>
        </Link>
        <Link :href="route('internship-programs.archivedlist', { status: 'inactive' })">
          <PrimaryButton>Archived Internship</PrimaryButton>
        </Link>
      </div>

      <!-- Filters Section -->
      <div class="bg-white p-6 rounded-xl shadow-md space-y-6">
        <h3 class="text-lg font-medium text-gray-700">Filter Internship Programs</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Program</label>
            <select v-model="filters.program_id" class="w-full border-gray-300 rounded-lg shadow-sm">
              <option value="">All Programs</option>
              <option v-for="p in programs" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Career Opportunity</label>
            <select v-model="filters.career_opportunity_id" class="w-full border-gray-300 rounded-lg shadow-sm">
              <option value="">All Career Opportunities</option>
              <option v-for="c in careerOpportunities" :key="c.id" :value="c.id">{{ c.title }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Skills</label>
            <input v-model="filters.skills" type="text" placeholder="e.g. Vue, Laravel" class="w-full border-gray-300 rounded-lg shadow-sm" />
          </div>
        </div>
        <div class="text-right">
          <PrimaryButton @click="applyFilters">Apply Filter</PrimaryButton>
        </div>
      </div>

      <!-- Internship Programs Table -->
      <div class="bg-white p-6 rounded-xl shadow-md space-y-4">
        <h3 class="text-lg font-medium text-gray-700">Internship Program List</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-100 text-xs uppercase text-gray-600 tracking-wide">
              <tr>
                <th class="px-6 py-4">Title</th>
                <th class="px-6 py-4">Program</th>
                <th class="px-6 py-4">Career Opportunity</th>
                <th class="px-6 py-4">Skills</th>
                <th class="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="ip in internshipPrograms" :key="ip.id" :class="{ 'bg-gray-50': ip.deleted_at }">
                <td class="px-6 py-4 whitespace-nowrap">{{ ip.title }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-for="(p, idx) in ip.programs" :key="p.id">
                    {{ p.name }}<span v-if="idx < ip.programs.length - 1">, </span>
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-for="(c, idx) in ip.career_opportunities" :key="c.id">
                    {{ c.title }}<span v-if="idx < ip.career_opportunities.length - 1">, </span>
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-for="(s, idx) in ip.skills" :key="s.id">
                    {{ s.name }}<span v-if="idx < ip.skills.length - 1">, </span>
                  </span>
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                  <Link :href="route('internship-programs.edit', ip.id)">
                    <PrimaryButton>Edit</PrimaryButton>
                  </Link>
                  <DangerButton v-if="!ip.deleted_at" @click="() => confirmArchive(ip)">Archive</DangerButton>
                  <PrimaryButton v-else @click="() => restoreProgram(ip.id)">Restore</PrimaryButton>
                </td>
              </tr>
              <tr v-if="internshipPrograms.length === 0">
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                  No internship programs found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Confirmation Modal -->
      <ConfirmationModal :show="showConfirmModal" @close="cancelArchive">
        <template #title>Are you sure?</template>
        <template #content>
          Are you sure you want to archive the internship program
          <strong>"{{ programToArchive?.title }}"</strong>?
        </template>
        <template #footer>
          <DangerButton @click="archiveConfirmed" class="mr-2">Archive</DangerButton>
          <SecondaryButton @click="cancelArchive">Cancel</SecondaryButton>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>
