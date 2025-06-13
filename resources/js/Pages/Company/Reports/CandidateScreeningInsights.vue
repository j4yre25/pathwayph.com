<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import VueECharts from "vue-echarts";

const props = defineProps({
  // qualificationCounts: Object,
  experienceCounts: Object,
  topSkills: Array,
  skillCounts: Object,
  departmentScreened: Object,
  roleScreened: Object,
  departments: Array,
});

// Stacked Bar Chart: By qualification, skills, experience
const stackedOption = {
  tooltip: { trigger: "axis" },
  // legend: { data: ["Qualification", "Experience", ...props.topSkills] },
  legend: { data: [ "Experience", ...props.topSkills] },
  xAxis: { type: "category", data: ["Screened Candidates"] },
  yAxis: { type: "value", name: "Count" },
  series: [
    // {
    //   name: "Qualification",
    //   type: "bar",
    //   stack: "total",
    //   data: [Object.values(props.qualificationCounts).reduce((a, b) => a + b, 0)],
    //   itemStyle: { color: "#60a5fa" }
    // },
    {
      name: "Experience",
      type: "bar",
      stack: "total",
      data: [Object.values(props.experienceCounts).reduce((a, b) => a + b, 0)],
      itemStyle: { color: "#4ade80" }
    },
    ...props.topSkills.map(skill => ({
      name: skill,
      type: "bar",
      stack: "total",
      data: [props.skillCounts[skill] || 0],
      itemStyle: { color: "#fbbf24" }
    }))
  ]
};

// Clustered Column Chart: By department and role
// const clusteredOption = {
//   tooltip: { trigger: "axis" },
//   legend: { data: ["Screened"] },
//   xAxis: { type: "category", data: props.departments },
//   yAxis: { type: "value", name: "Screened Candidates" },
//   series: [
//     {
//       name: "Screened",
//       type: "bar",
//       data: props.departments.map(d => props.departmentScreened[d] || 0),
//       itemStyle: { color: "#6366f1" }
//     }
//   ]
// };
</script>

<template>
  <AppLayout title="Candidate Screening Insights">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-12">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Candidate Screening Insights</h2>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Stacked Bar: Screened by Qualification, Skills, Experience</h3>
        <VueECharts :option="stackedOption" style="height: 400px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Clustered Column: Screening by Department</h3>
        <!-- <VueECharts :option="clusteredOption" style="height: 400px; width: 100%;" /> -->
        <p class="text-gray-600">This chart is currently under development.</p>
      </div>
    </div>
  </AppLayout>
</template>