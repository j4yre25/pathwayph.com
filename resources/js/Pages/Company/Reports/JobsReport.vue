<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import VueECharts from "vue-echarts";
import { ref, watch, computed } from "vue";
import { router } from '@inertiajs/vue3';

const props = defineProps({
  // JobOverview
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
  // DeptWise
  departmentCounts: Array,
  stackedData: Object,
  roleLevels: Array,
  // JobPostTrends
  jobsList: Object,
  jobPostingTrends: Array,
  areaChartLabels: Array,
  areaChartSeries: Array,
  departments: Array,
  statuses: Array,
  // EmployType
  typeCountsEmp: Object,
  departmentsEmp: Array,
  columnData: Object,
  types: Array,
  // SalaryInsights
  boxPlotData: Array,
  histogramBins: Array,
  // JobPerformance
  genderCounts: Object,
  ethnicityCounts: Object,
  jobRoles: Array,
  roleGenderData: Object,
  genders: Array,
  ethnicities: Array,
});

// --- JobOverview logic ---
const datePreset = ref(props.filters?.date_preset || 'overall');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');
const experienceLevel = ref(props.filters?.experience_level || '');
const workEnvironment = ref(props.filters?.work_environment || '');
const selectedType = ref(props.filters?.job_type || '');
const selectedStatus = ref(props.filters?.status || '');

let debounceTimer = null;
function debouncedApply() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => applyFilters(), 350);
}
function applyFilters() {
  const query = {
    date_preset: datePreset.value,
    date_from: datePreset.value === 'custom' ? (dateFrom.value || null) : null,
    date_to: datePreset.value === 'custom' ? (dateTo.value || null) : null,
    experience_level: experienceLevel.value || null,
    work_environment: workEnvironment.value || null,
    job_type: selectedType.value || null,
    status: selectedStatus.value || null,
  };
  router.get(route('company.reports.jobs'), query, { preserveState: true, preserveScroll: true, replace: true });
}
function resetFilters() {
  datePreset.value = 'overall';
  dateFrom.value = '';
  dateTo.value = '';
  experienceLevel.value = '';
  workEnvironment.value = '';
  selectedType.value = '';
  selectedStatus.value = '';
  applyFilters();
}
const filtersActive = computed(() =>
  !!(
    datePreset.value !== 'overall' ||
    experienceLevel.value ||
    workEnvironment.value ||
    selectedType.value ||
    selectedStatus.value ||
    (datePreset.value === 'custom' && dateFrom.value && dateTo.value)
  )
);
watch(datePreset, (val) => {
  if (val !== 'custom') {
    dateFrom.value = '';
    dateTo.value = '';
    debouncedApply();
  }
});
watch([experienceLevel, workEnvironment, selectedType, selectedStatus], () => {
  debouncedApply();
});
watch([dateFrom, dateTo], () => {
  if (datePreset.value === 'custom') {
    if (dateFrom.value && dateTo.value) debouncedApply();
  }
});
const page = ref(1);
const pageSize = 10;
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
const paginatedJobs = computed(() => {
  const jobs = filteredAllJobs.value;
  const start = (page.value - 1) * pageSize;
  return jobs.slice(start, start + pageSize);
});
const totalPages = computed(() => Math.ceil(filteredAllJobs.value.length / pageSize));
watch([selectedType, selectedStatus], () => { page.value = 1; });
const kpis = computed(() => [
  { label: "Total Job Posted", value: props.totalOpenings, color: "text-blue-600" },
  { label: "Active Listings", value: props.activeListings, color: "text-green-600" },
  { label: "Roles Filled", value: props.rolesFilled, color: "text-purple-600" },
]);
const typeCountsLocal = computed(() => {
  const jobs = filteredAllJobs.value;
  const counts = {};
  (props.jobTypes ?? []).forEach(jt => {
    const typeName = jt.type;
    counts[typeName] =
      jobs.filter(j =>
        Array.isArray(j.job_types) && j.job_types.some(rel => rel.type === typeName)
      ).length
      + jobs.filter(j => j.job_type === typeName).length;
  });
  return counts;
});
const topType = computed(() => {
  const entries = Object.entries(typeCountsLocal.value).filter(([, v]) => v > 0);
  entries.sort((a, b) => b[1] - a[1]);
  return entries.length ? entries[0][0] : "N/A";
});
const pieOption = computed(() => {
  const sel = selectedType.value;
  const data = Object.entries(typeCountsLocal.value)
    .map(([type, count]) => ({ name: type, value: count, selected: !!sel && type === sel }))
    .filter(d => d.value > 0);

  return {
    tooltip: { trigger: "item", formatter: "{b}: {c} ({d}%)" },
    legend: { orient: "vertical", left: 0, top: "middle" },
    series: [{
      name: "Job Type",
      type: "pie",
      radius: "60%",
      center: ["60%", "50%"],
      selectedMode: sel ? "single" : false,
      data,
      emphasis: {
        scale: true,
        scaleSize: 10,
        itemStyle: { shadowBlur: 10, shadowOffsetX: 0, shadowColor: "rgba(0,0,0,0.5)" }
      },
      label: { formatter: "{b}: {d}%", color: "#374151", fontWeight: "bold" },
    }]
  };
});
const textualReport = computed(() => {
  const total = props.totalOpenings ?? 0;
  const open = props.activeListings ?? 0;
  const filled = props.rolesFilled ?? 0;
  const top = topType.value;
  const dateRange = props.filters?.date_range_label || 'the selected period';

  if (total === 0) {
    return `No job postings were recorded for ${dateRange}. Try adjusting filters to see more data.`;
  }

  const filledPct = ((filled / total) * 100).toFixed(1);
  const openPct = ((open / total) * 100).toFixed(1);

  return `During ${dateRange}, there were ${total} job postings, of which ${open} (${openPct}%) are still active and ${filled} (${filledPct}%) have been successfully filled. ` +
         `The most frequently posted job type was "${top}", indicating where most of your hiring efforts are focused. ` +
         (filledPct >= 75
           ? "This shows strong progress in meeting hiring goals."
           : filledPct >= 40
             ? "Filling progress is moderate — you may want to focus on remaining openings."
             : "A majority of roles are still open, suggesting a need to accelerate recruitment.");
});

// --- DeptWise logic ---
const selectedDepartment = ref("");
const selectedRoleLevel = ref("");
const filteredJobsDept = computed(() => {
  let jobs = props.stackedData.allJobs ?? [];
  if (selectedDepartment.value) {
    jobs = jobs.filter(j => j.department === selectedDepartment.value);
  }
  if (selectedRoleLevel.value) {
    jobs = jobs.filter(j => j.job_experience_level === selectedRoleLevel.value);
  }
  return jobs;
});
const pageDept = ref(1);
const pageSizeDept = 10;
;

watch([selectedDepartment, selectedRoleLevel], () => { pageDept.value = 1; });
function goToPageDept(p) { pageDept.value = p; }
const barOption = computed(() => {
  const departments = [...new Set(filteredJobsDept.value.map(j => j.department))];
  const roleLevels = props.roleLevels || [];
  const series = roleLevels.map(level => ({
    name: level,
    type: "bar",
    stack: false,
    barGap: 0,
    barCategoryGap: "40%",
    data: departments.map(dept =>
      filteredJobsDept.value.filter(
        j => j.department === dept && j.job_experience_level === level
      ).length
    ),
    label: {
      show: true,
      position: "top",
      fontSize: 12,
      color: "#374151",
      formatter: "{c}"
    }
  }));

  return {
    tooltip: { trigger: "axis", axisPointer: { type: "shadow" } },
    legend: {
      data: roleLevels,
      top: 0,
      textStyle: { fontSize: 13 }
    },
    grid: { left: 40, right: 30, top: 50, bottom: 200 }, // more bottom for long labels
    xAxis: {
      type: "category",
      data: departments,
      name: "Department",
      nameLocation: "middle",
      nameGap: 168,
      axisLabel: {
        fontSize: 13,
        interval: 0,
        rotate: 80, 
        overflow: "truncate", // Prevent overlap, but show as much as possible
        width: 180, // Wider for long names
        lineHeight: 18,
        formatter: value => value // Show full name (if fits)
      }
    },
    yAxis: {
      type: "value",
      name: "Openings",
      nameLocation: "middle",
      nameGap: 40,
      axisLabel: { fontSize: 12 }
    },
    series
  };
});

const summaryReportDept = computed(() => {
  const deptCounts = props.departmentCounts ?? [];
  const total = deptCounts.reduce((sum, d) => sum + (d.total ?? 0), 0);

  if (!deptCounts.length || total === 0) {
    return "There are currently no job openings recorded across departments.";
  }
  const maxCount = Math.max(...deptCounts.map(d => d.total ?? 0));
  const topDepts = deptCounts
    .filter(d => (d.total ?? 0) === maxCount)
    .map(d => `"${d.department}"`);
  const topPercent = ((maxCount / total) * 100).toFixed(1);
  const topLabel = topDepts.length > 1
    ? `Departments ${topDepts.join(" and ")}`
    : `Department ${topDepts[0]}`;
  const roleLevelCounts = {};
  const jobs = props.stackedData?.allJobs ?? [];
  for (const role of props.roleLevels ?? []) {
    roleLevelCounts[role] = jobs.filter(j => j.job_experience_level === role).length;
  }
  const topRoleLevelEntry = Object.entries(roleLevelCounts)
    .sort((a, b) => b[1] - a[1])[0];
  const roleLine = topRoleLevelEntry && topRoleLevelEntry[1] > 0
    ? `The most common role level across departments is "${topRoleLevelEntry[0]}", with ${topRoleLevelEntry[1]} listings.`
    : "";
  return `As of now, there are ${total} job openings across all departments. 
${topLabel} currently ${topDepts.length > 1 ? 'share' : 'has'} the highest number of postings, each accounting for ${topPercent}% of the total (${maxCount} positions). 
${roleLine}`;
});

// --- JobPostTrends logic ---
const selectedDepartmentTrend = ref(props.filters?.department || "");
const selectedStatusTrend = ref(props.filters?.status || "");
const dateFromTrend = ref(props.filters?.date_from || "");
const pageTrend = ref(props.jobsList?.current_page || 1);
function applyFiltersTrend(newPage = 1) {
  router.get(
    route("company.reports.jobs"),
    {
      department: selectedDepartmentTrend.value,
      status: selectedStatusTrend.value,
      date_from: dateFromTrend.value,
      page: newPage,
    },
    { preserveState: true, replace: true }
  );
}
watch([selectedDepartmentTrend, selectedStatusTrend, dateFromTrend], () => applyFiltersTrend(1));
watch(pageTrend, (val) => applyFiltersTrend(val));
const lineOption = computed(() => ({
  tooltip: { trigger: "axis" },
  xAxis: {
    type: "category",
    data: props.jobPostingTrends.map(item => item.month),
    name: "Month",
    nameLocation: "middle",
    nameGap: 30,
  },
  yAxis: { type: "value", name: "Job Posts", nameLocation: "middle", nameGap: 40 },
  series: [
    {
      name: "Job Posts",
      type: "line",
      areaStyle: {},
      smooth: true,
      data: props.jobPostingTrends.map(item => item.total),
    },
  ],
}));
const areaOption = computed(() => ({
  tooltip: { trigger: "axis" },
  legend: { top: "top" },
  grid: { top: 90 },
  xAxis: {
    type: "category",
    boundaryGap: false,
    data: props.areaChartLabels || [],
    name: "Month",
    nameLocation: "middle",
    nameGap: 30,
  },
  yAxis: { type: "value", name: "Job Posts", nameLocation: "middle", nameGap: 40 },
  series: props.areaChartSeries || [],
}));
const summaryInsightTrend = computed(() => {
  const trends = props.jobPostingTrends || [];
  const areaSeries = props.areaChartSeries || [];
  const total = trends.reduce((sum, row) => sum + (row.total || 0), 0);
  const selectedDept = selectedDepartmentTrend.value;
  const status = selectedStatusTrend.value;
  const date = dateFromTrend.value;
  const statusPart = status ? ` with status <strong>${status}</strong>` : "";
  const datePart = date
    ? ` starting from <strong>${new Date(date).toLocaleDateString("en-US", { month: "long", year: "numeric" })}</strong>`
    : "";
  if (!total) {
    return `<strong>No job postings</strong> were recorded${statusPart}${datePart}.`;
  }
  const formatMonth = (str) => {
    const [year, month] = str.split("-");
    return new Date(Number(year), Number(month) - 1).toLocaleDateString("en-US", {
      month: "long",
      year: "numeric"
    });
  };
  const deptTotals = areaSeries.map(series => ({
    name: series.name,
    total: series.data.reduce((a, b) => a + b, 0),
    monthly: series.data
  }));
  if (selectedDept) {
    const deptEntry = deptTotals.find(d => d.name === selectedDept);
    const deptTotal = deptEntry?.total || 0;
    if (!deptEntry || deptTotal === 0) {
      return `
        A total of <strong>${total} job postings</strong> were recorded${statusPart}${datePart}.
        However, <strong>${selectedDept}</strong> had no recorded postings in the selected period.
      `;
    }
    const deptMonthly = deptEntry.monthly || [];
    const peakCount = Math.max(...deptMonthly);
    const peakIndices = deptMonthly
      .map((val, idx) => val === peakCount ? idx : -1)
      .filter(idx => idx !== -1);
    const peakMonths = peakIndices
      .map(idx => props.areaChartLabels?.[idx])
      .filter(Boolean)
      .map(formatMonth);
    const peakText = peakMonths.length === 0
      ? `There was no peak posting month for <strong>${selectedDept}</strong> in this period.`
      : peakMonths.length === 1
        ? `The highest job posting activity in this department was in <strong>${peakMonths[0]}</strong> with <strong>${peakCount} postings</strong>.`
        : `The peak months in this department were <strong>${peakMonths.join(", ")}</strong>, each with <strong>${peakCount} postings</strong>.`;
    const sorted = [...deptTotals].sort((a, b) => b.total - a.total);
    const rank = sorted.findIndex(d => d.name === selectedDept) + 1;
    const totalDepts = sorted.length;
    return `
      A total of <strong>${total} job postings</strong> were recorded${statusPart}${datePart}.
      Within the <strong>${selectedDept}</strong> department, there were <strong>${deptTotal} postings</strong> overall.
      ${peakText}
      This department ranked <strong>#${rank}</strong> out of <strong>${totalDepts}</strong> in posting volume.
    `;
  } else {
    const maxTrend = Math.max(...trends.map(row => row.total));
    const peakMonths = trends
      .filter(row => row.total === maxTrend)
      .map(row => formatMonth(row.month));
    const peakText = peakMonths.length === 1
      ? `The highest job posting activity was recorded in <strong>${peakMonths[0]}</strong> with <strong>${maxTrend} postings</strong>.`
      : `Peak posting months were <strong>${peakMonths.join(", ")}</strong>, each with <strong>${maxTrend} postings</strong>.`;
    const maxDeptTotal = Math.max(...deptTotals.map(d => d.total));
    const topDepartments = deptTotals
      .filter(d => d.total === maxDeptTotal)
      .map(d => d.name);
    const deptText = topDepartments.length === 1
      ? `The most active department was <strong>${topDepartments[0]}</strong> with <strong>${maxDeptTotal} postings</strong>.`
      : `Top departments with equal posting activity (<strong>${maxDeptTotal} postings</strong> each) include: <strong>${topDepartments.join(", ")}</strong>.`;
    return `
      A total of <strong>${total} job postings</strong> were recorded${statusPart}${datePart}.
      ${peakText}
      ${deptText}
    `;
  }
});

// --- SalaryInsights logic ---
const boxCategories = computed(() => props.boxPlotData.map(d => d.role));
const boxSeriesData = computed(() => props.boxPlotData.map(d => d.values));
const histCategories = computed(() => props.histogramBins.map(b => b.range));
const histCounts = computed(() => props.histogramBins.map(b => b.count));
const boxPlotOption = computed(() => ({
  tooltip: { trigger: "item" },
  grid: { left: 50, right: 30, top: 40, bottom: 60 },
  xAxis: { type: "category", data: boxCategories.value, name: "Job Role", axisLabel: { rotate: 25 } },
  yAxis: { type: "value", name: "Salary" },
  series: [{
    name: "Salary Range",
    type: "boxplot",
    itemStyle: { color: "#4F46E5" },
    data: boxSeriesData.value,
  }],
}));
const histogramOption = computed(() => ({
  tooltip: {
    trigger: "axis",
    formatter: params => {
      const idx = params[0].dataIndex
      const bin = props.histogramBins[idx]
      if (!bin) return ''
      const jobs = bin.jobs?.length ? `<br/>Jobs:<br/>${bin.jobs.join('<br/>')}` : ''
      return `${bin.range}<br/>Count: ${bin.count}${jobs}`
    }
  },
  grid: { left: 50, right: 30, top: 40, bottom: 80 },
  xAxis: {
    type: "category",
    data: histCategories.value,
    name: "Salary Range",
    axisLabel: { rotate: 35, fontSize: 11, lineHeight: 14 }
  },
  yAxis: { type: "value", name: "Jobs" },
  series: [{
    name: "Jobs",
    type: "bar",
    data: histCounts.value,
    itemStyle: { color: "#6366F1" }
  }]
}));

// --- JobPerformance logic ---
const genderPieOption = {
  tooltip: { trigger: "item" },
  legend: { top: "bottom" },
  series: [{
    name: "Gender",
    type: "pie",
    radius: "60%",
    data: Object.entries(props.genderCounts).map(([name, value]) => ({ name, value })),
    label: { formatter: "{b}: {c} ({d}%)" }
  }]
};
const ethnicityPieOption = {
  tooltip: { trigger: "item" },
  legend: { top: "bottom" },
  series: [{
    name: "Ethnicity",
    type: "pie",
    radius: "60%",
    data: Object.entries(props.ethnicityCounts).map(([name, value]) => ({ name, value })),
    label: { formatter: "{b}: {c} ({d}%)" }
  }]
};
const stackedOptionPerf = {
  tooltip: { trigger: "axis" },
  legend: { data: props.genders },
  xAxis: { type: "category", data: props.jobRoles, name: "Job Role" },
  yAxis: { type: "value", name: "Applicants" },
  series: props.genders.map(gender => ({
    name: gender,
    type: "bar",
    stack: "total",
    data: props.jobRoles.map(role => props.roleGenderData[gender][role] || 0),
  }))
};

const topDepartment = computed(() => {
  if (!props.departmentCounts?.length) return null;
  const max = Math.max(...props.departmentCounts.map(d => d.total));
  return props.departmentCounts.filter(d => d.total === max).map(d => d.department);
});

const quickInsights = computed(() => {
  return [
    {
      icon: 'fa-briefcase',
      title: 'Active Listings',
      main: props.activeListings ?? 0,
      note: `${(props.totalOpenings ?? 0) - (props.activeListings ?? 0)} inactive`
    },
    {
      icon: 'fa-layer-group',
      title: 'Top Job Type',
      main: topType.value,
      note: `${Object.values(typeCountsLocal.value).reduce((a,b)=>a+b,0)} typed entries`
    },
    {
      icon: 'fa-building',
      title: 'Top Department',
      main: topDepartment.value ? topDepartment.value.join(', ') : '—',
      note: topDepartment.value ? 'Highest openings' : 'No dept data'
    },
    {
      icon: 'fa-users',
      title: 'Gender Split',
      main: Object.entries(props.genderCounts || {}).map(([k,v])=>`${k[0]}:${v}`).join('  '),
      note: 'Applicants by gender'
    },
    {
      icon: 'fa-chart-line',
      title: 'Peak Month',
      main: props.jobPostingTrends?.length
        ? props.jobPostingTrends.reduce((a,b)=> b.total > a.total ? b : a).month
        : '—',
      note: 'Most postings'
    },
  ];
});

// OPTIONAL: small helper for header capitalization
const capitalize = s => (s || '').replace(/\b\w/g,m=>m.toUpperCase());


</script>

<template>
  <AppLayout title="Jobs Dashboard">
    <div class="min-h-screen bg-gray-50 py-8 px-4">
      <!-- Filters (compact) -->
      <div class="max-w-7xl mx-auto mb-8">
        <div class="bg-white border border-gray-200 rounded-xl px-4 py-3 grid gap-4 md:grid-cols-8 lg:grid-cols-10 items-end">
          <div class="col-span-2">
            <label class="block text-[11px] font-semibold text-gray-500 tracking-wide mb-1">Date Preset</label>
            <select v-model="datePreset" class="w-full h-8 text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
              <option value="last_7">Last 7 Days</option>
              <option value="last_30">Last 30 Days</option>
              <option value="this_month">This Month</option>
              <option value="last_month">Last Month</option>
              <option value="overall">Overall</option>
              <option value="custom">Custom</option>
            </select>
          </div>
          <div v-if="datePreset==='custom'" class="col-span-2">
            <label class="block text-[11px] font-semibold text-gray-500 tracking-wide mb-1">Start</label>
            <input type="date" v-model="dateFrom" class="w-full h-8 text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" />
          </div>
          <div v-if="datePreset==='custom'" class="col-span-2">
            <label class="block text-[11px] font-semibold text-gray-500 tracking-wide mb-1">End</label>
            <input type="date" v-model="dateTo" class="w-full h-8 text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" />
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-gray-500 tracking-wide mb-1">Exp. Level</label>
            <select v-model="experienceLevel" class="w-full h-8 text-sm border-gray-300 rounded-md">
              <option value="">All</option>
              <option v-for="lvl in filterOptions.experience_levels" :key="lvl" :value="lvl">{{ lvl }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-gray-500 tracking-wide mb-1">Environment</label>
            <select v-model="workEnvironment" class="w-full h-8 text-sm border-gray-300 rounded-md">
              <option value="">All</option>
              <option v-for="we in filterOptions.work_environments.filter(we => isNaN(Number(we.environment_type)))"
                      :key="we.environment_type" :value="we.environment_type">{{ we.environment_type }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-gray-500 tracking-wide mb-1">Job Type</label>
            <select v-model="selectedType" class="w-full h-8 text-sm border-gray-300 rounded-md">
              <option value="">All</option>
              <option v-for="jt in jobTypes" :key="jt.id" :value="jt.type">{{ jt.type }}</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-gray-500 tracking-wide mb-1">Status</label>
            <select v-model="selectedStatus" class="w-full h-8 text-sm border-gray-300 rounded-md">
              <option value="">All</option>
              <option value="Active">Active</option>
              <option value="Closed">Closed</option>
            </select>
          </div>
          <div class="flex justify-end">
            <button @click="resetFilters"
                    class="h-8 px-3 text-xs font-medium border border-gray-300 rounded-md text-gray-600 hover:bg-gray-100">
              Reset
            </button>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto space-y-10">
        <!-- TABLE: Move to top, full width, add Department & Salary Range columns -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 mb-10">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700">Current Listings (Filtered)</h3>
            <span class="text-[11px] text-gray-500">
              {{ filteredAllJobs.length }} result(s)
            </span>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
              <thead>
                <tr class="text-gray-500 font-medium border-b">
                  <th class="py-2 pr-4 text-left">Title</th>
                  <th class="py-2 pr-4 text-left">Type(s)</th>
                  <th class="py-2 pr-4 text-left">Status</th>
                  <th class="py-2 pr-4 text-left">Exp</th>
                  <th class="py-2 pr-4 text-left">Department</th>
                  <th class="py-2 pr-4 text-left">Salary Range</th>
                  <th v-if="filtersActive" class="py-2 pr-4 text-left">Vac</th>
                  <th v-if="filtersActive" class="py-2 pr-4 text-left">Filled</th>
                  <th v-else class="py-2 pr-4 text-left">Env</th>
                  <th class="py-2 pr-4 text-left">Posted</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="job in paginatedJobs" :key="job.id" class="border-b last:border-0 hover:bg-gray-50">
                  <td class="py-2 pr-4 font-medium text-gray-800">{{ job.job_title }}</td>
                  <td class="py-2 pr-4">
                    <span v-if="job.job_types?.length">{{ job.job_types.map(j=>j.type).join(', ') }}</span>
                    <span v-else class="text-gray-500">—</span>
                  </td>
                  <td class="py-2 pr-4 capitalize">{{ job.status }}</td>
                  <td class="py-2 pr-4">{{ job.job_experience_level || '—' }}</td>
                  <td class="py-2 pr-4">
                    <!-- Department: try job.department, fallback to '—' -->
                    {{ job.department || '—' }}
                  </td>
                  <td class="py-2 pr-4">
                    <!-- Salary Range: try job.salary, fallback to job.job_min_salary/job.job_max_salary -->
                    <span v-if="job.salary && (job.salary.job_min_salary || job.salary.job_max_salary)">
                      {{ job.salary.job_min_salary || '—' }} - {{ job.salary.job_max_salary || '—' }}
                    </span>
                    <span v-else-if="job.job_min_salary || job.job_max_salary">
                      {{ job.job_min_salary || '—' }} - {{ job.job_max_salary || '—' }}
                    </span>
                    <span v-else>—</span>
                  </td>
                  <td v-if="filtersActive" class="py-2 pr-4">{{ job.job_vacancies ?? '—' }}</td>
                  <td v-if="filtersActive" class="py-2 pr-4">{{ job.roles_filled ?? '—' }}</td>
                  <td v-else class="py-2 pr-4">
                    {{
                      filterOptions.work_environments.find(we =>
                        job.work_environment == we.id || job.work_environment == we.environment_type
                      )?.environment_type || job.work_environment || '—'
                    }}
                  </td>
                  <td class="py-2 pr-4">{{ (job.created_at || '').substring(0,10) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-if="totalPages > 1" class="flex justify-end mt-4 gap-1">
            <button
              v-for="p in totalPages"
              :key="p"
              @click="page = p"
              :class="[
                'px-2 h-7 rounded-md text-[11px] border',
                p===page ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-indigo-600 hover:bg-indigo-50'
              ]">
              {{ p }}
            </button>
          </div>
          <div v-else-if="!paginatedJobs.length" class="text-center text-gray-400 text-xs py-6">
            No jobs found
          </div>
        </div>

        <!-- Row 1: Overview Chart + Quick Insights -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
          <div class="xl:col-span-2 bg-white border border-gray-200 rounded-xl p-6">
            <div class="flex items-start justify-between mb-4">
              <h2 class="text-lg font-semibold text-gray-800">Job Overview</h2>
              <div class="flex gap-4">
                <div v-for="k in kpis" :key="k.label" class="text-center">
                  <div class="text-[11px] uppercase tracking-wide text-gray-500 font-medium">{{ k.label }}</div>
                  <div :class="['text-xl font-semibold mt-1', k.color]">{{ k.value }}</div>
                </div>
              </div>
            </div>
            <VueECharts
              v-if="pieOption.series[0].data.length"
              :option="pieOption"
              class="w-full"
              style="height:300px" />
            <div v-else class="h-[300px] flex items-center justify-center text-gray-400 text-sm">
              No chart data
            </div>
            <div class="mt-4 text-xs text-gray-600 leading-relaxed">
              {{ textualReport }}
            </div>
          </div>
          <div class="bg-white border border-gray-200 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Quick Insights</h2>
            <ul class="divide-y divide-gray-100">
              <li v-for="qi in quickInsights" :key="qi.title" class="py-3 flex">
                <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center mr-3 text-indigo-600">
                  <i :class="['fas', qi.icon]"></i>
                </div>
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-800">{{ qi.title }}</div>
                  <div class="text-[13px] text-gray-700">
                    <span class="font-semibold text-indigo-600">{{ qi.main }}</span>
                    <span v-if="qi.note" class="ml-1 text-gray-500">— {{ qi.note }}</span>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>

        <!-- Row 2: Dept Bar, Trends Line, Salary Box -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 flex flex-col">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">
                Openings by Department & Role Level
            </h3>
            <VueECharts
                :option="barOption"
                style="height:430px;min-width:100%;"
            />
            <div class="mt-4 text-[11px] text-gray-600 leading-snug">
                {{ summaryReportDept }}
            </div>
        </div>
        
        <!-- Row 3: Employment Type, Dept Role Levels (Stacked), Diversity -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="bg-white border border-gray-200 rounded-xl p-6">
              <h3 class="text-sm font-semibold text-gray-700 mb-4">Monthly Posting Trend</h3>
              <VueECharts :option="lineOption" style="height:260px" />
              <div class="mt-4 text-[11px] text-gray-600 leading-snug" v-html="summaryInsightTrend"></div>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl p-6">
              <h3 class="text-sm font-semibold text-gray-700 mb-4">Salary Ranges (Box Plot)</h3>
              <VueECharts v-if="boxPlotData.length" :option="boxPlotOption" style="height:260px" />
              <div v-else class="h-[260px] flex items-center justify-center text-gray-400 text-xs">No salary data</div>
            </div>
          <div class="bg-white border border-gray-200 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Employment Type Share</h3>
            <VueECharts :option="pieOptionEmp" style="height:260px" />
          </div>
          
          <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-6">
            <div>
              <h3 class="text-sm font-semibold text-gray-700 mb-3">Gender Diversity</h3>
              <VueECharts :option="genderPieOption" style="height:110px" />
            </div>
            <div v-if="ethnicities.length">
              <h3 class="text-sm font-semibold text-gray-700 mb-3">Ethnicity Diversity</h3>
              <VueECharts :option="ethnicityPieOption" style="height:110px" />
            </div>
          </div>
        </div>

        <!-- Full Width: Histogram + Job Table -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
          <div class="bg-white border border-gray-200 rounded-xl p-6 xl:col-span-1">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Salary Distribution</h3>
            <VueECharts v-if="histogramBins.length" :option="histogramOption" style="height:300px" />
            <div v-else class="h-[300px] flex items-center justify-center text-gray-400 text-xs">No histogram data</div>
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>