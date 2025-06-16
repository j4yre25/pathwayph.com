<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import VueECharts from "vue-echarts";

const props = defineProps({
  statusCounts: Object,
  totalApplications: Number,
  shortlisted: Number,
  rolesFilled: Number,
});

const pieOption = {
  tooltip: { trigger: "item" },
  legend: { top: "bottom" },
  series: [{
    name: "Applicant Status",
    type: "pie",
    radius: "60%",
    data: Object.entries(props.statusCounts).map(([name, value]) => ({ name, value })),
    label: { formatter: "{b}: {c} ({d}%)" }
  }]
};
</script>

<template>
  <AppLayout title="Applicant Status Overview">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-12">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Applicant Status Overview</h2>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <div class="text-3xl font-bold text-blue-600">{{ totalApplications }}</div>
          <div class="text-gray-600 mt-2">Total Applications</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <div class="text-3xl font-bold text-green-600">{{ shortlisted }}</div>
          <div class="text-gray-600 mt-2">Candidates Shortlisted</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <div class="text-3xl font-bold text-indigo-600">{{ rolesFilled }}</div>
          <div class="text-gray-600 mt-2">Roles Filled</div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Pie Chart: Candidate Status Distribution</h3>
        <VueECharts :option="pieOption" style="height: 350px; width: 100%;" />
      </div>
    </div>
  </AppLayout>
</template>