<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
    degree: Object,
});

const form = useForm({
    type: props.degree.degree?.type,
});

const updateDegree = () => {
    form.put(route('degrees.update', { institutionDegree: props.degree.id }), {
        preserveScroll: true,
    });
};

const goBack = () => {
  window.history.back();
};
</script>

<template>
  <AppLayout title="Edit Degree">
    <template #header>
      <div>
        <div class="flex items-center">
          <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
            <i class="fas fa-chevron-left"></i>
          </button>
          <i class="fas fa-graduation-cap text-blue-500 text-xl mr-2"></i>
          <h2 class="text-2xl font-bold text-gray-800">Edit Degree</h2>
        </div>
        <p class="text-sm text-gray-500 mb-1">Update the degree in the system.</p>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-lg sm:rounded-2xl">
          <div class="p-6 bg-white">
            <form @submit.prevent="updateDegree" class="space-y-6">
              <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                  <i class="fas fa-award text-blue-500 mr-2"></i>
                  Degree Details
                </h2>
                <div class="grid grid-cols-1 gap-6">
                  <div class="flex flex-col">
                    <label for="type" class="text-sm font-medium text-gray-700 mb-1">Degree Type</label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-list text-gray-400"></i>
                      </div>
                      <select
                        id="type"
                        v-model="form.type"
                        class="mt-2 block w-full pl-10 pr-10 py-2.5 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 appearance-none"
                      >
                        <option value="">Select Degree</option>
                        <option value="Associate">Associate</option>
                        <option value="Bachelor of Science">Bachelor of Science</option>
                        <option value="Bachelor of Arts">Bachelor of Arts</option>
                        <option value="Master of Science">Master of Science</option>
                        <option value="Master of Arts">Master of Arts</option>
                        <option value="Doctoral">Doctoral</option>
                        <option value="Diploma">Diploma</option>
                      </select>
                      <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                      </div>
                    </div>
                    <InputError :message="form.errors.type" class="mt-2" />
                  </div>
                </div>
              </div>

              <div class="flex justify-end pt-4">
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring focus:ring-blue-200 disabled:opacity-25 transition"
                >
                  <i class="fas fa-save mr-2"></i>
                  {{ form.processing ? 'Updating...' : 'Update Degree' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
