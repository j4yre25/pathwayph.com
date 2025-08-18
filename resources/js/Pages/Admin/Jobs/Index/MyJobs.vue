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


console.log('Jobs', props.jobs)
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
  <!-- Table Section -->
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm text-left text-gray-700">
        <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-600 font-medium">
          <tr>
            <th class="px-6 py-4 font-semibold">Job Title</th>
            <th class="px-6 py-4 font-semibold">Posted By</th>
            <th class="px-6 py-4 font-semibold">Location</th>
            <th class="px-6 py-4 font-semibold">Employment Type</th>
            <th class="px-6 py-4 font-semibold">Experience Level</th>
            <th class="px-6 py-4 font-semibold">Applicants</th>
            <th class="px-6 py-4 font-semibold">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="job in filteredJobs" :key="job.id" @click="goToJob(job.id)"
            class="hover:bg-gray-50 transition-colors cursor-pointer">
            <td class="px-6 py-4 font-medium">{{ job.job_title }}</td>
            <td class="px-6 py-4">
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
            <td class="px-6 py-4">{{ job.job_location }}</td>
            <td class="px-6 py-4">{{ job.job_employment_type }}</td>
            <td class="px-6 py-4">{{ job.job_experience_level }}</td>
            <td class="px-6 py-4">{{ job.applicants_count ?? 0 }}</td>
            <td class="px-6 py-4">
              <span 
                class="px-2 py-1 text-xs font-medium rounded-full" 
                :class="{
                  'bg-green-100 text-green-800': job.is_approved === 1,
                  'bg-red-100 text-red-800': job.is_approved === 0,
                  'bg-yellow-100 text-yellow-800': job.is_approved === null
                }">
                {{ job.is_approved === 1 ? 'Approved' : (job.is_approved === 0 ? 'Disapproved' : 'Pending') }}
              </span>
            </td>
          </tr>
          <tr v-if="filteredJobs.length === 0">
            <td colspan="7" class="px-6 py-8 text-center">
              <div class="flex flex-col items-center justify-center text-gray-500">
                <svg class="w-12 h-12 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-lg font-medium">No jobs found</p>
                <p class="text-sm">Try adjusting your search or filter criteria</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Pagination -->
  <div class="mt-6 flex justify-center">
    <nav v-if="jobs.links && jobs.links.length > 3" aria-label="Page navigation">
      <ul class="inline-flex items-center -space-x-px rounded-md shadow-sm">
        <li v-for="link in jobs.links" :key="link.url">
          <a v-if="link.url" @click.prevent="goTo(link.url)"
            :class="[
              'relative inline-flex items-center px-4 py-2 text-sm font-medium border',
              link.active
                ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
              link.label.includes('Previous') ? 'rounded-l-md' : '',
              link.label.includes('Next') ? 'rounded-r-md' : ''
            ]"
            :aria-current="link.active ? 'page' : undefined">
            <span v-html="link.label"></span>
          </a>
          <span v-else
            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 cursor-not-allowed">
            <span v-html="link.label"></span>
          </span>
        </li>
      </ul>
    </nav>
  </div>
</template>