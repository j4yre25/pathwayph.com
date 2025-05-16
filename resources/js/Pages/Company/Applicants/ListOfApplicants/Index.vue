<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed } from 'vue'
import Container from '@/Components/Container.vue'
import ListOfJobs from './ListOfApplicants.vue'
import StatCard from './StatsCard.vue'

const props = defineProps({
  job: {
    type: Object,
    required: true,
  },
  applications: {
    type: Array,
    default: () => [],
  },
  stats: {
    type: Object,
    default: () => ({
      total: 0,
      hired: 0,
      rejected: 0,
      declined: 0,
      interviews: 0,
      pending: 0,
    }),
  },
})

const searchQuery = ref('')

const filteredApplications = computed(() => {
  if (!searchQuery.value) return props.applications
  return props.applications.filter(application =>
    application.user.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})
</script>

<template>
  <AppLayout title="Manage Applicants">
    <template #header>
      Job Title: {{ job.job_title }}
    </template>

    <Container class="py-4">
      <div class="flex justify-center mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 max-w-7xl w-full mb-6">
          <StatCard label="Total Applicants" :value="stats.total" color="blue" />
          <StatCard label="Interviews" :value="stats.interviews" color="yellow" />
          <StatCard label="Hired" :value="stats.hired" color="green" />
          <StatCard label="Rejected by Company" :value="stats.rejected" color="red" />
          <StatCard label="Declined by Graduate" :value="stats.declined" color="indigo" />
          <StatCard label="Pending" :value="stats.pending" color="gray" />
        </div>
      </div>

      <div class="flex items-center mt-6 mb-6 space-x-4">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search applicants..."
          class="border border-gray-300 rounded-lg px-4 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500 w-64"
        />
      </div>

      <div class="mt-8">
        <ListOfJobs :jobs="filteredApplications" />
      </div>
    </Container>
  </AppLayout>
</template>
