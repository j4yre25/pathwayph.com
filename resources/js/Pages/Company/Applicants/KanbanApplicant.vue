<script setup>
import draggable from 'vuedraggable'
const stages = ['Shortlisted', 'Interview', 'Offer', 'Hired', 'Rejected']
const applicantsByStage = ref({
  Shortlisted: [],
  Interview: [],
  Offer: [],
  Hired: [],
  Rejected: [],
})
// Populate applicantsByStage from props or API
function onMove({ from, to, item }) {
  // Update backend with new stage for applicant
}
</script>

<template>
  <div class="flex gap-4">
    <div v-for="stage in stages" :key="stage" class="flex-1 bg-gray-50 rounded p-2">
      <h3 class="font-bold mb-2">{{ stage }}</h3>
      <draggable
        :list="applicantsByStage[stage]"
        group="applicants"
        @end="onMove"
        item-key="id"
      >
        <template #item="{ element }">
          <div class="bg-white rounded shadow p-2 mb-2 flex items-center">
            <img :src="element.profile_picture || '/default-avatar.png'" class="w-8 h-8 rounded-full mr-2" />
            <span>{{ element.name }}</span>
            <span class="ml-auto text-xs font-bold px-2 py-1 rounded" :class="getStatusBadge(element.status)">
              {{ getStatusText(element.status) }}
            </span>
          </div>
        </template>
      </draggable>
    </div>
  </div>
</template>