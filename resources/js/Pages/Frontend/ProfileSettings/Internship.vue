<script setup>
import { computed } from 'vue';

const props = defineProps({
  internships: Array,
  programs: Array,
  careerOpportunities: Array,
});
</script>

<template>
    <div class="py-8 space-y-8 max-w-4xl mx-auto">
      <h2 class="text-2xl font-bold text-gray-800 mb-6">Assigned Internships</h2>
      <div v-if="internships.length === 0" class="text-gray-500 text-center">
        No internships assigned yet.
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div
          v-for="internship in internships"
          :key="internship.id"
          class="bg-white rounded-xl shadow p-6 flex flex-col gap-3"
        >
          <div class="mb-2">
            <span class="block text-lg font-semibold text-blue-700">{{ internship.title }}</span>
          </div>
          <div>
            <span class="font-semibold text-gray-700">Programs:</span>
            <span v-if="internship.programs && internship.programs.length" class="text-gray-600">
              {{ internship.programs.map(p => p.name).join(', ') }}
            </span>
            <span v-else class="text-gray-400">None</span>
          </div>
          <div>
            <span class="font-semibold text-gray-700">Career Opportunities:</span>
            <span v-if="internship.career_opportunities && internship.career_opportunities.length" class="text-gray-600">
              {{ internship.career_opportunities.map(c => c.title).join(', ') }}
            </span>
            <span v-else class="text-gray-400">None</span>
          </div>
        </div>
      </div>
    </div>
</template>