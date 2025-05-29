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
  <div class="flex items-center justify-between w-full">
    <template v-for="(step, index) in stages" :key="step">
      <div class="flex flex-col items-center">
        <span
          :class="[
            'text-sm mb-1',
            index <= stages.indexOf(stage) ? 'font-medium text-blue-600' : 'text-gray-400'
          ]"
        >
          {{ step }}
        </span>
        <div
          :class="[
            'w-full h-1.5 rounded-sm',
            index <= stages.indexOf(stage) ? 'bg-blue-500' : 'bg-gray-300'
          ]"
          style="width: 120px;"
        ></div>
      </div>
      <div v-if="index < stages.length - 1" class="invisible px-1">.</div>
    </template>
  </div>
</template>
