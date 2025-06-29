<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed, watchEffect } from "vue";
import { usePage, router } from "@inertiajs/vue3";


// Data from backend
const inertiaPage = usePage();
const certifications = computed(() => inertiaPage.props.certifications || []); 
const chartData = inertiaPage.props.chartData || {};
const institutions = inertiaPage.props.institutions || [];
const programs = inertiaPage.props.programs || [];
const filters = ref({
  timeline: inertiaPage.props.filters.timeline || "",
  institution_id: inertiaPage.props.filters.institution_id ?? "",
  program_id: inertiaPage.props.filters.program_id ?? "",
});

// Chart data
const chartLabels = computed(() => Object.keys(chartData));
const chartCounts = computed(() => Object.values(chartData));

// ECharts option for line chart
const lineOption = computed(() => ({
  tooltip: { trigger: "axis" },
  xAxis: {
    type: "category",
    data: chartLabels.value,
    name: "Month",
  },
  yAxis: {
    type: "value",
    name: "Certifications",
  },
  series: [
    {
      data: chartCounts.value,
      type: "line",
      smooth: true,
      name: "Certifications",
      color: "#6366f1",
    },
  ],
}));


// Watch for changes in chart data to update the chart
watchEffect(() => {
  lineOption.value.xAxis.data = chartLabels.value;
  lineOption.value.series[0].data = chartCounts.value;
});

function applyFilters() {
  // If timeline is a Date object, format as YYYY-MM for backend
  if (filters.value.timeline instanceof Date) {
    const y = filters.value.timeline.getFullYear();
    const m = String(filters.value.timeline.getMonth() + 1).padStart(2, '0');
    filters.value.timeline = `${y}-${m}`;
  }
  router.get(route('company.reports.certificationTracking'), filters.value, { preserveState: true });
}


const totalCerts = computed(() => certifications.value.length);
const mostRecentMonth = computed(() => chartLabels.value.slice(-1)[0]);
const mostCertsInMonth = computed(() => {
  if (!chartCounts.value.length) return { month: "N/A", count: 0 };
  const max = Math.max(...chartCounts.value);
  const index = chartCounts.value.indexOf(max);
  return {
    month: chartLabels.value[index],
    count: max,
  };
});


// All certifications from backend
const allCertifications = certifications; 
const currentPage = ref(1);
const pageSize = 10;

const paginatedCertifications = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return allCertifications.value.slice(start, start + pageSize);
});
const totalPages = computed(() => Math.ceil(allCertifications.value.length / pageSize));

function goToPage(p) {
  currentPage.value = p;
}

const certificationSummaryInsight = computed(() => {
  const total = totalCerts.value;
  const timeline = filters.value.timeline;
  const institution = institutions.find(i => i.id == filters.value.institution_id);
  const program = programs.find(p => p.id == filters.value.program_id);

  const institutionPart = institution ? ` from <strong>${institution.institution_name || institution.name}</strong>` : "";
  const programPart = program ? ` under the <strong>${program.name}</strong> program` : "";
  const timelinePart = timeline
    ? ` for the month of <strong>${new Date(timeline).toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}</strong>`
    : "";

  // No data
  if (!total) {
    return `No certifications were recorded${institutionPart}${programPart}${timelinePart}.`;
  }

  // Format months
  const mostRecent = mostRecentMonth.value
    ? new Date(`${mostRecentMonth.value}-01`).toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
    : "N/A";

  const peakMonth = mostCertsInMonth.value?.month || "N/A";
  const peakCount = mostCertsInMonth.value?.count || 0;
  const peakFormatted = peakMonth !== "N/A"
    ? new Date(`${peakMonth}-01`).toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
    : "N/A";

  // 🔍 Trend Analysis
  const firstCount = chartCounts.value[0] ?? 0;
  const lastCount = chartCounts.value.slice(-1)[0] ?? 0;
  let trendText = "";
  if (firstCount < lastCount) {
    trendText = `There was a <strong>rising trend</strong> in certifications from <strong>${firstCount}</strong> to <strong>${lastCount}</strong> over the observed period.`;
  } else if (firstCount > lastCount) {
    trendText = `There was a <strong>decline</strong> in certifications from <strong>${firstCount}</strong> to <strong>${lastCount}</strong> over the observed period.`;
  } else {
    trendText = `Certification numbers remained <strong>steady</strong> at <strong>${firstCount}</strong> over the observed period.`;
  }

  // 📊 Top Institution
  const institutionCounts = {};
  certifications.value.forEach(cert => {
    const name = cert.graduate?.institution?.institution_name || "Unknown";
    institutionCounts[name] = (institutionCounts[name] || 0) + 1;
  });
  const topInstitution = Object.entries(institutionCounts).sort((a, b) => b[1] - a[1])[0];

  // 📊 Top Program
  const programCounts = {};
  certifications.value.forEach(cert => {
    const name = cert.graduate?.program?.name || "Unknown";
    programCounts[name] = (programCounts[name] || 0) + 1;
  });
  const topProgram = Object.entries(programCounts).sort((a, b) => b[1] - a[1])[0];

  // Final summary
  return `
    A total of <strong>${total}</strong> professional certifications were recorded${institutionPart}${programPart}${timelinePart}.
    The most recent data was from <strong>${mostRecent}</strong>.
    The highest monthly activity was <strong>${peakCount}</strong> certification(s) in <strong>${peakFormatted}</strong>.<br>
    ${trendText}<br>
    🏫 Top institution: <strong>${topInstitution?.[0]}</strong> with <strong>${topInstitution?.[1]}</strong> certifications.<br>
    🎓 Top program: <strong>${topProgram?.[0]}</strong> with <strong>${topProgram?.[1]}</strong> certifications.
  `;
});


</script>

<template>
  <AppLayout title="Professional Certification Tracking">
    <div class="max-w-7xl mx-auto py-8 px-4">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Professional Certification Tracking</h2>

      <!-- Filters -->
      <form @submit.prevent="applyFilters" class="flex gap-4 mb-8">
        <input v-model="filters.timeline" type="month" class="border rounded px-2 py-1" placeholder="Timeline" />
        <select v-model="filters.institution_id" class="border rounded px-2 py-1">
          <option value="">All Institutions</option>
          <option v-for="inst in institutions" :key="inst.id" :value="inst.id">
            {{ inst.name || inst.institution_name }}
          </option>
        </select>
        <select v-model="filters.program_id" class="border rounded px-2 py-1">
          <option value="">All Programs</option>
          <option v-for="prog in programs" :key="prog.id" :value="prog.id">
            {{ prog.name }}
          </option>
        </select>
        <button type="submit" class="bg-blue-600 text-white px-4 py-1 rounded">Filter</button>
      </form>

      <!-- List -->
      <div class="bg-white rounded-xl shadow p-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Certification Logs</h3>
        <table class="min-w-full border">
          <thead>
            <tr>
              <th class="border px-2 py-1">Graduate</th>
              <th class="border px-2 py-1">Certification</th>
              <th class="border px-2 py-1">Institution</th>
              <th class="border px-2 py-1">Program</th>
              <th class="border px-2 py-1">Date Awarded</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="cert in paginatedCertifications" :key="cert.id">
              <td class="border px-2 py-1">{{ cert.graduate?.first_name}} {{cert.graduate?.last_name}}</td>
              <td class="border px-2 py-1">{{ cert.name }}</td>
              <td class="border px-2 py-1">{{ cert.graduate?.institution?.institution_name }}</td>
              <td class="border px-2 py-1">{{ cert.graduate?.program?.name }}</td>
              <td class="border px-2 py-1">{{ cert.issue_date }}</td>
            </tr>
          </tbody>
        </table>
        <!-- Pagination Controls -->
       <div class="flex justify-center mt-4" v-if="totalPages > 1">
          <button
            v-for="p in totalPages"
            :key="p"
            :class="['mx-1 px-3 py-1 rounded border', { 'bg-blue-600 text-white': p === currentPage.value, 'bg-white text-blue-600': p !== currentPage.value }]"
            @click="goToPage(p)"
          >
            {{ p }}
          </button>
        </div>
      </div>

      <!-- Chart -->
        <div class="bg-white rounded-xl shadow p-8 mt-12">
            <h3 class="text-lg font-semibold mb-6 text-gray-700">Certifications Over Time</h3>
            <VueECharts :option="lineOption" style="height: 350px; width: 100%;" />

            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4 text-blue-900">
              <p class="font-semibold mb-1">📊 Summary Insight:</p>
              <p v-html="certificationSummaryInsight"></p>
            </div>
        </div>


    </div>
  </AppLayout>
</template>