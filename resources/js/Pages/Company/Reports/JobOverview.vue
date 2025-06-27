<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch, computed } from "vue";
import VueECharts from "vue-echarts";

const props = defineProps({
  totalOpenings: Number,
  activeListings: Number,
  rolesFilled: Number,
  typeCounts: Object,
  jobTypes: Array, 
  jobs: Object,      // paginated jobs for table (not used for filtering)
  allJobs: Array     // all jobs for KPIs, charts, and filtering
});

// --- FILTERS ---
const selectedType = ref("");
const selectedStatus = ref(""); 

// --- CLIENT-SIDE PAGINATION ---
const page = ref(1);
const pageSize = 10;

// Filtered jobs (all, not paginated)
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

// Paginated jobs for the table
const paginatedJobs = computed(() => {
  const jobs = filteredAllJobs.value;
  const start = (page.value - 1) * pageSize;
  return jobs.slice(start, start + pageSize);
});
const totalPages = computed(() => Math.ceil(filteredAllJobs.value.length / pageSize));

function goToPage(p) {
  page.value = p;
}

// Reset to page 1 when filters change
watch([selectedType, selectedStatus], () => { page.value = 1; });

// --- KPIs, Summary, and Chart ---
const kpis = computed(() => {
  const jobs = filteredAllJobs.value;
  return [
    { label: "Total Job Posted", value: jobs.length, color: "text-blue-600" },
    { label: "Active Listings", value: jobs.filter(j => j.status === 'open').length, color: "text-green-600" },
    { label: "Roles Filled", value: jobs.filter(j => j.status === 'filled').length, color: "text-purple-600" },
  ];
});

const textualReport = computed(() => {
  const total = props.allJobs.length;
  const open = props.allJobs.filter(j => j.status === 'open').length;
  const filled = props.allJobs.filter(j => j.status === 'filled').length;
  const top = topType.value;

  return `As of the latest data, your organization has posted a total of ${total} job openings. 
  Out of these, ${open} are currently active and ${filled} have been filled. 
  The job type most frequently posted is "${top}". This indicates a strong demand in that area.`;
  });

// Pie chart data from filteredAllJobs
const typeCounts = computed(() => {
  const jobs = filteredAllJobs.value;
  const counts = {};
  (props.jobTypes ?? []).forEach(jt => {
    const typeName = jt.type;
    counts[typeName] = jobs.filter(j =>
      Array.isArray(j.job_types) && j.job_types.some(rel => rel.type === typeName)
    ).length;
  });
  return counts;
});

const topType = computed(() => {
  const jobs = props.allJobs ?? [];
  const counts = {};
  (props.jobTypes ?? []).forEach(jt => {
    const typeName = jt.type;
    counts[typeName] = jobs.filter(j =>
      Array.isArray(j.job_types) && j.job_types.some(rel => rel.type === typeName)
    ).length;
  });
  const sorted = Object.entries(counts).sort((a, b) => b[1] - a[1]);
  return sorted.length ? sorted[0][0] : "N/A";
});

// Pie Chart Option
const pieOption = computed(() => {
  const selectedTypeName = selectedType.value;
  const data = Object.entries(typeCounts.value).map(([type, count]) => ({
    name: type,
    value: count,
    selected: !!selectedTypeName && type === selectedTypeName
  }));

  return {
    tooltip: { trigger: "item", formatter: "{b}: {c} ({d}%)" },
    legend: {
      orient: "vertical",
      left: 0,
      top: "middle",
    },
    series: [
      {
        name: "Job Type",
        type: "pie",
        radius: "60%",
        center: ["60%", "50%"],
        selectedMode: selectedTypeName ? "single" : false,
        data,
        emphasis: {
          scale: true,
          scaleSize: 10,
          itemStyle: {
            shadowBlur: 10,
            shadowOffsetX: 0,
            shadowColor: "rgba(0, 0, 0, 0.5)",
          },
        },
        label: { formatter: "{b}: {d}%", color: "#374151", fontWeight: "bold" },
      },
    ],
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

      <!-- FILTERS -->
      <div class="flex flex-wrap gap-4 mb-6">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Job Type</label>
          <select v-model="selectedType" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="jt in props.jobTypes" :key="jt.id" :value="jt.type">{{ jt.type }}</option>
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
      </div>
      
      <!-- KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">
        <div
          v-for="kpi in kpis"
          :key="kpi.label"
          class="bg-white rounded-lg shadow p-6 flex flex-col items-center"
        >
          <span class="text-gray-500 text-sm">{{ kpi.label }}</span>
          <span :class="['text-3xl font-bold', kpi.color]">{{ kpi.value }}</span>
        </div>
      </div>

      <!-- TEXTUAL REPORT -->
      <section class="bg-white rounded-2xl shadow p-6 space-y-6 mt-10 mb-10">
        <h3 class="text-2xl font-bold text-gray-700 ">List of Jobs</h3>
        <div v-if="paginatedJobs.length" class="mt-2">
          <h4 class="text-lg font-semibold text-gray-600 mb-2">Job List</h4>
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead>
              <tr>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Title</th>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Job Type</th>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(job, idx) in paginatedJobs" :key="job.id" class="hover:bg-gray-50">
                <td class="px-2 py-1">{{ job.job_title }}</td>
                <td class="px-2 py-1">
                  <span v-if="job.job_type">{{ jobTypeMap[job.job_type] || job.job_type }}</span>
                  <span v-if="job.job_types && job.job_types.length">
                    <template v-if="job.job_type"></template>
                  </span>
                </td>
                <td class="px-2 py-1">{{ job.status }}</td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination Controls -->
          <div class="flex justify-center mt-4" v-if="totalPages > 1">
            <button
              v-for="p in totalPages"
              :key="p"
              :class="['mx-1 px-3 py-1 rounded border', { 'bg-blue-600 text-white': p === page, 'bg-white text-blue-600': p !== page }]"
              @click="goToPage(p)"
            >{{ p }}</button>
          </div>
        </div>
        <div v-else class="text-gray-400">No jobs found for the selected filters.</div>
      </section>

      <!-- Pie Chart -->
      <div class="bg-white rounded-xl shadow p-8">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Job Postings by Type</h3>
        <div class="flex justify-center">
          <VueECharts
            v-if="pieOption.series[0].data.length"
            :option="pieOption"
            style="height: 350px; width: 100%; max-width: 100%;"
          />
          <div v-else class="text-gray-400 text-center py-8">No chart data available.</div>
        </div>

        <section class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-900 mb-6">
          <p class="font-semibold mb-1">📊 Summary Insight:</p>
          <p>{{ textualReport }}</p>
        </section>
      </div>
    </div>
  </AppLayout>
</template>
