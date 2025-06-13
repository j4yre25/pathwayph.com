<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import VueECharts from "vue-echarts";

const props = defineProps({
  lineChart: Object,
  scatterData: Array,
});

// Line Chart Option
const lineOption = {
  tooltip: { trigger: "axis" },
  legend: { data: props.lineChart.series.map(s => s.name) },
  xAxis: { type: "category", data: props.lineChart.labels, name: "Month" },
  yAxis: { type: "value", name: "Avg Days" },
  series: props.lineChart.series,
};

// Scatter Plot Option
const scatterOption = {
  tooltip: {
    trigger: "item",
    formatter: params => {
      const d = params.data;
      return `${d[2]}<br/>Avg Days: ${d[0]}<br/>Success Rate: ${d[1]}%`;
    }
  },
  xAxis: { type: "value", name: "Avg Days to Hire" },
  yAxis: { type: "value", name: "Hiring Success Rate (%)" },
  series: [{
    name: "Roles",
    type: "scatter",
    symbolSize: val => Math.max(20, val[1] / 2),
    data: props.scatterData.map(d => [d.avg_days, d.success_rate, d.job_role]),
    itemStyle: { color: "#60a5fa" }
  }]
};
</script>

<template>
  <AppLayout title="Recruitment Efficiency">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-12">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Recruitment Efficiency</h2>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Line Chart: Avg Time to Progress Through Stages</h3>
        <VueECharts :option="lineOption" style="height: 400px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Scatter Plot: Efficiency vs. Hiring Success by Role</h3>
        <VueECharts :option="scatterOption" style="height: 400px; width: 100%;" />
      </div>
    </div>
  </AppLayout>
</template>