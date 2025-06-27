<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch, onMounted, computed } from 'vue'
import VueECharts from 'vue-echarts';

import * as echarts from 'echarts/core'
import { BarChart, HeatmapChart, LineChart } from 'echarts/charts'
import { TitleComponent, TooltipComponent, GridComponent, VisualMapComponent, LegendComponent, GeoComponent } from 'echarts/components'
import { CanvasRenderer } from 'echarts/renderers'

const allTabs = [
  { key: 'employmentStatus', label: 'Employment Status Overview' },
  { key: 'employmentIndustry', label: 'Employment by Industry' },
  { key: 'employmentProgram', label: 'Employment by Program' },
  { key: 'geoDistribution', label: 'Geographic Distribution' },
  { key: 'employmentTrend', label: 'Employment Trend Over Time' },
  { key: 'skillsRoles', label: 'Skills and Roles Analysis' },
  { key: 'jobSearchDuration', label: 'Job Search Duration' },
  { key: 'graduateSatisfaction', label: 'Graduate Satisfaction' },
  { key: 'skillsGap', label: 'Skills Gap Analysis' },
  { key: 'unemploymentRate', label: 'Unemployment Rate' },
  { key: 'salaryInsights', label: 'Salary Insights' },
  { key: 'careerProgression', label: 'Career Progression' },
  { key: 'educationalImpact', label: 'Educational Impact' },
  { key: 'genderDiversity', label: 'Gender and Diversity Metrics' },
  { key: 'jobSeekerAlignment', label: 'Job Seeker and Role Alignment' },
  { key: 'matchingSuccess', label: 'Matching Success Rate' },
  { key: 'jobSeekerDemographics', label: 'Job Seeker Demographics' },
  { key: 'employerPreferences', label: 'Employer Preferences' },
  { key: 'referralSuccess', label: 'Referral Success Rate' },
  { key: 'sourceOfReferrals', label: 'Source of Referrals' },
  { key: 'referralTrends', label: 'Referral Trends Over Time' },
  { key: 'referralNetwork', label: 'Referral Network Analysis' },
  { key: 'referralPerformance', label: 'Referral Performance by Role' },
  { key: 'referralBonuses', label: 'Referral Bonuses and Outcomes' },
  { key: 'reasonReferralSuccess', label: 'Reason for Referral Success' },
  { key: 'economicImpact', label: 'Economic Impact' },
  { key: 'employerDiversity', label: 'Employer Diversity' },
  { key: 'industryPerformance', label: 'Industry Performance' },
  { key: 'jobSatisfaction', label: 'Job Satisfaction' },
  { key: 'futureJobTrends', label: 'Future Job Trends' },
]

const VISIBLE_TAB_COUNT = 4
const activeTab = ref(allTabs[0].key)
const showMore = ref(false)

const visibleTabs = computed(() => allTabs.slice(0, VISIBLE_TAB_COUNT))
const moreTabs = computed(() => allTabs.slice(VISIBLE_TAB_COUNT))

function selectMoreTab(key) {
  activeTab.value = key
  showMore.value = false
}

const currentTabLabel = computed(() => {
  const found = allTabs.find(tab => tab.key === activeTab.value)
  return found ? found.label : ''
})
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
  educationLevels: { type: Array, default: () => [] }, // e.g. ['High School', 'Bachelor', 'Master']
  employmentByEducation: { type: Array, default: () => [] }, // e.g. [60, 75, 85]
  radarPrograms: { type: Array, default: () => [] }, // e.g. ['IT', 'Business', 'Engineering']
  radarSkills: { type: Array, default: () => [] }, // e.g. ['Technical', 'Communication', 'Problem Solving', 'Teamwork']
  radarData: { type: Array, default: () => [] }, // e.g. [{ value: [80, 70, 60, 90], name: 'IT' }, ...]

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

const educationBarOption = computed(() => ({
  tooltip: { trigger: 'axis' },
  xAxis: {
    type: 'category',
    data: props.educationLevels,
    axisLabel: { rotate: 20 }
  },
  yAxis: { type: 'value', name: 'Employment Rate (%)', max: 100 },
  series: [
    {
      name: 'Employment Rate',
      type: 'bar',
      data: props.employmentByEducation,
      itemStyle: { color: '#3b82f6' }
    }
  ]
}))

const radarOption = computed(() => ({
  tooltip: {},
  legend: { data: props.radarPrograms },
  radar: {
    indicator: props.radarSkills.map(skill => ({ name: skill, max: 100 })),
    radius: 90
  },
  series: [
    {
      name: 'Skills vs Employability',
      type: 'radar',
      data: props.radarData
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
    <div class="max-w-5xl mx-auto py-8 px-4">
      <!-- Tabs Navigation -->
      <div class="flex items-center border-b border-gray-200 mb-6">
        <nav class="flex space-x-2 overflow-x-auto">
          <button
            v-for="tab in visibleTabs"
            :key="tab.key"
            @click="activeTab = tab.key"
            :class="[
              'px-4 py-2 rounded-t font-semibold',
              activeTab === tab.key
                ? 'bg-gray-200 text-gray-900'
                : 'text-gray-600 hover:bg-gray-100'
            ]"
          >
            {{ tab.label }}
          </button>
          <div v-if="moreTabs.length" class="relative">
            <button
              @click="showMore = !showMore"
              class="px-3 py-2 rounded-t text-gray-600 hover:bg-gray-100"
            >
              <span class="text-xl">⋯</span>
            </button>
            <div
              v-if="showMore"
              class="absolute z-10 bg-white border rounded shadow-lg mt-2 right-0 min-w-[200px]"
            >
              <button
                v-for="tab in moreTabs"
                :key="tab.key"
                @click="selectMoreTab(tab.key)"
                class="block w-full text-left px-4 py-2 hover:bg-gray-100"
              >
                {{ tab.label }}
              </button>
            </div>
          </div>
        </nav>
      </div>

      <!-- Render the active report section -->
      <div>
        <div v-if="activeTab === 'employmentStatus'">
          <!-- Employment Status Overview content here -->
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Employment Status Overview</h3>
          <!-- ... -->
        </div>
        <div v-else-if="activeTab === 'employmentIndustry'">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Employment by Industry</h3>
          <!-- ... -->
        </div>
        <div v-else-if="activeTab === 'employmentProgram'">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Employment by Program</h3>
          <!-- ... -->
        </div>
        <div v-else-if="activeTab === 'geoDistribution'">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Geographic Distribution</h3>
          <!-- ... -->
        </div>
        <!-- Add more v-else-if blocks for other tabs as needed -->
        <div v-else>
          <h3 class="text-lg font-semibold mb-6 text-gray-700">{{ currentTabLabel }}</h3>
          <!-- ... -->
        </div>
      </div>
    </div>
  </AppLayout>
</template>