<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Briefcase } from 'lucide-vue-next';
import axios from 'axios';
import '@vue-flow/core/dist/style.css';
import { VueFlow, useVueFlow } from '@vue-flow/core';


const loading = ref(true);

const careerOpportunities = ref([]);
const graduates = ref([]);
const schoolYears = ref([]);
const degrees = ref([]);
const programs = ref([]);

// Fetch data on mount
const fetchData = async () => {
  loading.value = true;
  try {
    const res = await axios.get(route('institutions.reports.career.data'));
    careerOpportunities.value = res.data.careerOpportunities ?? [];
    graduates.value = res.data.graduates ?? [];
    schoolYears.value = res.data.schoolYears ?? [];
    degrees.value = res.data.degrees ?? [];
    programs.value = res.data.programs ?? [];
    console.log('Career Opportunities:', careerOpportunities.value);
  } finally {
    loading.value = false;
  }
};
onMounted(fetchData);

// Pagination for career opportunities
const careerPage = ref(1);
const careerPerPage = 10;
const careerTotalPages = computed(() => Math.ceil(careerOpportunities.value.length / careerPerPage));
const paginatedCareerOpportunities = computed(() => {
  const start = (careerPage.value - 1) * careerPerPage;
  return careerOpportunities.value.slice(start, start + careerPerPage);
});
watch(careerPage, () => {
  if (careerPage.value > careerTotalPages.value) careerPage.value = careerTotalPages.value;
  if (careerPage.value < 1) careerPage.value = 1;
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
  return graduates.value.filter(g => {
    // School year range filter
    let inRange = true;
    if (startSchoolYear.value && endSchoolYear.value) {
      inRange = g.school_year_range >= startSchoolYear.value && g.school_year_range <= endSchoolYear.value;
    } else if (startSchoolYear.value) {
      inRange = g.school_year_range >= startSchoolYear.value;
    } else if (endSchoolYear.value) {
      inRange = g.school_year_range <= endSchoolYear.value;
    }
    if (!inRange) return false;
    if (selectedDegree.value && g.degree_id != selectedDegree.value) return false;
    if (selectedProgram.value && g.program_id != selectedProgram.value) return false;
    if (selectedCareerOpportunity.value && (!g.career_opportunity || g.career_opportunity !== selectedCareerOpportunity.value)) return false;
    return true;
  });
});
// Start/End School Year, Degree, Program, Career Opportunity filters
const startSchoolYear = ref('');
const endSchoolYear = ref('');
const selectedDegree = ref('');
const selectedProgram = ref('');
const selectedCareerOpportunity = ref('');

// Unique, sorted school year labels
const schoolYearLabels = computed(() => {
  return [...new Set(schoolYears.value.map(sy => sy.school_year_range))].sort();
});

// Unique degrees and programs for dropdowns
const uniqueDegrees = computed(() => {
  const seen = new Set();
  return degrees.value.filter(deg => {
    if (!deg.id || seen.has(deg.id)) return false;
    seen.add(deg.id);
    return true;
  });
});
const uniquePrograms = computed(() => {
  const seen = new Set();
  return programs.value.filter(prog => {
    if (!prog.id || seen.has(prog.id)) return false;
    seen.add(prog.id);
    return true;
  });
});

// Unique career opportunities for dropdown
const uniqueCareerOpportunities = computed(() => {
  const seen = new Set();
  return careerOpportunities.value.filter(co => {
    if (!co.career_opportunity || seen.has(co.career_opportunity)) return false;
    seen.add(co.career_opportunity);
    return true;
  });
});

// For the flow filter
const selectedFlowProgram = ref('');
const flowPrograms = computed(() => {
  // Unique program-degree pairs from careerOpportunities
  const seen = new Set();
  return careerOpportunities.value
    .filter(co => {
      const key = `${co.program}||${co.degree}`;
      if (!co.program || !co.degree || seen.has(key)) return false;
      seen.add(key);
      return true;
    })
    .map(co => ({
      program: co.program,
      degree: co.degree,
      label: `${co.degree} - ${co.program}`,
    }));
});
// Top Career Opportunities by Graduate Count
const topCareerOpportunities = computed(() => {
  // Count graduates per career_opportunity
  const counts = {};
  graduates.value.forEach(g => {
    if (g.career_opportunity) {
      counts[g.career_opportunity] = (counts[g.career_opportunity] || 0) + 1;
    }
  });
  // Convert to array and sort
  return Object.entries(counts)
    .map(([title, count]) => ({ title, count }))
    .sort((a, b) => b.count - a.count)
    .slice(0, 10); // Top 10
});

const topCareerOpportunitiesSummary = computed(() => {
  if (!topCareerOpportunities.value.length) return 'No graduates have mapped career opportunities yet.';
  const top = topCareerOpportunities.value[0];
  return `The most pursued career opportunity among graduates is "${top.title}" with ${top.count} graduates. This highlights the most popular career path mapped by your institution.`;
});
// Demand vs. Supply: Graduates per program vs. mapped career opportunities
const demandSupply = computed(() => {
  // { [program]: { graduates: count, opportunities: Set } }
  const map = {};
  graduates.value.forEach(g => {
    if (!g.program) return;
    if (!map[g.program]) map[g.program] = { graduates: 0, opportunities: new Set() };
    map[g.program].graduates += 1;
    if (g.career_opportunity) map[g.program].opportunities.add(g.career_opportunity);
  });
  return Object.entries(map).map(([program, data]) => ({
    program,
    graduates: data.graduates,
    opportunities: data.opportunities.size,
  }));
});

const demandSupplySummary = computed(() => {
  if (!demandSupply.value.length) return 'No demand vs. supply data available.';
  const max = demandSupply.value.reduce((a, b) => (a.graduates > b.graduates ? a : b), { graduates: 0 });
  return `The program "${max.program}" has the highest number of graduates, which may indicate higher demand for career opportunities in this field.`;
});
// Graduates whose current job is not in mapped career opportunities for their program
const unmappedGraduates = computed(() => {
  // Build a map of program -> Set of mapped career opportunities
  const programToOpportunities = {};
  careerOpportunities.value.forEach(co => {
    if (!co.program) return;
    if (!programToOpportunities[co.program]) programToOpportunities[co.program] = new Set();
    if (co.career_opportunity) programToOpportunities[co.program].add(co.career_opportunity.toLowerCase());
  });
  // Find graduates whose current job is not in mapped opportunities
  return graduates.value.filter(g => {
    if (!g.program || !g.current_job_title) return false;
    const mapped = programToOpportunities[g.program];
    if (!mapped || !mapped.size) return true;
    return !mapped.has(g.current_job_title.toLowerCase());
  });
});

const unmappedGraduatesSummary = computed(() => {
  if (!unmappedGraduates.value.length) return 'All graduates have jobs that match mapped career opportunities.';
  return `There are ${unmappedGraduates.value.length} graduates whose current jobs are not mapped as career opportunities for their program. This suggests potential new pathways or gaps in your current mapping.`;
});
// Career Opportunity Trends Over Time
const careerTrends = computed(() => {
  // { [school_year_range]: Set of career_opportunity }
  const trends = {};
  graduates.value.forEach(g => {
    if (g.school_year_range && g.career_opportunity) {
      if (!trends[g.school_year_range]) trends[g.school_year_range] = new Set();
      trends[g.school_year_range].add(g.career_opportunity);
    }
  });
  // Convert to array for charting
  return Object.entries(trends)
    .map(([year, set]) => ({ year, count: set.size }))
    .sort((a, b) => a.year.localeCompare(b.year));
});

const careerTrendsSummary = computed(() => {
  if (!careerTrends.value.length) return 'No trend data available.';
  const max = careerTrends.value.reduce((a, b) => (a.count > b.count ? a : b), { count: 0 });
  return `The year "${max.year}" saw the highest diversity of career opportunities mapped by graduates, indicating a peak in career pathway variety.`;
});

// Prepare nodes and edges for Vue Flow
const flowNodes = computed(() => {
  if (!selectedFlowProgram.value) return [];
  // Get all career opportunities for the selected program-degree
  const [degree, program] = selectedFlowProgram.value.split('||');
  const filtered = careerOpportunities.value.filter(
    co => co.program === program && co.degree === degree
  );
  // Root node: Program-Degree
  const rootId = `root-${degree}-${program}`;
  const nodes = [
    {
      id: rootId,
      label: `${degree} - ${program}`,
      position: { x: 250, y: 50 },
      type: 'input',
      style: { background: '#06b6d4', color: '#fff', fontWeight: 'bold' },
    },
  ];
  // Career opportunity nodes
  filtered.forEach((co, idx) => {
    nodes.push({
      id: `co-${co.id}`,
      label: co.career_opportunity,
      position: { x: 100 + idx * 200, y: 200 },
      type: 'output',
      style: { background: '#f1f5f9', color: '#0f172a' },
    });
  });
  return nodes;
});

const flowEdges = computed(() => {
  if (!selectedFlowProgram.value) return [];
  const [degree, program] = selectedFlowProgram.value.split('||');
  const filtered = careerOpportunities.value.filter(
    co => co.program === program && co.degree === degree
  );
  const rootId = `root-${degree}-${program}`;
  return filtered.map(co => ({
    id: `e-${rootId}-${co.id}`,
    source: rootId,
    target: `co-${co.id}`,
    type: 'default',
  }));
});

// Summary interpretation
const flowSummary = computed(() => {
  if (!selectedFlowProgram.value) return 'Select a program-degree to view its career flow.';
  const [degree, program] = selectedFlowProgram.value.split('||');
  const count = flowNodes.value.length - 1;
  if (count === 0) return `No career opportunities mapped for ${degree} - ${program}.`;
  return `This flowchart visualizes the career pathways available for graduates of "${degree} - ${program}". Each branch represents a career opportunity mapped by the institution, helping students and stakeholders understand the possible directions after completing this program.`;
});

// Pagination for graduates
const graduatePage = ref(1);
const graduatePerPage = 10;
const graduateTotalPages = computed(() => Math.ceil(filteredGraduates.value.length / graduatePerPage));
const paginatedGraduates = computed(() => {
  const start = (graduatePage.value - 1) * graduatePerPage;
  return filteredGraduates.value.slice(start, start + graduatePerPage);
});
watch(filteredGraduates, () => { graduatePage.value = 1; });
</script>

<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto py-10 px-4">
      <h1 class="text-3xl font-bold mb-6 text-gray-800 flex items-center gap-2">
        <Briefcase class="w-8 h-8 text-cyan-500" />
        Career Opportunity Report
      </h1>
      <div v-if="loading" class="flex justify-center items-center py-20">
        <svg class="animate-spin h-10 w-10 text-cyan-500" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
      </div>
      <div v-else>
        <!-- Card for total career opportunity entries -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
            <span class="text-2xl font-bold text-gray-800">{{ careerOpportunities.length }}</span>
            <span class="text-gray-500">Total Career Opportunity Entries</span>
          </div>
        </div>

        <!-- Table 1: List of Career Opportunities (paginated by 10) -->
        <div class="mb-10">
          <h2 class="text-xl font-semibold mb-4">All Career Opportunities</h2>
          <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Degree</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Program</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Career Opportunity</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="co in paginatedCareerOpportunities" :key="co.id">
                  <td class="px-4 py-2">{{ co.degree }}</td>
                  <td class="px-4 py-2">{{ co.program }}</td>
                  <td class="px-4 py-2">{{ co.career_opportunity }}</td>
                </tr>
                <tr v-if="paginatedCareerOpportunities.length === 0">
                  <td colspan="3" class="text-center text-gray-400 py-6">No career opportunities found.</td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- Pagination for career opportunities -->
          <div class="mt-4 flex justify-center gap-2">
            <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300" :disabled="careerPage === 1"
              @click="careerPage--">Prev</button>
            <span class="px-3 py-1">{{ careerPage }} / {{ careerTotalPages }}</span>
            <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300" :disabled="careerPage === careerTotalPages"
              @click="careerPage++">Next</button>
          </div>
        </div>

        <!-- Filters for graduates table -->
        <div class="bg-white rounded-xl shadow p-6 mb-4 grid grid-cols-1 md:grid-cols-1 gap-4">
          <!-- Main Filters (ProgramsReport + Career Opportunity) -->
          <div class="flex flex-wrap gap-4 mb-8 mt-4">
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Start School Year</label>
              <select v-model="startSchoolYear" class="rounded border-gray-300">
                <option value="">All</option>
                <option v-for="sy in schoolYearLabels" :key="sy" :value="sy">{{ sy }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">End School Year</label>
              <select v-model="endSchoolYear" class="rounded border-gray-300">
                <option value="">All</option>
                <option v-for="sy in schoolYearLabels" :key="sy" :value="sy">{{ sy }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Degree</label>
              <select v-model="selectedDegree" class="rounded border-gray-300">
                <option value="">All Degrees</option>
                <option v-for="deg in uniqueDegrees" :key="deg.id" :value="deg.id">{{ deg.type }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Program</label>
              <select v-model="selectedProgram" class="rounded border-gray-300">
                <option value="">All Programs</option>
                <option v-for="prog in uniquePrograms" :key="prog.id" :value="prog.id">{{ prog.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Career Opportunity</label>
              <select v-model="selectedCareerOpportunity" class="rounded border-gray-300">
                <option value="">All Career Opportunities</option>
                <option v-for="co in uniqueCareerOpportunities" :key="co.career_opportunity"
                  :value="co.career_opportunity">
                  {{ co.career_opportunity }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Table 2: List of Graduates -->
        <div class="bg-white rounded-xl shadow overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Degree</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Program</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employment Status</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Job Title</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Career Opportunity</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="g in paginatedGraduates" :key="g.id">
                <td class="px-4 py-2">{{ g.first_name }} {{ g.middle_name }} {{ g.last_name }}</td>
                <td class="px-4 py-2">{{ g.degree || 'N/A' }}</td>
                <td class="px-4 py-2">{{ g.program || 'N/A' }}</td>
                <td class="px-4 py-2">{{ g.employment_status }}</td>
                <td class="px-4 py-2">{{ g.current_job_title }}</td>
                <td class="px-4 py-2">{{ g.current_job_title }}</td>
              </tr>
              <tr v-if="paginatedGraduates.length === 0">
                <td colspan="8" class="text-center text-gray-400 py-6">No graduates found.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Career Flow Visualization -->
        <div class="my-10">
          <h2 class="text-xl font-semibold mb-4 text-cyan-700">Career Flow Visualization</h2>
          <div class="mb-4">
            <label class="block text-xs font-semibold text-gray-600 mb-1">Select Program-Degree</label>
            <select v-model="selectedFlowProgram" class="rounded border-gray-300">
              <option value="">-- Select --</option>
              <option v-for="prog in flowPrograms" :key="prog.label" :value="`${prog.degree}||${prog.program}`">
                {{ prog.label }}
              </option>
            </select>
          </div>
          <div v-if="selectedFlowProgram && flowNodes.length > 1" style="height: 350px; background: #f8fafc;"
            class="rounded-lg shadow mb-4">
            <VueFlow :nodes="flowNodes" :edges="flowEdges" fit-view-on-init />
          </div>
          <div v-else class="text-gray-400 text-center py-8">No flow to display. Select a program-degree above.</div>
          <div class="mt-4 p-4 bg-cyan-50 rounded-lg text-cyan-900 text-base font-medium">
            <span class="font-semibold">Summary Interpretation:</span>
            <br>
            {{ flowSummary }}
          </div>
        </div>

        <!-- Top Career Opportunities by Graduate Count -->
        <div class="my-10">
          <h2 class="text-xl font-semibold mb-4 text-cyan-700">Top Career Opportunities by Graduate Count</h2>
          <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Career Opportunity</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Graduate Count</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="row in topCareerOpportunities" :key="row.title">
                  <td class="px-4 py-2">{{ row.title }}</td>
                  <td class="px-4 py-2">{{ row.count }}</td>
                </tr>
                <tr v-if="!topCareerOpportunities.length">
                  <td colspan="2" class="text-center text-gray-400 py-6">No data available.</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="mt-4 p-4 bg-cyan-50 rounded-lg text-cyan-900 text-base font-medium">
            <span class="font-semibold">Summary Interpretation:</span>
            <br>
            {{ topCareerOpportunitiesSummary }}
          </div>
        </div>
        <!-- Career Opportunity Trends Over Time -->
        <div class="my-10">
          <h2 class="text-xl font-semibold mb-4 text-cyan-700">Career Opportunity Trends Over Time</h2>
          <VChart v-if="careerTrends.length" :option="{
            xAxis: { type: 'category', data: careerTrends.map(t => t.year) },
            yAxis: { type: 'value', name: 'Unique Career Opportunities' },
            series: [{ type: 'bar', data: careerTrends.map(t => t.count), itemStyle: { color: '#06b6d4' } }]
          }" autoresize style="width: 100%; height: 300px;" />
          <div v-else class="text-gray-400 text-center py-8">No trend data available.</div>
          <div class="mt-4 p-4 bg-cyan-50 rounded-lg text-cyan-900 text-base font-medium">
            <span class="font-semibold">Summary Interpretation:</span>
            <br>
            {{ careerTrendsSummary }}
          </div>
        </div>

        <!-- Career Opportunity Demand vs. Supply -->
        <div class="my-10">
          <h2 class="text-xl font-semibold mb-4 text-cyan-700">Career Opportunity Demand vs. Supply</h2>
          <VChart v-if="demandSupply.length" :option="{
            tooltip: {},
            legend: { data: ['Graduates', 'Career Opportunities'] },
            xAxis: { type: 'category', data: demandSupply.map(d => d.program) },
            yAxis: { type: 'value' },
            series: [
              { name: 'Graduates', type: 'bar', data: demandSupply.map(d => d.graduates), itemStyle: { color: '#06b6d4' } },
              { name: 'Career Opportunities', type: 'bar', data: demandSupply.map(d => d.opportunities), itemStyle: { color: '#f59e42' } }
            ]
          }" autoresize style="width: 100%; height: 300px;" />
          <div v-else class="text-gray-400 text-center py-8">No demand vs. supply data available.</div>
          <div class="mt-4 p-4 bg-cyan-50 rounded-lg text-cyan-900 text-base font-medium">
            <span class="font-semibold">Summary Interpretation:</span>
            <br>
            {{ demandSupplySummary }}
          </div>
        </div>


        <!-- Pagination for graduates -->
        <div class="mt-6 flex justify-center gap-2">
          <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300" :disabled="graduatePage === 1"
            @click="graduatePage--">Prev</button>
          <span class="px-3 py-1">{{ graduatePage }} / {{ graduateTotalPages }}</span>
          <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300"
            :disabled="graduatePage === graduateTotalPages" @click="graduatePage++">Next</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
