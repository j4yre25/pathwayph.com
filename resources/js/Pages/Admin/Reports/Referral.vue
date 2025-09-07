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
const selectedTimeline = ref('')

// --- Static filter options from page.props ---
const page = usePage()
const companies = ref(page.props.companies ?? [])
const roles = ref(page.props.roles ?? [])
const sources = ref(page.props.sources ?? [])

// --- Analytics data (fetched asynchronously) ---
const analyticsData = ref({})
const loading = ref(false)

// --- Fetch analytics data from backend ---
function fetchAnalyticsData() {
    loading.value = true
    axios.get(route('peso.reports.referral.data'), {
        params: {
            company_id: selectedCompany.value,
            role: selectedRole.value,
            source: selectedSource.value,
            timeline: selectedTimeline.value,
        }
    }).then(res => {
        analyticsData.value = res.data
        console.log('Referral Data:', response.data);
    }).finally(() => {
        loading.value = false
    })
}

// Fetch on mount and when filters change
onMounted(fetchAnalyticsData)
watch([selectedCompany, selectedRole, selectedSource, selectedTimeline], fetchAnalyticsData)

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

const wordCloudData = computed(() => Object.entries(analyticsData.value.wordCloudData ?? {}).map(([text, value]) => ({ name: text, value })))

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
                            <option v-for="company in companies" :key="company.id" :value="company.id">{{ company.company_name }}</option>
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
                        <label class="block text-sm font-medium text-gray-700 mb-2">Source</label>
                        <select v-model="selectedSource"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                            <option value="">All Sources</option>
                            <option v-for="src in sources" :key="src" :value="src">{{ src }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Timeline</label>
                        <input v-model="selectedTimeline" type="month"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200" />
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
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500 hover:shadow-md transition-shadow duration-200">
                    <h3 class="text-gray-600 text-sm font-medium mb-2">Total Referrals</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ analyticsData.totalReferrals ?? 0 }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500 hover:shadow-md transition-shadow duration-200">
                    <h3 class="text-gray-600 text-sm font-medium mb-2">Screened</h3>
                    <p class="text-3xl font-bold text-green-600">{{ analyticsData.funnelData?.[1]?.count ?? 0 }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500 hover:shadow-md transition-shadow duration-200">
                    <h3 class="text-gray-600 text-sm font-medium mb-2">Interviewed</h3>
                    <p class="text-3xl font-bold text-yellow-600">{{ analyticsData.funnelData?.[2]?.count ?? 0 }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500 hover:shadow-md transition-shadow duration-200">
                    <h3 class="text-gray-600 text-sm font-medium mb-2">Hired</h3>
                    <p class="text-3xl font-bold text-red-600">{{ analyticsData.funnelData?.[3]?.count ?? 0 }}</p>
                </div>
            </div>

            <!-- Funnel Chart -->
            <div v-if="!loading" class="bg-white rounded-xl shadow p-8 mb-10">
                <h3 class="text-lg font-semibold mb-6 text-gray-700">Referral Success Rate (Funnel)</h3>
                <VueECharts :option="funnelOption" style="height: 400px; width: 100%;" />
            </div>

            <!-- Bar Chart: Success rates -->
            <div v-if="!loading" class="bg-white rounded-xl shadow p-8 mb-10">
                <h3 class="text-lg font-semibold mb-6 text-gray-700">Success Rates: Referrals vs Other Sources</h3>
                <VueECharts :option="barSuccessOption" style="height: 400px; width: 100%;" />
            </div>

            <!-- Source of Referrals -->
            <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Source of Referrals</h2>
            <div v-if="!loading" class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                <div class="bg-white rounded-xl shadow p-8">
                    <h3 class="text-lg font-semibold mb-6 text-gray-700">Pie Chart</h3>
                    <VueECharts :option="pieSourceOption" style="height: 400px; width: 100%;" />
                </div>
                <div class="bg-white rounded-xl shadow p-8">
                    <h3 class="text-lg font-semibold mb-6 text-gray-700">Treemap</h3>
                    <VueECharts :option="treemapSourceOption" style="height: 400px; width: 100%;" />
                </div>
            </div>

            <!-- Referral Trends Over Time -->
            <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Referral Trends Over Time</h2>
            <div v-if="!loading" class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                <div class="bg-white rounded-xl shadow p-8">
                    <h3 class="text-lg font-semibold mb-6 text-gray-700">Line Chart</h3>
                    <VueECharts :option="lineTrendOption" style="height: 400px; width: 100%;" />
                </div>
                <div class="bg-white rounded-xl shadow p-8">
                    <h3 class="text-lg font-semibold mb-6 text-gray-700">Area Chart</h3>
                    <VueECharts :option="areaTrendOption" style="height: 400px; width: 100%;" />
                </div>
            </div>

            <!-- Referral Network Analysis -->
            <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Referral Network Analysis</h2>
            <div v-if="!loading" class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                <div class="bg-white rounded-xl shadow p-8">
                    <h3 class="text-lg font-semibold mb-6 text-gray-700">Bubble Chart (Influence)</h3>
                    <VueECharts :option="bubbleOption" style="height: 400px; width: 100%;" />
                </div>
                <!-- Network Graph placeholder (requires custom ECharts graph config) -->
                <div class="bg-white rounded-xl shadow p-8">
                    <h3 class="text-lg font-semibold mb-6 text-gray-700">Network Graph</h3>
                    <div class="text-gray-400 text-center py-8">Network graph visualization coming soon.</div>
                </div>
            </div>

            <!-- Referral Performance by Role -->
            <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Referral Performance by Role</h2>
            <div v-if="!loading" class="bg-white rounded-xl shadow p-8 mb-10">
                <h3 class="text-lg font-semibold mb-6 text-gray-700">Clustered Bar Chart</h3>
                <VueECharts :option="roleStatsOption" style="height: 400px; width: 100%;" />
            </div>

            <!-- Referral Bonuses and Outcomes -->
            <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Referral Bonuses and Outcomes</h2>
            <div v-if="!loading" class="bg-white rounded-xl shadow p-8 mb-10">
                <h3 class="text-lg font-semibold mb-6 text-gray-700">Histogram</h3>
                <VueECharts :option="histogramOption" style="height: 400px; width: 100%;" />
            </div>

            <!-- Reason for Referral Success -->
            <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Reason for Referral Success</h2>
            <div v-if="!loading" class="bg-white rounded-xl shadow p-8 mb-10">
                <h3 class="text-lg font-semibold mb-6 text-gray-700">Word Cloud</h3>
                <div class="flex flex-wrap gap-2">
                    <span v-for="item in wordCloudData" :key="item.name"
                        :style="{ fontSize: (item.value * 2 + 12) + 'px', color: '#6366f1', fontWeight: 'bold' }">
                        {{ item.name }}
                    </span>
                </div>
            </div>
        </Container>
    </AppLayout>
</template>