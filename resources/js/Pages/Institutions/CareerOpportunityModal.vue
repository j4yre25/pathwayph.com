<template>
  <div v-if="show" class="fixed inset-0 z-50 bg-black bg-opacity-40 flex items-center justify-center">
    <div class="bg-white w-full max-w-lg p-6 rounded-lg shadow relative">
      <button @click="$emit('close')" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
        &times;
      </button>
      <h2 class="text-lg font-semibold mb-4">
        {{ editing ? 'Edit Career Opportunity' : 'Add Career Opportunity' }}
      </h2>

      <form @submit.prevent="$emit('submit')" class="space-y-4">
        <!-- Program Multi-select -->
        <div>
          <label class="block text-sm font-medium mb-1">Program Name</label>
          <select v-model="form.program_ids" multiple class="w-full border p-2 rounded">
            <option v-for="program in programs" :key="program.id" :value="program.id">
              {{ program.name }}
            </option>
          </select>
          <p v-if="errors.program_ids" class="text-sm text-red-500 mt-1">{{ errors.program_ids }}</p>
        </div>

        <!-- Title Comma-Separated -->
        <div>
          <label class="block text-sm font-medium mb-1">Career Opportunity Title</label>
          <input
            v-model="form.titles"
            placeholder="Ex. Doctor, Nurse (Separate with comma)."
            class="w-full border p-2 rounded"
          />
          <p v-if="errors.titles" class="text-sm text-red-500 mt-1">{{ errors.titles }}</p>
        </div>

        <div class="flex justify-end gap-2 pt-4">
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
  errors: Object,
  programs: Array
})
defineEmits(['close', 'submit'])
</script>
