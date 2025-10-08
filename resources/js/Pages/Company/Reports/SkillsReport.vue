<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import VueECharts from "vue-echarts";
import { ref, watch, computed } from "vue";
import { router } from "@inertiajs/vue3";


const props = defineProps({
  skillWordCloud: Object,
  certWordCloud: Object,
  bubbleData: Array,
  gradSkillWordCloud: Object,
  radarIndicators: Array,
  jobSkillValues: Array,
  gradSkillValues: Array,
  filters: { type: Object, default: () => ({}) },
  departments: { type: Array, default: () => [] },
  jobs: { type: Array, default: () => [] },
  programs: { type: Array, default: () => [] },
  totals: { type: Object, default: () => ({ jobs: 0, graduates: 0 }) },
  skillAnalytics: { type: Array, default: () => [] },
});

// Filters (UI)
const local = ref({
  department: props.filters.department || "",
  job_id: props.filters.job_id || "",
  program_id: props.filters.program_id || "",
  date_preset: props.filters.date_preset || "overall",
  date_from: props.filters.date_from || "",
  date_to: props.filters.date_to || "",
});

watch(local, () => {
  const q = { ...local.value };
  if (q.date_preset !== "custom") {
    q.date_from = undefined;
    q.date_to = undefined;
  }
  router.get(route("company.reports.skills"), q, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
}, { deep: true });

watch(() => props.jobs, (opts) => {
  const ids = new Set((opts || []).map(o => String(o.id)));
  if (local.value.job_id && !ids.has(String(local.value.job_id))) {
    local.value.job_id = "";
  }
});
watch(() => props.programs, (opts) => {
  const ids = new Set((opts || []).map(o => String(o.id)));
  if (local.value.program_id && !ids.has(String(local.value.program_id))) {
    local.value.program_id = "";
  }
});

// Helpers
const totalJobs = computed(() => Number(props.totals?.jobs ?? 0));
const totalGraduates = computed(() => Number(props.totals?.graduates ?? 0));

// Top 10 skills side panel
const topSkills = computed(() => {
  const entries = Object.entries(props.skillWordCloud || {});
  const sorted = entries.sort((a,b)=> b[1]-a[1]).slice(0,10);
  return sorted.map(([name, count]) => ({
    name,
    count: Number(count || 0),
    pct: totalJobs.value ? Math.round((Number(count||0) / totalJobs.value) * 1000) / 10 : 0,
  }));
});

// Word Cloud options (with tooltip showing frequency and % of jobs)
const makeWCData = obj => Object.entries(obj || {})
  .filter(([n,v]) => n && Number(v) > 0)
  .map(([name, value]) => ({ name, value: Number(value) }));

const wcTooltipFormatter = (p) => {
  const cnt = Number(p.data?.value ?? 0);
  const pct = totalJobs.value ? ((cnt / totalJobs.value) * 100).toFixed(1) : "0.0";
  return `${p.name}<br/>Required in ${cnt} job(s) (${pct}%)`;
};

const skillWordCloudOption = computed(() => ({
  tooltip: { trigger: "item", formatter: wcTooltipFormatter },
  series: [{
    type: 'wordCloud',
    shape: 'circle',
    left: 'center',
    top: 'center',
    width: '90%',
    height: '90%',
    sizeRange: [12, 50],
    rotationRange: [-45, 90],
    gridSize: 8,
    drawOutOfBound: false,
    textStyle: { fontFamily: 'sans-serif' },
    data: makeWCData(props.skillWordCloud),
  }]
}));

const certWordCloudOption = computed(() => ({
  tooltip: { trigger: "item", formatter: (p) => `${p.name}<br/>Mentioned in ${p.data?.value ?? 0} job(s)` },
  series: [{
    type: 'wordCloud',
    shape: 'circle',
    left: 'center',
    top: 'center',
    width: '90%',
    height: '90%',
    sizeRange: [12, 50],
    rotationRange: [-45, 90],
    gridSize: 8,
    drawOutOfBound: false,
    textStyle: { fontFamily: 'sans-serif' },
    data: makeWCData(props.certWordCloud),
  }]
}));

const gradSkillWordCloudOption = computed(() => ({
  tooltip: { trigger: "item", formatter: (p) => `${p.name}<br/>Found in ${p.data?.value ?? 0} graduate(s)` },
  series: [{
    type: 'wordCloud',
    shape: 'circle',
    left: 'center',
    top: 'center',
    width: '90%',
    height: '90%',
    sizeRange: [12, 50],
    rotationRange: [-45, 90],
    gridSize: 8,
    drawOutOfBound: false,
    textStyle: { fontFamily: 'sans-serif' },
    data: makeWCData(props.gradSkillWordCloud),
  }]
}));

// Bubble Chart
const bubbleOption = computed(() => {
  const raw = Array.isArray(props.bubbleData) ? props.bubbleData : [];
  const data = raw.map(d => {
    const demand = Number(d.demand) || 0;
    const supply = Number(d.supply) || 0;
    return {
      value: [demand, supply],
      name: d.name,
      demand,
      supply
    };
  });

  const maxDemand = Math.max(0, ...data.map(d => d.demand));
  const maxSupply = Math.max(0, ...data.map(d => d.supply));

  return {
    tooltip: {
      trigger: 'item',
      formatter: p => `${p.data.name}<br/>Demand: ${p.data.demand}<br/>Supply: ${p.data.supply}`
    },
    xAxis: {
      name: 'Demand',
      type: 'value',
      min: 0,
      max: maxDemand + 1,
      interval: 1,
      axisLabel: { formatter: v => Math.round(v) }
    },
    yAxis: {
      name: 'Supply',
      type: 'value',
      min: 0,
      max: maxSupply + 1,
      interval: 1,
      axisLabel: { formatter: v => Math.round(v) }
    },
    series: [{
      type: 'scatter',
      symbolSize: p => {
        const demand = p[0];
        const supply = p[1];
        const base = Math.sqrt(demand + supply) * 5;
        return Math.max(14, Math.round(base));
      },
      data,
      label: {
        show: true,
        formatter: p => p.data.name,
        position: 'top',
        fontSize: 10
      }
    }]
  };
});

// Radar Chart
const radarOption = {
  tooltip: {},
  legend: { data: ["Job Requirements", "Graduate Pool"] },
  radar: {
    indicator: (props.radarIndicators && props.radarIndicators.length
      ? props.radarIndicators.map(i => ({ ...i, max: Math.max(i.max, 5) }))
      : [{ name: 'N/A', max: 5 }]
    ),
    radius: 90,
  },
  series: [{
    type: "radar",
    data: [
      { value: (props.jobSkillValues && props.jobSkillValues.length ? props.jobSkillValues : [0]), name: "Job Requirements" },
      { value: (props.gradSkillValues && props.gradSkillValues.length ? props.gradSkillValues : [0]), name: "Graduate Pool" }
    ]
  }]
};

// Table state
const sortState = ref({ key: 'job_freq', dir: 'desc' });

const sortableCols = [
  { key:'skill', label:'Skill Name' },
  { key:'job_freq', label:'Frequency in Job Postings' },
  { key:'grad_freq', label:'Frequency Among Graduates' },
  { key:'demand_pct', label:'Demand %' },
  { key:'supply_pct', label:'Supply %' },
  { key:'top_department', label:'Most Common in Department' },
  { key:'gap', label:'Gap Indicator' },
];

function toggleSort(col) {
  if (sortState.value.key === col) {
    sortState.value.dir = sortState.value.dir === 'asc' ? 'desc' : 'asc';
  } else {
    sortState.value.key = col;
    sortState.value.dir = 'desc';
  }
}

const filteredSkills = computed(() => {
  let rows = (props.skillAnalytics || []).map(r => ({
    skill: r.skill,
    job_freq: r.job_freq ?? 0,
    grad_freq: r.grad_freq ?? 0,
    demand_pct: r.demand_pct ?? 0,
    supply_pct: r.supply_pct ?? 0,
    top_department: r.top_department ?? '—',
    gap: r.gap ?? 'Balanced'
  }));
  const { key, dir } = sortState.value;
  rows.sort((a,b) => {
    const av = a[key] ?? '';
    const bv = b[key] ?? '';
    if (typeof av === 'number' && typeof bv === 'number') {
      return dir === 'asc' ? av - bv : bv - av;
    }
    return dir === 'asc'
      ? String(av).localeCompare(String(bv))
      : String(bv).localeCompare(String(av));
  });
  return rows;
});

// Pagination (appears only if > 10 rows)
const page = ref(1);
const pageSize = 10;
const totalPages = computed(() => Math.max(1, Math.ceil(filteredSkills.value.length / pageSize)));
watch(filteredSkills, () => { if (page.value > totalPages.value) page.value = 1; });
const paginatedSkills = computed(() => {
  const start = (page.value - 1) * pageSize;
  return filteredSkills.value.slice(start, start + pageSize);
});
function goto(p) {
  if (p < 1 || p > totalPages.value) return;
  page.value = p;
}
function gapClass(gap) {
  if (gap === 'High Demand Gap') return 'text-red-600 font-semibold';
  if (gap === 'Oversupply') return 'text-indigo-600 font-semibold';
  if (gap === 'Balanced') return 'text-green-600 font-medium';
  return 'text-gray-500';
}

// ---- ADD (after filteredSkills / pagination declarations) a dynamic summary insight like RecruitmentReport ----
const skillSummaryInsight = computed(() => {
  const rows = filteredSkills.value;
  if (!rows.length) {
    return 'No skill analytics available for the current filters.\n\nTip:\nAdjust date range or broaden filters to populate insights.';
  }

  // Core aggregates
  const total = rows.length;
  const totalJobsVal = totalJobs.value;
  const totalGradsVal = totalGraduates.value;

  // Top demand skill (by job_freq)
  const topDemand = [...rows].sort((a,b)=>b.job_freq - a.job_freq)[0];

  // Largest gap (High Demand Gap) => highest (demand_pct - supply_pct)
  const gapCandidates = rows
    .filter(r => r.gap === 'High Demand Gap')
    .map(r => ({ r, diff: r.demand_pct - r.supply_pct }));
  const largestGap = gapCandidates.sort((a,b)=> b.diff - a.diff)[0]?.r || null;

  // Oversupply (supply surplus) => highest (supply_pct - demand_pct) among Oversupply
  const oversupplyCandidates = rows
    .filter(r => r.gap === 'Oversupply')
    .map(r => ({ r, diff: r.supply_pct - r.demand_pct }));
  const largestOversupply = oversupplyCandidates.sort((a,b)=> b.diff - a.diff)[0]?.r || null;

  // Balanced count
  const balancedCount = rows.filter(r => r.gap === 'Balanced').length;

  // Averages
  const avgDemand = rows.reduce((a,b)=>a + (b.demand_pct||0),0) / total;
  const avgSupply = rows.reduce((a,b)=>a + (b.supply_pct||0),0) / total;

  // Top department concentration (frequency of appearing as top_department)
  const deptFreqMap = {};
  rows.forEach(r => {
    if (r.top_department && r.top_department !== '—') {
      deptFreqMap[r.top_department] = (deptFreqMap[r.top_department] || 0) + 1;
    }
  });
  const topDept = Object.entries(deptFreqMap)
    .sort((a,b)=> b[1]-a[1])[0];

  const topDeptName = topDept ? `${topDept[0]} (${topDept[1]} skills)` : 'N/A';

  const formatSkill = (s) => s
    ? `${s.skill} (Demand ${s.demand_pct}%, Supply ${s.supply_pct}%)`
    : 'N/A';

  return `Skill Market Overview:
- Filtered Jobs: ${totalJobsVal} | Graduates: ${totalGradsVal} | Skill Rows: ${total}
- Top Demand Skill: ${topDemand ? `${topDemand.skill} (${topDemand.job_freq} postings)` : 'N/A'}
- Largest Demand Gap: ${formatSkill(largestGap)}
- Largest Oversupply: ${formatSkill(largestOversupply)}
- Balanced Skills: ${balancedCount} (${((balancedCount/total)*100).toFixed(1)}%)
- Avg Demand % (skills): ${avgDemand.toFixed(2)}%
- Avg Supply % (skills): ${avgSupply.toFixed(2)}%
- Most Frequent Top Department: ${topDeptName}

Interpretation:
Use gap skills (e.g., ${largestGap ? largestGap.skill : '—'}) for targeted upskilling or recruiting initiatives. Oversupply skills (e.g., ${largestOversupply ? largestOversupply.skill : '—'}) indicate potential to refine job specs or redeploy talent.
Balanced distribution (${balancedCount} skills) suggests stable alignment; monitor shifts in average demand (${avgDemand.toFixed(1)}%) vs supply (${avgSupply.toFixed(1)}%). Department concentration (${topDeptName}) may guide cross‑training or diversification efforts.`;
});
</script>

<template>
  <AppLayout title="Skills and Qualifications">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-10">
      <h2 class="text-2xl font-bold text-gray-800">Skills and Qualifications</h2>

      <!-- Filters -->
      <div class="bg-white rounded-xl shadow p-5 flex flex-wrap gap-5 items-end">
        <div>
          <label class="block text-[11px] font-semibold mb-1 text-gray-600">Date Range</label>
          <select v-model="local.date_preset" class="border rounded px-2 py-1 text-sm w-44">
            <option value="last_30">Last 30 Days</option>
            <option value="last_90">Last 90 Days</option>
            <option value="this_year">This Year</option>
            <option value="overall">Overall</option>
            <option value="custom">Custom</option>
          </select>
        </div>
        <div v-if="local.date_preset==='custom'">
          <label class="block text-[11px] font-semibold mb-1 text-gray-600">From</label>
          <input type="date" v-model="local.date_from" class="border rounded px-2 py-1 text-sm" />
        </div>
        <div v-if="local.date_preset==='custom'">
          <label class="block text-[11px] font-semibold mb-1 text-gray-600">To</label>
          <input type="date" v-model="local.date_to" class="border rounded px-2 py-1 text-sm" />
        </div>

        <div>
          <label class="block text-[11px] font-semibold mb-1 text-gray-600">Department</label>
          <select v-model="local.department" class="border rounded px-2 py-1 text-sm w-56">
            <option value="">All</option>
            <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
          </select>
        </div>

        <div>
          <label class="block text-[11px] font-semibold mb-1 text-gray-600">Job Posting / Position</label>
          <select v-model="local.job_id" class="border rounded px-2 py-1 text-sm w-60">
            <option value="">All</option>
            <option v-for="j in jobs" :key="j.id" :value="j.id">{{ j.job_title }}</option>
          </select>
        </div>

        <!-- <div>
          <label class="block text-[11px] font-semibold mb-1 text-gray-600">Program (Optional)</label>
          <select v-model="local.program_id" class="border rounded px-2 py-1 text-sm w-56">
            <option value="">All</option>
            <option v-for="p in programs" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
        </div> -->

        <div class="ml-auto">
          <button @click="() => { local.department=''; local.job_id=''; local.program_id=''; local.date_preset='overall'; local.date_from=''; local.date_to=''; }"
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold px-3 py-2 rounded">
            Reset
          </button>
        </div>
      </div>
      
      <!-- Skill Analytics Table -->
      <div class="bg-white rounded-xl shadow p-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
          <div>
            <h3 class="text-lg font-semibold text-gray-700">Skill Analytics</h3>
            <p class="text-xs text-gray-500 mt-1">
              Jobs: {{ totalJobs }} • Graduates: {{ totalGraduates }} • Rows: {{ filteredSkills.length }}
            </p>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-xs">
            <thead class="bg-gray-50 border-b">
              <tr>
                <th
                  v-for="col in sortableCols"
                  :key="col.key"
                  class="px-3 py-2 text-left font-semibold text-gray-600 cursor-pointer select-none"
                  @click="toggleSort(col.key)"
                >
                  <span class="inline-flex items-center gap-1">
                    {{ col.label }}
                    <span v-if="sortState.key === col.key">
                      <i v-if="sortState.dir==='asc'" class="fas fa-sort-up text-[10px]"></i>
                      <i v-else class="fas fa-sort-down text-[10px]"></i>
                    </span>
                  </span>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="row in paginatedSkills"
                :key="row.skill"
                class="border-b last:border-0 hover:bg-gray-50"
              >
                <td class="px-3 py-2 font-medium text-gray-800 capitalize">{{ row.skill }}</td>
                <td class="px-3 py-2">{{ row.job_freq }}</td>
                <td class="px-3 py-2">{{ row.grad_freq }}</td>
                <td class="px-3 py-2">{{ row.demand_pct }}%</td>
                <td class="px-3 py-2">{{ row.supply_pct }}%</td>
                <td class="px-3 py-2">{{ row.top_department }}</td>
                <td class="px-3 py-2">
                  <span :class="gapClass(row.gap)">
                    {{ row.gap }}
                  </span>
                </td>
              </tr>
              <tr v-if="!paginatedSkills.length">
                <td :colspan="sortableCols.length" class="px-3 py-6 text-center text-gray-500">
                  No data.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="filteredSkills.length > pageSize" class="mt-4 flex flex-wrap items-center gap-2 text-xs">
          <span class="text-gray-600">
            Showing {{ ((page-1)*pageSize)+1 }} -
            {{ Math.min(page*pageSize, filteredSkills.length) }} of {{ filteredSkills.length }}
            (Page {{ page }} / {{ totalPages }})
          </span>
          <div class="ml-auto flex items-center gap-1">
            <button
              @click="goto(page-1)"
              :disabled="page===1"
              class="px-2 py-1 border rounded disabled:opacity-40 hover:bg-gray-100">
              Prev
            </button>
            <button
              v-for="p in totalPages"
              :key="p"
              @click="goto(p)"
              class="px-2 py-1 border rounded hover:bg-gray-100"
              :class="p===page ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-700'">
              {{ p }}
            </button>
            <button
              @click="goto(page+1)"
              :disabled="page===totalPages"
              class="px-2 py-1 border rounded disabled:opacity-40 hover:bg-gray-100">
              Next
            </button>
          </div>
        </div>
      </div>
      <!-- Word Cloud + Top 10 Panel -->
      <div class="bg-white rounded-xl shadow p-6 grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <div class="lg:col-span-1">
          <h3 class="text-lg font-semibold mb-4 text-gray-700">Word Cloud: Most Required Skills</h3>
          <VueECharts :option="skillWordCloudOption" style="height: 380px; width: 100%;" />
          <p class="text-xs text-gray-500 mt-2">Total filtered jobs: {{ totalJobs }}</p>
        </div>
        <div class="lg:col-span-1">
          <h4 class="text-sm font-semibold mb-2 text-gray-700">Top 10 Skills</h4>
          <div class="text-xs text-gray-700 space-y-1">
            <div v-for="s in topSkills" :key="s.name" class="flex items-center justify-between border-b py-1">
              <span class="truncate mr-2">{{ s.name }}</span>
              <span class="text-gray-600">{{ s.count }} ({{ s.pct }}%)</span>
            </div>
            <div v-if="!topSkills.length" class="text-gray-500">No data.</div>
          </div>
        </div>
        <!-- Graduate Skills Word Cloud -->
        <div class="lg:col-span-1">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Word Cloud: Most Common Graduate Skills</h3>
          <VueECharts :option="gradSkillWordCloudOption" style="height: 380px; width: 100%;" />
        </div>
      </div>

      <div class="bg-white rounded-xl shadow p-6 grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Bubble Chart -->
        <div class="lg:col-span-2">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Bubble Chart: Skills Demand vs. Talent Pool</h3>
          <VueECharts :option="bubbleOption" style="height: 420px; width: 100%;" />
        </div>


        <!-- Radar Chart -->
        <div class="lg:col-span-1">
          <h3 class="text-lg font-semibold mb-6 text-gray-700">Radar Chart: Graduate Skills vs. Job Requirements</h3>
          <VueECharts :option="radarOption" style="height: 420px; width: 100%;" />
        </div>
      </div>

      <!-- Dynamic Insight Panel -->
      <div class="mt-6 bg-amber-50 border border-amber-200 rounded-lg p-4 text-amber-900 whitespace-pre-line text-sm">
        <p class="font-semibold mb-1">🧠 Skill Market Insight</p>
        {{ skillSummaryInsight }}
      </div>
    </div>
  </AppLayout>
</template>