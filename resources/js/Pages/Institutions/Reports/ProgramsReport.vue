<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ClipboardList } from 'lucide-vue-next';
import axios from 'axios';
import VChart from 'vue-echarts';
import { use } from 'echarts/core';
import { BarChart, RadarChart } from 'echarts/charts';
import { TitleComponent, TooltipComponent, LegendComponent, GridComponent } from 'echarts/components';
import { CanvasRenderer } from 'echarts/renderers';

use([BarChart, RadarChart, TitleComponent, TooltipComponent, LegendComponent, GridComponent, CanvasRenderer]);

const loading = ref(true);

const programs = ref([]);
const graduates = ref([]);
const schoolYears = ref([]);
const degrees = ref([]);
const programOptions = ref([]);

// Main filters
const startSchoolYear = ref('');
const endSchoolYear = ref('');
const selectedDegree = ref('');
const selectedProgram = ref('');

// Fetch data on mount
const fetchData = async () => {
  loading.value = true;
  try {
    const res = await axios.get(route('institutions.reports.programs.data'));
    programs.value = res.data.programs ?? [];
    graduates.value = res.data.graduates ?? [];
    schoolYears.value = res.data.schoolYears ?? [];
    degrees.value = res.data.degrees ?? [];
    programOptions.value = res.data.programOptions ?? [];
  } finally {
    loading.value = false;
  }
};
onMounted(fetchData);

// Unique, sorted school year labels (no duplicates)
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
  return programOptions.value.filter(prog => {
    if (!prog.id || seen.has(prog.id)) return false;
    seen.add(prog.id);
    return true;
  });
});

// Filtered graduates
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
    return true;
  });
});
//Pagination for Professions by Program Table
const topProfessionsPage = ref(1);
const topProfessionsPerPage = 10;
const topProfessionsTotalPages = computed(() =>
  Math.ceil(topEmployersByProgram.value.length / topProfessionsPerPage)
);
const paginatedTopEmployersByProgram = computed(() => {
  const start = (topProfessionsPage.value - 1) * topProfessionsPerPage;
  return topEmployersByProgram.value.slice(start, start + topProfessionsPerPage);
});
// Pagination for programs
const programPage = ref(1);
const programPerPage = 10;
const programTotalPages = computed(() => Math.ceil(programs.value.length / programPerPage));
const paginatedPrograms = computed(() => {
  const start = (programPage.value - 1) * programPerPage;
  return programs.value.slice(start, start + programPerPage);
});
watch(programPage, () => {
  if (programPage.value > programTotalPages.value) programPage.value = programTotalPages.value;
  if (programPage.value < 1) programPage.value = 1;
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

// --- Employment by Program Chart and Summary ---
const barEmploymentByProgramOption = computed(() => {
  // Only show programs that exist in the current paginatedPrograms
  const labels = paginatedPrograms.value.map(p => `${p.degree} - ${p.name}`);
  const employedData = paginatedPrograms.value.map(p =>
    filteredGraduates.value.filter(g => g.program_id === p.program_id && g.employment_status === "Employed").length
  );
  const underemployedData = paginatedPrograms.value.map(p =>
    filteredGraduates.value.filter(g => g.program_id === p.program_id && g.employment_status === "Underemployed").length
  );
  const unemployedData = paginatedPrograms.value.map(p =>
    filteredGraduates.value.filter(g => g.program_id === p.program_id && g.employment_status === "Unemployed").length
  );

  return {
    tooltip: { trigger: 'axis' },
    legend: { top: 40, data: ['Employed', 'Underemployed', 'Unemployed'] },
    grid: { left: 40, right: 20, bottom: 40, top: 80 },
    xAxis: { type: 'category', data: labels, axisLabel: { rotate: 30, interval: 0 } },
    yAxis: { type: 'value', name: 'Number of Graduates', minInterval: 1 },
    series: [
      {
        name: 'Employed',
        type: 'bar',
        stack: 'employment',
        data: employedData,
        itemStyle: { color: '#22c55e' }
      },
      {
        name: 'Underemployed',
        type: 'bar',
        stack: 'employment',
        data: underemployedData,
        itemStyle: { color: '#facc15' }
      },
      {
        name: 'Unemployed',
        type: 'bar',
        stack: 'employment',
        data: unemployedData,
        itemStyle: { color: '#ef4444' }
      }
    ]
  };
});

// Summary Interpretation for Employment by Program Chart
const employmentByProgramSummary = computed(() => {
  const labels = paginatedPrograms.value.map(p => `${p.degree} - ${p.name}`);
  const employedData = paginatedPrograms.value.map(p =>
    filteredGraduates.value.filter(g => g.program_id === p.program_id && g.employment_status === "Employed").length
  );
  const underemployedData = paginatedPrograms.value.map(p =>
    filteredGraduates.value.filter(g => g.program_id === p.program_id && g.employment_status === "Underemployed").length
  );
  const unemployedData = paginatedPrograms.value.map(p =>
    filteredGraduates.value.filter(g => g.program_id === p.program_id && g.employment_status === "Unemployed").length
  );

  if (!labels.length) {
    return 'No data available for the selected filters.';
  }

  // Find program with highest employed
  const maxEmployed = Math.max(...employedData);
  const maxEmployedIdx = employedData.indexOf(maxEmployed);
  const maxEmployedProgram = labels[maxEmployedIdx] || 'N/A';

  // Find program with highest unemployed
  const maxUnemployed = Math.max(...unemployedData);
  const maxUnemployedIdx = unemployedData.indexOf(maxUnemployed);
  const maxUnemployedProgram = labels[maxUnemployedIdx] || 'N/A';

  // Find program with highest underemployed
  const maxUnderemployed = Math.max(...underemployedData);
  const maxUnderemployedIdx = underemployedData.indexOf(maxUnderemployed);
  const maxUnderemployedProgram = labels[maxUnderemployedIdx] || 'N/A';

  let summary = `This chart compares employment status across programs. `;
  summary += `"${maxEmployedProgram}" has the highest number of employed graduates (${maxEmployed}), `;
  summary += `"${maxUnemployedProgram}" has the highest number of unemployed graduates (${maxUnemployed}), `;
  summary += `and "${maxUnderemployedProgram}" has the highest number of underemployed graduates (${maxUnderemployed}). `;
  summary += `Other programs show varying distributions of employment status.`;

  return summary;
});

// Fetch career opportunities for each program (cache for performance)
const programCareerOpportunities = computed(() => {
  // Build a map: program_id => [career opportunity titles]
  const map = {};
  programs.value.forEach(p => {
    map[p.program_id] = Array.isArray(p.career_opportunities) ? p.career_opportunities : [];
  });
  return map;
});

// --- Top Employers by Program ---
const topEmployersByProgram = computed(() => {
  // For each program in paginatedPrograms, find top 3 employers (current_job_title)
  return paginatedPrograms.value.map(p => {
    // Only Employed/Underemployed, and job title not N/A/empty
    const grads = filteredGraduates.value.filter(
      g =>
        g.program_id === p.program_id &&
        ['Employed', 'Underemployed'].includes(g.employment_status) &&
        g.current_job_title &&
        g.current_job_title.trim().toLowerCase() !== 'n/a'
    );
    const employerCounts = {};
    grads.forEach(g => {
      const employer = g.current_job_title.trim();
      if (!employer) return;
      employerCounts[employer] = (employerCounts[employer] || 0) + 1;
    });
    // Sort employers by count desc
    const sorted = Object.entries(employerCounts).sort((a, b) => b[1] - a[1]);
    return {
      program: `${p.degree} - ${p.name}`,
      employers: sorted.slice(0, 3).map(([name, count]) => ({ name, count }))
    };
  });
});

// --- Alignment Helper ---
function isAlignedGraduate(g, program) {
  // Only count if employed or underemployed
  if (!['Employed', 'Underemployed'].includes(g.employment_status)) return false;
  // Get all possible career opportunities for this program
  const careerTitles = programCareerOpportunities.value[program.program_id] || [];
  if (!g.current_job_title) return false;
  const jobTitle = g.current_job_title.toLowerCase().trim();
  // Match if job title contains any career opportunity title
  return careerTitles.some(co => co && jobTitle.includes(co));
}

// --- Program-to-Job Match Rate ---
const programMatchRates = computed(() => {
  return paginatedPrograms.value.map(p => {
    // Only Employed/Underemployed
    const grads = filteredGraduates.value.filter(
      g =>
        g.program_id === p.program_id &&
        ['Employed', 'Underemployed'].includes(g.employment_status) &&
        g.current_job_title &&
        g.current_job_title.trim().toLowerCase() !== 'n/a'
    );
    const total = grads.length;
    const matched = grads.filter(g => isAlignedGraduate(g, p)).length;
    return {
      label: `${p.degree} - ${p.name}`,
      rate: total > 0 ? Math.round((matched / total) * 100) : 0
    };
  });
});
const matchRateBarOption = computed(() => ({
  title: { text: 'Program-to-Job Match Rate', left: 'center', top: 10, textStyle: { fontSize: 16 } },
  tooltip: { trigger: 'axis' },
  grid: { left: 40, right: 20, bottom: 40, top: 80 },
  xAxis: { type: 'category', data: programMatchRates.value.map(p => p.label), axisLabel: { rotate: 30, interval: 0 } },
  yAxis: { type: 'value', name: 'Match Rate (%)', min: 0, max: 100 },
  series: [
    {
      name: 'Match Rate',
      type: 'bar',
      data: programMatchRates.value.map(p => p.rate),
      itemStyle: { color: '#38bdf8' }
    }
  ]
}));
const matchRateSummary = computed(() => {
  if (!programMatchRates.value.length) return 'No data available for program-to-job match rate.';
  const max = Math.max(...programMatchRates.value.map(p => p.rate));
  const min = Math.min(...programMatchRates.value.map(p => p.rate));
  const maxProg = programMatchRates.value.find(p => p.rate === max)?.label || 'N/A';
  const minProg = programMatchRates.value.find(p => p.rate === min)?.label || 'N/A';
  return `This chart shows the percentage of graduates whose job titles align with their academic program. "${maxProg}" has the highest match rate (${max}%), while "${minProg}" has the lowest (${min}%). This helps assess how well each program prepares students for relevant careers.`;
});

// --- Academic Program Contribution (Radar Chart) ---
const radarPrograms = computed(() => paginatedPrograms.value.map(p => `${p.degree} - ${p.name}`));
const radarMatchRates = computed(() => {
  return paginatedPrograms.value.map(p => {
    // Only Employed/Underemployed
    const grads = filteredGraduates.value.filter(
      g =>
        g.program_id === p.program_id &&
        ['Employed', 'Underemployed'].includes(g.employment_status) &&
        g.current_job_title &&
        g.current_job_title.trim().toLowerCase() !== 'n/a'
    );
    const total = grads.length;
    const matched = grads.filter(g => isAlignedGraduate(g, p)).length;
    return total > 0 ? Math.round((matched / total) * 100) : 0;
  });
});
const radarOption = computed(() => ({
  title: { text: 'Academic Program Contribution', left: 'center', top: 10, textStyle: { fontSize: 16 } },
  tooltip: {},
  radar: {
    indicator: radarPrograms.value.map((name, i) => ({
      name,
      max: 100
    })),
    radius: '65%',
    center: ['50%', '55%']
  },
  series: [
    {
      name: 'Alignment Success',
      type: 'radar',
      data: [
        {
          value: radarMatchRates.value,
          name: 'Alignment Success (%)',
          areaStyle: { color: 'rgba(34,197,94,0.2)' },
          lineStyle: { color: '#22c55e' },
          symbol: 'circle',
          itemStyle: { color: '#22c55e' }
        }
      ]
    }
  ]
}));
const radarSummary = computed(() => {
  if (!radarPrograms.value.length) return 'No data available for academic program contribution.';
  const max = Math.max(...radarMatchRates.value);
  const min = Math.min(...radarMatchRates.value);
  const maxProg = radarPrograms.value[radarMatchRates.value.indexOf(max)] || 'N/A';
  const minProg = radarPrograms.value[radarMatchRates.value.indexOf(min)] || 'N/A';
  return `This radar chart compares the alignment success of different academic programs. "${maxProg}" demonstrates the highest alignment (${max}%), indicating strong curriculum-to-career preparation, while "${minProg}" shows the lowest (${min}%). Use this to assess how well each program prepares students for their desired careers.`;
});
watch(topEmployersByProgram, () => { topProfessionsPage.value = 1; });
</script>


<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto py-10 px-4">
      <h1 class="text-3xl font-bold mb-6 text-gray-800 flex items-center gap-2">
        <ClipboardList class="w-8 h-8 text-cyan-500" />
        Programs Report
      </h1>

      <div v-if="loading" class="flex justify-center items-center py-20">
        <svg class="animate-spin h-10 w-10 text-cyan-500" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
        </svg>
      </div>
      <div v-else>
        <!-- Card for total program entries -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
            <span class="text-2xl font-bold text-gray-800">{{ programs.length }}</span>
            <span class="text-gray-500">Total Program Entries</span>
          </div>
        </div>

        <!-- Table 1: List of Programs (paginated by 10) -->
        <div class="mb-10">
          <h2 class="text-xl font-semibold mb-4">All Programs</h2>
          <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Program Code</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Program Name</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Degree</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="prog in paginatedPrograms" :key="prog.id">
                  <td class="px-4 py-2">{{ prog.program_code }}</td>
                  <td class="px-4 py-2">{{ prog.name }}</td>
                  <td class="px-4 py-2">{{ prog.degree }}</td>
                  <td class="px-4 py-2">{{ prog.status }}</td>
                </tr>
                <tr v-if="paginatedPrograms.length === 0">
                  <td colspan="4" class="text-center text-gray-400 py-6">No programs found.</td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- Pagination for programs -->
          <div class="mt-4 flex justify-center gap-2">
            <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300" :disabled="programPage === 1"
              @click="programPage--">Prev</button>
            <span class="px-3 py-1">{{ programPage }} / {{ programTotalPages }}</span>
            <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300"
              :disabled="programPage === programTotalPages" @click="programPage++">Next</button>
          </div>
        </div>

        <!-- Main Filters (same style as DegreeReport, but only 4 filters) -->
        <div class="flex flex-wrap gap-4 mb-6 mt-12">
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
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="g in paginatedGraduates" :key="g.id">
                <td class="px-4 py-2">{{ g.first_name }} {{ g.middle_name }} {{ g.last_name }}</td>
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

        <!-- Pagination for graduates -->
        <div class="mt-6 flex justify-center gap-2">
          <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300" :disabled="graduatePage === 1"
            @click="graduatePage--">Prev</button>
          <span class="px-3 py-1">{{ graduatePage }} / {{ graduateTotalPages }}</span>
          <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300"
            :disabled="graduatePage === graduateTotalPages" @click="graduatePage++">Next</button>
        </div>

        <!-- Graphical Reports -->
        <div class="my-10">
          <h3 class="text-lg font-semibold mb-4 text-cyan-700">Employment by Program</h3>
          <VChart
            v-if="barEmploymentByProgramOption.xAxis.data.length && barEmploymentByProgramOption.series.some(s => s.data.some(val => val > 0))"
            :option="barEmploymentByProgramOption" autoresize style="width: 100%; height: 350px;" />
          <div v-else class="text-gray-400 text-center py-8">No data available for employment by program.</div>
          <div class="mt-6 p-4 bg-green-50 rounded-lg text-green-900 text-base font-medium">
            <span class="font-semibold">Summary Interpretation:</span>
            <br>
            {{ employmentByProgramSummary }}
          </div>
        </div>

        <!-- Top Professions by Program -->
        <div class="my-10">
          <h3 class="text-lg font-semibold mb-4 text-cyan-700">Top Professions by Program</h3>
          <div class="overflow-x-auto bg-white rounded-xl shadow">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Program</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Top Professions)</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="row in paginatedTopEmployersByProgram" :key="row.program">
                  <td class="px-4 py-2 font-semibold">{{ row.program }}</td>
                  <td class="px-4 py-2">
                    <span v-if="row.employers.length">
                      <span v-for="(emp, idx) in row.employers" :key="emp.name">
                        {{ emp.name }} <span class="text-xs text-gray-400">({{ emp.count }})</span>
                        <span v-if="idx < row.employers.length - 1">, </span>
                      </span>
                    </span>
                    <span v-else class="text-gray-400">No data</span>
                  </td>
                </tr>
                <tr v-if="!paginatedTopEmployersByProgram.length">
                  <td colspan="2" class="text-center text-gray-400 py-6">No data available.</td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- Pagination for Top Professions by Program -->
          <div class="mt-4 flex justify-center gap-2">
            <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300" :disabled="topProfessionsPage === 1"
              @click="topProfessionsPage--">Prev</button>
            <span class="px-3 py-1">{{ topProfessionsPage }} / {{ topProfessionsTotalPages }}</span>
            <button class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300"
              :disabled="topProfessionsPage === topProfessionsTotalPages" @click="topProfessionsPage++">Next</button>
          </div>
        </div>

        <!-- Program-to-Job Match Rate -->
        <div class="my-10">
          <h3 class="text-lg font-semibold mb-4 text-cyan-700">Program-to-Job Match Rate</h3>
          <VChart v-if="matchRateBarOption.xAxis.data.length" :option="matchRateBarOption" autoresize
            style="width: 100%; height: 350px;" />
          <div v-else class="text-gray-400 text-center py-8">No data available for program-to-job match rate.</div>
          <div class="mt-6 p-4 bg-yellow-50 rounded-lg text-yellow-900 text-base font-medium">
            <span class="font-semibold">Summary Interpretation:</span>
            <br>
            {{ matchRateSummary }}
          </div>
        </div>

        <!-- Academic Program Contribution (Radar Chart) -->
        <div class="my-10">
          <h3 class="text-lg font-semibold mb-4 text-cyan-700">Academic Program Contribution</h3>
          <VChart v-if="radarOption.radar.indicator.length" :option="radarOption" autoresize
            style="width: 100%; height: 350px;" />
          <div v-else class="text-gray-400 text-center py-8">No data available for academic program contribution.</div>
          <div class="mt-6 p-4 bg-purple-50 rounded-lg text-purple-900 text-base font-medium">
            <span class="font-semibold">Summary Interpretation:</span>
            <br>
            {{ radarSummary }}
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>