<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { router } from '@inertiajs/vue3';
import {inertia} from '@inertiajs/inertia';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  all_jobs: Array
});

const restoreJob = () => {
  console.log(route('jobs.restore', { job: jobToRestore.value.id })); // Log the generated URL
  router.post(route('jobs.restore', {
    job: jobToRestore.value.id
  }), {}, {
    onSuccess: () => {
      console.log('Job restored successfully!');
      showModal.value = false; // Close the modal after success
      inertia.visit(route('jobs', { user: jobToRestore.value.user_id }), {
        method: 'get',
        preserveState: true,
        preserveScroll: true,
      });
    }
  });
};

const confirmRestore = (job) => {
  jobToRestore.value = job;
  showModal.value = true;
};

const showModal = ref(false);
const jobToRestore = ref(null);
</script>

<template>
  <AppLayout title="Archived Jobs">
    <template #header>
      <div class="flex items-center">
        <Link :href="route('jobs', { user: $page.props.auth.user.id })" class="text-gray-500 hover:text-gray-700 mr-3">
          <i class="fas fa-chevron-left"></i>
        </Link>
        <i class="fas fa-archive text-amber-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Archived Jobs
        </h2>
      </div>
    </template>

    <Container class="py-8">
      <!-- Job Listings Header with Container -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6">
        <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center">
          <div class="flex items-center">
            <h3 class="text-lg font-semibold text-gray-800">Archived Job Listings</h3>
            <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ all_jobs.length }} jobs</span>
          </div>
        </div>
      </div>

      <!-- Jobs List -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <h3 class="text-lg font-semibold p-4 border-b border-gray-200">Archived Job Positions</h3>
        
        <!-- Job listings as table -->
        <div v-if="all_jobs && all_jobs.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Title</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employment Type</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Experience Level</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="job in all_jobs" :key="job.id" class="hover:bg-gray-50 transition-colors duration-150">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ job.job_title }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ job.job_type }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <div class="flex items-center">
                    <i class="fas fa-briefcase text-gray-400 mr-1"></i>
                    {{ job.experience_level }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    Archived
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button @click="confirmRestore(job)" class="text-indigo-600 hover:text-indigo-900 mr-2">
                    <i class="fas fa-undo mr-1"></i> Restore
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Empty state -->
        <div v-else class="p-6 text-center">
          <div class="flex flex-col items-center justify-center py-12">
            <i class="fas fa-folder-open text-gray-300 text-5xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-1">No archived jobs</h3>
            <p class="text-gray-500 mb-6">You don't have any archived job listings at the moment.</p>
            <Link :href="route('jobs', { user: $page.props.auth.user.id })" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              <i class="fas fa-arrow-left mr-2"></i>
              Back to Active Jobs
            </Link>
          </div>
        </div>
      </div>
    </Container>

    <!-- Confirmation Modal -->
    <ConfirmationModal :show="showModal" @close="showModal = false">
      <template #title>
        Restore Job
      </template>
      <template #content>
        Are you sure you want to restore this job? It will be moved back to your active job listings.
      </template>
      <template #footer>
        <SecondaryButton @click="showModal = false" class="mr-2">
          Cancel
        </SecondaryButton>
        <PrimaryButton @click="restoreJob">
          Restore Job
        </PrimaryButton>
      </template>
    </ConfirmationModal>
  </AppLayout>
</template>
