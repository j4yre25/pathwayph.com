<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import { useForm, usePage, Link } from '@inertiajs/vue3';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  careerOpportunities: Array,
});

const userId = usePage().props.auth.user.id;

const form = useForm({
  career_opportunity_ids: [],
  skill_names: '',
});

const showSuccess = ref(false);

const uniqueCareerOpportunities = computed(() => {
  const seenTitles = new Set();
  return props.careerOpportunities.filter((opportunity) => {
    if (seenTitles.has(opportunity.title)) {
      return false;
    }
    seenTitles.add(opportunity.title);
    return true;
  });
});

function toggleCheckbox(id) {
  if (form.career_opportunity_ids.includes(id)) {
    form.career_opportunity_ids = form.career_opportunity_ids.filter((i) => i !== id);
  } else {
    form.career_opportunity_ids.push(id);
  }
}

function submit() {
  form.post(route('instiskills.store', { user: userId }), {
    preserveScroll: true,
    onSuccess: () => {
      showSuccess.value = true;
      form.reset();
    },
    onError: () => {
      showSuccess.value = false;
    },
  });
}
</script>

<template>
  <AppLayout title="Add Skills">
    <template #header>
      <div class="flex items-center">
        <Link :href="route('instiskills.index')" class="mr-4 text-gray-600 hover:text-gray-900 transition">
          <i class="fas fa-chevron-left"></i>
        </Link>
        <i class="fas fa-plus-circle text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Skills</h2>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center">
                <i class="fas fa-tools text-blue-500 mr-2"></i>
                <h3 class="text-lg font-medium text-gray-900">New Skills</h3>
              </div>
            </div>
            
            <form @submit.prevent="submit" class="space-y-6">
              <!-- Career Opportunities -->
              <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                  <i class="fas fa-briefcase text-indigo-500 mr-2"></i>
                  Career Opportunities
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                  <div
                    v-for="opportunity in uniqueCareerOpportunities"
                    :key="opportunity.id"
                    class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-50 transition-colors"
                  >
                    <input
                      type="checkbox"
                      :id="'career-' + opportunity.id"
                      :value="opportunity.id"
                      :checked="form.career_opportunity_ids.includes(opportunity.id)"
                      @change="() => toggleCheckbox(opportunity.id)"
                      class="rounded text-indigo-600 focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50"
                    />
                    <label :for="'career-' + opportunity.id" class="text-sm text-gray-700 cursor-pointer">{{ opportunity.title }}</label>
                  </div>
                </div>
                <p v-if="form.errors.career_opportunity_ids" class="text-sm text-red-500 mt-1">{{ form.errors.career_opportunity_ids }}</p>
              </div>

              <!-- Skill Names -->
              <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                  <i class="fas fa-tools text-purple-500 mr-2"></i>
                  Skill Names
                </h3>
                <div>
                  <label for="skill_names" class="block text-sm font-medium text-gray-700 mb-1">Enter Skills (Separate with comma)</label>
                  <textarea
                    id="skill_names"
                    v-model="form.skill_names"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50 transition-colors"
                    placeholder="Ex. Programming, Editing, Project Management"
                    rows="3"
                  />
                  <p v-if="form.errors.skill_names" class="text-sm text-red-500 mt-1">{{ form.errors.skill_names }}</p>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <Link :href="route('instiskills.index')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center">
                  <i class="fas fa-times mr-2"></i>
                  Cancel
                </Link>
                <button
                  type="submit"
                  class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
                  :disabled="form.processing"
                >
                  <i class="fas fa-save mr-2"></i>
                  <span v-if="form.processing">Saving...</span>
                  <span v-else>Save Skills</span>
                </button>
              </div>
              
              <!-- Success Message -->
              <transition name="fade">
                <div
                  v-if="showSuccess"
                  class="mt-4 p-4 bg-green-50 border border-green-200 rounded-md text-green-600 font-medium flex items-center"
                >
                  <i class="fas fa-check-circle text-green-500 mr-2"></i>
                  <span>Skills added successfully!</span>
                </div>
              </transition>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
