<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { router } from '@inertiajs/vue3';


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
      Archived Jobs

    </template>

    <Container class="py-16">


      <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
          <thead>
            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
              <th class="py-2 px-4 text-left border">Job Title</th>
              <th class="py-2 px-4 text-left border">Employment Type</th>
              <th class="py-2 px-4 text-left border">Experience Level</th>
              <th class="py-2 px-4 text-left border">Status</th>
              <th class="py-2 px-4 text-left border">Action</th>

            </tr>
          </thead>
          <tbody class="text-gray-600 text-sm font-light">
            <tr v-for="job in all_jobs" :key="job.id" class="border-b border-gray-200 hover:bg-gray-100">
              <td class="border border-gray-200 px-6 py-4">{{ job.job_title }}</td>
              <td class="border border-gray-200 px-6 py-4">{{ job.job_type }}</td>
              <td class="border border-gray-200 px-6 py-4">{{ job.experience_level }}</td>
              <td class="border border-gray-200 px-6 py-4">
              <span class="text-red-600 font-semibold">Archived</span>
              </td>
              <td class="border border-gray-200 px-6 py-4">
                <DangerButton class="mr-2" @click="confirmRestore(job)">Restore Job
                </DangerButton>

              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <ConfirmationModal :show="showModal" @close="showModal = false" @confirm="restoreJob">
        <template #title>
          Confirm Deletion
        </template>
        <template #content>
          Are you sure you want to restore this job?
        </template>
        <template #footer>
          <PrimaryButton @click="showModal = false" class="mr-2">Cancel</PrimaryButton>
          <DangerButton @click="restoreJob">Restore</DangerButton>
        </template>
      </ConfirmationModal>


    </Container>
  </AppLayout>




</template>
