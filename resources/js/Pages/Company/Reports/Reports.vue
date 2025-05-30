<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch, onMounted, computed } from "vue";
import VueECharts from "vue-echarts";


const props = defineProps({
  totalOpenings: Number,
  activeListings: Number,
  rolesFilled: Number,
  typeCounts: Object,
  jobPostingTrends: Array, // for line chart
  areaChartLabels: Array,  // x-axis for area chart
  areaChartSeries: Array   // series for area chart
});

// KPI Cards
const kpis = [
  { label: "Total Openings", value: computed(() => props.totalOpenings), color: "text-blue-600" },
  { label: "Active Listings", value: computed(() => props.activeListings), color: "text-green-600" },
  { label: "Roles Filled", value: computed(() => props.rolesFilled), color: "text-purple-600" },
];

// Pie Chart Option
const pieOption = ref({
  tooltip: { trigger: "item", formatter: "{b}: {c} ({d}%)" },
  legend: {
    orient: "vertical",
    left: 0,
    top: "middle",
  },
  series: [
    {
      name: "Job Type",
      type: "pie",
      radius: "60%",
      center: ["60%", "50%"],
      data: [],
      emphasis: {
        itemStyle: {
          shadowBlur: 10,
          shadowOffsetX: 0,
          shadowColor: "rgba(0, 0, 0, 0.5)",
        },
      },
      label: { formatter: "{b}: {d}%", color: "#374151", fontWeight: "bold" },
    },
  ],
});

const updatePieChart = () => {
  pieOption.value.series[0].data = Object.entries(props.typeCounts || {}).map(
    ([type, count]) => ({ name: type, value: count })
  );
};
watch(() => props.typeCounts, updatePieChart, { immediate: true });
onMounted(updatePieChart);

// Line Chart: Job Postings Over Time
const lineOption = ref({
  tooltip: {
    trigger: "axis",
  },
  xAxis: {
    type: "category",
    boundaryGap: false,
    data: props.jobPostingTrends.map((item) => item.month), // must match controller's format: "YYYY-MM"
  },
  yAxis: {
    type: "value",
  },
  series: [
    {
      name: "Job Posts",
      type: "line",
      areaStyle: {},
      smooth: true,
      data: props.jobPostingTrends.map((item) => item.total),
    },
  ],
});
onMounted(() => {
  console.log("Line Chart Data:", props.jobPostingTrends);
});

// Area Chart: Job Activity by Department
const areaOption = ref({
  tooltip: { trigger: "axis" },
  legend: {
    top: "top",
  },
  xAxis: {
    type: "category",
    boundaryGap: false,
    data: props.areaChartLabels || [],
  },
  yAxis: {
    type: "value",
  },
  series: props.areaChartSeries || [],
});
</script>

<template>
  <AppLayout title="Company Reports Overview">
    <div class="max-w-7xl mx-auto py-8 px-4">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Job Openings Overview</h2>

      <!-- KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">
        <div
          v-for="kpi in kpis"
          :key="kpi.label"
          class="bg-white rounded-lg shadow p-6 flex flex-col items-center"
        >
          <span class="text-gray-500 text-sm">{{ kpi.label }}</span>
          <span :class="['text-3xl font-bold', kpi.color]">{{ kpi.value }}</span>
        </div>
      </div>

      <!-- Pie Chart -->
      <div class="bg-white rounded-xl shadow p-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Job Postings by Type</h3>
        <div class="flex justify-center">
          <VueECharts
            v-if="pieOption.series[0].data.length"
            :option="pieOption"
            style="height: 350px; width: 100%; max-width: 100%;"
          />
          <div v-else class="text-gray-400 text-center py-8">No chart data available.</div>
        </div>
      </div>

      <!-- Department Wise Placeholder -->
      <div class="bg-white rounded-xl shadow p-8 mt-12">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Department Wise Job Listings</h3>
        <!-- Reserved for future department-wise bar/stacked chart -->
        <div class="text-gray-400 text-center py-8">Coming soon...</div>
      </div>

      <!-- Job Posting Trends -->
      <div class="bg-white rounded-xl shadow p-8 mt-12">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Job Posting Trends</h3>

        <div class="mb-12">
          <h4 class="text-md font-semibold mb-3 text-gray-600">Line Chart: Postings Over Time</h4>
          <VueECharts :option="lineOption" style="height: 300px; width: 100%;" />
        </div>

        <div>
          <h4 class="text-md font-semibold mb-3 text-gray-600">Area Chart: Department Activity</h4>
          <!-- <VueECharts :option="areaOption" style="height: 350px; width: 100%;" /> -->
          <div class="text-gray-400 text-center py-8">Coming soon...</div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
