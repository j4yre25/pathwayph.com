<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
  skills: Array,
  careerOpportunities: Array,
  selectedCareerOpportunity: String,
});

const userId = usePage().props.auth.user.id;
const selectedCareerOpportunity = ref(props.selectedCareerOpportunity || '');

const uniqueCareerOpportunities = computed(() => {
  const seenTitles = new Set();
  return (props.careerOpportunities || []).filter((opportunity) => {
    if (seenTitles.has(opportunity.title)) {
      return false;
    }
    seenTitles.add(opportunity.title);
    return true;
  });
});

const filteredSkills = computed(() => {
  if (!selectedCareerOpportunity.value) return props.skills;
  return props.skills.filter(
    (skill) =>
      String(skill.career_opportunity_id) === String(selectedCareerOpportunity.value)
  );
});

function applyFilter() {
  router.get(route('instiskills.list', { user: userId }), {
    career_opportunity_id: selectedCareerOpportunity.value,
    preserveScroll: true,
    preserveState: true,
  });
}

function clearFilter() {
  selectedCareerOpportunity.value = '';
  applyFilter();
}
</script>

<template>
  <AppLayout title="Skills List">
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Skills</h2>
    </template>

    <Container class="py-8 space-y-6">
      <!-- Filters -->
      <div class="flex gap-4 flex-wrap items-center bg-white p-4 rounded-md shadow-sm">
        <div>
          <label class="block text-sm font-medium text-gray-700">Filter by Career Opportunity:</label>
          <select v-model="selectedCareerOpportunity" class="mt-1 border-gray-300 rounded-md">
            <option value="">All Career Opportunities</option>
            <option
              v-for="opportunity in uniqueCareerOpportunities"
              :key="opportunity.id"
              :value="opportunity.id"
            >
              {{ opportunity.title }}
            </option>
          </select>
        </div>
        <PrimaryButton @click="applyFilter">Apply Filter</PrimaryButton>
        <PrimaryButton v-if="selectedCareerOpportunity" @click="clearFilter" class="bg-gray-300 text-gray-700">
          Clear Filter
        </PrimaryButton>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-700">
          <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
            <tr>
              <th class="px-6 py-4">Career Opportunity</th>
              <th class="px-6 py-4">Skill Name</th>
              <th class="px-6 py-4">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="skill in filteredSkills"
              :key="skill.id"
              class="border-t hover:bg-gray-50 transition"
            >
              <td class="px-6 py-4">{{ skill.career_opportunity?.title }}</td>
              <td class="px-6 py-4">{{ skill.skill?.name }}</td>
              <td class="px-6 py-4 font-semibold" :class="skill.deleted_at ? 'text-red-600' : 'text-green-600'">
                {{ skill.deleted_at ? 'Inactive' : 'Active' }}
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
    </Container>
  </AppLayout>
</template>