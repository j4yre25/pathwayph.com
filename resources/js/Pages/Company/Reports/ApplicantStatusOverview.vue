<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import VueECharts from "vue-echarts";
import { ref, computed, watch } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
  statusCounts: Object,
  totalApplications: Number,
  shortlisted: Number,
  rolesFilled: Number,
  tableRows: { type: Array, default: ()=>[] },
  summary: { type: Object, default: ()=>({}) },
  filters: { type: Object, default: ()=>({}) },
  jobs: { type: Array, default: ()=>[] },
  departments: { type: Array, default: ()=>[] },
  stages: { type: Array, default: ()=>[] },
});

const local = ref({
  job_id: props.filters.job_id || "",
  department: props.filters.department || "",
  status: props.filters.status || "",
  date_preset: props.filters.date_preset || "overall",
  date_from: props.filters.date_from || "",
  date_to: props.filters.date_to || ""
});

// Auto-apply filters
watch(local, () => {
  const q = { ...local.value };
  if (q.date_preset !== 'custom') {
    q.date_from = undefined;
    q.date_to = undefined;
  }
  router.get(route('company.reports.applicantStatus'), q, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  });
}, { deep: true });

// Reset
function resetFilters() {
  local.value = {
    job_id: "",
    department: "",
    status: "",
    date_preset: "overall",
    date_from: "",
    date_to: ""
  };
}

// KPI formatting
const kpiShortlisted = computed(()=> props.shortlisted);
const kpiRolesFilled = computed(()=> props.rolesFilled);

// Table data
const rows = computed(()=> props.tableRows);

// Pie chart with custom colors (Rejected red, Hired green)
const pieOption = computed(() => {
  const colorMap = {
    Rejected: '#dc2626',
    Hired: '#16a34a'
  };
  const data = Object.entries(props.statusCounts || {}).map(([name,value]) => ({
    name,
    value,
    itemStyle: colorMap[name] ? { color: colorMap[name] } : undefined
  }));
  return {
    tooltip: { trigger: "item", formatter: "{b}: {c} ({d}%)" },
    legend: { bottom: 0 },
    series: [{
      name: "Applicant Status",
      type: "pie",
      radius: "60%",
      data
    }]
  };
});

// Interpretation
const interpretation = computed(() => {
  const s = props.summary || {};
  if (!s.total) return "No applicant data available for the selected filters.";
  const base = `In the selected range (${s.date_range_label}), a total of ${s.total} applications were recorded. ${s.offer ?? 0} reached the Offer stage, ${s.hired} were hired, and ${s.rejected} were rejected. The offer-to-hire conversion is ${s.offer_to_hire_rate}% while the overall rejection rate is ${s.rejection_rate}%.`;
  const offerRej = `Of the rejected candidates, ${s.rejected_after_offer} (${s.post_offer_rejection_rate}% of those who received offers) had already reached the Offer stage, while ${s.rejected_before_offer} were rejected earlier.`;
  const roles = `Roles filled: ${s.roles_filled} (${s.roles_filled_pct}%).`;
  const focusParts = [];
  if (s.top_job) focusParts.push(`Top job by volume: ${s.top_job}`);
  if (s.top_department) focusParts.push(`Top department: ${s.top_department}`);
  let filtersMsg = s.filters_active
    ? "Insights reflect active filters. Reset to view overall distribution."
    : "No filters applied; showing full dataset.";
  return [base, offerRej, roles, focusParts.join(". "), filtersMsg]
    .filter(Boolean)
    .join(" ");
});
</script>

<template>
  <AppLayout title="Applicant Status Overview">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-10">
      <h2 class="text-2xl font-bold text-gray-800">Applicant Status Overview</h2>

      <!-- Filters -->
      <div class="bg-white rounded-xl shadow p-5 flex flex-wrap gap-5 items-end">
        <div>
          <label class="block text-[11px] font-semibold text-gray-600 mb-1">Job Title</label>
          <select v-model="local.job_id" class="border rounded px-2 py-1 text-sm w-52">
            <option value="">All</option>
            <option v-for="j in jobs" :key="j.id" :value="j.id">{{ j.job_title || j.title }}</option>
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-semibold text-gray-600 mb-1">Department</label>
            <select v-model="local.department" class="border rounded px-2 py-1 text-sm w-48">
              <option value="">All</option>
              <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
            </select>
        </div>
        <div>
          <label class="block text-[11px] font-semibold text-gray-600 mb-1">Status</label>
          <select v-model="local.status" class="border rounded px-2 py-1 text-sm w-44">
            <option value="">All</option>
            <option value="screening">Screening</option>
            <option value="offered">Offered</option>
            <option value="rejected">Rejected</option>
            <option value="hired">Hired</option>
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-semibold text-gray-600 mb-1">Date Range</label>
          <select v-model="local.date_preset" class="border rounded px-2 py-1 text-sm w-44">
            <option value="overall">Overall</option>
            <option value="last_7">Last 7 Days</option>
            <option value="last_30">Last 30 Days</option>
            <option value="last_90">Last 90 Days</option>
            <option value="this_month">This Month</option>
            <option value="last_month">Last Month</option>
            <option value="this_year">This Year</option>
            <option value="last_year">Last Year</option>
            <option value="last_2_years">Last 2 Years</option>
            <option value="custom">Custom Range</option>
          </select>
        </div>
        <div v-if="local.date_preset === 'custom'">
          <label class="block text-[11px] font-semibold text-gray-600 mb-1">From</label>
          <input type="date" v-model="local.date_from" class="border rounded px-2 py-1 text-sm" />
        </div>
        <div v-if="local.date_preset === 'custom'">
          <label class="block text-[11px] font-semibold text-gray-600 mb-1">To</label>
          <input type="date" v-model="local.date_to" class="border rounded px-2 py-1 text-sm" />
        </div>
        <div class="ml-auto">
          <button @click="resetFilters"
                  class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold px-3 py-2 rounded">
            Reset
          </button>
        </div>
      </div>

      <!-- KPIs -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <div class="text-3xl font-bold text-blue-600">{{ totalApplications }}</div>
          <div class="text-gray-600 mt-2 text-sm">Total Applications</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <div class="text-3xl font-bold text-amber-600">{{ kpiShortlisted }}</div>
          <div class="text-gray-600 mt-2 text-sm">Candidates Shortlisted</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <div class="text-3xl font-bold text-green-600">{{ statusCounts?.Hired || 0 }}</div>
          <div class="text-gray-600 mt-2 text-sm">Hired</div>
        </div>
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <div class="text-3xl font-bold text-indigo-600">{{ kpiRolesFilled }}</div>
          <div class="text-gray-600 mt-2 text-sm">Roles Filled</div>
        </div>
      </div>

      <!-- TABLE -->
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-700">Applicants (Filtered)</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-3 py-2 text-left font-medium text-gray-600">Applicant Name</th>
                <th class="px-3 py-2 text-left font-medium text-gray-600">Job Title</th>
                <th class="px-3 py-2 text-left font-medium text-gray-600">Department</th>
                <th class="px-3 py-2 text-left font-medium text-gray-600">Status</th>
                <th class="px-3 py-2 text-left font-medium text-gray-600">Date Applied</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="r in rows" :key="r.id">
                <td class="px-3 py-2 font-medium text-gray-800">{{ r.applicant_name }}</td>
                <td class="px-3 py-2">{{ r.job_title || '—' }}</td>
                <td class="px-3 py-2">{{ r.department || '—' }}</td>
                <td class="px-3 py-2">
                  <span :class="{
                    'text-blue-600': r.stage === 'applied',
                    'text-amber-600': r.stage === 'screening' || r.stage === 'offer',
                    'text-green-600 font-semibold': r.stage === 'hired',
                    'text-red-600 font-semibold': r.stage === 'rejected'
                  }">
                    {{ r.stage_label }}
                  </span>
                </td>
                <td class="px-3 py-2">{{ r.applied_date }}</td>
              </tr>
              <tr v-if="!rows.length">
                <td colspan="6" class="px-3 py-4 text-center text-gray-500">No applications match current filters.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- PIE CHART -->
      <div class="bg-white rounded-xl shadow p-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Candidate Status Distribution</h3>
        <VueECharts :option="pieOption" style="height:350px;width:100%;" />
      </div>

      <!-- INTERPRETATION -->
      <div class="bg-white rounded-xl shadow p-6">
        <h3 class="text-lg font-semibold mb-3 text-gray-700">Summary Insight</h3>
        <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-line">
          {{ interpretation }}
        </p>
      </div>
    </div>
  </AppLayout>
</template>