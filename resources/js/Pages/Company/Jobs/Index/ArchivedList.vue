<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import { ref, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { router } from '@inertiajs/vue3';
import {inertia} from '@inertiajs/inertia';



const props = defineProps({
  all_jobs: Array
});


onMounted(() => {
  console.log('All jobs:', props.all_jobs);
});
const restoreJob = () => {
  console.log(route('company.jobs.restore', { job: jobToRestore.value.id })); // Log the generated URL
  router.post(route('company.jobs.restore', {
    job: jobToRestore.value.id
  }), {}, {
    onSuccess: () => {
      console.log('Job restored successfully!');
      showModal.value = false; // Close the modal after success
      inertia.visit(route('company.jobs.index'), {
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

const goTo = (url) => {
    router.get(url); // Use Inertia's router to navigate to the next/previous page
};

const goBack = () => {
    window.history.back();
};

</script>


<template>
  <AppLayout title="Archived Jobs">
    <template #header>
      <div class="flex items-center">
       <button 
            @click="goBack"
            class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
            <i class="fas fa-chevron-left"></i>
        </button>
        <h2 class="font-semibold text-xl text-gray-800 flex items-center">
          <i class="fas fa-archive text-amber-500 text-xl mr-2"></i>
          Archived Jobs
        </h2>
      </div>
    </template>

    <Container class="py-6 space-y-6">
      <!-- Stats Card -->
      <div class="grid grid-cols-1 gap-6 mb-6">
        <div class="bg-gradient-to-br from-orange-100 to-orange-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-amber-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-archive text-white text-lg2"></i>
            </div>
            <h3 class="text-amber-700 text-sm font-medium mb-2">Total Archived Jobs</h3>
            <p class="text-2xl font-bold text-amber-900">{{ all_jobs.length }}</p>
          </div>
        </div>
      </div>

      <!-- Table Card -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
          <div class="flex items-center">
            <div class="flex items-center">
              <div class="w-10 h-10 bg-amber-500 rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-table text-white"></i>
              </div>
              <div>
                <h3 class="text-xl font-bold text-gray-800">Archived Jobs</h3>
                <p class="text-sm text-gray-600">Manage archived jobs</p>
                <span class="text-sm font-semibold text-gray-700">{{ all_jobs.length }} total</span>
              </div>
            </div>
          </div>
        </div>

        <div v-if="all_jobs && all_jobs.length > 0" class="overflow-x-auto">
          <table class="min-w-full table-auto">
            <thead class="bg-gradient-to-r from-amber-50 to-indigo-50 text-sm font-semibold text-gray-700">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                  <div class="flex items-center">Job Title</div>
                </th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                  <div class="flex items-center">Employment Type</div>
                </th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                  <div class="flex items-center">Experience Level</div>
                </th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                  <div class="flex items-center">Status</div>
                </th>
                <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                  <div class="flex items-center justify-end">Actions</div>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
              <tr v-for="job in all_jobs" :key="job.id" class="hover:bg-gradient-to-r hover:from-amber-50 hover:to-indigo-50 transition-all duration-200 group">
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="text-sm font-semibold text-gray-900">{{ job.job_title }}</div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                    v-if="job.job_types && job.job_types.length"
                  >
                    {{ job.job_types.map(type => type.type).join(', ') }}
                  </span>
                  <span v-else>-</span>
                </td>
                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-500">
                  <div class="flex items-center">
                    <i class="fas fa-briefcase text-gray-400 mr-1"></i>
                    {{ job.job_experience_level }}
                  </div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 shadow-sm">
                    <i class="fas fa-archive mr-1.5"></i> Archived
                  </span>
                </td>
                <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <button
                      @click="confirmRestore(job)"
                      class="bg-gradient-to-r from-amber-500 to-amber-600 text-white p-2 rounded-full hover:from-amber-600 hover:to-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md"
                      title="Restore"
                    >
                      <i class="fas fa-undo"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-else class="py-12 text-center">
          <i class="fas fa-archive text-gray-300 text-5xl mb-4"></i>
          <h3 class="text-lg font-medium text-gray-700">No archived jobs found</h3>
          <p class="text-gray-500 mt-1">When jobs are archived, they will appear here</p>
        </div>
      </div>

      <!-- Confirmation Modal -->
      <ConfirmationModal :show="showModal" @close="showModal = false">
        <template #title>
          <div class="flex items-center">
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-red-100 to-red-200 flex items-center justify-center mr-4 shadow-lg">
              <i class="fas fa-question-circle text-amber-500"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800">Confirm Restore</h3>
          </div>
        </template>
        <template #content>
          <p class="text-gray-600">
            Are you sure you want to restore the job
            <strong>"{{ jobToRestore && jobToRestore.job_title }}"</strong>? This will make the job visible again.
          </p>
        </template>
        <template #footer>
          <div class="flex justify-end space-x-4">
            <button
              @click="showModal = false"
              class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm transition-colors duration-200 flex items-center"
            >
              <i class="fas fa-times mr-2"></i> Cancel
            </button>
            <button
              @click="restoreJob"
              class="px-4 py-2 bg-amber-500 text-white rounded-md hover:bg-amber-600 text-sm transition-colors duration-200 flex items-center"
            >
              <i class="fas fa-undo mr-2"></i> Restore
            </button>
          </div>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>