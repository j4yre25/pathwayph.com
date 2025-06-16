<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto py-10 px-4">
      <h1 class="text-3xl font-bold mb-6 text-gray-800 flex items-center gap-2">
        <GraduationCap class="w-8 h-8 text-cyan-500" />
        Graduate Report
      </h1>

      <!-- Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <span class="text-2xl font-bold text-gray-800">{{ filteredGraduates.length }}</span>
          <span class="text-gray-500">Total Graduates</span>
        </div>
        <div class="bg-green-100 rounded-xl shadow p-6 flex flex-col items-center">
          <span class="text-2xl font-bold text-green-700">{{ employedCount }}</span>
          <span class="text-gray-600">Employed</span>
        </div>
        <div class="bg-yellow-100 rounded-xl shadow p-6 flex flex-col items-center">
          <span class="text-2xl font-bold text-yellow-700">{{ underemployedCount }}</span>
          <span class="text-gray-600">Underemployed</span>
        </div>
        <div class="bg-red-100 rounded-xl shadow p-6 flex flex-col items-center">
          <span class="text-2xl font-bold text-red-700">{{ unemployedCount }}</span>
          <span class="text-gray-600">Unemployed</span>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl shadow p-6 mb-8 grid grid-cols-1 md:grid-cols-3 gap-4">
        <input
          v-model="filters.name"
          type="text"
          placeholder="Search by name"
          class="rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500"
        />
        <select v-model="filters.gender" class="rounded-lg border-gray-300">
          <option value="">All Genders</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
        </select>
        <select v-model="filters.schoolYear" class="rounded-lg border-gray-300">
          <option value="">All School Years</option>
          <option v-for="sy in schoolYears" :key="sy.id" :value="sy.id">{{ sy.school_year_range }}</option>
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
          <option v-for="co in careerOpportunities" :key="co.id" :value="co.title">{{ co.title }}</option>
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
          <option v-for="t in terms" :key="t" :value="t">{{ t }}</option>
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
            </tr>
            <tr v-if="paginatedGraduates.length === 0">
              <td colspan="8" class="text-center text-gray-400 py-6">No graduates found.</td>
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
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { GraduationCap } from 'lucide-vue-next';
import VueECharts from 'vue-echarts';

const props = defineProps({
  graduates: Array,
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
  gender: '', // <-- add this
  schoolYear: '',
  degree: '',
  program: '',
  employmentStatus: '',
  careerOpportunity: '',
  skill: '',
  internshipProgram: '',
  term: '',
});

const filteredGraduates = computed(() => {
  return props.graduates.filter(g => {
    const name = `${g.first_name} ${g.middle_name ?? ''} ${g.last_name}`.toLowerCase();
    if (filters.value.name && !name.includes(filters.value.name.toLowerCase())) return false;
    if (filters.value.gender && g.gender !== filters.value.gender) return false; // <-- add this
    if (filters.value.schoolYear && g.school_year_id != filters.value.schoolYear) return false;
    if (filters.value.degree && g.degree_id != filters.value.degree) return false;
    if (filters.value.program && g.program_id != filters.value.program) return false;
    if (filters.value.employmentStatus && g.employment_status !== filters.value.employmentStatus) return false;
    if (filters.value.careerOpportunity) {
      const hasCareer = g.careerGoals?.career_opportunity?.title === filters.value.careerOpportunity
        || g.current_job_title === filters.value.careerOpportunity;
      if (!hasCareer) return false;
    }
    if (filters.value.skill) {
      const skillNames = (g.graduateSkills || []).map(gs => gs.skill?.name);
      if (!skillNames.includes(filters.value.skill)) return false;
    }
    if (filters.value.internshipProgram) {
      const hasInternship = (g.internshipPrograms || []).some(ip => ip.id == filters.value.internshipProgram);
      if (!hasInternship) return false;
    }
    if (filters.value.term && g.term != filters.value.term) return false;
    return true;
  });
});

const page = ref(1);
const perPage = 20;
const totalPages = computed(() => Math.ceil(filteredGraduates.value.length / perPage));
const paginatedGraduates = computed(() => {
  const start = (page.value - 1) * perPage;
  return filteredGraduates.value.slice(start, start + perPage);
});
watch(filteredGraduates, () => { page.value = 1; });

const employedCount = computed(() => filteredGraduates.value.filter(g => g.employment_status === 'Employed').length);
const underemployedCount = computed(() => filteredGraduates.value.filter(g => g.employment_status === 'Underemployed').length);
const unemployedCount = computed(() => filteredGraduates.value.filter(g => g.employment_status === 'Unemployed').length);

// --- Stacked Bar Chart: Gender vs Employment Status ---
const genderEmploymentData = computed(() => {
  const genders = ['Male', 'Female']
  const statuses = ['Employed', 'Underemployed', 'Unemployed']
  // Count graduates by gender and status
  const counts = genders.map(gender =>
    statuses.map(status =>
      props.graduates.filter(g =>
        g.gender === gender && g.employment_status === status
      ).length
    )
  )
  return { genders, statuses, counts }
})

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
}))

// --- Pie/Doughnut Chart: Employment Rate ---
const employed = computed(() =>
  props.graduates.filter(g => g.employment_status === 'Employed').length
)
const underemployed = computed(() =>
  props.graduates.filter(g => g.employment_status === 'Underemployed').length
)
const unemployed = computed(() =>
  props.graduates.filter(g => g.employment_status === 'Unemployed').length
)
const total = computed(() => employed.value + underemployed.value + unemployed.value)

const employmentPieOption = computed(() => ({
  tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
  legend: { orient: 'vertical', left: 'left' },
  series: [
    {
      name: 'Employment Status',
      type: 'pie',
      radius: ['60%', '80%'], // doughnut style
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
}))
</script>