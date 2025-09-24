<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  show: Boolean,
  internshipProgram: Object,
  programs: Array,
  careerOpportunities: Array,
  skills: Array,
});

const emit = defineEmits(['close']);

const form = useForm({
  title: '',
  program_id: [],
  career_opportunity_id: [],
  skill_id: [],
});

const showSuccess = ref(false);

watch(() => props.internshipProgram, (newValue) => {
  if (newValue) {
    form.title = newValue.title;
    form.program_id = newValue.programs.map(p => p.id);
    form.career_opportunity_id = newValue.career_opportunities.map(c => c.id);
    form.skill_id = newValue.skills.map(s => s.id);
  }
}, { immediate: true, deep: true });

function submit() {
  form.put(route('internship-programs.update', props.internshipProgram.id), {
    onSuccess: () => {
      showSuccess.value = true;
      setTimeout(() => {
        showSuccess.value = false;
        emit('close');
      }, 1500);
    },
    onError: () => {
      showSuccess.value = false;
    },
  });
}

function closeModal() {
  emit('close');
}
</script>

<template>
  <Modal :modelValue="show" max-width="4xl" @close="closeModal">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Edit Internship Program</h2>
        <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- General Information -->
        <section class="bg-white rounded-lg border border-gray-100 p-4">
          <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">General Information</h3>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input
              v-model="form.title"
              type="text"
              placeholder="Enter internship title"
              class="w-full rounded-md border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none transition p-2.5"
            />
            <p v-if="form.errors.title" class="text-sm text-red-500 mt-1">{{ form.errors.title }}</p>
          </div>
        </section>

        <!-- Programs -->
        <section class="bg-white rounded-lg border border-gray-100 p-4">
          <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Programs</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-40 overflow-y-auto">
            <div
              v-for="p in programs"
              :key="p.id"
              class="flex items-center space-x-2"
            >
              <input
                type="checkbox"
                :id="'program-' + p.id"
                :value="p.id"
                v-model="form.program_id"
                class="rounded text-blue-600 focus:ring-2 focus:ring-blue-500"
              />
              <label :for="'program-' + p.id" class="text-sm text-gray-700">{{ p.name }}</label>
            </div>
          </div>
          <p v-if="form.errors.program_id" class="text-sm text-red-500 mt-1">{{ form.errors.program_id }}</p>
        </section>

        <!-- Career Opportunities -->
        <section class="bg-white rounded-lg border border-gray-100 p-4">
          <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Career Opportunities</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-40 overflow-y-auto">
            <div
              v-for="c in careerOpportunities"
              :key="c.id"
              class="flex items-center space-x-2"
            >
              <input
                type="checkbox"
                :id="'career-' + c.id"
                :value="c.id"
                v-model="form.career_opportunity_id"
                class="rounded text-blue-600 focus:ring-2 focus:ring-blue-500"
              />
              <label :for="'career-' + c.id" class="text-sm text-gray-700">{{ c.title }}</label>
            </div>
          </div>
          <p v-if="form.errors.career_opportunity_id" class="text-sm text-red-500 mt-1">{{ form.errors.career_opportunity_id }}</p>
        </section>

        <!-- Skills -->
        <section class="bg-white rounded-lg border border-gray-100 p-4">
          <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Skills</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-40 overflow-y-auto">
            <div
              v-for="s in skills"
              :key="s.id"
              class="flex items-center space-x-2"
            >
              <input
                type="checkbox"
                :id="'skill-' + s.id"
                :value="s.id"
                v-model="form.skill_id"
                class="rounded text-blue-600 focus:ring-2 focus:ring-blue-500"
              />
              <label :for="'skill-' + s.id" class="text-sm text-gray-700">{{ s.name }}</label>
            </div>
          </div>
          <p v-if="form.errors.skill_id" class="text-sm text-red-500 mt-1">{{ form.errors.skill_id }}</p>
        </section>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-4 pt-4 border-t">
          <button
            type="button"
            @click="closeModal"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            Cancel
          </button>
          <button
            type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-md shadow transition"
            :disabled="form.processing"
          >
            Update Program
          </button>
          <transition name="fade">
            <div
              v-if="showSuccess"
              class="text-green-600 font-medium flex items-center space-x-2"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                   viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M5 13l4 4L19 7"/>
              </svg>
              <span>Updated!</span>
            </div>
          </transition>
        </div>
      </form>
    </div>
  </Modal>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>