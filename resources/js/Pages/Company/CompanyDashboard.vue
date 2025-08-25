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

const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth(); // 0-based (0 = January)

// Generate month labels up to the current month
const monthLabels = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
].slice(0, currentMonth + 1);

// Map your data to match the months (fill missing months with 0)
const applicantData = monthLabels.map((month, idx) => {
    // Find the data for this month from props.applicationTrends
    const labelIdx = props.applicationTrends.labels.findIndex(l => l === month);
    return labelIdx !== -1 ? props.applicationTrends.data[labelIdx] : 0;
});

const trendOptions = computed(() => ({
    title: {
        text: `Job Hiring Trends for Applicants as of ${monthLabels[monthLabels.length - 1]} (${currentYear})`,
        left: 'center',
        textStyle: {
            fontSize: 18,
            fontWeight: 'bold',
            color: '#6366F1'
        }
    },
    xAxis: {
        type: 'category',
        name: 'Months',
        nameLocation: 'middle',
        nameGap: 30,
        data: monthLabels,
        axisLabel: { color: '#64748B', fontWeight: 'bold' }
    },
    yAxis: {
        type: 'value',
        name: 'Number of Applicants',
        nameLocation: 'middle',
        nameGap: 40,
        axisLabel: { color: '#64748B', fontWeight: 'bold' }
    },
    series: [{
        data: applicantData,
        type: 'line',
        smooth: true,
        itemStyle: {
            color: '#4F46E5'
        },
        lineStyle: {
            width: 4
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
    <div class="space-y-8">
        <!-- Main KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-xl shadow-sm border border-blue-100 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-blue-700 text-sm flex items-center font-semibold">
                            Active Jobs
                            <span class="ml-1" title="Number of jobs currently open for applications">
                                <i class="fas fa-info-circle text-xs text-blue-400 hover:text-blue-600"></i>
                            </span>
                        </h3>
                        <p class="text-3xl font-extrabold text-blue-900 mt-2">
                          {{ summary?.active_jobs ?? 0 }}
                        </p>
                    </div>
                    <div class="bg-blue-200 p-3 rounded-full">
                        <i class="fas fa-briefcase text-blue-700"></i>
                    </div>
                </div>
                <div class="flex items-center text-sm text-blue-600 mt-2">
                    <i class="fas fa-chart-line mr-2"></i>
                    <span>Currently accepting applications</span>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-xl shadow-sm border border-purple-100 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-purple-700 text-sm flex items-center font-semibold">
                            Total Applications
                            <span class="ml-1" title="Total number of applications received for all jobs">
                                <i class="fas fa-info-circle text-xs text-purple-400 hover:text-purple-600"></i>
                            </span>
                        </h3>
                        <p class="text-3xl font-extrabold text-purple-900 mt-2">
                          {{ summary?.total_applications ?? 0 }}
                        </p>
                    </div>
                    <div class="bg-purple-200 p-3 rounded-full">
                        <i class="fas fa-users text-purple-700"></i>
                    </div>
                </div>
                <div class="flex items-center text-sm text-purple-600 mt-2">
                    <i class="fas fa-clock mr-2"></i>
                    <span>{{ summary?.this_month_applications ?? 0 }} this month</span>
                </div>
            </div>

            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-6 rounded-xl shadow-sm border border-yellow-100 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-yellow-700 text-sm flex items-center font-semibold">
                            In Pipeline
                            <span class="ml-1" title="Applications currently being processed (applied, screening, interview, offer)">
                                <i class="fas fa-info-circle text-xs text-yellow-400 hover:text-yellow-600"></i>
                            </span>
                        </h3>
                        <p class="text-3xl font-extrabold text-yellow-900 mt-2">
                          {{ totalInPipeline }}
                        </p>
                    </div>
                    <div class="bg-yellow-200 p-3 rounded-full">
                        <i class="fas fa-stream text-yellow-700"></i>
                    </div>
                </div>
                <div class="flex items-center text-sm text-yellow-600 mt-2">
                    <i class="fas fa-user-clock mr-2"></i>
                    <span>{{ summary?.pipeline?.interview || 0 }} in interviews</span>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl shadow-sm border border-green-100 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-green-700 text-sm flex items-center font-semibold">
                            Total Hires
                            <span class="ml-1" title="Total number of applicants hired for all jobs">
                                <i class="fas fa-info-circle text-xs text-green-400 hover:text-green-600"></i>
                            </span>
                        </h3>
                        <p class="text-3xl font-extrabold text-green-900 mt-2">
                          {{ summary?.total_hires ?? 0 }}
                        </p>
                    </div>
                    <div class="bg-green-200 p-3 rounded-full">
                        <i class="fas fa-user-check text-green-700"></i>
                    </div>
                </div>
                <div class="flex items-center text-sm text-green-600 mt-2">
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
        <div class="bg-white p-6 rounded-xl shadow border border-gray-100 mt-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-700">Status Breakdown</h2>
                <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold shadow">
                    <i class="fas fa-calendar-alt mr-1"></i> {{ currentYear }}
                </span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="(count, status) in summary?.status_counts || {}"
                     :key="status"
                     class="bg-gradient-to-br from-gray-50 to-gray-100 p-4 rounded-lg border border-gray-100 flex flex-col items-center">
                    <h4 class="text-sm font-semibold text-gray-500 capitalize mb-2">{{ status }}</h4>
                    <p class="text-2xl font-bold text-gray-700">{{ count ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Application Trends Chart -->
        <div class="bg-white p-6 rounded-xl shadow border border-gray-100 mt-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-700">Application Trends</h2>
                
            </div>
            <VueECharts :option="trendOptions" style="height: 320px;" />
        </div>

        <!-- Recent Applications Table -->
        <div class="bg-white p-6 rounded-xl shadow border border-gray-100 mt-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-700">Recent Applications</h2>
                <span class="inline-flex items-center px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold shadow">
                    <i class="fas fa-calendar-alt mr-1"></i> {{ currentYear }}
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Applicant</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Position</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Applied Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="app in recentApplications" :key="app.id">
                            <td class="px-6 py-4 font-medium text-gray-700">{{ app.applicant_name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ app.position }}</td>
                            <td class="px-6 py-4">
                                <span :class="{
                                    'px-2 py-1 text-xs rounded-full font-semibold': true,
                                    'bg-yellow-100 text-yellow-800': app.status === 'pending',
                                    'bg-green-100 text-green-800': app.status === 'hired',
                                    'bg-red-100 text-red-800': app.status === 'rejected'
                                }">
                                    {{ app.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ app.applied_date }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Job Performance Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
            <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-700">Top Performing Jobs</h2>
                    <span class="inline-flex items-center px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-semibold shadow">
                        <i class="fas fa-calendar-alt mr-1"></i> {{ currentYear }}
                    </span>
                </div>
                <div class="space-y-4">
                    <div v-for="job in jobPerformance" :key="job.id"
                         class="flex items-center justify-between p-4 bg-gray-50 rounded-lg shadow">
                        <div>
                            <h3 class="font-semibold text-gray-700">{{ job.title }}</h3>
                            <p class="text-sm text-gray-500">{{ job.applications }} applications</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-indigo-600">{{ job.interview_rate }}%</p>
                            <p class="text-xs text-gray-500">Interview Rate</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-700">Hiring Pipeline</h2>
                    <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold shadow">
                        <i class="fas fa-calendar-alt mr-1"></i> {{ currentYear }}
                    </span>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 font-semibold">New Applications</span>
                        <span class="font-bold text-gray-700">{{ summary?.new_applications ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 font-semibold">Screening</span>
                        <span class="font-bold text-gray-700">{{ summary?.screening ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 font-semibold">Interview</span>
                        <span class="font-bold text-gray-700">{{ summary?.in_interview ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 font-semibold">Offer</span>
                        <span class="font-bold text-gray-700">{{ summary?.in_offer ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
