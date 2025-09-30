<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { GraduationCap } from 'lucide-vue-next';
import VueECharts from 'vue-echarts';
import axios from 'axios';

const props = defineProps({
  schoolYears: Array,
  degrees: Array,
  programs: Array,
  careerOpportunities: Array,
  skills: Array,
  internshipPrograms: Array,
  terms: Array,
});

const filters = ref({
  name: '',
  gender: '',
  schoolYear: '', // school_year_range
  degree: '',
  program: '',
  employmentStatus: '',
  careerOpportunity: '',
  skill: '',
  internshipProgram: '',
  term: '', // term
  alignment: '',
});

const graduates = ref([]);
const employed = ref(0);
const underemployed = ref(0);
const unemployed = ref(0);
const total = ref(0);
const aligned = ref(0);
const misaligned = ref(0);
const loading = ref(true);

function fetchGraduateData() {
  loading.value = true;
  axios.get(route('institutions.reports.graduate.data'), {
    params: {
      name: filters.value.name,
      gender: filters.value.gender,
      school_year_range: filters.value.schoolYear, // <-- use school_year_range
      degree_id: filters.value.degree,
      program_id: filters.value.program,
      employment_status: filters.value.employmentStatus,
      career_opportunity: filters.value.careerOpportunity,
      skill: filters.value.skill,
      internship_program_id: filters.value.internshipProgram,
      term: filters.value.term, // <-- use term
      alignment: filters.value.alignment,
    }
  }).then(res => {
    graduates.value = res.data.graduates;
    employed.value = res.data.employed;
    underemployed.value = res.data.underemployed;
    unemployed.value = res.data.unemployed;
    total.value = res.data.total;
    aligned.value = res.data.aligned;
    misaligned.value = res.data.misaligned;
    page.value = 1;
  }).finally(() => {
    loading.value = false;
  });
}

onMounted(fetchGraduateData);
watch(filters, fetchGraduateData, { deep: true });

const page = ref(1);
const perPage = 20;
const totalPages = computed(() => Math.ceil(graduates.value.length / perPage));
const paginatedGraduates = computed(() => {
  const start = (page.value - 1) * perPage;
  return graduates.value.slice(start, start + perPage);
});

// Deduplicate school years and terms for filters
const uniqueSchoolYears = computed(() => {
  const seen = new Set();
  return props.schoolYears.filter(sy => {
    if (!sy.school_year_range || seen.has(sy.school_year_range)) return false;
    seen.add(sy.school_year_range);
    return true;
  });
});
const uniqueTerms = computed(() => {
  const seen = new Set();
  return props.schoolYears
    .map(sy => sy.term)
    .filter(term => term && !seen.has(term) && seen.add(term));
});
const uniqueCareerOpportunities = computed(() => {
  const seen = new Set();
  return props.careerOpportunities.filter(co => {
    if (seen.has(co.title)) return false;
    seen.add(co.title);
    return true;
  });
});

// --- Stacked Bar Chart: Gender vs Employment Status ---
const genderEmploymentData = computed(() => {
  const genders = ['Male', 'Female'];
  const statuses = ['Employed', 'Underemployed', 'Unemployed'];
  const counts = genders.map(gender =>
    statuses.map(status =>
      graduates.value.filter(g =>
        g.gender === gender && g.employment_status === status
      ).length
    )
  );
  return { genders, statuses, counts };
});

const genderStackedBarOption = computed(() => ({
  tooltip: { trigger: 'axis' },
  legend: { data: genderEmploymentData.value.statuses },
  xAxis: { type: 'category', data: genderEmploymentData.value.genders },
  yAxis: { type: 'value' },
  series: genderEmploymentData.value.statuses.map((status, idx) => ({
    name: status,
    type: 'bar',
    stack: 'total',
    data: genderEmploymentData.value.genders.map(
      (_, gIdx) => genderEmploymentData.value.counts[gIdx][idx]
    ),
    itemStyle: {
      color: ['#22c55e', '#facc15', '#ef4444'][idx]
    }
  }))
}));

const employmentPieOption = computed(() => ({
  tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
  legend: { orient: 'vertical', left: 'left' },
  series: [
    {
      name: 'Employment Status',
      type: 'pie',
      radius: ['60%', '80%'],
      avoidLabelOverlap: false,
      label: {
        show: true,
        formatter: '{b}: {d}%',
        fontWeight: 'bold'
      },
      data: [
        { value: employed.value, name: 'Employed', itemStyle: { color: '#22c55e' } },
        { value: underemployed.value, name: 'Underemployed', itemStyle: { color: '#facc15' } },
        { value: unemployed.value, name: 'Unemployed', itemStyle: { color: '#ef4444' } },
      ]
    }
  ]
}));

// Graduate Trends Over Time
const graduateTrends = computed(() => {
  // { [school_year_range]: graduate count }
  const trends = {};
  graduates.value.forEach(g => {
    if (g.school_year_range) {
      trends[g.school_year_range] = (trends[g.school_year_range] || 0) + 1;
    }
  });
  return Object.entries(trends)
    .map(([year, count]) => ({ year, count }))
    .sort((a, b) => a.year.localeCompare(b.year));
});

const graduateTrendsSummary = computed(() => {
  if (!graduateTrends.value.length) return 'No graduate trend data available.';
  const max = graduateTrends.value.reduce((a, b) => (a.count > b.count ? a : b), { count: 0 });
  return `The year "${max.year}" produced the highest number of graduates, indicating a peak in graduate output for your institution.`;
});
// Graduate Alignment Over Time
const alignmentTrends = computed(() => {
  // { [school_year_range]: { aligned: count, misaligned: count } }
  const trends = {};
  graduates.value.forEach(g => {
    if (g.school_year_range) {
      if (!trends[g.school_year_range]) trends[g.school_year_range] = { aligned: 0, misaligned: 0 };
      if (g.alignment === 'Aligned') trends[g.school_year_range].aligned += 1;
      else if (g.alignment === 'Misaligned') trends[g.school_year_range].misaligned += 1;
    }
  });
  return Object.entries(trends)
    .map(([year, data]) => ({ year, ...data }))
    .sort((a, b) => a.year.localeCompare(b.year));
});

const alignmentTrendsSummary = computed(() => {
  if (!alignmentTrends.value.length) return 'No alignment trend data available.';
  const best = alignmentTrends.value.reduce((a, b) => ((b.aligned / (b.aligned + b.misaligned || 1)) > (a.aligned / (a.aligned + a.misaligned || 1)) ? b : a), { aligned: 0, misaligned: 0 });
  const percent = best.aligned + best.misaligned > 0 ? ((best.aligned / (best.aligned + best.misaligned)) * 100).toFixed(1) : 0;
  return `The year "${best.year}" had the highest alignment rate at ${percent}%, showing the best match between graduate jobs and mapped career opportunities.`;
});
// Graduate Outcomes by Term
const outcomesByTerm = computed(() => {
  // { [term]: { employed, underemployed, unemployed } }
  const map = {};
  graduates.value.forEach(g => {
    if (!g.term) return;
    if (!map[g.term]) map[g.term] = { employed: 0, underemployed: 0, unemployed: 0 };
    if (g.employment_status === 'Employed') map[g.term].employed += 1;
    else if (g.employment_status === 'Underemployed') map[g.term].underemployed += 1;
    else if (g.employment_status === 'Unemployed') map[g.term].unemployed += 1;
  });
  return Object.entries(map)
    .map(([term, data]) => ({ term, ...data }))
    .sort((a, b) => a.term.localeCompare(b.term));
});

const outcomesByTermSummary = computed(() => {
  if (!outcomesByTerm.value.length) return 'No outcomes by term data available.';
  const best = outcomesByTerm.value.reduce((a, b) => (b.employed > a.employed ? b : a), { employed: 0 });
  return `The term "${best.term}" produced the most employed graduates, suggesting a possible seasonal or cohort effect on employment outcomes.`;
});
// Graduate Skills Distribution
const skillCounts = computed(() => {
  const counts = {};
  graduates.value.forEach(g => {
    if (Array.isArray(g.graduateSkills)) {
      g.graduateSkills.forEach(gs => {
        const skillName = gs.skill?.name || gs.name;
        if (skillName) counts[skillName] = (counts[skillName] || 0) + 1;
      });
    }
  });
  return Object.entries(counts)
    .map(([name, count]) => ({ name, count }))
    .sort((a, b) => b.count - a.count)
    .slice(0, 10); // Top 10 skills
});

const skillCountsSummary = computed(() => {
  if (!skillCounts.value.length) return 'No graduate skills data available.';
  const top = skillCounts.value[0];
  return `The most common skill among graduates is "${top.name}", reflecting the key competencies developed by your institution.`;
});

const genderEmploymentSummary = computed(() => {
  // Calculate totals for each gender and status
  const genders = ['Male', 'Female'];
  const statuses = ['Employed', 'Underemployed', 'Unemployed'];
  const counts = {};
  genders.forEach(gender => {
    counts[gender] = {};
    statuses.forEach(status => {
      counts[gender][status] = graduates.value.filter(
        g => g.gender === gender && g.employment_status === status
      ).length;
    });
  });
  // Find which gender has higher employment, underemployment, unemployment
  const moreEmployed =
    counts['Male']['Employed'] > counts['Female']['Employed']
      ? 'male'
      : counts['Male']['Employed'] < counts['Female']['Employed']
        ? 'female'
        : 'both male and female';
  const moreUnderemployed =
    counts['Male']['Underemployed'] > counts['Female']['Underemployed']
      ? 'male'
      : counts['Male']['Underemployed'] < counts['Female']['Underemployed']
        ? 'female'
        : 'both male and female';
  const moreUnemployed =
    counts['Male']['Unemployed'] > counts['Female']['Unemployed']
      ? 'male'
      : counts['Male']['Unemployed'] < counts['Female']['Unemployed']
        ? 'female'
        : 'both male and female';
  // Compose narrative
  let summary = '';
  if (counts['Male']['Employed'] === 0 && counts['Female']['Employed'] === 0 &&
    counts['Male']['Underemployed'] === 0 && counts['Female']['Underemployed'] === 0 &&
    counts['Male']['Unemployed'] === 0 && counts['Female']['Unemployed'] === 0) {
    summary = 'There are no graduates with employment data available for gender comparison.';
  } else {
    summary = `Among graduates, ${moreEmployed} graduates have the highest employment rate, while ${moreUnderemployed} graduates experience more underemployment, and ${moreUnemployed} graduates face higher unemployment. These patterns highlight how employment outcomes can differ by gender, suggesting the need for ongoing support to ensure equitable opportunities for all graduates.`;
  }
  return summary;
});
</script>


<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto py-10 px-4">
      <h1 class="text-3xl font-bold mb-6 text-gray-800 flex items-center gap-2">
        <GraduationCap class="w-8 h-8 text-cyan-500" />
        Graduate Report
      </h1>

      <!-- Loading Spinner -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <svg class="animate-spin h-10 w-10 text-cyan-500" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
      </div>

      <div v-else>
        <!-- KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
            <span class="text-2xl font-bold text-gray-800">{{ total }}</span>
            <span class="text-gray-500">Total Graduates</span>
          </div>
          <div class="bg-green-100 rounded-xl shadow p-6 flex flex-col items-center">
            <span class="text-2xl font-bold text-green-700">{{ employed }}</span>
            <span class="text-gray-600">Employed</span>
          </div>
          <div class="bg-yellow-100 rounded-xl shadow p-6 flex flex-col items-center">
            <span class="text-2xl font-bold text-yellow-700">{{ underemployed }}</span>
            <span class="text-gray-600">Underemployed</span>
          </div>
          <div class="bg-red-100 rounded-xl shadow p-6 flex flex-col items-center">
            <span class="text-2xl font-bold text-red-700">{{ unemployed }}</span>
            <span class="text-gray-600">Unemployed</span>
          </div>
        </div>

        <!-- Alignment KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-6 mb-8">
          <div class="bg-blue-100 rounded-xl shadow p-6 flex flex-col items-center">
            <span class="text-2xl font-bold text-blue-700">{{ aligned }}</span>
            <span class="text-gray-600">Aligned Graduates</span>
          </div>
          <div class="bg-pink-100 rounded-xl shadow p-6 flex flex-col items-center">
            <span class="text-2xl font-bold text-pink-700">{{ misaligned }}</span>
            <span class="text-gray-600">Misaligned Graduates</span>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow p-6 mb-8 grid grid-cols-1 md:grid-cols-3 gap-4">
          <input v-model="filters.name" type="text" placeholder="Search by name"
            class="rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
          <select v-model="filters.gender" class="rounded-lg border-gray-300">
            <option value="">All Genders</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
          <select v-model="filters.schoolYear" class="rounded-lg border-gray-300">
            <option value="">All School Years</option>
            <option v-for="sy in uniqueSchoolYears" :key="sy.school_year_range" :value="sy.school_year_range">
              {{ sy.school_year_range }}
            </option>
          </select>
          <select v-model="filters.degree" class="rounded-lg border-gray-300">
            <option value="">All Degrees</option>
            <option v-for="deg in degrees" :key="deg.id" :value="deg.id">{{ deg.type }}</option>
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
          <select v-model="filters.careerOpportunity" class="rounded-lg border-gray-300">
            <option value="">All Career Opportunities</option>
            <option v-for="co in uniqueCareerOpportunities" :key="co.id" :value="co.title">{{ co.title }}</option>
          </select>
          <select v-model="filters.skill" class="rounded-lg border-gray-300">
            <option value="">All Skills</option>
            <option v-for="sk in skills" :key="sk.id" :value="sk.name">{{ sk.name }}</option>
          </select>
          <select v-model="filters.internshipProgram" class="rounded-lg border-gray-300">
            <option value="">All Internship Programs</option>
            <option v-for="ip in internshipPrograms" :key="ip.id" :value="ip.id">{{ ip.title }}</option>
          </select>
          <select v-model="filters.term" class="rounded-lg border-gray-300">
            <option value="">All Terms</option>
            <option v-for="term in uniqueTerms" :key="term" :value="term">
              {{ term }}
            </option>
          </select>
          <!-- Alignment Filter -->
          <select v-model="filters.alignment" class="rounded-lg border-gray-300">
            <option value="">All Alignment</option>
            <option value="Directly aligned">Directly aligned</option>
            <option value="Partially aligned">Partially aligned</option>
            <option value="Misaligned">Misaligned</option>
          </select>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gender</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year Graduated</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Degree</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Program</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employment Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Job Title</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alignment</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="g in paginatedGraduates" :key="g.id">
                <td class="px-4 py-2">{{ g.first_name }} {{ g.middle_name }} {{ g.last_name }}</td>
                <td class="px-4 py-2">{{ g.gender || 'N/A' }}</td>
                <td class="px-4 py-2">{{ g.school_year_range || 'N/A' }}</td>
                <td class="px-4 py-2">{{ g.term || 'N/A' }}</td>
                <td class="px-4 py-2">{{ g.degree || 'N/A' }}</td>
                <td class="px-4 py-2">{{ g.program || 'N/A' }}</td>
                <td class="px-4 py-2">{{ g.employment_status }}</td>
                <td class="px-4 py-2">{{ g.current_job_title }}</td>
                <td class="px-4 py-2">
                  <span :class="{
                    'text-blue-700 font-semibold': g.alignment_category === 'Directly aligned',
                    'text-yellow-700 font-semibold': g.alignment_category === 'Partially aligned',
                    'text-pink-700 font-semibold': g.alignment_category === 'Misaligned'
                  }">
                    {{ g.alignment_score !== null ? g.alignment_score + '%' : 'N/A' }}
                    <br>
                    <small>{{ g.alignment_category || 'N/A' }}</small>
                  </span>
                </td>
              </tr>
              <tr v-if="paginatedGraduates.length === 0">
                <td colspan="9" class="text-center text-gray-400 py-6">No graduates found.</td>
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

        <!-- Charts -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-10">
          <!-- Stacked Bar Chart: Gender vs Employment Status -->
          <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-700">Employment Status by Gender</h3>
            <VueECharts :option="genderStackedBarOption" style="height: 400px; width: 100%;" />
            <div class="mt-4 p-4 bg-cyan-50 rounded-lg text-cyan-900 text-base font-medium">
              <span class="font-semibold">Summary Interpretation:</span>
              <br>
              {{ genderEmploymentSummary }}
            </div>
          </div>
          <!-- Pie/Doughnut Chart: Employment Rate -->
          <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center justify-center">
            <h3 class="text-lg font-semibold mb-4 text-gray-700">Employment Rate Distribution</h3>
            <VueECharts :option="employmentPieOption" style="height: 350px; width: 100%; max-width: 400px;" />
            <div class="mt-4 text-center">
              <div class="text-2xl font-bold text-green-700">
                {{ total ? ((employed + underemployed) / total * 100).toFixed(1) : 0 }}%
              </div>
              <div class="text-gray-500">Employment Rate</div>
            </div>
          </div>
        </div>
        <!-- Graduate Trends Over Time -->
        <div class="my-10">
          <h2 class="text-xl font-semibold mb-4 text-cyan-700">Graduate Trends Over Time</h2>
          <VueECharts v-if="graduateTrends.length" :option="{
            xAxis: { type: 'category', data: graduateTrends.map(t => t.year) },
            yAxis: { type: 'value', name: 'Graduate Count' },
            series: [{ type: 'line', data: graduateTrends.map(t => t.count), itemStyle: { color: '#06b6d4' } }]
          }" autoresize style="width: 100%; height: 300px;" />
          <div v-else class="text-gray-400 text-center py-8">No trend data available.</div>
          <div class="mt-4 p-4 bg-cyan-50 rounded-lg text-cyan-900 text-base font-medium">
            <span class="font-semibold">Summary Interpretation:</span>
            <br>
            {{ graduateTrendsSummary }}
          </div>
        </div>
        <!-- Graduate Alignment Over Time -->
        <div class="my-10">
          <h2 class="text-xl font-semibold mb-4 text-cyan-700">Graduate Alignment Over Time</h2>
          <VueECharts v-if="alignmentTrends.length" :option="{
            tooltip: { trigger: 'axis' },
            legend: { data: ['Aligned', 'Misaligned'] },
            xAxis: { type: 'category', data: alignmentTrends.map(t => t.year) },
            yAxis: { type: 'value', name: 'Graduate Count' },
            series: [
              { name: 'Aligned', type: 'bar', stack: 'total', data: alignmentTrends.map(t => t.aligned), itemStyle: { color: '#3b82f6' } },
              { name: 'Misaligned', type: 'bar', stack: 'total', data: alignmentTrends.map(t => t.misaligned), itemStyle: { color: '#f472b6' } }
            ]
          }" autoresize style="width: 100%; height: 300px;" />
          <div v-else class="text-gray-400 text-center py-8">No alignment trend data available.</div>
          <div class="mt-4 p-4 bg-cyan-50 rounded-lg text-cyan-900 text-base font-medium">
            <span class="font-semibold">Summary Interpretation:</span>
            <br>
            {{ alignmentTrendsSummary }}
          </div>
        </div>
        <!-- Graduate Outcomes by Term -->
        <div class="my-10">
          <h2 class="text-xl font-semibold mb-4 text-cyan-700">Graduate Outcomes by Term</h2>
          <VueECharts v-if="outcomesByTerm.length" :option="{
            tooltip: { trigger: 'axis' },
            legend: { data: ['Employed', 'Underemployed', 'Unemployed'] },
            xAxis: { type: 'category', data: outcomesByTerm.map(t => t.term) },
            yAxis: { type: 'value', name: 'Graduate Count' },
            series: [
              { name: 'Employed', type: 'bar', stack: 'total', data: outcomesByTerm.map(t => t.employed), itemStyle: { color: '#22c55e' } },
              { name: 'Underemployed', type: 'bar', stack: 'total', data: outcomesByTerm.map(t => t.underemployed), itemStyle: { color: '#facc15' } },
              { name: 'Unemployed', type: 'bar', stack: 'total', data: outcomesByTerm.map(t => t.unemployed), itemStyle: { color: '#ef4444' } }
            ]
          }" autoresize style="width: 100%; height: 300px;" />
          <div v-else class="text-gray-400 text-center py-8">No outcomes by term data available.</div>
          <div class="mt-4 p-4 bg-cyan-50 rounded-lg text-cyan-900 text-base font-medium">
            <span class="font-semibold">Summary Interpretation:</span>
            <br>
            {{ outcomesByTermSummary }}
          </div>
        </div>
        <!-- Graduate Skills Distribution -->
        <div class="my-10">
          <h2 class="text-xl font-semibold mb-4 text-cyan-700">Graduate Skills Distribution</h2>
          <VueECharts v-if="skillCounts.length" :option="{
            xAxis: { type: 'category', data: skillCounts.map(s => s.name) },
            yAxis: { type: 'value', name: 'Graduate Count' },
            series: [{ type: 'bar', data: skillCounts.map(s => s.count), itemStyle: { color: '#06b6d4' } }]
          }" autoresize style="width: 100%; height: 300px;" />
          <div v-else class="text-gray-400 text-center py-8">No skills data available.</div>
          <div class="mt-4 p-4 bg-cyan-50 rounded-lg text-cyan-900 text-base font-medium">
            <span class="font-semibold">Summary Interpretation:</span>
            <br>
            {{ skillCountsSummary }}
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
