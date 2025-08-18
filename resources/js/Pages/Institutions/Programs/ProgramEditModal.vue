<template>
  <ConfirmationModal :show="show" @close="$emit('close')">
    <template #title>
      <div class="flex items-center">
        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-4">
          <i class="fas fa-edit text-blue-500"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-800">Edit Program</h3>
      </div>
    </template>
    <template #content>
      <div class="space-y-4">
        <!-- Program Name -->
        <div>
          <InputLabel for="name" value="Program Name" class="mb-1" />
          <input
            id="name"
            v-model="form.name"
            type="text"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
            placeholder="ex. Computer Science"
          />
          <InputError :message="form.errors.name" class="mt-1" />
        </div>

        <!-- Degree Dropdown -->
        <div>
          <InputLabel for="degree_id" value="Degree" class="mb-1" />
          <select
            id="degree_id"
            v-model="form.degree_id"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
            required
          >
            <option disabled value="">Select Degree</option>
            <option v-for="degree in degrees" :key="degree.id" :value="degree.id">
              {{ degree.type }}
            </option>
          </select>
          <InputError :message="form.errors.degree_id" class="mt-1" />
        </div>

        <!-- Program Code (conditional) -->
        <div v-if="showProgramCode">
          <InputLabel for="program_code" value="Program Code" class="mb-1" />
          <input
            id="program_code"
            v-model="form.program_code"
            required
            type="text"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
            placeholder="e.g. BSCS-001, ASCT-2Y001"
          />
          <InputError :message="form.errors.program_code" class="mt-1" />
        </div>

        <!-- Duration (conditional) -->
        <div v-if="showDuration">
          <InputLabel for="duration" value="Duration" class="mb-1" />
          <select
            id="duration"
            v-model="form.duration"
            required
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
          >
            <option value="">Select</option>
            <option value="Year">Year</option>
            <option value="Month">Month</option>
          </select>
          <InputError :message="form.errors.duration" class="mt-1" />

          <InputLabel for="duration_time" value="Duration Time" class="mt-4 mb-1" />
          <input
            id="duration_time"
            type="number"
            v-model="form.duration_time"
            min="1"
            max="12"
            required
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
            placeholder="1,2,etc..."
          />
          <InputError :message="form.errors.duration_time" class="mt-1" />
        </div>
      </div>
    </template>
    <template #footer>
      <div class="flex justify-end space-x-3">
        <button @click="$emit('close')" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm transition-colors duration-200">
          Cancel
        </button>
        <button 
          @click="updateProgram" 
          class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm transition-colors duration-200"
          :disabled="form.processing"
        >
          <span v-if="form.processing">
            <i class="fas fa-spinner fa-spin mr-2"></i> Updating...
          </span>
          <span v-else>Update Program</span>
        </button>
      </div>
    </template>
  </ConfirmationModal>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
  show: Boolean,
  institutionProgram: Object,
  degrees: Array,
});

const emit = defineEmits(['close', 'updated']);

const form = useForm({
  name: '',
  degree_id: '',
  program_code: '',
  duration: '',
  duration_time: '',
});

const selectedDegree = computed(() =>
  props.degrees.find(d => d.id === form.degree_id)
);

const degreeType = computed(() => selectedDegree.value?.type || '');

const showProgramCode = computed(() =>
  ['Bachelor of Science', 'Bachelor of Arts', 'Master of Science', 'Master of Arts', 'Associate'].includes(degreeType.value)
);

const showDuration = computed(() =>
  ['Associate'].includes(degreeType.value)
);

// Watch for degree change to set initials
watch(() => form.degree_id, () => {
  if (!selectedDegree.value) return;
  const initials = {
    'Bachelor of Science': 'BS',
    'Bachelor of Arts': 'BA',
    'Associate': 'AS',
    'Master of Science': 'MS',
    'Master of Arts': 'MA',
    'Doctoral': 'PhD',
    'Diploma': 'D',
  }[degreeType.value] || '';
  
  if (showProgramCode.value) {
    // Only set if empty or matches previous initials
    if (!form.program_code || form.program_code.length <= initials.length) {
      form.program_code = initials;
    }
  } else {
    form.program_code = '';
  }
  
  if (!showDuration.value) {
    form.duration = '';
    form.duration_time = '';
  }
});

// Watch for institutionProgram prop to update form values
watch(
  () => props.institutionProgram,
  (newVal) => {
    if (newVal) {
      form.name = newVal.program?.name || '';
      form.degree_id = newVal.program?.degree_id || '';
      form.program_code = newVal.program_code || '';
      form.duration = newVal.duration || '';
      form.duration_time = newVal.duration_time || '';
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

const updateProgram = () => {
  if (!props.institutionProgram) return;
  
  form.put(route('programs.update', { id: props.institutionProgram.id }), {
    preserveScroll: true,
    onSuccess: () => {
      emit('updated');
      emit('close');
    },
  });
};
</script>