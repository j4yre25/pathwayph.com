<template>
  <select
    :value="modelValue"
    @change="$emit('update:modelValue', $event.target.value)"
    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
    :class="{ 'border-red-500': error }"
    v-bind="$attrs"
  >
    <slot></slot>
  </select>
  <div v-if="error" class="text-red-500 text-sm mt-1">{{ error }}</div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  error: {
    type: String,
    default: ''
  }
});

defineEmits(['update:modelValue']);
</script> 