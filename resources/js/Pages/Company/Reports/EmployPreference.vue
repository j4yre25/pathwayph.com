<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import VueECharts from "vue-echarts";

const props = defineProps({
  jobTypeCounts: Object,
  industryCounts: Object,
  jobTypes: Array,
  industries: Array,
});

console.log("jobTypeCounts:", props.jobTypeCounts);
console.log("jobTypes:", props.jobTypes);
// Pie Chart Option
const pieOption = {
  tooltip: { trigger: "item" },
  legend: { top: "bottom" },
  series: [{
    name: "Job Type Preference",
    type: "pie",
    radius: "60%",
    data: Object.entries(props.jobTypeCounts).map(([name, value]) => ({ name, value })),
    label: { formatter: "{b}: {c} ({d}%)" }
  }]
};


// Bar Chart Option
// const barOption = {
//   tooltip: { trigger: "axis" },
//   xAxis: { type: "category", data: props.industries, name: "Industry" },
//   yAxis: { type: "value", name: "Graduates" },
//   series: [{
//     name: "Preferred Industry",
//     type: "bar",
//     data: props.industries.map(ind => props.industryCounts[ind] || 0),
//     itemStyle: { color: "#60a5fa" }
//   }]
// };
</script>

<template>
  <AppLayout title="Employment Preferences">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-12">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Employment Preferences</h2>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Pie Chart: Job Type Preferences</h3>
        <VueECharts :option="pieOption" style="height: 350px; width: 100%;" />
      </div>

      <!-- <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Bar Chart: Preferred Industries</h3> -->
        <!-- <VueECharts :option="barOption" style="height: 350px; width: 100%;" /> -->
        <!-- <div class="text-gray-500 text-lg">
          Industry preference data is not yet available.<br>
          Please check back later.
        </div>
      </div> -->
    </div>
  </AppLayout>
</template>