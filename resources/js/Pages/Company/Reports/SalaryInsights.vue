<script setup>
import { ref, watch, computed } from "vue"
import AppLayout from "@/Layouts/AppLayout.vue"
import VueECharts from "vue-echarts"

// Safer props (defaults avoid .map on undefined)
const props = defineProps({
  boxPlotData: { type: Array, default: () => [] },
  histogramBins: { type: Array, default: () => [] },
})

console.log('SalaryInsights props -> boxPlotData:', props.boxPlotData)
console.log('SalaryInsights props -> histogramBins:', props.histogramBins)

// Derived categories
const boxCategories = computed(() => props.boxPlotData.map(d => d.role))
const boxSeriesData = computed(() => props.boxPlotData.map(d => d.values))
const histCategories = computed(() => props.histogramBins.map(b => b.range))
const histCounts = computed(() => props.histogramBins.map(b => b.count))

// Reactive chart options
const boxPlotOption = ref({})
const histogramOption = ref({})

function buildBoxPlotOption() {
  boxPlotOption.value = {
    tooltip: { trigger: "item" },
    grid: { left: 50, right: 30, top: 40, bottom: 60 },
    xAxis: { type: "category", data: boxCategories.value, name: "Job Role", axisLabel: { rotate: 25 } },
    yAxis: { type: "value", name: "Salary" },
    series: [{
      name: "Salary Range",
      type: "boxplot",
      itemStyle: { color: "#4F46E5" },
      data: boxSeriesData.value,
    }],
  }
}

function buildHistogramOption() {
  histogramOption.value = {
    tooltip: {
      trigger: "axis",
      formatter: params => {
        const idx = params[0].dataIndex
        const bin = props.histogramBins[idx]
        if (!bin) return ''
        const jobs = bin.jobs?.length ? `<br/>Jobs:<br/>${bin.jobs.join('<br/>')}` : ''
        return `${bin.range}<br/>Count: ${bin.count}${jobs}`
      }
    },
    grid: { left: 50, right: 30, top: 40, bottom: 80 },
    xAxis: {
      type: "category",
      data: histCategories.value,
      name: "Salary Range",
      axisLabel: { rotate: 35, fontSize: 11, lineHeight: 14 }
    },
    yAxis: { type: "value", name: "Jobs" },
    series: [{
      name: "Jobs",
      type: "bar",
      data: histCounts.value,
      itemStyle: { color: "#6366F1" }
    }]
  }
}

watch(
  () => [props.boxPlotData, props.histogramBins],
  () => {
    buildBoxPlotOption()
    buildHistogramOption()
  },
  { immediate: true, deep: true }
)
</script>

<template>
  <AppLayout title="Salary Insights">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-12">
      <h2 class="text-2xl font-bold text-gray-800">Salary Insights</h2>

      <!-- Box Plot -->
      <div class="bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-700">Salary Ranges by Job Role (Box Plot)</h3>
          <span class="text-xs text-gray-500" v-if="!boxPlotData.length">No salary data found.</span>
        </div>
        <div v-if="boxPlotData.length">
          <VueECharts :option="boxPlotOption" style="height: 420px; width: 100%;" />
        </div>
        <div v-else class="text-sm text-gray-500 italic py-6 text-center">
          No salary entries yet for this company’s jobs.
        </div>
      </div>

      <!-- Histogram -->
      <div class="bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-700">Salary Range Distribution (Histogram)</h3>
          <span class="text-xs text-gray-500" v-if="!histogramBins.length">No histogram data.</span>
        </div>
        <div v-if="histogramBins.length">
          <VueECharts :option="histogramOption" style="height: 500px; width: 100%;" />
        </div>
        <div v-else class="text-sm text-gray-500 italic py-6 text-center">
          Not enough salary data to build a histogram.
        </div>
      </div>
    </div>
  </AppLayout>
</template>