<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import VueECharts from "vue-echarts";

const props = defineProps({
  typeCounts: Object,
  departments: Array,
  columnData: Object,
  types: Array,
});

// Pie Chart Option
const pieOption = {
  tooltip: { trigger: "item" },
  legend: { top: "bottom" },
  series: [{
    name: "Employment Type",
    type: "pie",
    radius: "60%",
    data: Object.entries(props.typeCounts).map(([name, value]) => ({ name, value })),
    label: { formatter: "{b}: {c} ({d}%)" }
  }]
};

// Column Chart Option
const columnOption = {
  tooltip: { trigger: "axis" },
  legend: { data: props.types },
  xAxis: { type: "category", data: props.departments },
  yAxis: { type: "value", name: "Openings" },
  series: props.types.map(type => ({
    name: type,
    type: "bar",
    stack: false,
    data: props.departments.map(dept =>
      (props.columnData[type]?.[dept]) || 0 // Use optional chaining and fallback to 0
    )
  }))
};
</script>

<template>
  <AppLayout title="Employment Type">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-12">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Employment Type Report</h2>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Pie Chart: Job Openings by Employment Type</h3>
        <VueECharts :option="pieOption" style="height: 350px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Column Chart: Job Types Across Departments</h3>
        <VueECharts :option="columnOption" style="height: 400px; width: 100%;" />
        <!-- <div class="text-gray-400 text-center py-8">Coming soon...</div> -->
      </div>
    </div>
  </AppLayout>
</template>