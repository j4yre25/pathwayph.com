<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, onMounted, watch } from "vue";
import VueECharts from "vue-echarts";

const props = defineProps({
  departmentCounts: Array,
  stackedData: Object,
  roleLevels: Array,
});

// Bar Chart: Number of job openings per department
const barOption = ref({
  tooltip: { trigger: "axis" },
  xAxis: {
    type: "category",
    data: props.departmentCounts.map(d => d.department),
  },
  yAxis: { type: "value" },
  series: [
    {
      data: props.departmentCounts.map(d => d.total),
      type: "bar",
      color: "#6366f1",
      name: "Openings",
    },
  ],
});

// Stacked Column Chart: Job postings by role level per department
const stackedOption = ref({
  tooltip: { trigger: "axis" },
  legend: { data: props.roleLevels },
  xAxis: {
    type: "category",
    data: props.departmentCounts.map(d => d.department),
  },
  yAxis: { type: "value" },
  series: props.roleLevels.map(level => ({
    name: level,
    type: "bar",
    stack: "total",
    data: props.departmentCounts.map(d => props.stackedData[level][d.department] || 0),
  })),
});
</script>

<template>
  <AppLayout title="Department-Wise Job Listings">
    <div class="max-w-7xl mx-auto py-8 px-4">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Department-Wise Job Listings</h2>

      <div class="bg-white rounded-xl shadow p-8 mb-12">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Bar Chart: Openings per Department</h3>
        <!-- <VueECharts :option="barOption" style="height: 350px; width: 100%;" /> -->
        <div class="text-gray-400 text-center py-8">Coming soon...</div>
      </div>

      <div class="bg-white rounded-xl shadow p-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Stacked Column: Role Levels per Department</h3>
        <!-- <VueECharts :option="stackedOption" style="height: 350px; width: 100%;" /> -->
          <div class="text-gray-400 text-center py-8">Coming soon...</div>
        
      </div>
    </div>
  </AppLayout>
</template>