<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import VueECharts from "vue-echarts";

const props = defineProps({
  inclusiveCounts: Object,
  tagCounts: Object,
  surveyResults: Object,
  diversityTags: Array,
});

// Stacked Column Chart for diversity tags
const stackedOption = {
  tooltip: { trigger: "axis" },
  legend: { data: props.diversityTags },
  xAxis: { type: "category", data: ["Job Postings"] },
  yAxis: { type: "value", name: "Count" },
  series: props.diversityTags.map(tag => ({
    name: tag,
    type: "bar",
    stack: "total",
    data: [props.tagCounts[tag] || 0],
  }))
};

// Pie chart for inclusive language
const pieOption = {
  tooltip: { trigger: "item" },
  legend: { top: "bottom" },
  series: [{
    name: "Inclusive Language",
    type: "pie",
    radius: "60%",
    data: Object.entries(props.inclusiveCounts).map(([name, value]) => ({ name, value })),
    label: { formatter: "{b}: {c} ({d}%)" }
  }]
};
</script>

<template>
  <AppLayout title="Diversity & Inclusion">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-12">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Diversity & Inclusion Report</h2>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Inclusive Language in Job Postings</h3>
        <VueECharts :option="pieOption" style="height: 350px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Diversity Initiatives Across Job Postings</h3>
        <VueECharts :option="stackedOption" style="height: 400px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Candidate Survey Results</h3>
        <div v-for="(answers, question) in surveyResults" :key="question" class="mb-6">
          <div class="font-semibold mb-2">{{ question }}</div>
          <ul>
            <li v-for="(count, answer) in answers" :key="answer">
              {{ answer }}: {{ count }}
            </li>
          </ul>
        </div>
      </div>
    </div>
  </AppLayout>
</template>