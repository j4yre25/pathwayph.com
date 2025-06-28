<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import VueECharts from "vue-echarts";

const props = defineProps({
  jobsList: Object, // paginated
  jobPostingTrends: Array,
  areaChartLabels: Array,
  areaChartSeries: Array,
  departments: Array,
  statuses: Array,
  filters: Object,
});

const selectedDepartment = ref(props.filters?.department || "");
const selectedStatus = ref(props.filters?.status || "");
const dateFrom = ref(props.filters?.date_from || "");
const page = ref(props.jobsList?.current_page || 1);

function applyFilters(newPage = 1) {
  router.get(
    route("company.reports.trends"),
    {
      department: selectedDepartment.value,
      status: selectedStatus.value,
      date_from: dateFrom.value,
      page: newPage,
    },
    { preserveState: true, replace: true }
  );
}

watch([selectedDepartment, selectedStatus, dateFrom], () => applyFilters(1));
watch(page, (val) => applyFilters(val));

// Line Chart: Job postings over time
const lineOption = computed(() => ({
  tooltip: { trigger: "axis" },
  xAxis: {
    type: "category",
    data: props.jobPostingTrends.map(item => item.month),
    name: "Month",
    nameLocation: "middle",
    nameGap: 30,
  },
  yAxis: { type: "value", name: "Job Posts", nameLocation: "middle", nameGap: 40 },
  series: [
    {
      name: "Job Posts",
      type: "line",
      areaStyle: {},
      smooth: true,
      data: props.jobPostingTrends.map(item => item.total),
    },
  ],
}));

// Area Chart: Job activity by department
const areaOption = computed(() => ({
  tooltip: { trigger: "axis" },
  legend: { top: "top" },
  grid: { top: 90 },
  xAxis: {
    type: "category",
    boundaryGap: false,
    data: props.areaChartLabels || [],
    name: "Month",
    nameLocation: "middle",
    nameGap: 30,
  },
  yAxis: { type: "value", name: "Job Posts", nameLocation: "middle", nameGap: 40 },
  series: props.areaChartSeries || [],
}));

const summaryInsight = computed(() => {
  const trends = props.jobPostingTrends || [];
  const areaSeries = props.areaChartSeries || [];

  const total = trends.reduce((sum, row) => sum + (row.total || 0), 0);
  const selectedDept = selectedDepartment.value;
  const status = selectedStatus.value;
  const date = dateFrom.value;

  const statusPart = status ? ` with status <strong>${status}</strong>` : "";
  const datePart = date
    ? ` starting from <strong>${new Date(date).toLocaleDateString("en-US", { month: "long", year: "numeric" })}</strong>`
    : "";

  // If no total at all
  if (!total) {
    return `<strong>No job postings</strong> were recorded${statusPart}${datePart}.`;
  }

  const formatMonth = (str) => {
    const [year, month] = str.split("-");
    return new Date(Number(year), Number(month) - 1).toLocaleDateString("en-US", {
      month: "long",
      year: "numeric"
    });
  };

  const deptTotals = areaSeries.map(series => ({
    name: series.name,
    total: series.data.reduce((a, b) => a + b, 0),
    monthly: series.data
  }));

  if (selectedDept) {
    const deptEntry = deptTotals.find(d => d.name === selectedDept);
    const deptTotal = deptEntry?.total || 0;

    // If department has no data at all after filters
    if (!deptEntry || deptTotal === 0) {
      return `
        A total of <strong>${total} job postings</strong> were recorded${statusPart}${datePart}.
        However, <strong>${selectedDept}</strong> had no recorded postings in the selected period.
      `;
    }

    // Peak month(s) in the selected department
    const deptMonthly = deptEntry.monthly || [];
    const peakCount = Math.max(...deptMonthly);
    const peakIndices = deptMonthly
      .map((val, idx) => val === peakCount ? idx : -1)
      .filter(idx => idx !== -1);
    const peakMonths = peakIndices
      .map(idx => props.areaChartLabels?.[idx])
      .filter(Boolean)
      .map(formatMonth);

    const peakText = peakMonths.length === 0
      ? `There was no peak posting month for <strong>${selectedDept}</strong> in this period.`
      : peakMonths.length === 1
        ? `The highest job posting activity in this department was in <strong>${peakMonths[0]}</strong> with <strong>${peakCount} postings</strong>.`
        : `The peak months in this department were <strong>${peakMonths.join(", ")}</strong>, each with <strong>${peakCount} postings</strong>.`;

    // Rank of department
    const sorted = [...deptTotals].sort((a, b) => b.total - a.total);
    const rank = sorted.findIndex(d => d.name === selectedDept) + 1;
    const totalDepts = sorted.length;

    return `
      A total of <strong>${total} job postings</strong> were recorded${statusPart}${datePart}.
      Within the <strong>${selectedDept}</strong> department, there were <strong>${deptTotal} postings</strong> overall.
      ${peakText}
      This department ranked <strong>#${rank}</strong> out of <strong>${totalDepts}</strong> in posting volume.
    `;
  } else {
    // Global peak month(s)
    const maxTrend = Math.max(...trends.map(row => row.total));
    const peakMonths = trends
      .filter(row => row.total === maxTrend)
      .map(row => formatMonth(row.month));

    const peakText = peakMonths.length === 1
      ? `The highest job posting activity was recorded in <strong>${peakMonths[0]}</strong> with <strong>${maxTrend} postings</strong>.`
      : `Peak posting months were <strong>${peakMonths.join(", ")}</strong>, each with <strong>${maxTrend} postings</strong>.`;

    // Top department(s)
    const maxDeptTotal = Math.max(...deptTotals.map(d => d.total));
    const topDepartments = deptTotals
      .filter(d => d.total === maxDeptTotal)
      .map(d => d.name);

    const deptText = topDepartments.length === 1
      ? `The most active department was <strong>${topDepartments[0]}</strong> with <strong>${maxDeptTotal} postings</strong>.`
      : `Top departments with equal posting activity (<strong>${maxDeptTotal} postings</strong> each) include: <strong>${topDepartments.join(", ")}</strong>.`;

    return `
      A total of <strong>${total} job postings</strong> were recorded${statusPart}${datePart}.
      ${peakText}
      ${deptText}
    `;
  }
});



</script>

<template>
  <AppLayout title="Job Posting Trends">
    <div class="max-w-7xl mx-auto py-8 px-4">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Job Posting Trends</h2>

      <!-- Filters -->
      <div class="flex flex-wrap gap-4 mb-6">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Department</label>
          <select v-model="selectedDepartment" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="dept in props.departments" :key="dept" :value="dept">{{ dept }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
          <select v-model="selectedStatus" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="s in props.statuses" :key="s" :value="s">{{ s }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Date Created</label>
          <input type="date" v-model="dateFrom" class="rounded border-gray-300" />
        </div>
      </div>

      <!-- Job List Table -->
      <section class="bg-white rounded-2xl shadow p-6 space-y-6 mb-10">
        <h3 class="text-2xl font-bold text-gray-700">Job List</h3>
        <div v-if="props.jobsList && props.jobsList.data.length">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead>
              <tr>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Title</th>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Date Created</th>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Department</th>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="job in props.jobsList.data" :key="job.id" class="hover:bg-gray-50">
                <td class="px-2 py-1">{{ job.title }}</td>
                <td class="px-2 py-1">{{ job.created_at }}</td>
                <td class="px-2 py-1">{{ job.department }}</td>
                <td class="px-2 py-1 capitalize">{{ job.status }}</td>
              </tr>
            </tbody>
          </table>
          <!-- Pagination Controls -->
          <div class="flex justify-center mt-4" v-if="props.jobsList.last_page > 1">
            <button
              v-for="p in props.jobsList.last_page"
              :key="p"
              :class="['mx-1 px-3 py-1 rounded border', { 'bg-blue-600 text-white': p === props.jobsList.current_page, 'bg-white text-blue-600': p !== props.jobsList.current_page }]"
              @click="page = p"
            >{{ p }}</button>
          </div>
        </div>
        <div v-else class="text-gray-400">No jobs found for the selected filters.</div>
      </section>

      <!-- Charts -->
      <div class="bg-white rounded-xl shadow p-8 mb-6">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Line Chart: Postings Over Time</h3>
        <VueECharts :option="lineOption" style="height: 300px; width: 100%;" />
        
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Area Chart: Department Activity</h3>
        <VueECharts :option="areaOption" style="height: 400px; width: 100%;" />
        
        <!-- Summary Insight -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-900 mt-8">
          <p class="font-semibold mb-1">📊 Summary Insight:</p>
          <p v-html="summaryInsight"></p>
        </div>
      </div>

    </div>
  </AppLayout>
</template>