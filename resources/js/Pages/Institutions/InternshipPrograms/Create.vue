<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  programs: Array,
  careerOpportunities: Array,
});

const form = useForm({
  title: '',
  program_id: '',
  career_opportunity_id: '',
  skills: '',
  description: '',
});

const showSuccess = ref(false);

function submit() {
  form.post(route('internship-programs.store'), {
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
  <AppLayout title="Add Internship Program">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Internship Program</h2>
    </template>

    <div class="py-8">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="space-y-6">

          <!-- General Info Section -->
          <div class="bg-white shadow-sm rounded-xl p-6 space-y-4 border border-gray-100">
            <h3 class="text-lg font-medium text-gray-700 mb-2">General Information</h3>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
              <input v-model="form.title" type="text" class="w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200" />
              <p v-if="form.errors.title" class="text-sm text-red-500 mt-1">{{ form.errors.title }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea v-model="form.description" rows="4" class="w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200"></textarea>
              <p v-if="form.errors.description" class="text-sm text-red-500 mt-1">{{ form.errors.description }}</p>
            </div>
          </div>

          <!-- Classification Section -->
          <div class="bg-white shadow-sm rounded-xl p-6 space-y-4 border border-gray-100">
            <h3 class="text-lg font-medium text-gray-700 mb-2">Classification</h3>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Program</label>
              <select v-model="form.program_id" class="w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200">
                <option value="">Select Program</option>
                <option v-for="p in programs" :key="p.id" :value="p.id">{{ p.name }}</option>
              </select>
              <p v-if="form.errors.program_id" class="text-sm text-red-500 mt-1">{{ form.errors.program_id }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Career Opportunity</label>
              <select v-model="form.career_opportunity_id" class="w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200">
                <option value="">Select Career Opportunity</option>
                <option v-for="c in careerOpportunities" :key="c.id" :value="c.id">{{ c.title }}</option>
              </select>
              <p v-if="form.errors.career_opportunity_id" class="text-sm text-red-500 mt-1">{{ form.errors.career_opportunity_id }}</p>
            </div>
          </div>

          <!-- Skills Section -->
          <div class="bg-white shadow-sm rounded-xl p-6 space-y-4 border border-gray-100">
            <h3 class="text-lg font-medium text-gray-700 mb-2">Skills</h3>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Skills (comma-separated)</label>
              <input v-model="form.skills" type="text" class="w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-blue-200" />
              <p v-if="form.errors.skills" class="text-sm text-red-500 mt-1">{{ form.errors.skills }}</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-md shadow transition duration-150">
              Add Program
            </button>
            <div v-if="showSuccess" class="text-green-600 font-medium">Internship program added!</div>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
