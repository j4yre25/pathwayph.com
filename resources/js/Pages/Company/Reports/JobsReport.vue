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

// --- Overall Filters ---
const datePreset = ref(props.filters?.date_preset || 'overall');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');
const experienceLevel = ref(props.filters?.experience_level || '');
// default to All = '' and normalize possible array from server
const workEnvironment = ref(Array.isArray(props.filters?.work_environment) ? '' : (props.filters?.work_environment ?? ''));
const selectedType = ref(props.filters?.job_type || '');
const selectedStatus = ref(props.filters?.status || '');
// Department: multi-select for overall filtering
const selectedDepartment = ref('');

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
    departments: selectedDepartment.value ? selectedDepartment.value : null,
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
  selectedDepartment.value = '';
  applyFilters();
}

const filtersActive = computed(() =>
  !!(
    datePreset.value !== 'overall' ||
    experienceLevel.value ||
    workEnvironment.value ||
    selectedType.value ||
    selectedStatus.value ||
    (selectedDepartment.value && selectedDepartment.value.length) ||
    (datePreset.value === 'custom' && dateFrom.value && dateTo.value)
  )
);

// Watchers for all filters
watch(datePreset, (val) => {
  if (val !== 'custom') {
    dateFrom.value = '';
    dateTo.value = '';
    debouncedApply();
  }
});
watch([experienceLevel, workEnvironment, selectedType, selectedStatus, selectedDepartment], () => {
  debouncedApply();
});
watch([dateFrom, dateTo], () => {
  if (datePreset.value === 'custom') {
    if (dateFrom.value && dateTo.value) debouncedApply();
  }
});

// Helpers
const deptNameOf = (job) => {
  return typeof job?.department === 'object'
    ? (job.department?.department_name || '')
    : (job?.department || '');
};
const formatCurrency = (v) => {
  if (v === null || v === undefined || v === '' || isNaN(Number(v))) return '—';
  return `₱${Number(v).toLocaleString()}`;
};
const salaryRangeText = (job) => {
  const min = job?.salary?.job_min_salary ?? job?.job_min_salary ?? null;
  const max = job?.salary?.job_max_salary ?? job?.job_max_salary ?? null;
  if (min === null && max === null) return '—';
  return `${formatCurrency(min)} - ${formatCurrency(max)}`;
};

// --- Filtered Data for ALL charts/tables ---
const filteredAllJobs = computed(() => {
  let jobs = props.allJobs ?? [];
  // type
  if (selectedType.value) {
    jobs = jobs.filter(job =>
      (Array.isArray(job.job_types) && job.job_types.some(jt => jt.type === selectedType.value)) ||
      job.job_type === selectedType.value
    );
  }
  // status
  if (selectedStatus.value) {
    jobs = jobs.filter(job =>
      selectedStatus.value === "Active" ? job.status === "open" : job.status === "closed"
    );
  }
  // departments 
  if (selectedDepartment.value) {
    jobs = jobs.filter(job => deptNameOf(job) === selectedDepartment.value);
  }
  // experience level
  if (experienceLevel.value) {
    jobs = jobs.filter(job => job.job_experience_level === experienceLevel.value);
  }
  // environment: accept direct string or related list
  if (workEnvironment.value) {
    const sel = String(workEnvironment.value);
    jobs = jobs.filter(job => {
      const direct = job.work_environment;
      const directMatch = direct !== undefined && String(direct) === sel;
      const relMatch = Array.isArray(job.work_environments)
        ? job.work_environments.some(we => String(we.environment_type) === sel || String(we.id) === sel)
        : false;
      return directMatch || relMatch;
    });
  }
  // date (client-side guard for custom; server already filters for presets)
  if (datePreset.value === 'custom' && dateFrom.value && dateTo.value) {
    const from = new Date(dateFrom.value);
    const to = new Date(dateTo.value);
    jobs = jobs.filter(job => {
      const d = new Date(job.created_at);
      return d >= from && d <= to;
    });
  }
  return jobs;
});

// Pagination
const page = ref(1);
const pageSize = 10;
const paginatedJobs = computed(() => {
  const jobs = filteredAllJobs.value;
  const start = (page.value - 1) * pageSize;
  return jobs.slice(start, start + pageSize);
});
const totalPages = computed(() => Math.max(1, Math.ceil((filteredAllJobs.value?.length || 0) / pageSize)));

watch([selectedType, selectedStatus, selectedDepartment, experienceLevel, workEnvironment, datePreset, dateFrom, dateTo], () => { page.value = 1; });

// KPIs and overview pie
const kpis = computed(() => [
  { label: "Total Job Posted", value: props.totalOpenings ?? 0, color: "text-blue-600" },
  { label: "Active Listings", value: props.activeListings ?? 0, color: "text-green-600" },
  { label: "Roles Filled", value: props.rolesFilled ?? 0, color: "text-purple-600" },
]);

const typeCountsLocal = computed(() => {
  const jobs = filteredAllJobs.value;
  const counts = {};
  (props.jobTypes ?? []).forEach(jt => {
    const typeName = jt.type;
    const relCnt = jobs.filter(j => Array.isArray(j.job_types) && j.job_types.some(r => r.type === typeName)).length;
    const directCnt = jobs.filter(j => j.job_type === typeName).length;
    counts[typeName] = relCnt + directCnt;
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
  const total = filteredAllJobs.value.length;
  const open = filteredAllJobs.value.filter(j => j.status === "open").length;
  const filled = props.rolesFilled ?? 0;
  const top = topType.value;
  const dateRange = filtersActive.value 
    ? (props.filters?.date_range_label || "the selected period")
    : "the entire available dataset";

  if (total === 0) {
    return `No job postings were recorded for ${dateRange}. Try adjusting filters or posting new openings.`;
  }

  const filledPct = total ? ((filled / total) * 100).toFixed(1) : '0.0';
  const openPct = total ? ((open / total) * 100).toFixed(1) : '0.0';

  return `For ${dateRange}, there were ${total} job postings. ${open} (${openPct}%) remain active while ${filled} (${filledPct}%) have been filled. The most common job type was "${top}".`;
});

// --- Department bar (by role level) computed from filteredAllJobs ---
const roleLevelsLocal = computed(() =>
  (props.roleLevels && props.roleLevels.length)
    ? props.roleLevels
    : Array.from(new Set(filteredAllJobs.value.map(j => j.job_experience_level).filter(Boolean)))
);
const departmentsLocal = computed(() =>
  Array.from(new Set(filteredAllJobs.value.map(deptNameOf).filter(Boolean)))
);
const barOption = computed(() => {
  const depts = departmentsLocal.value;
  const levels = roleLevelsLocal.value;
  const dataByDeptLevel = {};
  for (const d of depts) dataByDeptLevel[d] = {};
  filteredAllJobs.value.forEach(j => {
    const d = deptNameOf(j) || 'N/A';
    const lvl = j.job_experience_level || 'N/A';
    if (!dataByDeptLevel[d]) dataByDeptLevel[d] = {};
    dataByDeptLevel[d][lvl] = (dataByDeptLevel[d][lvl] || 0) + 1;
  });
  const series = levels.map(level => ({
    name: level,
    type: "bar",
    stack: false,
    barGap: 0,
    barCategoryGap: "40%",
    data: depts.map(d => dataByDeptLevel[d]?.[level] || 0),
    label: { show: true, position: "top", fontSize: 12, color: "#374151", formatter: "{c}" }
  }));
  return {
    tooltip: { trigger: "axis", axisPointer: { type: "shadow" } },
    legend: { data: levels, top: 0, textStyle: { fontSize: 13 } },
    grid: { left: 40, right: 30, top: 50, bottom: 200 },
    xAxis: {
      type: "category",
      data: depts,
      name: "Department",
      nameLocation: "middle",
      nameGap: 168,
      axisLabel: { fontSize: 13, interval: 0, rotate: 80, overflow: "truncate", width: 180, lineHeight: 18 }
    },
    yAxis: { type: "value", name: "Openings", nameLocation: "middle", nameGap: 40, axisLabel: { fontSize: 12 } },
    series
  };
});
const summaryReportDept = computed(() => {
  const jobs = filteredAllJobs.value;
  if (!jobs.length) return "No department-level data is available for the selected filters.";

  const countsByDept = jobs.reduce((acc, j) => {
    const d = deptNameOf(j) || 'Unassigned';
    acc[d] = (acc[d] || 0) + 1;
    return acc;
  }, {});
  
  const total = jobs.length;
  const max = Math.max(...Object.values(countsByDept));
  const topDepts = Object.entries(countsByDept)
    .filter(([, v]) => v === max)
    .map(([k]) => `"${k}"`);
  
  const pct = ((max / total) * 100).toFixed(1);

  return `There are ${total} job openings across ${Object.keys(countsByDept).length} department(s). ` +
         `${topDepts.length > 1 ? 'Departments ' : 'Department '}${topDepts.join(' and ')} ` +
         `account${topDepts.length > 1 ? '' : 's'} for the highest share (${pct}% of all postings).`;
});


// --- Trends (line and stacked area) computed from filteredAllJobs ---
const monthsLocal = computed(() => {
  const s = new Set();
  filteredAllJobs.value.forEach(j => {
    if (!j.created_at) return;
    const d = new Date(j.created_at);
    const key = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;
    s.add(key);
  });
  return Array.from(s).sort(); // YYYY-MM
});
const lineOption = computed(() => {
  const monthKeys = monthsLocal.value;
  const monthCounts = monthKeys.map(m => {
    return filteredAllJobs.value.filter(j => {
      if (!j.created_at) return false;
      const d = new Date(j.created_at);
      const key = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;
      return key === m;
    }).length;
  });
  return {
    tooltip: { trigger: "axis" },
    xAxis: { type: "category", data: monthKeys, nameLocation: "middle", nameGap: 30 },
    yAxis: { type: "value", name: "Job Posts", nameLocation: "middle", nameGap: 40 },
    series: [{ name: "Job Posts", type: "line", areaStyle: {}, smooth: true, data: monthCounts }]
  };
});
const areaOption = computed(() => {
  const monthKeys = monthsLocal.value;
  const byDeptMonth = {};
  filteredAllJobs.value.forEach(j => {
    const dept = deptNameOf(j) || 'N/A';
    if (!j.created_at) return;
    const d = new Date(j.created_at);
    const key = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;
    if (!byDeptMonth[dept]) byDeptMonth[dept] = {};
    byDeptMonth[dept][key] = (byDeptMonth[dept][key] || 0) + 1;
  });
  const series = Object.keys(byDeptMonth).map(dept => {
    const data = monthKeys.map(m => byDeptMonth[dept][m] || 0);
    return { name: dept, type: 'line', areaStyle: {}, stack: 'total', data };
  });
  return {
    tooltip: { trigger: "axis" },
    legend: { top: "top" },
    grid: { top: 90 },
    xAxis: { type: "category", boundaryGap: false, data: monthKeys, name: "Month", nameLocation: "middle", nameGap: 30 },
    yAxis: { type: "value", name: "Job Posts", nameLocation: "middle", nameGap: 40 },
    series
  };
});
const summaryInsightTrend = computed(() => {
  const total = filteredAllJobs.value.length;
  if (!total) return `No job postings were recorded for the selected filters.`;
  const months = monthsLocal.value;
  const monthCounts = months.map(m => filteredAllJobs.value.filter(j => {
    const d = new Date(j.created_at);
    const key = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;
    return key === m;
  }).length);
  const max = Math.max(...monthCounts);
  const peaks = months.filter((m, i) => monthCounts[i] === max);
  return `A total of ${total} job postings were recorded. Peak month(s): ${peaks.join(', ')} with ${max} postings.`;
});

// --- SalaryInsights logic (fallback to client-side if props.histogramBins missing) ---
const computedBins = computed(() => {
  // build from filteredAllJobs on the fly
  const mids = [];
  filteredAllJobs.value.forEach(job => {
    const minS = job?.salary?.job_min_salary ?? job?.job_min_salary ?? null;
    const maxS = job?.salary?.job_max_salary ?? job?.job_max_salary ?? null;
    if (minS == null && maxS == null) return;
    const mid = (minS != null && maxS != null) ? (Number(minS) + Number(maxS)) / 2
               : (minS != null ? Number(minS) : Number(maxS));
    if (!isNaN(mid) && mid > 0) mids.push(mid);
  });
  if (!mids.length) return [];
  const binSize = 10000;
  let min = Math.floor(Math.min(...mids) / binSize) * binSize;
  let max = Math.ceil(Math.max(...mids) / binSize) * binSize;
  if (min === max) max = min + binSize;
  const bins = [];
  for (let b = min; b < max; b += binSize) {
    bins.push({ range: `${b}-${b + binSize - 1}`, count: 0, jobs: [] });
  }
  filteredAllJobs.value.forEach(job => {
    const minS = job?.salary?.job_min_salary ?? job?.job_min_salary ?? null;
    const maxS = job?.salary?.job_max_salary ?? job?.job_max_salary ?? null;
    if (minS == null && maxS == null) return;
    const mid = (minS != null && maxS != null) ? (Number(minS) + Number(maxS)) / 2
               : (minS != null ? Number(minS) : Number(maxS));
    if (isNaN(mid) || mid <= 0) return;
    const idx = Math.floor((mid - min) / binSize);
    if (bins[idx]) {
      bins[idx].count++;
      bins[idx].jobs.push(job.job_title);
    }
  });
  return bins;
});
const binsToUse = computed(() => (props.histogramBins && props.histogramBins.length) ? props.histogramBins : computedBins.value);
const histCategories = computed(() => (binsToUse.value ?? []).map(b => b.range));
const histCounts = computed(() => (binsToUse.value ?? []).map(b => b.count));
const histogramOption = computed(() => ({
  tooltip: {
    trigger: "axis",
    formatter: params => {
      const idx = params[0].dataIndex;
      const bin = (binsToUse.value ?? [])[idx];
      if (!bin) return '';
      const jobs = bin.jobs?.length ? `<br/>Jobs:<br/>${bin.jobs.join('<br/>')}` : '';
      return `${bin.range}<br/>Count: ${bin.count}${jobs}`;
    }
  },
  grid: { left: 50, right: 30, top: 40, bottom: 80 },
  xAxis: {
    type: "category",
    data: histCategories.value,
    name: "Salary Range (₱)",
    nameLocation: "middle",
    nameGap: 65,
    axisLabel: { rotate: 35, fontSize: 12, color: "#374151", lineHeight: 16, overflow: "truncate", width: 120 }
  },
  yAxis: { type: "value", name: "Jobs", nameLocation: "middle", nameGap: 30, axisLabel: { fontSize: 12, color: "#374151" } },
  series: [{ name: "Jobs", type: "bar", data: histCounts.value, itemStyle: { color: "#6366F1" }, emphasis: { itemStyle: { color: "#4338CA" } } }],
}));

// --- JobPerformance logic (server-filtered; will refresh via router.get) ---
const genderPieOption = computed(() => ({
  tooltip: { trigger: "item", formatter: "{b}: {c} ({d}%)" },
  legend: { type: "scroll", bottom: 0, left: "center", orient: "horizontal", textStyle: { color: "#374151", fontSize: 13 }, padding: [5, 0, 0, 0] },
  series: [{
    name: "Gender",
    type: "pie",
    radius: ["45%", "70%"],
    center: ["50%", "50%"],
    minAngle: 5,
    avoidLabelOverlap: true,
    labelLine: { show: true, length: 12, length2: 8, smooth: true },
    label: { show: true, formatter: "{b}: {d}%", color: "#374151", fontWeight: "bold", fontSize: 13, overflow: "break" },
    data: Object.entries(props.genderCounts || {}).map(([name, value]) => ({
      name, value,
      itemStyle: { color: name.toLowerCase().includes("female") ? "#EC4899" : name.toLowerCase().includes("male") ? "#3B82F6" : "#A3A3A3" }
    })),
  }]
}));
const ethnicityPieOption = computed(() => {
  const raw = props.ethnicityCounts || {};
  const palette = ["#F59E42","#60A5FA","#10B981","#F472B6","#6366F1","#EC4899","#3B82F6","#A3A3A3","#FBBF24","#34D399","#818CF8","#F87171","#F9A8D4","#6EE7B7","#FDE68A","#C7D2FE"];
  return {
    tooltip: { trigger: "item", formatter: "{b}: {c} ({d}%)" },
    legend: { type: "scroll", bottom: 0, left: "center", orient: "horizontal", textStyle: { color: "#374151", fontSize: 13 }, padding: [5, 0, 0, 0] },
    series: [{
      name: "Ethnicity",
      type: "pie",
      radius: ["45%","70%"],
      center: ["50%","50%"],
      minAngle: 5,
      avoidLabelOverlap: true,
      labelLine: { show: true, length: 12, length2: 5, smooth: true },
      label: { show: true, formatter: "{b}: {d}%", color: "#374151", fontWeight: "bold", fontSize: 13, overflow: "break" },
      data: Object.entries(raw)
        .filter(([name, value]) => !!name && value > 0)
        .map(([name, value], idx) => ({ name: String(name).trim(), value, itemStyle: { color: palette[idx % palette.length] } })),
    }]
  };
});

// Quick insights
const topDepartment = computed(() => {
  const jobs = filteredAllJobs.value;
  if (!jobs.length) return null;
  const byDept = jobs.reduce((acc,j)=>{ const d=deptNameOf(j)||'N/A'; acc[d]=(acc[d]||0)+1; return acc; }, {});
  const max = Math.max(...Object.values(byDept));
  return Object.entries(byDept).filter(([,v])=>v===max).map(([k])=>k);
});
const quickInsights = computed(() => ([
  { icon: 'fa-briefcase', title: 'Active Listings', main: props.activeListings ?? 0, note: `${(props.totalOpenings ?? 0) - (props.activeListings ?? 0)} inactive` },
  { icon: 'fa-layer-group', title: 'Top Job Type', main: topType.value, note: `${Object.values(typeCountsLocal.value).reduce((a,b)=>a+b,0)} typed entries` },
  { icon: 'fa-building', title: 'Top Department', main: topDepartment.value ? topDepartment.value.join(', ') : '—', note: topDepartment.value ? 'Highest openings' : 'No dept data' },
  { icon: 'fa-users', title: 'Gender Split', main: Object.entries(props.genderCounts || {}).map(([k,v])=>`${k[0]}:${v}`).join('  '), note: 'Applicants by gender' },
  { icon: 'fa-chart-line', title: 'Peak Month', main: (monthsLocal.value?.length ? monthsLocal.value[ (monthsLocal.value.length-1) ] : '—'), note: 'Most postings' },
]));
const capitalize = s => (s || '').replace(/\b\w/g,m=>m.toUpperCase());

const overallSummary = computed(() => {
  const total = filteredAllJobs.value.length;
  if (!total) return "No job postings match the current filters. Consider adjusting filters or reviewing posting activity.";

  const open = filteredAllJobs.value.filter(j => j.status === "open").length;
  const closed = total - open;
  const topDept = topDepartment.value ? topDepartment.value.join(", ") : "N/A";
  const peakMonths = monthsLocal.value.length ? monthsLocal.value.slice(-1) : ["N/A"];
  
  return `Summary: A total of ${total} job postings were found, with ${open} still active and ${closed} closed. 
  The department with the highest postings is ${topDept}, while the most common job type is "${topType.value}". 
  Posting activity peaked during ${peakMonths.join(", ")}. Use this insight to balance job distribution and schedule postings more effectively.`;
});
</script>

<template>
  <AppLayout title="Jobs Dashboard">
    <div class="min-h-screen bg-gray-50 py-8 px-4">
      <!-- Filters (overall, affect all charts/tables) -->
      <div class="max-w-7xl mx-auto mb-8">
        <div class="bg-white border border-gray-200 rounded-xl px-4 py-2 grid gap-4 md:grid-cols-9 lg:grid-cols-12 items-end">
          <div class="col-span-2">
            <label class="block text-[11px] font-semibold text-gray-500 tracking-wide mb-1">Date Preset</label>
            <select v-model="datePreset" class="w-full text-sm border-gray-300 rounded-md min-h-8 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
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
            <input type="date" v-model="dateFrom" class="w-full text-sm border-gray-300 rounded-md min-h-8 focus:ring-indigo-500 focus:border-indigo-500 bg-white" />
          </div>
          <div v-if="datePreset==='custom'" class="col-span-2">
            <label class="block text-[11px] font-semibold text-gray-500 tracking-wide mb-1">End</label>
            <input type="date" v-model="dateTo" class="w-full text-sm border-gray-300 rounded-md min-h-8 focus:ring-indigo-500 focus:border-indigo-500 bg-white" />
          </div>
          <div class="col-span-2">
            <label class="block text-[11px] font-semibold text-gray-500 tracking-wide mb-1">Exp. Level</label>
            <select v-model="experienceLevel" class="w-full text-sm border-gray-300 rounded-md min-h-8 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
              <option value="">All</option>
              <option v-for="lvl in filterOptions.experience_levels" :key="lvl" :value="lvl">{{ lvl }}</option>
            </select>
          </div>
          <div class="col-span-2">
            <label class="block text-[11px] font-semibold text-gray-500 tracking-wide mb-1">Environment</label>
            <select v-model="workEnvironment" class="w-full text-sm border-gray-300 rounded-md min-h-8 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
              <option value="">All</option>
              <option v-for="we in filterOptions.work_environments.filter(we => isNaN(Number(we.environment_type)))"
                      :key="we.environment_type" :value="we.environment_type">{{ we.environment_type }}</option>
            </select>
          </div>
          <div class="col-span-2">
            <label class="block text-[11px] font-semibold text-gray-500 tracking-wide mb-1">Job Type</label>
            <select v-model="selectedType" class="w-full text-sm border-gray-300 rounded-md min-h-8 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
              <option value="">All</option>
              <option v-for="jt in jobTypes" :key="jt.id" :value="jt.type">{{ jt.type }}</option>
            </select>
          </div>
          <div >
            <label class="block text-[11px] font-semibold text-gray-500 tracking-wide mb-1">Status</label>
            <select v-model="selectedStatus" class="w-full text-sm border-gray-300 rounded-md min-h-8 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
              <option value="">All</option>
              <option value="Active">Active</option>
              <option value="Closed">Closed</option>
            </select>
          </div>
          <div class="col-span-2">
            <label class="block text-[11px] font-semibold text-gray-500 tracking-wide mb-1">Department</label>
            <select
              v-model="selectedDepartment"
              class="w-full text-sm border-gray-300 rounded-md min-h-8 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
              <option value="">All</option>
              <option v-for="dept in (departments || [])" :key="dept" :value="dept">{{ dept }}</option>
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
                      {{
                        job.department && typeof job.department === 'object'
                          ? job.department.department_name || '—'
                          : job.department || '—'
                      }}
                  </td>
                  <td class="py-2 pr-4">
                    {{ salaryRangeText(job) }}
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
         <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
          <!-- 75% column -->
          <div class="lg:col-span-3 bg-white border border-gray-200 rounded-xl p-6  shadow-sm">
            <div class="space-y-6">
              <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Monthly Posting Trend & Department Activity</h3>
                <VueECharts :option="lineOption" class="w-full" style="height:280px" />
              </div>
              <div class="border-t border-gray-100">
                <VueECharts :option="areaOption" class="w-full" style="height:380px" />
              </div>
              <div class="text-[11px] text-gray-600 leading-snug" v-html="summaryInsightTrend"></div>
            </div>
          </div>

          <!-- 25% column -->
          <div class="lg:col-span-1 bg-white border border-gray-200 rounded-xl p-6 shadow-sm flex flex-col justify-center space-y-6" style="min-height:640px;">
            <div class="flex flex-col justify-center h-[50%]">
              <h3 class="text-sm font-semibold text-gray-700 mb-3 text-center">Gender Diversity</h3>
              <div class="grid place-items-center">
                <VueECharts
                  :option="genderPieOption"
                  class="w-full max-w-[260px] mx-auto"
                  style="height:260px" />
              </div>
            </div>
            <div v-if="ethnicityPieOption.series[0].data.length" class="border-t border-gray-100 pt-4 flex flex-col justify-center h-[50%]">
              <h3 class="text-sm font-semibold text-gray-700 mb-3 text-center">Ethnicity Diversity</h3>
              <div class="grid place-items-center">
                <VueECharts
                  :option="ethnicityPieOption"
                  class="w-full max-w-[260px] mx-auto"
                  style="height:260px" />
              </div>
            </div>
          </div>
        </div>

        <!-- Full Width: Histogram + Job Table -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 xl:col-span-1">
          <h3 class="text-sm font-semibold text-gray-700 mb-4">Salary Distribution</h3>
          <div class="w-full">
            <VueECharts v-if="histogramBins.length" :option="histogramOption" style="height:300px; width:100%;" />
            <div v-else class="h-[300px] flex items-center justify-center text-gray-400 text-xs">No histogram data</div>
          </div>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-900 mt-8">
          <p class="font-semibold mb-1">📊 Summary Insight:</p>
          <p v-html="overallSummary"></p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>