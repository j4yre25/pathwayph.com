<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { GraduationCap, Briefcase, Users, ThumbsUp, TrendingUp, BarChart2, Zap, Monitor, Code } from 'lucide-vue-next';
import VueECharts from 'vue-echarts';
import axios from 'axios';

// --- (Keep all logic and computed properties as they were. Do not modify this section's content or structure) ---

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

const directlyAligned = computed(() =>
  graduates.value.filter(g => g.alignment_category === 'Directly aligned').length
);
const partiallyAligned = computed(() =>
  graduates.value.filter(g => g.alignment_category === 'Partially aligned').length
);
const misalignedCount = computed(() =>
  graduates.value.filter(g => g.alignment_category === 'Misaligned').length
);

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
const perPage = 10;
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

// --- Chart Options (Adjusted colors/radius for professional look) ---
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
  grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
  series: genderEmploymentData.value.statuses.map((status, idx) => ({
    name: status,
    type: 'bar',
    stack: 'total',
    data: genderEmploymentData.value.genders.map(
      (_, gIdx) => genderEmploymentData.value.counts[gIdx][idx]
    ),
    itemStyle: {
      color: ['#10b981', '#f59e0b', '#ef4444'][idx]
    }
  }))
}));

const employmentPieOption = computed(() => ({
  tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
  legend: { orient: 'horizontal', bottom: '0', icon: 'circle', padding: [10, 0] },
  series: [
    {
      name: 'Employment Status',
      type: 'pie',
      radius: ['55%', '75%'],
      center: ['50%', '45%'],
      avoidLabelOverlap: false,
      label: {
        show: true,
        position: 'center',
        formatter: () => `{total|${total.value}} \n {text|Total}`,
        rich: {
          total: {
            fontSize: 24,
            fontWeight: 'bold',
            color: '#1f2937',
            lineHeight: 25,
          },
          text: {
            fontSize: 12,
            color: '#6b7280',
            padding: [5, 0],
          }
        },
      },
      emphasis: {
        label: {
          show: true,
          fontSize: '18',
          fontWeight: 'bold'
        }
      },
      labelLine: {
        show: false
      },
      data: [
        { value: employed.value, name: 'Employed', itemStyle: { color: '#10b981' } },
        { value: underemployed.value, name: 'Underemployed', itemStyle: { color: '#f59e0b' } },
        { value: unemployed.value, name: 'Unemployed', itemStyle: { color: '#ef4444' } },
      ]
    }
  ]
}));

// Trend summaries remain the same
const graduateTrends = computed(() => {
  // Logic remains the same
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
  return `The year "**${max.year}**" produced the highest number of graduates, indicating a peak in graduate output for your institution.`;
});

const alignmentTrends = computed(() => {
  // Logic remains the same
  const trends = {};
  graduates.value.forEach(g => {
    if (g.school_year_range) {
      if (!trends[g.school_year_range]) {
        trends[g.school_year_range] = { directlyAligned: 0, partiallyAligned: 0, misaligned: 0 };
      }
      if (g.alignment_category === 'Directly aligned') trends[g.school_year_range].directlyAligned += 1;
      else if (g.alignment_category === 'Partially aligned') trends[g.school_year_range].partiallyAligned += 1;
      else if (g.alignment_category === 'Misaligned') trends[g.school_year_range].misaligned += 1;
    }
  });
  return Object.entries(trends)
    .map(([year, data]) => ({ year, ...data }))
    .sort((a, b) => a.year.localeCompare(b.year));
});

const alignmentTrendsSummary = computed(() => {
  if (!alignmentTrends.value.length) return 'No alignment trend data available.';
  const best = alignmentTrends.value.reduce((a, b) => {
    const aTotal = a.directlyAligned + a.partiallyAligned + a.misaligned;
    const bTotal = b.directlyAligned + b.partiallyAligned + b.misaligned;
    const aRate = aTotal > 0 ? a.directlyAligned / aTotal : 0;
    const bRate = bTotal > 0 ? b.directlyAligned / bTotal : 0;
    return bRate > aRate ? b : a;
  }, { directlyAligned: 0, partiallyAligned: 0, misaligned: 0 });
  const total = best.directlyAligned + best.partiallyAligned + best.misaligned;
  const percent = total > 0 ? ((best.directlyAligned / total) * 100).toFixed(1) : 0;
  return `The year "**${best.year}**" had the highest direct alignment rate at **${percent}%**, showing the best match between graduate jobs and mapped career opportunities.`;
});

const outcomesByTerm = computed(() => {
  // Logic remains the same
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
  return `The term "**${best.term}**" produced the most employed graduates, suggesting a possible seasonal or cohort effect on employment outcomes.`;
});

const skillCounts = computed(() => {
  // Logic remains the same
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
    .slice(0, 10);
});

const skillCountsSummary = computed(() => {
  if (!skillCounts.value.length) return 'No graduate skills data available.';
  const top = skillCounts.value[0];
  return `The most common skill among graduates is "**${top.name}**", reflecting the key competencies developed by your institution.`;
});

const genderEmploymentSummary = computed(() => {
  // Logic remains the same
  const genders = ['Male', 'Female'];
  const counts = {};
  genders.forEach(gender => {
    counts[gender] = { 'Employed': 0, 'Underemployed': 0, 'Unemployed': 0 };
    graduates.value.filter(g => g.gender === gender).forEach(g => {
      if (g.employment_status) counts[gender][g.employment_status] = (counts[gender][g.employment_status] || 0) + 1;
    });
  });

  const moreEmployed = counts['Male']['Employed'] > counts['Female']['Employed'] ? 'male' : counts['Male']['Employed'] < counts['Female']['Employed'] ? 'female' : 'both male and female';
  const moreUnderemployed = counts['Male']['Underemployed'] > counts['Female']['Underemployed'] ? 'male' : counts['Male']['Underemployed'] < counts['Female']['Underemployed'] ? 'female' : 'both male and female';
  const moreUnemployed = counts['Male']['Unemployed'] > counts['Female']['Unemployed'] ? 'male' : counts['Male']['Unemployed'] < counts['Female']['Unemployed'] ? 'female' : 'both male and female';

  if (total.value === 0) return 'There are no graduates with employment data available for gender comparison.';

  return `Among graduates, **${moreEmployed}** graduates have the highest employment rate, while **${moreUnderemployed}** graduates experience more underemployment, and **${moreUnemployed}** graduates face higher unemployment. These patterns highlight how employment outcomes can differ by gender, suggesting the need for ongoing support to ensure equitable opportunities for all graduates.`;
});
</script>


<template>
  <AppLayout>
    <div class="max-w-full mx-auto py-6 px-4 sm:px-6 lg:px-8">

      <header class="mb-4">
        <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
          <Monitor class="w-8 h-8 text-cyan-600" />
          Graduate Analytics Report
        </h1>
        <p class="text-sm text-gray-500 mt-1">Review your graduate's employment, job titles, employment statuses,
          and their career alignment.</p>
      </header>

      <div class="bg-white rounded-xl shadow-2xl p-6 md:p-8">

        <div v-if="loading" class="flex justify-center items-center py-20">
          <svg class="animate-spin h-8 w-8 text-cyan-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
          </svg>
          <span class="ml-3 text-lg text-gray-500">Loading Report Data...</span>
        </div>

        <div v-else>

          <section class="mb-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800 flex items-center gap-2 border-b pb-2">
              <Users class="w-5 h-5 text-cyan-600" /> Key Graduate Metrics
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">

              <div
                class="bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-lg shadow-lg p-4 h-24 flex flex-col justify-between transition-transform duration-200 hover:scale-[1.01] hover:shadow-xl">
                <span class="text-xs font-medium text-cyan-100 uppercase">Total Grads</span>
                <span class="text-2xl font-extrabold text-white mt-auto">{{ total }}</span>
              </div>

              <div
                class="bg-white rounded-lg shadow-md border-l-4 border-green-500 p-4 h-24 flex flex-col justify-between transition-shadow hover:shadow-lg">
                <span class="text-xs font-medium text-gray-500 uppercase">Employed</span>
                <span class="text-2xl font-extrabold text-green-700 mt-auto">{{ employed }}</span>
              </div>
              <div
                class="bg-white rounded-lg shadow-md border-l-4 border-amber-500 p-4 h-24 flex flex-col justify-between transition-shadow hover:shadow-lg">
                <span class="text-xs font-medium text-gray-500 uppercase">Underemployed</span>
                <span class="text-2xl font-extrabold text-amber-700 mt-auto">{{ underemployed }}</span>
              </div>
              <div
                class="bg-white rounded-lg shadow-md border-l-4 border-red-500 p-4 h-24 flex flex-col justify-between transition-shadow hover:shadow-lg">
                <span class="text-xs font-medium text-gray-500 uppercase">Unemployed</span>
                <span class="text-2xl font-extrabold text-red-700 mt-auto">{{ unemployed }}</span>
              </div>

              <div
                class="bg-white rounded-lg shadow-md border-l-4 border-blue-500 p-4 h-24 flex flex-col justify-between transition-shadow hover:shadow-lg">
                <span class="text-xs font-medium text-gray-500 uppercase">Directly Aligned</span>
                <span class="text-2xl font-extrabold text-blue-700 mt-auto">{{ directlyAligned }}</span>
              </div>
              <div
                class="bg-white rounded-lg shadow-md border-l-4 border-orange-500 p-4 h-24 flex flex-col justify-between transition-shadow hover:shadow-lg">
                <span class="text-xs font-medium text-gray-500 uppercase">Partially Aligned</span>
                <span class="text-2xl font-extrabold text-orange-700 mt-auto">{{ partiallyAligned }}</span>
              </div>
              <div
                class="bg-white rounded-lg shadow-md border-l-4 border-pink-500 p-4 h-24 flex flex-col justify-between transition-shadow hover:shadow-lg">
                <span class="text-xs font-medium text-gray-500 uppercase">Misaligned</span>
                <span class="text-2xl font-extrabold text-pink-700 mt-auto">{{ misalignedCount }}</span>
              </div>
            </div>
          </section>

          <section class="mb-8 p-4 bg-gray-50 rounded-lg border border-gray-100">
            <h2 class="text-lg font-semibold mb-3 text-gray-700 flex items-center gap-2">
              <Zap class="w-4 h-4 text-cyan-600" /> Filter Data Set
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
              <input v-model="filters.name" type="text" placeholder="Search by name..."
                class="rounded-md border-gray-300 focus:ring-cyan-500 focus:border-cyan-500 h-9 text-sm p-2" />
              <select v-model="filters.gender"
                class="rounded-md border-gray-300 focus:ring-cyan-500 focus:border-cyan-500 h-9 text-sm">
                <option value="">All Genders</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
              <select v-model="filters.schoolYear"
                class="rounded-md border-gray-300 focus:ring-cyan-500 focus:border-cyan-500 h-9 text-sm">
                <option value="">All School Years</option>
                <option v-for="sy in uniqueSchoolYears" :key="sy.school_year_range" :value="sy.school_year_range">
                  {{ sy.school_year_range }}
                </option>
              </select>
              <select v-model="filters.degree"
                class="rounded-md border-gray-300 focus:ring-cyan-500 focus:border-cyan-500 h-9 text-sm">
                <option value="">All Degrees</option>
                <option v-for="deg in degrees" :key="deg.id" :value="deg.id">{{ deg.type }}</option>
              </select>
              <select v-model="filters.program"
                class="rounded-md border-gray-300 focus:ring-cyan-500 focus:border-cyan-500 h-9 text-sm">
                <option value="">All Programs</option>
                <option v-for="prog in programs" :key="prog.id" :value="prog.id">{{ prog.name }}</option>
              </select>
              <select v-model="filters.employmentStatus"
                class="rounded-md border-gray-300 focus:ring-cyan-500 focus:border-cyan-500 h-9 text-sm">
                <option value="">All Statuses</option>
                <option value="Employed">Employed</option>
                <option value="Underemployed">Underemployed</option>
                <option value="Unemployed">Unemployed</option>
              </select>

              <select v-model="filters.careerOpportunity"
                class="rounded-md border-gray-300 focus:ring-cyan-500 focus:border-cyan-500 h-9 text-sm">
                <option value="">All Career Opps</option>
                <option v-for="co in uniqueCareerOpportunities" :key="co.id" :value="co.title">{{ co.title }}</option>
              </select>
              <select v-model="filters.skill"
                class="rounded-md border-gray-300 focus:ring-cyan-500 focus:border-cyan-500 h-9 text-sm">
                <option value="">All Skills</option>
                <option v-for="sk in skills" :key="sk.id" :value="sk.name">{{ sk.name }}</option>
              </select>
              <select v-model="filters.internshipProgram"
                class="rounded-md border-gray-300 focus:ring-cyan-500 focus:border-cyan-500 h-9 text-sm">
                <option value="">All Internships</option>
                <option v-for="ip in internshipPrograms" :key="ip.id" :value="ip.id">{{ ip.title }}</option>
              </select>
              <select v-model="filters.term"
                class="rounded-md border-gray-300 focus:ring-cyan-500 focus:border-cyan-500 h-9 text-sm">
                <option value="">All Terms</option>
                <option v-for="term in uniqueTerms" :key="term" :value="term">
                  {{ term }}
                </option>
              </select>
              <select v-model="filters.alignment"
                class="rounded-md border-gray-300 focus:ring-cyan-500 focus:border-cyan-500 h-9 text-sm">
                <option value="">All Alignment</option>
                <option value="Directly aligned">Directly aligned</option>
                <option value="Partially aligned">Partially aligned</option>
                <option value="Misaligned">Misaligned</option>
              </select>
            </div>
          </section>

          <section class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">

            <div class="lg:col-span-3">
              <h2 class="text-xl font-bold mb-4 text-gray-800 flex items-center gap-2 border-b pb-2">
                <Users class="w-5 h-5 text-cyan-600" /> Detailed Graduate Records
              </h2>

              <div class="bg-white rounded-lg shadow-lg overflow-hidden border border-gray-100 flex flex-col">

                <div class="overflow-x-auto max-h-[550px] overflow-y-auto">
                  <table class="min-w-full divide-y divide-gray-200 text-sm text-left text-gray-700">
                    <thead class="bg-gray-50 sticky top-0 z-10">
                      <tr>
                        <th
                          class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                          Name</th>
                        <th
                          class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                          Gender</th>
                        <th
                          class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                          Year</th>
                        <th
                          class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                          Program</th>
                        <th
                          class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                          Status</th>
                        <th
                          class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                          Job Title</th>
                        <th
                          class="px-3 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                          Alignment</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                      <tr v-if="paginatedGraduates.length === 0">
                        <td colspan="7" class="text-center text-gray-400 py-6">No graduates found matching the current
                          filters.</td>
                      </tr>
                      <tr v-for="g in paginatedGraduates" :key="g.id"
                        class="text-sm hover:bg-cyan-50/50 transition-colors">
                        <td class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap">{{ g.first_name }} {{
                          g.last_name }}</td>
                        <td class="px-3 py-2 text-gray-500 whitespace-nowrap">{{ g.gender || 'N/A' }}</td>
                        <td class="px-3 py-2 text-gray-500 whitespace-nowrap">{{ g.school_year_range || 'N/A' }}</td>
                        <td class="px-3 py-2 text-gray-700">
                          {{ g.program || 'N/A' }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                          <span :class="{
                            'bg-green-100 text-green-800': g.employment_status === 'Employed',
                            'bg-amber-100 text-amber-800': g.employment_status === 'Underemployed',
                            'bg-red-100 text-red-800': g.employment_status === 'Unemployed'
                          }" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold">
                            {{ g.employment_status }}
                          </span>
                        </td>
                        <td class="px-3 py-2 text-gray-700 whitespace-nowrap">{{ g.current_job_title || 'N/A' }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">
                          <span
                            v-if="g.employment_status === 'Unemployed' || (g.current_job_title && g.current_job_title.toLowerCase() === 'n/a')"
                            class="text-gray-500 text-xs font-medium">
                            N/A
                          </span>
                          <div v-else class="flex flex-col text-xs">
                            <span :class="{
                              'text-blue-600 font-semibold': g.alignment_category === 'Directly aligned',
                              'text-orange-600 font-semibold': g.alignment_category === 'Partially aligned',
                              'text-pink-600 font-semibold': g.alignment_category === 'Misaligned'
                            }">
                              {{ g.alignment_score !== null ? g.alignment_score + '%' : '' }}
                            </span>
                            <small :class="{
                              'text-blue-500': g.alignment_category === 'Directly aligned',
                              'text-orange-500': g.alignment_category === 'Partially aligned',
                              'text-pink-500': g.alignment_category === 'Misaligned'
                            }">
                              {{ g.alignment_category || 'N/A' }}
                            </small>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <div v-if="totalPages > 1" class="p-4 flex items-center justify-between border-t border-gray-200">
                  <span class="text-sm text-gray-600">Showing page <span class="font-semibold">{{ page }}</span> of
                    <span class="font-semibold">{{ totalPages }}</span></span>
                  <div class="inline-flex rounded-md shadow-sm">
                    <button
                      class="px-3 py-1 rounded-l-lg text-sm text-gray-700 bg-gray-100 border border-gray-300 hover:bg-gray-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                      :disabled="page === 1" @click="page--">
                      &larr; Prev
                    </button>
                    <button
                      class="px-3 py-1 rounded-r-lg text-sm text-gray-700 bg-gray-100 border border-gray-300 hover:bg-gray-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                      :disabled="page === totalPages" @click="page++">
                      Next &rarr;
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="lg:col-span-1">
              <div class="bg-gray-50 rounded-lg shadow-md p-5 flex flex-col h-full transition-shadow hover:shadow-lg">
                <h3 class="text-lg font-semibold mb-3 text-gray-700 flex items-center gap-2">
                  <TrendingUp class="w-4 h-4 text-cyan-600" /> Overall Employment Rate
                </h3>
                <div class="flex-grow flex items-center justify-center pt-2">
                  <VueECharts :option="employmentPieOption" style="height: 300px; width: 100%; max-width: 350px;" />
                </div>
                <div class="mt-auto pt-4 text-center border-t border-gray-200">
                  <div class="text-3xl font-extrabold text-cyan-700">
                    {{ total ? ((employed + underemployed) / total * 100).toFixed(1) : 0 }}%
                  </div>
                  <div class="text-sm text-gray-500 font-medium">Total Employment Rate</div>
                </div>
              </div>
            </div>
          </section>

          <section class="space-y-6 mb-8">
            <h2 class="text-xl font-bold mb-4 text-gray-800 flex items-center gap-2 border-b pb-2">
              <TrendingUp class="w-5 h-5 text-cyan-600" /> School Year & Term Trends
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <div class="bg-white rounded-lg shadow-md p-5 transition-shadow hover:shadow-lg">
                <h3 class="text-lg font-semibold mb-3 text-gray-700">Graduate Volume by Year</h3>
                <VueECharts v-if="graduateTrends.length" :option="{
                  tooltip: { trigger: 'axis' },
                  grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                  xAxis: { type: 'category', data: graduateTrends.map(t => t.year) },
                  yAxis: { type: 'value', name: 'Count' },
                  series: [{ type: 'line', data: graduateTrends.map(t => t.count), itemStyle: { color: '#06b6d4' }, smooth: true, areaStyle: {} }]
                }" autoresize style="width: 100%; height: 250px;" />
                <div v-else class="text-gray-400 text-center py-8">No trend data available.</div>
                <div class="mt-4 p-3 bg-cyan-50 rounded-md text-gray-700 text-xs border-l-4 border-cyan-500">
                  <p class="font-semibold">Summary:</p>
                  <p class="mt-1" v-html="graduateTrendsSummary"></p>
                </div>
              </div>

              <div class="bg-white rounded-lg shadow-md p-5 transition-shadow hover:shadow-lg">
                <h3 class="text-lg font-semibold mb-3 text-gray-700">Alignment Trends by Year</h3>
                <VueECharts v-if="alignmentTrends.length" :option="{
                  tooltip: { trigger: 'axis' },
                  legend: { data: ['Directly aligned', 'Partially aligned', 'Misaligned'] },
                  grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                  xAxis: { type: 'category', data: alignmentTrends.map(t => t.year) },
                  yAxis: { type: 'value', name: 'Count' },
                  series: [
                    { name: 'Directly aligned', type: 'bar', stack: 'total', data: alignmentTrends.map(t => t.directlyAligned), itemStyle: { color: '#2563eb' } },
                    { name: 'Partially aligned', type: 'bar', stack: 'total', data: alignmentTrends.map(t => t.partiallyAligned), itemStyle: { color: '#f59e0b' } },
                    { name: 'Misaligned', type: 'bar', stack: 'total', data: alignmentTrends.map(t => t.misaligned), itemStyle: { color: '#ec4899' } }
                  ]
                }" autoresize style="width: 100%; height: 250px;" />
                <div v-else class="text-gray-400 text-center py-8">No alignment trend data available.</div>
                <div class="mt-4 p-3 bg-cyan-50 rounded-md text-gray-700 text-xs border-l-4 border-cyan-500">
                  <p class="font-semibold">Summary:</p>
                  <p class="mt-1" v-html="alignmentTrendsSummary"></p>
                </div>
              </div>

              <div class="bg-white rounded-lg shadow-md p-5 transition-shadow hover:shadow-lg">
                <h3 class="text-lg font-semibold mb-3 text-gray-700 flex items-center gap-2">
                  <Briefcase class="w-4 h-4 text-cyan-600" /> Outcomes by Term/Cohort
                </h3>
                <VueECharts v-if="outcomesByTerm.length" :option="{
                  tooltip: { trigger: 'axis' },
                  legend: { data: ['Employed', 'Underemployed', 'Unemployed'] },
                  grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                  xAxis: { type: 'category', data: outcomesByTerm.map(t => t.term) },
                  yAxis: { type: 'value', name: 'Count' },
                  series: [
                    { name: 'Employed', type: 'bar', stack: 'total', data: outcomesByTerm.map(t => t.employed), itemStyle: { color: '#10b981' } },
                    { name: 'Underemployed', type: 'bar', stack: 'total', data: outcomesByTerm.map(t => t.underemployed), itemStyle: { color: '#f59e0b' } },
                    { name: 'Unemployed', type: 'bar', stack: 'total', data: outcomesByTerm.map(t => t.unemployed), itemStyle: { color: '#ef4444' } }
                  ]
                }" autoresize style="width: 100%; height: 250px;" />
                <div v-else class="text-gray-400 text-center py-8">No outcomes by term data available.</div>
                <div class="mt-4 p-3 bg-cyan-50 rounded-md text-gray-700 text-xs border-l-4 border-cyan-500">
                  <p class="font-semibold">Summary:</p>
                  <p class="mt-1" v-html="outcomesByTermSummary"></p>
                </div>
              </div>
            </div>
          </section>

          <section class="space-y-6 mb-8">
            <h2 class="text-xl font-bold mb-4 text-gray-800 flex items-center gap-2 border-b pb-2">
              <BarChart2 class="w-5 h-5 text-cyan-600" /> Skills & Competencies
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

              <div class="bg-white rounded-lg shadow-md p-5 transition-shadow hover:shadow-lg">
                <h3 class="text-lg font-semibold mb-3 text-gray-700 flex items-center gap-2">
                  <BarChart2 class="w-4 h-4 text-cyan-600" /> Employment Status by Gender
                </h3>
                <VueECharts :option="genderStackedBarOption" style="height: 300px; width: 100%;" />
                <div class="mt-4 p-3 bg-gray-50 rounded-md text-gray-700 text-xs border-l-4 border-cyan-500">
                  <p class="font-semibold">Summary Interpretation:</p>
                  <p class="mt-1" v-html="genderEmploymentSummary"></p>
                </div>
              </div>

              <div class="bg-white rounded-lg shadow-md p-5 transition-shadow hover:shadow-lg">
                <h3 class="text-lg font-semibold mb-3 text-gray-700 flex items-center gap-2">
                  <Code class="w-4 h-4 text-cyan-600" /> Top Graduate Skills Distribution
                </h3>
                <VueECharts v-if="skillCounts.length" :option="{
                  tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
                  grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                  xAxis: { type: 'value' },
                  yAxis: {
                    type: 'category',
                    data: skillCounts.map(s => s.name.length > 20 ? s.name.substring(0, 20) + '...' : s.name).reverse(), // Reverse for top-down display
                    axisLabel: { interval: 0, rotate: 0 }
                  },
                  series: [{
                    type: 'bar',
                    data: skillCounts.map(s => s.count).reverse(), // Reverse data to match reversed y-axis labels
                    itemStyle: { color: '#4f46e5' }
                  }]
                }" autoresize style="width: 100%; height: 300px;" />
                <div v-else class="text-gray-400 text-center py-8">No skills data available.</div>
                <div class="mt-6 p-4 bg-indigo-50 border-l-4 border-indigo-400 rounded-md text-gray-800 text-sm">
                  <span class="font-semibold text-indigo-700">Summary Interpretation:</span>
                  <p class="mt-1">{{ skillCountsSummary }}</p>
                </div>
              </div>
            </div>
          </section>

        </div>
      </div>
    </div>
  </AppLayout>
</template>