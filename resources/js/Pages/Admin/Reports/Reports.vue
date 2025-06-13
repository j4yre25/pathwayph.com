<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch, onMounted, computed } from 'vue'
import VueECharts from 'vue-echarts';
import { usePage } from '@inertiajs/vue3'

import * as echarts from 'echarts/core'
import { BarChart, HeatmapChart, LineChart } from 'echarts/charts'
import { TitleComponent, TooltipComponent, GridComponent, VisualMapComponent, LegendComponent, GeoComponent } from 'echarts/components'

import { CanvasRenderer } from 'echarts/renderers'

const page = usePage()

const industryGraduateCounts = page.props.industryGraduateCounts ?? []
const industryNames = page.props.industryNames ?? []
const industryJobRoles = page.props.industryJobRoles ?? []
const industryApplicants = page.props.industryApplicants ?? []

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
  { key: 'jobOpeningsSeekers', label: 'Job Openings vs. Job Seekers' },
  { key: 'filteredGraduates', label: 'Filtered Graduates' },
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

// Bar/Clustered Column Chart Option
const industryBarOption = computed(() => ({
  tooltip: { trigger: 'axis' },
  xAxis: {
    type: 'category',
    data: industryGraduateCounts.map(i => i.name),
    axisLabel: { rotate: 30 }
  },
  yAxis: { type: 'value', name: 'Graduates' },
  series: [
    {
      name: 'Graduates',
      type: 'bar',
      data: industryGraduateCounts.map(i => i.value),
      itemStyle: { color: '#3b82f6' }
    }
  ]
}))

// Treemap Option
const industryTreemapOption = computed(() => ({
  tooltip: { trigger: 'item', formatter: '{b}: {c}' },
  series: [
    {
      type: 'treemap',
      data: industryGraduateCounts,
      label: { show: true, formatter: '{b}' }
    }
  ]
}))

// Stacked Column Chart Option
const industryStackedOption = computed(() => ({
  tooltip: { trigger: 'axis' },
  legend: { data: ['Job Roles', 'Applicants'] },
  xAxis: {
    type: 'category',
    data: industryNames,
    axisLabel: { rotate: 30 }
  },
  yAxis: { type: 'value' },
  series: [
    {
      name: 'Job Roles',
      type: 'bar',
      stack: 'total',
      data: industryJobRoles,
      itemStyle: { color: '#10b981' }
    },
    {
      name: 'Applicants',
      type: 'bar',
      stack: 'total',
      data: industryApplicants,
      itemStyle: { color: '#f59e42' }
    }
  ]
}))

// Employment Rate by Location (Bar Chart)

const locationStats = page.props.locationStats ?? []

const employmentRateBarOption = computed(() => ({
  tooltip: { trigger: 'axis' },
  xAxis: {
    type: 'category',
    data: locationStats.map(i => i.name),
    axisLabel: { rotate: 30 }
  },
  yAxis: { type: 'value', name: 'Employment Rate (%)', max: 100 },
  series: [
    {
      name: 'Employment Rate',
      type: 'bar',
      data: locationStats.map(i => i.employment_rate ?? 0),
      itemStyle: { color: '#3b82f6' }
    }
  ]
}))

// For Heatmap: Skill demand across industries (simulate with available data)
const industries = industryNames // from backend, e.g. ['IT', 'Healthcare', ...]
const skills = Object.keys(props.topSkillDemand) // top skills

// Simulate demand matrix: random or zero if you don't have per-industry data
const skillIndustryMatrix = computed(() =>
  skills.map(skill =>
    industries.map(() => Math.floor(Math.random() * 20)) // Replace with real data if available
  )
)

// Heatmap data format for ECharts
const skillIndustryHeatmapData = computed(() => {
  const data = []
  skills.forEach((skill, i) => {
    industries.forEach((industry, j) => {
      data.push([j, i, skillIndustryMatrix.value[i][j]])
    })
  })
  return data
})

// Heatmap option
const skillGapHeatmapOption = computed(() => ({
  tooltip: {
    position: 'top',
    formatter: params =>
      `Industry: ${industries[params.data[0]]}<br>Skill: ${skills[params.data[1]]}<br>Demand: ${params.data[2]}`
  },
  grid: { height: '60%', top: '10%' },
  xAxis: {
    type: 'category',
    data: industries,
    splitArea: { show: true },
    axisLabel: { rotate: 30 }
  },
  yAxis: {
    type: 'category',
    data: skills,
    splitArea: { show: true }
  },
  visualMap: {
    min: 0,
    max: 20,
    calculable: true,
    orient: 'horizontal',
    left: 'center',
    bottom: '5%',
    inRange: { color: ['#fca5a5', '#fbbf24', '#22c55e'] }
  },
  series: [
    {
      name: 'Skill Demand',
      type: 'heatmap',
      data: skillIndustryHeatmapData.value,
      label: { show: false }
    }
  ]
}))

// Bubble chart: Skill demand vs supply
const skillBubbleData = computed(() =>
  Object.keys(props.topSkillDemand).map(skill => ({
    name: skill,
    value: [
      props.topSkillDemand[skill] ?? 0, // x: demand
      props.topSkillSupply[skill] ?? 0, // y: supply
      props.topSkillDemand[skill] ?? 0  // bubble size: demand
    ]
  }))
)

const skillGapBubbleOption = computed(() => ({
  tooltip: {
    trigger: 'item',
    formatter: params =>
      `Skill: ${params.data.name}<br>Demand: ${params.data.value[0]}<br>Supply: ${params.data.value[1]}`
  },
  xAxis: {
    name: 'Demand (Jobs)',
    type: 'value'
  },
  yAxis: {
    name: 'Supply (Graduates)',
    type: 'value'
  },
  series: [
    {
      name: 'Skills',
      type: 'scatter',
      symbolSize: val => Math.max(20, val[2] * 2),
      data: skillBubbleData.value,
      itemStyle: { color: '#3b82f6', opacity: 0.7 }
    }
  ]
}))

// Salary Insights
const industrySalaryBoxData = page.props.industrySalaryBoxData ?? []
const industryLevelSalaries = page.props.industryLevelSalaries ?? []
const salaryExpectations = page.props.salaryExpectations ?? { graduateMin: [], graduateMax: [], jobMin: [], jobMax: [] }

// Box Plot Option
const boxPlotIndustries = industrySalaryBoxData.map(i => i.industry)
const boxPlotData = industrySalaryBoxData.map(i => i.salaries)
const salaryBoxPlotOption = computed(() => ({
  tooltip: { trigger: 'item' },
  xAxis: { type: 'category', data: boxPlotIndustries, axisLabel: { rotate: 30 } },
  yAxis: { type: 'value', name: 'Salary (₱)' },
  series: [
    {
      name: 'Salary Range',
      type: 'boxplot',
      data: boxPlotData,
      itemStyle: { color: '#3b82f6' }
    }
  ]
}))

// Stacked Bar Chart Option
const stackedIndustries = industryLevelSalaries.map(i => i.industry)
const entryData = industryLevelSalaries.map(i => i.Entry)
const midData = industryLevelSalaries.map(i => i.Mid)
const seniorData = industryLevelSalaries.map(i => i.Senior)
const salaryStackedBarOption = computed(() => ({
  tooltip: { trigger: 'axis' },
  legend: { data: ['Entry', 'Mid', 'Senior'] },
  xAxis: { type: 'category', data: stackedIndustries, axisLabel: { rotate: 30 } },
  yAxis: { type: 'value', name: 'Average Salary (₱)' },
  series: [
    { name: 'Entry', type: 'bar', stack: 'level', data: entryData, itemStyle: { color: '#60a5fa' } },
    { name: 'Mid', type: 'bar', stack: 'level', data: midData, itemStyle: { color: '#fbbf24' } },
    { name: 'Senior', type: 'bar', stack: 'level', data: seniorData, itemStyle: { color: '#10b981' } },
  ]
}))

// Histogram Option
const salaryHistogramOption = computed(() => ({
  tooltip: { trigger: 'axis' },
  legend: { data: ['Job Seekers (Min)', 'Job Seekers (Max)', 'Jobs (Min)', 'Jobs (Max)'] },
  xAxis: { type: 'category', data: xLabels, name: 'Salary Range (₱)' },
  yAxis: { type: 'value', name: 'Count' },
  series: [
    {
      name: 'Job Seekers (Min)',
      type: 'bar',
      data: getCounts(gradMinBins, xLabels),
      itemStyle: { color: '#3b82f6' }
    },
    {
      name: 'Job Seekers (Max)',
      type: 'bar',
      data: getCounts(gradMaxBins, xLabels),
      itemStyle: { color: '#60a5fa' }
    },
    {
      name: 'Jobs (Min)',
      type: 'bar',
      data: getCounts(jobMinBins, xLabels),
      itemStyle: { color: '#fbbf24' }
    },
    {
      name: 'Jobs (Max)',
      type: 'bar',
      data: getCounts(jobMaxBins, xLabels),
      itemStyle: { color: '#f59e42' }
    }
  ]
}));

const binSize = 5000;
const gradMinBins = binData(salaryExpectations.graduateMin, binSize);
const gradMaxBins = binData(salaryExpectations.graduateMax, binSize);
const jobMinBins = binData(salaryExpectations.jobMin, binSize);
const jobMaxBins = binData(salaryExpectations.jobMax, binSize);

const allBins = [gradMinBins, gradMaxBins, jobMinBins, jobMaxBins].flat();
const xLabels = [...new Set(allBins.map(b => `${b.min}-${b.max}`))];

function getCounts(bins, xLabels) {
  const map = Object.fromEntries(bins.map(b => [`${b.min}-${b.max}`, b.count]));
  return xLabels.map(label => map[label] || 0);
}

function binData(data, binSize = 5000) {
  if (!Array.isArray(data) || data.length === 0) return [];
  const min = Math.min(...data);
  const max = Math.max(...data);
  const bins = [];
  for (let i = min; i <= max; i += binSize) {
    bins.push({ min: i, max: i + binSize, count: 0 });
  }
  data.forEach(val => {
    const idx = Math.floor((val - min) / binSize);
    if (bins[idx]) bins[idx].count++;
  });
  return bins;
}
</script>

<template>
  <AppLayout title="PESO Reports">
    <div class="max-w-5xl mx-auto py-8 px-4">
      <!-- Horizontal Scrollable Tabs -->
      <div class="border-b border-gray-200 mb-6 relative">
        <nav
          class="flex space-x-2 overflow-x-auto whitespace-nowrap scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-transparent"
          style="scrollbar-width: thin;">
          <button v-for="tab in allTabs" :key="tab.key" @click="activeTab = tab.key" :class="[
            'px-4 py-2 rounded-t font-semibold transition',
            activeTab === tab.key
              ? 'bg-gray-200 text-gray-900'
              : 'text-gray-600 hover:bg-gray-100'
          ]">
            {{ tab.label }}
          </button>
        </nav>
        <!-- Fade effect at ends -->
        <div class="pointer-events-none absolute top-0 left-0 h-full w-8 bg-gradient-to-r from-white/90 to-transparent">
        </div>
        <div
          class="pointer-events-none absolute top-0 right-0 h-full w-8 bg-gradient-to-l from-white/90 to-transparent">
        </div>
      </div>

      <!-- Employment Status Overview -->
      <div v-if="activeTab === 'employmentStatus'">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Employment Status Overview</h2>
        <!-- FILTER CONTROLS -->
        <div class="flex flex-wrap gap-4 mb-8">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
            <select v-model="selectedYear" class="border rounded px-2 py-1">
              <option value="">All</option>
              <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Program</label>
            <select v-model="selectedProgram" class="border rounded px-2 py-1">
              <option value="">All</option>
              <option v-for="program in programs" :key="program.id" :value="program.id">{{ program.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select v-model="selectedStatus" class="border rounded px-2 py-1">
              <option value="">All</option>
              <option value="Employed">Employed</option>
              <option value="Unemployed">Unemployed</option>
              <option value="Underemployed">Underemployed</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
            <select v-model="selectedLocation" class="border rounded px-2 py-1">
              <option value="">All</option>
              <option v-for="loc in locations" :key="loc" :value="loc">{{ loc }}</option>
            </select>
          </div>
        </div>
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
      </div>

      <!-- Employment by Industry -->
      <div v-else-if="activeTab === 'employmentIndustry'">
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
      </div>

      <!-- Employment by Program -->
      <div v-else-if="activeTab === 'employmentProgram'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Employment By Program</h2>
        <div class="bg-white rounded-xl shadow p-8 mt-12">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Employment by Program (Stacked Bar)</h3>
          <VueECharts :option="barOption" style="height: 400px; width: 100%;" />
        </div>
        <div class="bg-white rounded-xl shadow p-8 mt-12">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Program Employment Rate Heatmap</h3>
          <VueECharts :option="heatmapOption" style="height: 350px; width: 100%;" />
        </div>
      </div>

      <!-- Geographic Distribution -->
      <div v-else-if="activeTab === 'geoDistribution'">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Geographic Distribution</h2>

        <!-- Bubble Map: Job Openings vs. Job Seekers -->
        <div class="bg-white rounded-xl shadow p-8 mb-12">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Job Openings vs. Job Seekers by Location</h3>
          <VueECharts :option="bubbleMapOption" style="height: 500px; width: 100%;" />
        </div>

        <!-- Heatmap: Referral Success Rate -->
        <div class="bg-white rounded-xl shadow p-8 mb-12">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Referral Success Rate by Location (Heatmap)</h3>
          <VueECharts :option="referralHeatmapOption" style="height: 500px; width: 100%;" />
        </div>

        <!-- Bar Chart: Employment Rate by Area -->
        <div class="bg-white rounded-xl shadow p-8">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Employment Rate by Area</h3>
          <VueECharts :option="employmentRateBarOption" style="height: 400px; width: 100%;" />
        </div>
      </div>

      <!-- Employment Trend Over Time -->
      <div v-else-if="activeTab === 'employmentTrend'">
        <div class="bg-white rounded-xl shadow p-8 mt-12">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Employment Trend Over Time</h3>
          <VueECharts :option="employmentTrendOption" style="height: 350px; width: 100%;" />
        </div>
        <div class="bg-white rounded-xl shadow p-8 mt-12">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Job Placement Rate Over Time</h3>
          <VueECharts :option="jobPlacementOption" style="height: 350px; width: 100%;" />
        </div>
      </div>

      <!-- Skills and Roles Analysis -->
      <div v-else-if="activeTab === 'skillsRoles'">
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
      </div>

      <!-- Job Search Duration -->
      <div v-else-if="activeTab === 'jobSearchDuration'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Job Search Duration</h2>
        <!-- Add your Job Search Duration content here -->
      </div>

      <!-- Graduate Satisfaction -->
      <div v-else-if="activeTab === 'graduateSatisfaction'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Graduate Satisfaction</h2>
        <!-- Add your Graduate Satisfaction content here -->
      </div>

      <!-- Skills Gap Analysis -->
      <div v-else-if="activeTab === 'skillsGap'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Skills Gap Analysis</h2>
        <!-- Heatmap: Skill Demand Across Industries -->
        <div class="bg-white rounded-xl shadow p-8 mb-12">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Skill Demand Across Industries (Heatmap)</h3>
          <VueECharts :option="skillGapHeatmapOption" style="height: 500px; width: 100%;" />
        </div>

        <!-- Bubble Chart: Demand vs. Supply -->
        <div class="bg-white rounded-xl shadow p-8">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Skill Demand vs. Supply (Bubble Chart)</h3>
          <VueECharts :option="skillGapBubbleOption" style="height: 500px; width: 100%;" />
        </div>
      </div>

      <!-- Unemployment Rate -->
      <div v-else-if="activeTab === 'unemploymentRate'">
        <div class="bg-white rounded-xl shadow p-8 mt-12">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Unemployment Rate Distribution</h3>
          <VueECharts :option="unemploymentPieOption" style="height: 350px; width: 100%; max-width: 420px;" />
        </div>
        <div class="bg-white rounded-xl shadow p-8 mt-12">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Unemployment Rate Over Time</h3>
          <VueECharts :option="areaOption" style="height: 350px; width: 100%;" />
        </div>
      </div>

      <!-- Salary Insights -->
      <div v-else-if="activeTab === 'salaryInsights'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Salary Insights</h2>
        <!-- Box Plot: Salary Ranges Across Industries -->
        <div class="bg-white rounded-xl shadow p-8 mb-12">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Salary Ranges Across Industries (Box Plot)</h3>
          <VueECharts :option="salaryBoxPlotOption" style="height: 500px; width: 100%;" />
        </div>

        <!-- Stacked Bar Chart: Entry, Mid, Senior -->
        <div class="bg-white rounded-xl shadow p-8 mb-12">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Average Salary by Level (Stacked Bar)</h3>
          <VueECharts :option="salaryStackedBarOption" style="height: 500px; width: 100%;" />
        </div>

        <!-- Histogram: Salary Expectations vs. Offered -->
        <div class="bg-white rounded-xl shadow p-8">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Salary Expectations vs. Offered (Histogram)</h3>
          <VueECharts :option="salaryHistogramOption" style="height: 500px; width: 100%;" />
        </div>
      </div>

      <!-- Career Progression -->
      <div v-else-if="activeTab === 'careerProgression'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Career Progression</h2>
        <!-- Add your Career Progression content here -->
      </div>

      <!-- Educational Impact -->
      <div v-else-if="activeTab === 'educationalImpact'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Educational Impact</h2>
        <!-- Add your Educational Impact content here -->
      </div>

      <!-- Gender and Diversity Metrics -->
      <div v-else-if="activeTab === 'genderDiversity'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Gender and Diversity Metrics</h2>
        <!-- Add your Gender and Diversity Metrics content here -->
      </div>

      <!-- Job Seeker and Role Alignment -->
      <div v-else-if="activeTab === 'jobSeekerAlignment'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Job Seeker and Role Alignment</h2>
        <!-- Add your Job Seeker and Role Alignment content here -->
      </div>

      <!-- Matching Success Rate -->
      <div v-else-if="activeTab === 'matchingSuccess'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Matching Success Rate</h2>
        <!-- Add your Matching Success Rate content here -->
      </div>

      <!-- Job Seeker Demographics -->
      <div v-else-if="activeTab === 'jobSeekerDemographics'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Job Seeker Demographics</h2>
        <!-- Add your Job Seeker Demographics content here -->
      </div>

      <!-- Employer Preferences -->
      <div v-else-if="activeTab === 'employerPreferences'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Employer Preferences</h2>
        <!-- Add your Employer Preferences content here -->
      </div>

      <!-- Referral Success Rate -->
      <div v-else-if="activeTab === 'referralSuccess'">
        <div class="bg-white rounded-xl shadow p-8 mt-12">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Referral Success Rate Heatmap</h3>
          <VueECharts :option="referralHeatmapOption" style="height: 400px; width: 100%;" />
        </div>
      </div>

      <!-- Source of Referrals -->
      <div v-else-if="activeTab === 'sourceOfReferrals'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Source of Referrals</h2>
        <!-- Add your Source of Referrals content here -->
      </div>

      <!-- Referral Trends Over Time -->
      <div v-else-if="activeTab === 'referralTrends'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Referral Trends Over Time</h2>
        <!-- Add your Referral Trends Over Time content here -->
      </div>

      <!-- Referral Network Analysis -->
      <div v-else-if="activeTab === 'referralNetwork'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Referral Network Analysis</h2>
        <!-- Add your Referral Network Analysis content here -->
      </div>

      <!-- Referral Performance by Role -->
      <div v-else-if="activeTab === 'referralPerformance'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Referral Performance by Role</h2>
        <!-- Add your Referral Performance by Role content here -->
      </div>

      <!-- Referral Bonuses and Outcomes -->
      <div v-else-if="activeTab === 'referralBonuses'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Referral Bonuses and Outcomes</h2>
        <!-- Add your Referral Bonuses and Outcomes content here -->
      </div>

      <!-- Reason for Referral Success -->
      <div v-else-if="activeTab === 'reasonReferralSuccess'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Reason for Referral Success</h2>
        <!-- Add your Reason for Referral Success content here -->
      </div>

      <!-- Economic Impact -->
      <div v-else-if="activeTab === 'economicImpact'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Economic Impact</h2>
        <!-- Add your Economic Impact content here -->
      </div>

      <!-- Employer Diversity -->
      <div v-else-if="activeTab === 'employerDiversity'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Employer Diversity</h2>
        <!-- Add your Employer Diversity content here -->
      </div>

      <!-- Industry Performance -->
      <div v-else-if="activeTab === 'industryPerformance'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Industry Performance</h2>
        <!-- Add your Industry Performance content here -->
      </div>

      <!-- Job Satisfaction -->
      <div v-else-if="activeTab === 'jobSatisfaction'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Job Satisfaction</h2>
        <!-- Add your Job Satisfaction content here -->
      </div>

      <!-- Future Job Trends -->
      <div v-else-if="activeTab === 'futureJobTrends'">
        <h2 class="text-2xl font-bold mb-3 mt-6 text-gray-800">Future Job Trends</h2>
        <!-- Add your Future Job Trends content here -->
      </div>

      <!-- Job Openings vs. Seekers -->
      <div v-else-if="activeTab === 'jobOpeningsSeekers'">
        <div class="bg-white rounded-xl shadow p-8 mt-12">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Job Openings vs. Job Seekers</h3>
          <VueECharts :option="bubbleMapOption" style="height: 400px; width: 100%;" />
        </div>
      </div>

      <!-- Filtered Graduates Table -->
      <div v-else-if="activeTab === 'filteredGraduates'">
        <div class="bg-white rounded-xl shadow p-8 mt-12" v-if="filteredGraduates.length">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Filtered Graduates</h3>
          <table class="min-w-full text-sm">
            <thead>
              <tr>
                <th class="px-2 py-1 text-left">Name</th>
                <th class="px-2 py-1 text-left">Program</th>
                <th class="px-2 py-1 text-left">Status</th>
                <th class="px-2 py-1 text-left">Year</th>
                <th class="px-2 py-1 text-left">Location</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="g in filteredGraduates" :key="g.id">
                <td class="px-2 py-1">{{ g.name }}</td>
                <td class="px-2 py-1">{{ g.program?.name }}</td>
                <td class="px-2 py-1">{{ g.employment_status }}</td>
                <td class="px-2 py-1">{{ g.schoolYear?.school_year_range }}</td>
                <td class="px-2 py-1">{{ g.location }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
nav::-webkit-scrollbar {
  height: 6px;
}

nav::-webkit-scrollbar-thumb {
  background: #e5e7eb;
  border-radius: 3px;
}

nav::-webkit-scrollbar-track {
  background: transparent;
}
</style>