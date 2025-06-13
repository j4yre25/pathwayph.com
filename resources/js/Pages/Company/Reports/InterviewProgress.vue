<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import VueECharts from "vue-echarts";

const props = defineProps({
  funnelData: Array,
  bubbleData: Array,
});

// Funnel Chart Option
const funnelOption = {
  tooltip: { trigger: "item", formatter: "{b}: {c}" },
  series: [{
    name: "Interview Funnel",
    type: "funnel",
    left: "10%",
    width: "80%",
    label: { position: "inside" },
    data: props.funnelData.map(d => ({ name: d.stage, value: d.count })),
    itemStyle: { color: "#60a5fa" }
  }]
};

// Bubble Chart Option
const bubbleOption = {
  tooltip: {
    trigger: "item",
    formatter: params => `${params.data.job_role}<br/>Interviews: ${params.data.interviewed}`
  },
  xAxis: { type: "category", data: props.bubbleData.map(d => d.job_role), name: "Job Role" },
  yAxis: { type: "value", name: "Interviews" },
  series: [{
    name: "Interviews",
    type: "scatter",
    symbolSize: val => Math.max(20, val[1]), // Bubble size
    data: props.bubbleData.map(d => [d.job_role, d.interviewed, d.size]),
    itemStyle: { color: "#fbbf24" }
  }]
};
</script>

<template>
  <AppLayout title="Interview Progress">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-12">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Interview Progress</h2>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Funnel: Applicant Flow Through Stages</h3>
        <VueECharts :option="funnelOption" style="height: 400px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Bubble: Interviews per Job Role</h3>
        <VueECharts :option="bubbleOption" style="height: 400px; width: 100%;" />
      </div>
    </div>
  </AppLayout>
</template>