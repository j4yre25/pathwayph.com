<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import VueECharts from "vue-echarts";

const props = defineProps({
  boxPlotData: Array,
  histogramBins: Array,
});

console.log('Box Plot Data:', props.boxPlotData);
// Box Plot Option
const boxPlotOption = {
  tooltip: { trigger: "item" },
  xAxis: { type: "category", data: props.boxPlotData.map(d => d.role), name: "Job Role" },
  yAxis: { type: "value", name: "Salary" },
  series: [{
    name: "Salary Range",
    type: "boxplot",
    data: props.boxPlotData.map(d => d.values),
  }]
};

// Histogram Option
const histogramOption = {
  tooltip: {
    trigger: "axis",
    formatter: params => {
      const bin = props.histogramBins[params[0].dataIndex];
      const jobs = bin.jobs && bin.jobs.length ? `<br>Jobs:<br>${bin.jobs.join('<br>')}` : '';
      return `${bin.range}<br>Count: ${bin.count}${jobs}`;
    }
  },
  xAxis: {
    type: "category",
    data: props.histogramBins.map(b => b.range),
    name: "Salary Range",
    axisLabel: {
        formatter: (value, idx) => {
        const bin = props.histogramBins[idx];
        if (bin.jobs && bin.jobs.length) {
            // Salary range on first line, job titles on second line
            return `${value}\n${bin.jobs.join(', ')}`;
        }
        return value;
        },
        fontSize: 11,
        color: "#444",
        lineHeight: 15,
        rotate: 30, // Rotates both lines, but this is the best ECharts can do
        }
    },
  yAxis: { type: "value", name: "Number of Jobs" },
  series: [{
    name: "Jobs",
    type: "bar",
    data: props.histogramBins.map(b => b.count),
    label: {
      show: false 
    }
  }]
};
</script>

<template>
  <AppLayout title="Salary Insights">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-12">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Salary Insights</h2>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Box Plot: Salary Ranges by Job Role</h3>
        <VueECharts :option="boxPlotOption" style="height: 400px; width: 100%;" />
      </div>

      <div class="bg-white rounded-xl shadow p-8 mb-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Histogram: Frequency of Salary Ranges</h3>
        <VueECharts :option="histogramOption" style="height: 500px; width: 100%;" />
      </div>
    </div>
  </AppLayout>
</template>