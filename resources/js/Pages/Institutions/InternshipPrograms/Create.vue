<script setup>
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
  <div>
    <h1 class="text-2xl font-bold mb-4">Add Internship Program</h1>
    <form @submit.prevent="submit" class="space-y-4 max-w-lg">
      <div>
        <label class="block mb-1">Title</label>
        <input v-model="form.title" type="text" class="border rounded p-2 w-full" />
        <div v-if="form.errors.title" class="text-red-500 text-sm">{{ form.errors.title }}</div>
      </div>
      <div>
        <label class="block mb-1">Program</label>
        <select v-model="form.program_id" class="border rounded p-2 w-full">
          <option value="">Select Program</option>
          <option v-for="p in programs" :key="p.id" :value="p.id">{{ p.name }}</option>
        </select>
        <div v-if="form.errors.program_id" class="text-red-500 text-sm">{{ form.errors.program_id }}</div>
      </div>
      <div>
        <label class="block mb-1">Career Opportunity</label>
        <select v-model="form.career_opportunity_id" class="border rounded p-2 w-full">
          <option value="">Select Career Opportunity</option>
          <option v-for="c in careerOpportunities" :key="c.id" :value="c.id">{{ c.title }}</option>
        </select>
        <div v-if="form.errors.career_opportunity_id" class="text-red-500 text-sm">{{ form.errors.career_opportunity_id }}</div>
      </div>
      <div>
        <label class="block mb-1">Skills (comma separated)</label>
        <input v-model="form.skills" type="text" class="border rounded p-2 w-full" />
        <div v-if="form.errors.skills" class="text-red-500 text-sm">{{ form.errors.skills }}</div>
      </div>
      <div>
        <label class="block mb-1">Description</label>
        <textarea v-model="form.description" class="border rounded p-2 w-full"></textarea>
        <div v-if="form.errors.description" class="text-red-500 text-sm">{{ form.errors.description }}</div>
      </div>
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Add</button>
      <div v-if="showSuccess" class="text-green-600 mt-2">Internship program added!</div>
    </form>
  </div>
</template>