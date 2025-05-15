<script setup>
defineProps({
    label: String,
    value: [String, Number],
    subtitle: String,
    color: {
        type: String,
        default: 'blue',
    },
})
</script>

<template>
  <div :class="`bg-${color}-100 p-4 rounded-xl shadow text-center`">
        <div :class="`text-${color}-700 text-2xl font-semibold`">{{ value }}</div>
        <div class="text-gray-600">{{ label }}</div>
        <div v-if="subtitle" class="text-gray-400 text-xs mt-1">{{ subtitle }}</div>
  </div>
</template>
