<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, onMounted, computed } from 'vue'
import VueECharts from 'vue-echarts'
import axios from 'axios'
import Container from '@/Components/Container.vue'


const loading = ref(false)
const analyticsData = ref({})
const reportType = ref('alignment')

const reportTypes = [
    { key: 'alignment', label: 'Job Seeker & Role Alignment' },
    { key: 'matching', label: 'Matching Success Rate' },
    { key: 'future', label: 'Future Job Market Trends' },
    { key: 'preference', label: 'Employer Preferences' },
]



function fetchJobMarketData() {
    loading.value = true
    axios.get(route('peso.reports.jobmarket.data'))
        .then(res => {
            analyticsData.value = res.data
            console.log('JobMarket:', res.data)
        })
        .finally(() => {
            loading.value = false
        })
}

onMounted(fetchJobMarketData)


// Scatter Plot: Job Seeker & Role Alignment


const scatterOption = computed(() => ({
    tooltip: {
        trigger: 'item',
        formatter: function (params) {
            // params.data = [matched, required, applicant, matchedSkills, requiredSkills]
            const [matched, required, applicant, matchedSkills, requiredSkills] = params.data;
            return `
                <b>${'Job Seeker'}</b><br/>
                Skills Matched: ${matched}<br/>
                Required Skills: ${required}<br/>
                <span class="block mt-1 text-xs text-gray-500">Matched Skills: ${matchedSkills?.join(', ') || '-'}</span>
                <span class="block mt-1 text-xs text-gray-500">Required Qualifications: ${requiredSkills?.join(', ') || '-'}</span>
            `;
        }
    },
    xAxis: { name: 'Skills Matched', type: 'value' },
    yAxis: { name: 'Required Skills', type: 'value' },
    series: [{
        symbolSize: 14,
        data: (analyticsData.value.scatterData ?? []).map(d => [
            d.matched,
            d.required,
            d.applicant,
            d.matchedSkills ?? [],
            d.requiredSkills ?? []
        ]),
        type: 'scatter',
        itemStyle: { color: '#3b82f6' }
    }]
}));

// Bar Chart: Alignment (Meets, Exceeds, Falls Short)
const alignmentBarOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    xAxis: {
        type: 'category',
        data: ['Meets', 'Exceeds', 'Falls Short']
    },
    yAxis: { type: 'value', name: 'Applicants' },
    series: [{
        name: 'Applicants',
        type: 'bar',
        data: [
            analyticsData.value.barAlignment?.Meets ?? 0,
            analyticsData.value.barAlignment?.Exceeds ?? 0,
            analyticsData.value.barAlignment?.['Falls Short'] ?? 0
        ],
        itemStyle: { color: '#f59e42' }
    }]
}))

const alignmentSummary = computed(() => {
    const scatter = analyticsData.value.scatterData ?? [];
    const bar = analyticsData.value.barAlignment ?? {};
    let summary = "";

    // Scatter plot: general insight
    if (scatter.length) {
        const avgMatched = (scatter.reduce((sum, d) => sum + (d.matched ?? 0), 0) / scatter.length).toFixed(1);
        const avgRequired = (scatter.reduce((sum, d) => sum + (d.required ?? 0), 0) / scatter.length).toFixed(1);
        summary += `<ul class="list-disc ml-6 mb-2">
            <li>Applicants match <strong>${avgMatched}</strong> out of <strong>${avgRequired}</strong> required skills on average.</li>
        </ul>`;
    }

    // Bar chart: alignment categories
    const meets = bar.Meets ?? 0;
    const exceeds = bar.Exceeds ?? 0;
    const fallsShort = bar['Falls Short'] ?? 0;
    const total = meets + exceeds + fallsShort;

    if (total > 0) {
        summary += `<ul class="list-disc ml-6 mb-2">
            <li><strong>${meets}</strong> meet requirements</li>
            <li><strong>${exceeds}</strong> exceed requirements</li>
            <li><strong>${fallsShort}</strong> fall short</li>
        </ul>`;
        if (exceeds > meets && exceeds > fallsShort) {
            summary += `<div class="mt-2">Most applicants <span class="font-semibold text-purple-600">exceed</span> job requirements.</div>`;
        } else if (fallsShort > meets && fallsShort > exceeds) {
            summary += `<div class="mt-2">Most applicants <span class="font-semibold text-red-600">fall short</span> of requirements.</div>`;
        } else if (meets > exceeds && meets > fallsShort) {
            summary += `<div class="mt-2">Most applicants <span class="font-semibold text-green-600">meet</span> job requirements.</div>`;
        }
    } else {
        summary += `<div>No applicant alignment data available.</div>`;
    }

    return summary;
});


// Matching Success Rate - Pie Chart
const matchPieOption = computed(() => ({
    tooltip: { trigger: 'item' },
    legend: { top: 'bottom' },
    series: [{
        name: 'Matching',
        type: 'pie',
        radius: ['40%', '70%'],
        avoidLabelOverlap: false,
        itemStyle: { borderRadius: 8, borderColor: '#fff', borderWidth: 2 },
        label: { show: true, formatter: '{b}: {d}%' },
        data: analyticsData.value.pieMatchData ?? []
    }]
}));

// Matching Success Rate - Funnel Chart
const funnelOption = computed(() => ({
    tooltip: { trigger: 'item', formatter: '{b}: {c}' },
    series: [{
        name: 'Funnel',
        type: 'funnel',
        left: '10%',
        width: '80%',
        min: 0,
        max: Math.max(...(analyticsData.value.funnelData?.map(d => d.count) ?? [0])),
        sort: 'descending',
        label: { show: true, position: 'inside' },
        data: (analyticsData.value.funnelData ?? []).map(d => ({ name: d.stage, value: d.count }))
    }]
}));

const matchingSummary = computed(() => {
    const rate = analyticsData.value.matchingSuccessRate ?? 0;
    const funnel = analyticsData.value.funnelData ?? [];
    const pie = analyticsData.value.pieMatchData ?? [];
    let summary = "";

    summary += `<ul class="list-disc ml-6 mb-2">`;
    summary += `<li>Matching Success Rate: <strong>${rate}%</strong></li>`;

    // Funnel chart: show drop-off
    if (funnel.length) {
        const applied = funnel.find(f => f.stage.toLowerCase() === 'applied')?.count ?? 0;
        const hired = funnel.find(f => f.stage.toLowerCase() === 'hired')?.count ?? 0;
        summary += `<li>Applicants: <strong>${applied}</strong></li>`;
        summary += `<li>Hired: <strong>${hired}</strong></li>`;
    }

    // Pie chart: matched vs unmatched
    if (pie.length) {
        const matched = pie.find(p => p.name === 'Matched')?.value ?? 0;
        const unmatched = pie.find(p => p.name === 'Unmatched')?.value ?? 0;
        summary += `<li>Matched: <strong>${matched}</strong></li>`;
        summary += `<li>Unmatched: <strong>${unmatched}</strong></li>`;
    }
    summary += `</ul>`;

    if (rate >= 70) {
        summary += `<div class="mt-2">A <span class="font-semibold text-green-600">high</span> match rate shows strong alignment between applicants and jobs.</div>`;
    } else if (rate >= 30) {
        summary += `<div class="mt-2">A <span class="font-semibold text-yellow-600">moderate</span> match rate suggests room for improvement.</div>`;
    } else {
        summary += `<div class="mt-2">A <span class="font-semibold text-red-600">low</span> match rate indicates a significant gap between applicants and job requirements.</div>`;
    }

    return summary;
});

// Future Job Trends - Line Chart
const lineTrendOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    xAxis: { type: 'category', data: (analyticsData.value.lineTrendData ?? []).map(d => d.month) },
    yAxis: { type: 'value', name: 'Openings' },
    series: [{
        name: 'Job Openings',
        type: 'line',
        data: (analyticsData.value.lineTrendData ?? []).map(d => d.openings),
        smooth: true,
        itemStyle: { color: '#3b82f6' }
    }]
}));



// Industry Area Chart Option (Stacked Area)
const industryAreaOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    legend: { top: 10 },
    xAxis: {
        type: 'category',
        data: (analyticsData.value.lineTrendData ?? []).map(d => d.month)
    },
    yAxis: { type: 'value', name: 'Openings' },
    series: analyticsData.value.industryAreaChart ?? []
}));

// Skill Area Chart Option (Stacked Area)
const skillAreaOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    legend: { top: 10, type: 'scroll' },
    xAxis: {
        type: 'category',
        data: (analyticsData.value.lineTrendData ?? []).map(d => d.month)
    },
    yAxis: { type: 'value', name: 'Openings' },
    series: analyticsData.value.skillAreaChart ?? []
}));

// Employer Preferences - Bar Chart
const barQualificationOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    xAxis: {
        type: 'category',
        data: (analyticsData.value.barQualificationData ?? []).map(d => d.qualification)
    },
    yAxis: { type: 'value', name: 'Count' },
    series: [{
        name: 'Requested',
        type: 'bar',
        data: (analyticsData.value.barQualificationData ?? []).map(d => d.count),
        itemStyle: { color: '#f59e42' }
    }]
}));

const futureTrendsSummary = computed(() => {
    const lineData = analyticsData.value.lineTrendData ?? [];
    const industrySeries = analyticsData.value.industryAreaChart ?? [];
    const skillSeries = analyticsData.value.skillAreaChart ?? [];

    if (!lineData.length && !industrySeries.length && !skillSeries.length) {
        return "No job market trend data available for the current selection.";
    }

    let summary = "<ul class='list-disc ml-6 mb-2'>";

    // Line Chart: Job openings trend
    if (lineData.length) {
        const first = lineData[0]?.openings ?? 0;
        const last = lineData[lineData.length - 1]?.openings ?? 0;
        if (last > first) {
            summary += `<li>Job openings increased from <strong>${first}</strong> to <strong>${last}</strong>.</li>`;
        } else if (last < first) {
            summary += `<li>Job openings decreased from <strong>${first}</strong> to <strong>${last}</strong>.</li>`;
        } else {
            summary += `<li>Job openings remained stable at <strong>${first}</strong>.</li>`;
        }
    }

    // Industry Area Chart: Top industry
    if (industrySeries.length) {
        const totals = industrySeries.map(s => ({
            name: s.name,
            total: (s.data ?? []).reduce((a, b) => a + b, 0)
        }));
        const topIndustry = totals.sort((a, b) => b.total - a.total)[0];
        if (topIndustry && topIndustry.total > 0) {
            summary += `<li>Highest demand: <strong>${topIndustry.name}</strong> (${topIndustry.total} openings)</li>`;
        }
    }

    // Skill Area Chart: Top skill
    if (skillSeries.length) {
        const totals = skillSeries.map(s => ({
            name: s.name,
            total: (s.data ?? []).reduce((a, b) => a + b, 0)
        }));
        const topSkill = totals.sort((a, b) => b.total - a.total)[0];
        if (topSkill && topSkill.total > 0) {
            summary += `<li>Most in-demand skill: <strong>${topSkill.name}</strong> (${topSkill.total} openings)</li>`;
        }
    }

    summary += "</ul>";
    return summary;
});

// Employer Preferences - Radar Chart
const radarPriorityOption = computed(() => ({
    tooltip: { trigger: 'item' },
    radar: {
        indicator: (analyticsData.value.radarPriorityLabels ?? []).map(label => ({
            name: label, max: Math.max(...(analyticsData.value.radarPriorityData ?? [100]))
        })),
        radius: 90
    },
    series: [{
        type: 'radar',
        data: [{
            value: analyticsData.value.radarPriorityData ?? [],
            name: 'Employer Priorities'
        }],
        areaStyle: { opacity: 0.1 }
    }]
}));

const employerPreferencesSummary = computed(() => {
    const barData = analyticsData.value.barQualificationData ?? [];
    const radarLabels = analyticsData.value.radarPriorityLabels ?? [];
    const radarData = analyticsData.value.radarPriorityData ?? [];

    if (!barData.length && !radarData.length) {
        return "No employer preference data available for the current selection.";
    }

    let summary = "<ul class='list-disc ml-6 mb-2'>";

    // Bar Chart: Most requested qualifications
    if (barData.length) {
        const top = barData[0];
        summary += `<li>Most requested: <strong>${top.qualification}</strong> (${top.count} mentions)</li>`;
        if (barData.length > 1) {
            summary += `<li>Other in-demand: <strong>${barData.slice(1, 4).map(q => q.qualification).join(', ')}</strong></li>`;
        }
    }

    // Radar Chart: Employer priorities
    if (radarLabels.length && radarData.length) {
        const maxIdx = radarData.indexOf(Math.max(...radarData));
        summary += `<li>Top employer priority: <strong>${radarLabels[maxIdx]}</strong></li>`;
    }

    summary += "</ul>";
    return summary;
});


</script>

<template>
    <AppLayout title="Job Market Report">
        <Container class="py-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Job Market Reports</h2>
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

            <!-- Job Seeker and Role Alignment Section -->
            <div v-if="reportType === 'alignment'">
                <!-- Scatter Plot: Job Seeker & Role Alignment -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-braille text-purple-500"></i>
                        Job Seeker & Role Alignment <span class="font-bold text-purple-600">(Scatter Plot)</span>
                    </h3>
                    <div v-if="loading" class="flex justify-center items-center py-16">
                        <span class="loader mr-3"></span>
                        <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                    </div>
                    <div v-else>
                        <VueECharts v-if="analyticsData.scatterData && analyticsData.scatterData.length"
                            :option="scatterOption" style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No alignment data available.</div>
                    </div>
                </div>

                <!-- Bar Chart: Alignment (Meets, Exceeds, Falls Short) -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-chart-bar text-yellow-500"></i>
                        Applicant Alignment <span class="font-bold text-yellow-600">(Bar Chart)</span>
                    </h3>
                    <div v-if="loading" class="flex justify-center items-center py-16">
                        <span class="loader mr-3"></span>
                        <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                    </div>
                    <div v-else>
                        <VueECharts v-if="analyticsData.barAlignment" :option="alignmentBarOption"
                            style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No applicant alignment data available.</div>
                    </div>
                </div>

                <div class="mt-8 flex justify-center">
                    <div class="w-full md:w-2/3 lg:w-1/2 shadow-lg rounded-xl bg-white border border-gray-200">
                        <div class="flex items-center px-6 pt-6 pb-2">
                            <!-- Heroicon: Chart Bar -->
                            <svg class="w-6 h-6 text-purple-500 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 3v18h18M9 17V9m4 8V5m4 12v-6" />
                            </svg>
                            <span class="text-lg font-semibold text-gray-800">Analytics Summary</span>
                        </div>
                        <div class="px-6 pb-6 text-gray-700 leading-relaxed">
                            <span v-html="alignmentSummary"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Matching Success Rate Section -->
            <div v-if="reportType === 'matching'">

                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-percentage text-green-500"></i>
                        Matching Success Rate
                    </h3>
                    <div class="mb-6">
                        <span class="text-2xl font-bold text-green-600">
                            {{ analyticsData.matchingSuccessRate ?? 0 }}%
                        </span>
                        <span class="ml-2 text-gray-500">of applicants matched successfully</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <div class="text-center font-semibold mb-2 text-gray-700">Funnel Chart</div>
                            <VueECharts v-if="analyticsData.funnelData" :option="funnelOption"
                                style="height: 300px; width: 100%;" />
                        </div>
                        <div>
                            <div class="text-center font-semibold mb-2 text-gray-700">Pie Chart</div>
                            <VueECharts v-if="analyticsData.pieMatchData" :option="matchPieOption"
                                style="height: 300px; width: 100%;" />
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-center">
                    <div class="w-full md:w-2/3 lg:w-1/2 shadow-lg rounded-xl bg-white border border-gray-200">
                        <div class="flex items-center px-6 pt-6 pb-2">
                            <!-- Heroicon: Chart Bar -->
                            <svg class="w-6 h-6 text-green-500 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 3v18h18M9 17V9m4 8V5m4 12v-6" />
                            </svg>
                            <span class="text-lg font-semibold text-gray-800">Analytics Summary</span>
                        </div>
                        <div class="px-6 pb-6 text-gray-700 leading-relaxed">
                            <span v-html="matchingSummary"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Future Job Trends Section -->
            <div v-if="reportType === 'future'">
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-chart-line text-blue-500"></i>
                        Future Job Trends
                    </h3>
                    <div>
                        <div class="text-center font-semibold mb-2 text-gray-700">Line Chart</div>
                        <VueECharts v-if="analyticsData.lineTrendData" :option="lineTrendOption"
                            style="height: 300px; width: 100%;" />
                    </div>

                </div>

                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-industry text-indigo-500"></i>
                        Industry Demand Over Time <span class="font-bold text-indigo-600">(Area Chart)</span>
                    </h3>
                    <VueECharts v-if="analyticsData.industryAreaChart" :option="industryAreaOption"
                        style="height: 350px; width: 100%;" />
                    <div v-else class="text-gray-400 text-center py-12">No industry area data available.</div>
                </div>

                <!-- Skill Demand Area Chart -->
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-tools text-green-500"></i>
                        Skill Demand Over Time <span class="font-bold text-green-600">(Area Chart)</span>
                    </h3>
                    <VueECharts v-if="analyticsData.skillAreaChart" :option="skillAreaOption"
                        style="height: 350px; width: 100%;" />
                    <div v-else class="text-gray-400 text-center py-12">No skill area data available.</div>
                </div>
                <!-- Analytics Summary/Interpretation for Future Job Trends -->
                <div class="mt-8 flex justify-center">
                    <div class="w-full md:w-2/3 lg:w-1/2 shadow-lg rounded-xl bg-white border border-gray-200">
                        <div class="flex items-center px-6 pt-6 pb-2">
                            <!-- Heroicon: Chart Bar -->
                            <svg class="w-6 h-6 text-blue-500 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 3v18h18M9 17V9m4 8V5m4 12v-6" />
                            </svg>
                            <span class="text-lg font-semibold text-gray-800">Analytics Summary</span>
                        </div>
                        <div class="px-6 pb-6 text-gray-700 leading-relaxed">
                            <span v-html="futureTrendsSummary"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employer Preferences Section -->
            <div v-if="reportType === 'preference'">
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-user-tie text-yellow-500"></i>
                        Employer Preferences
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <div class="text-center font-semibold mb-2 text-gray-700">Most Requested Qualifications (Bar
                                Chart)</div>
                            <VueECharts v-if="analyticsData.barQualificationData" :option="barQualificationOption"
                                style="height: 300px; width: 100%;" />
                        </div>
                        <div>
                            <div class="text-center font-semibold mb-2 text-gray-700">Employer Priorities (Radar Chart)
                            </div>
                            <VueECharts v-if="analyticsData.radarPriorityData" :option="radarPriorityOption"
                                style="height: 300px; width: 100%;" />
                        </div>
                    </div>


                </div>
                <!-- Analytics Summary/Interpretation -->
                <div class="mt-8 flex justify-center">
                    <div class="w-full md:w-2/3 lg:w-1/2 shadow-lg rounded-xl bg-white border border-gray-200">
                        <div class="flex items-center px-6 pt-6 pb-2">
                            <span class="material-icons text-blue-500 mr-2">insights</span>
                            <span class="text-lg font-semibold text-gray-800">Analytics Summary</span>
                        </div>
                        <div class="px-6 pb-6 text-gray-700 leading-relaxed">
                            <span v-html="employerPreferencesSummary"></span>
                        </div>
                    </div>
                </div>
            </div>



        </Container>
    </AppLayout>
</template>