<script setup>
import { defineProps, onMounted, computed } from "vue";

const props = defineProps({
    summary: Object,
    recentApplications: Array,
    applicationTrends: Object,
    jobPerformance: Array,
});

// Chart options for Application Trends
const trendOptions = computed(() => ({
    title: {
        text: 'Application Trends',
        left: 'center'
    },
    xAxis: {
        type: 'category',
        data: props.applicationTrends?.labels || []
    },
    yAxis: {
        type: 'value'
    },
    series: [{
        data: props.applicationTrends?.data || [],
        type: 'line',
        smooth: true,
        itemStyle: {
            color: '#4F46E5' // Indigo-600 to match your theme
        }
    }],
    tooltip: {
        trigger: 'axis'
    }
}));
</script>

<template>
    <div class="space-y-6">
        <!-- Enhanced KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-gray-600">Total Job Postings</h3>
                        <p class="text-3xl font-bold text-indigo-600">
                            {{ summary.total_jobs }}
                        </p>
                    </div>
                    <div class="bg-indigo-100 p-3 rounded-full">
                        <i class="fas fa-briefcase text-indigo-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">Active job listings</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-gray-600">Applications</h3>
                        <p class="text-3xl font-bold text-green-600">
                            {{ summary.total_applications }}
                        </p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-users text-green-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">Total applications received</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-gray-600">Interviews</h3>
                        <p class="text-3xl font-bold text-yellow-600">
                            {{ summary.total_interviews }}
                        </p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-calendar-check text-yellow-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">Scheduled interviews</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-gray-600">Hires</h3>
                        <p class="text-3xl font-bold text-blue-600">
                            {{ summary.total_hires }}
                        </p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-user-check text-blue-600 text-xl"></i>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">Successfully hired</p>
            </div>
        </div>

        <!-- Application Trends Chart -->
        <div class="bg-white p-6 rounded-lg shadow">
            <VueECharts :option="trendOptions" style="height: 300px;" />
        </div>

        <!-- Recent Applications Table -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4">Recent Applications</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applicant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Position</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Applied Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="app in recentApplications" :key="app.id">
                            <td class="px-6 py-4">{{ app.applicant_name }}</td>
                            <td class="px-6 py-4">{{ app.position }}</td>
                            <td class="px-6 py-4">
                                <span :class="{
                                    'px-2 py-1 text-xs rounded-full': true,
                                    'bg-yellow-100 text-yellow-800': app.status === 'pending',
                                    'bg-green-100 text-green-800': app.status === 'hired',
                                    'bg-red-100 text-red-800': app.status === 'rejected'
                                }">
                                    {{ app.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ app.applied_date }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Job Performance Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold mb-4">Top Performing Jobs</h2>
                <div class="space-y-4">
                    <div v-for="job in jobPerformance" :key="job.id" 
                         class="flex items-center justify-between p-4 bg-gray-50 rounded">
                        <div>
                            <h3 class="font-medium">{{ job.title }}</h3>
                            <p class="text-sm text-gray-500">{{ job.applications }} applications</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium">{{ job.interview_rate }}%</p>
                            <p class="text-xs text-gray-500">Interview Rate</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold mb-4">Hiring Pipeline</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">New Applications</span>
                        <span class="font-medium">{{ summary.new_applications }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Screening</span>
                        <span class="font-medium">{{ summary.screening }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Interview</span>
                        <span class="font-medium">{{ summary.in_interview }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Offer</span>
                        <span class="font-medium">{{ summary.in_offer }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
