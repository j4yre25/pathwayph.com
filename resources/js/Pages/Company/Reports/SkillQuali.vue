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
  totals: { type: Object, default: () => ({ jobs: 0 }) },
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
const bubbleOption = {
  tooltip: { trigger: "item", formatter: p => `${p.data.name}<br>Demand: ${p.data.demand}<br>Supply: ${p.data.supply}` },
  xAxis: { name: "Demand", type: "value" },
  yAxis: { name: "Supply", type: "value" },
  series: [{
    type: "scatter",
    symbolSize: d => Math.max(20, Math.sqrt(d.demand + d.supply) * 8),
    data: (props.bubbleData || []).map(d => ({
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

        <div>
          <label class="block text-[11px] font-semibold mb-1 text-gray-600">Program (Optional)</label>
          <select v-model="local.program_id" class="border rounded px-2 py-1 text-sm w-56">
            <option value="">All</option>
            <option v-for="p in programs" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
        </div>

        <div class="ml-auto">
          <button @click="() => { local.department=''; local.job_id=''; local.program_id=''; local.date_preset='overall'; local.date_from=''; local.date_to=''; }"
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold px-3 py-2 rounded">
            Reset
          </button>
        </div>
      </div>

      <!-- Word Cloud + Top 10 Panel -->
      <div class="bg-white rounded-xl shadow p-6 grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <div class="lg:col-span-2">
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
      </div>

      <!-- Bubble Chart -->
      <div class="bg-white rounded-xl shadow p-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Bubble Chart: Skills Demand vs. Talent Pool</h3>
        <VueECharts :option="bubbleOption" style="height: 420px; width: 100%;" />
      </div>

      <!-- Graduate Skills Word Cloud -->
      <div class="bg-white rounded-xl shadow p-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Word Cloud: Most Common Graduate Skills</h3>
        <VueECharts :option="gradSkillWordCloudOption" style="height: 380px; width: 100%;" />
      </div>

      <!-- Radar Chart -->
      <div class="bg-white rounded-xl shadow p-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Radar Chart: Graduate Skills vs. Job Requirements</h3>
        <VueECharts :option="radarOption" style="height: 420px; width: 100%;" />
      </div>
    </div>
  </AppLayout>
</template>