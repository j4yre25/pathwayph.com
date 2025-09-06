<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto py-10 px-4">
      <h1 class="text-3xl font-bold mb-6 text-gray-800 flex items-center gap-2">
        <GraduationCap class="w-8 h-8 text-cyan-500" />
        Graduate Report
      </h1>

      <!-- Loading Spinner -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <svg class="animate-spin h-10 w-10 text-cyan-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
          <input v-model="filters.name" type="text" placeholder="Search by name" class="rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
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
            <option value="Aligned">Aligned</option>
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
                  <span
                    :class="{
                      'text-blue-700 font-semibold': g.alignment === 'Aligned',
                      'text-pink-700 font-semibold': g.alignment === 'Misaligned'
                    }"
                  >{{ g.alignment }}</span>
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
          <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300" :disabled="page === 1" @click="page--">Prev</button>
          <span class="px-3 py-1">{{ page }} / {{ totalPages }}</span>
          <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300" :disabled="page === totalPages" @click="page++">Next</button>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-10">
          <!-- Stacked Bar Chart: Gender vs Employment Status -->
          <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-700">Employment Status by Gender</h3>
            <VueECharts :option="genderStackedBarOption" style="height: 400px; width: 100%;" />
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
      </div>
    </div>
  </AppLayout>
</template>

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
</script>