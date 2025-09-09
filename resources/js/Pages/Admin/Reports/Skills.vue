<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, onMounted, computed } from 'vue'
import VueECharts from 'vue-echarts'
import axios from 'axios'

const loading = ref(false)
const skillsData = ref({})

function fetchSkillsData() {
    loading.value = true
    axios.get(route('peso.reports.skills.data'))
        .then(res => {
            skillsData.value = res.data
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
    tooltip: { trigger: 'item', formatter: params => `${params.data.skill}<br/>Demand: ${params.data.demand}<br/>Supply: ${params.data.supply}` },
    xAxis: { name: 'Supply', type: 'value' },
    yAxis: { name: 'Demand', type: 'value' },
    series: [{
        type: 'scatter',
        symbolSize: val => Math.max(val[2], 10),
        data: (skillsData.value.bubbleData ?? []).map(d => [d.supply, d.demand, d.size, d.skill]),
        label: { show: true, formatter: params => params.data[3], position: 'top' },
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
const jobRolesWordCloud = computed(() => Object.entries(skillsData.value.jobRolesWordCloud ?? {}).map(([role, count]) => ({ role, count })))
const skillsWordCloud = computed(() => Object.entries(skillsData.value.skillsWordCloud ?? {}).map(([skill, count]) => ({ skill, count })))
</script>

<template>
    <AppLayout title="Skills & Roles Analytics">
        <div class="py-8">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Skills Gap & Roles Analysis</h2>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-chart-bar text-blue-500 mr-2"></i>
                    Skills Gap Analysis: Heatmap
                </h3>
                <div v-if="loading" class="flex justify-center items-center py-12">
                    <svg class="animate-spin h-8 w-8 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                </div>
                <div v-else>
                    <VueECharts v-if="skillsData.heatmap && skillsData.heatmap.length" :option="heatmapOption" style="height: 400px; width: 100%;" />
                    <div v-else class="text-gray-400 text-center py-8">No heatmap data available.</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-braille text-green-500 mr-2"></i>
                    Skill Demand vs Supply (Bubble Chart)
                </h3>
                <div v-if="loading" class="flex justify-center items-center py-12">
                    <svg class="animate-spin h-8 w-8 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                </div>
                <div v-else>
                    <VueECharts v-if="skillsData.bubbleData && skillsData.bubbleData.length" :option="bubbleOption" style="height: 400px; width: 100%;" />
                    <div v-else class="text-gray-400 text-center py-8">No bubble chart data available.</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-cloud text-purple-500 mr-2"></i>
                    Skills & Roles Word Cloud
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold mb-2 text-gray-700">Job Roles</h4>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="item in jobRolesWordCloud" :key="item.role"
                                class="inline-block px-2 py-1 rounded bg-blue-100 text-blue-700 text-xs font-semibold">
                                {{ item.role }} <span class="font-normal">({{ item.count }})</span>
                            </span>
                        </div>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-2 text-gray-700">Skills</h4>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="item in skillsWordCloud" :key="item.skill"
                                class="inline-block px-2 py-1 rounded bg-green-100 text-green-700 text-xs font-semibold">
                                {{ item.skill }} <span class="font-normal">({{ item.count }})</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-chart-bar text-yellow-500 mr-2"></i>
                    Skill Demand by Job Title (Bar Chart)
                </h3>
                <div v-if="loading" class="flex justify-center items-center py-12">
                    <svg class="animate-spin h-8 w-8 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                    </svg>
                    <span class="text-blue-500 text-lg font-semibold">Loading analytics...</span>
                </div>
                <div v-else>
                    <VueECharts v-if="skillsData.barChartData && skillsData.barChartData.length" :option="barOption" style="height: 400px; width: 100%;" />
                    <div v-else class="text-gray-400 text-center py-8">No bar chart data available.</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-ranking-star text-pink-500 mr-2"></i>
                    Top Skills Ranking
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold mb-2 text-gray-700">Top Skills by Demand</h4>
                        <ul class="list-disc ml-6">
                            <li v-for="item in topSkillDemand" :key="item.skill">
                                {{ item.skill }} <span class="text-gray-500">({{ item.count }})</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-2 text-gray-700">Top Skills by Supply</h4>
                        <ul class="list-disc ml-6">
                            <li v-for="item in topSkillSupply" :key="item.skill">
                                {{ item.skill }} <span class="text-gray-500">({{ item.count }})</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>