<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, onMounted, computed } from 'vue'
import VueECharts from 'vue-echarts'
import axios from 'axios'
import Container from '@/Components/Container.vue'

const loading = ref(false)
const analyticsData = ref({})

const reportType = ref('education'); // 'education' or 'salary'

const reportTypes = [
    { key: 'education', label: 'Educational Impact' },
    { key: 'salary', label: 'Salary Insights' }
];

function fetchCareerData() {
    loading.value = true
    axios.get(route('peso.reports.careers.data'))
        .then(res => {
            analyticsData.value = res.data
            console.log('Career Analytics:', res.data)
        })
        .finally(() => {
            loading.value = false
        })
}

onMounted(fetchCareerData)

// Bar Chart: Employment Rate by Education Level
const barOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    xAxis: {
        type: 'category',
        data: (analyticsData.value.barChartData ?? []).map(d => d.education),
        axisLabel: { rotate: 20 }
    },
    yAxis: { type: 'value', name: 'Employment Rate (%)' },
    series: [{
        name: 'Employment Rate',
        type: 'bar',
        data: (analyticsData.value.barChartData ?? []).map(d => d.employment_rate),
        itemStyle: { color: '#3b82f6' }
    }]
}))

// Radar Chart: Skills Development vs Employability by Program
const radarOption = computed(() => ({
    tooltip: { trigger: 'item' },
    legend: { data: (analyticsData.value.radarData ?? []).map(d => d.name) },
    radar: {
        indicator: (analyticsData.value.radarSkills ?? []).map(skill => ({
            name: skill, max: 100
        })),
        radius: 90
    },
    series: [{
        type: 'radar',
        data: analyticsData.value.radarData ?? [],
        areaStyle: { opacity: 0.1 }
    }]
}))


// Box Plot: Salary Ranges Across Industries
const boxPlotOption = computed(() => ({
    tooltip: { trigger: 'item' },
    legend: { data: ['Salary Range'] },
    xAxis: {
        type: 'category',
        data: (analyticsData.value.industrySalaryBoxData ?? []).map(d => d.industry),
        axisLabel: { rotate: 20 }
    },
    yAxis: { type: 'value', name: 'Salary' },
    series: [{
        name: 'Salary Range',
        type: 'boxplot',
        data: (analyticsData.value.industrySalaryBoxData ?? []).map(d => {
            // ECharts expects [min, Q1, median, Q3, max]
            const arr = d.all ?? [];
            arr.sort((a, b) => a - b);
            const min = arr[0] ?? 0;
            const max = arr[arr.length - 1] ?? 0;
            const q1 = arr[Math.floor(arr.length * 0.25)] ?? min;
            const median = arr[Math.floor(arr.length * 0.5)] ?? min;
            const q3 = arr[Math.floor(arr.length * 0.75)] ?? max;
            return [min, q1, median, q3, max];
        }),
        itemStyle: { color: '#f59e42' }
    }]
}));

// Stacked Bar: Entry/Mid/Senior Salary per Industry
const stackedBarOption = computed(() => ({
    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
    legend: { data: ['Entry', 'Mid', 'Senior'] },
    xAxis: {
        type: 'category',
        data: (analyticsData.value.industryLevelSalaries ?? []).map(d => d.industry),
        axisLabel: { rotate: 20 }
    },
    yAxis: { type: 'value', name: 'Average Salary' },
    series: [
        {
            name: 'Entry',
            type: 'bar',
            stack: 'salary',
            data: (analyticsData.value.industryLevelSalaries ?? []).map(d => d.Entry),
            itemStyle: { color: '#60a5fa' }
        },
        {
            name: 'Mid',
            type: 'bar',
            stack: 'salary',
            data: (analyticsData.value.industryLevelSalaries ?? []).map(d => d.Mid),
            itemStyle: { color: '#34d399' }
        },
        {
            name: 'Senior',
            type: 'bar',
            stack: 'salary',
            data: (analyticsData.value.industryLevelSalaries ?? []).map(d => d.Senior),
            itemStyle: { color: '#f472b6' }
        }
    ]
}));


// Histogram: Salary Expectations vs Offered

function binData(data, binSize = 10000) {
    if (!Array.isArray(data)) data = Object.values(data ?? {});
    if (!data.length) return [];
    const min = Math.min(...data);
    const max = Math.max(...data);
    const bins = [];
    for (let i = min; i <= max; i += binSize) {
        bins.push({
            bin: `${i}-${i + binSize - 1}`,
            count: 0
        });
    }
    data.forEach(val => {
        const idx = Math.floor((val - min) / binSize);
        if (bins[idx]) bins[idx].count++;
    });
    return bins;
}

const graduateMinBins = computed(() => binData(analyticsData.value.salaryExpectations?.graduateMin));
const graduateMaxBins = computed(() => binData(analyticsData.value.salaryExpectations?.graduateMax));
const jobMinBins = computed(() => binData(analyticsData.value.salaryExpectations?.jobMin));
const jobMaxBins = computed(() => binData(analyticsData.value.salaryExpectations?.jobMax));

const allBins = computed(() => {
    // Get all unique bin labels
    const labels = new Set([
        ...graduateMinBins.value.map(b => b.bin),
        ...graduateMaxBins.value.map(b => b.bin),
        ...jobMinBins.value.map(b => b.bin),
        ...jobMaxBins.value.map(b => b.bin),
    ]);
    return Array.from(labels).sort((a, b) => {
        // Sort numerically by bin start
        return parseInt(a.split('-')[0]) - parseInt(b.split('-')[0]);
    });
});

const histogramOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    legend: { data: ['Graduate Min', 'Graduate Max', 'Job Min', 'Job Max'] },
    xAxis: { type: 'category', name: 'Salary', data: allBins.value },
    yAxis: { type: 'value', name: 'Count' },
    series: [
        {
            name: 'Graduate Min',
            type: 'bar',
            data: allBins.value.map(bin =>
                graduateMinBins.value.find(b => b.bin === bin)?.count ?? 0
            ),
            itemStyle: { color: '#60a5fa', opacity: 0.7 }
        },
        {
            name: 'Graduate Max',
            type: 'bar',
            data: allBins.value.map(bin =>
                graduateMaxBins.value.find(b => b.bin === bin)?.count ?? 0
            ),
            itemStyle: { color: '#3b82f6', opacity: 0.7 }
        },
        {
            name: 'Job Min',
            type: 'bar',
            data: allBins.value.map(bin =>
                jobMinBins.value.find(b => b.bin === bin)?.count ?? 0
            ),
            itemStyle: { color: '#fbbf24', opacity: 0.7 }
        },
        {
            name: 'Job Max',
            type: 'bar',
            data: allBins.value.map(bin =>
                jobMaxBins.value.find(b => b.bin === bin)?.count ?? 0
            ),
            itemStyle: { color: '#f59e42', opacity: 0.7 }
        }
    ]
}));

// Education Analytics Summary
const educationSummary = computed(() => {
    const bar = analyticsData.value.barChartData ?? [];
    const radar = analyticsData.value.radarData ?? [];
    let summary = "<ul class='list-disc ml-6 mb-2'>";

    // Bar Chart: Employment Rate by Education
    if (bar.length) {
        const top = bar.reduce((a, b) => (a.employment_rate > b.employment_rate ? a : b));
        summary += `<li>Highest employment rate: <strong>${top.education}</strong> (${top.employment_rate}%)</li>`;
        const low = bar.reduce((a, b) => (a.employment_rate < b.employment_rate ? a : b));
        summary += `<li>Lowest employment rate: <strong>${low.education}</strong> (${low.employment_rate}%)</li>`;
    }

    // Radar Chart: Skills Development vs Employability
    if (radar.length) {
        const topProgram = radar.reduce((a, b) => (a.value?.reduce((x, y) => x + y, 0) > b.value?.reduce((x, y) => x + y, 0) ? a : b));
        summary += `<li>Program with strongest skills-employability: <strong>${topProgram.name}</strong></li>`;
    }

    summary += "</ul>";
    return summary;
});

// Salary Analytics Summary
const salarySummary = computed(() => {
    const box = analyticsData.value.industrySalaryBoxData ?? [];
    const stacked = analyticsData.value.industryLevelSalaries ?? [];
    const hist = analyticsData.value.salaryExpectations ?? {};
    let summary = "<ul class='list-disc ml-6 mb-2'>";

    // Box Plot: Salary Ranges
    if (box.length) {
        const top = box.reduce((a, b) => (Math.max(...(a.all ?? [0])) > Math.max(...(b.all ?? [0])) ? a : b));
        summary += `<li>Highest salary range: <strong>${top.industry}</strong></li>`;
        const low = box.reduce((a, b) => (Math.min(...(a.all ?? [0])) < Math.min(...(b.all ?? [0])) ? a : b));
        summary += `<li>Lowest salary range: <strong>${low.industry}</strong></li>`;
    }

    // Stacked Bar: Salary by Experience Level
    if (stacked.length) {
        const topEntry = stacked.reduce((a, b) => (a.Entry > b.Entry ? a : b));
        summary += `<li>Highest entry-level salary: <strong>${topEntry.industry}</strong> (₱${topEntry.Entry})</li>`;
        const topSenior = stacked.reduce((a, b) => (a.Senior > b.Senior ? a : b));
        summary += `<li>Highest senior-level salary: <strong>${topSenior.industry}</strong> (₱${topSenior.Senior})</li>`;
    }

    // Histogram: Salary Expectations vs Offered
    if (hist.graduateMin && hist.jobMin) {
        const gradMin = Math.min(...Object.values(hist.graduateMin));
        const jobMin = Math.min(...Object.values(hist.jobMin));
        summary += `<li>Lowest expected salary: <strong>₱${gradMin}</strong></li>`;
        summary += `<li>Lowest offered salary: <strong>₱${jobMin}</strong></li>`;
    }

    summary += "</ul>";
    return summary;
});


</script>

<template>
    <AppLayout title="Educational Impact Analytics">
        <Container class="py-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Career Reports</h2>


            <div class="flex justify-center mb-8">
                <div class="inline-flex rounded-lg shadow bg-gray-100 overflow-hidden">
                    <button v-for="type in reportTypes" :key="type.key" @click="reportType = type.key" :class="[
                        'px-6 py-2 text-sm font-semibold focus:outline-none transition',
                        reportType === type.key
                            ? 'bg-white text-blue-600 shadow'
                            : 'text-gray-500 hover:bg-gray-200'
                    ]">
                        {{ type.label }}
                    </button>
                </div>
            </div>


            <div v-if="reportType === 'education'">
                <!-- Bar Chart: Employment Rate by Education Level -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-chart-bar text-blue-500"></i>
                        Employment Rate by Education Level <span class="font-bold text-blue-600">(Bar Chart)</span>
                    </h3>
                    <div v-if="loading" class="flex justify-center items-center py-16">
                        <span class="loader mr-3"></span>
                        <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                    </div>
                    <div v-else>
                        <VueECharts v-if="analyticsData.barChartData && analyticsData.barChartData.length"
                            :option="barOption" style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No bar chart data available.</div>
                    </div>
                </div>

                <!-- Radar Chart: Skills Development vs Employability -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-chart-pie text-green-500"></i>
                        Skills Development vs Employability <span class="font-bold text-green-600">(Radar Chart)</span>
                    </h3>
                    <div v-if="loading" class="flex justify-center items-center py-16">
                        <span class="loader mr-3"></span>
                        <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                    </div>
                    <div v-else>
                        <VueECharts v-if="analyticsData.radarData && analyticsData.radarData.length"
                            :option="radarOption" style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No radar chart data available.</div>
                    </div>
                </div>

                <!-- Analytics Summary Card for Education -->
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
                            <span v-html="educationSummary"></span>
                        </div>
                    </div>
                </div>

            </div>

            <div v-if="reportType === 'salary'">

                <!-- Box Plot: Salary Ranges Across Industries -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-box text-orange-500"></i>
                        Salary Ranges by Industry <span class="font-bold text-orange-600">(Box Plot)</span>
                    </h3>
                    <div v-if="loading" class="flex justify-center items-center py-16">
                        <span class="loader mr-3"></span>
                        <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                    </div>
                    <div v-else>
                        <VueECharts
                            v-if="analyticsData.industrySalaryBoxData && analyticsData.industrySalaryBoxData.length"
                            :option="boxPlotOption" style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No box plot data available.</div>
                    </div>
                </div>

                <!-- Stacked Bar: Entry/Mid/Senior Salary per Industry -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-layer-group text-pink-500"></i>
                        Salary by Experience Level <span class="font-bold text-pink-600">(Stacked Bar)</span>
                    </h3>
                    <div v-if="loading" class="flex justify-center items-center py-16">
                        <span class="loader mr-3"></span>
                        <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                    </div>
                    <div v-else>
                        <VueECharts
                            v-if="analyticsData.industryLevelSalaries && analyticsData.industryLevelSalaries.length"
                            :option="stackedBarOption" style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No stacked bar data available.</div>
                    </div>
                </div>

                <!-- Histogram: Salary Expectations vs Offered -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-chart-bar text-yellow-500"></i>
                        Salary Expectations vs Offered <span class="font-bold text-yellow-600">(Histogram)</span>
                    </h3>
                    <div v-if="loading" class="flex justify-center items-center py-16">
                        <span class="loader mr-3"></span>
                        <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                    </div>
                    <div v-else>
                        <VueECharts v-if="analyticsData.salaryExpectations" :option="histogramOption"
                            style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No histogram data available.</div>
                    </div>
                </div>

                <!-- Analytics Summary Card for Salary -->
                <div class="mt-8 flex justify-center">
                    <div class="w-full md:w-2/3 lg:w-1/2 shadow-lg rounded-xl bg-white border border-gray-200">
                        <div class="flex items-center px-6 pt-6 pb-2">
                            <svg class="w-6 h-6 text-orange-500 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 3v18h18M9 17V9m4 8V5m4 12v-6" />
                            </svg>
                            <span class="text-lg font-semibold text-gray-800">Analytics Summary</span>
                        </div>
                        <div class="px-6 pb-6 text-gray-700 leading-relaxed">
                            <span v-html="salarySummary"></span>
                        </div>
                    </div>
                </div>
            </div>


        </Container>
    </AppLayout>
</template>