<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import VueECharts from "vue-echarts";
import { ref, computed, watch } from "vue";
import dayjs from "dayjs";

const props = defineProps({
  jobTitles: Array,
  stackedBarSeries: Array,
  scatterData: Array,
  months: Array,
  lineData: Array,
  areaSeries: Array,
  allSkills: Array,
  summaryTable: Array, 
  departments: Array,  
  jobDepartments: Object,
  years: Array,
  selectedYear: String,
});

const selectedDepartment = ref("");
const selectedSkill = ref("");
const academicYears = [
  "2019-2020",
  "2020-2021",
  "2021-2022",
  "2022-2023"
];

const years = props.years ?? [];
const selectedYear = ref(props.selectedYear || (years.length ? years[years.length - 1] : ""));

// Pagination state
const page = ref(1);
const pageSize = 10;
const filteredSummary = computed(() => {
  let filtered = props.summaryTable ?? [];
  if (selectedDepartment.value) {
    filtered = filtered.filter(row => row.department === selectedDepartment.value);
  }
  if (selectedYear.value) {
    filtered = filtered.filter(row => row.applicationYear == selectedYear.value);
  }
  return filtered;
});
const totalPages = computed(() => Math.ceil(filteredSummary.value.length / pageSize));
const paginatedSummary = computed(() => {
  const start = (page.value - 1) * pageSize;
  return filteredSummary.value.slice(start, start + pageSize);
});
function goToPage(p) { page.value = p; }
watch([selectedDepartment, selectedSkill, selectedYear], () => { page.value = 1; });

// Filter helpers
const filteredMonths = computed(() => {
  // Only the selected academic year
  return [selectedYear.value];
});

// Filtered job titles by department
const filteredJobTitles = computed(() => {
  if (!selectedDepartment.value) return props.jobTitles;
  // If you have jobDepartments mapping, use it:
  if (props.jobDepartments) {
    return props.jobTitles.filter(
      title => props.jobDepartments[title] === selectedDepartment.value
    );
  }
  // Otherwise, fallback: filter summaryTable for department, then get jobTitles
  return (props.summaryTable ?? [])
    .filter(row => row.department === selectedDepartment.value)
    .map(row => row.jobTitle);
});

// Filtered data for all graphs
const filteredBarSeries = computed(() => {
  const idx = years.indexOf(selectedYear.value);
  return props.stackedBarSeries.map(s => ({
    ...s,
    data: idx !== -1 ? [s.data[idx]] : [],
  }));
});

const filteredLineData = computed(() => {
  const idx = years.indexOf(selectedYear.value);
  return idx !== -1 ? [props.lineData[idx]] : [];
});

const filteredAreaSeries = computed(() => {
  const idx = years.indexOf(selectedYear.value);
  return props.areaSeries.map(s => ({
    ...s,
    data: idx !== -1 ? [s.data[idx]] : [],
  }));
});

// Scatter plot filtering
const filteredScatterData = computed(() => {
  let data = props.scatterData;
  if (selectedSkill.value) {
    data = data.filter(d => d[3]?.includes(selectedSkill.value));
  }
  if (selectedDepartment.value && props.jobDepartments) {
    data = data.filter(d => props.jobDepartments[d[2]] === selectedDepartment.value);
  }
  // Filter by year (d[4] is year)
  if (selectedYear.value) {
    data = data.filter(d => d[4] == selectedYear.value);
  }
  return data;
});

const scatterSeries = computed(() => {
  const titles = filteredJobTitles.value;
  return titles.map((title) => ({
    name: title,
    type: "scatter",
    symbolSize: 18,
    data: filteredScatterData.value.filter(d => d[2] === title).map(d => [d[0], d[1]]),
    emphasis: { focus: "series" },
  }));
});

// Chart options
const stackedBarOption = computed(() => ({
  tooltip: { trigger: "axis" },
  legend: { type: "scroll", data: filteredBarSeries.value.map(s => s.name) },
  xAxis: { 
    type: "category", 
    data: filteredMonths.value, 
    axisLabel: { interval: 0, rotate: 45 }
  },
  yAxis: { type: "value" },
  series: filteredBarSeries.value,
  dataZoom: [{ type: 'slider', start: 0, end: 20 }]
}));

const scatterOption = computed(() => {
  const allX = scatterSeries.value.flatMap(series => series.data.map(d => d[0]));
  const allY = scatterSeries.value.flatMap(series => series.data.map(d => d[1]));
  const maxX = Math.max(10, ...allX);
  const maxY = Math.max(10, ...allY);

  return {
    tooltip: {
      trigger: "item",
      formatter: params => `${params.seriesName}<br/>Years: ${params.data[0]}, Applications: ${params.data[1]}`
    },
    legend: {
      data: filteredJobTitles.value,
      type: "scroll",
      top: 20
    },
    grid: {
      top: 70, 
      left: 80,
      right: 20,
      bottom: 40
    },
    xAxis: { 
      type: "value", 
      name: "Years of Experience", 
      nameLocation: "middle",
      nameGap: 30,
      minInterval: 1,
      min: 0,
      max: maxX,
      splitNumber: 10,
    },
    yAxis: { 
      type: "value", 
      name: "Number of Applications", 
      nameLocation: "middle",
      nameGap: 30,
      nameRotate: 90,
      minInterval: 1,
      min: 0,
      max: maxY,
      splitNumber: 10,
    },
    series: scatterSeries.value,
  };
});

const lineOption = computed(() => ({
  tooltip: { trigger: "axis" },
  xAxis: { 
    type: "category", 
    data: filteredMonths.value,
    name: "Month", 
    nameLocation: "middle",
    nameGap: 30
  },
  yAxis: { 
    type: "value", 
    name: "Applications",
    nameLocation: "middle",
    nameGap: 50
  },
  series: [
    {
      name: "Applications",
      type: "line",
      data: filteredLineData.value,
      smooth: true,
      areaStyle: {},
    },
  ],
}));

const areaOption = computed(() => ({
  tooltip: { trigger: "axis" },
  legend: { type: "scroll", data: filteredAreaSeries.value.map(s => s.name) },
  xAxis: { type: "category", data: filteredMonths.value },
  yAxis: { type: "value" },
  series: filteredAreaSeries.value,
  dataZoom: [{ type: 'slider', start: 0, end: 20 }]
}));

// Dynamic summary paragraph
const summaryText = computed(() => {
  const total = filteredSummary.value.length;
  const totalApplications = filteredSummary.value.reduce((sum, row) => sum + (row.totalApplications ?? 0), 0);
  const totalHired = filteredSummary.value.reduce((sum, row) => sum + (row.hired ?? 0), 0);

  let filterDesc = "";
  if (selectedDepartment.value) filterDesc += ` for the department "${selectedDepartment.value}"`;
  if (selectedSkill.value) filterDesc += (filterDesc ? " and" : " for") + ` skill "${selectedSkill.value}"`;
  if (selectedYear.value) {
    filterDesc += (filterDesc ? ", " : " for ") + `the academic year ${selectedYear.value}`;
  }

  return `This report summarizes your company's job application activity${filterDesc || " for all departments and periods"}.
The summary table above lists ${total} job${total === 1 ? "" : "s"} with a total of ${totalApplications} application${totalApplications === 1 ? "" : "s"} and ${totalHired} hire${totalHired === 1 ? "" : "s"}.
The stacked bar chart visualizes applications per job by stage, the scatter plot shows the relationship between experience and applications, the line chart tracks application trends over time, and the area chart displays department-wise application flow.
All graphs and the table update dynamically as you change the filters above, allowing you to analyze specific departments, skills, or timeframes.`;
});
</script>

<template>
  <AppLayout title="Application Analysis">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-10">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Application Analysis</h2>

      <!-- FILTERS -->
      <div class="bg-white rounded-xl shadow p-6 mb-8 flex flex-wrap gap-6 items-center">
        <div>
          <label class="font-semibold">Filter by Department:</label>
          <select v-model="selectedDepartment" 
            class="w-56 border rounded px-2 py-1 ml-2"
            >
            <option value="">All Departments</option>
            <option v-for="dept in props.departments" :key="dept" :value="dept">{{ dept }}</option>
          </select>
        </div>
        <div>
          <label class="font-semibold">Filter by Skill:</label>
          <select v-model="selectedSkill" class="w-56 border rounded px-2 py-1 ml-2">
            <option value="">All Skills</option>
            <option v-for="skill in props.allSkills" :key="skill" :value="skill">{{ skill }}</option>
          </select>
        </div>
      <div>
        <label class="font-semibold">Application Year:</label>
        <select v-model="selectedYear" class="border rounded w-60 px-2 py-1 ml-2">
          <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
        </select>
      </div>
      </div>

      <!-- TEXTUAL SUMMARY TABLE -->
      <div class="bg-white rounded-xl shadow p-6 mb-8">
        <h3 class="text-lg font-semibold mb-4 text-gray-700">Summary Table</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Job Title</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total Applications</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hired</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Application Year</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="row in paginatedSummary" :key="row.jobTitle">
                <td class="px-4 py-2">{{ row.jobTitle }}</td>
                <td class="px-4 py-2">{{ row.department }}</td>
                <td class="px-4 py-2">{{ row.totalApplications }}</td>
                <td class="px-4 py-2">{{ row.hired }}</td>
                <td class="px-4 py-2">{{ row.applicationYear }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- PAGINATION CONTROLS -->
        <div class="mt-4 flex justify-between items-center">
          <div class="text-sm text-gray-500">
            Page {{ page }} of {{ totalPages }}
          </div>
          <div>
            <button 
              @click="goToPage(1)" 
              :disabled="page === 1" 
              class="mx-2 px-3 py-1 text-sm font-semibold rounded bg-blue-600 text-white disabled:opacity-50"
            >
              First
            </button>
            <button 
              @click="goToPage(page - 1)" 
              :disabled="page === 1" 
              class="mx-2 px-3 py-1 text-sm font-semibold rounded bg-blue-600 text-white disabled:opacity-50"
            >
              Previous
            </button>
            <button 
              @click="goToPage(page + 1)" 
              :disabled="page === totalPages" 
              class="mx-2 px-3 py-1 text-sm font-semibold rounded bg-blue-600 text-white disabled:opacity-50"
            >
              Next
            </button>
            <button 
              @click="goToPage(totalPages)" 
              :disabled="page === totalPages" 
              class="mx-2 px-3 py-1 text-sm font-semibold rounded bg-blue-600 text-white disabled:opacity-50"
            >
              Last
            </button>
          </div>
        </div>
      </div>

      <!-- GRAPHS SECTION -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Stacked Bar Chart -->
        <div class="bg-white rounded-xl shadow p-6 flex flex-col">
          <div>
            <h3 class="text-lg font-semibold mb-2 text-gray-700">Applications per Job Posting (by Stage)</h3>
            <p class="text-sm text-gray-500 mb-4">
              This stacked bar chart shows the number of applications for each job posting, broken down by stage.
            </p>
          </div>
          <VueECharts :option="stackedBarOption" style="height: 350px; width: 100%;" />
        </div>

        <!-- Scatter Plot -->
        <div class="bg-white rounded-xl shadow p-6 flex flex-col">
          <div>
            <h3 class="text-lg font-semibold mb-2 text-gray-700">Applications by Experience & Job</h3>
            <p class="text-sm text-gray-500 mb-4">
              This scatter plot visualizes the relationship between years of experience and the number of applications for each job.
            </p>
          </div>
          <VueECharts :option="scatterOption" style="height: 350px; width: 100%;" />
        </div>

        <!-- Line Chart -->
        <div class="bg-white rounded-xl shadow p-6 flex flex-col">
          <div>
            <h3 class="text-lg font-semibold mb-2 text-gray-700">Applications Over Time</h3>
            <p class="text-sm text-gray-500 mb-4">
              This line chart displays the trend of applications received over the selected period.
            </p>
          </div>
          <VueECharts :option="lineOption" style="height: 350px; width: 100%;" />
        </div>

        <!-- Area Chart -->
        <div class="bg-white rounded-xl shadow p-6 flex flex-col">
          <div>
            <h3 class="text-lg font-semibold mb-2 text-gray-700">Applications per Department Over Time</h3>
            <p class="text-sm text-gray-500 mb-4">
              This area chart shows the number of applications per department over the selected period.
            </p>
          </div>
          <VueECharts :option="areaOption" style="height: 350px; width: 100%;" />
        </div>
      </div>

      <!-- SUMMARY PARAGRAPH -->
      <div class="bg-white rounded-xl shadow p-6 mt-10">
        <h3 class="text-lg font-semibold mb-2 text-gray-700">Summary</h3>
        <p class="text-gray-700 whitespace-pre-line">{{ summaryText }}</p>
      </div>
    </div>
  </AppLayout>
</template>