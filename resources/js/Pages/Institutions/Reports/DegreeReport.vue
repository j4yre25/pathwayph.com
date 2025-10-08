<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import * as echarts from 'echarts';
import VChart from 'vue-echarts';
import AppLayout from '@/Layouts/AppLayout.vue';
import { BookMarked } from 'lucide-vue-next';

// Data refs
const degrees = ref([]);
const graduates = ref([]);
const schoolYears = ref([]);
const programs = ref([]);
const results = ref([]);
const availableYears = ref({});
const availableSectors = ref({});
const availablePrograms = ref({});
const loading = ref(true);

const filters = ref({
  schoolYear: '',
  degree: '',
  program: '',
  sector: '',
});

// Fetch degree data
function fetchDegreeData() {
  loading.value = true;
  axios.get(route('institutions.reports.degree.data'))
    .then(res => {
      degrees.value = res.data.degrees ?? [];
      graduates.value = res.data.graduates ?? [];
      schoolYears.value = res.data.schoolYears ?? [];
      programs.value = res.data.programs ?? [];
      results.value = res.data.results ?? [];
      availableYears.value = res.data.availableYears ?? {};
      availableSectors.value = res.data.availableSectors ?? {};
      availablePrograms.value = res.data.availablePrograms ?? {};

      // Only update filter fields if present
      if (res.data.filters) {
        filters.value.schoolYear = res.data.filters.schoolYear ?? filters.value.schoolYear;
        filters.value.degree = res.data.filters.degree ?? filters.value.degree;
        filters.value.program = res.data.filters.program ?? filters.value.program;
        filters.value.sector = res.data.filters.sector ?? filters.value.sector;
      }
    })
    .finally(() => {
      loading.value = false;
    });
}

onMounted(fetchDegreeData);

// Graduates table filters
const filteredGraduates = computed(() => {
  return graduates.value.filter(g => {
    if (filters.value.schoolYear && g.school_year_range !== filters.value.schoolYear) return false;
    if (filters.value.degree && g.degree_id != filters.value.degree) return false;
    if (filters.value.program && g.program_id != filters.value.program) return false;
    return true;
  });
});

// Graduates table pagination
const page = ref(1);
const perPage = 10;
const totalPages = computed(() => Math.ceil(filteredGraduates.value.length / perPage));
const paginatedGraduates = computed(() => {
  const start = (page.value - 1) * perPage;
  return filteredGraduates.value.slice(start, start + perPage);
});
watch(filteredGraduates, () => { page.value = 1; });

// Degree-to-Job Local Match Table filtering and pagination
const filteredResults = computed(() => {
  return results.value.filter(row => {
    if (filters.value.schoolYear && row.school_year_range && row.school_year_range !== filters.value.schoolYear) return false;
    if (filters.value.degree && row.degree_id && row.degree_id != filters.value.degree) return false;
    if (filters.value.program && row.program_id && row.program_id != filters.value.program) return false;
    if (filters.value.sector && row.sector_id && row.sector_id != filters.value.sector) return false;
    return true;
  });
});
const matchPage = ref(1);
const matchPerPage = 10;
const matchTotalPages = computed(() => Math.ceil(filteredResults.value.length / matchPerPage));
const paginatedResults = computed(() => {
  const start = (matchPage.value - 1) * matchPerPage;
  return filteredResults.value.slice(start, start + matchPerPage);
});
watch(filteredResults, () => { matchPage.value = 1; });

// Highest/Lowest match for summary
const highestMatch = computed(() => {
  if (!filteredResults.value.length) return { program: 'N/A', match_percentage: 0 };
  return filteredResults.value.reduce((max, curr) =>
    curr.match_percentage > max.match_percentage ? curr : max
  );
});
const lowestMatch = computed(() => {
  if (!filteredResults.value.length) return { program: 'N/A', match_percentage: 0 };
  return filteredResults.value.reduce((min, curr) =>
    curr.match_percentage < min.match_percentage ? curr : min
  );
});

// Match chart summary
const matchChartSummary = computed(() => {
  if (!filteredResults.value.length) {
    return 'No data available for the selected filters.';
  }

  // Find highest and lowest match
  const highest = highestMatch.value;
  const lowest = lowestMatch.value;

  // Calculate average match percentage
  const avg =
    filteredResults.value.reduce((sum, r) => sum + r.match_percentage, 0) /
    filteredResults.value.length;

  // Find programs above/below average
  const aboveAvg = filteredResults.value.filter(r => r.match_percentage > avg);
  const belowAvg = filteredResults.value.filter(r => r.match_percentage < avg);

  let summary = `The Degree-to-Job Local Match chart shows the alignment between graduates' current jobs and local job opportunities for each program. `;

  summary += `On average, ${avg.toFixed(1)}% of graduates are working in jobs that match their degree programs. `;
  if (aboveAvg.length) {
    summary += `Programs with above-average match include: ${aboveAvg.map(r => `"${r.program}"`).join(', ')}. `;
  }
  if (belowAvg.length) {
    summary += `Programs with below-average match include: ${belowAvg.map(r => `"${r.program}"`).join(', ')}. `;
  }

  if (avg >= 50) {
    summary += `Overall, the majority of graduates are well-matched to local job opportunities.`;
  } else {
    summary += `Overall, many graduates may be working in jobs not closely aligned with their degree programs.`;
  }

  return summary;
});

// Degree Graduate Trends Report (Line Chart)
const degreeTrendData = computed(() => {
  // Get all degrees present in filtered graduates
  const degreesArr = [...new Set(filteredGraduates.value.map(g => g.degree))].filter(Boolean);
  // Get all school years present in filtered graduates
  const years = [...new Set(filteredGraduates.value.map(g => g.school_year_range))].filter(Boolean).sort();

  // For each degree, count graduates per year
  const series = degreesArr.map(degree => ({
    name: degree,
    type: 'line',
    data: years.map(year =>
      filteredGraduates.value.filter(g => g.degree === degree && g.school_year_range === year).length
    ),
    smooth: true,
    symbol: 'circle'
  }));

  return { degrees: degreesArr, years, series };
});

const degreeTrendChartOptions = computed(() => ({
  title: { text: 'Degree Graduate Trends', left: 'center', top: 10, textStyle: { fontSize: 16 } },
  tooltip: { trigger: 'axis' },
  legend: { top: 40, data: degreeTrendData.value.degrees },
  grid: { left: 40, right: 20, bottom: 40, top: 80 },
  xAxis: { type: 'category', data: degreeTrendData.value.years, axisLabel: { rotate: 45 } },
  yAxis: { type: 'value', name: 'Number of Graduates', minInterval: 1 },
  series: degreeTrendData.value.series
}));

const degreeTrendSummary = computed(() => {
  if (!degreeTrendData.value.degrees.length || !degreeTrendData.value.years.length) {
    return 'No data available for the selected filters.';
  }
  // Find degree with highest growth
  let growths = degreeTrendData.value.series.map(s => ({
    degree: s.name,
    growth: s.data[s.data.length - 1] - s.data[0]
  }));
  let highestGrowth = growths.reduce((max, curr) => curr.growth > max.growth ? curr : max, growths[0]);
  let summary = `This chart shows the number of graduates per degree over time. `;
  summary += `The degree with the highest growth is "${highestGrowth.degree}" (${highestGrowth.growth >= 0 ? '+' : ''}${highestGrowth.growth} graduates from ${degreeTrendData.value.years[0]} to ${degreeTrendData.value.years[degreeTrendData.value.years.length - 1]}). `;
  summary += `Other degrees show ${growths.map(g => `"${g.degree}" (${g.growth >= 0 ? '+' : ''}${g.growth})`).join(', ')}.`;
  return summary;
});

// Employment by Degree Report (Stacked Bar Chart)
const employmentByDegreeData = computed(() => {
  const degreesArr = [...new Set(filteredGraduates.value.map(g => g.degree))].filter(Boolean);
  // For each degree, count employed, underemployed, unemployed
  const employed = degreesArr.map(degree =>
    filteredGraduates.value.filter(g => g.degree === degree && g.employment_status === 'Employed').length
  );
  const underemployed = degreesArr.map(degree =>
    filteredGraduates.value.filter(g => g.degree === degree && g.employment_status === 'Underemployed').length
  );
  const unemployed = degreesArr.map(degree =>
    filteredGraduates.value.filter(g => g.degree === degree && g.employment_status === 'Unemployed').length
  );
  return { degrees: degreesArr, employed, underemployed, unemployed };
});

const employmentByDegreeChartOptions = computed(() => ({
  title: { text: 'Employment by Degree', left: 'center', top: 10, textStyle: { fontSize: 16 } },
  tooltip: { trigger: 'axis' },
  legend: { top: 40, data: ['Employed', 'Underemployed', 'Unemployed'] },
  grid: { left: 40, right: 20, bottom: 40, top: 80 },
  xAxis: { type: 'category', data: employmentByDegreeData.value.degrees, axisLabel: { rotate: 45 } },
  yAxis: { type: 'value', name: 'Number of Graduates', minInterval: 1 },
  series: [
    {
      name: 'Employed',
      type: 'bar',
      stack: 'status',
      data: employmentByDegreeData.value.employed,
      itemStyle: { color: '#22c55e' }
    },
    {
      name: 'Underemployed',
      type: 'bar',
      stack: 'status',
      data: employmentByDegreeData.value.underemployed,
      itemStyle: { color: '#f59e42' }
    },
    {
      name: 'Unemployed',
      type: 'bar',
      stack: 'status',
      data: employmentByDegreeData.value.unemployed,
      itemStyle: { color: '#ef4444' }
    }
  ]
}));

const employmentByDegreeSummary = computed(() => {
  if (!employmentByDegreeData.value.degrees.length) {
    return 'No data available for the selected filters.';
  }
  // Find degree with highest employed
  const maxEmployed = Math.max(...employmentByDegreeData.value.employed);
  const maxDegree = employmentByDegreeData.value.degrees[employmentByDegreeData.value.employed.indexOf(maxEmployed)];
  // Find degree with highest unemployed
  const maxUnemployed = Math.max(...employmentByDegreeData.value.unemployed);
  const maxUnDegree = employmentByDegreeData.value.degrees[employmentByDegreeData.value.unemployed.indexOf(maxUnemployed)];
  let summary = `This chart compares employment status across degrees. `;
  summary += `"${maxDegree}" has the highest number of employed graduates (${maxEmployed}), while "${maxUnDegree}" has the highest number of unemployed graduates (${maxUnemployed}). `;
  summary += `Other degrees show varying distributions of employment status.`;
  return summary;
});

// ECharts logic for Degree-to-Job Local Match Chart only
const chartRef = ref(null);
let chartInstance = null;
const chartOptions = computed(() => ({
  tooltip: { trigger: 'axis' },
  xAxis: {
    type: 'category',
    data: filteredResults.value.map(r => r.program),
    axisLabel: { rotate: 30, interval: 0 }
  },
  yAxis: {
    type: 'value',
    min: 0,
    max: 100,
    axisLabel: { formatter: '{value}%' }
  },
  series: [
    {
      name: 'Match %',
      type: 'bar',
      data: filteredResults.value.map(r => r.match_percentage),
      itemStyle: {
        color: params => params.value >= 50 ? '#059669' : '#dc2626'
      },
      label: {
        show: true,
        position: 'top',
        formatter: '{c}%'
      }
    }
  ]
}));
const renderChart = () => {
  if (!chartRef.value) return;
  if (chartInstance) {
    chartInstance.dispose();
    chartInstance = null;
  }
  chartInstance = echarts.init(chartRef.value);
  chartInstance.setOption(chartOptions.value, true);
  chartInstance.resize();
};

// Only render chart after loading is false and DOM is updated
watch(loading, (val) => {
  if (!val) {
    nextTick(() => {
      renderChart();
    });
  }
});

// Remove previous onMounted and chartOptions watcher for chartRef
onBeforeUnmount(() => {
  if (chartInstance) {
    chartInstance.dispose();
    chartInstance = null;
  }
  window.removeEventListener('resize', renderChart);
});

onMounted(() => {
  window.addEventListener('resize', renderChart);
});
</script>

<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8 bg-gray-50 min-h-screen">
      <header class="mb-8">
        <h1 class="text-4xl font-extrabold text-gray-900 flex items-center gap-3">
          <BookMarked class="w-10 h-10 text-cyan-600" />
          <span>Degrees Report</span>
        </h1>
        <p class="mt-2 text-lg text-gray-600">Comprehensive overview of degree data, graduate trends, and job matching.</p>
      </header>

      <div v-if="loading" class="flex justify-center items-center py-32 bg-white rounded-xl shadow-lg">
        <svg class="animate-spin h-12 w-12 text-cyan-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
        <span class="ml-4 text-lg font-medium text-gray-700">Loading Report Data...</span>
      </div>

      <div v-else>
        <section class="mb-10">
          <h2 class="sr-only">Key Metrics</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            <div
              class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-start transition duration-300 hover:shadow-xl hover:scale-[1.02] border-l-4 border-cyan-500">
              <span class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Degree Entries</span>
              <span class="text-4xl font-extrabold text-gray-900 mt-2">{{ degrees.length }}</span>
            </div>

            <div
              class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-start transition duration-300 hover:shadow-xl hover:scale-[1.02] border-l-4 border-indigo-500">
              <span class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Total Graduates (Filtered)</span>
              <span class="text-4xl font-extrabold text-gray-900 mt-2">{{ filteredGraduates.length }}</span>
            </div>

            <div
              class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-start transition duration-300 hover:shadow-xl hover:scale-[1.02] border-l-4 border-green-500">
              <span class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Highest Job Match</span>
              <span class="text-xl font-bold text-green-700 mt-2">{{ highestMatch.match_percentage }}%</span>
              <span class="text-sm text-gray-700 truncate w-full">{{ highestMatch.program }}</span>
            </div>

            <div
              class="bg-white rounded-xl shadow-lg p-6 flex flex-col items-start transition duration-300 hover:shadow-xl hover:scale-[1.02] border-l-4 border-red-500">
              <span class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Lowest Job Match</span>
              <span class="text-xl font-bold text-red-700 mt-2">{{ lowestMatch.match_percentage }}%</span>
              <span class="text-sm text-gray-700 truncate w-full">{{ lowestMatch.program }}</span>
            </div>
          </div>
        </section>

        <section class="bg-white p-6 shadow-xl rounded-xl mb-10 border border-gray-100">
          <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Data Filters</h2>
          <div class="flex flex-wrap gap-6">
            <div class="min-w-[150px]">
              <label class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
              <select v-model="filters.schoolYear" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 text-sm">
                <option value="">All</option>
                <option v-for="(label, id) in availableYears" :key="id" :value="label">{{ label }}</option>
              </select>
            </div>
            <div class="min-w-[150px]">
              <label class="block text-sm font-medium text-gray-700 mb-1">Degree Type</label>
              <select v-model="filters.degree" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 text-sm">
                <option value="">All Degrees</option>
                <option v-for="deg in degrees" :key="deg.degree_id" :value="deg.degree_id">{{ deg.type }}</option>
              </select>
            </div>
            <div class="min-w-[150px]">
              <label class="block text-sm font-medium text-gray-700 mb-1">Program</label>
              <select v-model="filters.program" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 text-sm">
                <option value="">All Programs</option>
                <option v-for="prog in programs" :key="prog.id" :value="prog.id">{{ prog.name }}</option>
              </select>
            </div>
            <div class="min-w-[150px]">
              <label class="block text-sm font-medium text-gray-700 mb-1">Sector (for Match Data)</label>
              <select v-model="filters.sector" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 text-sm">
                <option value="">All Sectors</option>
                <option v-for="(name, id) in availableSectors" :key="id" :value="id">{{ name }}</option>
              </select>
            </div>
          </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
          
          <div class="lg:col-span-1 bg-white rounded-xl shadow-xl overflow-hidden ring-1 ring-gray-200">
            <h2 class="text-lg font-bold p-4 bg-gray-50 border-b text-gray-700">All Registered Degrees</h2>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Code</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                  <tr v-for="deg in degrees" :key="deg.id" class="hover:bg-gray-50 transition duration-150">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ deg.degree_code }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ deg.type }}</td>
                    <td class="px-4 py-3">
                      <span :class="{'bg-green-100 text-green-800': deg.status === 'Active', 'bg-red-100 text-red-800': deg.status === 'Inactive'}" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                        {{ deg.status }}
                      </span>
                    </td>
                  </tr>
                  <tr v-if="degrees.length === 0">
                    <td colspan="3" class="text-center text-gray-400 py-6">No degrees found.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="lg:col-span-2 bg-white rounded-xl shadow-xl overflow-hidden ring-1 ring-gray-200">
            <h2 class="text-lg font-bold p-4 bg-gray-50 border-b text-gray-700">Filtered Graduates ({{ filteredGraduates.length }} Total)</h2>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Degree</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Program</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Employment Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Current Job Title</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                  <tr v-for="g in paginatedGraduates" :key="g.id" class="hover:bg-gray-50 transition duration-150">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ g.first_name }} {{ g.middle_name }} {{ g.last_name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ g.degree || 'N/A' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ g.program || 'N/A' }}</td>
                    <td class="px-4 py-3">
                      <span :class="{'bg-green-100 text-green-800': g.employment_status === 'Employed', 'bg-yellow-100 text-yellow-800': g.employment_status === 'Underemployed', 'bg-red-100 text-red-800': g.employment_status === 'Unemployed'}" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                        {{ g.employment_status }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ g.current_job_title }}</td>
                  </tr>
                  <tr v-if="paginatedGraduates.length === 0">
                    <td colspan="7" class="text-center text-gray-400 py-6">No graduates found with current filters.</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="p-4 flex justify-end items-center bg-gray-50 border-t">
              <span class="text-sm text-gray-700 mr-4">Page {{ page }} of {{ totalPages }}</span>
              <div class="flex gap-2">
                <button class="px-4 py-2 rounded-lg bg-white border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition" :disabled="page === 1"
                  @click="page--">Previous</button>
                <button class="px-4 py-2 rounded-lg bg-white border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition" :disabled="page === totalPages"
                  @click="page++">Next</button>
              </div>
            </div>
          </div>
        </div>

        <section class="mb-12">
          <h2 class="text-2xl font-bold text-gray-800 mb-6">Degree-to-Job Local Match Index</h2>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1 p-6 bg-cyan-50 rounded-xl shadow-lg border border-cyan-200 h-fit">
              <h3 class="text-xl font-bold text-cyan-800 mb-3">Match Index Summary</h3>
              <div v-if="filteredResults.length">
                <p class="text-gray-700 text-sm leading-relaxed mb-4">
                  {{ matchChartSummary }}
                </p>
                <ul class="space-y-2 text-sm">
                  <li class="p-3 rounded-lg bg-green-100 border-l-4 border-green-500">
                    <span class="font-semibold text-green-800">Highest Match:</span>
                    <br class="sm:hidden">
                    <span class="text-green-700 font-bold">{{ highestMatch.program }} ({{ highestMatch.match_percentage }}%)</span>
                  </li>
                  <li class="p-3 rounded-lg bg-red-100 border-l-4 border-red-500">
                    <span class="font-semibold text-red-800">Lowest Match:</span>
                    <br class="sm:hidden">
                    <span class="text-red-700 font-bold">{{ lowestMatch.program }} ({{ lowestMatch.match_percentage }}%)</span>
                  </li>
                </ul>
              </div>
              <p v-else class="text-gray-500 italic">No data to summarize yet. Apply filters to see the analysis.</p>
            </div>

            <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-xl ring-1 ring-gray-200">
              <h3 class="text-xl font-bold text-gray-800 mb-4">Degree-to-Job Local Match % by Program</h3>
              <div class="h-[400px]" ref="chartRef"></div>
            </div>
          </div>
        </section>

        <section class="bg-white rounded-xl shadow-xl overflow-hidden ring-1 ring-gray-200 mb-12">
          <h2 class="text-lg font-bold p-4 bg-gray-50 border-b text-gray-700">Match Index Data Table</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-cyan-100">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-cyan-800 uppercase tracking-wider">Program</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-cyan-800 uppercase tracking-wider">Degree</th>
                  <th class="px-6 py-3 text-center text-xs font-semibold text-cyan-800 uppercase tracking-wider">Total Graduates</th>
                  <th class="px-6 py-3 text-center text-xs font-semibold text-cyan-800 uppercase tracking-wider">Matched Graduates</th>
                  <th class="px-6 py-3 text-center text-xs font-semibold text-cyan-800 uppercase tracking-wider">Match %</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 text-sm">
                <tr v-for="row in paginatedResults" :key="row.program" class="hover:bg-gray-50 transition duration-150">
                  <td class="px-6 py-4 font-medium text-gray-900">{{ row.program }}</td>
                  <td class="px-6 py-4 text-gray-600">{{ row.degree }}</td>
                  <td class="px-6 py-4 text-center text-gray-600">{{ row.total_graduates }}</td>
                  <td class="px-6 py-4 text-center text-gray-600">{{ row.matched_graduates }}</td>
                  <td class="px-6 py-4 text-center whitespace-nowrap">
                    <span :class="row.match_percentage >= 50 ? 'text-green-600 bg-green-50 px-2 py-1 rounded-full font-bold' : 'text-red-600 bg-red-50 px-2 py-1 rounded-full font-bold'">
                      {{ row.match_percentage }}%
                    </span>
                  </td>
                </tr>
                <tr v-if="!paginatedResults.length">
                  <td colspan="5" class="px-6 py-4 text-center text-gray-400">No Degree-to-Job Match data found with current filters.</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="p-4 flex justify-end items-center bg-gray-50 border-t">
            <span class="text-sm text-gray-700 mr-4">Page {{ matchPage }} of {{ matchTotalPages }}</span>
            <div class="flex gap-2">
              <button class="px-4 py-2 rounded-lg bg-white border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition" :disabled="matchPage === 1"
                @click="matchPage--">Previous</button>
              <button class="px-4 py-2 rounded-lg bg-white border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition" :disabled="matchPage === matchTotalPages"
                @click="matchPage++">Next</button>
            </div>
          </div>
        </section>

        <section class="mb-12">
          <div class="bg-white p-6 rounded-xl shadow-xl ring-1 ring-gray-200">
            <h3 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Employment by Degree</h3>
            <v-chart :option="employmentByDegreeChartOptions" autoresize style="height:350px;" class="mb-4" />
            <div class="mt-6 p-4 bg-green-50 rounded-lg text-green-900 text-sm font-medium border border-green-200">
              <span class="font-extrabold text-green-800">Summary Interpretation:</span>
              <p class="mt-1">{{ employmentByDegreeSummary }}</p>
            </div>
          </div>
        </section>

      </div>
    </div>
  </AppLayout>
</template>