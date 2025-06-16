<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import VueECharts from "vue-echarts";

const props = defineProps({
  ageCounts: Object,
  genderCounts: Object,
  locationCounts: Object,
  educationLevels: Array,
  genders: Array,
  stackedData: Object,
});

// Bar Chart: Age, Gender, Location
const ageBarOption = {
  tooltip: { trigger: "axis" },
  xAxis: { type: "category", data: Object.keys(props.ageCounts), name: "Age Range" },
  yAxis: { type: "value", name: "Graduates" },
  series: [{
    name: "Graduates",
    type: "bar",
    data: Object.values(props.ageCounts),
    itemStyle: { color: "#60a5fa" }
  }]
};

const genderBarOption = {
  tooltip: { trigger: "axis" },
  xAxis: { type: "category", data: Object.keys(props.genderCounts), name: "Gender" },
  yAxis: { type: "value", name: "Graduates" },
  series: [{
    name: "Graduates",
    type: "bar",
    data: Object.values(props.genderCounts),
    itemStyle: { color: "#f472b6" }
  }]
};

const locationBarOption = {
  tooltip: { trigger: "axis" },
  xAxis: { type: "category", data: Object.keys(props.locationCounts), name: "Location" },
  yAxis: { type: "value", name: "Graduates" },
  series: [{
    name: "Graduates",
    type: "bar",
    data: Object.values(props.locationCounts),
    itemStyle: { color: "#34d399" }
  }]
};

// Stacked Column Chart: Education Level by Gender
const stackedOption = {
  tooltip: { trigger: "axis" },
  legend: { data: props.genders },
  xAxis: { type: "category", data: props.educationLevels, name: "Education Level" },
  yAxis: { type: "value", name: "Graduates" },
  series: props.genders.map(gender => ({
    name: gender,
    type: "bar",
    stack: "total",
    data: props.educationLevels.map(level => props.stackedData[level][gender] || 0),
  }))
};
</script>

<template>
  <AppLayout title="Graduate Demographics">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-12">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Graduate Demographics</h2>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Bar Chart: Age Distribution</h3>
        <VueECharts :option="ageBarOption" style="height: 300px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Bar Chart: Gender Distribution</h3>
        <VueECharts :option="genderBarOption" style="height: 300px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Bar Chart: Geographic Distribution</h3>
        <VueECharts :option="locationBarOption" style="height: 300px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Stacked Column: Demographics by Education Level</h3>
        <VueECharts :option="stackedOption" style="height: 400px; width: 100%;" />
      </div>
    </div>
  </AppLayout>
</template>