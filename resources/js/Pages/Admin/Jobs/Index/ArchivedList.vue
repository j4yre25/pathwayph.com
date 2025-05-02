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
  all_jobs: Object
});



const restoreJob = () => {
  console.log(route('peso.jobs.restore', { job: jobToRestore.value.id })); // Log the generated URL
  router.post(route('peso.jobs.restore', {
    job: jobToRestore.value.id
  }), {}, {
    onSuccess: () => {
      console.log('Job restored successfully!');
      showModal.value = false; // Close the modal after success
      inertia.visit(route('peso.jobs', { user: page.props.auth.user.id }), {
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
  if (url) {
    router.get(url);
  }
};

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
            <tr v-for="job in all_jobs.data" :key="job.id" class="border-b border-gray-200 hover:bg-gray-100">
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

      <div class="mt-4">
        <nav v-if="all_jobs.links.length > 1" aria-label="Page navigation">
          <ul class="inline-flex -space-x-px text-sm">
            <li v-for="link in all_jobs.links" :key="link.url" :class="{ 'active': link.active }">
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
