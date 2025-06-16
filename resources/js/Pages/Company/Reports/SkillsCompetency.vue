<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { split } from "postcss/lib/list";
import VueECharts from "vue-echarts";

const props = defineProps({
  radarIndicators: Array,
  jobSkillValues: Array,
  candidateSkillValues: Array,
  wordCloud: Object,
});

// Radar Chart Option
const radarOption = {
  tooltip: {},
  legend: { data: ["Job Requirements", "Candidates"] },
  radar: {
    indicator: props.radarIndicators,
    splitNumber: 2
  },
  series: [{
    name: "Skills Comparison",
    type: "radar",
    data: [
      { value: props.jobSkillValues, name: "Job Requirements" },
      { value: props.candidateSkillValues, name: "Candidates" }
    ]
  }]
};

// Word Cloud Option (if using echarts-wordcloud plugin)
const wordCloudOption = {
  tooltip: {},
  series: [{
    type: "wordCloud",
    shape: "circle",
    left: "center",
    top: "center",
    width: "90%",
    height: "90%",
    sizeRange: [14, 50],
    rotationRange: [-45, 90],
    gridSize: 8,
    drawOutOfBound: false,
    textStyle: { fontFamily: "sans-serif" },
    data: Object.entries(props.wordCloud).map(([name, value]) => ({ name, value })),
  }]
};
</script>

<template>
  <AppLayout title="Skills and Competency Analysis">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-12">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Skills and Competency Analysis</h2>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Radar Chart: Candidate Skills vs. Job Requirements</h3>
        <VueECharts :option="radarOption" style="height: 400px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Word Cloud: Frequently Mentioned Skills</h3>
        <VueECharts :option="wordCloudOption" style="height: 400px; width: 100%;" />
      </div>
    </div>
  </AppLayout>
</template>