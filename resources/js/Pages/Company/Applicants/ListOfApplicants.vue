<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  applicants: {
    type: Array,
    default: () => [],
  },
  jobs: {
    type: Array,
    default: () => [],
  },
  statuses: {
    type: Array,
    default: () => [],
  },
  employmentTypes: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});
console.log("Props", props);
const filters = ref({ ...props.filters });

const filteredApplicants = computed(() => {
  let apps = props.applicants;
  if (filters.value.job_id) {
    apps = apps.filter(a => a.job_id == filters.value.job_id);
  }
  if (filters.value.status) {
    apps = apps.filter(a => a.status == filters.value.status);
  }
  if (filters.value.employment_type) {
    apps = apps.filter(a => a.employment_type?.includes(filters.value.employment_type));
  }
  if (filters.value.date_from) {
    apps = apps.filter(a => new Date(a.applied_at) >= new Date(filters.value.date_from));
  }
  if (filters.value.date_to) {
    apps = apps.filter(a => new Date(a.applied_at) <= new Date(filters.value.date_to));
  }
  return apps;
});

function applyFilters() {
  router.get(window.location.pathname, filters.value, { preserveState: true });
}
</script>

<template>
  <div>
    <!-- Filter Bar -->
    <div class="flex flex-wrap gap-2 mb-4">
      <select v-model="filters.job_id" @change="applyFilters" class="border px-2 py-1 rounded">
        <option value="">All Positions</option>
        <option v-for="job in jobs || []" :key="job.id" :value="job.id">{{ job.job_title || job.title }}</option>
      </select>
      <select v-model="filters.status" @change="applyFilters" class="border px-2 py-1 rounded">
        <option value="">All Statuses</option>
        <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
      </select>
      <select v-model="filters.employment_type" @change="applyFilters" class="border px-2 py-1 rounded">
        <option value="">All Employment Types</option>
        <option v-for="type in employmentTypes" :key="type" :value="type">{{ type }}</option>
      </select>
      <input type="date" v-model="filters.date_from" @change="applyFilters" class="border px-2 py-1 rounded" />
      <input type="date" v-model="filters.date_to" @change="applyFilters" class="border px-2 py-1 rounded" />
    </div>

    <!-- Applicants Table -->
    <table class="min-w-full bg-white border">
      <thead>
        <tr>
          <th class="px-4 py-2 border">Applicant Name</th>
          <th class="px-4 py-2 border">Job Applied For</th>
          <th class="px-4 py-2 border">Employment Type</th>
          <th class="px-4 py-2 border">Status</th>
          <th class="px-4 py-2 border">Date Applied</th>
          <th class="px-4 py-2 border">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="applicant in filteredApplicants" :key="applicant.id">
          <td class="border px-4 py-2">{{ applicant.name }}</td>
          <td class="border px-4 py-2">{{ applicant.job_title }}</td>
          <td class="border px-4 py-2">{{ applicant.employment_type }}</td>
          <td class="border px-4 py-2">{{ applicant.status }}</td>
          <td class="border px-4 py-2">{{ applicant.applied_at }}</td>
          <td class="border px-4 py-2">
            <button class="text-blue-600 hover:underline mr-2">View Profile</button>
            <button class="text-green-600 hover:underline">Schedule Interview</button>
          </td>
        </tr>
        <tr v-if="filteredApplicants.length === 0">
          <td colspan="6" class="text-center py-4 text-gray-500">No applicants found.</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>