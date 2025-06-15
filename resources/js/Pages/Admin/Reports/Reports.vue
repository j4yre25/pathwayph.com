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

// Use defineProps with object syntax for better type safety and xIDE support
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
  graduates: { type: Array, default: () => [] },


})
const graduates = ref([]);
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

const filteredGraduates = computed(() => {
  return props.graduates.filter(g =>
    (!selectedYear.value || g.schoolYear?.school_year_range === selectedYear.value) &&
    (!selectedProgram.value || g.program_id === selectedProgram.value) &&
    (!selectedStatus.value || g.employment_status === selectedStatus.value) &&
    (!selectedLocation.value || g.location === selectedLocation.value)
  );
});
// FILTER CONTROLS
const selectedYear = ref('')
const selectedProgram = ref('')
const selectedStatus = ref('')
const selectedLocation = ref('')

const years = ref([])
const programs = ref([])
const locations = ref([])

// Populate filter options on mount
onMounted(() => {
  // Assuming `graduates` is available in the context
  if (graduates.value && graduates.value.length) {
    // Years
    const allYears = new Set()
    graduates.value.forEach(g => {
      if (g.schoolYear?.school_year_range) {
        const yearRange = g.schoolYear.school_year_range.split('-')
        if (yearRange.length === 2) {
          const startYear = parseInt(yearRange[0])
          const endYear = parseInt(yearRange[1])
          for (let y = startYear; y <= endYear; y++) {
            allYears.add(y)
          }
        }
      }
    })
    years.value = Array.from(allYears).sort((a, b) => b - a)

    // Programs and Locations (assuming these are available in graduates data)
    const allPrograms = new Set()
    const allLocations = new Set()
    graduates.value.forEach(g => {
      if (g.program_id) allPrograms.add(g.program_id)
      if (g.location) allLocations.add(g.location)
    })
    programs.value = Array.from(allPrograms)
    locations.value = Array.from(allLocations)
  }
})
</script>

<template>
  <AppLayout title="PESO Reports">
    <template #header>
      <div class="flex items-center">
        <i class="fas fa-chart-line text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Employment Analytics Dashboard</h2>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Filter Controls Card -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex items-center mb-4">
              <i class="fas fa-filter text-blue-500 mr-2"></i>
              <h3 class="text-lg font-medium text-gray-900">Filter Reports</h3>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                <select v-model="selectedYear" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                  <option value="">All Years</option>
                  <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Program</label>
                <select v-model="selectedProgram" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                  <option value="">All Programs</option>
                  <option v-for="program in programs" :key="program.id" :value="program.id">{{ program.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select v-model="selectedStatus" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                  <option value="">All Statuses</option>
                  <option value="Employed">Employed</option>
                  <option value="Unemployed">Unemployed</option>
                  <option value="Underemployed">Underemployed</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <select v-model="selectedLocation" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                  <option value="">All Locations</option>
                  <option v-for="loc in locations" :key="loc" :value="loc">{{ loc }}</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-500">Employed</p>
                <h3 class="text-3xl font-bold text-green-600 mt-1">{{ employed }}</h3>
              </div>
              <div class="bg-green-100 rounded-full p-3">
                <i class="fas fa-user-check text-green-600"></i>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-500">Unemployed</p>
                <h3 class="text-3xl font-bold text-red-600 mt-1">{{ unemployed }}</h3>
              </div>
              <div class="bg-red-100 rounded-full p-3">
                <i class="fas fa-user-times text-red-600"></i>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-500">Total Graduates</p>
                <h3 class="text-3xl font-bold text-blue-600 mt-1">{{ totalGraduates }}</h3>
              </div>
              <div class="bg-blue-100 rounded-full p-3">
                <i class="fas fa-user-graduate text-blue-600"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Employment Status Overview -->
        <div v-if="statusCounts && Object.keys(statusCounts).length" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md mb-6">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
              <i class="fas fa-chart-pie text-blue-500 mr-2"></i>
              <h3 class="text-lg font-medium text-gray-900">Employment Status Overview</h3>
            </div>
          </div>
          <div class="p-6">
            <div class="flex flex-col lg:flex-row gap-8 items-center justify-between">
              <div class="w-full lg:w-2/5 mb-6 lg:mb-0">
                <h4 class="text-base font-medium text-gray-700 mb-4">Detailed Status</h4>
                <ul class="space-y-3">
                  <li v-for="(count, status) in statusCounts" :key="status"
                    class="flex justify-between items-center px-4 py-2 rounded-md bg-gray-50 hover:bg-gray-100 transition-colors duration-200">
                    <span class="capitalize text-gray-700">{{ status }}</span>
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
        </div>

        <!-- Employment By Program -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md mb-6">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
              <i class="fas fa-graduation-cap text-green-500 mr-2"></i>
              <h3 class="text-lg font-medium text-gray-900">Employment By Program</h3>
            </div>
          </div>
          <div class="p-6">
            <VueECharts :option="barOption" style="height: 400px; width: 100%;" />
          </div>
        </div>

        <!-- Program Employment Rate Heatmap -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md mb-6">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
              <i class="fas fa-th text-indigo-500 mr-2"></i>
              <h3 class="text-lg font-medium text-gray-900">Program Employment Rate Heatmap</h3>
            </div>
          </div>
          <div class="p-6">
            <VueECharts :option="heatmapOption" style="height: 350px; width: 100%;" />
          </div>
        </div>

        <!-- Skills and Roles Analysis -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md mb-6">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
              <i class="fas fa-tools text-purple-500 mr-2"></i>
              <h3 class="text-lg font-medium text-gray-900">Skills and Roles Analysis</h3>
            </div>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div>
                <h4 class="text-base font-medium text-gray-700 mb-3">Top Job Roles (Employed Graduates)</h4>
                <ul class="flex flex-wrap gap-2">
                  <li v-for="[role, count] in topRoles" :key="role"
                    class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                    {{ role }} <span class="font-bold">({{ count }})</span>
                  </li>
                </ul>
                <h4 class="text-base font-medium text-gray-700 mt-6 mb-3">Top Skills (Employed Graduates)</h4>
                <ul class="flex flex-wrap gap-2">
                  <li v-for="[skill, count] in topSkills" :key="skill"
                    class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                    {{ skill }} <span class="font-bold">({{ count }})</span>
                  </li>
                </ul>
              </div>
              <div>
                <h4 class="text-base font-medium text-gray-700 mb-3">Top Skills: Demand vs. Supply</h4>
                <VueECharts :option="skillBarOption" style="height: 320px; width: 100%;" />
              </div>
            </div>
          </div>
        </div>

        <!-- Unemployment Rate Distribution -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md mb-6">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
              <i class="fas fa-chart-pie text-orange-500 mr-2"></i>
              <h3 class="text-lg font-medium text-gray-900">Unemployment Rate Distribution</h3>
            </div>
          </div>
          <div class="p-6 flex justify-center">
            <VueECharts :option="unemploymentPieOption" style="height: 350px; width: 100%; max-width: 420px;" />
          </div>
        </div>

        <!-- Unemployment Rate Over Time -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md mb-6">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
              <i class="fas fa-chart-area text-red-500 mr-2"></i>
              <h3 class="text-lg font-medium text-gray-900">Unemployment Rate Over Time</h3>
            </div>
          </div>
          <div class="p-6">
            <VueECharts :option="areaOption" style="height: 350px; width: 100%;" />
          </div>
        </div>

        <!-- Employment Trend Over Time -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md mb-6">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
              <i class="fas fa-chart-line text-blue-500 mr-2"></i>
              <h3 class="text-lg font-medium text-gray-900">Employment Trend Over Time</h3>
            </div>
          </div>
          <div class="p-6">
            <VueECharts :option="employmentTrendOption" style="height: 350px; width: 100%;" />
          </div>
        </div>

        <!-- Job Placement Rate Over Time -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md mb-6">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
              <i class="fas fa-chart-area text-green-500 mr-2"></i>
              <h3 class="text-lg font-medium text-gray-900">Job Placement Rate Over Time</h3>
            </div>
          </div>
          <div class="p-6">
            <VueECharts :option="jobPlacementOption" style="height: 350px; width: 100%;" />
          </div>
        </div>

        <!-- Job Openings vs. Job Seekers -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md mb-6">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
              <i class="fas fa-map-marked-alt text-indigo-500 mr-2"></i>
              <h3 class="text-lg font-medium text-gray-900">Job Openings vs. Job Seekers</h3>
            </div>
          </div>
          <div class="p-6">
            <VueECharts :option="bubbleMapOption" style="height: 400px; width: 100%;" />
          </div>
        </div>

        <!-- Referral Success Rate Heatmap -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md mb-6">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
              <i class="fas fa-fire text-orange-500 mr-2"></i>
              <h3 class="text-lg font-medium text-gray-900">Referral Success Rate Heatmap</h3>
            </div>
          </div>
          <div class="p-6">
            <VueECharts :option="referralHeatmapOption" style="height: 400px; width: 100%;" />
          </div>
        </div>

        <!-- Filtered Graduates Table -->
        <div v-if="filteredGraduates.length" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md mb-6">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
              <i class="fas fa-user-graduate text-blue-500 mr-2"></i>
              <h3 class="text-lg font-medium text-gray-900">Filtered Graduates</h3>
            </div>
          </div>
          <div class="p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="g in filteredGraduates" :key="g.id" class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ g.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ g.program?.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span :class="{
                      'px-2 py-1 rounded-full text-xs font-medium': true,
                      'bg-green-100 text-green-800': g.employment_status === 'Employed',
                      'bg-red-100 text-red-800': g.employment_status === 'Unemployed',
                      'bg-yellow-100 text-yellow-800': g.employment_status === 'Underemployed',
                      'bg-blue-100 text-blue-800': g.employment_status === 'Further Studies'
                    }">
                      {{ g.employment_status }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ g.schoolYear?.school_year_range }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ g.location }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>