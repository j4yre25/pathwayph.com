<script setup>
import { ref, computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import VueECharts from "vue-echarts";

const props = defineProps({
  totalGraduates: Number,
  matchedGraduates: Number,
  avgQualification: Number,
  fieldCounts: Object,
});

// Dropdown state
const selectedField = ref("All");

// All field names
const fieldNames = computed(() => ["All", ...Object.keys(props.fieldCounts)]);

// Pie data with highlight
const highlightedPieData = computed(() =>
  Object.entries(props.fieldCounts).map(([name, value]) => ({
    name,
    value,
    selected: selectedField.value !== "All" && name === selectedField.value,
    itemStyle: selectedField.value === "All" || name === selectedField.value
      ? {}
      : { color: "#e5e7eb" } // Tailwind gray-200
  }))
);

// Make pieOption reactive!
const pieOption = computed(() => ({
  tooltip: { trigger: "item" },
  legend: { show: false },
  series: [{
    name: "Field of Study",
    type: "pie",
    radius: "60%",
    center: ["40%", "50%"],
    minShowLabelAngle: 10,
    selectedMode: "single",
    label: {
      formatter: params => {
        const name = params.name.length > 18 ? params.name.slice(0, 18) + '…' : params.name;
        return `${name}: ${params.value} (${params.percent}%)`;
      },
      overflow: "truncate"
    },
    labelLine: {
      length: 10,
      length2: 10,
      maxSurfaceAngle: 45
    },
    data: highlightedPieData.value,
  }]
}));
</script>

<template>
  <AppLayout title="Graduate Pool Overview">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-12">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Graduate Pool Overview</h2>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <div class="text-3xl font-bold text-blue-600">{{ totalGraduates }}</div>
          <div class="text-gray-600 mt-2">Total Graduates</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <div class="text-3xl font-bold text-green-600">{{ matchedGraduates }}</div>
          <div class="text-gray-600 mt-2">Matched to Job Roles</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <div class="text-3xl font-bold text-indigo-600">{{ avgQualification }}</div>
          <div class="text-gray-600 mt-2">Average Qualification</div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <div class="flex items-center mb-4">
          <label for="fieldFilter" class="mr-2 font-semibold text-gray-700">Filter by Field of Study:</label>
          <select id="fieldFilter" v-model="selectedField" class="border rounded px-2 py-1">
            <option v-for="name in fieldNames" :key="name" :value="name">{{ name }}</option>
          </select>
        </div>
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Pie Chart: Graduates by Field of Study</h3>
        <VueECharts :option="pieOption" style="height: 350px; width: 100%;" />
      </div>
    </div>
  </AppLayout>
</template>