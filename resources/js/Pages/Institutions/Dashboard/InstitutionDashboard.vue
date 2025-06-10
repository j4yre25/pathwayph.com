<script setup>
import { ref, computed } from "vue";
import { PieChart, BarChart, LineChart } from "vue-chart-3";
import { Users, Briefcase, LineChart as LineIcon, Ban } from "lucide-vue-next";
import { VueFlow } from '@vue-flow/core'
import '@vue-flow/core/dist/style.css'

const props = defineProps({
  graduates: Array,
  programs: Array,
  careerOpportunities: Array,
  schoolYears: Array,
  institutionCareerOpportunities: {
    type: Array,
    default: () => [],
  },
});

const filteredGraduates = computed(() => props.graduates ?? []);

const totalGraduates = computed(() => filteredGraduates.value.length);
const careerPathCounts = computed(() => {
  const counts = {};
  filteredGraduates.value.forEach(g => {
    const title = g.current_job_title || "Unknown";
    counts[title] = (counts[title] || 0) + 1;
  });
  return counts;
});
const mostCommonPath = computed(() => {
  let max = 0, result = "";
  for (const [title, count] of Object.entries(careerPathCounts.value)) {
    if (count > max) {
      max = count;
      result = title;
    }
  }
  return result;
});

const employed = computed(() =>
  filteredGraduates.value.filter((g) => g.employment_status === "Employed").length
);
const underemployed = computed(() =>
  filteredGraduates.value.filter((g) => g.employment_status === "Underemployed").length
);
const unemployed = computed(() =>
  filteredGraduates.value.filter((g) => g.employment_status === "Unemployed").length
);
const furtherStudies = computed(() =>
  filteredGraduates.value.filter((g) => g.employment_status === "Further Studies").length
);

const employmentRate = computed(() => {
  if (totalGraduates.value === 0) return 0;
  return (((employed.value + underemployed.value) / totalGraduates.value) * 100).toFixed(1);
});
const unemploymentRate = computed(() => {
  if (totalGraduates.value === 0) return 0;
  return ((unemployed.value / totalGraduates.value) * 100).toFixed(1);
});
const furtherStudiesRate = computed(() => {
  if (totalGraduates.value === 0) return 0;
  return ((furtherStudies.value / totalGraduates.value) * 100).toFixed(1);
});

// Pie Chart Filters
const selectedSchoolYear = ref("");
const selectedTerm = ref("");
const selectedProgram = ref("");
const selectedCareerOpportunity = ref("");
const selectedGender = ref("");

// Flowchart program filter (default to first program)
const selectedFlowProgram = ref(
  props.programs && props.programs.length ? props.programs[0].id : ""
);

const filteredPieGraduates = computed(() => {
  return props.graduates.filter(g => {
    if (selectedSchoolYear.value && g.school_year_range !== selectedSchoolYear.value) return false;
    // if (selectedTerm.value && g.term !== selectedTerm.value) return false;
    if (selectedProgram.value && g.program_id != selectedProgram.value) return false;
    if (selectedGender.value && g.gender !== selectedGender.value) return false;
    if (selectedCareerOpportunity.value) {
      const match = props.institutionCareerOpportunities.find(
        ico => ico.id == selectedCareerOpportunity.value && ico.program_id == g.program_id
      );
      if (!match) return false;
      if (g.current_job_title !== match.title) return false;
    }
    return true;
  });
});

const pieCareerPathData = computed(() => {
  const icoList = props.institutionCareerOpportunities ?? [];
  const counts = {};
  icoList.forEach(ico => {
    counts[ico.title] = 0;
  });

  filteredPieGraduates.value.forEach(g => {
    const match = icoList.find(
      ico => ico.title === g.current_job_title
    );
    if (match) {
      counts[match.title] = (counts[match.title] || 0) + 1;
    }
  });

  const labels = [];
  const data = [];
  Object.entries(counts).forEach(([title, count]) => {
    if (count > 0) {
      labels.push(title);
      data.push(count);
    }
  });

  return {
    labels,
    datasets: [
      {
        data,
        backgroundColor: [
          "#60a5fa", "#fbbf24", "#34d399", "#f87171", "#a78bfa", "#f472b6", "#facc15", "#38bdf8"
        ],
      },
    ],
  };
});

const pieEmploymentStatusData = computed(() => ({
  labels: ["Employed", "Underemployed", "Unemployed", "Further Studies"],
  datasets: [
    {
      data: [employed.value, underemployed.value, unemployed.value, furtherStudies.value],
      backgroundColor: ["#22c55e", "#facc15", "#ef4444", "#3b82f6"],
    },
  ],
}));

const barEmploymentByProgramData = computed(() => {
  const labels = props.programs.map(p => `${p.degree} - ${p.name}`);
  const employedData = props.programs.map(p =>
    filteredGraduates.value.filter(g => g.program_id === p.id && g.employment_status === "Employed").length
  );
  const underemployedData = props.programs.map(p =>
    filteredGraduates.value.filter(g => g.program_id === p.id && g.employment_status === "Underemployed").length
  );
  const unemployedData = props.programs.map(p =>
    filteredGraduates.value.filter(g => g.program_id === p.id && g.employment_status === "Unemployed").length
  );
  return {
    labels,
    datasets: [
      {
        label: "Employed",
        data: employedData,
        backgroundColor: "#22c55e",
      },
      {
        label: "Underemployed",
        data: underemployedData,
        backgroundColor: "#facc15",
      },
      {
        label: "Unemployed",
        data: unemployedData,
        backgroundColor: "#ef4444",
      },
    ],
  };
});

const stackedEmploymentByGenderData = computed(() => {
  const genders = ["Male", "Female"];
  const statuses = ["Employed", "Underemployed", "Unemployed"];
  const datasets = statuses.map((status, idx) => ({
    label: status,
    data: genders.map(gender =>
      filteredGraduates.value.filter(g => g.gender === gender && g.employment_status === status).length
    ),
    backgroundColor: ["#22c55e", "#facc15", "#ef4444"][idx],
  }));
  return {
    labels: genders,
    datasets,
  };
});

const lineEmploymentRateData = computed(() => {
  const schoolYearLabels = (props.schoolYears ?? []).map(sy => sy.range);

  const employedRates = schoolYearLabels.map(range => {
    const grads = filteredGraduates.value.filter(g => g.school_year_range === range);
    if (!grads.length) return 0;
    const employedCount = grads.filter(g => ["Employed", "Underemployed"].includes(g.employment_status)).length;
    return ((employedCount / grads.length) * 100).toFixed(1);
  });

  return {
    labels: schoolYearLabels,
    datasets: [
      {
        label: "Employment Rate (%)",
        data: employedRates,
        borderColor: "#22c55e",
        backgroundColor: "#bbf7d0",
        fill: true,
        tension: 0.4,
      },
    ],
  };
});

// Flowchart nodes and edges (use selectedFlowProgram)
const nodes = computed(() => {
  if (!selectedFlowProgram.value) return [];
  const program = props.programs.find(p => p.id === selectedFlowProgram.value);
  const opportunities = props.institutionCareerOpportunities.filter(
    ico => ico.program_id === selectedFlowProgram.value
  );
  // Arrange program node at the top, opportunities vertically below
  return [
    { id: 'program', label: program ? `${program.degree} - ${program.name}` : 'Program', position: { x: 200, y: 50 } },
    ...opportunities.map((ico, idx) => ({
      id: `opportunity-${ico.id}`,
      label: ico.title,
      position: { x: 200, y: 150 + idx * 80 }
    }))
  ];
});

const edges = computed(() => {
  if (!selectedFlowProgram.value) return [];
  return props.institutionCareerOpportunities
    .filter(ico => ico.program_id === selectedFlowProgram.value)
    .map(ico => ({
      id: `edge-program-${ico.id}`,
      source: 'program',
      target: `opportunity-${ico.id}`
    }));
});

// Unique Career Opportunities for filters
const uniqueCareerOpportunities = computed(() => {
  const seen = new Set();
  return props.institutionCareerOpportunities.filter(co => {
    if (seen.has(co.title)) return false;
    seen.add(co.title);
    return true;
  });
});

// Unique Terms for filters
const uniqueTerms = computed(() => {
  const seen = new Set();
  return props.schoolYears
    .map(sy => sy.term)
    .filter(term => {
      if (!term || seen.has(term)) return false;
      seen.add(term);
      return true;
    });
});

// Unique School Years for filters
const uniqueSchoolYears = computed(() => {
  const seen = new Set();
  return props.schoolYears
    .map(sy => sy.range)
    .filter(range => {
      if (!range || seen.has(range)) return false;
      seen.add(range);
      return true;
    });
});
</script>

<template>
  <section class="bg-gray-100 rounded-3xl p-6 sm:p-10 shadow-inner">
    <div class="max-w-7xl mx-auto">
      <!-- Pie Chart Filters -->
      <div class="flex flex-wrap gap-4 mb-4">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">School Year</label>
          <select v-model="selectedSchoolYear" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="range in uniqueSchoolYears" :key="range" :value="range">{{ range }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Term</label>
          <select v-model="selectedTerm" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="term in uniqueTerms" :key="term" :value="term">{{ term }}</option>
          </select>
        </div>
        <div class="mb-4">
          <label class="block text-xs font-medium text-gray-700 mb-1">Program</label>
          <select v-model="selectedProgram" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="p in props.programs" :key="p.id" :value="p.id">{{ p.degree }} - {{ p.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Career Opportunity</label>
          <select v-model="selectedCareerOpportunity" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="co in uniqueCareerOpportunities" :key="co.id" :value="co.id">{{ co.title }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Gender</label>
          <select v-model="selectedGender" class="rounded border-gray-300">
            <option value="">All</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </div>
      </div>

      <!-- 1. Career Path Overview -->
      <section class="bg-white rounded-2xl shadow p-6 space-y-6 mb-10">
        <h3 class="text-2xl font-bold text-gray-700 mb-2">1. Career Path Overview</h3>
        <!-- Textual Report -->
        <div>
          <h4 class="text-lg font-semibold text-gray-600 mb-1">Textual Report</h4>
          <ul class="list-disc ml-6 text-gray-700 space-y-1">
            <li>
              Out of <b>{{ totalGraduates }}</b> graduates, the most common career path is
              <b>{{ mostCommonPath || 'N/A' }}</b>.
            </li>
            <li>
              Graduates have pursued <b>{{ Object.keys(careerPathCounts).length }}</b> different career options.
            </li>
            <li>
              The top 3 career paths are:
              <span v-if="Object.keys(careerPathCounts).length">
                <span v-for="(item, idx) in Object.entries(careerPathCounts).sort((a,b)=>b[1]-a[1]).slice(0,3)" :key="item[0]">
                  <b>{{ item[0] }}</b> ({{ item[1] }})<span v-if="idx < 2">, </span>
                </span>
              </span>
              <span v-else>N/A</span>
            </li>
          </ul>
        </div>
        <!-- Graphical Reports: Flowchart (left) and Pie Chart (right) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Flowchart: Career Opportunities -->
          <div>
            <h4 class="text-lg font-semibold text-gray-600 mb-1">Career Opportunities Flow</h4>
            <VueFlow :nodes="nodes" :edges="edges" style="height: 350px; background: #f9fafb;" />
            <div class="mt-2 flex items-center space-x-2">
              <label class="text-xs font-medium text-gray-700" for="flow-program-select">Program:</label>
              <select
                id="flow-program-select"
                v-model.number="selectedFlowProgram"
                class="rounded border-gray-300 text-xs py-1 px-2 w-40"
              >
                <option v-for="p in props.programs" :key="p.id" :value="p.id">
                  {{ p.degree }} - {{ p.name }}
                </option>
              </select>
            </div>
          </div>
          <!-- Pie Chart: Career Paths -->
          <div>
            <h4 class="text-lg font-semibold text-gray-600 mb-1">Distribution of Career Opportunities</h4>
            <PieChart :chartData="pieCareerPathData" :options="{ plugins: { legend: { position: 'bottom' } } }" />
          </div>
        </div>
      </section>

      <!-- 2. Employment Status -->
      <section class="bg-white rounded-2xl shadow p-6 space-y-6">
        <h3 class="text-2xl font-bold text-gray-700 mb-2">2. Employment Status</h3>
        <!-- Textual Report -->
        <div>
          <h4 class="text-lg font-semibold text-gray-600 mb-1">Textual Report</h4>
          <ul class="list-disc ml-6 text-gray-700 space-y-1">
            <li>
              <b>{{ employed }}</b> graduates are employed, <b>{{ underemployed }}</b> are underemployed, and <b>{{ unemployed }}</b> are unemployed.
            </li>
            <li>
              <b>{{ furtherStudies }}</b> graduates are pursuing further studies.
            </li>
            <li>
              The employment rate is <b>{{ employmentRate }}%</b>, while the unemployment rate is <b>{{ unemploymentRate }}%</b>.
            </li>
            <li>
              <b>{{ furtherStudiesRate }}%</b> of graduates are pursuing further education.
            </li>
          </ul>
        </div>
        <!-- Graphical Reports -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Bar Chart: Employment by Program -->
          <div>
            <h4 class="text-lg font-semibold text-gray-600 mb-1">Bar Chart</h4>
            <div v-if="barEmploymentByProgramData.labels.length && barEmploymentByProgramData.datasets.some(ds => ds.data.some(val => val > 0))">
              <BarChart :chartData="barEmploymentByProgramData" :options="{ plugins: { legend: { position: 'bottom' } }, responsive: true, scales: { x: { stacked: true }, y: { stacked: true } } }" />
            </div>
            <div v-else class="text-gray-400 text-center py-8">No data available for employment by program.</div>
          </div>

          <!-- Stacked Column Chart: Employment by Gender -->
          <div>
            <h4 class="text-lg font-semibold text-gray-600 mb-1">Stacked Column Chart</h4>
            <div v-if="stackedEmploymentByGenderData.labels.length && stackedEmploymentByGenderData.datasets.some(ds => ds.data.some(val => val > 0))">
              <BarChart :chartData="stackedEmploymentByGenderData" :options="{ plugins: { legend: { position: 'bottom' } }, responsive: true, scales: { x: { stacked: true }, y: { stacked: true } } }" />
            </div>
            <div v-else class="text-gray-400 text-center py-8">No data available for employment by gender.</div>
          </div>
          <!-- Pie Chart: Employment Status -->
          <div>
            <h4 class="text-lg font-semibold text-gray-600 mb-1">Pie Chart</h4>
            <PieChart :chartData="pieEmploymentStatusData" :options="{ plugins: { legend: { position: 'bottom' } } }" />
          </div>
          <!-- KPI Cards -->
          <div>
            <h4 class="text-lg font-semibold text-gray-600 mb-1">KPI Cards</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="bg-green-50 p-4 rounded-lg flex flex-col items-center">
                <Briefcase class="w-6 h-6 text-green-600 mb-1" />
                <div class="text-sm text-gray-600">Employed</div>
                <div class="text-xl font-bold text-green-700">{{ employed }}</div>
              </div>
              <div class="bg-yellow-50 p-4 rounded-lg flex flex-col items-center">
                <LineIcon class="w-6 h-6 text-yellow-600 mb-1" />
                <div class="text-sm text-gray-600">Underemployed</div>
                <div class="text-xl font-bold text-yellow-700">{{ underemployed }}</div>
              </div>
              <div class="bg-red-50 p-4 rounded-lg flex flex-col items-center">
                <Ban class="w-6 h-6 text-red-600 mb-1" />
                <div class="text-sm text-gray-600">Unemployed</div>
                <div class="text-xl font-bold text-red-700">{{ unemployed }}</div>
              </div>
              <div class="bg-blue-50 p-4 rounded-lg flex flex-col items-center">
                <Users class="w-6 h-6 text-blue-600 mb-1" />
                <div class="text-sm text-gray-600">Further Studies</div>
                <div class="text-xl font-bold text-blue-700">{{ furtherStudies }}</div>
              </div>
            </div>
          </div>
          <!-- Line Chart: Employment Rate Over Years -->
          <div class="col-span-2">
            <h4 class="text-lg font-semibold text-gray-600 mb-1">Line Chart</h4>
            <LineChart :chartData="lineEmploymentRateData" :options="{ plugins: { legend: { position: 'bottom' } }, responsive: true }" />
          </div>
        </div>
      </section>
    </div>
  </section>
</template>
