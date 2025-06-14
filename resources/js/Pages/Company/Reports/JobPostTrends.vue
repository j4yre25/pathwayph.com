<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref } from "vue";
import VueECharts from "vue-echarts";

const props = defineProps({
  jobPostingTrends: Array,
  areaChartLabels: Array,
  areaChartSeries: Array,
});

// Line Chart: Job postings over time
const lineOption = ref({
  tooltip: { trigger: "axis" },
  xAxis: {
    type: "category",
    data: props.jobPostingTrends.map(item => item.month),
  },
  yAxis: { type: "value" },
  series: [
    {
      name: "Job Posts",
      type: "line",
      areaStyle: {},
      smooth: true,
      data: props.jobPostingTrends.map(item => item.total),
    },
  ],
});

// Area Chart: Job activity by department
const areaOption = ref({
  tooltip: { trigger: "axis" },
  legend: { top: "top" },
  xAxis: {
    type: "category",
    boundaryGap: false,
    data: props.areaChartLabels || [],
  },
  yAxis: { type: "value" },
  series: props.areaChartSeries || [],
});
</script>

<template>
  <AppLayout title="Job Posting Trends">
    <div class="max-w-7xl mx-auto py-8 px-4">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Job Posting Trends</h2>

      <div class="bg-white rounded-xl shadow p-8 mb-12">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Line Chart: Postings Over Time</h3>
        <VueECharts :option="lineOption" style="height: 300px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Area Chart: Department Activity</h3>
        <VueECharts :option="areaOption" style="height: 350px; width: 100%;" />
        <!-- <div class="text-gray-400 text-center py-8">Coming soon...</div>  -->

      </div>
    </div>
  </AppLayout>
</template>