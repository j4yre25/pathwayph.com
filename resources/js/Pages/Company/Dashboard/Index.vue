<template>
  <div class="p-6">
    <h1 class="text-2xl font-semibold mb-6">Company Dashboard</h1>

    <!-- Job Openings Overview Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <KpiCard 
        title="Total Job Openings" 
        :value="dashboardData.total_job_openings" 
        icon="briefcase"
      />
      <KpiCard 
        title="Active Listings" 
        :value="dashboardData.active_listings" 
        icon="list"
      />
      <KpiCard 
        title="Roles Filled" 
        :value="dashboardData.roles_filled" 
        icon="check-circle"
      />
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <ChartCard title="Job Type Distribution">
        <PieChart :data="dashboardData.job_type_distribution" />
      </ChartCard>
      <ChartCard title="Application Trend">
        <LineChart :data="dashboardData.application_trend_by_time" />
      </ChartCard>
    </div>

    <!-- Applicant Status Overview Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <KpiCard 
        title="Total Applications" 
        :value="dashboardData.total_applications" 
        icon="users"
      />
      <KpiCard 
        title="Shortlisted" 
        :value="dashboardData.shortlisted" 
        icon="user-check"
      />
      <KpiCard 
        title="Hired" 
        :value="dashboardData.hired" 
        icon="user-plus"
      />
    </div>
    <div class="mb-6">
      <ChartCard title="Applicant Status Distribution">
        <PieChart :data="dashboardData.applicant_status_distribution" />
      </ChartCard>
    </div>

    <!-- Graduate Pool Overview Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <KpiCard 
        title="Total Scouted" 
        :value="dashboardData.total_scouted" 
        icon="search"
      />
      <KpiCard 
        title="Matched" 
        :value="dashboardData.matched" 
        icon="check"
      />
      <KpiCard 
        title="Avg. Qualification" 
        :value="dashboardData.avg_qualification" 
        icon="award"
      />
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
      <ChartCard title="Graduates by Study Field">
        <PieChart :data="dashboardData.graduates_by_study_field" />
      </ChartCard>
      <ChartCard title="Average Stage Duration">
        <BarChart :data="dashboardData.average_stage_duration" />
      </ChartCard>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
// import KpiCard from '@/Components/Company/Dashboard/KpiCard.vue'
// import ChartCard from '@/Components/Company/Dashboard/ChartCard.vue'
// import PieChart from '@/Components/Company/Charts/PieChart.vue'
// import LineChart from '@/Components/Company/Charts/LineChart.vue'
// import BarChart from '@/Components/Company/Charts/BarChart.vue'

const dashboardData = ref({
  total_job_openings: 0,
  active_listings: 0,
  roles_filled: 0,
  job_type_distribution: [],
  total_applications: 0,
  shortlisted: 0,
  hired: 0,
  applicant_status_distribution: [],
  total_scouted: 0,
  matched: 0,
  avg_qualification: 0,
  graduates_by_study_field: [],
  application_trend_by_time: [],
  average_stage_duration: []
})

onMounted(async () => {
  try {
    const response = await fetch('/api/company/dashboard/stats')
    const data = await response.json()
    dashboardData.value = data
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
  }
})
</script> 