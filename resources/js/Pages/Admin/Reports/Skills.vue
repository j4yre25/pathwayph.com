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
</script>

<template>
    <AppLayout title="Skills & Roles Analytics">
        <Container class="py-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Skills Gap & Roles Analysis</h2>

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

                  <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-8 mb-8">
                <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                    <i class="fas fa-cloud text-purple-500"></i>
                    <span>Skills & Roles <span class="font-bold text-purple-600">Word Cloud</span></span>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h4 class="font-semibold mb-4 text-gray-700 text-base tracking-wide">Job Roles</h4>
                        <div class="bg-gray-50 rounded-lg p-4 shadow-inner">
                            <VueECharts v-if="jobRolesWordCloud.length" :option="jobRolesWordCloudOption" style="height: 300px; width: 100%;" />
                            <div v-else class="text-gray-400 text-center py-8">No job roles data available.</div>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4 text-gray-700 text-base tracking-wide">Skills</h4>
                        <div class="bg-gray-50 rounded-lg p-4 shadow-inner">
                            <VueECharts v-if="skillsWordCloud.length" :option="skillsWordCloudOption" style="height: 300px; width: 100%;" />
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
                        <h4 class="font-semibold mb-4 text-gray-700 text-base tracking-wide">Top Skills by Demand</h4>
                        <ul class="space-y-2">
                            <li v-for="item in topSkillDemand" :key="item.skill" class="flex items-center justify-between px-4 py-2 bg-gray-50 rounded">
                                <span class="font-medium text-gray-800">{{ item.skill }}</span>
                                <span class="text-xs bg-blue-100 text-blue-700 rounded-full px-2 py-0.5 font-semibold">{{ item.count }}</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4 text-gray-700 text-base tracking-wide">Top Skills by Supply</h4>
                        <ul class="space-y-2">
                            <li v-for="item in topSkillSupply" :key="item.skill" class="flex items-center justify-between px-4 py-2 bg-gray-50 rounded">
                                <span class="font-medium text-gray-800">{{ item.skill }}</span>
                                <span class="text-xs bg-green-100 text-green-700 rounded-full px-2 py-0.5 font-semibold">{{ item.count }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </Container>
    </AppLayout>
</template>