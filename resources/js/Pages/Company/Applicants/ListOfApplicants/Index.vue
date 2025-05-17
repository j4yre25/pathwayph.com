<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed } from 'vue'
import Container from '@/Components/Container.vue'
import ListOfApplicants from './ListOfApplicants.vue'
import StatCard from '@/Components/StatsCard.vue'
import CandidatePipeline from '@/Components/CandidatePipeline.vue'

const props = defineProps({
  job: {
    type: Object,
    required: true,
  },
  applicants: {
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
  if (!searchQuery.value) return props.applicants
  return props.applicants.filter(application =>
    application.name.toLowerCase().includes(searchQuery.value.toLowerCase())
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
        <div
          class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 max-w-7xl w-full">
          <StatCard label="Applicants" :value="stats.total" color="blue" />
          <StatCard label="Pending" :value="stats.pending" color="gray" />
          <StatCard label="Interviews" :value="stats.interviews" color="yellow" />
          <StatCard label="Hired" :value="stats.hired" color="green" />
          <StatCard label="Rejected" :value="stats.rejected" color="red" />
          <StatCard label="Declined by Graduate" :value="stats.declined" color="indigo" />
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
        <h2 class="text-xl font-bold mb-4">List of Applicants</h2>
        <ListOfApplicants :applicants="applicants" />
      </div>
    </Container>
  </AppLayout>
</template>
