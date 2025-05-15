<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Container from '@/Components/Container.vue'
import ListOfJobs from './ListOfJobs.vue'
import StatCard from './ListOfApplicants/StatsCard.vue'


const page = usePage()

const props = defineProps({
    jobs: Array,
    stats: {
        type: Object,
        default: () => ({
            this_month: 0,
            this_month_label: '',
            hired: 0,
            rejected: 0,
            interviews: 0,
            total_jobs: 0,
            total_applicants: 0,
        }),
    },
   
})

// Reactive search query
const searchQuery = ref('')

// Computed filtered jobs based on search query (case insensitive)
const filteredJobs = computed(() => {
  if (!searchQuery.value) return props.jobs
  return props.jobs.filter(job =>
    job.title.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})


</script>


<template>
    <AppLayout title="Manage Jobs">
        <template #header>
            Manage Applicants
        </template>

        <Container class="py-4">

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
                <StatCard label="Total Applicants" :value="stats.total_applicants" :subtitle="stats.this_month_label" color="purple" />
                <StatCard label="Total Hired" :value="stats.hired" :subtitle="stats.this_month_label" color="green" />
                <StatCard label="Total Rejected" :value="stats.rejected" :subtitle="stats.this_month_label" color="red" />
                <StatCard label="Total Interviews Scheduled" :value="stats.interviews" :subtitle="stats.this_month_label" color="yellow" />
                <StatCard label="Total Jobs" :value="stats.total_jobs" :subtitle="stats.this_month_label" color="indigo" />
            </div>
        
           
            <div class="flex items-center mt-6 mb-6 space-x-4">
                <!-- Summary Cards -->
                <!-- Search Input -->
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search jobs..."
                    class="border border-gray-300 rounded-lg px-4 py-2 shadow-sm focus:ring-blue-500 focus:border-blue-500 w-64"
                />
            </div>

            <div class="mt-8">
                <ListOfJobs :jobs="jobs" />
            </div>
        </Container>
    </AppLayout>
</template>