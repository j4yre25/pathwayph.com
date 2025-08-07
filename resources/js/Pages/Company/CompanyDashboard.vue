<script setup>
import { defineProps, computed } from "vue";
import VueECharts from "vue-echarts";

const props = defineProps({
    summary: {
        type: Object,
        required: true,
        default: () => ({
            active_jobs: 0,
            total_applications: 0,
            this_month_applications: 0,
            total_hires: 0,
            pipeline: {
                applied: 0,
                screening: 0,
                interview: 0,
                offer: 0
            },
            status_counts: {
                pending: 0,
                hired: 0,
                rejected: 0,
                declined: 0
            },
            new_applications: 0,
            screening: 0,
            in_interview: 0,
            in_offer: 0
        })
    },
    recentApplications: {
        type: Array,
        default: () => []
    },
    applicationTrends: {
        type: Object,
        default: () => ({
            labels: [],
            data: []
        })
    },
    jobPerformance: {
        type: Array,
        default: () => []
    }
});

console.log(props.summary)

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
            color: '#4F46E5'
        }
    }],
    tooltip: {
        trigger: 'axis'
    }
}));

const totalInPipeline = computed(() => {
    const pipeline = props.summary?.pipeline || {};
    return (pipeline.applied || 0) +
           (pipeline.screening || 0) +
           (pipeline.interview || 0) +
           (pipeline.offer || 0);
});
</script>

<template>
    <div class="space-y-6">
        <!-- Main KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Active Jobs -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-gray-500 text-sm flex items-center">
                            Active Jobs
                            <span class="ml-1" title="Number of jobs currently open for applications">
                                <i class="fas fa-info-circle text-xs text-gray-400 hover:text-blue-500"></i>
                            </span>
                        </h3>
                        <p class="text-2xl font-bold text-gray-900">
                          {{ summary?.active_jobs ?? 0 }}
                        </p>
                    </div>
                    <div class="bg-blue-50 p-3 rounded-full">
                        <i class="fas fa-briefcase text-blue-500"></i>
                    </div>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-chart-line mr-2"></i>
                    <span>Currently accepting applications</span>
                </div>
            </div>

            <!-- Total Applications -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-gray-500 text-sm flex items-center">
                            Total Applications
                            <span class="ml-1" title="Total number of applications received for all jobs">
                                <i class="fas fa-info-circle text-xs text-gray-400 hover:text-blue-500"></i>
                            </span>
                        </h3>
                        <p class="text-2xl font-bold text-gray-900">
                          {{ summary?.total_applications ?? 0 }}
                        </p>
                    </div>
                    <div class="bg-purple-50 p-3 rounded-full">
                        <i class="fas fa-users text-purple-500"></i>
                    </div>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-clock mr-2"></i>
                    <span>{{ summary?.this_month_applications ?? 0 }} this month</span>
                </div>
            </div>

            <!-- In Pipeline -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-gray-500 text-sm flex items-center">
                            In Pipeline
                            <span class="ml-1" title="Applications currently being processed (applied, screening, interview, offer)">
                                <i class="fas fa-info-circle text-xs text-gray-400 hover:text-blue-500"></i>
                            </span>
                        </h3>
                        <p class="text-2xl font-bold text-gray-900">
                          {{ totalInPipeline }}
                        </p>
                    </div>
                    <div class="bg-amber-50 p-3 rounded-full">
                        <i class="fas fa-stream text-amber-500"></i>
                    </div>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-user-clock mr-2"></i>
                    <span>{{ summary?.pipeline?.interview || 0 }} in interviews</span>
                </div>
            </div>

            <!-- Total Hires -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-gray-500 text-sm flex items-center">
                            Total Hires
                            <span class="ml-1" title="Total number of applicants hired for all jobs">
                                <i class="fas fa-info-circle text-xs text-gray-400 hover:text-blue-500"></i>
                            </span>
                        </h3>
                        <p class="text-2xl font-bold text-gray-900">
                          {{ summary?.total_hires ?? 0 }}
                        </p>
                    </div>
                    <div class="bg-green-50 p-3 rounded-full">
                        <i class="fas fa-user-check text-green-500"></i>
                    </div>
                </div>
                <div class="flex items-center text-sm text-gray-600">
                    <i class="fas fa-chart-pie mr-2"></i>
                    <span>
                        {{
                            summary?.total_applications
                                ? ((summary.total_hires / summary.total_applications) * 100).toFixed(1)
                                : 0
                        }}% hire rate
                    </span>
                </div>
            </div>
        </div>

        <!-- Status Breakdown -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div v-for="(count, status) in summary?.status_counts || {}"
                 :key="status"
                 class="bg-white p-4 rounded-lg border border-gray-100">
                <h4 class="text-sm font-medium text-gray-500 capitalize">{{ status }}</h4>
                <p class="text-2xl font-semibold mt-1">{{ count ?? 0 }}</p>
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
                        <span class="font-medium">{{ summary?.new_applications ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Screening</span>
                        <span class="font-medium">{{ summary?.screening ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Interview</span>
                        <span class="font-medium">{{ summary?.in_interview ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600">Offer</span>
                        <span class="font-medium">{{ summary?.in_offer ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
