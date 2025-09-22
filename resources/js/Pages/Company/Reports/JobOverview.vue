<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch, computed } from "vue";
import VueECharts from "vue-echarts";
import { router } from '@inertiajs/vue3'

const props = defineProps({
  totalOpenings: Number,
  activeListings: Number,
  rolesFilled: Number,
  typeCounts: Object,
  jobTypes: Array,
  jobs: Object,
  allJobs: Array,
  filters: Object,
  filterOptions: Object,
  overall: Object,
});

// ----- Filter State (bound to query params) -----
const datePreset = ref(props.filters?.date_preset || 'last_30');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');
const experienceLevel = ref(props.filters?.experience_level || '');
const vacancyMin = ref(props.filters?.vacancy_min || '');
const vacancyMax = ref(props.filters?.vacancy_max || '');
const workEnvironment = ref(props.filters?.work_environment || '');

// Trigger re-query (server filtering) when user clicks Apply
function applyFilters() {
  const query = {
    date_preset: datePreset.value,
    date_from: datePreset.value === 'custom' ? dateFrom.value : null,
    date_to: datePreset.value === 'custom' ? dateTo.value : null,
    experience_level: experienceLevel.value || null,
    vacancy_min: vacancyMin.value || null,
    vacancy_max: vacancyMax.value || null,
    work_environment: workEnvironment.value || null,
  };
  router.get(route('company.reports.overview'), query, { preserveState: true, preserveScroll: true });
}

function resetFilters() {
  datePreset.value = 'last_30';
  dateFrom.value = '';
  dateTo.value = '';
  experienceLevel.value = '';
  vacancyMin.value = '';
  vacancyMax.value = '';
  workEnvironment.value = '';
  applyFilters();
}

// ---- EXISTING Local Filters (job type + status) ----
const selectedType = ref("");
const selectedStatus = ref("");
const page = ref(1);
const pageSize = 10;

// Derive vacancy range limits
const vacancyMinActual = props.filterOptions?.vacancy_min_actual ?? 0;
const vacancyMaxActual = props.filterOptions?.vacancy_max_actual ?? 0;

// Filtered jobs AFTER server filters (client refinements)
const filteredAllJobs = computed(() => {
  let jobs = props.allJobs ?? [];
  if (selectedType.value) {
    jobs = jobs.filter(job =>
      Array.isArray(job.job_types) && job.job_types.some(jt => jt.type === selectedType.value)
    );
  }
  if (selectedStatus.value) {
    jobs = jobs.filter(job =>
      selectedStatus.value === "Active"
        ? job.status === "open"
        : job.status === "closed"
    );
  }
  return jobs;
});

// Pagination (client side)
const paginatedJobs = computed(() => {
  const jobs = filteredAllJobs.value;
  const start = (page.value - 1) * pageSize;
  return jobs.slice(start, start + pageSize);
});
const totalPages = computed(() => Math.ceil(filteredAllJobs.value.length / pageSize));
function goToPage(p) { page.value = p; }
watch([selectedType, selectedStatus], () => { page.value = 1; });

// KPIs from filtered (already server-filtered)
const kpis = computed(() => [
  { label: "Total Job Posted", value: props.totalOpenings, color: "text-blue-600" },
  { label: "Active Listings", value: props.activeListings, color: "text-green-600" },
  { label: "Roles Filled", value: props.rolesFilled, color: "text-purple-600" },
]);

// Recompute type counts on client for pie to reflect current local type/status refinement
const typeCountsLocal = computed(() => {
  const jobs = filteredAllJobs.value;
  const counts = {};
  (props.jobTypes ?? []).forEach(jt => {
    const typeName = jt.type;
    counts[typeName] = jobs.filter(j =>
      Array.isArray(j.job_types) && j.job_types.some(rel => rel.type === typeName)
    ).length
      + jobs.filter(j => j.job_type === typeName).length;
  });
  return counts;
});

const topType = computed(() => {
  const entries = Object.entries(typeCountsLocal.value).filter(([,v])=>v>0);
  entries.sort((a,b)=>b[1]-a[1]);
  return entries.length ? entries[0][0] : "N/A";
});

const textualReport = computed(() => {
  const total = props.totalOpenings;
  const open = props.activeListings;
  const filled = props.rolesFilled;
  const top = topType.value;
  const dateRange = props.filters?.date_range_label || '';
  return `During ${dateRange}, you have ${total} job postings, with ${open} active and ${filled} filled. The most frequent job type is "${top}", indicating concentrated demand.`;
});

// Pie chart option
const selectedTypeLocal = selectedType; // reuse
const pieOption = computed(() => {
  const sel = selectedTypeLocal.value;
  const data = Object.entries(typeCountsLocal.value).map(([type,count])=>({
    name:type,
    value:count,
    selected: !!sel && type === sel
  })).filter(d=>d.value>0);

  return {
    tooltip:{ trigger:"item", formatter:"{b}: {c} ({d}%)" },
    legend:{ orient:"vertical", left:0, top:"middle" },
    series:[{
      name:"Job Type",
      type:"pie",
      radius:"60%",
      center:["60%","50%"],
      selectedMode: sel ? "single" : false,
      data,
      emphasis:{
        scale:true,
        scaleSize:10,
        itemStyle:{ shadowBlur:10, shadowOffsetX:0, shadowColor:"rgba(0,0,0,0.5)" }
      },
      label:{ formatter:"{b}: {d}%", color:"#374151", fontWeight:"bold" },
    }]
  };
});

const jobTypeMap = computed(() => {
  const map = {};
  (props.jobTypes ?? []).forEach(jt => {
    map[jt.id] = jt.type;
    map[jt.type] = jt.type;
  });
  return map;
});
</script>

<template>
  <AppLayout title="Company Reports Overview">
    <div class="max-w-7xl mx-auto py-8 px-4">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Job Openings Overview</h2>

      <!-- SERVER FILTERS PANEL -->
      <div class="bg-white rounded-lg shadow p-4 mb-6 grid md:grid-cols-5 gap-4 text-sm">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Date Preset</label>
          <select v-model="datePreset" class="w-full border-gray-300 rounded">
            <option value="last_7">Last 7 Days</option>
            <option value="last_30">Last 30 Days</option>
            <option value="this_month">This Month</option>
            <option value="last_month">Last Month</option>
            <option value="overall">Overall</option>
            <option value="custom">Custom</option>
          </select>
        </div>
        <div v-if="datePreset === 'custom'">
          <label class="block text-xs font-medium text-gray-700 mb-1">Start Date</label>
            <input type="date" v-model="dateFrom" class="w-full border-gray-300 rounded" />
        </div>
        <div v-if="datePreset === 'custom'">
          <label class="block text-xs font-medium text-gray-700 mb-1">End Date</label>
            <input type="date" v-model="dateTo" class="w-full border-gray-300 rounded" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Experience Level</label>
          <select v-model="experienceLevel" class="w-full border-gray-300 rounded">
            <option value="">All</option>
            <option v-for="lvl in filterOptions.experience_levels" :key="lvl" :value="lvl">{{ lvl }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Work Environment</label>
          <select v-model="workEnvironment" class="w-full border-gray-300 rounded">
            <option value="">All</option>
            <option v-for="we in filterOptions.work_environments" :key="we" :value="we">{{ we }}</option>
          </select>
        </div>
        <div class="md:col-span-2">
          <label class="block text-xs font-medium text-gray-700 mb-1">Vacancy Range</label>
          <div class="flex gap-2">
            <input type="number" min="0"
              :placeholder="vacancyMinActual"
              v-model="vacancyMin"
              class="w-1/2 border-gray-300 rounded" />
            <input type="number" min="0"
              :placeholder="vacancyMaxActual"
              v-model="vacancyMax"
              class="w-1/2 border-gray-300 rounded" />
          </div>
          <div class="text-[10px] text-gray-400 mt-1">
            Actual range: {{ vacancyMinActual }} - {{ vacancyMaxActual }}
          </div>
        </div>
        <div class="md:col-span-5 flex justify-end gap-3 pt-2">
          <button @click="resetFilters"
                  class="px-3 py-2 rounded border text-gray-600 text-xs hover:bg-gray-50">
            Reset
          </button>
          <button @click="applyFilters"
                  class="px-4 py-2 rounded bg-indigo-600 text-white text-xs hover:bg-indigo-700">
            Apply
          </button>
        </div>
      </div>

      <!-- LOCAL FILTERS (client-side refinement) -->
      <div class="flex flex-wrap gap-4 mb-6">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Job Type</label>
          <select v-model="selectedType" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="jt in jobTypes" :key="jt.id" :value="jt.type">{{ jt.type }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
            <select v-model="selectedStatus" class="rounded border-gray-300">
              <option value="">All</option>
              <option value="Active">Active</option>
              <option value="Closed">Closed</option>
            </select>
        </div>
        <div class="self-end text-xs text-gray-500">
          <span v-if="filters?.filters_active">Filtered Period: {{ filters.date_range_label }}</span>
        </div>
      </div>

      <!-- KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">
        <div v-for="kpi in kpis" :key="kpi.label"
             class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
          <span class="text-gray-500 text-sm">{{ kpi.label }}</span>
          <span :class="['text-3xl font-bold', kpi.color]">{{ kpi.value }}</span>
        </div>
      </div>

      <!-- Job List Table -->
      <section class="bg-white rounded-2xl shadow p-6 space-y-6 mt-4 mb-10">
        <h3 class="text-2xl font-bold text-gray-700">List of Jobs</h3>
        <div v-if="paginatedJobs.length" class="mt-2">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead>
            <tr>
              <th class="px-2 py-1 text-left font-medium text-gray-700">Title</th>
              <th class="px-2 py-1 text-left font-medium text-gray-700">Job Type(s)</th>
              <th class="px-2 py-1 text-left font-medium text-gray-700">Status</th>
              <th class="px-2 py-1 text-left font-medium text-gray-700">Experience</th>
              <th class="px-2 py-1 text-left font-medium text-gray-700">Vacancies</th>
              <th class="px-2 py-1 text-left font-medium text-gray-700">Environment</th>
              <th class="px-2 py-1 text-left font-medium text-gray-700">Posted</th>
            </tr>
            </thead>
            <tbody>
              <tr v-for="job in paginatedJobs" :key="job.id" class="hover:bg-gray-50">
                <td class="px-2 py-1">{{ job.job_title }}</td>
                <td class="px-2 py-1">
                  <span v-if="job.job_types?.length">
                    {{ job.job_types.map(j=>j.type).join(', ') }}
                  </span>
                  <span v-else>{{ job.job_type || '—' }}</span>
                </td>
                <td class="px-2 py-1 capitalize">{{ job.status }}</td>
                <td class="px-2 py-1">{{ job.job_experience_level || '—' }}</td>
                <td class="px-2 py-1">{{ job.job_vacancies ?? '—' }}</td>
                <td class="px-2 py-1">
                  {{ job.work_environment || job.job_work_environment || job.job_work_arrangement || '—' }}
                </td>
                <td class="px-2 py-1">
                  {{ (job.created_at || '').substring(0,10) }}
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div class="flex justify-center mt-4" v-if="totalPages > 1">
            <button
              v-for="p in totalPages"
              :key="p"
              :class="['mx-1 px-3 py-1 rounded border text-xs', { 'bg-blue-600 text-white': p === page, 'bg-white text-blue-600': p !== page }]"
              @click="goToPage(p)"
            >{{ p }}</button>
          </div>
        </div>
        <div v-else class="text-gray-400">No jobs found for the selected filters.</div>
      </section>

      <!-- Pie Chart + Summary -->
      <div class="bg-white rounded-xl shadow p-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Job Postings by Type</h3>
        <div class="flex justify-center">
          <VueECharts
            v-if="pieOption.series[0].data.length"
            :option="pieOption"
            style="height: 350px; width: 100%; max-width:100%;"
          />
          <div v-else class="text-gray-400 text-center py-8">No chart data available.</div>
        </div>

        <section class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-900 mt-6">
          <p class="font-semibold mb-1">📊 Summary Insight:</p>
          <p>{{ textualReport }}</p>
          <p class="mt-2 text-xs text-green-700">
            Overall (unfiltered) totals: {{ overall.total }} posted, {{ overall.active }} active, {{ overall.filled }} filled.
          </p>
        </section>
      </div>
    </div>
  </AppLayout>
</template>
