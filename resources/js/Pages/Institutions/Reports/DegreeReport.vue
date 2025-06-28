<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto py-10 px-4">
      <h1 class="text-3xl font-bold mb-6 text-gray-800 flex items-center gap-2">
        <BookMarked class="w-8 h-8 text-cyan-500" />
        Degrees Report
      </h1>

      <!-- Card for total degree entries -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <span class="text-2xl font-bold text-gray-800">{{ degrees.length }}</span>
          <span class="text-gray-500">Total Degree Entries</span>
        </div>
      </div>

      <!-- Table 1: List of Degrees -->
      <div class="mb-10">
        <h2 class="text-xl font-semibold mb-4">All Degrees</h2>
        <div class="bg-white rounded-lg shadow overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Degree Code</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="deg in degrees" :key="deg.id">
                <td class="px-4 py-2">{{ deg.degree_code }}</td>
                <td class="px-4 py-2">{{ deg.type }}</td>
                <td class="px-4 py-2">{{ deg.status }}</td>
              </tr>
              <tr v-if="degrees.length === 0">
                <td colspan="3" class="text-center text-gray-400 py-6">No degrees found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Filters for graduates table -->
      <div class="bg-white rounded-xl shadow p-6 mb-8 grid grid-cols-1 md:grid-cols-3 gap-4">
        <input
          v-model="filters.name"
          type="text"
          placeholder="Search by name"
          class="rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500"
        />
        <select v-model="filters.schoolYear" class="rounded-lg border-gray-300">
          <option value="">All School Years</option>
          <option v-for="sy in schoolYears" :key="sy.id" :value="sy.id">{{ sy.school_year_range }}</option>
        </select>
        <select v-model="filters.degree" class="rounded-lg border-gray-300">
          <option value="">All Degrees</option>
          <option v-for="deg in degrees" :key="deg.degree_id" :value="deg.type">{{ deg.type }}</option>
        </select>
        <select v-model="filters.program" class="rounded-lg border-gray-300">
          <option value="">All Programs</option>
          <option v-for="prog in programs" :key="prog.id" :value="prog.id">{{ prog.name }}</option>
        </select>
        <select v-model="filters.employmentStatus" class="rounded-lg border-gray-300">
          <option value="">All Employment Status</option>
          <option value="Employed">Employed</option>
          <option value="Underemployed">Underemployed</option>
          <option value="Unemployed">Unemployed</option>
        </select>
      </div>

      <!-- Table 2: List of Graduates -->
      <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year Graduated</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Degree</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Program</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employment Status</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Job Title</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="g in paginatedGraduates" :key="g.id">
              <td class="px-4 py-2">{{ g.first_name }} {{ g.middle_name }} {{ g.last_name }}</td>
              <td class="px-4 py-2">{{ g.school_year_range || 'N/A' }}</td>
              <td class="px-4 py-2">{{ g.term || 'N/A' }}</td>
              <td class="px-4 py-2">{{ g.degree || 'N/A' }}</td>
              <td class="px-4 py-2">{{ g.program || 'N/A' }}</td>
              <td class="px-4 py-2">{{ g.employment_status }}</td>
              <td class="px-4 py-2">{{ g.current_job_title }}</td>
            </tr>
            <tr v-if="paginatedGraduates.length === 0">
              <td colspan="7" class="text-center text-gray-400 py-6">No graduates found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="mt-6 flex justify-center gap-2">
        <button
          class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300"
          :disabled="page === 1"
          @click="page--"
        >Prev</button>
        <span class="px-3 py-1">{{ page }} / {{ totalPages }}</span>
        <button
          class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300"
          :disabled="page === totalPages"
          @click="page++"
        >Next</button>
      </div>

      <!-- FILTERS FOR DEGREE-TO-JOB LOCAL MATCH -->
      <form class="flex flex-wrap gap-4 mb-6 mt-12" @submit.prevent="applyMatchFilters">
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Year</label>
          <select v-model="matchFilters.graduation_year" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="(label, id) in availableYears" :key="id" :value="label">{{ label }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Sector</label>
          <select v-model="matchFilters.sector_id" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="(name, id) in availableSectors" :key="id" :value="id">{{ name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Program</label>
          <select v-model="matchFilters.program_id" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="(name, id) in availablePrograms" :key="id" :value="id">{{ name }}</option>
          </select>
        </div>
        <div class="flex items-end">
          <button type="submit" class="ml-2 px-4 py-2 bg-cyan-600 text-white rounded hover:bg-cyan-700">
            Apply
          </button>
        </div>
      </form>

      <!-- Degree-to-Job Local Match Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 shadow rounded-lg">
          <thead class="bg-cyan-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-cyan-700 uppercase tracking-wider">Program</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-cyan-700 uppercase tracking-wider">Degree</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-cyan-700 uppercase tracking-wider">Total Graduates</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-cyan-700 uppercase tracking-wider">Matched Graduates</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-cyan-700 uppercase tracking-wider">Match %</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <tr v-for="row in results" :key="row.program">
              <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ row.program }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ row.degree }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ row.total_graduates }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ row.matched_graduates }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="row.match_percentage >= 50 ? 'text-green-600 font-bold' : 'text-red-600 font-bold'"
                >
                  {{ row.match_percentage }}%
                </span>
              </td>
            </tr>
            <tr v-if="!results || results.length === 0">
              <td colspan="5" class="px-6 py-4 text-center text-gray-400">No data found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Degree-to-Job Local Match Chart -->
      <div class="my-10">
        <h3 class="text-lg font-semibold mb-4 text-cyan-700">Degree-to-Job Local Match Chart</h3>
        <div ref="chartRef" style="width: 100%; height: 400px;"></div>
      </div>

      <!-- About the Degree-to-Job Local Match Index -->
      <div class="mb-6 p-4 bg-cyan-50 rounded-lg border border-cyan-100">
        <h2 class="text-lg font-semibold text-cyan-700 mb-2">About the Degree-to-Job Local Match Index</h2>
        <p class="text-gray-700 mb-2">
          The <span class="font-semibold text-cyan-700">Degree-to-Job Local Match Index</span> shows how closely your graduates’ current jobs align with the locally available jobs for each degree program. 
          <br>
          For every program, we compare the <span class="font-semibold">current job titles of graduates</span> to the <span class="font-semibold">local jobs mapped to that program</span>. The <span class="font-semibold">Match %</span> is the percentage of graduates whose current job matches a local job title for their degree.
        </p>
        <ul class="text-gray-700 mb-2 list-disc pl-6">
          <li>
            <span v-if="results.length">
              <span class="font-semibold">Highest Match:</span>
              <span class="text-green-700">
                {{ highestMatch.program }} ({{ highestMatch.match_percentage }}%)
              </span>
              &nbsp;|&nbsp;
              <span class="font-semibold">Lowest Match:</span>
              <span class="text-red-700">
                {{ lowestMatch.program }} ({{ lowestMatch.match_percentage }}%)
              </span>
            </span>
            <span v-else>
              No data to summarize yet.
            </span>
          </li>
        </ul>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import * as echarts from 'echarts';
import AppLayout from '@/Layouts/AppLayout.vue';



const props = defineProps({
  degrees: { type: Array, default: () => [] },
  graduates: { type: Array, default: () => [] },
  schoolYears: { type: Array, default: () => [] },
  programs: { type: Array, default: () => [] },
  results: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
  availableYears: { type: Object, default: () => ({}) },
  availableSectors: { type: Object, default: () => ({}) },
  availablePrograms: { type: Object, default: () => ({}) },
});

// Filters for graduates table
const filters = ref({
  name: '',
  schoolYear: '',
  degree: '',
  program: '',
  employmentStatus: '',
});

// Real-time filtered graduates
const filteredGraduates = computed(() => {
  return props.graduates.filter(g => {
    const name = `${g.first_name} ${g.middle_name ?? ''} ${g.last_name}`.toLowerCase();
    if (filters.value.name && !name.includes(filters.value.name.toLowerCase())) return false;
    if (filters.value.schoolYear && g.school_year_id != filters.value.schoolYear) return false;
    if (filters.value.degree && g.degree != filters.value.degree) return false;
    if (filters.value.program && g.program_id != filters.value.program) return false;
    if (filters.value.employmentStatus && g.employment_status !== filters.value.employmentStatus) return false;
    return true;
  });
});

const highestMatch = computed(() => {
  if (!props.results.length) return { program: 'N/A', match_percentage: 0 };
  return props.results.reduce((max, curr) =>
    curr.match_percentage > max.match_percentage ? curr : max
  );
});
const lowestMatch = computed(() => {
  if (!props.results.length) return { program: 'N/A', match_percentage: 0 };
  return props.results.reduce((min, curr) =>
    curr.match_percentage < min.match_percentage ? curr : min
  );
});

// Pagination
const page = ref(1);
const perPage = 20;
const totalPages = computed(() => Math.ceil(filteredGraduates.value.length / perPage));
const paginatedGraduates = computed(() => {
  const start = (page.value - 1) * perPage;
  return filteredGraduates.value.slice(start, start + perPage);
});
watch(filteredGraduates, () => { page.value = 1; });

// Filters for Degree-to-Job Local Match Index
const matchFilters = ref({
  graduation_year: props.filters.graduation_year || '',
  sector_id: props.filters.sector_id || '',
  program_id: props.filters.program_id || '',
});
function applyMatchFilters() {
  router.get(route('institutions.reports.degree'), matchFilters.value, {
    preserveState: true,
    preserveScroll: true,
  });
}

// ECharts logic
const chartRef = ref(null);
let chartInstance = null;
const chartOptions = computed(() => ({
  tooltip: { trigger: 'axis' },
  xAxis: {
    type: 'category',
    data: props.results.map(r => r.program),
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
      data: props.results.map(r => r.match_percentage),
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
  if (!chartInstance) {
    chartInstance = echarts.init(chartRef.value);
  }
  chartInstance.setOption(chartOptions.value);
  chartInstance.resize();
};
onMounted(() => {
  nextTick(renderChart);
  window.addEventListener('resize', renderChart);
});
onBeforeUnmount(() => {
  if (chartInstance) {
    chartInstance.dispose();
    chartInstance = null;
  }
  window.removeEventListener('resize', renderChart);
});
watch(chartOptions, renderChart);
</script>