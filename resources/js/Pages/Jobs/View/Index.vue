<script setup>
import { computed, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import EmployersJobDetails from '@/Pages/Jobs/View/EmployersJobDetails.vue';
import Container from '@/Components/Container.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  jobs: Object,
});

const user = computed(() => {
  return props.jobs.user;
});

const isJobDetailsOpen = reactive({
  value: false,
});

// We can remove the goBack function since it's now in the EmployersJobDetails component
</script>


<template>
  <AppLayout title="Job Details">
    <template #header>
      <div class="flex items-center">
        <i class="fas fa-briefcase text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Job Details
        </h2>
      </div>
    </template>

    <Container class="py-8">
      <!-- Job Details Content -->
      <div v-if="$page.props.query.showDetails && jobs.job" class="space-y-6">
        <EmployersJobDetails :job="jobs.job" :user="user" />
      </div>
      <div v-else class="bg-white rounded-lg border border-gray-200 p-8 text-center shadow-sm">
        <div class="flex flex-col items-center justify-center py-12">
          <i class="fas fa-search text-gray-400 text-5xl mb-4"></i>
          <h2 class="text-xl font-semibold text-gray-700 mb-2">No Job Selected</h2>
          <p class="text-gray-500 max-w-md">Please select a job from the job listings to view its details.</p>
        </div>
      </div>
    </Container>
  </AppLayout>
</template>
  