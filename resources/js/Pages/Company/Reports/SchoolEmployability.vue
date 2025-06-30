<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import VueECharts from "vue-echarts";

const { props } = usePage();
const institutions = props.institutions;
const programs = props.programs;
const graduateList = props.graduateList;
const filters = ref({ ...props.filters });

// KPI cards
const totalHired = computed(() => graduateList.length);
const topSchool = computed(() => {
  if (filters.value.institution_id) {
    // Top program within selected institution
    const selectedInstitution = institutions.find(i => i.id == filters.value.institution_id);
    if (!selectedInstitution) return "N/A";
    const programCounts = {};
    graduateList.forEach(grad => {
      if (grad.institution === selectedInstitution.institution_name && grad.program) {
        programCounts[grad.program] = (programCounts[grad.program] || 0) + 1;
      }
    });
    const sorted = Object.entries(programCounts).sort((a, b) => b[1] - a[1]);
    return sorted.length ? sorted[0][0] : "N/A";
  } else {
    // Top institution
    const institutionCounts = {};
    graduateList.forEach(grad => {
      if (grad.institution) {
        institutionCounts[grad.institution] = (institutionCounts[grad.institution] || 0) + 1;
      }
    });
    const sorted = Object.entries(institutionCounts).sort((a, b) => b[1] - a[1]);
    return sorted.length ? sorted[0][0] : "N/A";
  }
});

// Pagination (client-side, optional)
const page = ref(1);
const pageSize = 10;
const paginatedGraduates = computed(() => {
  const start = (page.value - 1) * pageSize;
  return graduateList.slice(start, start + pageSize);
});
const totalPages = computed(() =>
  Math.ceil(graduateList.length / pageSize)
);

function goToPage(p) {
  page.value = p;
}

function applyFilters() {
  router.get(route("company.reports.schoolWise"), filters.value);
}

// --- Dynamic Bar Chart Data ---
const dynamicChartLabels = computed(() => {
  if (filters.value.institution_id) {
    // Show programs for selected institution
    const selectedInstitution = institutions.find(i => i.id == filters.value.institution_id);
    if (!selectedInstitution) return [];
    // Count by program within selected institution
    const programCounts = {};
    graduateList.forEach(grad => {
      if (grad.institution === selectedInstitution.institution_name && grad.program) {
        programCounts[grad.program] = (programCounts[grad.program] || 0) + 1;
      }
    });
    return Object.keys(programCounts);
  } else {
    // Default: show institutions
    const institutionCounts = {};
    graduateList.forEach(grad => {
      if (grad.institution) {
        institutionCounts[grad.institution] = (institutionCounts[grad.institution] || 0) + 1;
      }
    });
    return Object.keys(institutionCounts);
  }
});

const dynamicChartData = computed(() => {
  if (filters.value.institution_id) {
    // Show programs for selected institution
    const selectedInstitution = institutions.find(i => i.id == filters.value.institution_id);
    if (!selectedInstitution) return [];
    const programCounts = {};
    graduateList.forEach(grad => {
      if (grad.institution === selectedInstitution.institution_name && grad.program) {
        programCounts[grad.program] = (programCounts[grad.program] || 0) + 1;
      }
    });
    return Object.values(programCounts);
  } else {
    // Default: show institutions
    const institutionCounts = {};
    graduateList.forEach(grad => {
      if (grad.institution) {
        institutionCounts[grad.institution] = (institutionCounts[grad.institution] || 0) + 1;
      }
    });
    return Object.values(institutionCounts);
  }
});

// --- Chart Options ---
const barColors = [
  '#4F46E5', // Indigo
  '#22D3EE', // Cyan
  '#F59E42', // Orange
  '#10B981', // Green
  '#F43F5E', // Pink
  '#6366F1', // Blue
  '#FBBF24', // Yellow
  '#8B5CF6', // Purple
  '#EC4899', // Magenta
  '#14B8A6', // Teal
];

const chartOptions = computed(() => ({
  xAxis: {
    type: 'category',
    data: dynamicChartLabels.value,
    name: filters.value.institution_id ? 'Programs' : 'Institutions',
    nameLocation: 'end',
    nameGap: 30,
    axisLabel: {
      rotate: 20, 
      interval: 0,
      formatter: value => value,
    },
  },
  yAxis: {
    type: 'value',
    name: 'Number of Hires',
    nameLocation: 'middle',
    nameGap: 40,
  },
  series: [{
    data: dynamicChartData.value.map((val, idx) => ({
      value: val,
      itemStyle: { color: barColors[idx % barColors.length] },
      label: {
        show: true,
        position: 'top',
        formatter: '{c}',
      },
    })),
    type: 'bar',
    label: {
      show: true,
      position: 'top',
      formatter: '{c}',
    },
  }]
}));

const employabilitySummaryInsight = computed(() => {
  const total = totalHired.value;
  const timeline = filters.value.timeline;
  const selectedInstitution = institutions.find(i => i.id == filters.value.institution_id);
  const selectedProgram = programs.find(p => p.id == filters.value.program_id);

  const institutionPart = selectedInstitution ? ` from <strong>${selectedInstitution.institution_name}</strong>` : '';
  const programPart = selectedProgram ? ` under the <strong>${selectedProgram.name}</strong> program` : '';
  const timelinePart = timeline
    ? ` for the month of <strong>${new Date(timeline).toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}</strong>` 
    : '';

  if (total === 0) {
    return `No graduates were hired locally${institutionPart}${programPart}${timelinePart}.`;
  }

  // === RANKING LOGIC ===
  let rankedList = [];
  let rankingTitle = "";

  if (selectedInstitution) {
    // Rank programs within selected institution
    const programCounts = {};
    graduateList.forEach(grad => {
      if (grad.program && grad.institution === selectedInstitution.institution_name) {
        programCounts[grad.program] = (programCounts[grad.program] || 0) + 1;
      }
    });
    rankedList = Object.entries(programCounts)
      .sort((a, b) => b[1] - a[1])
      .map(([name, count], index) => `${index + 1}. ${name} (${count} hires)`)
      .slice(0, 5);

    rankingTitle = `The top programs within <strong>${selectedInstitution.institution_name}</strong> are:<br><strong>${rankedList.join('<br>')}</strong>.`;
  } else {
    // Rank institutions
    const institutionCounts = {};
    graduateList.forEach(grad => {
      if (grad.institution) {
        institutionCounts[grad.institution] = (institutionCounts[grad.institution] || 0) + 1;
      }
    });
    rankedList = Object.entries(institutionCounts)
      .sort((a, b) => b[1] - a[1])
      .map(([name, count], index) => `${index + 1}. ${name} (${count} hires)`)
      .slice(0, 5);

    rankingTitle = `The top institutions based on local hiring are:<br><strong>${rankedList.join('<br>')}</strong>.`;
  }

  // === TOP PROGRAM OVERALL ===
  const programCounts = {};
  graduateList.forEach(grad => {
    if (grad.program) {
      programCounts[grad.program] = (programCounts[grad.program] || 0) + 1;
    }
  });
  const topProgram = Object.entries(programCounts).sort((a, b) => b[1] - a[1])[0];

  // === TREND ESTIMATION ===
  const sorted = graduateList
    .filter(grad => grad.hired_at)
    .sort((a, b) => new Date(a.hired_at) - new Date(b.hired_at));

  const first5 = sorted.slice(0, 5).length;
  const last5 = sorted.slice(-5).length;
  let trend = "";

  if (first5 < last5) {
    trend = `There is a noticeable <strong>increasing trend</strong> in graduate hiring.`;
  } else if (first5 > last5) {
    trend = `Recent data shows a <strong>decline</strong> in graduate hiring.`;
  } else {
    trend = `Hiring activity appears to be <strong>stable</strong> over time.`;
  }

  // === FINAL SUMMARY PARAGRAPH ===
  return `
    A total of <strong>${total}</strong> graduates were hired locally${institutionPart}${programPart}${timelinePart}.<br>
    The top academic program overall is <strong>${topProgram?.[0] || 'N/A'}</strong> with <strong>${topProgram?.[1] || 0}</strong> hires.<br>
    ${rankingTitle}<br>
    ${trend}
  `;
});



</script>

<template>
  <AppLayout title="School-wise Graduate Employability">
    <div class="max-w-7xl mx-auto py-8 px-4">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">
        School-wise Graduate Employability
      </h2>

      <!-- FILTERS -->
      <div class="flex flex-wrap gap-4 mb-6">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Institution</label>
          <select v-model="filters.institution_id" class="rounded border-gray-300">
            <option value="">All Institutions</option>
            <option v-for="inst in institutions" :key="inst.id" :value="inst.id">
              {{ inst.institution_name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Program</label>
          <select v-model="filters.program_id" class="rounded border-gray-300">
            <option value="">All Programs</option>
            <option v-for="prog in programs" :key="prog.id" :value="prog.id">
              {{ prog.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Timeline</label>
          <input v-model="filters.timeline" type="month" class="rounded border-gray-300" />
        </div>
        <button @click="applyFilters" class="bg-blue-600 text-white px-4 py-2 rounded mt-6">
          Apply
        </button>
      </div>

      <!-- KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-10">
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
          <span class="text-gray-500 text-sm">Total Hired Graduates</span>
          <span class="text-3xl font-bold text-blue-600">{{ totalHired }}</span>
        </div>
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
          <span class="text-gray-500 text-sm">
            {{ filters.institution_id ? 'Top Program' : 'Top School' }}
          </span>
          <span class="text-3xl font-bold text-green-600">{{ topSchool }}</span>
        </div>
      </div>

      <!-- Table -->
      <section class="bg-white rounded-2xl shadow p-6 space-y-6 mt-10 mb-10">
        <h3 class="text-2xl font-bold text-gray-700">List of Hired Graduates</h3>
        <div v-if="paginatedGraduates.length" class="mt-2">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead>
              <tr>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Name</th>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Institution</th>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Program</th>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Company</th>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Hired At</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="grad in paginatedGraduates" :key="grad.name + grad.hired_at">
                <td class="px-2 py-1">{{ grad.name }}</td>
                <td class="px-2 py-1">{{ grad.institution }}</td>
                <td class="px-2 py-1">{{ grad.program }}</td>
                <td class="px-2 py-1">{{ grad.company_name }}</td>
                <td class="px-2 py-1">
                  {{ grad.hired_at ? new Date(grad.hired_at).toLocaleDateString() : '' }}
                </td>
              </tr>
            </tbody>
          </table>
          <!-- Pagination Controls -->
          <div class="flex justify-center mt-4" v-if="totalPages > 1">
            <button
              v-for="p in totalPages"
              :key="p"
              :class="['mx-1 px-3 py-1 rounded border', { 'bg-blue-600 text-white': p === page, 'bg-white text-blue-600': p !== page }]"
              @click="goToPage(p)"
            >{{ p }}</button>
          </div>
        </div>
        <div v-else class="text-gray-400">No graduates found for the selected filters.</div>
      </section>
      <!-- Bar Chart -->
      <div class="bg-white rounded-xl shadow p-8 mb-10">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">
          {{ filters.institution_id ? 'Hires by Program' : 'Hires by School' }}
        </h3>
        <div class="flex justify-center">
          <VueECharts
            v-if="dynamicChartLabels.length"
            :option="chartOptions"
            style="height: 450px; width: 100%; max-width: 100%;"
          />
          <div v-else class="text-gray-400 text-center py-8">No chart data available.</div>
        </div>

        <div class="mt-6 bg-blue-50 border border-green-200 rounded-lg p-4 text-blue-900">
          <p class="font-semibold mb-1">📊 Summary Insight:</p>
          <p v-html="employabilitySummaryInsight"></p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>