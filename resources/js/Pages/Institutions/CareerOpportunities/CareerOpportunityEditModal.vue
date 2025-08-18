<template>
  <ConfirmationModal :show="show" @close="$emit('close')">
    <template #title>
      <div class="flex items-center">
        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-4">
          <i class="fas fa-edit text-blue-500"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-800">Edit Career Opportunity</h3>
      </div>
    </template>
    <template #content>
      <div class="space-y-4">
        <!-- Program Dropdown -->
        <div>
          <InputLabel for="program_id" value="Program" class="mb-1" />
          <select
            id="program_id"
            v-model="form.program_id"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
            required
          >
            <option disabled value="">Select Program</option>
            <option v-for="program in programs" :key="program.id" :value="program.id">
              {{ program.program.name }}
            </option>
          </select>
          <InputError :message="form.errors.program_id" class="mt-1" />
        </div>

        <!-- Career Opportunity Title -->
        <div>
          <InputLabel for="title" value="Career Opportunity Title" class="mb-1" />
          <input
            id="title"
            v-model="form.title"
            type="text"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
            placeholder="e.g. Software Engineer"
          />
          <InputError :message="form.errors.title" class="mt-1" />
        </div>
      </div>
    </template>
    <template #footer>
      <div class="flex justify-end space-x-3">
        <button @click="$emit('close')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm transition-colors duration-200">
          Cancel
        </button>
        <button 
          @click="submit" 
          class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm transition-colors duration-200"
          :disabled="form.processing"
        >
          <span v-if="form.processing">
            <i class="fas fa-spinner fa-spin mr-2"></i> Updating...
          </span>
          <span v-else>Update</span>
        </button>
      </div>
    </template>
  </ConfirmationModal>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
  show: Boolean,
  link: Object,
  programs: Array,
});

const emit = defineEmits(['close', 'updated']);

const form = useForm({
  title: '',
  program_id: '',
});

// Watch for link prop to update form values
watch(
  () => props.link,
  (newVal) => {
    if (newVal) {
      form.title = newVal.career_opportunity?.title || '';
      form.program_id = newVal.institution_program?.id || '';
    }
  },
  { immediate: true }
);

// Watch for show prop to reset errors when modal is closed
watch(
  () => props.show,
  (value) => {
    if (!value) {
      form.clearErrors();
    }
  }
);

const submit = () => {
  if (!props.link || !props.link.id) return;
  
  form.put(route('career-opportunities.update', { id: props.link.id }), {
    preserveScroll: true,
    onSuccess: () => {
      emit('updated');
      emit('close');
    },
  });
};
</script>