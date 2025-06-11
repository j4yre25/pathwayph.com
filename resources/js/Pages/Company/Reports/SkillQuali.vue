<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import VueECharts from "vue-echarts";

const props = defineProps({
  skillWordCloud: Object,
  certWordCloud: Object,
  bubbleData: Array,
  gradSkillWordCloud: Object,
  radarIndicators: Array,
  jobSkillValues: Array,
  gradSkillValues: Array,
});

// Word Cloud options
const skillWordCloudOption = {
  series: [{
    type: 'wordCloud',
    shape: 'circle',
    left: 'center',
    top: 'center',
    width: '90%',
    height: '90%',
    sizeRange: [12, 50],
    rotationRange: [-90, 90],
    gridSize: 8,
    drawOutOfBound: false,
    textStyle: { fontFamily: 'sans-serif' },
    data: Object.entries(props.skillWordCloud).map(([name, value]) => ({ name, value })),
  }]
  
};
console.log('Skill Word Cloud:', props.skillWordCloud);
const certWordCloudOption = {
  ...skillWordCloudOption,
  series: [{
    ...skillWordCloudOption.series[0],
    data: Object.entries(props.certWordCloud).map(([name, value]) => ({ name, value })),
  }]
};
const gradSkillWordCloudOption = {
  ...skillWordCloudOption,
  series: [{
    ...skillWordCloudOption.series[0],
    data: Object.entries(props.gradSkillWordCloud).map(([name, value]) => ({ name, value })),
  }]
};

// Bubble Chart
const bubbleOption = {
  tooltip: { trigger: "item", formatter: p => `${p.data.name}<br>Demand: ${p.data.demand}<br>Supply: ${p.data.supply}` },
  xAxis: { name: "Demand", type: "value" },
  yAxis: { name: "Supply", type: "value" },
  series: [{
    type: "scatter",
    symbolSize: d => Math.max(20, Math.sqrt(d.demand + d.supply) * 8),
    data: props.bubbleData.map(d => ({
      value: [d.demand, d.supply],
      name: d.name,
      demand: d.demand,
      supply: d.supply,
    })),
    label: { show: true, formatter: p => p.data.name, position: "top" }
  }]
};

// Radar Chart
const radarOption = {
  tooltip: {},
  legend: { data: ["Job Requirements", "Graduate Pool"] },
  radar: {
    indicator: props.radarIndicators && props.radarIndicators.length
        ? props.radarIndicators.map(i => ({
        ...i,
        max: Math.max(i.max, 5) 
        }))
    : [{ name: 'N/A', max: 5 }],
        radius: 90,
    },
  series: [{
    type: "radar",
    data: [
      { value: props.jobSkillValues && props.jobSkillValues.length ? props.jobSkillValues : [0], name: "Job Requirements" },
      { value: props.gradSkillValues && props.gradSkillValues.length ? props.gradSkillValues : [0], name: "Graduate Pool" }
    ]
  }]
};
</script>

<template>
  <AppLayout title="Skills and Qualifications">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-12">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Skills and Qualifications</h2>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Word Cloud: Most Required Skills</h3>
        <VueECharts :option="skillWordCloudOption" style="height: 350px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Word Cloud: Most Required Certifications</h3>
        <VueECharts :option="certWordCloudOption" style="height: 350px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Bubble Chart: Skills Demand vs. Talent Pool</h3>
        <VueECharts :option="bubbleOption" style="height: 400px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Word Cloud: Most Common Graduate Skills</h3>
        <VueECharts :option="gradSkillWordCloudOption" style="height: 350px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Radar Chart: Graduate Skills vs. Job Requirements</h3>
        <VueECharts :option="radarOption" style="height: 400px; width: 100%;" />
      </div>
    </div>
  </AppLayout>
</template>