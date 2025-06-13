<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import VueECharts from "vue-echarts";

const props = defineProps({
  genderCounts: Object,
  ethnicityCounts: Object,
  jobRoles: Array,
  roleGenderData: Object,
  genders: Array,
  ethnicities: Array,
});

// Pie Chart for Gender
const genderPieOption = {
  tooltip: { trigger: "item" },
  legend: { top: "bottom" },
  series: [{
    name: "Gender",
    type: "pie",
    radius: "60%",
    data: Object.entries(props.genderCounts).map(([name, value]) => ({ name, value })),
    label: { formatter: "{b}: {c} ({d}%)" }
  }]
};

// Pie Chart for Ethnicity (optional)
const ethnicityPieOption = {
  tooltip: { trigger: "item" },
  legend: { top: "bottom" },
  series: [{
    name: "Ethnicity",
    type: "pie",
    radius: "60%",
    data: Object.entries(props.ethnicityCounts).map(([name, value]) => ({ name, value })),
    label: { formatter: "{b}: {c} ({d}%)" }
  }]
};

// Stacked Column Chart for Gender Diversity by Job Role
const stackedOption = {
  tooltip: { trigger: "axis" },
  legend: { data: props.genders },
  xAxis: { type: "category", data: props.jobRoles, name: "Job Role" },
  yAxis: { type: "value", name: "Applicants" },
  series: props.genders.map(gender => ({
    name: gender,
    type: "bar",
    stack: "total",
    data: props.jobRoles.map(role => props.roleGenderData[gender][role] || 0),
  }))
};
</script>

<template>
  <AppLayout title="Job Posting Performance">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-12">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Job Posting Performance</h2>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Pie Chart: Gender Diversity of Applicants</h3>
        <VueECharts :option="genderPieOption" style="height: 350px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8 mb-8" v-if="props.ethnicities.length">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Pie Chart: Ethnicity Diversity of Applicants</h3>
        <VueECharts :option="ethnicityPieOption" style="height: 350px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Stacked Column: Gender Diversity by Job Role</h3>
        <VueECharts :option="stackedOption" style="height: 400px; width: 100%;" />
      </div>
    </div>
  </AppLayout>
</template>