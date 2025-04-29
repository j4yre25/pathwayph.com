<template>
    <div v-if="show" class="fixed inset-0 z-50 bg-black bg-opacity-40 flex items-center justify-center">
      <div
        class="bg-white w-full max-w-lg rounded-lg shadow-lg p-6 relative"
        @keydown.esc="$emit('close')"
        tabindex="0"
      >
        <!-- Close Button -->
        <button @click="$emit('close')" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
          &times;
        </button>
  
        <!-- Title -->
        <div class="mb-4 text-lg font-semibold text-gray-800">
          {{ editing ? 'Edit School Year' : 'Add School Year' }}
        </div>
  
        <!-- Form -->
        <form @submit.prevent="$emit('submit')" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">School Year Range</label>
            <input type="text" v-model="form.school_year_range" placeholder="Ex. 2024-2025"
                   class="w-full border p-2 rounded" />
            <p v-if="errors.school_year_range" class="text-red-500 text-sm">{{ errors.school_year_range }}</p>
          </div>
  
          <div>
            <label class="block text-sm font-medium text-gray-700">Term (1–5)</label>
            <input type="number" v-model="form.term" placeholder="Ex. 1" min="1" max="5"
                   class="w-full border p-2 rounded" />
            <p v-if="errors.term" class="text-red-500 text-sm">{{ errors.term }}</p>
          </div>
  
          <div class="flex justify-end gap-2 mt-4">
            <button type="button" @click="$emit('close')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script setup>
  defineProps({
    show: Boolean,
    editing: Boolean,
    form: Object,
    errors: Object
  })
  defineEmits(['close', 'submit'])
  </script>
  
  <style scoped>
  .btn-primary {
    @apply bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700;
  }
  .btn-secondary {
    @apply bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400;
  }
  </style>
  