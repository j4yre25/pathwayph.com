<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch, onMounted } from 'vue'
import VueECharts from 'vue-echarts'

const props = defineProps([
  'statusCounts',
  'employed',
  'unemployed',
  'programNames',
  'employedByProgram',
  'unemployedByProgram'
])

const programNames = props.programNames || []
const employedByProgram = props.employedByProgram || []
const unemployedByProgram = props.unemployedByProgram || []
const statusCounts = props.statusCounts
const employed = props.employed
const unemployed = props.unemployed


const barOption = ref({
  tooltip: { trigger: 'axis' },
  legend: { data: ['Employed', 'Unemployed'] },
  xAxis: { type: 'category', data: programNames },
  yAxis: { type: 'value' },
  series: [
    {
      name: 'Employed',
      type: 'bar',
      stack: 'total',
      emphasis: { focus: 'series' },
      data: employedByProgram,
      itemStyle: { color: '#22c55e' }
    },
    {
      name: 'Unemployed',
      type: 'bar',
      stack: 'total',
      emphasis: { focus: 'series' },
      data: unemployedByProgram,
      itemStyle: { color: '#ef4444' }
    }
  ]
})

const employmentRates = programNames.map((name, idx) => {
  const total = employedByProgram[idx] + unemployedByProgram[idx]
  return total ? (employedByProgram[idx] / total) : 0
})

// Prepare heatmap data: [programIndex, 0, rate]
const heatmapData = employmentRates.map((rate, idx) => [idx, 0, rate])

const heatmapOption = ref({
  tooltip: { formatter: params => `${programNames[params.data[0]]}: ${(params.data[2]*100).toFixed(1)}%` },
  xAxis: { type: 'category', data: programNames, splitArea: { show: true } },
  yAxis: { type: 'category', data: ['Employment Rate'], splitArea: { show: true } },
  visualMap: {
    min: 0,
    max: 1,
    calculable: true,
    orient: 'horizontal',
    left: 'center',
    bottom: '15%',
    inRange: { color: ['#fca5a5', '#22c55e'] }
  },
  series: [{
    name: 'Employment Rate',
    type: 'heatmap',
    data: heatmapData,
    label: { show: true, formatter: params => `${(params.data[2]*100).toFixed(1)}%` },
    emphasis: { itemStyle: { shadowBlur: 10, shadowColor: 'rgba(0,0,0,0.5)' } }
  }]
})

const pieOption = ref({

  tooltip: {
    trigger: 'item',
    formatter: '{b}: {c} ({d}%)'
  },
  legend: {
    orient: 'vertical',
    left: 'left'
  },
  series: [
    {
      name: 'Status',
      type: 'pie',
      radius: '60%',
      data: [],
      emphasis: {
        itemStyle: {
          shadowBlur: 10,
          shadowOffsetX: 0,
          shadowColor: 'rgba(0, 0, 0, 0.5)'
        }
      },
      label: {
        formatter: '{b}: {d}%', // Show label and percentage on the chart
        color: '#374151', // Tailwind gray-700
        fontWeight: 'bold'
      }
    }
  ]
})

const updateChart = () => {
  pieOption.value.series[0].data = Object.entries(props.statusCounts || {}).map(
    ([status, count]) => ({
      name: status,
      value: count
    })
  )
}

watch(() => props.statusCounts, updateChart, { immediate: true })
onMounted(updateChart)
</script>

<template>
  <AppLayout title="PESO Reports">
    <div class="max-w-5xl mx-auto py-8 px-4">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Employment Status Overview</h2>
      <!-- KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
          <span class="text-gray-500 text-sm">Employed</span>
          <span class="text-3xl font-bold text-green-600">{{ employed }}</span>
        </div>
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
          <span class="text-gray-500 text-sm">Unemployed</span>
          <span class="text-3xl font-bold text-red-600">{{ unemployed }}</span>
        </div>
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
          <span class="text-gray-500 text-sm">Total Graduates</span>
          <span class="text-3xl font-bold text-blue-600">{{ employed + unemployed }}</span>
        </div>
      </div>
      <div v-if="statusCounts && Object.keys(statusCounts).length" class="bg-white rounded-xl shadow p-8">
        <div class="flex flex-col lg:flex-row gap-12 items-center justify-between">
          <div class="w-full lg:w-2/5 mb-8 lg:mb-0">
            <h3 class="text-lg font-semibold mb-6 text-gray-700">Detailed Status</h3>
            <ul class="space-y-4">
              <li v-for="(count, status) in statusCounts" :key="status" class="flex justify-between items-center px-4 py-2 rounded hover:bg-gray-50 transition">
                <span class="capitalize text-gray-600">{{ status }}</span>
                <span class="font-semibold text-gray-900">{{ count }}</span>
              </li>
            </ul>
          </div>
          <div class="w-full lg:w-3/5 flex justify-center">
            <div class="bg-gray-50 rounded-lg p-4 w-full flex justify-center">
              <VueECharts
                v-if="pieOption.series[0].data.length"
                :option="pieOption"
                style="height: 350px; width: 100%; max-width: 420px;"
              />
              <div v-else class="text-gray-400 text-center py-8">No chart data available.</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Stacked Bar Chart -->
      <div class="bg-white rounded-xl shadow p-8 mt-12">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Employment by Program (Stacked Bar)</h3>
        <VueECharts :option="barOption" style="height: 400px; width: 100%;" />
      </div>

      <!-- Heatmap -->
      <div class="bg-white rounded-xl shadow p-8 mt-12">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Program Employment Rate Heatmap</h3>
        <VueECharts :option="heatmapOption" style="height: 200px; width: 100%;" />
      </div>
    </div>
  </AppLayout>
</template>