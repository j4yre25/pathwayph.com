<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  jobs: Object, // paginated jobs object
  sectors: Array,
  categories: Array,
})

const searchQuery = ref('');
const selectedSector = ref('');
const selectedCategory = ref('');

const jobTypeLabel = (type) => {
  switch (type) {
    case 1: return 'Full-Time';
    case 2: return 'Part-Time';
    default: return 'Unknown';
  }
};

const filteredJobs = computed(() => {
  return props.jobs.data
    .filter(job => {
      const matchesSearch = job.job_title?.toLowerCase().includes(searchQuery.value.toLowerCase());
      const matchesSector = selectedSector.value ? job.sector_id === parseInt(selectedSector.value) : true;
      const matchesCategory = selectedCategory.value ? job.category_id === parseInt(selectedCategory.value) : true;
      return matchesSearch && matchesSector && matchesCategory;
    })
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
});

const goToJob = (jobId) => {
  router.get(route('peso.jobs.view', { id: jobId }));
};
</script>

<template>
  <div class="overflow-x-auto">
    <table class="min-w-full bg-white border border-gray-200">
      <thead>
        <tr class="w-full bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
          <th class="py-2 px-4 text-left border">Job Title</th>
          <th class="py-2 px-4 text-left border">Posted By</th>
          <th class="py-2 px-4 text-left border">Location</th>
          <th class="py-2 px-4 text-left border">Employment Type</th>
          <th class="py-2 px-4 text-left border">Experience Level</th>
          <th class="py-2 px-4 text-left border">Applicants</th>
          <th class="py-2 px-4 text-left border">Status</th>
        </tr>
      </thead>
      <tbody class="text-gray-600 text-sm font-light">
        <tr v-for="job in props.jobs.data" :key="job.id" @click="goToJob(job.id)"
          class="border-b border-gray-200 hover:bg-gray-100 cursor-pointer">
          <td class="border border-gray-200 px-6 py-4">{{ job.job_title }}</td>
          <td class="border border-gray-200 px-6 py-4">
            <template v-if="job.company">
              {{ job.company.hr_first_name }} {{ job.company.hr_last_name }}
            </template>
            <template v-else-if="job.institution">
              {{ job.institution.career_officer_first_name }} {{ job.institution.career_officer_last_name }}
            </template>
            <template v-else-if="job.peso">
              {{ job.peso.peso_first_name }} {{ job.peso.peso_last_name }}
            </template>
            <template v-else>
              <span class="text-gray-500 italic">Unknown</span>
            </template>
          </td>
          <td class="border border-gray-200 px-6 py-4">
            {{ job.location?.address ?? job.company?.company_city ?? 'N/A' }}
          </td>
          <td class="border border-gray-200 px-6 py-4">
            {{ jobTypeLabel(job.job_type) }}
          </td>
          <td class="border border-gray-200 px-6 py-4">{{ job.job_experience_level }}</td>
          <td class="border border-gray-200 px-6 py-4">{{ job.applicants_count ?? 0 }}</td>
          <td class="border border-gray-200 px-6 py-4">
            <span v-if="job.is_approved === 1" class="text-green-600 font-semibold">Approved</span>
            <span v-else-if="job.is_approved === 0" class="text-red-600 font-semibold">Disapproved</span>
            <span v-else class="text-yellow-600 font-semibold">Pending</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    <nav v-if="jobs.links && jobs.links.length > 1" aria-label="Page navigation">
      <ul class="inline-flex -space-x-px text-sm">
        <li v-for="link in jobs.links" :key="link.url" :class="{ 'active': link.active }">
          <a v-if="link.url" @click.prevent="goToJob(link.url)"
            :class="link.active ? 'flex items-center justify-center px-3 h-8 text-blue-600 border border-gray-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700' : 'flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700'">
            <span v-html="link.label"></span>
          </a>
          <span v-else
            class="flex items-center justify-center px-3 h-8 leading-tight text-gray-400 bg-gray-200 border border-gray-300">
            <span v-html="link.label"></span>
          </span>
        </li>
      </ul>
    </nav>
  </div>
</template> 