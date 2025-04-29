<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import EmployersJobDetails from './EmployersJobDetails.vue'; // Adjust path if needed

defineProps({
  jobs: Array
});

const user = computed(() => usePage().props.auth.user);
const queryParams = new URLSearchParams(window.location.search);
const showDetails = queryParams.get('details'); // pass 'employer' to show

const isJobDetailsOpen = ref(false);
</script>


<template>
    <AppLayout title="Jobs">
      <template #header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Job Listings</h2>
      </template>
  
      <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
  
        <!-- Accordion-like section to show EmployersJobDetails -->
        <div v-if="showDetails === 'employer'">
            
          <div v-show="isJobDetailsOpen" id="job-details-body">
            <div class="p-5 border border-gray-200">
              <EmployersJobDetails :job="jobs[0]" :user="user" /> <!-- change index if needed -->
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  </template>
  