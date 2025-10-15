<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch, onMounted, computed } from 'vue'
import VueECharts from 'vue-echarts';
import { usePage } from '@inertiajs/vue3'
import Container from '@/Components/Container.vue';
import axios from 'axios'

// --- Filter state ---
const selectedCompany = ref('')
const selectedRole = ref('')
const selectedSource = ref('')



const reportTypes = [
    { value: 'referralsuccess', label: 'Referral Success Rate' },
    { value: 'trends', label: 'Referral Trends Over Time' },
    { value: 'performance', label: 'Referral Performance by Role' },
]
const selectedReport = ref(null)

// --- Static filter options from page.props ---
const page = usePage()
const companies = ref(page.props.companies ?? [])
const roles = ref(page.props.roles ?? [])
const sources = ref(page.props.sources ?? [])
const dateFrom = ref('')
const dateTo = ref('')


// --- Analytics data (fetched asynchronously) ---
const analyticsData = ref({})
const loading = ref(false)

// --- Fetch analytics data from backend ---
function fetchAnalyticsData() {
    console.log('fetchAnalyticsData called');

    loading.value = true
    axios.get(route('peso.reports.referral.data'), {
        params: {
            company_id: selectedCompany.value,
            role: selectedRole.value,
            source: selectedSource.value,
            date_from: dateFrom.value,
            date_to: dateTo.value,
        }
    }).then(res => {
        analyticsData.value = res.data
        console.log('Referral Data:', res.data);
    }).finally(() => {
        loading.value = false
    })
}

const currentPage = ref(1);
const pageSize = ref(10);

const paginatedGraduateTableData = computed(() => {
    const data = graduateTableData.value;
    const start = (currentPage.value - 1) * pageSize.value;
    return data.slice(start, start + pageSize.value);
});
const totalPages = computed(() =>
    Math.ceil(graduateTableData.value.length / pageSize.value)
);

const tableStageFilter = ref(''); // '' means show all
const graduateTableData = computed(() => {
    // analyticsData.value.graduates should be an array of { name, stage, ... }
    if (!analyticsData.value.graduates) return [];
    if (!tableStageFilter.value) return analyticsData.value.graduates;
    return analyticsData.value.graduates.filter(g => g.stage === tableStageFilter.value);
});
const referralStages = [
    { value: '', label: 'All Stages' },
    { value: 'referred', label: 'Referred' },
    { value: 'applied', label: 'Applied' },
    { value: 'screened', label: 'Screened' },
    { value: 'interviewed', label: 'Interviewed' },
    { value: 'offered', label: 'Offered' },
    { value: 'hired', label: 'Hired' },
    { value: 'rejected', label: 'Rejected' },
];

watch([tableStageFilter, graduateTableData], () => {
    currentPage.value = 1;
});

// Fetch on mount and when filters change
onMounted(fetchAnalyticsData)
watch([selectedCompany, selectedRole, selectedSource, dateFrom, dateTo], fetchAnalyticsData)

// --- Chart options ---
const funnelOption = computed(() => ({
    tooltip: { trigger: 'item', formatter: '{b}: {c}' },
    series: [{
        type: 'funnel',
        data: analyticsData.value.funnelData ?? [],
        label: { show: true, formatter: '{b}: {c}' }
    }]
}))

const barSuccessOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    xAxis: { type: 'category', data: (analyticsData.value.barSuccessData ?? []).map(d => d.source) },
    yAxis: { type: 'value' },
    series: [{
        name: 'Success',
        type: 'bar',
        data: (analyticsData.value.barSuccessData ?? []).map(d => d.success),
        itemStyle: { color: '#3b82f6' }
    }]
}))

const pieSourceOption = computed(() => ({
    tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
    series: [{
        type: 'pie',
        radius: '60%',
        data: analyticsData.value.pieSourceData ?? [],
        label: { formatter: '{b}: {d}%', color: '#374151', fontWeight: 'bold' }
    }]
}))

const treemapSourceOption = computed(() => ({
    tooltip: { trigger: 'item', formatter: '{b}: {c}' },
    series: [{
        type: 'treemap',
        data: analyticsData.value.treemapSourceData ?? [],
        label: { show: true, formatter: '{b}' }
    }]
}))

const lineTrendOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    xAxis: { type: 'category', data: (analyticsData.value.lineTrendData ?? []).map(d => d.month) },
    yAxis: { type: 'value' },
    series: [{
        name: 'Referrals',
        type: 'line',
        data: (analyticsData.value.lineTrendData ?? []).map(d => d.count),
        itemStyle: { color: '#10b981' }
    }]
}))

const areaTrendOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    xAxis: { type: 'category', data: (analyticsData.value.areaTrendData ?? []).map(d => d.month) },
    yAxis: { type: 'value' },
    series: [{
        name: 'Referrals',
        type: 'line',
        areaStyle: {},
        data: (analyticsData.value.areaTrendData ?? []).map(d => d.count),
        itemStyle: { color: '#f59e42' }
    }]
}))

const bubbleOption = computed(() => ({
    tooltip: { trigger: 'item', formatter: '{b}: {c}' },
    xAxis: { type: 'category', data: (analyticsData.value.bubbleData ?? []).map(d => d.name) },
    yAxis: { type: 'value' },
    series: [{
        name: 'Influence',
        type: 'scatter',
        symbolSize: d => d.value * 10,
        data: analyticsData.value.bubbleData ?? [],
        itemStyle: { color: '#6366f1' }
    }]
}))

const networkGraphOption = computed(() => ({
    tooltip: { trigger: 'item' },
    series: [{
        type: 'graph',
        layout: 'force',
        roam: true,
        data: (analyticsData.value.networkNodes ?? []).map(name => ({ name })),
        links: analyticsData.value.networkLinks ?? [],
        label: { show: true, position: 'right' },
        force: { repulsion: 100, edgeLength: 80 }
    }]
}));

const roleStatsOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    legend: { data: ['Referred', 'Hired', 'Screened', 'Interviewed'] },
    xAxis: { type: 'category', data: (analyticsData.value.roleStats ?? []).map(d => d.role) },
    yAxis: { type: 'value' },
    series: [
        { name: 'Referred', type: 'bar', data: (analyticsData.value.roleStats ?? []).map(d => d.referred), itemStyle: { color: '#22c55e' } },
        { name: 'Hired', type: 'bar', data: (analyticsData.value.roleStats ?? []).map(d => d.hired), itemStyle: { color: '#ef4444' } },
        { name: 'Screened', type: 'bar', data: (analyticsData.value.roleStats ?? []).map(d => d.screened), itemStyle: { color: '#3b82f6' } },
        { name: 'Interviewed', type: 'bar', data: (analyticsData.value.roleStats ?? []).map(d => d.interviewed), itemStyle: { color: '#f59e42' } }
    ]
}))

const stackedRoleStatsOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    legend: { data: ['Applied', 'Screened', 'Interviewed', 'Offered', 'Hired', 'Rejected'] },
    xAxis: { type: 'category', data: (analyticsData.value.roleStats ?? []).map(d => d.role) },
    yAxis: { type: 'value' },
    series: [
        { name: 'Referred', type: 'bar', stack: 'total', data: (analyticsData.value.roleStats ?? []).map(d => d.referred) },
        { name: 'Applied', type: 'bar', stack: 'total', data: (analyticsData.value.roleStats ?? []).map(d => d.applied) },
        { name: 'Screened', type: 'bar', stack: 'total', data: (analyticsData.value.roleStats ?? []).map(d => d.screened) },
        { name: 'Interviewed', type: 'bar', stack: 'total', data: (analyticsData.value.roleStats ?? []).map(d => d.interviewed) },
        { name: 'Offered', type: 'bar', stack: 'total', data: (analyticsData.value.roleStats ?? []).map(d => d.offered) },
        { name: 'Hired', type: 'bar', stack: 'total', data: (analyticsData.value.roleStats ?? []).map(d => d.hired) },
        { name: 'Rejected', type: 'bar', stack: 'total', data: (analyticsData.value.roleStats ?? []).map(d => d.rejected) },
    ]
}));

const histogramOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    xAxis: { type: 'category', data: (analyticsData.value.histogramData ?? []) },
    yAxis: { type: 'value' },
    series: [{
        name: 'Bonuses',
        type: 'bar',
        data: (analyticsData.value.histogramData ?? []),
        itemStyle: { color: '#f59e42' }
    }]
}))

const matchDetailsWordCloudOption = computed(() => ({
    tooltip: {
        show: true,
        formatter: params => `${params.name}<br/>Count: ${params.value}`
    },
    series: [{
        type: 'wordCloud',
        shape: 'circle',
        left: 'center',
        top: 'center',
        width: '100%',
        height: '100%',
        sizeRange: [16, 60],
        rotationRange: [-90, 90],
        gridSize: 8,
        textStyle: {
            fontFamily: 'sans-serif',
            fontWeight: 'bold',
            color: () => '#' + Math.floor(Math.random() * 16777215).toString(16)
        },
        data: matchDetailsWordCloudData.value // [{name, value}]
    }]
}));

const matchDetailsWordCloudData = computed(() =>
    Object.entries(analyticsData.value.matchDetailsWordCloud ?? {}).map(([name, value]) => ({ name, value }))
);


const stackedColumnOption = computed(() => {
    const buckets = (analyticsData.value.stackedColumnData ?? []).map(d => d.bucket);
    // Collect all unique feedbacks
    const allFeedbacks = Array.from(
        new Set(
            (analyticsData.value.stackedColumnData ?? []).flatMap(d => Object.keys(d.feedbacks))
        )
    );
    // Build series for each feedback
    const series = allFeedbacks.map(feedback => ({
        name: feedback,
        type: 'bar',
        stack: 'total',
        data: (analyticsData.value.stackedColumnData ?? []).map(d => d.feedbacks[feedback] ?? 0),
    }));
    return {
        tooltip: { trigger: 'axis' },
        legend: { data: allFeedbacks },
        xAxis: { type: 'category', data: buckets },
        yAxis: { type: 'value' },
        series,
    };
});


// Funnel Analytics Summary
const funnelSummary = computed(() => {
    const funnel = analyticsData.value.funnelData ?? [];
    let summary = "<ul class='list-disc ml-6 mb-2'>";
    if (funnel.length) {
        funnel.forEach((stage, idx) => {
            const label = stage.name ?? `Stage ${idx + 1}`;
            summary += `<li>${label}: <strong>${stage.value ?? stage.count ?? 0}</strong></li>`;
        });
        const hired = funnel.find(f => (f.name ?? '').toLowerCase() === 'hired')?.value ?? 0;
        const total = funnel[0]?.value ?? 0;
        if (total) {
            const rate = ((hired / total) * 100).toFixed(1);
            summary += `<li>Overall referral-to-hire rate: <strong>${rate}%</strong></li>`;
        }
    } else {
        summary += "<li>No funnel data available.</li>";
    }
    summary += "</ul>";
    return summary;
});

// Referral Trends Summary
const trendsSummary = computed(() => {
    const line = analyticsData.value.lineTrendData ?? [];
    let summary = "<ul class='list-disc ml-6 mb-2'>";
    if (line.length) {
        const first = line[0]?.count ?? 0;
        const last = line[line.length - 1]?.count ?? 0;
        if (last > first) {
            summary += `<li>Referrals increased from <strong>${first}</strong> to <strong>${last}</strong> over time.</li>`;
        } else if (last < first) {
            summary += `<li>Referrals decreased from <strong>${first}</strong> to <strong>${last}</strong> over time.</li>`;
        } else {
            summary += `<li>Referrals remained stable at <strong>${first}</strong>.</li>`;
        }
    } else {
        summary += "<li>No referral trend data available.</li>";
    }
    summary += "</ul>";
    return summary;
});

// Referral Performance by Role Summary
const performanceSummary = computed(() => {
    const stats = analyticsData.value.roleStats ?? [];
    let summary = "<ul class='list-disc ml-6 mb-2'>";
    if (stats.length) {
        const topRole = stats.reduce((a, b) => (b.hired > a.hired ? b : a));
        summary += `<li>Role with most hires: <strong>${topRole.role}</strong> (${topRole.hired} hired)</li>`;
        const topReferred = stats.reduce((a, b) => (b.referred > a.referred ? b : a));
        summary += `<li>Most referred role: <strong>${topReferred.role}</strong> (${topReferred.referred} referrals)</li>`;
    } else {
        summary += "<li>No role performance data available.</li>";
    }
    summary += "</ul>";
    return summary;
});

</script>

<template>
    <AppLayout title="Referral Analytics">
        <Container class="py-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Referral Analytics Overview</h2>
            <!-- FILTER CONTROLS -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-filter text-blue-500 mr-2"></i>
                    Filter Reports
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Company</label>
                        <select v-model="selectedCompany"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                            <option value="">All Companies</option>
                            <option v-for="company in companies" :key="company.id" :value="company.id">{{
                                company.company_name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                        <select v-model="selectedRole"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                            <option value="">All Roles</option>
                            <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input v-model="dateFrom" type="date"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input v-model="dateTo" type="date"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                        <select v-model="selectedReport"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                            <option v-for="type in reportTypes" :key="type.value" :value="type.value">{{ type.label }}
                            </option>
                        </select>
                    </div>


                </div>
            </div>
            <!-- Loading Spinner -->
            <div v-if="loading" class="flex justify-center items-center py-12">
                <svg class="animate-spin h-8 w-8 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
                <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
            </div>
            <!-- KPI Cards -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-10">
                <div
                    class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500 hover:shadow-md transition-shadow duration-200">
                    <h3 class="text-gray-600 text-sm font-medium mb-2">Total Referrals</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ analyticsData.totalReferrals ?? 0 }}</p>
                </div>
                <div
                    class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500 hover:shadow-md transition-shadow duration-200">
                    <h3 class="text-gray-600 text-sm font-medium mb-2">Screened</h3>
                    <p class="text-3xl font-bold text-green-600">{{ analyticsData.funnelData?.[2]?.value ?? 0 }}</p>
                </div>
                <div
                    class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500 hover:shadow-md transition-shadow duration-200">
                    <h3 class="text-gray-600 text-sm font-medium mb-2">Interviewed</h3>
                    <p class="text-3xl font-bold text-yellow-600">{{ analyticsData.funnelData?.[3]?.value ?? 0 }}</p>
                </div>
                <div
                    class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500 hover:shadow-md transition-shadow duration-200">
                    <h3 class="text-gray-600 text-sm font-medium mb-2">Hired</h3>
                    <p class="text-3xl font-bold text-red-600">{{ analyticsData.funnelData?.[5]?.value ?? 0 }}</p>
                </div>
            </div>


            <div v-if="!loading" class="mb-6">
                <h2 class="text-xl font-bold text-blue-700">
                    {{
                        reportTypes.find(type => type.value === selectedReport)?.label
                        || 'Referral Analytics'
                    }}
                </h2>
            </div>

            <div v-if="!loading && selectedReport === null" class="mb-6">
                <h2 class="text-xl font-bold text-blue-700">Please select a report type.</h2>
            </div>


            <!-- Referral Success Rate -->
            <div v-if="selectedReport === 'referralsuccess' && !loading" class="bg-white rounded-xl shadow p-8 mb-10">

                <div class="bg-white rounded-2xl shadow border border-gray-200 mb-10 w-full">
                    <!-- Filter Bar -->
                    <div
                        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-700">Stage</label>
                            <select v-model="tableStageFilter"
                                class="material-select block w-40 px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                <option v-for="stage in referralStages" :key="stage.value" :value="stage.value">
                                    {{ stage.label }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-700">Rows per page</label>
                            <select v-model="pageSize"
                                class="material-select block w-20 px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                            </select>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-800">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-4 py-3 text-left font-semibold tracking-wide">Graduate Name</th>
                                    <th class="px-4 py-3 text-left font-semibold tracking-wide">Stage</th>
                                    <th class="px-4 py-3 text-left font-semibold tracking-wide">Role</th>
                                    <th class="px-4 py-3 text-left font-semibold tracking-wide">Company</th>
                                    <th class="px-4 py-3 text-left font-semibold tracking-wide">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="grad in paginatedGraduateTableData" :key="grad.id"
                                    class="hover:bg-blue-50 transition">
                                    <td class="px-4 py-3">{{ grad.name }}</td>
                                    <td class="px-4 py-3 capitalize">{{ grad.stage }}</td>
                                    <td class="px-4 py-3">{{ grad.role }}</td>
                                    <td class="px-4 py-3">{{ grad.company }}</td>
                                    <td class="px-4 py-3">{{ grad.date }}</td>
                                </tr>
                                <tr v-if="paginatedGraduateTableData.length === 0">
                                    <td colspan="5" class="px-4 py-3 text-gray-400 text-center">No graduates found for
                                        this stage.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination Controls -->
                    <div
                        class="flex flex-col md:flex-row md:items-center md:justify-between px-6 py-4 border-t border-gray-100 bg-gray-50">
                        <div class="text-sm text-gray-600 mb-2 md:mb-0">
                            Page <span class="font-semibold text-blue-600">{{ currentPage }}</span> of <span
                                class="font-semibold text-blue-600">{{ totalPages }}</span>
                        </div>
                        <div class="flex gap-2">
                            <button
                                class="material-btn px-4 py-2 rounded-lg bg-blue-50 text-blue-700 font-medium shadow hover:bg-blue-100 disabled:opacity-50 transition"
                                :disabled="currentPage === 1" @click="currentPage--">
                                Prev
                            </button>
                            <button
                                class="material-btn px-4 py-2 rounded-lg bg-blue-50 text-blue-700 font-medium shadow hover:bg-blue-100 disabled:opacity-50 transition"
                                :disabled="currentPage === totalPages || totalPages === 0" @click="currentPage++">
                                Next
                            </button>
                        </div>
                    </div>
                </div>
                <h3 class="text-lg font-semibold mb-6 text-gray-700">Funnel Analytics</h3>
                <template v-if="(analyticsData.funnelData ?? []).length">
                    <VueECharts :option="funnelOption" style="height: 400px; width: 100%;" />
                </template>
                <template v-else>
                    <div class="text-gray-400 text-center py-8">No funnel data available.</div>
                </template>



                <!-- Analytics Summary Card for Funnel -->
                <div class="mt-8 flex justify-center">
                    <div class="w-full md:w-2/3 lg:w-1/2 shadow-lg rounded-xl bg-white border border-gray-200">
                        <div class="flex items-center px-6 pt-6 pb-2">
                            <svg class="w-6 h-6 text-blue-500 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 3v18h18M9 17V9m4 8V5m4 12v-6" />
                            </svg>
                            <span class="text-lg font-semibold text-gray-800">Analytics Summary</span>
                        </div>
                        <div class="px-6 pb-6 text-gray-700 leading-relaxed">
                            <span v-html="funnelSummary"></span>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Referral Trends Over Time -->
            <div v-if="selectedReport === 'trends' && !loading">
                <div class="bg-white rounded-2xl shadow border border-gray-200 mb-10 w-full">
                    <!-- Filter Bar -->
                    <div
                        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-700">Stage</label>
                            <select v-model="tableStageFilter"
                                class="material-select block w-40 px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                <option v-for="stage in referralStages" :key="stage.value" :value="stage.value">
                                    {{ stage.label }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-700">Rows per page</label>
                            <select v-model="pageSize"
                                class="material-select block w-20 px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                            </select>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-800">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-4 py-3 text-left font-semibold tracking-wide">Graduate Name</th>
                                    <th class="px-4 py-3 text-left font-semibold tracking-wide">Stage</th>
                                    <th class="px-4 py-3 text-left font-semibold tracking-wide">Role</th>
                                    <th class="px-4 py-3 text-left font-semibold tracking-wide">Company</th>
                                    <th class="px-4 py-3 text-left font-semibold tracking-wide">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="grad in paginatedGraduateTableData" :key="grad.id"
                                    class="hover:bg-blue-50 transition">
                                    <td class="px-4 py-3">{{ grad.name }}</td>
                                    <td class="px-4 py-3 capitalize">{{ grad.stage }}</td>
                                    <td class="px-4 py-3">{{ grad.role }}</td>
                                    <td class="px-4 py-3">{{ grad.company }}</td>
                                    <td class="px-4 py-3">{{ grad.date }}</td>
                                </tr>
                                <tr v-if="paginatedGraduateTableData.length === 0">
                                    <td colspan="5" class="px-4 py-3 text-gray-400 text-center">No graduates found
                                        for this
                                        stage.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination Controls -->
                    <div
                        class="flex flex-col md:flex-row md:items-center md:justify-between px-6 py-4 border-t border-gray-100 bg-gray-50">
                        <div class="text-sm text-gray-600 mb-2 md:mb-0">
                            Page <span class="font-semibold text-blue-600">{{ currentPage }}</span> of <span
                                class="font-semibold text-blue-600">{{ totalPages }}</span>
                        </div>
                        <div class="flex gap-2">
                            <button
                                class="material-btn px-4 py-2 rounded-lg bg-blue-50 text-blue-700 font-medium shadow hover:bg-blue-100 disabled:opacity-50 transition"
                                :disabled="currentPage === 1" @click="currentPage--">
                                Prev
                            </button>
                            <button
                                class="material-btn px-4 py-2 rounded-lg bg-blue-50 text-blue-700 font-medium shadow hover:bg-blue-100 disabled:opacity-50 transition"
                                :disabled="currentPage === totalPages || totalPages === 0" @click="currentPage++">
                                Next
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow p-8 mb-10">
                    <h3 class="text-lg font-semibold mb-6 text-gray-700">Line Chart</h3>
                    <VueECharts :option="lineTrendOption" style="height: 400px; width: 100%;" />
                </div>
                <div class="bg-white rounded-xl shadow p-8 mb-10">
                    <h3 class="text-lg font-semibold mb-6 text-gray-700">Area Chart</h3>
                    <VueECharts :option="areaTrendOption" style="height: 400px; width: 100%;" />
                </div>

                <div class="mt-8 flex justify-center">
                    <div class="w-full md:w-2/3 lg:w-1/2 shadow-lg rounded-xl bg-white border border-gray-200">
                        <div class="flex items-center px-6 pt-6 pb-2">
                            <svg class="w-6 h-6 text-green-500 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 3v18h18M9 17V9m4 8V5m4 12v-6" />
                            </svg>
                            <span class="text-lg font-semibold text-gray-800">Analytics Summary</span>
                        </div>
                        <div class="px-6 pb-6 text-gray-700 leading-relaxed">
                            <span v-html="trendsSummary"></span>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Referral Performance by Role -->
            <div v-if="selectedReport === 'performance' && !loading" class="bg-white rounded-xl shadow p-8 mb-10">

                <div class="bg-white rounded-2xl shadow border border-gray-200 mb-10 w-full">
                    <!-- Filter Bar -->
                    <div
                        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-700">Stage</label>
                            <select v-model="tableStageFilter"
                                class="material-select block w-40 px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                <option v-for="stage in referralStages" :key="stage.value" :value="stage.value">
                                    {{ stage.label }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-700">Rows per page</label>
                            <select v-model="pageSize"
                                class="material-select block w-20 px-3 py-2 rounded-lg border border-gray-300 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                            </select>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-800">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-4 py-3 text-left font-semibold tracking-wide">Graduate Name</th>
                                    <th class="px-4 py-3 text-left font-semibold tracking-wide">Stage</th>
                                    <th class="px-4 py-3 text-left font-semibold tracking-wide">Role</th>
                                    <th class="px-4 py-3 text-left font-semibold tracking-wide">Company</th>
                                    <th class="px-4 py-3 text-left font-semibold tracking-wide">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="grad in paginatedGraduateTableData" :key="grad.id"
                                    class="hover:bg-blue-50 transition">
                                    <td class="px-4 py-3">{{ grad.name }}</td>
                                    <td class="px-4 py-3 capitalize">{{ grad.stage }}</td>
                                    <td class="px-4 py-3">{{ grad.role }}</td>
                                    <td class="px-4 py-3">{{ grad.company }}</td>
                                    <td class="px-4 py-3">{{ grad.date }}</td>
                                </tr>
                                <tr v-if="paginatedGraduateTableData.length === 0">
                                    <td colspan="5" class="px-4 py-3 text-gray-400 text-center">No graduates found for
                                        this stage.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination Controls -->
                    <div
                        class="flex flex-col md:flex-row md:items-center md:justify-between px-6 py-4 border-t border-gray-100 bg-gray-50">
                        <div class="text-sm text-gray-600 mb-2 md:mb-0">
                            Page <span class="font-semibold text-blue-600">{{ currentPage }}</span> of <span
                                class="font-semibold text-blue-600">{{ totalPages }}</span>
                        </div>
                        <div class="flex gap-2">
                            <button
                                class="material-btn px-4 py-2 rounded-lg bg-blue-50 text-blue-700 font-medium shadow hover:bg-blue-100 disabled:opacity-50 transition"
                                :disabled="currentPage === 1" @click="currentPage--">
                                Prev
                            </button>
                            <button
                                class="material-btn px-4 py-2 rounded-lg bg-blue-50 text-blue-700 font-medium shadow hover:bg-blue-100 disabled:opacity-50 transition"
                                :disabled="currentPage === totalPages || totalPages === 0" @click="currentPage++">
                                Next
                            </button>
                        </div>
                    </div>
                </div>
                <div v-if="selectedReport === 'performance' && !loading" class="bg-white rounded-xl shadow p-8 mb-10">
                    <h3 class="text-lg font-semibold mb-6 text-gray-700">Clustered Bar Chart</h3>
                    <VueECharts :option="roleStatsOption" style="height: 400px; width: 100%;" />
                </div>

                <div v-if="selectedReport === 'performance' && !loading" class="bg-white rounded-xl shadow p-8 mb-10">
                    <h3 class="text-lg font-semibold mb-6 text-gray-700">Stacked Column Chart</h3>
                    <VueECharts :option="stackedRoleStatsOption" style="height: 400px; width: 100%;" />
                </div>



                <div class="mt-8 flex justify-center">
                    <div class="w-full md:w-2/3 lg:w-1/2 shadow-lg rounded-xl bg-white border border-gray-200">
                        <div class="flex items-center px-6 pt-6 pb-2">
                            <svg class="w-6 h-6 text-purple-500 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 3v18h18M9 17V9m4 8V5m4 12v-6" />
                            </svg>
                            <span class="text-lg font-semibold text-gray-800">Analytics Summary</span>
                        </div>
                        <div class="px-6 pb-6 text-gray-700 leading-relaxed">
                            <span v-html="performanceSummary"></span>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Reason for Referral Success -->
            <div v-if="selectedReport === 'reasons' && !loading" class="bg-white rounded-xl shadow p-8 mb-10">
                <h3 class="text-lg font-semibold mb-6 text-gray-700">Word Cloud (Key Attributes)</h3>
                <div class="bg-gray-50 rounded-lg p-4 shadow-inner">
                    <VueECharts v-if="matchDetailsWordCloudData.length" :option="matchDetailsWordCloudOption"
                        style="height: 300px; width: 100%;" />
                    <div v-else class="text-gray-400 text-center py-8">No word cloud data available.</div>
                </div>
            </div>


            <div v-if="selectedReport === 'reasons' && !loading" class="bg-white rounded-xl shadow p-8 mb-10">
                <h3 class="text-lg font-semibold mb-6 text-gray-700">Stacked Column Chart (Feedback by Match Score)
                </h3>
                <VueECharts :option="stackedColumnOption" style="height: 400px; width: 100%;" />
            </div>
        </Container>
    </AppLayout>
</template>