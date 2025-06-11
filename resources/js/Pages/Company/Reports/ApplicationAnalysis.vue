<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import VueECharts from "vue-echarts";
import { ref, computed } from "vue";

const props = defineProps({
  jobTitles: Array,
  stackedBarSeries: Array,
  scatterData: Array,
  months: Array,
  lineData: Array,
  areaSeries: Array,
});

// Stacked Bar Chart: Applications per job posting (by stage)
const stackedBarOption = ref({
  tooltip: { trigger: "axis" },
  legend: { data: props.stackedBarSeries.map(s => s.name) },
  xAxis: { type: "category", data: props.jobTitles },
  yAxis: { type: "value" },
  series: props.stackedBarSeries,
});
console.log("Job Titles for X Axis:", props.jobTitles);

// Scatter Plot
//For filtering in scatter plot setup
const selectedSkill = ref(""); 
const selectedJobTitle = ref("");
const filteredScatterData = computed(() => {
  let data = props.scatterData;
  if (selectedSkill.value) {
    data = data.filter(d => d[3]?.includes(selectedSkill.value));
  }
  if (selectedJobTitle.value) {
    data = data.filter(d => d[2] === selectedJobTitle.value);
  }
  return data;
});
//End filtering in scatter plot setup

// Group scatter data by job title for separate series
const scatterSeries = computed(() => {
  if (selectedJobTitle.value) {
    return [{
      name: selectedJobTitle.value,
      type: "scatter",
      symbolSize: 18,
      data: filteredScatterData.value.map(d => [d[0], d[1]]),
      emphasis: { focus: "series" },
    }];
  } else {
    // Show all jobs (default)
    return props.jobTitles.map((title) => ({
      name: title,
      type: "scatter",
      symbolSize: 18,
      data: filteredScatterData.value.filter(d => d[2] === title).map(d => [d[0], d[1]]),
      emphasis: { focus: "series" },
    }));
  }
});


const scatterOption = computed(() => {
  // Find max values in your data for dynamic scaling
  const allX = scatterSeries.value.flatMap(series => series.data.map(d => d[0]));
  const allY = scatterSeries.value.flatMap(series => series.data.map(d => d[1]));
  const maxX = Math.max(10, ...allX); // at least 10
  const maxY = Math.max(10, ...allY); // at least 10

  return {
    tooltip: {
      trigger: "item",
      formatter: params => `${params.seriesName}<br/>Years: ${params.data[0]}, Applications: ${params.data[1]}`
    },
    legend: {
      data: props.jobTitles,
      type: "scroll",
      top: 20
    },
    grid: {
      top: 70, 
      left: 80,
      right: 20,
      bottom: 40
    },
    xAxis: { 
      type: "value", 
      name: "Years of Experience", 
      nameLocation: "middle",
      nameGap: 30,
      minInterval: 1,
      min: 0,
      max: maxX,
      splitNumber: 10, // Always show 10 ticks if possible
    },
    yAxis: { 
      type: "value", 
      name: "Number of Applications", 
      nameLocation: "middle",
      nameGap: 30,
      nameRotate: 90,
      minInterval: 1,
      min: 0,
      max: maxY,
      splitNumber: 10, // Always show 10 ticks if possible
    },
    series: scatterSeries.value,
  };
});

// Line Chart: Applications received over time
const lineOption = ref({
  tooltip: { trigger: "axis" },
  xAxis: { 
    type: "category", 
    data: props.months,
    name: "Month", 
    nameLocation: "middle",
    nameGap: 30
  },
  yAxis: { 
    type: "value", 
    name: "Applications",
    nameLocation: "middle",
    nameGap: 50
  },
  series: [
    {
      name: "Applications",
      type: "line",
      data: props.lineData,
      smooth: true,
      areaStyle: {},
    },
  ],
});

// // Area Chart: Applications per department over time
// const areaOption = ref({
//   tooltip: { trigger: "axis" },
//   legend: { data: props.areaSeries.map(s => s.name) },
//   xAxis: { type: "category", data: props.months },
//   yAxis: { type: "value" },
//   series: props.areaSeries,
// });
</script>

<template>
  <AppLayout title="Application Analysis">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-12">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Application Analysis</h2>

      <div class="bg-white rounded-xl shadow p-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Stacked Bar: Applications per Job Posting</h3>
        <VueECharts :option="stackedBarOption" style="height: 350px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8">
        <!-- Dropdown for job selection -->
        <div class="mb-4 flex flex-wrap gap-4 items-center">
          <label class="font-semibold">Filter by Job:</label>
          <select v-model="selectedJobTitle" class="w-80 border rounded px-2 py-1">
            <option value="">All Jobs</option>
            <option v-for="title in props.jobTitles" :key="title" :value="title">{{ title }}</option>
          </select>
          <label class="font-semibold ml-6">Filter by Skill:</label>
          <select v-model="selectedSkill" class="w-80 border rounded px-2 py-1">
            <option value="">All Skills</option>
            <option v-for="skill in props.allSkills" :key="skill" :value="skill">{{ skill }}</option>
          </select>
        </div>
        <VueECharts :option="scatterOption" style="height: 350px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Line Chart: Applications Over Time</h3>
        <VueECharts :option="lineOption" style="height: 350px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Area Chart: Applications per Department Over Time</h3>
        <!-- <VueECharts :option="areaOption" style="height: 350px; width: 100%;" /> -->
        <div class="text-gray-400 text-center py-8">Coming soon...</div>
      </div>
    </div>
  </AppLayout>
</template>