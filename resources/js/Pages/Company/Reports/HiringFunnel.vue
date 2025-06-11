<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watchEffect } from "vue";
import VueECharts from "vue-echarts";

const props = defineProps({
  funnelData: Array,
  stageTrends: Array,
});

// Debug: Log the incoming props
console.log("HiringFunnel.vue props.funnelData:", props.funnelData);
console.log("HiringFunnel.vue props.stageTrends:", props.stageTrends);


// Funnel Chart Data
const funnelOption = ref({
  tooltip: { trigger: "item", formatter: "{b}: {c}" },
  legend: { data: ["Applied", "Interviewed", "Offered", "Hired"] },
  series: [
    {
      name: "Hiring Funnel", // This can be any string
      type: "funnel",
      left: "10%",
      width: "80%",
      label: { position: "inside" },
      data: [
        { value: props.funnelData.reduce((a, b) => a + (b.applied || 0), 0), name: "Applied" },
        { value: props.funnelData.reduce((a, b) => a + (b.interviewed || 0), 0), name: "Interviewed" },
        { value: props.funnelData.reduce((a, b) => a + (b.offered || 0), 0), name: "Offered" },
        { value: props.funnelData.reduce((a, b) => a + (b.hired || 0), 0), name: "Hired" },
      ],
    },
  ],
});

// Line Chart for stage trends (placeholder)
const lineOption = ref({
  tooltip: { trigger: "axis" },
  legend: { data: props.stageTrends?.series?.map(s => s.name) || [] },
  xAxis: { type: "category", data: props.stageTrends?.labels || [] },
  yAxis: { type: "value" },
  series: props.stageTrends?.series || [],
});
// Debug: Log chart data when it changes
watchEffect(() => {
  console.log("Funnel chart data:", funnelOption.value.series[0].data);
  console.log("Line chart series:", lineOption.value.series);
  console.log("Line chart labels:", lineOption.value.xAxis.data);
});
</script>

<template>
  <AppLayout title="Hiring Funnel">
    <div class="max-w-7xl mx-auto py-8 px-4">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Hiring Funnel</h2>

      <div class="bg-white rounded-xl shadow p-8 mb-12">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Funnel Chart: Recruitment Pipeline</h3>
        <VueECharts :option="funnelOption" style="height: 350px; width: 100%;" />
    </div>
    
      <div class="bg-white rounded-xl shadow p-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Line Chart: Time to Progress Through Stages</h3>
        <VueECharts :option="lineOption" style="height: 350px; width: 100%;" />
      </div>
    </div>
  </AppLayout>
</template>