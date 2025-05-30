<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
  internshipPrograms: Array,
  programs: Array,
  careerOpportunities: Array,
  selectedProgram: String,
  selectedCareerOpportunity: String,
});

const selectedProgram = ref(props.selectedProgram || '');
const selectedCareerOpportunity = ref(props.selectedCareerOpportunity || '');
const skillFilter = ref('');

const filteredInternships = computed(() => {
  return props.internshipPrograms.filter(ip => {
    const matchesProgram =
      !selectedProgram.value ||
      ip.programs.some(p => String(p.id) === String(selectedProgram.value));
    const matchesCareer =
      !selectedCareerOpportunity.value ||
      ip.career_opportunities.some(c => String(c.id) === String(selectedCareerOpportunity.value));
    const matchesSkill =
      !skillFilter.value ||
      ip.skills.some(s => s.name.toLowerCase().includes(skillFilter.value.toLowerCase()));
    return matchesProgram && matchesCareer && matchesSkill;
  });
});

function applyFilter() {
  router.get(route('internship-programs.list'), {
    program_id: selectedProgram.value,
    career_opportunity_id: selectedCareerOpportunity.value,
    skills: skillFilter.value,
    preserveScroll: true,
    preserveState: true,
  });
}

function clearFilters() {
  selectedProgram.value = '';
  selectedCareerOpportunity.value = '';
  skillFilter.value = '';
  applyFilter();
}
</script>

<template>
  <AppLayout title="Internship Programs List">
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Internship Programs</h2>
    </template>

    <Container class="py-8 space-y-6">
      <!-- Filters -->
      <div class="flex gap-4 flex-wrap items-center bg-white p-4 rounded-md shadow-sm">
        <div>
          <label class="block text-sm font-medium text-gray-700">Filter by Program:</label>
          <select v-model="selectedProgram" class="mt-1 border-gray-300 rounded-md">
            <option value="">All Programs</option>
            <option v-for="program in programs" :key="program.id" :value="program.id">
              {{ program.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Filter by Career Opportunity:</label>
          <select v-model="selectedCareerOpportunity" class="mt-1 border-gray-300 rounded-md">
            <option value="">All Career Opportunities</option>
            <option v-for="c in careerOpportunities" :key="c.id" :value="c.id">
              {{ c.title }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Filter by Skill:</label>
          <input v-model="skillFilter" type="text" class="mt-1 border-gray-300 rounded-md" placeholder="Skill name..." />
        </div>
        <PrimaryButton @click="applyFilter">Apply Filter</PrimaryButton>
        <PrimaryButton v-if="selectedProgram || selectedCareerOpportunity || skillFilter" @click="clearFilters" class="bg-gray-300 text-gray-700">
          Clear Filters
        </PrimaryButton>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-700">
          <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
            <tr>
              <th class="px-6 py-4">Title</th>
              <th class="px-6 py-4">Programs</th>
              <th class="px-6 py-4">Career Opportunities</th>
              <th class="px-6 py-4">Skills</th>
              <th class="px-6 py-4">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="ip in filteredInternships"
              :key="ip.id"
              class="border-t hover:bg-gray-50 transition"
            >
              <td class="px-6 py-4">{{ ip.title }}</td>
              <td class="px-6 py-4">
                <span v-for="(p, idx) in ip.programs" :key="p.id">
                  {{ p.name }}<span v-if="idx < ip.programs.length - 1">, </span>
                </span>
              </td>
              <td class="px-6 py-4">
                <span v-for="(c, idx) in ip.career_opportunities" :key="c.id">
                  {{ c.title }}<span v-if="idx < ip.career_opportunities.length - 1">, </span>
                </span>
              </td>
              <td class="px-6 py-4">
                <span v-for="(s, idx) in ip.skills" :key="s.id">
                  {{ s.name }}<span v-if="idx < ip.skills.length - 1">, </span>
                </span>
              </td>
              <td class="px-6 py-4 font-semibold" :class="ip.deleted_at ? 'text-red-600' : 'text-green-600'">
                {{ ip.deleted_at ? 'Inactive' : 'Active' }}
              </td>
            </tr>
            <tr v-if="filteredInternships.length === 0">
              <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                No internship programs found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </Container>
  </AppLayout>
</template>