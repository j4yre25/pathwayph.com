<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import VueECharts from "vue-echarts";
import { ref, computed, watch } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
  stackedCategories: { type:Array, default:()=>[] },
  stackedSeries: { type:Array, default:()=>[] },
  deptCategories: { type:Array, default:()=>[] },
  deptSeries: { type:Array, default:()=>[] },
  roleCategories: { type:Array, default:()=>[] },
  roleSeries: { type:Array, default:()=>[] },
  filters: { type:Object, default:()=>({}) },
  departments: { type:Array, default:()=>[] },
  jobs: { type:Array, default:()=>[] },
  experienceLevels: { type:Array, default:()=>[] },
  summary: { type:Object, default:()=>({}) }
});

const local = ref({
  department: props.filters.department || "",
  job_id: props.filters.job_id || "",
  outcome: props.filters.outcome || "",
  experience_level: props.filters.experience_level || "",
  date_preset: props.filters.date_preset || "last_30",
  date_from: props.filters.date_from || "",
  date_to: props.filters.date_to || ""
});

watch(local, () => {
  const q = { ...local.value };
  if (q.date_preset !== 'custom') {
    q.date_from = undefined;
    q.date_to = undefined;
  }
  router.get(route('company.reports.screening'), q, {
    preserveState:true, preserveScroll:true, replace:true
  });
}, { deep:true });

function resetFilters(){
  local.value = {
    department:"", job_id:"", outcome:"", experience_level:"",
    date_preset:"last_30", date_from:"", date_to:""
  };
}

// Charts
const stackedOption = computed(()=>({
  tooltip:{ trigger:'axis', axisPointer:{ type:'shadow'} },
  legend:{ top:0 },
  grid:{ left:40,right:20,bottom:80,top:40 },
  xAxis:{ type:'category', data: props.stackedCategories, axisLabel:{ rotate:35, fontSize:10 }},
  yAxis:{ type:'value', name:'Candidates' },
  series: props.stackedSeries
}));

const deptOption = computed(()=>({
  tooltip:{ trigger:'axis' },
  legend:{ top:0 },
  xAxis:{ type:'category', data: props.deptCategories, axisLabel:{ rotate:25 }},
  yAxis:{ type:'value', name:'Candidates' },
  series: props.deptSeries
}));

const roleOption = computed(()=>({
  tooltip:{ trigger:'axis' },
  legend:{ top:0 },
  xAxis:{ type:'category', data: props.roleCategories, axisLabel:{ rotate:35 }},
  yAxis:{ type:'value', name:'Candidates' },
  series: props.roleSeries
}));

const interpretation = computed(()=>{
  const s = props.summary;
  if (!s.total_candidates) return "No screening data for current filters.";
  let msg = `During ${s.date_range_label}, ${s.total_candidates} candidates were evaluated. Pass: ${s.pass_pct}%, Fail: ${s.fail_pct}%, Under Review: ${s.under_pct}%.`;
  msg += s.filters_active ? " Figures reflect active filters." : " Showing overall screening performance.";
  return msg;
});
</script>

<template>
  <AppLayout title="Candidate Screening Insights">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-10">
      <h2 class="text-2xl font-bold text-gray-800">Candidate Screening Insights</h2>

      <!-- FILTERS -->
      <div class="bg-white rounded-xl shadow p-5 flex flex-wrap gap-5 items-end">
        <div>
          <label class="block text-[11px] font-semibold mb-1 text-gray-600">Department</label>
          <select v-model="local.department" class="border rounded px-2 py-1 text-sm w-44">
            <option value="">All</option>
            <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-semibold mb-1 text-gray-600">Job</label>
          <select v-model="local.job_id" class="border rounded px-2 py-1 text-sm w-48">
            <option value="">All</option>
            <option v-for="j in jobs" :key="j.id" :value="j.id">{{ j.job_title || j.title }}</option>
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-semibold mb-1 text-gray-600">Outcome</label>
          <select v-model="local.outcome" class="border rounded px-2 py-1 text-sm w-40">
            <option value="">All</option>
            <option value="pass">Pass</option>
            <option value="fail">Fail</option>
            <option value="under">Under Review</option>
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-semibold mb-1 text-gray-600">Experience Level</label>
          <select v-model="local.experience_level" class="border rounded px-2 py-1 text-sm w-44">
            <option value="">All</option>
            <option v-for="e in experienceLevels" :key="e" :value="e">{{ e }}</option>
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-semibold mb-1 text-gray-600">Date Range</label>
          <select v-model="local.date_preset" class="border rounded px-2 py-1 text-sm w-40">
            <option value="last_7">Last 7 Days</option>
            <option value="last_30">Last 30 Days</option>
            <option value="last_90">Last 90 Days</option>
            <option value="this_month">This Month</option>
            <option value="this_year">This Year</option>
            <option value="overall">Overall</option>
            <option value="custom">Custom</option>
          </select>
        </div>
        <div v-if="local.date_preset==='custom'">
          <label class="block text-[11px] font-semibold mb-1 text-gray-600">From</label>
          <input type="date" v-model="local.date_from" class="border rounded px-2 py-1 text-sm"/>
        </div>
        <div v-if="local.date_preset==='custom'">
          <label class="block text-[11px] font-semibold mb-1 text-gray-600">To</label>
            <input type="date" v-model="local.date_to" class="border rounded px-2 py-1 text-sm"/>
        </div>
        <div class="ml-auto">
          <button @click="resetFilters"
                  class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold px-3 py-2 rounded">
            Reset
          </button>
        </div>
      </div>

      <!-- STACKED BAR -->
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-700">
          Screening Breakdown (Qualification / Experience / Skill Match)
        </h3>
        <div v-if="!stackedCategories.length" class="text-sm text-gray-500">
          No data for current filters.
        </div>
        <VueECharts v-else :option="stackedOption" style="height:460px;width:100%;" />
      </div>

      <!-- DEPARTMENT CLUSTER -->
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-700">
          Screening Outcomes by Department
        </h3>
        <div v-if="!deptCategories.length" class="text-sm text-gray-500">
          No department data.
        </div>
        <VueECharts v-else :option="deptOption" style="height:400px;width:100%;" />
      </div>

      <!-- ROLE CLUSTER -->
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-700">
          Screening Outcomes by Job Role (Top 12)
        </h3>
        <div v-if="!roleCategories.length" class="text-sm text-gray-500">
          No role data.
        </div>
        <VueECharts v-else :option="roleOption" style="height:400px;width:100%;" />
      </div>

      <!-- INTERPRETATION -->
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-3 text-gray-700">Insight</h3>
        <p class="text-sm text-gray-700">{{ interpretation }}</p>
      </div>
    </div>
  </AppLayout>
</template>