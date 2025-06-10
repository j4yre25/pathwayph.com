<script setup>
import { ref, computed, watch } from "vue";
import { PieChart, BarChart, LineChart, DoughnutChart } from "vue-chart-3";
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

// --- Career Path Overview Filters ---
const cpoSchoolYear = ref("");
const cpoTerm = ref("");
const cpoProgram = ref("");
const cpoCareerOpportunity = ref("");
const cpoGender = ref("");

// --- Employment Status Filters ---
const esSchoolYear = ref("");
const esTerm = ref("");
const esProgram = ref("");
const esCareerOpportunity = ref("");
const esGender = ref("");

// Line Chart Filters (independent from Employment Status filters)
const lcSchoolYearStart = ref("");
const lcSchoolYearEnd = ref("");
const lcTerm = ref("");
const lcProgram = ref("");
const lcCareerOpportunity = ref("");
const lcGender = ref("");

// Flowchart program filter (default to first program)
const selectedFlowProgram = ref(
  props.programs && props.programs.length ? props.programs[0].id : ""
);

// Unique filter values
const uniqueCareerOpportunities = computed(() => {
  const seen = new Set();
  return props.institutionCareerOpportunities.filter(co => {
    if (seen.has(co.title)) return false;
    seen.add(co.title);
    return true;
  });
});
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

// --- Career Path Overview Computed ---
const filteredPieGraduates = computed(() => {
  return props.graduates.filter(g => {
    if (cpoSchoolYear.value && g.school_year_range !== cpoSchoolYear.value) return false;
    // if (cpoTerm.value && g.term !== cpoTerm.value) return false;
    if (cpoProgram.value && g.program_id != cpoProgram.value) return false;
    if (cpoGender.value && g.gender !== cpoGender.value) return false;
    if (cpoCareerOpportunity.value) {
      const match = props.institutionCareerOpportunities.find(
        ico => ico.id == cpoCareerOpportunity.value && ico.program_id == g.program_id
      );
      if (!match) return false;
      if (g.current_job_title !== match.title) return false;
    }
    return true;
  });
});
const totalGraduates = computed(() => filteredPieGraduates.value.length);

const careerPathCounts = computed(() => {
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

// Pie Chart: Career Paths (Career Path Overview)
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

// Graduate List with Pagination (Career Path Overview)
const filteredGraduateDetails = computed(() => {
  return props.graduates
    .filter(g => {
      if (cpoSchoolYear.value && g.school_year_range !== cpoSchoolYear.value) return false;
      if (cpoProgram.value && g.program_id != cpoProgram.value) return false;
      if (cpoGender.value && g.gender !== cpoGender.value) return false;
      if (cpoCareerOpportunity.value) {
        const match = props.institutionCareerOpportunities.find(
          ico => ico.id == cpoCareerOpportunity.value && ico.program_id == g.program_id
        );
        if (!match) return false;
        if (g.current_job_title !== match.title) return false;
      } else {
        const match = props.institutionCareerOpportunities.find(
          ico => ico.title === g.current_job_title && ico.program_id == g.program_id
        );
        if (!match) return false;
      }
      return true;
    })
    .map(g => {
      const program = props.programs.find(p => p.id == g.program_id);
      const career = props.institutionCareerOpportunities.find(
        ico => ico.title === g.current_job_title && ico.program_id == g.program_id
      );
      return {
        name: [g.first_name, g.middle_name, g.last_name].filter(Boolean).join(' '),
        program: program ? program.name : 'N/A',
        degree: program ? program.degree : 'N/A',
        career: g.current_job_title || (career ? career.title : 'N/A'),
      };
    });
});
const graduatePage = ref(1);
const graduatesPerPage = 10;
const paginatedGraduateDetails = computed(() => {
  const start = (graduatePage.value - 1) * graduatesPerPage;
  const end = start + graduatesPerPage;
  return filteredGraduateDetails.value.slice(start, end);
});
const graduateTotalPages = computed(() =>
  Math.ceil(filteredGraduateDetails.value.length / graduatesPerPage)
);
watch(
  () => filteredGraduateDetails.value.length,
  () => { graduatePage.value = 1; }
);

// --- Employment Status Computed ---
const filteredEmploymentGraduates = computed(() => {
  return props.graduates.filter(g => {
    if (esSchoolYear.value && g.school_year_range !== esSchoolYear.value) return false;
    // if (esTerm.value && g.term !== esTerm.value) return false;
    if (esProgram.value && g.program_id != esProgram.value) return false;
    if (esGender.value && g.gender !== esGender.value) return false;
    if (esCareerOpportunity.value) {
      const match = props.institutionCareerOpportunities.find(
        ico => ico.id == esCareerOpportunity.value && ico.program_id == g.program_id
      );
      if (!match) return false;
      if (g.current_job_title !== match.title) return false;
    }
    return true;
  });
});
const employed = computed(() =>
  filteredEmploymentGraduates.value.filter((g) => g.employment_status === "Employed").length
);
const underemployed = computed(() =>
  filteredEmploymentGraduates.value.filter((g) => g.employment_status === "Underemployed").length
);
const unemployed = computed(() =>
  filteredEmploymentGraduates.value.filter((g) => g.employment_status === "Unemployed").length
);

const employmentRate = computed(() => {
  const total = filteredEmploymentGraduates.value.length;
  if (total === 0) return 0;
  return (((employed.value + underemployed.value) / total) * 100).toFixed(1);
});
const unemploymentRate = computed(() => {
  const total = filteredEmploymentGraduates.value.length;
  if (total === 0) return 0;
  return ((unemployed.value / total) * 100).toFixed(1);
});

// Bar Chart: Employment by Program (Employment Status)
const barEmploymentByProgramData = computed(() => {
  const labels = props.programs.map(p => `${p.degree} - ${p.name}`);
  const employedData = props.programs.map(p =>
    filteredEmploymentGraduates.value.filter(g => g.program_id === p.id && g.employment_status === "Employed").length
  );
  const underemployedData = props.programs.map(p =>
    filteredEmploymentGraduates.value.filter(g => g.program_id === p.id && g.employment_status === "Underemployed").length
  );
  const unemployedData = props.programs.map(p =>
    filteredEmploymentGraduates.value.filter(g => g.program_id === p.id && g.employment_status === "Unemployed").length
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

// Pie Chart: Employment Status (Employment Status)
const pieEmploymentStatusData = computed(() => ({
  labels: ["Employed", "Underemployed", "Unemployed"],
  datasets: [
    {
      data: [employed.value, underemployed.value, unemployed.value],
      backgroundColor: ["#22c55e", "#facc15", "#ef4444", "#3b82f6"],
    },
  ],
}));

// Doughnut Chart Data for Employment Status
const doughnutEmploymentStatusData = computed(() => ({
  labels: ["Employed", "Underemployed", "Unemployed"],
  datasets: [
    {
      data: [employed.value, underemployed.value, unemployed.value],
      backgroundColor: ["#22c55e", "#facc15", "#ef4444"],
    },
  ],
}));

// Doughnut Chart Options
const doughnutEmploymentStatusOptions = computed(() => ({
  plugins: {
    legend: { position: "bottom" },
    tooltip: {
      callbacks: {
        label: (ctx) => {
          const total = employed.value + underemployed.value + unemployed.value;
          const val = ctx.raw;
          const pct = total ? ((val / total) * 100).toFixed(1) : 0;
          return `${ctx.label}: ${val} (${pct}%)`;
        }
      }
    }
  },
  cutout: "70%",
}));

// Stacked Bar Chart: Employment by Gender (Employment Status)
const stackedEmploymentByGenderData = computed(() => {
  const genders = ["Male", "Female"];
  const statuses = ["Employed", "Underemployed", "Unemployed"];
  const datasets = statuses.map((status, idx) => ({
    label: status,
    data: genders.map(gender =>
      filteredEmploymentGraduates.value.filter(g => g.gender === gender && g.employment_status === status).length
    ),
    backgroundColor: ["#22c55e", "#facc15", "#ef4444"][idx],
  }));
  return {
    labels: genders,
    datasets,
  };
});

// Line Chart: Employment Rate Over Years (Employment Status)
const lineEmploymentRateData = computed(() => {
  // Get all unique school year ranges and sort them chronologically
  let schoolYearLabels = (props.schoolYears ?? [])
    .map(sy => sy.range)
    .filter(Boolean)
    .sort(); // Assumes school years are formatted like "2021-2022"

  // Filter by selected start and end
  if (lcSchoolYearStart.value && lcSchoolYearEnd.value) {
    schoolYearLabels = schoolYearLabels.filter(
      sy => sy >= lcSchoolYearStart.value && sy <= lcSchoolYearEnd.value
    );
  } else if (lcSchoolYearStart.value) {
    schoolYearLabels = schoolYearLabels.filter(
      sy => sy >= lcSchoolYearStart.value
    );
  } else if (lcSchoolYearEnd.value) {
    schoolYearLabels = schoolYearLabels.filter(
      sy => sy <= lcSchoolYearEnd.value
    );
  }

  const employedRates = schoolYearLabels.map(range => {
    const grads = filteredLineChartGraduates.value.filter(g => g.school_year_range === range);
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

// Highest/Lowest Employed Program
const highestEmployedProgram = computed(() => {
  let max = 0, result = null;
  props.programs.forEach(p => {
    const count = filteredEmploymentGraduates.value.filter(
      g => g.program_id === p.id && g.employment_status === "Employed"
    ).length;
    if (count > max) {
      max = count;
      result = p;
    }
  });
  return max > 0 ? `${result.degree} - ${result.name}` : "N/A";
});
const lowestEmployedProgram = computed(() => {
  let min = Infinity, result = null;
  props.programs.forEach(p => {
    const count = filteredEmploymentGraduates.value.filter(
      g => g.program_id === p.id && g.employment_status === "Employed"
    ).length;
    if (count < min && count > 0) {
      min = count;
      result = p;
    }
  });
  return min < Infinity && result ? `${result.degree} - ${result.name}` : "N/A";
});

// Highest/Lowest Unemployed Program
const highestUnemployedProgram = computed(() => {
  let max = 0, result = null;
  props.programs.forEach(p => {
    const count = filteredEmploymentGraduates.value.filter(
      g => g.program_id === p.id && g.employment_status === "Unemployed"
    ).length;
    if (count > max) {
      max = count;
      result = p;
    }
  });
  return max > 0 ? `${result.degree} - ${result.name}` : "N/A";
});
const lowestUnemployedProgram = computed(() => {
  let min = Infinity, result = null;
  props.programs.forEach(p => {
    const count = filteredEmploymentGraduates.value.filter(
      g => g.program_id === p.id && g.employment_status === "Unemployed"
    ).length;
    if (count < min && count > 0) {
      min = count;
      result = p;
    }
  });
  return min < Infinity && result ? `${result.degree} - ${result.name}` : "N/A";
});

// Highest/Lowest Underemployed Program
const highestUnderemployedProgram = computed(() => {
  let max = 0, result = null;
  props.programs.forEach(p => {
    const count = filteredEmploymentGraduates.value.filter(
      g => g.program_id === p.id && g.employment_status === "Underemployed"
    ).length;
    if (count > max) {
      max = count;
      result = p;
    }
  });
  return max > 0 ? `${result.degree} - ${result.name}` : "N/A";
});
const lowestUnderemployedProgram = computed(() => {
  let min = Infinity, result = null;
  props.programs.forEach(p => {
    const count = filteredEmploymentGraduates.value.filter(
      g => g.program_id === p.id && g.employment_status === "Underemployed"
    ).length;
    if (count < min && count > 0) {
      min = count;
      result = p;
    }
  });
  return min < Infinity && result ? `${result.degree} - ${result.name}` : "N/A";
});

// Highest/Lowest Employed Career Opportunity
const highestEmployedCareerOpportunity = computed(() => {
  let max = 0, result = null;
  props.institutionCareerOpportunities.forEach(ico => {
    const count = filteredEmploymentGraduates.value.filter(
      g => g.current_job_title === ico.title && g.employment_status === "Employed" && g.program_id === ico.program_id
    ).length;
    if (count > max) {
      max = count;
      result = ico;
    }
  });
  return max > 0 && result ? `${result.title} (${max})` : "N/A";
});
const lowestEmployedCareerOpportunity = computed(() => {
  let min = Infinity, result = null;
  props.institutionCareerOpportunities.forEach(ico => {
    const count = filteredEmploymentGraduates.value.filter(
      g => g.current_job_title === ico.title && g.employment_status === "Employed" && g.program_id === ico.program_id
    ).length;
    if (count < min && count > 0) {
      min = count;
      result = ico;
    }
  });
  return min < Infinity && result ? `${result.title} (${min})` : "N/A";
});

// Graduate List with Pagination (Employment Status)
const filteredEmploymentGraduateDetails = computed(() => {
  return props.graduates
    .filter(g => {
      if (esSchoolYear.value && g.school_year_range !== esSchoolYear.value) return false;
      if (esProgram.value && g.program_id != esProgram.value) return false;
      if (esGender.value && g.gender !== esGender.value) return false;
      // Do NOT filter by career opportunity here!
      return true;
    })
    .map(g => {
      const program = props.programs.find(p => p.id == g.program_id);
      return {
        name: [g.first_name, g.middle_name, g.last_name].filter(Boolean).join(' '),
        program: program ? program.name : 'N/A',
        degree: program ? program.degree : 'N/A',
        career: g.current_job_title || 'N/A',
        status: g.employment_status || 'N/A',
      };
    });
});
const employmentGraduatePage = ref(1);
const employmentGraduatesPerPage = 10;
const paginatedEmploymentGraduateDetails = computed(() => {
  const start = (employmentGraduatePage.value - 1) * employmentGraduatesPerPage;
  const end = start + employmentGraduatesPerPage;
  return filteredEmploymentGraduateDetails.value.slice(start, end);
});
const employmentGraduateTotalPages = computed(() =>
  Math.ceil(filteredEmploymentGraduateDetails.value.length / employmentGraduatesPerPage)
);
watch(
  () => filteredEmploymentGraduateDetails.value.length,
  () => { employmentGraduatePage.value = 1; }
);

// Graduate List by Gender (Employment Status)
const filteredEmploymentGraduateGenderDetails = computed(() => {
  return props.graduates
    .filter(g => {
      if (esSchoolYear.value && g.school_year_range !== esSchoolYear.value) return false;
      if (esProgram.value && g.program_id != esProgram.value) return false;
      if (esGender.value && g.gender !== esGender.value) return false;
      return true;
    })
    .map(g => {
      const program = props.programs.find(p => p.id == g.program_id);
      return {
        name: [g.first_name, g.middle_name, g.last_name].filter(Boolean).join(' '),
        gender: g.gender || 'N/A',
        program: program ? program.name : 'N/A',
        degree: program ? program.degree : 'N/A',
        career: g.current_job_title || 'N/A',
      };
    });
});
const genderGraduatePage = ref(1);
const genderGraduatesPerPage = 10;
const paginatedEmploymentGraduateGenderDetails = computed(() => {
  const start = (genderGraduatePage.value - 1) * genderGraduatesPerPage;
  const end = start + genderGraduatesPerPage;
  return filteredEmploymentGraduateGenderDetails.value.slice(start, end);
});
const genderGraduateTotalPages = computed(() =>
  Math.ceil(filteredEmploymentGraduateGenderDetails.value.length / genderGraduatesPerPage)
);
watch(
  () => filteredEmploymentGraduateGenderDetails.value.length,
  () => { genderGraduatePage.value = 1; }
);

const employmentPieOptions = computed(() => ({
  plugins: {
    legend: { position: "bottom" }
  }
}));

const filteredLineChartGraduates = computed(() => {
  return props.graduates.filter(g => {
    // School year range filter
    if (lcSchoolYearStart.value && lcSchoolYearEnd.value) {
      if (
        g.school_year_range < lcSchoolYearStart.value ||
        g.school_year_range > lcSchoolYearEnd.value
      ) return false;
    } else if (lcSchoolYearStart.value) {
      if (g.school_year_range < lcSchoolYearStart.value) return false;
    } else if (lcSchoolYearEnd.value) {
      if (g.school_year_range > lcSchoolYearEnd.value) return false;
    }
    if (lcTerm.value && g.term !== lcTerm.value) return false;
    if (lcProgram.value && g.program_id != lcProgram.value) return false;
    if (lcGender.value && g.gender !== lcGender.value) return false;
    if (lcCareerOpportunity.value) {
      const match = props.institutionCareerOpportunities.find(
        ico => ico.id == lcCareerOpportunity.value && ico.program_id == g.program_id
      );
      if (!match) return false;
      if (g.current_job_title !== match.title) return false;
    }
    return true;
  });
});

// Career Path Overview
const filteredCpoCareerOpportunities = computed(() => {
  if (!cpoProgram.value) return uniqueCareerOpportunities.value;
  return uniqueCareerOpportunities.value.filter(co => co.program_id == cpoProgram.value);
});

// Employment Status
const filteredEsCareerOpportunities = computed(() => {
  if (!esProgram.value) return uniqueCareerOpportunities.value;
  return uniqueCareerOpportunities.value.filter(co => co.program_id == esProgram.value);
});

// Line Chart
const filteredLcCareerOpportunities = computed(() => {
  if (!lcProgram.value) return uniqueCareerOpportunities.value;
  return uniqueCareerOpportunities.value.filter(co => co.program_id == lcProgram.value);
});
</script>

<template>
  <section class="bg-gray-100 rounded-3xl p-6 sm:p-10 shadow-inner">
    <div class="max-w-7xl mx-auto">
      <!-- Career Path Overview Filters -->
      <div class="flex flex-wrap gap-4 mb-4">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">School Year</label>
          <select v-model="cpoSchoolYear" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="range in uniqueSchoolYears" :key="range" :value="range">{{ range }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Term</label>
          <select v-model="cpoTerm" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="term in uniqueTerms" :key="term" :value="term">{{ term }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Program</label>
          <select v-model="cpoProgram" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="p in props.programs" :key="p.id" :value="p.id">{{ p.degree }} - {{ p.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Career Opportunity</label>
          <select v-model="cpoCareerOpportunity" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="co in filteredCpoCareerOpportunities" :key="co.id" :value="co.id">{{ co.title }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Gender</label>
          <select v-model="cpoGender" class="rounded border-gray-300">
            <option value="">All</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </div>
      </div>

      <!-- 1. Career Path Overview -->
      <section class="bg-white rounded-2xl shadow p-6 space-y-6 mb-10">
        <h3 class="text-2xl font-bold text-gray-700 mb-2">Career Path Overview</h3>
        <!-- Textual Report -->
        <div>
          <h4 class="text-lg font-semibold text-gray-600 mb-1">Summary</h4>
          <ul class="list-disc ml-6 text-gray-700 space-y-1">
            <li>
              Out of <b>{{ totalGraduates }}</b> graduates, the most common career path is
              <b>{{ mostCommonPath || 'N/A' }}</b>.
            </li>
            <li>
              The top 3 career paths are:
              <span v-if="Object.keys(careerPathCounts).length">
                <span v-for="(item, idx) in Object.entries(careerPathCounts).sort((a, b) => b[1] - a[1]).slice(0, 3)"
                  :key="item[0]">
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
              <select id="flow-program-select" v-model.number="selectedFlowProgram"
                class="rounded border-gray-300 text-xs py-1 px-2 w-40">
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
        <!-- Graduate Report -->
        <section class="bg-white rounded-2xl shadow p-6 space-y-6">
          <h3 class="text-2xl font-bold text-gray-700 mb-2">Textual Report</h3>
          <!-- Textual Report: List of Graduates (no extra filters, uses pie chart filters) -->
          <div class="mt-6">
            <h4 class="text-lg font-semibold text-gray-600 mb-2">Graduate List</h4>
            <div v-if="paginatedGraduateDetails.length">
              <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead>
                  <tr>
                    <th class="px-2 py-1 text-left font-medium text-gray-700">Name</th>
                    <th class="px-2 py-1 text-left font-medium text-gray-700">Program</th>
                    <th class="px-2 py-1 text-left font-medium text-gray-700">Degree</th>
                    <th class="px-2 py-1 text-left font-medium text-gray-700">Career Opportunity</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(g, idx) in paginatedGraduateDetails" :key="idx" class="hover:bg-gray-50">
                    <td class="px-2 py-1">{{ g.name }}</td>
                    <td class="px-2 py-1">{{ g.program }}</td>
                    <td class="px-2 py-1">{{ g.degree }}</td>
                    <td class="px-2 py-1">{{ g.career }}</td>
                  </tr>
                </tbody>
              </table>
              <div v-if="graduateTotalPages > 1" class="flex justify-end items-center mt-4 space-x-2">
                <button class="px-3 py-1 rounded border text-gray-700 bg-gray-100 hover:bg-gray-200"
                  :disabled="graduatePage === 1" @click="graduatePage--">
                  Prev
                </button>
                <span>Page {{ graduatePage }} of {{ graduateTotalPages }}</span>
                <button class="px-3 py-1 rounded border text-gray-700 bg-gray-100 hover:bg-gray-200"
                  :disabled="graduatePage === graduateTotalPages" @click="graduatePage++">
                  Next
                </button>
              </div>
            </div>
            <div v-else class="text-gray-400">No graduates found for the selected filters.</div>
          </div>
        </section>
      </section>

      <!-- Employment Status Filters -->
      <div class="flex flex-wrap gap-4 mb-4">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">School Year</label>
          <select v-model="esSchoolYear" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="range in uniqueSchoolYears" :key="range" :value="range">{{ range }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Term</label>
          <select v-model="esTerm" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="term in uniqueTerms" :key="term" :value="term">{{ term }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Program</label>
          <select v-model="esProgram" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="p in props.programs" :key="p.id" :value="p.id">{{ p.degree }} - {{ p.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Career Opportunity</label>
          <select v-model="esCareerOpportunity" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="co in filteredEsCareerOpportunities" :key="co.id" :value="co.id">{{ co.title }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Gender</label>
          <select v-model="esGender" class="rounded border-gray-300">
            <option value="">All</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </div>
      </div>

      <!-- 2. Employment Status -->
      <section class="bg-white rounded-2xl shadow p-6 space-y-6">
        <h3 class="text-2xl font-bold text-gray-700 mb-2">Employment Status</h3>
        <!-- Textual Report -->
        <div>
          <h4 class="text-lg font-semibold text-gray-600 mb-1">Summary</h4>
          <ul class="list-disc ml-6 text-gray-700 space-y-1">
            <li>
              <b>{{ employed }}</b> graduates are employed, <b>{{ underemployed }}</b> are underemployed, and <b>{{
                unemployed }}</b> are unemployed.
            </li>
            <li>
              The employment rate is <b>{{ employmentRate }}%</b>, while the unemployment rate is <b>{{ unemploymentRate
                }}%</b>.
            </li>
            <li>
              The program with the highest number of employed graduates is <b>{{ highestEmployedProgram }}</b>.
            </li>
            <li>
              The program with the lowest number of employed graduates is <b>{{ lowestEmployedProgram }}</b>.
            </li>
            <li>
              The program with the highest number of unemployed graduates is <b>{{ highestUnemployedProgram }}</b>.
            </li>
            <li>
              The program with the lowest number of unemployed graduates is <b>{{ lowestUnemployedProgram }}</b>.
            </li>
            <li>
              The program with the highest number of underemployed graduates is <b>{{ highestUnderemployedProgram
                }}</b>.
            </li>
            <li>
              The program with the lowest number of underemployed graduates is <b>{{ lowestUnderemployedProgram }}</b>.
            </li>
            <li>
              The career opportunity with the highest number of employed graduates is <b>{{
                highestEmployedCareerOpportunity }}</b>.
            </li>
            <li>
              The career opportunity with the lowest number of employed graduates is <b>{{
                lowestEmployedCareerOpportunity }}</b>.
            </li>
          </ul>
        </div>

        <!-- KPI Cards -->
        <div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
          </div>
        </div>

        <!-- Graphical Reports -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Bar Chart: Employment by Program -->
          <div>
            <h4 class="text-lg font-semibold text-gray-600 mb-1">Employment based on Program</h4>
            <div
              v-if="barEmploymentByProgramData.labels.length && barEmploymentByProgramData.datasets.some(ds => ds.data.some(val => val > 0))">
              <BarChart :chartData="barEmploymentByProgramData"
                :options="{ plugins: { legend: { position: 'bottom' } }, responsive: true, scales: { x: { stacked: true }, y: { stacked: true } } }" />
            </div>
            <div v-else class="text-gray-400 text-center py-8">No data available for employment by program.</div>
          </div>

          <!-- Doughnut Chart: Employment Status -->
          <div>
            <h4 class="text-lg font-semibold text-gray-600 mb-1">Employment Percentage</h4>
            <div class="relative flex justify-center items-center" style="height:400px;">
              <DoughnutChart :chartData="doughnutEmploymentStatusData" :options="doughnutEmploymentStatusOptions"
                style="height:350px; width:350px;" />
              <div class="absolute text-center"
                style="left:0; right:0; top:50%; transform:translateY(-80%); pointer-events:none;">
                <div class="text-2xl font-bold text-gray-700">{{ employmentRate }}%</div>
                <div class="text-l text-gray-500">Employment Rate</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Graduate Report: List of Graduates -->
        <div class="mt-6">
          <h4 class="text-lg font-semibold text-gray-600 mb-2">Graduate List</h4>
          <div v-if="paginatedEmploymentGraduateDetails.length">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
              <thead>
                <tr>
                  <th class="px-2 py-1 text-left font-medium text-gray-700">Name</th>
                  <th class="px-2 py-1 text-left font-medium text-gray-700">Program</th>
                  <th class="px-2 py-1 text-left font-medium text-gray-700">Degree</th>
                  <th class="px-2 py-1 text-left font-medium text-gray-700">Career Opportunity</th>
                  <th class="px-2 py-1 text-left font-medium text-gray-700">Employment Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(g, idx) in paginatedEmploymentGraduateDetails" :key="idx" class="hover:bg-gray-50">
                  <td class="px-2 py-1">{{ g.name }}</td>
                  <td class="px-2 py-1">{{ g.program }}</td>
                  <td class="px-2 py-1">{{ g.degree }}</td>
                  <td class="px-2 py-1">{{ g.career }}</td>
                  <td class="px-2 py-1">{{ g.status }}</td>
                </tr>
              </tbody>
            </table>
            <div v-if="employmentGraduateTotalPages > 1" class="flex justify-end items-center mt-4 space-x-2">
              <button class="px-3 py-1 rounded border text-gray-700 bg-gray-100 hover:bg-gray-200"
                :disabled="employmentGraduatePage === 1" @click="employmentGraduatePage--">
                Prev
              </button>
              <span>Page {{ employmentGraduatePage }} of {{ employmentGraduateTotalPages }}</span>
              <button class="px-3 py-1 rounded border text-gray-700 bg-gray-100 hover:bg-gray-200"
                :disabled="employmentGraduatePage === employmentGraduateTotalPages" @click="employmentGraduatePage++">
                Next
              </button>
            </div>
          </div>
          <div v-else class="text-gray-400">No graduates found for the selected filters.</div>
        </div>

        <!-- Stacked Column Chart and Graduate List by Gender: Side by Side -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
          <!-- Stacked Bar Chart: Employment by Gender -->
          <div>
            <h4 class="text-lg font-semibold text-gray-600 mb-1">Employment based on Gender</h4>
            <div
              v-if="stackedEmploymentByGenderData.labels.length && stackedEmploymentByGenderData.datasets.some(ds => ds.data.some(val => val > 0))"
              class="h-[600px] flex items-center">
              <BarChart :chartData="stackedEmploymentByGenderData"
                :options="{ plugins: { legend: { position: 'bottom' } }, responsive: true, maintainAspectRatio: false, scales: { x: { stacked: true }, y: { stacked: true } } }"
                style="height:100%; width:100%;" />
            </div>
            <div v-else class="text-gray-400 text-center py-8">No data available for employment by gender.</div>
          </div>
          <!-- Graduate List by Gender -->
          <div>
            <h4 class="text-lg font-semibold text-gray-600 mb-2">Graduate List by Gender</h4>
            <div v-if="paginatedEmploymentGraduateGenderDetails.length">
              <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead>
                  <tr>
                    <th class="px-2 py-1 text-left font-medium text-gray-700">Name</th>
                    <th class="px-2 py-1 text-left font-medium text-gray-700">Gender</th>
                    <th class="px-2 py-1 text-left font-medium text-gray-700">Program</th>
                    <th class="px-2 py-1 text-left font-medium text-gray-700">Degree</th>
                    <th class="px-2 py-1 text-left font-medium text-gray-700">Career Opportunity</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(g, idx) in paginatedEmploymentGraduateGenderDetails" :key="idx" class="hover:bg-gray-50">
                    <td class="px-2 py-1">{{ g.name }}</td>
                    <td class="px-2 py-1">{{ g.gender }}</td>
                    <td class="px-2 py-1">{{ g.program }}</td>
                    <td class="px-2 py-1">{{ g.degree }}</td>
                    <td class="px-2 py-1">{{ g.career }}</td>
                  </tr>
                </tbody>
              </table>
              <div v-if="genderGraduateTotalPages > 1" class="flex justify-end items-center mt-4 space-x-2">
                <button class="px-3 py-1 rounded border text-gray-700 bg-gray-100 hover:bg-gray-200"
                  :disabled="genderGraduatePage === 1" @click="genderGraduatePage--">
                  Prev
                </button>
                <span>Page {{ genderGraduatePage }} of {{ genderGraduateTotalPages }}</span>
                <button class="px-3 py-1 rounded border text-gray-700 bg-gray-100 hover:bg-gray-200"
                  :disabled="genderGraduatePage === genderGraduateTotalPages" @click="genderGraduatePage++">
                  Next
                </button>
              </div>
            </div>
            <div v-else class="text-gray-400">No graduates found for the selected filters.</div>
          </div>
        </div>

        <!-- Line Chart: Employment Rate Over Years -->
        <div class="col-span-2 mt-8">
          <h4 class="text-lg font-semibold text-gray-600 mb-1">Employment Rate by School Year</h4>
          <!-- Line Chart Filters -->
          <div class="flex flex-wrap gap-4 mb-4 items-end">
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">School Year Start</label>
              <select v-model="lcSchoolYearStart" class="rounded border-gray-300">
                <option value="">All</option>
                <option v-for="range in uniqueSchoolYears" :key="range" :value="range">{{ range }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">School Year End</label>
              <select v-model="lcSchoolYearEnd" class="rounded border-gray-300">
                <option value="">All</option>
                <option v-for="range in uniqueSchoolYears" :key="range" :value="range">{{ range }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Term</label>
              <select v-model="lcTerm" class="rounded border-gray-300">
                <option value="">All</option>
                <option v-for="term in uniqueTerms" :key="term" :value="term">{{ term }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Program</label>
              <select v-model="lcProgram" class="rounded border-gray-300">
                <option value="">All</option>
                <option v-for="p in props.programs" :key="p.id" :value="p.id">{{ p.degree }} - {{ p.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Career Opportunity</label>
              <select v-model="lcCareerOpportunity" class="rounded border-gray-300">
                <option value="">All</option>
                <option v-for="co in filteredLcCareerOpportunities" :key="co.id" :value="co.id">{{ co.title }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-1">Gender</label>
              <select v-model="lcGender" class="rounded border-gray-300">
                <option value="">All</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
          </div>
          <LineChart :chartData="lineEmploymentRateData"
            :options="{ plugins: { legend: { position: 'bottom' } }, responsive: true }" />
        </div>
      </section>
    </div>
  </section>
</template>
