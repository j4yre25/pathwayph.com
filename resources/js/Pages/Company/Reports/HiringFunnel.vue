<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import VueECharts from "vue-echarts";
import { ref, computed, watch } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
  stageCounts: { type:Array, default:()=>[] },   // unfiltered
  stageTable: { type:Array, default:()=>[] },     // filtered
  lineChart: { type:Object, default:()=>({labels:[],series:[]}) },
  summaryStats: { type:Object, default:()=>({}) },
  filters: { type:Object, default:()=>({}) },
  departments: { type:Array, default:()=>[] },
  jobs: { type:Array, default:()=>[] },
});

const local = ref({
  department: props.filters.department || "",
  job_id: props.filters.job_id || "",
  duration_band: props.filters.duration_band || ""
});

// Auto-submit on filter change (affects table + line chart only server-side)
watch(local, () => {
  router.get(route('company.reports.hiringFunnel'), {
    department: local.value.department || undefined,
    job_id: local.value.job_id || undefined,
    duration_band: local.value.duration_band || undefined,
  }, { preserveState:true, preserveScroll:true, replace:true });
}, { deep:true });

function resetFilters() {
  local.value = { department:"", job_id:"", duration_band:"" };
}

// Funnel chart (unchanged, uses unfiltered stageCounts)
const funnelOption = computed(()=>{
  const data = props.stageCounts.map(s=>({ name:s.name, value:s.count }));
  return {
    tooltip:{ trigger:'item', formatter: p => `${p.name}: ${p.value}` },
    series:[{
      type:'funnel',
      left:'10%', width:'80%',
      sort:'none',
      gap:4,
      // BEFORE: formatter:'{b}\\n{c}'
      label:{
        position:'inside',
        formatter: params => `${params.name}\n${params.value}`, // real newline
      },
      itemStyle:{ borderColor:'#fff', borderWidth:1 },
      data: data.length ? data : [{ name:'No Data', value:0 }]
    }]
  };
});

// Line chart (filtered)
const lineOption = computed(()=>({
  tooltip:{ trigger:'axis' },
  xAxis:{ type:'category', data: props.lineChart.labels },
  yAxis:{ type:'value', name:'Avg Days' },
  series: props.lineChart.series
}));

// Table rows already filtered server-side; we only show them
const rows = computed(()=> props.stageTable);

const interpretation = computed(() => {
  if (!rows.value.length) return 'No stage data for selected filters.';

  const total = props.summaryStats.total_candidates_first_stage ?? 0;
  const b = props.summaryStats.bottleneck_stage ?? 'N/A';
  const bd = props.summaryStats.bottleneck_avg_days ?? 'N/A';
  const band = props.filters.duration_band
    ? ` The data is currently filtered to show only **${props.filters.duration_band}** transitions.`
    : '';

  return `This report shows how candidates progress through each stage of the hiring pipeline, including the number of candidates per stage, their conversion rate from the first stage, and the average number of days before moving to the next stage.
  
Currently, there are **${total}** candidates who have entered the first stage. The slowest stage in the process is **${b}**, where candidates take an average of **${bd} days** to transition to the next stage, indicating a possible bottleneck.${band}

The line chart illustrates the average transition duration between consecutive stages, helping identify where delays occur. Use the filters above (Department, Job Title, and Transition Speed) to drill down and compare how different jobs or departments perform.

Monitoring these numbers regularly allows you to quickly identify where candidates are getting stuck and optimize those stages to speed up the hiring process.`;
});
</script>

<template>
  <AppLayout title="Hiring Funnel">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-10">
      <h2 class="text-2xl font-bold text-gray-800">Hiring Funnel</h2>

      <!-- FILTERS (real-time) -->
      <div class="bg-white rounded-xl shadow p-5 flex flex-wrap gap-5 items-end">
        <div>
          <label class="block text-[11px] font-semibold text-gray-600 mb-1">Department</label>
          <select v-model="local.department" class="border rounded px-2 py-1 text-sm w-44">
            <option value="">All</option>
            <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-semibold text-gray-600 mb-1">Job Title</label>
          <select v-model="local.job_id" class="border rounded px-2 py-1 text-sm w-48">
            <option value="">All</option>
            <option v-for="j in jobs" :key="j.id" :value="j.id">{{ j.job_title || j.title }}</option>
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-semibold text-gray-600 mb-1">Stage Transition Duration</label>
          <select v-model="local.duration_band" class="border rounded px-2 py-1 text-sm w-56">
            <option value="">All Speeds</option>
            <option value="fast">Fast (&lt; 5 days)</option>
            <option value="moderate">Moderate (5–10 days)</option>
            <option value="slow">Slow (&gt; 10 days)</option>
          </select>
        </div>
        <div class="ml-auto">
          <button @click="resetFilters"
          class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold px-3 py-2 rounded">
          Reset
        </button>
      </div>
      </div>
      <!-- TABLE (Filtered) -->
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-700">Stage Summary (Filtered)</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm divide-y divide-gray-200">
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
              <tr v-for="r in rows" :key="r.slug">
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
                    'text-green-600 font-medium': r.transition_feedback.startsWith('Fast'),
                    'text-amber-600 font-medium': r.transition_feedback.startsWith('Moderate'),
                    'text-red-600 font-medium': r.transition_feedback.startsWith('Slow')
                  }">
                    {{ r.transition_feedback }}
                  </span>
                </td>
              </tr>
              <tr v-if="!rows.length">
                <td colspan="7" class="px-3 py-4 text-center text-gray-500">No data for current filters.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- FUNNEL (Unfiltered) -->
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-700">Funnel: Candidates per Stage (All Data)</h3>
        <VueECharts :option="funnelOption" style="height:360px;width:100%;" />
      </div>

      <!-- LINE CHART (Filtered) -->
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-700">
          Average Days Between Consecutive Stages (Filtered)
        </h3>
        <VueECharts :option="lineOption" style="height:360px;width:100%;" />
        <p v-if="!lineOption.xAxis.data.length" class="text-xs text-gray-500 mt-2">
          No transition durations for current filters.
        </p>
      </div>


      <!-- INTERPRETATION -->
      <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-900 mb-6">
        <p class="font-semibold mb-1">📊 Summary Insight:</p>
        <p class="text-gray-700 whitespace-pre-line text-sm">
          {{ interpretation }}
        </p>
      </div>
    </div>
  </AppLayout>
</template>