<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
  show: Boolean,
  skill: Object,
  careerOpportunities: Array,
});

const emit = defineEmits(['close']);

const form = useForm({
  skill_name: '',
  career_opportunity_id: '',
});

watch(() => props.skill, (newValue) => {
  if (newValue) {
    form.skill_name = newValue.skill?.name || '';
    form.career_opportunity_id = newValue.career_opportunity_id;
  }
}, { immediate: true, deep: true });

const uniqueCareerOpportunities = computed(() => {
  const seenTitles = new Set();
  return (props.careerOpportunities || []).filter((opportunity) => {
    if (seenTitles.has(opportunity.title)) {
      return false;
    }
    seenTitles.add(opportunity.title);
    return true;
  });
});

function submit() {
  if (!props.skill) return;
  
  form.put(route('instiskills.update', props.skill.id), {
    preserveScroll: true,
    onSuccess: () => {
      emit('close');
    },
  });
}

function closeModal() {
  emit('close');
}
</script>

<template>
  <Modal :modelValue="show" max-width="md" @close="closeModal">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
          <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-4">
            <i class="fas fa-edit text-blue-500"></i>
          </div>
          <h3 class="text-lg font-semibold text-gray-800">Edit Skill</h3>
        </div>
        <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <!-- Skill Name -->
        <div>
          <InputLabel for="skill_name" value="Skill Name" class="mb-1" />
          <input
            id="skill_name"
            v-model="form.skill_name"
            type="text"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
            autocomplete="off"
          />
          <InputError :message="form.errors.skill_name" class="mt-1" />
        </div>

        <!-- Career Opportunity Dropdown -->
        <div>
          <InputLabel for="career_opportunity_id" value="Career Opportunity" class="mb-1" />
          <select
            id="career_opportunity_id"
            v-model="form.career_opportunity_id"
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
            required
          >
            <option disabled value="">Select one</option>
            <option
              v-for="op in uniqueCareerOpportunities"
              :key="op.id"
              :value="op.id"
            >
              {{ op.title }}
            </option>
          </select>
          <InputError :message="form.errors.career_opportunity_id" class="mt-1" />
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3 pt-4 border-t mt-6">
          <button 
            type="button"
            @click="closeModal" 
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm transition-colors duration-200"
          >
            Cancel
          </button>
          <button 
            type="submit" 
            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm transition-colors duration-200"
            :disabled="form.processing"
          >
            <span v-if="form.processing">
              <i class="fas fa-spinner fa-spin mr-2"></i> Updating...
            </span>
            <span v-else>Update Skill</span>
          </button>
        </div>
      </form>
    </div>
  </Modal>
</template>