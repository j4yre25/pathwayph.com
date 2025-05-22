<script setup>
import { ref, computed } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';

import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const page = usePage();
const userId = page.props.auth.user.id;

const careerOpportunities = page.props.careerOpportunities;
const skills = ref([...page.props.skills]);
const selectedCareerOpportunity = ref(page.props.selectedCareerOpportunity ?? '');

// Confirmation modal state
const showConfirmModal = ref(false);
const skillToArchive = ref(null);

function confirmArchive(skill) {
  skillToArchive.value = skill;
  showConfirmModal.value = true;
}

function archiveConfirmed() {
  if (!skillToArchive.value) return;
  router.delete(route('instiskills.archive', skillToArchive.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      skills.value = skills.value.filter((skill) => skill.id !== skillToArchive.value.id);
      showConfirmModal.value = false;
      skillToArchive.value = null;
    },
  });
}

function cancelArchive() {
  showConfirmModal.value = false;
  skillToArchive.value = null;
}

// Filter skills by selected career opportunity
const filteredSkills = computed(() => {
  if (!selectedCareerOpportunity.value) return skills.value;
  return skills.value.filter(
    (item) => String(item.career_opportunity_id) === String(selectedCareerOpportunity.value)
  );
});

function applyFilter() {
  router.get(route('instiskills', { user: userId }), {
    preserveScroll: true,
    preserveState: true,
    career_opportunity_id: selectedCareerOpportunity.value,
  });
}
</script>

<template>
  <AppLayout title="Manage Skills">
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Manage Skills</h2>
    </template>

    <Container class="py-6 space-y-6">
      <!-- Action Buttons -->
      <div class="flex gap-4 flex-wrap">
        <Link :href="route('instiskills.create', { user: userId })">
          <PrimaryButton>Add Skill</PrimaryButton>
        </Link>
        <Link :href="route('instiskills.list', { user: userId })">
          <PrimaryButton>All Skills</PrimaryButton>
        </Link>
        <Link :href="route('instiskills.archivedlist', { user: userId })">
          <PrimaryButton>Archived Skills</PrimaryButton>
        </Link>
      </div>

      <!-- Filters -->
      <div class="flex gap-4 items-center bg-white p-4 rounded shadow-sm">
        <div>
          <label class="block text-sm font-medium text-gray-700">Filter by Career Opportunity</label>
          <select v-model="selectedCareerOpportunity" class="mt-1 border-gray-300 rounded-md">
            <option value="">All</option>
            <option v-for="item in careerOpportunities" :key="item.id" :value="item.id">
              {{ item.title }}
            </option>
          </select>
        </div>
        <PrimaryButton @click="applyFilter">Apply Filter</PrimaryButton>
      </div>

      <!-- Table -->
      <div class="bg-white shadow rounded overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-700">
          <thead class="bg-gray-100 text-xs uppercase text-gray-600 tracking-wide">
            <tr>
              <th class="px-6 py-4">Career Opportunity</th>
              <th class="px-6 py-4">Skill</th>
              <th class="px-6 py-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in filteredSkills" :key="item.id" class="border-t hover:bg-gray-50">
              <td class="px-6 py-4">{{ item.career_opportunity?.title }}</td>
              <td class="px-6 py-4">{{ item.skill?.name }}</td>
              <td class="px-6 py-4 text-right space-x-2">
                <Link :href="route('instiskills.edit', item.id)">
                  <PrimaryButton>Edit</PrimaryButton>
                </Link>
                <DangerButton @click="() => confirmArchive(item)">Archive</DangerButton>
              </td>
            </tr>
            <tr v-if="filteredSkills.length === 0">
              <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                No skills found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Confirmation Modal -->
      <ConfirmationModal :show="showConfirmModal" @close="cancelArchive">
        <template #title>Are you sure?</template>
        <template #content>
          Are you sure you want to archive the skill
          <strong>"{{ skillToArchive?.skill?.name }}"</strong>
          for career opportunity
          <strong>"{{ skillToArchive?.career_opportunity?.title }}"</strong>?
        </template>
        <template #footer>
          <DangerButton @click="archiveConfirmed" class="mr-2">Archive</DangerButton>
          <SecondaryButton @click="cancelArchive">Cancel</SecondaryButton>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>
