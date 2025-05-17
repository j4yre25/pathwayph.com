<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ref } from 'vue';

const page = usePage()
const props = defineProps({
  jobs: Array,
  
});

const goToJob = (jobId) => {
  router.get(route('company.job.applicants.show', { id: jobId }));
};

</script>

<template>
  
  <div class="overflow-x-auto">
    <table class="min-w-full bg-white border border-gray-300">
      <thead >
        <tr class="bg-blue-500 text-white uppercase text-sm leading-normal">
          <th class="py-3 px-4 text-left border">Job Title</th>
          <th class="py-3 px-4 text-left border">Employment Type</th>
          <th class="py-3 px-4 text-left border">Vacancies</th>
          <th class="py-3 px-4 text-left border">Status</th>
          <th class="py-3 px-4 text-left border">Applicants</th>
          <th class="py-3 px-4 text-left border">Action</th>
        </tr>
      </thead>
      <tbody class="text-gray-700 text-sm font-light">
        <tr 
          v-for="job in jobs" 
          :key="job.id" 
          @click="goToJob(job.id)"
          class="border-b border-gray-200 hover:bg-blue-100 even:bg-gray-50 odd:bg-white">
          <td class="border border-gray-300 px-6 py-4">{{ job.job_title }}</td>
          <td class="border border-gray-300 px-6 py-4">{{ job.job_type }}</td>
          <td class="border border-gray-300 px-6 py-4">{{ job.vacancy }}</td>
          <td class="border border-gray-300 px-6 py-4"> 
            <span v-if="job.is_approved === 1" class="text-green-600 font-semibold">Open</span>
            <span v-else-if="job.is_approved === 0" class="text-red-600 font-semibold">Closed</span>
            <span v-else class="text-yellow-600 font-semibold">Pending</span></td>
          <td class="border border-gray-300 px-6 py-4">{{ job.applications_count ?? 0 }}</td>
          <td class="border border-gray-300 px-6 py-4">
            <Link :href="`/jobs/${job.id}/applicants`" class="text-blue-600 hover:underline">
              View Applicants
            </Link>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>