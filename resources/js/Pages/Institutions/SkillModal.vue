<template>
  <div v-if="show" class="fixed inset-0 z-50 bg-black bg-opacity-40 flex items-center justify-center">
    <div class="bg-white w-full max-w-lg p-6 rounded-lg shadow relative">
      <button @click="$emit('close')" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
        &times;
      </button>
      <h2 class="text-lg font-semibold mb-4">
        {{ editing ? 'Edit Skill' : 'Add Skill' }}
      </h2>

      <form @submit.prevent="$emit('submit')" class="space-y-4">
        <!-- Skill Name -->
        <div>
          <label class="block text-sm font-medium mb-1">Skill Name</label>
          <input v-model="form.name" class="w-full border p-2 rounded" />
          <p v-if="errors.name" class="text-sm text-red-500 mt-1">{{ errors.name }}</p>
        </div>

        <!-- Program Select -->
        <div>
          <label class="block text-sm font-medium mb-1">Program Name(s)</label>
          <select v-model="form.program_ids" multiple class="w-full border p-2 rounded">
            <option v-for="program in programs" :key="program.id" :value="program.id">
              {{ program.name }}
            </option>
          </select>
          <p v-if="errors.program_ids" class="text-sm text-red-500 mt-1">{{ errors.program_ids }}</p>
        </div>

        <!-- Career Select -->
        <div>
          <label class="block text-sm font-medium mb-1">Career Opportunity Title(s)</label>
          <select v-model="form.career_opportunity_ids" multiple class="w-full border p-2 rounded">
            <option v-for="career in careers" :key="career.id" :value="career.id">
              {{ career.title }}
            </option>
          </select>
          <p v-if="errors.career_opportunity_ids" class="text-sm text-red-500 mt-1">{{ errors.career_opportunity_ids }}</p>
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
  programs: Array,
  careers: Array
})
defineEmits(['close', 'submit'])
</script>
