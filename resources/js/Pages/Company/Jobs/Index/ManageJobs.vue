<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3'; // Ensure router is imported
import { usePage } from '@inertiajs/vue3'; // Ensure usePage is imported
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';



const page = usePage()

const props = defineProps({
  jobs: Array,
  job: Object,
});

console.log(props);

const showModal = ref(false);
const jobToArchive = ref(null);
const open = ref(false)

const archiveJob = () => {
    if (jobToArchive.value) {
        router.delete(route('company.jobs.delete', { job: jobToArchive.value.id }));
        showModal.value = false;
        jobToArchive.value = null;
    }
};

const confirmArchive = (job) => {
    jobToArchive.value = job;
    showModal.value = true;
};


// const archiveJob = (jobId) => {
//   router.post(route('jobs.archive', jobId), {}, {
//     onSuccess: () => console.log("Job archived!")
//   });
// };

const approveJob = (job) => {

  router.post(route('company.jobs.approve', { job: job.id }), {
    onSuccess: () => {
      console.log("Job approved!");
      location.reload(); // Reload the page to fetch updated data
    }
  });
};


const disapproveJob = (job) => {
  router.post(route('company.jobs.disapprove', { job: job.id }), {}, {
    onSuccess: () => {
      console.log("Job disapproved!");
      location.reload(); // Reload the page to fetch updated data
    }
  });
};

</script>

<template>
  <AppLayout title="Jobs">
    <template #header>
      Jobs
    </template>

    <Container class="py-4">
      <div class="flex space-x-2">
        <div class="mt-8">

          <Link :href="route('company.jobs', { user: page.props.auth.user.id })">
          <PrimaryButton class="mr-2">List of Jobs</PrimaryButton>
          </Link>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="mt-8 min-w-full bg-white border border-gray-200">
          <thead>
            <tr class="w-full bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
              <th class="py-2 px-4 text-left border">Job Title</th>
              <th class="py-2 px-4 text-left border">Location</th>
              <th class="py-2 px-4 text-left border">Employment Type</th>
              <th class="py-2 px-4 text-left border">Experience Level</th>
              <th class="py-2 px-4 text-left border">Status</th>
              <th class="py-2 px-4 text-left border">Action</th>
            </tr>
          </thead>
          <tbody class="text-gray-600 text-sm font-light">
            <tr v-for="job in jobs" :key="job.id" class="border-b border-gray-200 hover:bg-gray-100">
              <td class="border border-gray-200 px-6 py-4">{{ job.job_title }}</td>
              <td class="border border-gray-200 px-6 py-4">{{ job.location }}</td>
              <td class="border border-gray-200 px-6 py-4">{{ job.job_type }}</td>
              <td class="border border-gray-200 px-6 py-4">{{ job.experience_level }}</td>
              <td class="border border-gray-200 px-6 py-4">
                <span v-if="job.is_approved === 1" class="text-green-600 font-semibold">Approved</span>
                <span v-else-if="job.is_approved === 0" class="text-red-600 font-semibold">Disapproved</span>
                <span v-else class="text-yellow-600 font-semibold">Pending</span>
              </td>
              <td class="border border-gray-200 px-6 py-4">
                <PrimaryButton class="mr-2 mb-2" @click="approveJob(job)" v-if="page.props.roles.isPeso && !job.is_approved">Approve
                </PrimaryButton>
                <DangerButton class="mr-2" @click="disapproveJob(job)" v-if="page.props.roles.isPeso && !job.is_approved">
                  Disapprove
                </DangerButton>
                <Link :href="route('company.jobs.view', { job: job.id })">
                <PrimaryButton class="mr-2">View</PrimaryButton>
                </Link>
                <Link :href="route('company.jobs.edit', { job: job.id })">
                <PrimaryButton class="mr-2">Edit</PrimaryButton>
                </Link>
                <button @click="confirmArchive(job)">
                  <DangerButton class="mr-2">Archive</DangerButton>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <ConfirmationModal @close="showModal = false" :show="showModal">
                <template #title>
                    Are you sure?
                </template>

                <template #content>
                    Are you sure you want to archive this Job {{ jobs.job_title }}
                </template>

                <template #footer>
                    <DangerButton @click="archiveJob" class="mr-2">
                        Archive User
                    </DangerButton>
                    <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                </template>

            </ConfirmationModal>
    </Container>
  </AppLayout>
</template>