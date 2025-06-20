<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch, onMounted, computed } from "vue";
import VueECharts from "vue-echarts";

const props = defineProps({
  totalOpenings: Number,
  activeListings: Number,
  rolesFilled: Number,
  typeCounts: Object,
  jobTypes: Array, 
  jobs: {
    type: Array,
    default: () => [],
  },     
});

// --- FILTERS ---
const selectedType = ref("");
const selectedStatus = ref(""); 

const filteredJobs = computed(() => {
  return (props.jobs ?? []).filter(job => {
    let matchesType = true;
    if (!selectedType.value) return true;
    // Check if jobTypes relation contains the selected type
    return Array.isArray(job.jobTypes) && job.jobTypes.some(jt => jt.type === selectedType.value)
  });
});

const filteredTypeCounts = computed(() => {
  const jobs = filteredJobs.value;
  const counts = {};
  (props.jobTypes ?? []).forEach(jt => {
    const typeName = jt.type;
    counts[typeName] = jobs.filter(j =>
      Array.isArray(j.jobTypes) && j.jobTypes.some(rel => rel.type === typeName)
    ).length;
  });
  return counts;
});

const jobTypeMap = computed(() => {
  const map = {};
  (props.jobTypes ?? []).forEach(jt => {
    map[jt.id] = jt.type;
    map[jt.type] = jt.type; // allow direct match if already a string
  });
  return map;
});

// --- SUMMARY & TEXTUAL REPORT ---
const summaryText = computed(() => {
  const total = filteredJobs.value.length;
  const typeText = selectedType.value ? ` of type "${selectedType.value}"` : "";
  return `There are ${total} job postings${typeText} currently displayed.`;
});

const topType = computed(() => {
  if (!filteredTypeCounts.value) return "N/A";
  const sorted = Object.entries(filteredTypeCounts.value).sort((a, b) => b[1] - a[1]);
  return sorted.length ? sorted[0][0] : "N/A";
});

const textualReport = computed(() => {
  return `
    As of now, your company has posted a total of ${props.totalOpenings} job openings, with ${props.activeListings} currently active and ${props.rolesFilled} roles filled.
    The most common job type is "${topType.value}".`;
});



// KPI Cards
const filteredKpis = computed(() => {
  const jobs = filteredJobs.value;
  return [
    { label: "Total Job Posted", value: jobs.length, color: "text-blue-600" },
    { label: "Active Listings", value: jobs.filter(j => j.status === 'open').length, color: "text-green-600" },
    { label: "Roles Filled", value: jobs.filter(j => j.status === 'filled').length, color: "text-purple-600" },
  ];
});


// Pie Chart Option
const pieOption = ref({
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
      data: Object.entries(filteredTypeCounts.value).map(
        ([type, count]) => ({ name: type, value: count })
      ),
      emphasis: {
        itemStyle: {
          shadowBlur: 10,
          shadowOffsetX: 0,
          shadowColor: "rgba(0, 0, 0, 0.5)",
        },
      },
      label: { formatter: "{b}: {d}%", color: "#374151", fontWeight: "bold" },
    },
  ],
});

const updatePieChart = () => {
  pieOption.value.series[0].data = Object.entries(props.typeCounts || {}).map(
    ([type, count]) => ({ name: type, value: count })
  );
};
watch(() => props.typeCounts, updatePieChart, { immediate: true });
onMounted(updatePieChart);


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

      <!-- SUMMARY -->
      <!-- <div class="mb-6 bg-blue-50 rounded-lg p-4 text-blue-900 font-medium">
        {{ summaryText }}
      </div> -->

      <!-- KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">
        <div
          v-for="kpi in filteredKpis"
          :key="kpi.label"
          class="bg-white rounded-lg shadow p-6 flex flex-col items-center"
        >
          <span class="text-gray-500 text-sm">{{ kpi.label }}</span>
          <span :class="['text-3xl font-bold', kpi.color]">{{ kpi.value }}</span>
        </div>
      </div>

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
      </div>

      <!-- TEXTUAL REPORT -->
      <section class="bg-white rounded-2xl shadow p-6 space-y-6 mt-10">
        <h3 class="text-2xl font-bold text-gray-700 mb-2">Textual Report</h3>
        <div class="text-gray-700 whitespace-pre-line">{{ textualReport }}</div>
        <!-- Example: List jobs (optional) -->
        <div v-if="filteredJobs.length" class="mt-4">
          <h4 class="text-lg font-semibold text-gray-600 mb-2">Job List</h4>
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead>
              <tr>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Title</th>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Type</th>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(job, idx) in filteredJobs" :key="idx" class="hover:bg-gray-50">
                <td class="px-2 py-1">{{ job.job_title }}</td>
                <td class="px-2 py-1">
                  <span v-if="job.job_type">{{ jobTypeMap[job.job_type] || job.job_type }}</span>
                  <span v-if="job.jobTypes && job.jobTypes.length">
                    <template v-if="job.job_type">, </template>
                    {{ job.jobTypes.map(jt => jt.type).join(', ') }}
                  </span>
                </td>
                <td class="px-2 py-1">{{ job.status }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-gray-400">No jobs found for the selected filters.</div>
      </section>
    </div>
  </AppLayout>
</template>
