<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';
import { useForm, usePage, Link } from '@inertiajs/vue3';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  programs: Array,
});

const { props: pageProps } = usePage();
const userId = pageProps.auth.user.id;

const form = useForm({
  titles: '',
  program_ids: [],
});

const showSuccess = ref(false);

const toggleProgram = (id) => {
  if (form.program_ids.includes(id)) {
    form.program_ids = form.program_ids.filter((pid) => pid !== id);
  } else {
    form.program_ids.push(id);
  }
};

const submit = () => {
  form.post(route('careeropportunities.store', { user: pageProps.auth.user.id }), {
    onSuccess: () => {
      showSuccess.value = true;
      form.reset();
    },
    onError: () => {
      showSuccess.value = false;
    },
  });
};
</script>

<template>
  <AppLayout title="Add Career Opportunity">
    <template #header>
      <div class="flex items-center">
        <Link :href="route('careeropportunities', { user: userId })" class="mr-4 text-gray-600 hover:text-gray-900 transition">
          <i class="fas fa-chevron-left"></i>
        </Link>
        <i class="fas fa-plus-circle text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Career Opportunity</h2>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center">
                <i class="fas fa-briefcase text-blue-500 mr-2"></i>
                <h3 class="text-lg font-medium text-gray-900">New Career Opportunity</h3>
              </div>
            </div>
            
            <form @submit.prevent="submit" class="space-y-6">
              <!-- Career Titles -->
              <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                  <i class="fas fa-tag text-blue-500 mr-2"></i>
                  Career Opportunity Titles
                </h3>
                <div>
                  <label for="titles" class="block text-sm font-medium text-gray-700 mb-1">Enter Titles (Separate with comma)</label>
                  <textarea
                    id="titles"
                    v-model="form.titles"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors"
                    placeholder="Ex. Doctor, Nurse, Medical Technician"
                    rows="3"
                  />
                  <p v-if="form.errors.titles" class="text-sm text-red-500 mt-1">{{ form.errors.titles }}</p>
                </div>
              </div>

              <!-- Programs -->
              <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                  <i class="fas fa-graduation-cap text-green-500 mr-2"></i>
                  Programs
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                  <div
                    v-for="program in props.programs"
                    :key="program.id"
                    class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-50 transition-colors"
                  >
                    <input
                      type="checkbox"
                      :id="'program-' + program.id"
                      :value="program.id"
                      :checked="form.program_ids.includes(program.id)"
                      @change="() => toggleProgram(program.id)"
                      class="rounded text-green-600 focus:ring-2 focus:ring-green-500 focus:ring-opacity-50"
                    />
                    <label :for="'program-' + program.id" class="text-sm text-gray-700 cursor-pointer">{{ program.program?.name }}</label>
                  </div>
                </div>
                <p v-if="form.errors.program_ids" class="text-sm text-red-500 mt-1">{{ form.errors.program_ids }}</p>
              </div>

              <!-- Action Buttons -->
              <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <Link :href="route('careeropportunities', { user: userId })" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center">
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
                  <span v-else>Save Opportunity</span>
                </button>
              </div>
              
              <!-- Success Message -->
              <transition name="fade">
                <div
                  v-if="showSuccess"
                  class="mt-4 p-4 bg-green-50 border border-green-200 rounded-md text-green-600 font-medium flex items-center"
                >
                  <i class="fas fa-check-circle text-green-500 mr-2"></i>
                  <span>Career opportunity added successfully!</span>
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
