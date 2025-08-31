<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch, onMounted, computed } from 'vue'
import VueECharts from 'vue-echarts';
import { usePage } from '@inertiajs/vue3'
import Container from '@/Components/Container.vue';
import axios from 'axios'

// --- Filter state ---
const selectedYear = ref('')
const selectedProgram = ref('')
const selectedStatus = ref('')
const selectedInstitution = ref('')

// --- Static filter options from page.props ---
const page = usePage()
const institutions = ref(page.props.institutions ?? [])
const programs = ref(page.props.programs ?? [])

// --- Analytics data (fetched asynchronously) ---
const analyticsData = ref({})
const loading = ref(false)

// --- Fetch analytics data from backend ---
function fetchAnalyticsData() {
    loading.value = true
    axios.get(route('peso.reports.employment.data'), {
        params: {
            year: selectedYear.value,
            program_id: selectedProgram.value,
            institution_id: selectedInstitution.value,
            status: selectedStatus.value,
        }
    }).then(res => {
        analyticsData.value = res.data
        // Reset pagination on filter change
        currentPage.value = 1
    }).finally(() => {
        loading.value = false
    })
}

// Fetch on mount and when filters change
onMounted(fetchAnalyticsData)
watch([selectedYear, selectedProgram, selectedInstitution, selectedStatus], fetchAnalyticsData)

// --- Graduates and computed values ---
const graduates = computed(() => analyticsData.value.graduates ?? [])
const currentPage = ref(1)
const pageSize = ref(10)
const paginatedGraduates = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value
    return safeFilteredGraduates.value.slice(start, start + pageSize.value)
})
const totalPages = computed(() => Math.ceil(safeFilteredGraduates.value.length / pageSize.value))

// --- Filtered graduates ---
const safeFilteredGraduates = computed(() => graduates.value) // Already filtered by backend

// --- KPI values ---
const employed = computed(() => safeFilteredGraduates.value.filter(g => g.employment_status === 'Employed').length)
const unemployed = computed(() => safeFilteredGraduates.value.filter(g => g.employment_status === 'Unemployed').length)
const underemployed = computed(() => safeFilteredGraduates.value.filter(g => g.employment_status === 'Underemployed').length)
const totalGraduates = computed(() => safeFilteredGraduates.value.length)

// --- Years for dropdown ---
const years = computed(() => {
    const set = new Set()
    graduates.value.forEach(g => {
        if (g.school_year?.school_year_range) set.add(g.school_year.school_year_range)
    })
    return Array.from(set).sort().reverse()
})

// --- Status counts for chart ---
const statusCounts = computed(() => analyticsData.value.statusCounts ?? {})
const filteredStatusCounts = computed(() => statusCounts.value)

// --- Chart options (example for pie chart) ---
const pieOption = computed(() => ({
    tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
    legend: { orient: 'vertical', left: 'left' },
    series: [
        {
            name: 'Status',
            type: 'pie',
            radius: '60%',
            data: [
                { name: 'Employed', value: filteredStatusCounts.value.Employed },
                { name: 'Underemployed', value: filteredStatusCounts.value.Underemployed },
                { name: 'Unemployed', value: filteredStatusCounts.value.Unemployed }
            ],
            emphasis: {
                itemStyle: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                }
            },
            label: {
                formatter: '{b}: {d}%',
                color: '#374151',
                fontWeight: 'bold'
            }
        }
    ]
}))

// --- Industry/Program chart options ---
const industryGraduateCounts = computed(() => analyticsData.value.industryGraduateCounts ?? [])
const industryNames = computed(() => analyticsData.value.industryNames ?? [])
const industryJobRoles = computed(() => analyticsData.value.industryJobRoles ?? [])
const industryApplicants = computed(() => analyticsData.value.industryApplicants ?? [])
const programNames = computed(() => analyticsData.value.programNames ?? [])
const employedByProgram = computed(() => analyticsData.value.employedByProgram ?? [])
const unemployedByProgram = computed(() => analyticsData.value.unemployedByProgram ?? [])

// --- Bar chart for employment by program ---
const barOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    legend: { data: ['Employed', 'Unemployed'] },
    xAxis: { type: 'category', data: programNames.value },
    yAxis: { type: 'value' },
    series: [
        {
            name: 'Employed',
            type: 'bar',
            stack: 'total',
            data: employedByProgram.value,
            itemStyle: { color: '#22c55e' }
        },
        {
            name: 'Unemployed',
            type: 'bar',
            stack: 'total',
            data: unemployedByProgram.value,
            itemStyle: { color: '#ef4444' }
        }
    ]
}))

// --- Industry bar chart ---
const industryBarOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    xAxis: { type: 'category', data: industryGraduateCounts.value.map(i => i.name) },
    yAxis: { type: 'value', name: 'Graduates' },
    series: [
        {
            name: 'Graduates',
            type: 'bar',
            data: industryGraduateCounts.value.map(i => i.value),
            itemStyle: { color: '#3b82f6' }
        }
    ]
}))

// --- Treemap ---
const industryTreemapOption = computed(() => ({
    tooltip: { trigger: 'item', formatter: '{b}: {c}' },
    series: [
        {
            type: 'treemap',
            data: industryGraduateCounts.value,
            label: { show: true, formatter: '{b}' }
        }
    ]
}))

// --- Stacked Column Chart ---
const industryStackedOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    legend: { data: ['Job Roles', 'Applicants'] },
    xAxis: { type: 'category', data: industryNames.value },
    yAxis: { type: 'value' },
    series: [
        {
            name: 'Job Roles',
            type: 'bar',
            stack: 'total',
            data: industryJobRoles.value,
            itemStyle: { color: '#10b981' }
        },
        {
            name: 'Applicants',
            type: 'bar',
            stack: 'total',
            data: industryApplicants.value,
            itemStyle: { color: '#f59e42' }
        }
    ]
}))

</script>

<template>
    <AppLayout title="Employment Overview">
        <Container class="py-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Employment Overview</h2>
            <!-- FILTER CONTROLS -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-filter text-blue-500 mr-2"></i>
                    Filter Reports
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                        <select v-model="selectedYear"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                            <option value="">All Years</option>
                            <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Program</label>
                        <select v-model="selectedProgram"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                            <option value="">All Programs</option>
                            <option v-for="program in programs" :key="program.id" :value="program.id">{{ program.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select v-model="selectedStatus"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                            <option value="">All Statuses</option>
                            <option value="Employed">Employed</option>
                            <option value="Unemployed">Unemployed</option>
                            <option value="Underemployed">Underemployed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Institution</label>
                        <select v-model="selectedInstitution"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200">
                            <option value="">All Locations</option>
                            <option v-for="inst in institutions" :key="inst.id" :value="inst.id">{{
                                inst.institution_name }}</option>
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
            <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">
                <div
                    class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500 hover:shadow-md transition-shadow duration-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-gray-600 text-sm font-medium mb-2">Employed</h3>
                            <p class="text-3xl font-bold text-green-600">{{ employed }}</p>
                            <p class="text-sm text-gray-500 mt-1">{{ ((employed / totalGraduates) * 100).toFixed(1) }}%
                                of graduates</p>
                        </div>
                        <div class="bg-green-100 rounded-full p-3 flex items-center justify-center">
                            <i class="fas fa-briefcase text-green-500"></i>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500 hover:shadow-md transition-shadow duration-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-gray-600 text-sm font-medium mb-2">Unemployed</h3>
                            <p class="text-3xl font-bold text-red-600">{{ unemployed }}</p>
                            <p class="text-sm text-gray-500 mt-1">{{ ((unemployed / totalGraduates) * 100).toFixed(1)
                                }}% of graduates</p>
                        </div>
                        <div class="bg-red-100 rounded-full p-3 flex items-center justify-center">
                            <i class="fas fa-user-clock text-red-500"></i>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500 hover:shadow-md transition-shadow duration-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-gray-600 text-sm font-medium mb-2">Total Graduates</h3>
                            <p class="text-3xl font-bold text-blue-600">{{ totalGraduates }}</p>
                            <p class="text-sm text-gray-500 mt-1">Across all programs</p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3 flex items-center justify-center">
                            <i class="fas fa-user-graduate text-blue-500"></i>
                        </div>
                    </div>
                </div>
            </div>
            <p v-if="!loading" class="mb-2 text-sm text-gray-500">
                Showing {{ safeFilteredGraduates.length }} of {{ totalGraduates }} graduates
            </p>
            <div v-if="!loading && safeFilteredGraduates.length" class="bg-white rounded-xl shadow p-8 mb-10">
                <h3 class="text-lg font-semibold mb-6 text-gray-700">Filtered Graduates</h3>
                <table class="min-w-full text-sm text-gray-800">
                    <thead>
                        <tr>
                            <th class="px-2 py-1 text-left">Name</th>
                            <th class="px-2 py-1 text-left">Program</th>
                            <th class="px-2 py-1 text-left">Status</th>
                            <th class="px-2 py-1 text-left">Year</th>
                            <th class="px-2 py-1 text-left">Institution</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="g in paginatedGraduates" :key="g.id" class="hover:bg-gray-100">
                            <td class="px-2 py-1">{{ g.first_name }} {{ g.last_name }}</td>
                            <td class="px-2 py-1">{{ g.program?.name }}</td>
                            <td class="px-2 py-1">{{ g.employment_status }}</td>
                            <td class="px-2 py-1">{{ g.school_year?.school_year_range }}</td>
                            <td class="px-2 py-1">{{ g.institution?.institution_name }}</td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="totalPages > 1" class="flex items-center justify-between mt-4">
                    <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300" :disabled="currentPage === 1"
                        @click="currentPage--">Prev</button>
                    <span class="text-gray-700">Page {{ currentPage }} of {{ totalPages }}</span>
                    <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300"
                        :disabled="currentPage === totalPages" @click="currentPage++">Next</button>
                </div>
            </div>

            <!-- Employment Status Chart & Industry/Program Charts -->
            <div v-if="!loading && statusCounts && Object.keys(statusCounts).length"
                class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 hover:shadow-md transition-shadow duration-200">
                <div class="flex flex-col lg:flex-row gap-12 items-center justify-between">
                    <div class="w-full lg:w-2/5 mb-8 lg:mb-0">
                        <h3 class="text-lg font-semibold mb-6 text-gray-700 flex items-center">
                            <i class="fas fa-list-alt text-blue-500 mr-2"></i>
                            Detailed Status
                        </h3>
                        <ul class="space-y-4">
                            <li v-for="(count, status) in filteredStatusCounts" :key="status"
                                class="flex justify-between items-center px-4 py-2 rounded hover:bg-gray-50 transition">
                                <span class="capitalize text-gray-600">{{ status }}</span>
                                <span class="font-semibold text-gray-900">{{ count }}</span>
                            </li>
                        </ul>
                    </div>
                    <div class="w-full lg:w-3/5 flex justify-center">
                        <div class="bg-gray-50 rounded-lg p-4 w-full flex justify-center">
                            <VueECharts v-if="pieOption.series[0].data.length" :option="pieOption"
                                style="height: 350px; width: 100%; max-width: 420px;" />
                            <div v-else class="text-gray-400 text-center py-8">No chart data available.</div>
                        </div>
                    </div>
                </div>
                <!-- Employment by Industry -->
                <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Employment by Industry</h2>

                <!-- Bar/Clustered Column Chart -->
                <div class="bg-white rounded-xl shadow p-8 mb-12">
                    <h3 class="text-lg font-semibold mb-6 text-gray-700">Graduates per Industry</h3>
                    <VueECharts :option="industryBarOption" style="height: 400px; width: 100%;" />
                </div>

                <!-- Treemap -->
                <div class="bg-white rounded-xl shadow p-8 mb-12">
                    <h3 class="text-lg font-semibold mb-6 text-gray-700">Industry Share (Treemap)</h3>
                    <VueECharts :option="industryTreemapOption" style="height: 400px; width: 100%;" />
                </div>

                <!-- Stacked Column Chart -->
                <div class="bg-white rounded-xl shadow p-8">
                    <h3 class="text-lg font-semibold mb-6 text-gray-700">Job Roles vs. Applicants by Industry</h3>
                    <VueECharts :option="industryStackedOption" style="height: 400px; width: 100%;" />
                </div>

                <!-- Employment By Program -->
                <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
                    <i class="fas fa-graduation-cap text-blue-500 mr-2"></i>
                    Employment by Program
                </h2>
                <div
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-10 hover:shadow-md transition-shadow duration-200">
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        <i class="fas fa-chart-bar text-green-500 mr-2"></i>
                        Employment Rate by Program
                    </h3>
                    <VueECharts :option="barOption" style="height: 400px; width: 100%;" />
                </div>
            </div>

            <!-- Summary Block -->
            <div v-if="!loading" class="mt-8 bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
                <div class="text-blue-900 space-y-2">
                    <div>
                        <span class="font-semibold">Summary:</span>
                        <span>
                            Out of <span class="font-bold">{{ totalGraduates }}</span> filtered graduates:
                            <span class="text-green-700 font-semibold">{{ employed }}</span> employed ({{ ((employed /
                                totalGraduates) * 100).toFixed(1) }}%),
                            <span class="text-yellow-700 font-semibold">{{ underemployed }}</span> underemployed ({{
                                ((underemployed
                                    / totalGraduates) * 100).toFixed(1) }}%),
                            <span class="text-red-700 font-semibold">{{ unemployed }}</span> unemployed ({{ ((unemployed
                                /
                                totalGraduates) * 100).toFixed(1) }}%).
                        </span>
                    </div>
                    <div v-if="years.length">
                        <span class="font-semibold">School Years:</span>
                        <span>{{ years.join(', ') }}</span>
                    </div>
                    <div v-if="safeFilteredGraduates.length">
                        <span class="font-semibold">Top Program:</span>
                        <span>
                            {{
                                (() => {
                                    const counts = {};
                                    safeFilteredGraduates.forEach(g => {
                                        if (g.program?.name) counts[g.program.name] = (counts[g.program.name] || 0) + 1;
                                    });
                                    const top = Object.entries(counts).sort((a, b) => b[1] - a[1])[0];
                                    return top ? `${top[0]} (${top[1]})` : 'N/A';
                                })()
                            }}
                        </span>
                    </div>
                    <div v-if="safeFilteredGraduates.length">
                        <span class="font-semibold">Top Institution:</span>
                        <span>
                            {{
                                (() => {
                                    const counts = {};
                                    safeFilteredGraduates.forEach(g => {
                                        if (g.institution?.institution_name) counts[g.institution.institution_name] =
                                            (counts[g.institution.institution_name] || 0) + 1;
                                    });
                                    const top = Object.entries(counts).sort((a, b) => b[1] - a[1])[0];
                                    return top ? `${top[0]} (${top[1]})` : 'N/A';
                                })()
                            }}
                        </span>
                    </div>
                </div>
            </div>
        </Container>
    </AppLayout>
</template>