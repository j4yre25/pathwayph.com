<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch, onMounted, computed } from 'vue'
import VueECharts from 'vue-echarts';

import * as echarts from 'echarts/core'
import { BarChart, HeatmapChart, LineChart } from 'echarts/charts'
import { TitleComponent, TooltipComponent, GridComponent, VisualMapComponent, LegendComponent, GeoComponent } from 'echarts/components'
import { CanvasRenderer } from 'echarts/renderers'

// Register required ECharts components
echarts.use([
  BarChart,
  GeoComponent,
  HeatmapChart,
  TitleComponent,
  TooltipComponent,
  GridComponent,
  VisualMapComponent,
  LegendComponent,
  CanvasRenderer,
  LineChart,
])

import phMap from '@/maps/ph.json'
echarts.registerMap('PH', phMap)

// Use defineProps with object syntax for better type safety and IDE support
const props = defineProps({
  statusCounts: { type: Object, default: () => ({}) },
  employed: { type: Number, default: 0 },
  unemployed: { type: Number, default: 0 },
  programNames: { type: Array, default: () => [] },
  employedByProgram: { type: Array, default: () => [] },
  unemployedByProgram: { type: Array, default: () => [] },
  jobRolesWordCloud: { type: Object, default: () => ({}) },
  skillsWordCloud: { type: Object, default: () => ({}) },
  topSkillDemand: { type: Object, default: () => ({}) },
  topSkillSupply: { type: Object, default: () => ({}) },
  summary: { type: Object, default: () => ({ employed: 0, unemployed: 0, further_studies: 0 }) },
  unemploymentOverTime: { type: Array, default: () => [] },
  employmentTrend: { type: Array, default: () => [] },
  jobPlacementTrend: { type: Array, default: () => [] },
  locationStats: { type: Array, default: () => [] },
  jobOpeningsByLocation: { type: Object, default: () => ({}) },
  jobSeekersByLocation: { type: Object, default: () => ({}) },
  referralByLocation: { type: Object, default: () => ({}) },
  referralSuccessHeatmap: { type: Array, default: () => [] },

})

// KPI values
const employed = computed(() => props.statusCounts?.Employed ?? props.employed ?? 0)
const unemployed = computed(() => props.statusCounts?.Unemployed ?? props.unemployed ?? 0)
const totalGraduates = computed(() => {
  // If you want to include underemployed, add it here
  return employed.value + unemployed.value + (props.statusCounts?.Underemployed ?? 0)
})

// Pie chart for employment status
const pieOption = ref({
  tooltip: {
    trigger: 'item',
    formatter: '{b}: {c} ({d}%)'
  },
  legend: {
    orient: 'vertical',
    left: 'left'
  },
  series: [
    {
      name: 'Status',
      type: 'pie',
      radius: '60%',
      data: [],
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
})

const updatePieChart = () => {
  pieOption.value.series[0].data = Object.entries(props.statusCounts || {}).map(
    ([status, count]) => ({
      name: status,
      value: count
    })
  )
}
watch(() => props.statusCounts, updatePieChart, { immediate: true })
onMounted(updatePieChart)

// Bar chart for employment by program
const barOption = computed(() => ({
  tooltip: { trigger: 'axis' },
  legend: { data: ['Employed', 'Unemployed'] },
  xAxis: { type: 'category', data: props.programNames },
  yAxis: { type: 'value' },
  series: [
    {
      name: 'Employed',
      type: 'bar',
      stack: 'total',
      emphasis: { focus: 'series' },
      data: props.employedByProgram,
      itemStyle: { color: '#22c55e' }
    },
    {
      name: 'Unemployed',
      type: 'bar',
      stack: 'total',
      emphasis: { focus: 'series' },
      data: props.unemployedByProgram,
      itemStyle: { color: '#ef4444' }
    }
  ]
}))

// Heatmap for employment rate by program
const employmentRates = computed(() =>
  props.programNames.map((name, idx) => {
    const total = (props.employedByProgram[idx] ?? 0) + (props.unemployedByProgram[idx] ?? 0)
    return total ? (props.employedByProgram[idx] ?? 0) / total : 0
  })
)
const heatmapData = computed(() =>
  employmentRates.value.map((rate, idx) => [idx, 0, rate])
)
const heatmapOption = computed(() => ({
  tooltip: {
    formatter: params =>
      `${props.programNames[params.data[0]]}: ${(params.data[2] * 100).toFixed(1)}%`
  },
  grid: {
    top: 40,
    left: 80,
    right: 40,
    bottom: 60,
    containLabel: true
  },
  xAxis: {
    type: 'category',
    data: props.programNames,
    splitArea: { show: true },
    axisLabel: {
      rotate: 30,
      fontSize: 14,
      margin: 16,
      interval: 0
    }
  },
  yAxis: {
    type: 'category',
    data: ['Employment Rate'],
    splitArea: { show: true },
    axisLabel: {
      fontSize: 16,
      margin: 24
    }
  },
  visualMap: {
    min: 0,
    max: 1,
    calculable: true,
    orient: 'horizontal',
    left: 'center',
    bottom: 10,
    inRange: { color: ['#fca5a5', '#22c55e'] }
  },
  series: [
    {
      name: 'Employment Rate',
      type: 'heatmap',
      data: heatmapData.value,
      label: {
        show: true,
        formatter: params => `${(params.data[2] * 100).toFixed(1)}%`,
        fontSize: 14
      },
      emphasis: { itemStyle: { shadowBlur: 10, shadowColor: 'rgba(0,0,0,0.5)' } }
    }
  ]
}))

// For word cloud, you can use a Vue word cloud component or just list the top items
const topRoles = computed(() =>
  Object.entries(props.jobRolesWordCloud)
    .sort((a, b) => b[1] - a[1])
    .slice(0, 20)
)
const topSkills = computed(() =>
  Object.entries(props.skillsWordCloud)
    .sort((a, b) => b[1] - a[1])
    .slice(0, 20)
)

// Bar chart for skill demand vs supply
const skillBarOption = computed(() => ({
  tooltip: { trigger: 'axis' },
  legend: { data: ['Demand (Jobs)', 'Supply (Graduates)'] },
  xAxis: {
    type: 'category',
    data: Object.keys(props.topSkillDemand),
    axisLabel: { rotate: 30, fontSize: 12 }
  },
  yAxis: { type: 'value' },
  series: [
    {
      name: 'Demand (Jobs)',
      type: 'bar',
      data: Object.values(props.topSkillDemand),
      itemStyle: { color: '#f59e42' }
    },
    {
      name: 'Supply (Graduates)',
      type: 'bar',
      data: Object.keys(props.topSkillDemand).map(
        skill => props.topSkillSupply[skill] ?? 0
      ),
      itemStyle: { color: '#3b82f6' }
    }
  ]
}))

// Pie chart for unemployment rate (Employed, Unemployed, Further Studies)
const unemploymentPieOption = computed(() => ({
  tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
  legend: { orient: 'vertical', left: 'left' },
  series: [
    {
      name: 'Status',
      type: 'pie',
      radius: '60%',
      data: [
        { name: 'Employed', value: props.summary.employed ?? 0 },
        { name: 'Unemployed', value: props.summary.unemployed ?? 0 },
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
}));

// Area chart for unemployment rate over time
const areaOption = computed(() => ({
  tooltip: {
    trigger: 'axis',
    formatter: params => {
      return params.map(p =>
        `${p.seriesName}: ${p.value}%`
      ).join('<br>');
    }
  },
  legend: { data: ['Unemployment Rate', 'Employment Rate'] },
  xAxis: {
    type: 'category',
    data: props.unemploymentOverTime.map(item => item.year),
    boundaryGap: false,
    axisLabel: { fontSize: 14 }
  },
  yAxis: {
    type: 'value',
    axisLabel: { formatter: '{value}%', fontSize: 14 }
  },
  series: [
    {
      name: 'Unemployment Rate',
      type: 'line',
      data: props.unemploymentOverTime.map(item => item.unemployment_rate),
      areaStyle: { color: '#ef4444', opacity: 0.3 },
      itemStyle: { color: '#ef4444' },
      lineStyle: { color: '#ef4444' },
      smooth: true
    },
    {
      name: 'Employment Rate',
      type: 'line',
      data: props.unemploymentOverTime.map(item => item.employment_rate),
      areaStyle: { color: '#22c55e', opacity: 0.2 },
      itemStyle: { color: '#22c55e' },
      lineStyle: { color: '#22c55e' },
      smooth: true
    }
  ]
}));

// Employment Trend Over Time (Line Chart)
const employmentTrendOption = computed(() => ({
  tooltip: { trigger: 'axis' },
  xAxis: {
    type: 'category',
    data: props.employmentTrend.map(item => item.year),
    axisLabel: { fontSize: 14 }
  },
  yAxis: {
    type: 'value',
    axisLabel: { formatter: '{value}%', fontSize: 14 }
  },
  series: [
    {
      name: 'Employment Rate',
      type: 'line',
      data: props.employmentTrend.map(item => item.employment_rate),
      itemStyle: { color: '#3b82f6' },
      lineStyle: { color: '#3b82f6' },
      smooth: true
    }
  ]
}));

// Job Placement Rate Over Time (Area Chart)
const jobPlacementOption = computed(() => ({
  tooltip: { trigger: 'axis' },
  xAxis: {
    type: 'category',
    data: props.jobPlacementTrend.map(item => item.year),
    axisLabel: { fontSize: 14 }
  },
  yAxis: {
    type: 'value',
    axisLabel: { formatter: '{value}%', fontSize: 14 }
  },
  series: [
    {
      name: 'Job Placement Rate',
      type: 'line',
      areaStyle: { color: '#f59e42', opacity: 0.3 },
      data: props.jobPlacementTrend.map(item => item.placement_rate),
      itemStyle: { color: '#f59e42' },
      lineStyle: { color: '#f59e42' },
      smooth: true
    }
  ]
}));

// Bubble Map: Job Openings vs. Job Seekers
const bubbleMapData = computed(() =>
  Object.keys(props.jobOpeningsByLocation).map(loc => ({
    name: loc,
    value: [
      loc, // For a real map, use [lng, lat] or region name
      props.jobOpeningsByLocation[loc],
      props.jobSeekersByLocation[loc] ?? 0
    ],
    symbolSize: Math.max(props.jobOpeningsByLocation[loc], 10)
  }))
);

// Heatmap: Referral Success Rate
const heatmapMapData = computed(() =>
  props.referralSuccessHeatmap.map(item => ({
    name: item.name,
    value: item.rate
  }))
);

// Example ECharts option for Bubble Map (using geo or map)
const bubbleMapOption = computed(() => ({
  tooltip: {
    trigger: 'item',
    formatter: p => `${p.name}<br>Jobs: ${p.value[1]}<br>Seekers: ${p.value[2]}`
  },
  geo: {
    map: 'PH', // Use 'world' or your country/region map
    roam: true,
    label: { show: false },
    itemStyle: { areaColor: '#e0e7ef', borderColor: '#999' }
  },
  series: [
    {
      name: 'Job Openings',
      type: 'scatter',
      coordinateSystem: 'geo',
      data: bubbleMapData.value,
      symbolSize: val => Math.sqrt(val[1]) * 4, // Bubble size by job openings
      itemStyle: { color: '#3b82f6', opacity: 0.7 }
    }
  ]
}));

// Example ECharts option for Heatmap
const referralHeatmapOption = computed(() => ({
  tooltip: {
    trigger: 'item',
    formatter: p => `${p.name}<br>Referral Success Rate: ${p.value}%`
  },
  geo: {
    map: 'PH',
    roam: true,
    label: { show: false },
    itemStyle: { areaColor: '#e0e7ef', borderColor: '#999' }
  },
  visualMap: {
    min: 0,
    max: 100,
    left: 'left',
    top: 'bottom',
    text: ['High', 'Low'],
    inRange: { color: ['#fca5a5', '#22c55e'] },
    calculable: true
  },
  series: [
    {
      name: 'Referral Success Rate',
      type: 'heatmap',
      coordinateSystem: 'geo',
      data: heatmapMapData.value
    }
  ]
}));
</script>

<template>
  <AppLayout title="PESO Reports">
    <div class="max-w-5xl mx-auto py-8 px-4">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Employment Status Overview</h2>
      <!-- KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
          <span class="text-gray-500 text-sm">Employed</span>
          <span class="text-3xl font-bold text-green-600">{{ employed }}</span>
        </div>
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
          <span class="text-gray-500 text-sm">Unemployed</span>
          <span class="text-3xl font-bold text-red-600">{{ unemployed }}</span>
        </div>
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
          <span class="text-gray-500 text-sm">Total Graduates</span>
          <span class="text-3xl font-bold text-blue-600">{{ totalGraduates }}</span>
        </div>
      </div>
      <div v-if="statusCounts && Object.keys(statusCounts).length" class="bg-white rounded-xl shadow p-8">
        <div class="flex flex-col lg:flex-row gap-12 items-center justify-between">
          <div class="w-full lg:w-2/5 mb-8 lg:mb-0">
            <h3 class="text-lg font-semibold mb-6 text-gray-700">Detailed Status</h3>
            <ul class="space-y-4">
              <li v-for="(count, status) in statusCounts" :key="status"
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
      </div>
      <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Employment By Program</h2>

      <!-- Stacked Bar Chart -->
      <div class="bg-white rounded-xl shadow p-8 mt-12">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Employment by Program (Stacked Bar)</h3>
        <VueECharts :option="barOption" style="height: 400px; width: 100%;" />
      </div>

      <!-- Heatmap -->
      <div class="bg-white rounded-xl shadow p-8 mt-12">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Program Employment Rate Heatmap</h3>
        <VueECharts :option="heatmapOption" style="height: 350px; width: 100%;" />
      </div>

      <!-- Skills and Roles Analysis -->
      <div class="bg-white rounded-xl shadow p-8 mt-12">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Skills and Roles Analysis</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div>
            <h4 class="font-semibold mb-2">Top Job Roles (Employed Graduates)</h4>
            <ul class="flex flex-wrap gap-2">
              <li v-for="[role, count] in topRoles" :key="role"
                class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                {{ role }} <span class="font-bold">({{ count }})</span>
              </li>
            </ul>
            <h4 class="font-semibold mt-6 mb-2">Top Skills (Employed Graduates)</h4>
            <ul class="flex flex-wrap gap-2">
              <li v-for="[skill, count] in topSkills" :key="skill"
                class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                {{ skill }} <span class="font-bold">({{ count }})</span>
              </li>
            </ul>
          </div>
          <div>
            <h4 class="font-semibold mb-2">Top Skills: Demand vs. Supply</h4>
            <VueECharts :option="skillBarOption" style="height: 320px; width: 100%;" />
          </div>
        </div>
      </div>

      <!-- Unemployment Rate Pie Chart -->
      <div class="bg-white rounded-xl shadow p-8 mt-12">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Unemployment Rate Distribution</h3>
        <VueECharts :option="unemploymentPieOption" style="height: 350px; width: 100%; max-width: 420px;" />
      </div>

      <!-- Unemployment Rate Over Time Area Chart -->
      <div class="bg-white rounded-xl shadow p-8 mt-12">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Unemployment Rate Over Time</h3>
        <VueECharts :option="areaOption" style="height: 350px; width: 100%;" />
      </div>

      <!-- Employment Trend Over Time Line Chart -->
      <div class="bg-white rounded-xl shadow p-8 mt-12">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Employment Trend Over Time</h3>
        <VueECharts :option="employmentTrendOption" style="height: 350px; width: 100%;" />
      </div>

      <!-- Job Placement Rate Over Time Area Chart -->
      <div class="bg-white rounded-xl shadow p-8 mt-12">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Job Placement Rate Over Time</h3>
        <VueECharts :option="jobPlacementOption" style="height: 350px; width: 100%;" />
      </div>

      <!-- Job Openings and Seekers Bubble Map -->
      <div class="bg-white rounded-xl shadow p-8 mt-12">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Job Openings vs. Job Seekers</h3>
        <VueECharts :option="bubbleMapOption" style="height: 400px; width: 100%;" />
      </div>

      <!-- Referral Success Rate Heatmap -->
      <div class="bg-white rounded-xl shadow p-8 mt-12">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Referral Success Rate Heatmap</h3>
        <VueECharts :option="referralHeatmapOption" style="height: 400px; width: 100%;" />
      </div>

      <!-- Bubble Map: Job Openings vs. Seekers -->
      <div class="bg-white rounded-xl shadow p-8 mt-12">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Job Openings vs. Job Seekers by Location (Bubble Map)</h3>
        <VueECharts :option="bubbleMapOption" style="height: 500px; width: 100%;" />
      </div>

      <!-- Heatmap: Referral Success Rate -->
      <div class="bg-white rounded-xl shadow p-8 mt-12">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Referral Success Rate by Location (Heatmap)</h3>
        <VueECharts :option="referralHeatmapOption" style="height: 500px; width: 100%;" />
      </div>
    </div>
  </AppLayout>
</template>