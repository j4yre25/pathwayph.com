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
  shortlisted: [Number, Boolean],
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
  stageTableColumns: Array,
  stageTableVariant: String,
})

/* --- NEW: Tab system --- */
const tabs = [
  { key: 'pipeline', label: 'Recruitment Performance Report' },
  { key: 'analysis', label: 'Applicant Tracking and Screening Report' },
]
const activeTab = ref('pipeline')

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
  transition_feedback: props.filters?.transition_feedback || '',
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
    transition_feedback: '',
    date_preset: 'last_90',
    date_from: '',
    date_to: ''
  }
}

/* ------------- Series Normalizers (updated) ------------- */
function coerceNumericArray(arr = [], defaultLen = 0) {
  if (!Array.isArray(arr)) return Array.from({ length: defaultLen }, () => 0);
  return arr.map(v => {
    const n = Number(v);
    return Number.isFinite(n) ? n : 0;
  });
}

function sanitizeSeries(series, fallbackName, fallbackType = 'bar', expectedLen = 0) {
  if (!Array.isArray(series) || !series.length) {
    return [{
      name: fallbackName,
      type: fallbackType,
      data: Array.from({ length: expectedLen || 1 }, () => 0),
      smooth: fallbackType === 'line'
    }];
  }

  return series
    .filter(s => s && typeof s === 'object')
    .map(s => {
      const type = s.type || fallbackType;
      let data = s.data;
      // If someone passed a plain number or object, coerce
      if (!Array.isArray(data)) data = [data ?? 0];
      data = coerceNumericArray(data, expectedLen || data.length);
      return {
        name: s.name || fallbackName,
        type,
        smooth: type === 'line' ? (s.smooth !== false) : undefined,
        areaStyle: s.areaStyle,
        lineStyle: s.lineStyle,
        stack: s.stack,
        data
      };
    });
}

/* Transition Line (Average Days Between Consecutive Stages) */
const transitionLineOption = computed(() => {
  // Base labels from backend
  const base = Array.isArray(props.lineChart?.labels) ? props.lineChart.labels : [];

  // (Optional) attempt to derive extra labels if data length > labels length
  // For safety we just keep existing order; if mismatch we synthesize generic placeholders.
  const primarySeries = (props.lineChart?.series && props.lineChart.series[0]) || null;
  const dataLen = Array.isArray(primarySeries?.data) ? primarySeries.data.length : 0;

  let labels = [...base];

  if (dataLen > labels.length) {
    // Synthesize missing labels (keeps chart consistent instead of failing silently)
    for (let i = labels.length; i < dataLen; i++) {
      labels.push(`T${i + 1}`);
    }
  }

  // Ensure uniqueness & preserve order
  const seen = new Set();
  labels = labels.filter(l => {
    if (seen.has(l)) return false;
    seen.add(l);
    return true;
  });

  // Normalize series (assumes backend already supplies line series)
  const series = (props.lineChart?.series || []).map(s => {
    const data = Array.isArray(s.data) ? s.data : [];
    // Pad or trim data to match labels
    let norm = [...data];
    if (norm.length < labels.length) {
      norm = norm.concat(Array.from({ length: labels.length - norm.length }, () => 0));
    } else if (norm.length > labels.length) {
      norm = norm.slice(0, labels.length);
    }
    return {
      name: s.name || 'Avg Days',
      type: 'line',
      smooth: true,
      data: norm
    };
  });

  if (!series.length) {
    series.push({
      name: 'Avg Days',
      type: 'line',
      smooth: true,
      data: labels.map(() => 0)
    });
  }

  return {
    tooltip: { trigger: 'axis' },
    grid: { top: 40, left: 50, right: 20, bottom: 70, containLabel: true },
    xAxis: {
      type: 'category',
      data: labels,
      name: 'Stage Transition',
      nameLocation: 'middle',
      nameGap: 45,
      axisLabel: {
        interval: 0,          // Force show every label
        rotate: labels.length > 6 ? 35 : 0,
        formatter: (val) => {
          // Optional: wrap long labels (e.g. applied→screening)
            if (val.length > 18) {
              const mid = Math.ceil(val.length / 2);
              return val.slice(0, mid) + val.slice(mid);
            }
            return val;
        }
      }
    },
    yAxis: {
      type: 'value',
      name: 'Avg Days'
    },
    series
  };
});

/* Average Time-to-Hire (line) */
const tthLineOption = computed(() => {
  const labels = props.tthLine?.labels || [];
  const rawSeries = props.tthLine?.series || [];
  const series = sanitizeSeries(rawSeries, 'Avg TTH (days)', 'line', labels.length);
  return {
    tooltip: { trigger: 'axis' },
    xAxis: { type: 'category', data: labels },
    yAxis: { type: 'value', name: 'Avg TTH (days)' },
    series
  };
});

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

/* ---------------- Dynamic Insights ---------------- */
const pipelineInsight = computed(() => {
  // Stage counts / funnel
  const stages = props.stageCounts || [];
  const totalFirst = stages.length ? (stages[0].count || 0) : 0;
  const bottleneck = (props.stageTable || [])
    .filter(r => r && r.avg_days_to_next !== null)
    .sort((a,b)=> (b.avg_days_to_next ?? 0) - (a.avg_days_to_next ?? 0))[0];
  const fastest = (props.stageTable || [])
    .filter(r => r && r.avg_days_to_next !== null && (r.avg_days_to_next ?? 0) > 0)
    .sort((a,b)=> (a.avg_days_to_next ?? 0) - (b.avg_days_to_next ?? 0))[0];

  // Time-to-hire line (avg of latest window)
  const tthSeries = props.tthLine?.series || [];
  let overallTTH = null;
  if (tthSeries.length) {
    const allPoints = tthSeries.flatMap(s => Array.isArray(s.data) ? s.data : []);
    if (allPoints.length) {
      overallTTH = (allPoints.reduce((a,b)=>a + (Number(b)||0),0) / allPoints.length).toFixed(1);
    }
  }

  // Offer acceptance
  const offerData = props.offerPie || [];
  let offerRate = null;
  if (offerData.length) {
    const offerTotal = offerData.reduce((a,b)=> a + (Number(b.value)||0), 0);
    const accepted = offerData.find(d=> /accept/i.test(d.name))?.value || 0;
    if (offerTotal) offerRate = ((accepted/offerTotal)*100).toFixed(1);
  }

  // Dept stage time (identify slowest dept)
  let slowDept = null;
  if (props.stageTimeByDept?.series?.length) {
    // Flatten series to dept => total
    const categories = props.stageTimeByDept.categories || [];
    const series = props.stageTimeByDept.series;
    const deptTotals = categories.map((dept, idx) => {
      const sum = series.reduce((acc,s) => acc + (Number(s.data?.[idx])||0), 0);
      return { dept, sum };
    });
    slowDept = deptTotals.sort((a,b)=> b.sum - a.sum)[0];
  }

  const bnName = bottleneck?.name || 'N/A';
  const bnDays = bottleneck?.avg_days_to_next != null ? bottleneck.avg_days_to_next : '—';
  const fastName = fastest?.name || 'N/A';
  const fastDays = fastest?.avg_days_to_next != null ? fastest.avg_days_to_next : '—';
  const slowDeptTxt = slowDept ? `${slowDept.dept} (total stage presence ${slowDept.sum})` : 'N/A';
  const offerTxt = offerRate !== null ? `${offerRate}%` : 'N/A';
  const tthTxt = overallTTH !== null ? `${overallTTH} days` : 'N/A';

  return `Pipeline Overview:
- Candidates entering first stage: ${totalFirst}
- Bottleneck stage: ${bnName} (avg days to next: ${bnDays})
- Fastest advancing stage: ${fastName} (avg days: ${fastDays})
- Average Time-to-Hire (overall): ${tthTxt}
- Offer Acceptance Rate: ${offerTxt}
- Heaviest department load (stage presence): ${slowDeptTxt}

Interpretation:
Focus on reducing delays in ${bnName !== 'N/A' ? bnName : 'the slowest stage'} while preserving the efficiency of ${fastName !== 'N/A' ? fastName : 'faster stages'}. Monitor departments with heavier stage presence to prevent process drag and consider reallocating interviewer / reviewer bandwidth.`;
});

// Optional helper (place near other computeds)
const normalizedStackedForInsight = computed(() =>
  (props.stackedSeries || [])
    .filter(s => s && typeof s === 'object')
    .map(s => {
      const raw = Array.isArray(s.data) ? s.data : (s.data !== undefined ? [s.data] : []);
      const coerced = raw.map(v => Number(v) || 0);
      return { name: s.name || 'Series', data: coerced };
    })
);

const analysisInsight = computed(() => {
  // Status distribution
  const status = props.statusCounts || {};
  const entries = Object.entries(status)
    .map(([k,v]) => [k, Number(v)||0])
    .sort((a,b)=> b[1]-a[1]);
  const topStatus = entries[0] ? `${entries[0][0]} (${entries[0][1]})` : 'N/A';
  const totalApps = entries.reduce((a,[_k,v])=>a+v,0);

  // Screening stacked (dominant dimension) – use normalized series
  const stacked = normalizedStackedForInsight.value;
  let dominantDimension = 'N/A';
  if (stacked.length) {
    const seriesTotals = stacked.map(s => ({
      name: s.name,
      total: s.data.reduce((a,b)=>a+b,0)
    })).sort((a,b)=> b.total - a.total);
    dominantDimension = seriesTotals[0]?.name || 'N/A';
  }

  // Applications trend growth
  let trendGrowth = 'N/A';
  const line = Array.isArray(props.lineData) ? props.lineData.map(n=>Number(n)||0) : [];
  if (line.length > 3) {
    const seg = Math.ceil(line.length/3);
    const firstAvg = line.slice(0,seg).reduce((a,b)=>a+b,0)/seg;
    const lastAvg  = line.slice(-seg).reduce((a,b)=>a+b,0)/seg;
    if (firstAvg > 0) {
      trendGrowth = (((lastAvg - firstAvg)/firstAvg)*100).toFixed(1)+'%';
    }
  }

  // Top job by applications
  let topJob = 'N/A';
  if (Array.isArray(props.stackedBarSeries) && Array.isArray(props.jobTitles)) {
    const totalsPerJob = props.jobTitles.map((title, idx) => {
      const total = props.stackedBarSeries.reduce((acc,s)=>{
        const arr = Array.isArray(s.data)? s.data : [];
        return acc + (Number(arr[idx])||0);
      },0);
      return { title, total };
    }).sort((a,b)=> b.total - a.total);
    if (totalsPerJob[0] && totalsPerJob[0].total > 0) {
      topJob = `${totalsPerJob[0].title} (${totalsPerJob[0].total})`;
    }
  }

  // Median experience
  let medianExp = 'N/A';
  if (Array.isArray(props.scatterData) && props.scatterData.length) {
    const exps = props.scatterData.map(r=>Number(r?.[0])||0).sort((a,b)=>a-b);
    if (exps.length) {
      const mid = Math.floor(exps.length/2);
      medianExp = exps.length % 2 ? exps[mid] : ((exps[mid-1]+exps[mid])/2).toFixed(1);
    }
  }

  return `Applications & Screening Overview:
- Total Applications (range): ${totalApps}
- Top Status: ${topStatus}
- Dominant Screening Dimension: ${dominantDimension}
- Trend Growth (recent vs early period avg): ${trendGrowth}
- Top Job by Applications: ${topJob}
- Median Applicant Experience (years): ${medianExp}

Interpretation:
Leverage strengths in ${dominantDimension !== 'N/A' ? dominantDimension : 'the leading screening dimension'} while addressing any imbalance in status distribution. Monitor growth (${trendGrowth}) to forecast sourcing needs, emphasize high‑demand roles like ${topJob !== 'N/A' ? topJob : 'priority roles'}, and calibrate requirements to median experience (${medianExp}).`;
});

/* ---------------- Analysis Tab Dynamic Table Logic ---------------- */
const selectedJobTitle = computed(() => {
  if (!f.value.job_id) return null;
  const m = (props.jobs || []).find(j => j.id == f.value.job_id);
  return m ? m.job_title : null;
});

/*
  Variant precedence:
  1. job_id chosen -> by_job
  2. department chosen (and no job_id) -> by_department
  3. stage chosen (and no job_id / dept precedence) -> by_stage
  4. default -> full rows
*/
const analysisVariant = computed(() => {
  if (f.value.job_id) return 'by_job';
  if (f.value.department && !f.value.job_id) return 'by_department';
  if (f.value.stage && !f.value.job_id) return 'by_stage';
  return 'default';
});

const analysisColumns = computed(() => {
  switch (analysisVariant.value) {
    case 'by_job':
      return [
        { key: 'applicant_name', label: 'Applicant' },
        { key: 'stage_label', label: 'Stage' },
        { key: 'applied_date', label: 'Applied Date' },
      ];
    case 'by_department':
      return [
        { key: 'job_title', label: 'Job Title' },
        { key: 'candidates', label: 'Candidates' },
      ];
    case 'by_stage':
      return [
        { key: 'applicant_name', label: 'Applicant' },
        { key: 'job_title', label: 'Job Title' },
        { key: 'department', label: 'Department' },
        { key: 'applied_date', label: 'Applied Date' },
      ];
    default:
      return [
        { key: 'applicant_name', label: 'Applicant' },
        { key: 'job_title', label: 'Job Title' },
        { key: 'department', label: 'Department' },
        { key: 'stage_label', label: 'Stage' },
        { key: 'applied_date', label: 'Applied' },
      ];
  }
});

const analysisRows = computed(() => {
  const rows = props.appStatusRows || [];

  switch (analysisVariant.value) {
    case 'by_job':
      // Backend already filtered; just map
      return rows.map(r => ({
        applicant_name: r.applicant_name,
        stage_label: r.stage_label,
        applied_date: r.applied_date
      }));
    case 'by_department':
      // Aggregate by job_title (since department already filtered server-side)
      const counts = {};
      rows.forEach(r => {
        const jt = r.job_title || '—';
        counts[jt] = (counts[jt] || 0) + 1;
      });
      return Object.entries(counts).map(([job_title, candidates]) => ({ job_title, candidates }));
    case 'by_stage':
      // Backend already filtered to single stage
      return rows.map(r => ({
        applicant_name: r.applicant_name,
        job_title: r.job_title,
        department: r.department,
        applied_date: r.applied_date
      }));
    default:
      return rows;
  }
});

const analysisEmpty = computed(() => analysisRows.value.length === 0);
</script>

<template>
  <AppLayout title="Recruitment Report">
    <div class="min-h-screen bg-gray-50 py-8 px-4 relative">
      <!-- Loading Overlay -->
      <transition name="fade">
        <div v-if="isLoading"
             class="absolute inset-0 bg-white/70 backdrop-blur-sm z-40 flex flex-col items-center justify-center">
          <div class="animate-spin h-9 w-9 rounded-full border-2 border-indigo-600 border-t-transparent mb-3"></div>
          <p class="text-xs font-medium text-indigo-700">Refreshing analytics...</p>
        </div>
      </transition>

      <div class="max-w-7xl mx-auto space-y-8">
        <!-- Header & Tabs -->
        <div class="flex flex-col gap-4">
          <h1 class="text-2xl font-bold text-gray-800">Recruitment Report</h1>

          <!-- Tabs -->
          <div class="inline-flex rounded-lg overflow-hidden  w-full md:w-auto justify-center">
            <button
              v-for="t in tabs"
              :key="t.key"
              @click="activeTab = t.key"
              :class="[
                'px-5 py-2 text-sm font-semibold transition',
                activeTab === t.key
                  ? 'bg-white text-indigo-600 shadow'
                  : 'text-gray-500 hover:bg-gray-200'
              ]">
              {{ t.label }}
            </button>
          </div>
        </div>

        
        <!-- KPIs
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
          <div v-for="k in kpiList" :key="k.label"
          class="bg-white border border-gray-200 rounded-xl p-4 flex flex-col items-center text-center shadow-sm">
          <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center mb-2">
            <i :class="['fas', k.icon, k.color]"></i>
          </div>
          <div class="text-xs font-medium text-gray-500 tracking-wide">{{ k.label }}</div>
          <div :class="['mt-1 text-lg font-semibold', k.color]">{{ k.value }}</div>
        </div>
      </div> -->
      
      <!-- TAB 1: PIPELINE & EFFICIENCY -->
      <div v-if="activeTab === 'pipeline'" class="space-y-10">
        <!-- FILTER BAR (Pipeline Tab Only) -->
        <div v-if="activeTab==='pipeline'" class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm flex flex-wrap gap-4 items-end">
          <div>
            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Stage</label>
            <select v-model="f.stage" class="border rounded px-2 py-1 text-sm w-40">
              <option value="">All</option>
              <option v-for="s in ['applied','screening','assessment','interview','offer','hired']" :key="s" :value="s">
                {{ s.charAt(0).toUpperCase()+s.slice(1) }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Department</label>
            <select v-model="f.department" class="border rounded px-2 py-1 text-sm w-44">
              <option value="">All</option>
              <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Job</label>
            <select v-model="f.job_id" class="border rounded px-2 py-1 text-sm w-48">
              <option value="">All</option>
              <option v-for="j in jobs" :key="j.id" :value="j.id">{{ j.job_title }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Transition Feedback</label>
            <select v-model="f.transition_feedback" class="border rounded px-2 py-1 text-sm w-48">
              <option value="">All</option>
              <option value="Fast">Fast</option>
              <option value="Moderate">Moderate</option>
              <option value="Slow">Slow</option>
            </select>
          </div>
          <div class="ml-auto">
            <button @click="resetFilters"
              class="h-8 px-3 text-xs font-medium border border-gray-300 rounded-md text-gray-600 hover:bg-gray-100">
              Reset
            </button>
          </div>
        </div>

        <!-- Dynamic Stage Summary Table -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm mt-6">
          <h3 class="text-sm font-semibold text-gray-700 mb-4">
            Stage Summary ({{ props.stageTableVariant || 'default' }})
          </h3>
          <div class="overflow-x-auto">
            <table class="min-w-full text-xs divide-y divide-gray-200" aria-label="Dynamic stage summary table">
              <thead class="bg-gray-50">
                <tr>
                  <th v-for="col in (stageTableColumns || [])" :key="col.key"
                      class="px-3 py-2 text-left font-medium text-gray-600">
                    {{ col.label }}
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="(row,idx) in stageTable" :key="idx">
                  <td v-for="col in (stageTableColumns || [])" :key="col.key"
                      class="px-3 py-2"
                      :class="{
                        'font-medium text-gray-800': ['stage','department','job_title'].includes(col.key),
                        'text-green-600 font-medium': col.key==='transition_feedback' && (row[col.key]||'').startsWith('Fast'),
                        'text-amber-600 font-medium': col.key==='transition_feedback' && (row[col.key]||'').startsWith('Moderate'),
                        'text-red-600 font-medium': col.key==='transition_feedback' && (row[col.key]||'').startsWith('Slow')
                      }">
                    <span v-if="row[col.key] !== null && row[col.key] !== undefined">
                      {{ row[col.key] === '' ? '—' : row[col.key] }}
                    </span>
                    <span v-else class="text-gray-400">—</span>
                  </td>
                </tr>
                <tr v-if="!(stageTable || []).length">
                  <td :colspan="(stageTableColumns || []).length"
                      class="px-3 py-4 text-center text-gray-500">
                    No data for current filters.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Combined Recruitment Performance Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Stage Conversion Funnel -->
          <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Stage Conversion Funnel</h3>
            <VueECharts :option="funnelOption" style="height:300px" />
            <p v-if="!hasFunnel" class="mt-3 text-[11px] text-gray-500">No stage conversion data in range.</p>
          </div>
          <!-- Average Days Between Consecutive Stages -->
          <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm col-span-2">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Average Days Between Consecutive Stages</h3>
            <VueECharts :option="transitionLineOption" style="height:300px" />
            <p v-if="!transitionLineOption.xAxis.data.length"
              class="mt-2 text-[11px] text-gray-500">
              No transition data for current filters.
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Average Time-to-Hire -->
          <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm col-span-2">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Average Time-to-Hire</h3>
            <VueECharts :option="tthLineOption" style="height:300px" />
          </div>
          <!-- Offer Acceptance Rate -->
          <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Offer Acceptance Rate</h3>
            <VueECharts :option="offerPieOption" style="height:300px" />
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-1 gap-8">
          <!-- Average Time in Each Stage by Department -->
          <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Average Time in Each Stage by Department</h3>
            <VueECharts :option="stageTimeOption" style="height:300px" />
          </div>
        </div>

        <!-- Insight Panel -->
        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 text-emerald-900 whitespace-pre-line text-sm">
          <p class="font-semibold mb-1">📊 Performance Insight</p>
          {{ pipelineInsight }}
        </div>
      </div>

      <!-- TAB 2: APPLICATIONS / SCREENING / STATUS -->
      <div v-else-if="activeTab === 'analysis'" class="space-y-10">

        <!-- Analysis Filter Bar -->
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm flex flex-wrap gap-4 items-end">
          <div>
            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Date Preset</label>
            <select v-model="f.date_preset" class="border rounded px-2 py-1 text-sm w-40">
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
            <input type="date" v-model="f.date_from" class="border rounded px-2 py-1 text-sm w-44" />
          </div>
            <div v-if="f.date_preset==='custom'">
            <label class="block text-[11px] font-semibold text-gray-600 mb-1">To</label>
            <input type="date" v-model="f.date_to" class="border rounded px-2 py-1 text-sm w-44" />
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Department</label>
            <select v-model="f.department" class="border rounded px-2 py-1 text-sm w-48">
              <option value="">All</option>
              <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Job Title</label>
            <select v-model="f.job_id" class="border rounded px-2 py-1 text-sm w-56">
              <option value="">All</option>
              <option v-for="j in jobs" :key="j.id" :value="j.id">{{ j.job_title }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Stage</label>
            <select v-model="f.stage" class="border rounded px-2 py-1 text-sm w-40">
              <option value="">All</option>
              <option v-for="s in ['applied','screening','assessment','interview','offer','hired']" :key="s" :value="s">
                {{ s.charAt(0).toUpperCase()+s.slice(1) }}
              </option>
            </select>
          </div>
          <div class="ml-auto">
            <button @click="resetFilters"
              class="h-8 px-3 text-xs font-medium border border-gray-300 rounded-md text-gray-600 hover:bg-gray-100">
              Reset
            </button>
          </div>
        </div>

        <!-- Dynamic Applicants / Aggregated Table -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700">
              <span v-if="analysisVariant==='by_job'">Applicants for Job: {{ selectedJobTitle || '—' }}</span>
              <span v-else-if="analysisVariant==='by_department'">Job Titles in Department: {{ f.department }}</span>
              <span v-else-if="analysisVariant==='by_stage'">Applicants in Stage: {{ f.stage }}</span>
              <span v-else>Applicants (Filtered)</span>
            </h3>
            <span class="text-[11px] text-gray-500">{{ analysisRows.length }} result(s)</span>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full text-xs" aria-label="Applicants dynamic table">
              <thead class="border-b bg-gray-50">
                <tr class="text-gray-600">
                  <th v-for="c in analysisColumns" :key="c.key" class="py-2 pr-4 text-left font-medium">
                    {{ c.label }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(r, idx) in analysisRows" :key="idx" class="border-b last:border-0">
                  <td v-for="c in analysisColumns" :key="c.key" class="py-2 pr-4">
                    {{ r[c.key] ?? '—' }}
                  </td>
                </tr>
                <tr v-if="analysisEmpty">
                  <td :colspan="analysisColumns.length" class="py-4 text-center text-gray-400">
                    No data for current filters.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <p class="mt-3 text-[11px] text-gray-500">
            View adapts to active filter:
            <span class="font-semibold">Job Title</span> → applicant list;
            <span class="font-semibold">Department</span> → job title counts;
            <span class="font-semibold">Stage</span> → applicant details; otherwise full table.
          </p>
        </div>

        <!-- Applicant Status & Screening -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Applicant Status Distribution</h3>
            <VueECharts :option="statusPie" style="height:300px" />
          </div>
          <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm lg:col-span-2">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Screening Dimension Breakdown</h3>
            <VueECharts :option="screeningStackedOption" style="height:300px" />
          </div>
        </div>

        <!-- Application Analysis (per job) & Experience Scatter -->
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

        <!-- Trends -->
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

        <!-- Insight Panel -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-blue-900 whitespace-pre-line text-sm">
          <p class="font-semibold mb-1">📈 Applications & Screening Insight</p>
          {{ analysisInsight }}
        </div>
      </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.fade-enter-active,.fade-leave-active { transition: opacity .25s ease }
.fade-enter-from,.fade-leave-to { opacity:0 }
</style>