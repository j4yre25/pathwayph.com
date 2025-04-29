<template>
    <div v-if="show" class="fixed inset-0 z-50 bg-black bg-opacity-40 flex items-center justify-center">
      <div class="bg-white w-full max-w-md p-6 rounded-lg shadow relative">
        <button @click="$emit('close')" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
          &times;
        </button>
        <h2 class="text-lg font-semibold mb-4">
          {{ editing ? 'Edit Degree' : 'Add Degree' }}
        </h2>
  
        <form @submit.prevent="$emit('submit')" class="space-y-4">
          <div>
            <label class="block mb-1 text-sm">Degree Type</label>
            <select v-model="form.type" class="w-full border p-2 rounded">
              <option value="Bachelor">Bachelor</option>
              <option value="Associate">Associate</option>
              <option value="Diploma">Diploma</option>
              <option value="Master">Master</option>
              <option value="Doctorate">Doctorate</option>
            </select>
            <p v-if="errors.type" class="text-sm text-red-500">{{ errors.type }}</p>
          </div>
  
          <div class="text-right pt-4">
            <button type="button" @click="$emit('close')" class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </template>
  
  <script setup>
  defineProps({ show: Boolean, editing: Boolean, form: Object, errors: Object })
  defineEmits(['close', 'submit'])
  </script>
  
  <style scoped>
  .btn-primary { @apply bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700; }
  .btn-secondary { @apply bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400; }
  </style>
  