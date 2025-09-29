<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, onMounted, computed } from 'vue'
import VueECharts from 'vue-echarts'
import axios from 'axios'
import Container from '@/Components/Container.vue';

const loading = ref(false)
const skillsData = ref({})

function fetchSkillsData() {
    loading.value = true
    axios.get(route('peso.reports.skills.data'))
        .then(res => {
            skillsData.value = res.data
            console.log('Skill Data:', res.data);
        })
        .finally(() => {
            loading.value = false
        })
}

onMounted(fetchSkillsData)

// --- Chart options ---
const heatmapOption = computed(() => ({
    tooltip: { trigger: 'item', formatter: params => `${params.data.industry}<br/>${params.data.skill}: ${params.data.demand}` },
    grid: { left: '5%', right: '5%', top: '10%', bottom: '10%' },
    xAxis: { type: 'category', data: Array.from(new Set((skillsData.value.heatmap ?? []).map(d => d.skill))), name: 'Skill', axisLabel: { rotate: 30 } },
    yAxis: { type: 'category', data: Array.from(new Set((skillsData.value.heatmap ?? []).map(d => d.industry))), name: 'Industry' },
    visualMap: { min: 0, max: Math.max(...(skillsData.value.heatmap ?? []).map(d => d.demand), 10), calculable: true, orient: 'horizontal', left: 'center', bottom: '5%' },
    series: [{
        type: 'heatmap',
        data: (skillsData.value.heatmap ?? []).map(d => [d.skill, d.industry, d.demand, d]),
        encode: { x: 0, y: 1, value: 2 },
        label: { show: false }
    }]
}))

const bubbleOption = computed(() => ({
    tooltip: {
        trigger: 'item',
        // params.data is [supply, demand, size, skill]
        formatter: params =>
            `${params.data[3]}<br/>Demand: ${params.data[1]}<br/>Supply: ${params.data[0]}`
    },
    xAxis: { name: 'Supply', type: 'value' },
    yAxis: { name: 'Demand', type: 'value' },
    series: [{
        type: 'scatter',
        symbolSize: val => Math.max(val[2], 10),
        data: (skillsData.value.bubbleData ?? []).map(d => [d.supply, d.demand, d.size, d.skill]),
        label: {
            show: true,
            formatter: params => params.data[3], // skill name
            position: 'top'
        },
        itemStyle: { color: '#3b82f6', opacity: 0.7 }
    }]
}))

const barOption = computed(() => ({
    tooltip: { trigger: 'axis' },
    xAxis: { type: 'category', data: Array.from(new Set((skillsData.value.barChartData ?? []).map(d => d.skill))), axisLabel: { rotate: 30 } },
    yAxis: { type: 'value', name: 'Demand' },
    series: [{
        name: 'Demand',
        type: 'bar',
        data: (skillsData.value.barChartData ?? []).map(d => d.demand),
        itemStyle: { color: '#f59e42' }
    }]
}))

const topSkillDemand = computed(() => Object.entries(skillsData.value.topSkillDemand ?? {}).map(([skill, count]) => ({ skill, count })))
const topSkillSupply = computed(() => Object.entries(skillsData.value.topSkillSupply ?? {}).map(([skill, count]) => ({ skill, count })))


const jobRolesWordCloudOption = computed(() => ({
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
        data: jobRolesWordCloud.value.map(item => ({
            name: item.role,
            value: item.count
        }))
    }]
}));

const skillsWordCloudOption = computed(() => ({
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
        data: skillsWordCloud.value.map(item => ({
            name: item.skill,
            value: item.count
        }))
    }]
}));

const jobRolesWordCloud = computed(() => {
    const raw = skillsData.value.jobRolesWordCloud;
    if (Array.isArray(raw)) {
        return raw;
    }
    if (raw && typeof raw === 'object') {
        // Convert {role: count, ...} to [{role, count}]
        return Object.entries(raw).map(([role, count]) => ({ role, count }));
    }
    return [];
});

const skillsWordCloud = computed(() => {
    const raw = skillsData.value.skillsWordCloud;
    if (Array.isArray(raw)) {
        return raw;
    }
    if (raw && typeof raw === 'object') {
        // Convert {skill: count, ...} to [{skill, count}]
        return Object.entries(raw).map(([skill, count]) => ({ skill, count }));
    }
    return [];
});

const reportType = ref('gap'); // 'gap' or 'roles'

const reportTypes = [
    { key: 'gap', label: 'Skills Gap Analysis' },
    { key: 'roles', label: 'Skills & Roles Analysis' }
];



// Skills Gap Summary
const skillsGapSummary = computed(() => {
    const heatmap = skillsData.value.heatmap ?? [];
    const bubble = skillsData.value.bubbleData ?? [];
    let summary = "<ul class='list-disc ml-6 mb-2'>";

    // Heatmap: Most critical skill gap
    if (heatmap.length) {
        const topGap = heatmap.reduce((a, b) => (b.demand > a.demand ? b : a));
        summary += `<li>Largest skill gap: <strong>${topGap.skill}</strong> in <strong>${topGap.industry}</strong> (Demand: ${topGap.demand})</li>`;
    }

    // Bubble: Skill with highest demand-supply gap
    if (bubble.length) {
        const top = bubble.reduce((a, b) => ((b.demand - b.supply) > (a.demand - a.supply) ? b : a));
        summary += `<li>Highest demand-supply gap: <strong>${top.skill}</strong> (Demand: ${top.demand}, Supply: ${top.supply})</li>`;
    }

    summary += "</ul>";
    return summary;
});

// Skills & Roles Summary
const skillsRolesSummary = computed(() => {
    const jobRoles = jobRolesWordCloud.value ?? [];
    const skills = skillsWordCloud.value ?? [];
    const bar = skillsData.value.barChartData ?? [];
    const topDemand = topSkillDemand.value ?? [];
    const topSupply = topSkillSupply.value ?? [];
    let summary = "<ul class='list-disc ml-6 mb-2'>";

    // Word Cloud: Most frequent job role
    if (jobRoles.length) {
        const topRole = jobRoles.reduce((a, b) => (b.count > a.count ? b : a));
        summary += `<li>Most frequent job role: <strong>${topRole.role}</strong> (${topRole.count} mentions)</li>`;
    }
    // Word Cloud: Most frequent skill
    if (skills.length) {
        const topSkill = skills.reduce((a, b) => (b.count > a.count ? b : a));
        summary += `<li>Most frequent skill: <strong>${topSkill.skill}</strong> (${topSkill.count} mentions)</li>`;
    }
    // Bar Chart: Skill with highest demand
    if (bar.length) {
        const topBar = bar.reduce((a, b) => (b.demand > a.demand ? b : a));
        summary += `<li>Skill with highest demand: <strong>${topBar.skill}</strong> (${topBar.demand})</li>`;
    }
    // Top Skills by Demand/Supply
    if (topDemand.length) {
        summary += `<li>Top skill by demand: <strong>${topDemand[0].skill}</strong> (${topDemand[0].count})</li>`;
    }
    if (topSupply.length) {
        summary += `<li>Top skill by supply: <strong>${topSupply[0].skill}</strong> (${topSupply[0].count})</li>`;
    }

    summary += "</ul>";
    return summary;
});
</script>

<template>
    <AppLayout title="Skills & Roles Analytics">
        <Container class="py-8">
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
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Skills Gap & Roles Analysis</h2>

            <div v-if="reportType === 'gap'">
                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-chart-bar text-blue-500"></i>
                        Skills Gap Analysis: <span class="font-bold text-blue-600">Heatmap</span>
                    </h3>
                    <div v-if="loading" class="flex justify-center items-center py-16">
                        <span class="loader mr-3"></span>
                        <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                    </div>
                    <div v-else>
                        <VueECharts v-if="skillsData.heatmap && skillsData.heatmap.length" :option="heatmapOption"
                            style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No heatmap data available.</div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-braille text-green-500"></i>
                        Skill Demand vs Supply <span class="font-bold text-green-600">(Bubble Chart)</span>
                    </h3>
                    <div v-if="loading" class="flex justify-center items-center py-16">
                        <span class="loader mr-3"></span>
                        <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                    </div>
                    <div v-else>
                        <VueECharts v-if="skillsData.bubbleData && skillsData.bubbleData.length" :option="bubbleOption"
                            style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No bubble chart data available.</div>
                    </div>
                </div>
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
                            <span v-html="skillsGapSummary"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="reportType === 'roles'">


                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-cloud text-purple-500"></i>
                        <span>Skills & Roles <span class="font-bold text-purple-600">Word Cloud</span></span>
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h4 class="font-semibold mb-4 text-gray-700 text-base tracking-wide">Job Roles</h4>
                            <div class="bg-gray-50 rounded-lg p-4 shadow-inner">
                                <VueECharts v-if="jobRolesWordCloud.length" :option="jobRolesWordCloudOption"
                                    style="height: 300px; width: 100%;" />
                                <div v-else class="text-gray-400 text-center py-8">No job roles data available.</div>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-4 text-gray-700 text-base tracking-wide">Skills</h4>
                            <div class="bg-gray-50 rounded-lg p-4 shadow-inner">
                                <VueECharts v-if="skillsWordCloud.length" :option="skillsWordCloudOption"
                                    style="height: 300px; width: 100%;" />
                                <div v-else class="text-gray-400 text-center py-8">No skills data available.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-chart-bar text-yellow-500"></i>
                        Skill Demand by Job Title <span class="font-bold text-yellow-600">(Bar Chart)</span>
                    </h3>
                    <div v-if="loading" class="flex justify-center items-center py-16">
                        <span class="loader mr-3"></span>
                        <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                    </div>
                    <div v-else>
                        <VueECharts v-if="skillsData.barChartData && skillsData.barChartData.length" :option="barOption"
                            style="height: 400px; width: 100%;" />
                        <div v-else class="text-gray-400 text-center py-12">No bar chart data available.</div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-ranking-star text-pink-500"></i>
                        Top Skills <span class="font-bold text-pink-600">Ranking</span>
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h4 class="font-semibold mb-4 text-gray-700 text-base tracking-wide">Top Skills by Demand
                            </h4>
                            <ul class="space-y-2">
                                <li v-for="item in topSkillDemand" :key="item.skill"
                                    class="flex items-center justify-between px-4 py-2 bg-gray-50 rounded">
                                    <span class="font-medium text-gray-800">{{ item.skill }}</span>
                                    <span
                                        class="text-xs bg-blue-100 text-blue-700 rounded-full px-2 py-0.5 font-semibold">{{
                                            item.count }}</span>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-4 text-gray-700 text-base tracking-wide">Top Skills by Supply
                            </h4>
                            <ul class="space-y-2">
                                <li v-for="item in topSkillSupply" :key="item.skill"
                                    class="flex items-center justify-between px-4 py-2 bg-gray-50 rounded">
                                    <span class="font-medium text-gray-800">{{ item.skill }}</span>
                                    <span
                                        class="text-xs bg-green-100 text-green-700 rounded-full px-2 py-0.5 font-semibold">{{
                                            item.count }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Analytics Summary Card for Skills & Roles -->
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
                            <span v-html="skillsRolesSummary"></span>
                        </div>
                    </div>
                </div>
            </div>

        </Container>
    </AppLayout>
</template>