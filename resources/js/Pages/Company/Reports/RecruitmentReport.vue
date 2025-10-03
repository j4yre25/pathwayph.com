<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import VueECharts from 'vue-echarts'
import { ref, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  dateRangeLabel: String,
  filters: Object,
  departments: Array,
  jobs: Array,
  experienceLevels: Array,
  kpis: Object,
  // Funnel
  stageCounts: Array,
  lineChart: Object,
  // Applicant Status
  statusCounts: Object,
  totalApplications: Number,
  shortlisted: Number,
  rolesFilled: Number,
  appStatusRows: Array,
  // Screening
  stackedCategories: Array,
  stackedSeries: Array,
  deptCategories: Array,
  deptSeries: Array,
  roleCategories: Array,
  roleSeries: Array,
  screeningSummary: Object,
  // Efficiency
  tthLine: Object,
  offerPie: Array,
  stageTimeByDept: Object,
  // Application Analysis
  summaryTable: Array,
  stackedBarSeries: Array,
  jobTitles: Array,
  scatterData: Array,
  months: Array,
  lineData: Array,
  areaSeries: Array,
})

/* Unified filter state */
const f = ref({
  department: props.filters?.department || '',
  job_id: props.filters?.job_id || '',
  experience_level: props.filters?.experience_level || '',
  stage: props.filters?.stage || '',
  date_preset: props.filters?.date_preset || 'last_90',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || ''
})

watch(f, () => {
  const q = { ...f.value }
  if (q.date_preset !== 'custom') { q.date_from = undefined; q.date_to = undefined }
  router.get(route('company.reports.recruitment'), q, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}, { deep: true })

function resetFilters() {
  f.value = {
    department: '',
    job_id: '',
    experience_level: '',
    stage: '',
    date_preset: 'last_90',
    date_from: '',
    date_to: ''
  }
}

/* Chart options (minimal styling) */
const funnelOption = computed(()=>({
  tooltip:{ trigger:'item', formatter:'{b}: {c}' },
  series:[{
    type:'funnel',
    top:10, bottom:10,
    sort:'none',
    gap:4,
    label:{ formatter:'{b}\n{c}' },
    data:(props.stageCounts||[]).map(s=>({ name:s.name, value:s.count }))
  }]
}))

const transitionLineOption = computed(()=>({
  tooltip:{ trigger:'axis' },
  xAxis:{ type:'category', data: props.lineChart?.labels || [] },
  yAxis:{ type:'value', name:'Avg Days' },
  series: props.lineChart?.series || []
}))

const statusPie = computed(()=>({
  tooltip:{ trigger:'item', formatter:'{b}: {c} ({d}%)' },
  series:[{
    type:'pie', radius:['40%','70%'],
    data:Object.entries(props.statusCounts||{}).map(([k,v])=>({ name:k, value:v }))
  }]
}))

const screeningStackedOption = computed(()=>({
  tooltip:{ trigger:'axis' },
  xAxis:{ type:'category', data: props.stackedCategories || [] },
  yAxis:{ type:'value' },
  series: props.stackedSeries || []
}))

const tthLineOption = computed(()=>({
  tooltip:{ trigger:'axis' },
  xAxis:{ type:'category', data: props.tthLine?.labels || [] },
  yAxis:{ type:'value', name:'Avg TTH (days)' },
  series: props.tthLine?.series || []
}))

const offerPieOption = computed(()=>({
  tooltip:{ trigger:'item', formatter:'{b}: {c} ({d}%)' },
  series:[{ type:'pie', radius:['45%','70%'], data: props.offerPie || [] }]
}))

const stageTimeOption = computed(()=>({
  tooltip:{ trigger:'axis' },
  legend:{ top:0 },
  xAxis:{ type:'category', data: props.stageTimeByDept?.categories || [] },
  yAxis:{ type:'value', name:'Count' },
  series: props.stageTimeByDept?.series || []
}))

const stackedBarOption = computed(()=>({
  tooltip:{ trigger:'axis' },
  legend:{ type:'scroll' },
  xAxis:{ type:'category', data: props.jobTitles || [] },
  yAxis:{ type:'value' },
  series: props.stackedBarSeries || []
}))

const scatterOption = computed(()=>({
  tooltip:{ trigger:'item', formatter: p => `${p.seriesName}<br/>Exp: ${p.data[0]} yrs<br/>Apps: ${p.data[1]}` },
  xAxis:{ type:'value', name:'Years Exp' },
  yAxis:{ type:'value', name:'Applications' },
  series: (props.jobTitles||[]).map(title => ({
    name:title,
    type:'scatter',
    data:(props.scatterData||[]).filter(d=>d[2]===title).map(d=>[d[0],d[1]])
  }))
}))

const appsTrendOption = computed(()=>({
  tooltip:{ trigger:'axis' },
  xAxis:{ type:'category', data: props.months || [] },
  yAxis:{ type:'value', name:'Applications' },
  series:[{ name:'Applications', type:'line', smooth:true, areaStyle:{}, data: props.lineData || [] }]
}))

const areaOption = computed(()=>({
  tooltip:{ trigger:'axis' },
  legend:{ type:'scroll' },
  xAxis:{ type:'category', data: props.months || [] },
  yAxis:{ type:'value' },
  series: props.areaSeries || []
}))

const kpiList = computed(()=>[
  { label:'Jobs', value: props.kpis?.total_jobs || 0, icon:'fa-briefcase', color:'text-indigo-600' },
  { label:'Applications', value: props.kpis?.total_applications || 0, icon:'fa-file-alt', color:'text-blue-600' },
  { label:'Shortlisted', value: props.kpis?.shortlisted || 0, icon:'fa-star', color:'text-amber-600' },
  { label:'Roles Filled', value: props.kpis?.roles_filled || 0, icon:'fa-user-check', color:'text-green-600' },
  { label:'Hired', value: props.kpis?.hired || 0, icon:'fa-user-tie', color:'text-emerald-600' },
])

</script>

<template>
  <AppLayout title="Recruitment Pipeline (Unified)">
    <div class="min-h-screen bg-gray-50 py-8 px-4">
      <div class="max-w-7xl mx-auto space-y-10">
        <!-- Unified Filters -->
        <div class="bg-white border border-gray-200 rounded-xl px-4 py-2 grid gap-4 md:grid-cols-9 lg:grid-cols-12 items-end mb-10">
          <div class="col-span-2">
            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Date Preset</label>
            <select v-model="f.date_preset" class="border rounded px-2 py-1 text-sm w-full">
              <option value="last_7">Last 7 Days</option>
              <option value="last_30">Last 30 Days</option>
              <option value="last_90">Last 90 Days</option>
              <option value="this_month">This Month</option>
              <option value="this_year">This Year</option>
              <option value="overall">Overall</option>
              <option value="custom">Custom</option>
            </select>
          </div>
          <div v-if="f.date_preset==='custom'">
            <label class="block text-[11px] font-semibold text-gray-600 mb-1">From</label>
            <input type="date" v-model="f.date_from" class="border rounded px-2 py-1 text-sm w-full" />
          </div>
          <div v-if="f.date_preset==='custom'">
            <label class="block text-[11px] font-semibold text-gray-600 mb-1">To</label>
            <input type="date" v-model="f.date_to" class="border rounded px-2 py-1 text-sm w-full" />
          </div>
          <div class="col-span-2">
            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Department</label>
            <select v-model="f.department" class="border rounded px-2 py-1 text-sm w-full">
              <option value="">All</option>
              <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
            </select>
          </div>
          <div class="col-span-2">
            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Job</label>
            <select v-model="f.job_id" class="border rounded px-2 py-1 text-sm w-full">
              <option value="">All</option>
              <option v-for="j in jobs" :key="j.id" :value="j.id">{{ j.job_title }}</option>
            </select>
          </div>
          <div class="col-span-2">
            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Experience</label>
            <select v-model="f.experience_level" class="border rounded px-2 py-1 text-sm w-full">
              <option value="">All</option>
              <option v-for="e in experienceLevels" :key="e" :value="e">{{ e }}</option>
            </select>
          </div>
          <div class="col-span-2">
            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Stage</label>
            <select v-model="f.stage" class="border rounded px-2 py-1 text-sm w-full">
              <option value="">All</option>
              <option value="applied">Applied</option>
              <option value="screening">Screening</option>
              <option value="assessment">Assessment</option>
              <option value="interview">Interview</option>
              <option value="offer">Offer</option>
              <option value="hired">Hired</option>
            </select>
          </div>
          <div class="flex justify-end">
            <button @click="resetFilters"
                    class="h-8 px-3 text-xs font-medium border border-gray-300 rounded-md text-gray-600 hover:bg-gray-100">
              Reset
            </button>
          </div>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-10">
          <div v-for="k in kpiList" :key="k.label"
               class="bg-white border border-gray-200 rounded-xl p-4 flex flex-col items-center text-center">
            <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center mb-2">
              <i :class="['fas', k.icon, k.color]"></i>
            </div>
            <div class="text-xs font-medium text-gray-500 tracking-wide">{{ k.label }}</div>
            <div :class="['mt-1 text-lg font-semibold', k.color]">{{ k.value }}</div>
          </div>
        </div>

        <!-- Row 1: Funnel + Transition Line + Status Pie -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
          <div class="bg-white border border-gray-200 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Pipeline Funnel</h3>
            <VueECharts :option="funnelOption" style="height:300px" />
          </div>
          <div class="bg-white border border-gray-200 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Stage Transitions (Avg Days)</h3>
            <VueECharts :option="transitionLineOption" style="height:300px" />
          </div>
          <div class="bg-white border border-gray-200 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Applicant Status Distribution</h3>
            <VueECharts :option="statusPie" style="height:300px" />
          </div>
        </div>

        <!-- Row 2: Screening Insights + TTH + Offer Pie -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-10">
          <div class="bg-white border border-gray-200 rounded-xl p-6 xl:col-span-2">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Screening Dimension Breakdown</h3>
            <VueECharts :option="screeningStackedOption" style="height:300px" />
          </div>
          <div class="space-y-8">
            <div class="bg-white border border-gray-200 rounded-xl p-6">
              <h3 class="text-sm font-semibold text-gray-700 mb-3">Time to Hire Trend</h3>
              <VueECharts :option="tthLineOption" style="height:200px" />
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-6">
              <h3 class="text-sm font-semibold text-gray-700 mb-3">Offer Acceptance</h3>
              <VueECharts :option="offerPieOption" style="height:200px" />
            </div>
          </div>
        </div>

        <!-- Row 3: Stage Time by Dept -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 mb-10">
          <h3 class="text-sm font-semibold text-gray-700 mb-3">Stage Presence by Department (Counts)</h3>
          <VueECharts :option="stageTimeOption" style="height:360px" />
        </div>

        <!-- Row 4: Application Analysis (Stacked Bar + Scatter) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
          <div class="bg-white border border-gray-200 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Applications per Job (Stage Breakdown)</h3>
            <VueECharts :option="stackedBarOption" style="height:320px" />
          </div>
          <div class="bg-white border border-gray-200 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Experience vs Applications (Scatter)</h3>
            <VueECharts :option="scatterOption" style="height:320px" />
          </div>
        </div>

        <!-- Row 5: Apps Trend + Area -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
          <div class="bg-white border border-gray-200 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Applications Over Time</h3>
            <VueECharts :option="appsTrendOption" style="height:300px" />
          </div>
          <div class="bg-white border border-gray-200 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Department Application Layers</h3>
            <VueECharts :option="areaOption" style="height:300px" />
          </div>
        </div>

        <!-- Applicant Table -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 mb-10">
          <h3 class="text-sm font-semibold text-gray-700 mb-3">Applicants (Filtered)</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
              <thead class="border-b">
                <tr class="text-gray-600">
                  <th class="py-2 pr-4 text-left">Applicant</th>
                  <th class="py-2 pr-4 text-left">Job Title</th>
                  <th class="py-2 pr-4 text-left">Department</th>
                  <th class="py-2 pr-4 text-left">Stage</th>
                  <th class="py-2 pr-4 text-left">Applied</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="r in appStatusRows" :key="r.id" class="border-b last:border-0">
                  <td class="py-2 pr-4 font-medium text-gray-800">{{ r.applicant_name }}</td>
                  <td class="py-2 pr-4">{{ r.job_title }}</td>
                  <td class="py-2 pr-4">{{ r.department || '—' }}</td>
                  <td class="py-2 pr-4">{{ r.stage_label }}</td>
                  <td class="py-2 pr-4">{{ r.applied_date }}</td>
                </tr>
                <tr v-if="!appStatusRows.length">
                  <td colspan="5" class="py-4 text-center text-gray-400">No applications</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Summary (basic) -->
        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 text-indigo-900 mb-10">
          <p class="text-sm">
            Unified recruitment analytics for period: <strong>{{ dateRangeLabel }}</strong>.
            Filters update every chart simultaneously.
          </p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>