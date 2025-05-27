<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
  internshipPrograms: Array,
  programs: Array,
  careerOpportunities: Array,
});

const filters = ref({
  program_id: '',
  career_opportunity_id: '',
  skills: '',
  status: '',
});

function applyFilters() {
  router.get(route('internship-programs.index'), filters.value, { preserveState: true, preserveScroll: true });
}

function archiveProgram(id) {
  if (confirm('Archive this internship program?')) {
    router.delete(route('internship-programs.archive', { id }), { preserveScroll: true });
  }
}

function restoreProgram(id) {
  if (confirm('Restore this internship program?')) {
    router.post(route('internship-programs.restore', { id }), {}, { preserveScroll: true });
  }
}
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Manage Internship Programs</h1>
    <!-- Filters -->
    <div class="flex gap-4 mb-4">
      <select v-model="filters.program_id" @change="applyFilters" class="border rounded p-2">
        <option value="">All Programs</option>
        <option v-for="p in programs" :key="p.id" :value="p.id">{{ p.name }}</option>
      </select>
      <select v-model="filters.career_opportunity_id" @change="applyFilters" class="border rounded p-2">
        <option value="">All Career Opportunities</option>
        <option v-for="c in careerOpportunities" :key="c.id" :value="c.id">{{ c.title }}</option>
      </select>
      <input v-model="filters.skills" @keyup.enter="applyFilters" placeholder="Skills" class="border rounded p-2" />
      <select v-model="filters.status" @change="applyFilters" class="border rounded p-2">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
    </div>

    <!-- List -->
    <table class="min-w-full bg-white border">
      <thead>
        <tr>
          <th class="p-2 border">Title</th>
          <th class="p-2 border">Program</th>
          <th class="p-2 border">Career Opportunity</th>
          <th class="p-2 border">Skills</th>
          <th class="p-2 border">Status</th>
          <th class="p-2 border">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="ip in internshipPrograms" :key="ip.id" :class="{ 'bg-gray-100': ip.deleted_at }">
          <td class="p-2 border">{{ ip.title }}</td>
          <td class="p-2 border">{{ ip.program?.name }}</td>
          <td class="p-2 border">{{ ip.career_opportunity?.title }}</td>
          <td class="p-2 border">{{ ip.skills }}</td>
          <td class="p-2 border">
            <span v-if="ip.deleted_at" class="text-red-500">Archived</span>
            <span v-else class="text-green-600">Active</span>
          </td>
          <td class="p-2 border">
            <button v-if="!ip.deleted_at" @click="archiveProgram(ip.id)" class="text-red-600 hover:underline">Archive</button>
            <button v-else @click="restoreProgram(ip.id)" class="text-green-600 hover:underline">Restore</button>
            <!-- Add Edit and Assign buttons as needed -->
          </td>
        </tr>
      </tbody>
    </table>
    <!-- Add Create, Batch Upload, and Assign buttons as needed -->
  </div>
</template>