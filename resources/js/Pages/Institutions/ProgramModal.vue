<template>
    <div v-if="show" class="fixed inset-0 z-50 bg-black bg-opacity-40 flex items-center justify-center">
      <div class="bg-white w-full max-w-lg p-6 rounded-lg shadow-lg relative">
        <button @click="$emit('close')" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
          &times;
        </button>
  
        <h2 class="text-lg font-semibold mb-4">
          {{ editing ? 'Edit Program' : 'Add Program' }}
        </h2>
  
        <form @submit.prevent="$emit('submit')" class="space-y-4">
          <div>
            <label class="block mb-1 text-sm">Program Name</label>
            <input v-model="form.name" class="w-full border p-2 rounded" />
            <p v-if="errors.name" class="text-sm text-red-500">{{ errors.name }}</p>
          </div>
  
          <div>
            <label class="block mb-1 text-sm">Degree</label>
            <select v-model="form.degree_id" class="w-full border p-2 rounded">
              <option disabled value="">-- Select Degree --</option>
              <option v-for="d in degrees" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
            <p v-if="errors.degree_id" class="text-sm text-red-500">{{ errors.degree_id }}</p>
          </div>
  
          <div class="flex justify-end space-x-2 pt-4">
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
    degrees: Array,
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
  