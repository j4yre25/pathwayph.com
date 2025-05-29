<script setup>
import { ref } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';

import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

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
  <AppLayout title="Manage Internship Programs">
    <template #header>
      <h2 class="text-3xl font-semibold text-gray-800 tracking-tight">Manage Internship Programs</h2>
    </template>

    <Container class="py-8 space-y-10">
      <!-- Action Buttons -->
      <div class="flex flex-wrap gap-3">
        <Link :href="route('internship-programs.create')">
          <PrimaryButton>Add Internship</PrimaryButton>
        </Link>
        <Link :href="route('internship-programs.index')">
          <PrimaryButton>All Internship</PrimaryButton>
        </Link>
        <Link :href="route('internship-programs.index', { status: 'inactive' })">
          <PrimaryButton>Archived Internship</PrimaryButton>
        </Link>
      </div>

      <!-- Filters Section -->
      <div class="bg-white p-6 rounded-xl shadow-md space-y-6">
        <h3 class="text-lg font-medium text-gray-700">Filter Internship Programs</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Program</label>
            <select v-model="filters.program_id" class="w-full border-gray-300 rounded-lg shadow-sm">
              <option value="">All Programs</option>
              <option v-for="p in programs" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Career Opportunity</label>
            <select v-model="filters.career_opportunity_id" class="w-full border-gray-300 rounded-lg shadow-sm">
              <option value="">All Career Opportunities</option>
              <option v-for="c in careerOpportunities" :key="c.id" :value="c.id">{{ c.title }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Skills</label>
            <input v-model="filters.skills" type="text" placeholder="e.g. Vue, Laravel" class="w-full border-gray-300 rounded-lg shadow-sm" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-600 mb-1">Status</label>
            <select v-model="filters.status" class="w-full border-gray-300 rounded-lg shadow-sm">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="text-right">
          <PrimaryButton @click="applyFilters">Apply Filter</PrimaryButton>
        </div>
      </div>

      <!-- Internship Programs Table -->
      <div class="bg-white p-6 rounded-xl shadow-md space-y-4">
        <h3 class="text-lg font-medium text-gray-700">Internship Program List</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-100 text-xs uppercase text-gray-600 tracking-wide">
              <tr>
                <th class="px-6 py-4">Title</th>
                <th class="px-6 py-4">Program</th>
                <th class="px-6 py-4">Career Opportunity</th>
                <th class="px-6 py-4">Skills</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="ip in internshipPrograms" :key="ip.id" :class="{ 'bg-gray-50': ip.deleted_at }">
                <td class="px-6 py-4 whitespace-nowrap">{{ ip.title }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ ip.program?.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ ip.career_opportunity?.title }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ ip.skills }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="ip.deleted_at" class="text-red-500 font-semibold">Archived</span>
                  <span v-else class="text-green-600 font-semibold">Active</span>
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                  <button v-if="!ip.deleted_at" @click="archiveProgram(ip.id)" class="text-red-600 hover:underline">Archive</button>
                  <button v-else @click="restoreProgram(ip.id)" class="text-green-600 hover:underline">Restore</button>
                </td>
              </tr>
              <tr v-if="internshipPrograms.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                  No internship programs found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </Container>
  </AppLayout>
</template>
