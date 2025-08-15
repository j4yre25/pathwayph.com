<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, onMounted, watch, computed } from "vue";
import VueECharts from "vue-echarts";

const props = defineProps({
  departmentCounts: Array,
  stackedData: Object,
  roleLevels: Array,
});

const selectedDepartment = ref("");
const selectedRoleLevel = ref("");

// Filtered jobs (all, not paginated)
const filteredJobs = computed(() => {
  let jobs = props.stackedData.allJobs ?? [];
  if (selectedDepartment.value) {
    jobs = jobs.filter(j => j.department === selectedDepartment.value);
  }
  if (selectedRoleLevel.value) {
    jobs = jobs.filter(j => j.job_experience_level === selectedRoleLevel.value);
  }
  return jobs;
});

// Pagination
const page = ref(1);
const pageSize = 10;
const paginatedJobs = computed(() => {
  const jobs = filteredJobs.value;
  const start = (page.value - 1) * pageSize;
  return jobs.slice(start, start + pageSize);
});
const totalPages = computed(() => Math.ceil(filteredJobs.value.length / pageSize));
watch([selectedDepartment, selectedRoleLevel], () => { page.value = 1; });
function goToPage(p) { page.value = p; }

// Bar Chart: Number of job openings per department
const barOption = computed(() => {
  const departments = [...new Set(filteredJobs.value.map(j => j.department))];
  const counts = departments.map(dept =>
    filteredJobs.value.filter(j => j.department === dept).length
  );
   return {
    tooltip: { trigger: "axis" },
    xAxis: {
      type: "category",
      data: departments,
      name: "Department",
      nameLocation: "middle",
      nameGap: 62,
      axisLabel: {
        rotate: 30, // slant the department names
        interval: 0, // show all labels
      },
    },
    yAxis: {
      type: "value",
      name: "Number of Openings",
      nameLocation: "middle",
      nameGap: 50,
    },
    series: [
      {
        data: counts,
        type: "bar",
        color: "#6366f1",
        name: "Openings",
      },
    ],
  };
});

// Stacked Column Chart: Role levels per department
const stackedOption = computed(() => {
  const departments = [...new Set(filteredJobs.value.map(j => j.department))];
  return {
    tooltip: { trigger: "axis" },
    legend: { data: props.roleLevels },
    xAxis: {
      type: "category",
      data: departments,
      name: "Department",
      nameLocation: "middle",
      nameGap: 62,
      axisLabel: {
        rotate: 30, // slant the department names
        interval: 0, // show all labels
      },
    },
    yAxis: { 
      type: "value",
      name: "Number of Openings",
      nameLocation: "middle",
      nameGap: 50,
    },
    series: props.roleLevels.map(level => ({
      name: level,
      type: "bar",
      stack: "total",
      data: departments.map(dept =>
        filteredJobs.value.filter(
          j => j.department === dept && j.job_experience_level === level
        ).length
      ),
    })),
  };
});

const summaryReport = computed(() => {
  const deptCounts = props.departmentCounts;
  const total = deptCounts.reduce((sum, d) => sum + d.total, 0);

  if (!deptCounts.length || total === 0) {
    return "There are currently no job openings recorded across departments.";
  }

  // Get the highest count
  const maxCount = Math.max(...deptCounts.map(d => d.total));

  // Get all departments tied for top
  const topDepts = deptCounts
    .filter(d => d.total === maxCount)
    .map(d => `"${d.department}"`);

  const topPercent = ((maxCount / total) * 100).toFixed(1);
  const topLabel = topDepts.length > 1
    ? `Departments ${topDepts.join(" and ")}`
    : `Department ${topDepts[0]}`;

  // Analyze stacked role levels
  const roleLevelCounts = {};
  const jobs = props.stackedData.allJobs ?? [];

  for (const role of props.roleLevels) {
    roleLevelCounts[role] = jobs.filter(j => j.job_experience_level === role).length;
  }

  const topRoleLevel = Object.entries(roleLevelCounts)
    .sort((a, b) => b[1] - a[1])[0];

  const roleLine = topRoleLevel
    ? `The most common role level across departments is "${topRoleLevel[0]}", with ${topRoleLevel[1]} listings.`
    : "";

  return `As of now, there are ${total} job openings across all departments. 
${topLabel} currently ${topDepts.length > 1 ? 'share' : 'has'} the highest number of postings, each accounting for ${topPercent}% of the total (${maxCount} positions). 
${roleLine}`;
});

</script>

<template>
  <AppLayout title="Department-Wise Job Listings">
    <div class="max-w-7xl mx-auto py-8 px-4">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Department-Wise Job Listings</h2>

      <!-- Filters -->
      <div class="flex gap-4 mb-6">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Department</label>
          <select v-model="selectedDepartment" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="d in props.departmentCounts" :key="d.department" :value="d.department">{{ d.department }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Role Level</label>
          <select v-model="selectedRoleLevel" class="rounded border-gray-300">
            <option value="">All</option>
            <option v-for="level in props.roleLevels" :key="level" :value="level">{{ level }}</option>
          </select>
        </div>
      </div>

      <!-- Job List -->
      <section class="bg-white rounded-2xl shadow p-6 space-y-6 mt-10 mb-10">
        <h3 class="text-2xl font-bold text-gray-700">Job List</h3>
        <div v-if="paginatedJobs.length">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead>
              <tr>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Title</th>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Department</th>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Role Level</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="job in paginatedJobs" :key="job.id" class="hover:bg-gray-50">
                <td class="px-2 py-1">{{ job.job_title }}</td>
                <td class="px-2 py-1">{{ job.department }}</td>
                <td class="px-2 py-1">{{ job.job_experience_level }}</td>
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

      <div class="bg-white rounded-xl shadow p-8 mb-12">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Bar Chart: Openings per Department</h3>
        <VueECharts :option="barOption" style="height: 450px; width: 100%;" />
        
        <h3 class="text-lg font-semibold mb-6 text-gray-700 mt-8">Stacked Column: Role Levels per Department</h3>
        <VueECharts :option="stackedOption" style="height: 450px; width: 100%;" />
        
        <div class="mt-5 bg-green-50 border border-green-200 rounded-lg p-4 text-green-900">
          <p class="font-semibold mb-1">📊 Summary Insight:</p>
          <p>{{ summaryReport }}</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>