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
  stageCounts: Array,
  stageTable: Array,
  lineChart: Object,
  statusCounts: Object,
  totalApplications: Number,
  shortlisted: [Number, Boolean],          // CHANGED: allow boolean
  rolesFilled: Number,
  appStatusRows: Array,
  stackedCategories: Array,
  stackedSeries: Array,
  deptCategories: Array,
  deptSeries: Array,
  roleCategories: Array,
  roleSeries: Array,
  screeningSummary: Object,
  tthLine: Object,
  offerPie: Array,
  stageTimeByDept: Object,
  summaryTable: Array,
  stackedBarSeries: Array,
  jobTitles: Array,
  scatterData: Array,
  months: Array,
  lineData: Array,
  areaSeries: Array,
})

/* --- Coerced numeric counts --- */
const shortlistedCount = computed(() =>
  typeof props.shortlisted === 'number' ? props.shortlisted : 0
)

/* ---------------- Filter State (Unified) ---------------- */
const f = ref({
  department: props.filters?.department || '',
  job_id: props.filters?.job_id || '',
  experience_level: props.filters?.experience_level || '',
  stage: props.filters?.stage || '',
  date_preset: props.filters?.date_preset || 'last_90',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || ''
})

const isLoading = ref(false)
let lastQuerySerialized = JSON.stringify(f.value)
let debounceTimer = null
const debounce = (fn, wait=400) => (...args) => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(()=>fn(...args), wait)
}

const triggerFetch = () => {
  const q = { ...f.value }
  if (q.date_preset !== 'custom') {
    q.date_from = undefined
    q.date_to = undefined
  }
  const serialized = JSON.stringify(q)
  if (serialized === lastQuerySerialized) return
  lastQuerySerialized = serialized
  isLoading.value = true
  router.get(route('company.reports.recruitment'), q, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    onFinish: () => { isLoading.value = false }
  })
}

watch(f, debounce(triggerFetch), { deep: true })

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

/* ------------- Series Sanitizer (prevents ECharts undefined errors) ------------- */
function sanitizeSeries(series, fallbackName, fallbackType='bar') {
  if (!Array.isArray(series) || !series.length) {
    return [{ name: fallbackName, type: fallbackType, data: [0] }]
  }
  return series
    .filter(s => s && typeof s === 'object')
    .map(s => {
      if (!s.type) {
        return { ...s, type: fallbackType, data: Array.isArray(s.data) ? s.data : [] }
      }
      if (!Array.isArray(s.data)) {
        return { ...s, data: [] }
      }
      return s
    })
}

/* ---------------- Chart Options ---------------- */
const funnelOption = computed(()=> {
  const data = (props.stageCounts||[]).length
    ? props.stageCounts.map(s=>({ name:s.name, value:Number(s.count)||0 }))
    : [{ name:'No Data', value:0 }]
  return {
    tooltip:{ trigger:'item', formatter:'{b}: {c}' },
    series:[{
      type:'funnel',
      top:10, bottom:10,
      sort:'none',
      gap:4,
      label:{ formatter:'{b}\n{c}' },
      data
    }]
  }
})

const transitionLineOption = computed(()=> {
  const labels = props.lineChart?.labels || []
  const rawSeries = props.lineChart?.series || []
  return {
    tooltip:{ trigger:'axis' },
    xAxis:{ type:'category', data: labels },
    yAxis:{ type:'value', name:'Avg Days' },
    series: sanitizeSeries(rawSeries, 'Avg Days', 'line')
  }
})

const statusPie = computed(()=> {
  const entries = Object.entries(props.statusCounts||{})
  const data = entries.length
    ? entries.map(([k,v])=>({ name:k, value:Number(v)||0 }))
    : [{ name:'No Data', value:0 }]
  return {
    tooltip:{ trigger:'item', formatter:'{b}: {c} ({d}%)' },
    series:[{
      type:'pie',
      radius:['42%','70%'],
      itemStyle:{ borderColor:'#fff', borderWidth:2 },
      data
    }]
  }
})

const screeningStackedOption = computed(()=> ({
  tooltip:{ trigger:'axis' },
  legend:{ top:0 },
  xAxis:{ type:'category', data: props.stackedCategories || [] },
  yAxis:{ type:'value' },
  series: sanitizeSeries(props.stackedSeries, 'No Data', 'bar')
}))

const tthLineOption = computed(()=> {
  const labels = props.tthLine?.labels || []
  const rawSeries = props.tthLine?.series || []
  return {
    tooltip:{ trigger:'axis' },
    xAxis:{ type:'category', data: labels },
    yAxis:{ type:'value', name:'Avg TTH (days)' },
    series: sanitizeSeries(rawSeries, 'Avg TTH (days)', 'line')
  }
})

const offerPieOption = computed(()=> {
  const data = (props.offerPie||[]).length
    ? props.offerPie.map(d=>({ name:d.name, value:Number(d.value)||0 }))
    : [{ name:'No Data', value:0 }]
  return {
    tooltip:{ trigger:'item', formatter:'{b}: {c} ({d}%)' },
    series:[{
      type:'pie',
      radius:['45%','70%'],
      itemStyle:{ borderColor:'#fff', borderWidth:2 },
      data
    }]
  }
})

const stageTimeOption = computed(()=> ({
  tooltip:{ trigger:'axis' },
  legend:{ top:0 },
  xAxis:{ type:'category', data: props.stageTimeByDept?.categories || [] },
  yAxis:{ type:'value', name:'Count' },
  series: sanitizeSeries(props.stageTimeByDept?.series, 'No Data', 'bar')
}))

const stackedBarOption = computed(()=> ({
  tooltip:{ trigger:'axis' },
  legend:{ type:'scroll', top:0 },
  grid:{ top:50, left:40, right:20, bottom:40 },
  xAxis:{ type:'category', data: props.jobTitles || [] },
  yAxis:{ type:'value' },
  series: sanitizeSeries(props.stackedBarSeries, 'No Data', 'bar')
}))

const scatterOption = computed(()=> {
  const titles = props.jobTitles || []
  let series = titles.map(title => ({
    name:title,
    type:'scatter',
    symbolSize: d => 8 + (d[1] ?? 0) * 0.8,
    data:(props.scatterData||[]).filter(d=>d[2]===title).map(d=>[d[0],d[1]])
  }))
  if (!series.length) {
    series = [{ name:'No Data', type:'scatter', data:[[0,0]] }]
  }
  return {
    tooltip:{ trigger:'item', formatter: p => `${p.seriesName}<br/>Experience: ${p.data[0]} yrs<br/>Applications: ${p.data[1]}` },
    xAxis:{ type:'value', name:'Years Exp' },
    yAxis:{ type:'value', name:'Applications' },
    series
  }
})

const appsTrendOption = computed(()=> ({
  tooltip:{ trigger:'axis' },
  xAxis:{ type:'category', data: props.months || [] },
  yAxis:{ type:'value', name:'Applications' },
  series: sanitizeSeries([{
    name:'Applications',
    type:'line',
    smooth:true,
    areaStyle:{ opacity:.15 },
    lineStyle:{ width:2 },
    data: props.lineData || []
  }], 'Applications', 'line')
}))

const areaOption = computed(()=> ({
  tooltip:{ trigger:'axis' },
  legend:{ type:'scroll', top:0 },
  xAxis:{ type:'category', data: props.months || [] },
  yAxis:{ type:'value' },
  series: sanitizeSeries(props.areaSeries, 'No Data', 'line')
}))

/* ---------------- KPI List ---------------- */
const kpiList = computed(()=>[
  { label:'Jobs', value: props.kpis?.total_jobs || 0, icon:'fa-briefcase', color:'text-indigo-600' },
  { label:'Applications', value: props.kpis?.total_applications || 0, icon:'fa-file-alt', color:'text-blue-600' },
  { label:'Shortlisted', value: shortlistedCount.value, icon:'fa-star', color:'text-amber-600' },
  { label:'Roles Filled', value: props.kpis?.roles_filled || 0, icon:'fa-user-check', color:'text-green-600' },
  { label:'Hired', value: props.kpis?.hired || 0, icon:'fa-user-tie', color:'text-emerald-600' },
])

/* ---------------- Presence Flags ---------------- */
const hasApplicants = computed(()=> (props.appStatusRows||[]).length > 0)
const hasStageTable = computed(()=> (props.stageTable||[]).length > 0)
const hasFunnel = computed(()=> (props.stageCounts||[]).some(s=>Number(s.count)>0))
</script>

<template>
  <AppLayout title="Recruitment Pipeline (Unified)">
    <div class="min-h-screen bg-gray-50 py-8 px-4 relative">
      <!-- Loading Overlay -->
      <transition name="fade">
        <div v-if="isLoading"
             class="absolute inset-0 bg-white/70 backdrop-blur-sm z-40 flex flex-col items-center justify-center">
          <div class="animate-spin h-9 w-9 rounded-full border-2 border-indigo-600 border-t-transparent mb-3"></div>
          <p class="text-xs font-medium text-indigo-700">Refreshing analytics...</p>
        </div>
      </transition>

      <div class="max-w-7xl mx-auto space-y-10">
        <!-- Unified Filters -->
        <div class="bg-white border border-gray-200 rounded-xl px-4 py-4 grid gap-4 md:grid-cols-9 lg:grid-cols-12 items-end shadow-sm">
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
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
          <div v-for="k in kpiList" :key="k.label"
               class="bg-white border border-gray-200 rounded-xl p-4 flex flex-col items-center text-center shadow-sm">
            <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center mb-2">
              <i :class="['fas', k.icon, k.color]"></i>
            </div>
            <div class="text-xs font-medium text-gray-500 tracking-wide">{{ k.label }}</div>
            <div :class="['mt-1 text-lg font-semibold', k.color]">{{ k.value }}</div>
          </div>
        </div>

        <!-- Stage Summary Table -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
          <h3 class="text-sm font-semibold text-gray-700 mb-4">Stage Summary (Filtered)</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full text-xs divide-y divide-gray-200" aria-label="Stage summary table">
              <thead class="bg-gray-50">
              <tr>
                <th class="px-3 py-2 text-left font-medium text-gray-600">Stage</th>
                <th class="px-3 py-2 text-left font-medium text-gray-600">Candidates</th>
                <th class="px-3 py-2 text-left font-medium text-gray-600">Top Department</th>
                <th class="px-3 py-2 text-left font-medium text-gray-600">Top Job Title</th>
                <th class="px-3 py-2 text-left font-medium text-gray-600">Conversion %</th>
                <th class="px-3 py-2 text-left font-medium text-gray-600">Avg Days to Next</th>
                <th class="px-3 py-2 text-left font-medium text-gray-600">Transition Feedback</th>
              </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
              <tr v-for="r in stageTable" :key="r.slug">
                <td class="px-3 py-2 font-medium text-gray-800">{{ r.name }}</td>
                <td class="px-3 py-2">{{ r.count }}</td>
                <td class="px-3 py-2">{{ r.top_department || '—' }}</td>
                <td class="px-3 py-2">{{ r.top_job || '—' }}</td>
                <td class="px-3 py-2">{{ r.conversion_percent }}%</td>
                <td class="px-3 py-2">
                  <span v-if="r.avg_days_to_next !== null">{{ r.avg_days_to_next }}</span>
                  <span v-else class="text-gray-400">—</span>
                </td>
                <td class="px-3 py-2">
                  <span :class="{
                    'text-green-600 font-medium': r.transition_feedback?.startsWith('Fast'),
                    'text-amber-600 font-medium': r.transition_feedback?.startsWith('Moderate'),
                    'text-red-600 font-medium': r.transition_feedback?.startsWith('Slow')
                  }">
                    {{ r.transition_feedback }}
                  </span>
                </td>
              </tr>
              <tr v-if="!hasStageTable">
                <td colspan="7" class="px-3 py-4 text-center text-gray-500">No data for current filters.</td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Applicant Table -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
          <h3 class="text-sm font-semibold text-gray-700 mb-3">Applicants (Filtered)</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full text-xs" aria-label="Applicants table">
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
                <td class="py-2 pr-4 font-medium text-gray-800 truncate max-w-[160px]">{{ r.applicant_name }}</td>
                <td class="py-2 pr-4 truncate max-w-[160px]">{{ r.job_title }}</td>
                <td class="py-2 pr-4 truncate max-w-[120px]">{{ r.department || '—' }}</td>
                <td class="py-2 pr-4">{{ r.stage_label }}</td>
                <td class="py-2 pr-4">{{ r.applied_date }}</td>
              </tr>
              <tr v-if="!hasApplicants">
                <td colspan="5" class="py-4 text-center text-gray-400">No applications</td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Row 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Pipeline Funnel</h3>
            <VueECharts :option="funnelOption" style="height:300px" />
            <p v-if="!hasFunnel" class="mt-3 text-[11px] text-gray-500">No funnel progression data in range.</p>
          </div>
          <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Stage Transitions (Avg Days)</h3>
            <VueECharts :option="transitionLineOption" style="height:300px" />
          </div>
          <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Applicant Status Distribution</h3>
            <VueECharts :option="statusPie" style="height:300px" />
          </div>
        </div>

        <!-- Row 2 -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
          <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm xl:col-span-2">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Screening Dimension Breakdown</h3>
            <VueECharts :option="screeningStackedOption" style="height:300px" />
          </div>
          <div class="space-y-8">
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
              <h3 class="text-sm font-semibold text-gray-700 mb-3">Time to Hire Trend</h3>
              <VueECharts :option="tthLineOption" style="height:200px" />
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
              <h3 class="text-sm font-semibold text-gray-700 mb-3">Offer Acceptance</h3>
              <VueECharts :option="offerPieOption" style="height:200px" />
            </div>
          </div>
        </div>

        <!-- Row 3 -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
          <h3 class="text-sm font-semibold text-gray-700 mb-3">Stage Presence by Department (Counts)</h3>
          <VueECharts :option="stageTimeOption" style="height:360px" />
        </div>

        <!-- Row 4 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Applications per Job (Stage Breakdown)</h3>
            <VueECharts :option="stackedBarOption" style="height:320px" />
          </div>
          <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Experience vs Applications (Scatter)</h3>
            <VueECharts :option="scatterOption" style="height:320px" />
          </div>
        </div>

        <!-- Row 5 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Applications Over Time</h3>
            <VueECharts :option="appsTrendOption" style="height:300px" />
          </div>
          <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Department Application Layers</h3>
            <VueECharts :option="areaOption" style="height:300px" />
          </div>
        </div>

        <!-- Summary -->
        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 text-indigo-900">
          <p class="text-sm">
            Unified recruitment analytics for period:
            <strong>{{ dateRangeLabel }}</strong>. All charts reflect the same filter context.
          </p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.fade-enter-active,.fade-leave-active { transition: opacity .25s ease }
.fade-enter-from,.fade-leave-to { opacity:0 }
</style>