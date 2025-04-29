<template>
  <textarea
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
    class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
    :class="{ 'border-red-500': error }"
    v-bind="$attrs"
  ></textarea>
  <div v-if="error" class="text-red-500 text-sm mt-1">{{ error }}</div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  }
});

defineEmits(['update:modelValue']);
</script> 