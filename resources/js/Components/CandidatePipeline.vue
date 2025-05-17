<script setup>
defineProps({
  stage: {
    type: String,
    required: true,
  },
})

const stages = ['Applying', 'Screening', 'Interview', 'Test', 'Onboarding']
</script>

<template>
  <div class="flex items-center space-x-2 mt-4">
    <template v-for="(step, index) in stages" :key="step">
      <div class="flex items-center">
        <div
          :class="[
            'w-24 h-1 rounded',
            index <= stages.indexOf(stage) ? 'bg-blue-500' : 'bg-gray-300'
          ]"
        ></div>
        <span
          class="text-xs text-gray-600 ml-1"
          :class="index === stages.indexOf(stage) ? 'font-semibold text-blue-600' : ''"
        >
          {{ step }}
        </span>
      </div>
      <div v-if="index < stages.length - 1" class="text-gray-400">›</div>
    </template>
  </div>
</template>
