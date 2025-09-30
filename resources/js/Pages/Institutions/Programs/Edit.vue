<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    show: Boolean,
    institutionProgram: Object,
    degrees: Array,
});

const emit = defineEmits(['close', 'updated']);

const form = useForm({
    name: props.institutionProgram.program?.name || '',
    degree_id: props.institutionProgram.program?.degree_id || '',
    program_code: props.institutionProgram.program_code || '',
    duration: props.institutionProgram.duration || '',
    duration_time: props.institutionProgram.duration_time || '',
});

// Watch for institutionProgram prop to update form values
watch(
    () => props.institutionProgram,
    (newVal) => {
        form.name = newVal.program?.name || '';
        form.degree_id = newVal.program?.degree_id || '';
        form.program_code = newVal.program_code || '';
        form.duration = newVal.duration || '';
        form.duration_time = newVal.duration_time || '';
    },
    { immediate: true }
);

const updateProgram = () => {
    form.put(route('programs.update', { id: props.institutionProgram.id }), {
        preserveScroll: true,
        onSuccess: () => emit('updated'), // This triggers the reload in Index.vue
    });
};
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6">
      <h2 class="text-xl font-bold mb-4">Edit Program</h2>
      <form @submit.prevent="updateProgram">
        <div class="mb-4">
          <InputLabel for="name" value="Program Name" />
          <input
            id="name"
            v-model="form.name"
            type="text"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            placeholder="ex. Computer Science"
          />
        </div>
        <div class="mb-4">
          <InputLabel for="degree_id" value="Degree" />
          <select
            id="degree_id"
            v-model="form.degree_id"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
          >
            <option disabled value="">Select Degree</option>
            <option v-for="degree in degrees" :key="degree.id" :value="degree.id">
              {{ degree.type }}
            </option>
          </select>
        </div>
        <div class="mb-4">
          <InputLabel for="program_code" value="Program Code" />
          <input
            id="program_code"
            v-model="form.program_code"
            type="text"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            placeholder="e.g. BSCS-001, ASCT-2Y001"
          />
        </div>
        <div class="mb-4">
          <InputLabel for="duration" value="Duration" />
          <select
            id="duration"
            v-model="form.duration"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
          >
            <option value="">Select</option>
            <option value="Year">Year</option>
            <option value="Month">Month</option>
          </select>
        </div>
        <div class="mb-4">
          <InputLabel for="duration_time" value="Duration Time" />
          <input
            id="duration_time"
            type="number"
            v-model="form.duration_time"
            min="1"
            max="12"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
            placeholder="1,2,etc..."
          />
        </div>
        <div class="flex justify-end">
          <PrimaryButton :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
            Update
          </PrimaryButton>
          <button type="button" @click="emit('close')" class="ml-2 px-4 py-2 border rounded text-gray-700 bg-gray-100">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</template>
