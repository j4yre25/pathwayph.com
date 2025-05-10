<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3'; // Ensure router is imported

const page = usePage();
const props = defineProps({
  jobs: Object, // Paginated jobs object
  sectors: Array,
  categories: Array,
});

console.log(props.jobs)
const goTo = (url) => {
  if (url) {
    router.get(url); // Use Inertia's router to navigate to the provided URL
  }
};

const searchQuery = ref('');
const selectedSector = ref('');
const selectedCategory = ref('');

const filteredJobs = computed(() => {
  return props.jobs.data
    .filter(job => {
      const matchesSearch = job.job_title.toLowerCase().includes(searchQuery.value.toLowerCase());
      const matchesSector = selectedSector.value ? job.sector === selectedSector.value : true;
      const matchesCategory = selectedCategory.value ? job.category === selectedCategory.value : true;
      return matchesSearch && matchesSector && matchesCategory;
    })
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at)); // Sort by created_at in descending order
});

const archiveJob = (jobId) => {
  router.post(route('peso.jobs.archive', jobId), {}, {
    onSuccess: () => console.log("Job archived!")
  });
};

const approveJob = (job) => {
  router.post(route('peso.jobs.approve', { job: job.id }), {});
};

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
          <th class="py-2 px-4 text-left border">Status</th>
        </tr>
      </thead>
      <tbody class="text-gray-600 text-sm font-light">
        <tr v-for="job in filteredJobs" :key="job.id" @click="goToJob(job.id)"
          class="border-b border-gray-200 hover:bg-gray-100">
          <td class="border border-gray-200 px-6 py-4">{{ job.job_title }}</td>
          <td class="border border-gray-200 px-6 py-4">
            <template v-if="job.user">
              <template v-if="job.user.role === 'company'">
                {{ job.user.company_name }}
              </template>
              <template v-else-if="job.user.role === 'institution'">
                {{ job.user.institution_career_officer_first_name }} {{ job.user.institution_career_officer_last_name }}
              </template>
              <template v-else-if="job.user.role === 'peso'">
                {{ job.user.peso_first_name }} {{ job.user.peso_last_name }}
              </template>
              <template v-else>
                {{ job.user.name }}
              </template>
            </template>
            <template v-else>
              <span class="text-gray-500 italic">Unknown</span>
            </template>
          </td>
          <td class="border border-gray-200 px-6 py-4">{{ job.location }}</td>
          <td class="border border-gray-200 px-6 py-4">{{ job.job_type }}</td>
          <td class="border border-gray-200 px-6 py-4">{{ job.experience_level }}</td>
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
    <nav v-if="jobs.links.length > 1" aria-label="Page navigation">
      <ul class="inline-flex -space-x-px text-sm">
        <li v-for="link in jobs.links" :key="link.url" :class="{ 'active': link.active }">
          <a v-if="link.url" @click.prevent="goTo(link.url)"
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