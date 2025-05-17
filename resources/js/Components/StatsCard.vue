<script setup>
defineProps({
  label: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number],
    required: true,
  },
  color: {
    type: String,
    default: 'gray',
  },
  subtitle: {
    type: String,
    default: '', 
  },
});
</script>

<template>
  <div :class="`bg-${color}-100 p-4 h-28 w-full flex flex-col justify-center items-center rounded-xl shadow text-center`">
        <div :class="`text-${color}-700 text-2xl font-semibold`">{{ value }}</div>
        <div class="text-gray-600">{{ label }}</div>
        <div v-if="subtitle" class="mt-1">
          <p class="text-sm text-gray-500">{{ subtitle }}</p>
        </div>
  </div>
</template>
