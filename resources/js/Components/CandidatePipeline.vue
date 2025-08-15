<script setup>
const props = defineProps({
  stage: {
    type: String,
    required: true,
  },
})

// Display stages in order for progress bar logic
const stages = [
  'Applying',
  'Screening',
  'Testing',
  'Final Interview',
  'Onboarding'
]

// Map backend stage keys to internal stage names
const stageMap = {
  applying: 'Applying',
  applied: 'Applying',
  screening: 'Screening',
  screened: 'Screening',
  testing: 'Testing',
  tested: 'Testing',
  'final interview': 'Final Interview',
  final_interview: 'Final Interview',
  finalinterview: 'Final Interview',
  onboarding: 'Onboarding',
  onboarded: 'Onboarding',
}

// Human-readable display labels
const displayLabelMap = {
  Applying: 'Applied',
  Screening: 'Screened',
  Testing: 'Tested',
  'Final Interview': 'Final Interviewed',
  Onboarding: 'Onboarding',
}

const normalizedStage = stageMap[props.stage?.toLowerCase()] ?? null
const currentStageIndex = stages.findIndex(s => s === normalizedStage)

</script>

<template>
  <div class="flex items-center justify-between w-full">
    <template v-for="(step, index) in stages" :key="step">
      <div class="flex flex-col items-center">
        <span
          class="text-xs text-gray-600 ml-1"
          :class="index === currentStageIndex ? 'font-semibold text-blue-600' : ''"
        >
          {{ index < currentStageIndex ? displayLabelMap[step] : step }}
        </span>
        <div
          :class="[
            'w-24 h-1 rounded',
            index <= currentStageIndex ? 'bg-blue-500' : 'bg-gray-300'
          ]"
          style="width: 120px;"
        ></div>
      </div>
      <div v-if="index < stages.length - 1" class="invisible px-1">.</div>
    </template>
  </div>
</template>


