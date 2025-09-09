<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { use } from 'echarts/core';
import VChart from 'vue-echarts';
import { LineChart } from 'echarts/charts';
import { TitleComponent, TooltipComponent, LegendComponent, GridComponent } from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';
import { Calendar } from 'lucide-vue-next';
import axios from 'axios';

// Local refs for all data
const schoolYears = ref([]);
const graduates = ref([]);
const degrees = ref([]);
const programs = ref([]);
const loading = ref(true);

// Fetch data on mount
function fetchSchoolYearData() {
  loading.value = true;
  axios.get(route('institutions.reports.schoolYear.data')) // Make sure this route returns JSON data
    .then(res => {
      // Use res.data.schoolYears, etc.
      schoolYears.value = res.data.schoolYears;
      graduates.value = res.data.graduates;
      degrees.value = res.data.degrees;
      programs.value = res.data.programs;
    })
    .finally(() => {
      loading.value = false;
    });
}

onMounted(fetchSchoolYearData);

// Employment trend data per school year
const employmentTrend = computed(() => {
  const years = filteredSchoolYearLabels.value;
  const employed = [];
  const underemployed = [];
  const unemployed = [];

  years.forEach(year => {
    const grads = filteredGraduates.value.filter(g => g.school_year_range === year);
    employed.push(grads.filter(g => g.employment_status === 'Employed').length);
    underemployed.push(grads.filter(g => g.employment_status === 'Underemployed').length);
    unemployed.push(grads.filter(g => g.employment_status === 'Unemployed').length);
  });

  return {
    years,
    employed,
    underemployed,
    unemployed,
  };
});
//For Start/End School Year Range Filter
const lcSchoolYearStart = ref('');
const lcSchoolYearEnd = ref('');

// ECharts option
const employmentTrendOption = computed(() => ({
  title: {
    text: 'Graduate Employment Trends per School Year',
    left: 'center',
    top: 10,
    textStyle: { fontSize: 16 }
  },
  tooltip: { trigger: 'axis' },
  legend: { top: 40, data: ['Employed', 'Underemployed', 'Unemployed'] },
  grid: { left: 40, right: 20, bottom: 40, top: 80 },
  xAxis: {
    type: 'category',
    data: employmentTrend.value.years,
    name: 'School Year',
    nameLocation: 'middle',
    nameGap: 30,
    axisLabel: { rotate: 45 }
  },
  yAxis: {
    type: 'value',
    name: 'Number of Graduates',
    minInterval: 1,
    nameLocation: 'middle',
    nameGap: 40,
  },
  series: [
    {
      name: 'Employed',
      type: 'line',
      data: employmentTrend.value.employed,
      smooth: true,
      symbol: 'circle',
      lineStyle: { color: '#22c55e' },
      itemStyle: { color: '#22c55e' }
    },
    {
      name: 'Underemployed',
      type: 'line',
      data: employmentTrend.value.underemployed,
      smooth: true,
      symbol: 'circle',
      lineStyle: { color: '#f59e42' },
      itemStyle: { color: '#f59e42' }
    },
    {
      name: 'Unemployed',
      type: 'line',
      data: employmentTrend.value.unemployed,
      smooth: true,
      symbol: 'circle',
      lineStyle: { color: '#ef4444' },
      itemStyle: { color: '#ef4444' }
    }
  ]
}));

// Gender distribution option
const genderDistributionOption = computed(() => {
  const years = filteredSchoolYearLabels.value;
  const male = [];
  const female = [];
  years.forEach(year => {
    const grads = filteredGraduates.value.filter(g => g.school_year_range === year);
    male.push(grads.filter(g => g.gender === 'Male').length);
    female.push(grads.filter(g => g.gender === 'Female').length);
  });
  return {
    title: { text: 'Gender Distribution per School Year', left: 'center', top: 10, textStyle: { fontSize: 16 } },
    tooltip: { trigger: 'axis' },
    legend: { top: 40, data: ['Male', 'Female'] },
    grid: { left: 40, right: 20, bottom: 40, top: 80 },
    xAxis: { type: 'category', data: years, axisLabel: { rotate: 45 } },
    yAxis: { type: 'value', name: 'Number of Graduates', minInterval: 1 },
    series: [
      { name: 'Male', type: 'bar', stack: 'gender', data: male, itemStyle: { color: '#3b82f6' } },
      { name: 'Female', type: 'bar', stack: 'gender', data: female, itemStyle: { color: '#f472b6' } }
    ]
  };
});

const genderDistributionSummary = computed(() => {
  const years = filteredSchoolYearLabels.value;
  if (!years.length) return 'No data available for the selected school year range.';

  // Determine start and end year for summary
  const startYear = lcSchoolYearStart.value || years[0];
  const endYear = lcSchoolYearEnd.value || years[years.length - 1];

  // Find index for start and end year
  const startIdx = years.indexOf(startYear);
  const endIdx = years.indexOf(endYear);

  // Helper to get summary for a year
  function yearSummary(idx) {
    const year = years[idx];
    const maleEmployed = filteredGraduates.value.filter(g => g.school_year_range === year && g.gender === 'Male' && g.employment_status === 'Employed').length;
    const femaleEmployed = filteredGraduates.value.filter(g => g.school_year_range === year && g.gender === 'Female' && g.employment_status === 'Employed').length;
    const maleUnemployed = filteredGraduates.value.filter(g => g.school_year_range === year && g.gender === 'Male' && g.employment_status === 'Unemployed').length;
    const femaleUnemployed = filteredGraduates.value.filter(g => g.school_year_range === year && g.gender === 'Female' && g.employment_status === 'Unemployed').length;

    // Highest employed gender
    let employedGender = '';
    if (maleEmployed > femaleEmployed) {
      employedGender = `male graduates had the highest employment (${maleEmployed} employed)`;
    } else if (femaleEmployed > maleEmployed) {
      employedGender = `female graduates had the highest employment (${femaleEmployed} employed)`;
    } else {
      employedGender = `male and female graduates had equal employment (${maleEmployed} each)`;
    }

    // Highest unemployed gender
    let unemployedGender = '';
    if (maleUnemployed > femaleUnemployed) {
      unemployedGender = `male graduates had the highest unemployment (${maleUnemployed} unemployed)`;
    } else if (femaleUnemployed > maleUnemployed) {
      unemployedGender = `female graduates had the highest unemployment (${femaleUnemployed} unemployed)`;
    } else {
      unemployedGender = `male and female graduates had equal unemployment (${maleUnemployed} each)`;
    }

    return `In year ${year}, ${employedGender}, while ${unemployedGender}.`;
  }

  // Only show summary for start and end year (if different)
  let summary = yearSummary(startIdx);
  if (endIdx !== startIdx) {
    summary += ' ' + yearSummary(endIdx);
  }

  // Trend for employment
  const maleEmployedArr = years.map(year =>
    filteredGraduates.value.filter(g => g.school_year_range === year && g.gender === 'Male' && g.employment_status === 'Employed').length
  );
  const femaleEmployedArr = years.map(year =>
    filteredGraduates.value.filter(g => g.school_year_range === year && g.gender === 'Female' && g.employment_status === 'Employed').length
  );
  function getTrend(arr) {
    if (arr.length < 2) return 'remained stable';
    return arr[arr.length - 1] > arr[0] ? 'increased' :
      arr[arr.length - 1] < arr[0] ? 'decreased' : 'remained stable';
  }
  const maleTrend = getTrend(maleEmployedArr);
  const femaleTrend = getTrend(femaleEmployedArr);

  summary += ` Looking at the trends, employment among male graduates has ${maleTrend} over time, while employment among female graduates has ${femaleTrend}.`;

  return summary;
});

const graduateCountByTermSummary = computed(() => {
  const years = filteredSchoolYearLabels.value;
  const terms = termLabels.value;
  if (!years.length || !terms.length) return 'No data available for the selected school year range or terms.';

  // Determine start and end year for summary
  const startYear = lcSchoolYearStart.value || years[0];
  const endYear = lcSchoolYearEnd.value || years[years.length - 1];
  const startIdx = years.indexOf(startYear);
  const endIdx = years.indexOf(endYear);

  // Helper to get summary for a year
  function yearSummary(idx) {
    const year = years[idx];
    let maxEmployed = -1, maxUnemployed = -1;
    let maxEmployedTerm = '', maxUnemployedTerm = '';
    terms.forEach(term => {
      const employed = filteredGraduates.value.filter(g =>
        g.school_year_range === year && g.term === term && g.employment_status === 'Employed').length;
      const unemployed = filteredGraduates.value.filter(g =>
        g.school_year_range === year && g.term === term && g.employment_status === 'Unemployed').length;
      if (employed > maxEmployed) {
        maxEmployed = employed;
        maxEmployedTerm = term;
      }
      if (unemployed > maxUnemployed) {
        maxUnemployed = unemployed;
        maxUnemployedTerm = term;
      }
    });
    return `In year ${year}, the term with the highest employment was ${maxEmployedTerm} (${maxEmployed} employed), while the highest unemployment was in ${maxUnemployedTerm} (${maxUnemployed} unemployed).`;
  }

  // Only show summary for start and end year (if different)
  let summary = yearSummary(startIdx);
  if (endIdx !== startIdx) {
    summary += ' ' + yearSummary(endIdx);
  }

  // Trend for each term
  let trends = '';
  terms.forEach(term => {
    const employedArr = years.map(year =>
      filteredGraduates.value.filter(g =>
        g.school_year_range === year && g.term === term && g.employment_status === 'Employed').length
    );
    const unemployedArr = years.map(year =>
      filteredGraduates.value.filter(g =>
        g.school_year_range === year && g.term === term && g.employment_status === 'Unemployed').length
    );
    function getTrend(arr) {
      if (arr.length < 2) return 'remained stable';
      return arr[arr.length - 1] > arr[0] ? 'increased' :
        arr[arr.length - 1] < arr[0] ? 'decreased' : 'remained stable';
    }
    trends += `For the term ${term}, employment has ${getTrend(employedArr)} and unemployment has ${getTrend(unemployedArr)} over the selected years. `;
  });

  return `${summary} ${trends}`;
});

// Get all unique school year ranges sorted
const allSchoolYearLabels = computed(() => {
  return [...new Set(schoolYears.value.map(sy => sy.school_year_range))].sort();
});

// Filtered school year labels for chart and table
const filteredSchoolYearLabels = computed(() => {
  let labels = allSchoolYearLabels.value;
  if (lcSchoolYearStart.value && lcSchoolYearEnd.value) {
    labels = labels.filter(
      sy => sy >= lcSchoolYearStart.value && sy <= lcSchoolYearEnd.value
    );
  } else if (lcSchoolYearStart.value) {
    labels = labels.filter(sy => sy >= lcSchoolYearStart.value);
  } else if (lcSchoolYearEnd.value) {
    labels = labels.filter(sy => sy <= lcSchoolYearEnd.value);
  }
  return labels;
});

// Deduplicate school years by school_year_range
const uniqueSchoolYears = computed(() => {
  const seen = new Set();
  return schoolYears.value.filter(sy => {
    if (!sy.school_year_range || seen.has(sy.school_year_range)) return false;
    seen.add(sy.school_year_range);
    return true;
  });
});

// Deduplicate terms
const uniqueTerms = computed(() => {
  const seen = new Set();
  return schoolYears.value
    .map(sy => sy.term)
    .filter(term => term && !seen.has(term) && seen.add(term));
});

// Filters for graduates table
const filters = ref({
  name: '',
  gender: '',
  schoolYear: '',
  degree: '',
  program: '',
  employmentStatus: '',
  term: '',
});

// Real-time filtered graduates
const filteredGraduates = computed(() => {
  return graduates.value.filter(g => {
    // ...other filters...
    let inRange = true;
    if (lcSchoolYearStart.value && lcSchoolYearEnd.value) {
      inRange = g.school_year_range >= lcSchoolYearStart.value && g.school_year_range <= lcSchoolYearEnd.value;
    } else if (lcSchoolYearStart.value) {
      inRange = g.school_year_range >= lcSchoolYearStart.value;
    } else if (lcSchoolYearEnd.value) {
      inRange = g.school_year_range <= lcSchoolYearEnd.value;
    }
    if (!inRange) return false;
    // ...existing filter logic...
    const name = `${g.first_name} ${g.middle_name ?? ''} ${g.last_name}`.toLowerCase();
    if (filters.value.name && !name.includes(filters.value.name.toLowerCase())) return false;
    if (filters.value.gender && g.gender !== filters.value.gender) return false;
    if (filters.value.schoolYear && g.school_year_range !== filters.value.schoolYear) return false;
    if (filters.value.degree && g.degree_id != filters.value.degree) return false;
    if (filters.value.program && g.program_id != filters.value.program) return false;
    if (filters.value.employmentStatus && g.employment_status !== filters.value.employmentStatus) return false;
    if (filters.value.term && g.term !== filters.value.term) return false;
    return true;
  });
});

// Pagination
const page = ref(1);
const perPage = 10;
const totalPages = computed(() => Math.ceil(filteredGraduates.value.length / perPage));
const paginatedGraduates = computed(() => {
  const start = (page.value - 1) * perPage;
  return filteredGraduates.value.slice(start, start + perPage);
});
watch(filteredGraduates, () => { page.value = 1; });

const employmentSummary = computed(() => {
  const { years, employed, underemployed, unemployed } = employmentTrend.value;
  if (!years.length) return 'No data available for the selected school year range.';

  // Find peak employed year
  const maxEmployed = Math.max(...employed);
  const peakYearIdx = employed.indexOf(maxEmployed);
  const peakYear = years[peakYearIdx];

  // Find lowest unemployed year
  const minUnemployed = Math.min(...unemployed);
  const minUnemployedYearIdx = unemployed.indexOf(minUnemployed);
  const minUnemployedYear = years[minUnemployedYearIdx];

  // Find most recent year
  const recentYear = years[years.length - 1];
  const recentEmployed = employed[years.length - 1];
  const recentUnderemployed = underemployed[years.length - 1];
  const recentUnemployed = unemployed[years.length - 1];

  let summary = `From ${years[0]} to ${recentYear}, the number of employed graduates peaked in ${peakYear} (${maxEmployed}). `;
  summary += `The lowest unemployment was recorded in ${minUnemployedYear} (${minUnemployed}). `;
  summary += `In the most recent year (${recentYear}), there were ${recentEmployed} employed, ${recentUnderemployed} underemployed, and ${recentUnemployed} unemployed graduates.`;

  // Add trend direction
  if (recentEmployed > employed[0]) {
    summary += ` Overall, employment among graduates has increased over time.`;
  } else if (recentEmployed < employed[0]) {
    summary += ` Overall, employment among graduates has decreased over time.`;
  } else {
    summary += ` Overall, employment among graduates has remained stable over time.`;
  }

  return summary;
});

const termLabels = computed(() => {
  return [...new Set(filteredGraduates.value.map(g => g.term).filter(Boolean))];
});

const graduateCountByTermOption = computed(() => {
  const years = filteredSchoolYearLabels.value;
  const series = termLabels.value.map(term => ({
    name: term,
    type: 'bar',
    data: years.map(year => {
      const grads = filteredGraduates.value.filter(g => g.school_year_range === year && g.term === term);
      return grads.length;
    }),
    itemStyle: { color: undefined } // Assign colors if desired
  }));
  return {
    title: { text: 'Graduate Count by Term per School Year', left: 'center', top: 10, textStyle: { fontSize: 16 } },
    tooltip: { trigger: 'axis' },
    legend: { top: 40, data: termLabels.value },
    grid: { left: 40, right: 20, bottom: 40, top: 80 },
    xAxis: { type: 'category', data: years, axisLabel: { rotate: 45 } },
    yAxis: { type: 'value', name: 'Number of Graduates', minInterval: 1 },
    series
  };
});
</script>

<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto py-10 px-4">
      <h1 class="text-3xl font-bold mb-6 text-gray-800 flex items-center gap-2">
        <Calendar class="w-8 h-8 text-cyan-500" />
        School Year Report
      </h1>

      <!-- Loading Spinner -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <svg class="animate-spin h-10 w-10 text-cyan-500" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
      </div>

      <div v-else>
        <!-- Card for total school year entries -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
            <span class="text-2xl font-bold text-gray-800">{{ schoolYears.length }}</span>
            <span class="text-gray-500">Total School Year Entries</span>
          </div>
        </div>

        <!-- Table 1: List of School Years -->
        <div class="mb-10">
          <h2 class="text-xl font-semibold mb-4">All School Years</h2>
          <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">School Year</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="sy in schoolYears" :key="sy.id">
                  <td class="px-4 py-2">{{ sy.school_year_range }}</td>
                  <td class="px-4 py-2">{{ sy.term }}</td>
                  <td class="px-4 py-2">{{ sy.status }}</td>
                </tr>
                <tr v-if="schoolYears.length === 0">
                  <td colspan="3" class="text-center text-gray-400 py-6">No school years found.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Filters for graduates table -->
        <div class="bg-white rounded-xl shadow p-6 mb-8 grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search by Name</label>
            <input v-model="filters.name" type="text" class="rounded-lg border-gray-300 w-full"
              placeholder="Type name..." />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start School Year</label>
            <select v-model="lcSchoolYearStart" class="rounded-lg border-gray-300 w-full">
              <option value="">All</option>
              <option v-for="sy in allSchoolYearLabels" :key="sy" :value="sy">{{ sy }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">End School Year</label>
            <select v-model="lcSchoolYearEnd" class="rounded-lg border-gray-300 w-full">
              <option value="">All</option>
              <option v-for="sy in allSchoolYearLabels" :key="sy" :value="sy">{{ sy }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Term</label>
            <select v-model="filters.term" class="rounded-lg border-gray-300 w-full">
              <option value="">All</option>
              <option v-for="term in uniqueTerms" :key="term" :value="term">{{ term }}</option>
            </select>
          </div>
        </div>

        <!-- Table 2: List of Graduates -->
        <div class="bg-white rounded-xl shadow overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year Graduated</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="g in paginatedGraduates" :key="g.id">
                <td class="px-4 py-2">{{ g.first_name }} {{ g.middle_name }} {{ g.last_name }}</td>
                <td class="px-4 py-2">{{ g.school_year_range || 'N/A' }}</td>
                <td class="px-4 py-2">{{ g.term || 'N/A' }}</td>
              </tr>
              <tr v-if="paginatedGraduates.length === 0">
                <td colspan="7" class="text-center text-gray-400 py-6">No graduates found.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex justify-center gap-2">
          <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300" :disabled="page === 1"
            @click="page--">Prev</button>
          <span class="px-3 py-1">{{ page }} / {{ totalPages }}</span>
          <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300" :disabled="page === totalPages"
            @click="page++">Next</button>
        </div>

        <!-- Employment Trend Line Chart -->
        <div class="mt-10 bg-white rounded-xl shadow p-6">
          <v-chart :option="employmentTrendOption" autoresize style="height:350px;" />
          <div class="mt-6 p-4 bg-blue-50 rounded-lg text-blue-900 text-base font-medium">
            <span class="font-semibold">Summary Interpretation:</span>
            <br>
            {{ employmentSummary }}
          </div>
        </div>

        <!-- Gender Distribution Chart -->
        <div class="mt-10 bg-white rounded-xl shadow p-6">
          <v-chart :option="genderDistributionOption" autoresize style="height:350px;" />
          <div class="mt-6 p-4 bg-pink-50 rounded-lg text-pink-900 text-base font-medium">
            <span class="font-semibold">Summary Interpretation:</span>
            <br>
            {{ genderDistributionSummary }}
          </div>
        </div>

        <!-- Graduate Count by Term Chart -->
        <div class="mt-10 bg-white rounded-xl shadow p-6">
          <v-chart :option="graduateCountByTermOption" autoresize style="height:350px;" />
          <div class="mt-6 p-4 bg-yellow-50 rounded-lg text-yellow-900 text-base font-medium">
            <span class="font-semibold">Summary Interpretation:</span>
            <br>
            {{ graduateCountByTermSummary }}
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
