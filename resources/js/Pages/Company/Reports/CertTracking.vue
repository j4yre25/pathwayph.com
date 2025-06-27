<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed, watchEffect } from "vue";
import { usePage, router } from "@inertiajs/vue3";


// Data from backend
const page = usePage();
const certifications = page.props.certifications || [];
const chartData = page.props.chartData || {};
const institutions = page.props.institutions || [];
const programs = page.props.programs || [];
const filters = ref({
  timeline: page.props.filters.timeline || "",
  institution_id: page.props.filters.institution_id ?? "",
  program_id: page.props.filters.program_id ?? "",
});

// Chart data
const chartLabels = computed(() => Object.keys(chartData));
const chartCounts = computed(() => Object.values(chartData));

// ECharts option for line chart
const lineOption = ref({
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
});

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


const totalCerts = computed(() => certifications.length);
const mostRecentMonth = computed(() => chartLabels.value.slice(-1)[0]);
const mostCertsInMonth = computed(() => {
  const max = Math.max(...chartCounts.value);
  const index = chartCounts.value.indexOf(max);
  return {
    month: chartLabels.value[index],
    count: max,
  };
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
            <tr v-for="cert in certifications" :key="cert.id">
              <td class="border px-2 py-1">{{ cert.graduate?.first_name}} {{cert.graduate?.last_name}}</td>
              <td class="border px-2 py-1">{{ cert.name }}</td>
              <td class="border px-2 py-1">{{ cert.graduate?.institution?.institution_name }}</td>
              <td class="border px-2 py-1">{{ cert.graduate?.program?.name }}</td>
              <td class="border px-2 py-1">{{ cert.issue_date }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Chart -->
        <div class="bg-white rounded-xl shadow p-8 mt-12">
            <h3 class="text-lg font-semibold mb-6 text-gray-700">Certifications Over Time</h3>
            <VueECharts :option="lineOption" style="height: 350px; width: 100%;" />

            <div class="mb-6">
                <p class="text-gray-700">
                    ✅ Total certifications recorded: <strong>{{ totalCerts }}</strong>
                </p>
                <p class="text-gray-700">
                    📅 Most recent data: <strong>{{ mostRecentMonth }}</strong>
                </p>
                <p class="text-gray-700">
                    📈 Highest in a month: <strong>{{ mostCertsInMonth.count }}</strong> certifications in <strong>{{ mostCertsInMonth.month }}</strong>
                </p>
            </div>  
        </div>


    </div>
  </AppLayout>
</template>