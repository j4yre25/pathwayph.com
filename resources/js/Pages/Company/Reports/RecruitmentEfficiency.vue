<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import VueECharts from "vue-echarts";
import { ref, watch, computed } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
  tthLine: { type: Object, default: () => ({ labels: [], series: [] }) },
  stageFunnel: { type: Array, default: () => [] },
  offerPie: { type: Array, default: () => [] },
  stageTimeByDept: { type: Object, default: () => ({ categories: [], series: [] }) },
  filters: { type: Object, default: () => ({}) },
  departments: { type: Array, default: () => [] },
  jobs: { type: Array, default: () => [] },
  programs: { type: Array, default: () => [] },
  stages: { type: Array, default: () => [] },
});

const local = ref({
  department: props.filters.department || "",
  job_id: props.filters.job_id || "",
  program_id: props.filters.program_id || "",
  stage: props.filters.stage || "",
  date_preset: props.filters.date_preset || "last_90",
  date_from: props.filters.date_from || "",
  date_to: props.filters.date_to || "",
});

watch(local, () => {
  const q = { ...local.value };
  if (q.date_preset !== "custom") {
    q.date_from = undefined;
    q.date_to = undefined;
  }
  router.get(route("company.reports.efficiency"), q, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  });
}, { deep: true });

function resetFilters() {
  local.value = {
    department: "",
    job_id: "",
    program_id: "",
    stage: "",
    date_preset: "last_90",
    date_from: "",
    date_to: "",
  };
}

// Line: Time-to-Hire per month
const tthLineOption = {
  tooltip: { trigger: "axis" },
  legend: { data: (props.tthLine.series || []).map(s => s.name) },
  xAxis: { type: "category", data: props.tthLine.labels, name: "Month" },
  yAxis: { type: "value", name: "Avg TTH (days)" },
  series: props.tthLine.series || [],
};

// Funnel: Stage Conversion
const funnelOption = {
  tooltip: { trigger: "item", formatter: "{b}: {c}" },
  series: [
    {
      name: "Stage Conversion",
      type: "funnel",
      left: "10%",
      top: 20,
      bottom: 20,
      width: "80%",
      min: 0,
      maxSize: "80%",
      sort: "descending",
      gap: 4,
      label: { show: true, formatter: "{b}: {c}" },
      labelLine: { length: 10, lineStyle: { width: 1 } },
      itemStyle: { borderColor: "#fff", borderWidth: 1 },
      emphasis: { label: { fontSize: 14 } },
      data: props.stageFunnel || [],
    },
  ],
};

// Pie: Offer Acceptance Rate
const pieOption = {
  tooltip: { trigger: "item", formatter: "{b}: {c} ({d}%)" },
  legend: { orient: "horizontal", bottom: 0 },
  series: [
    {
      name: "Offer Outcomes",
      type: "pie",
      radius: ["40%", "70%"],
      avoidLabelOverlap: true,
      itemStyle: { borderRadius: 6, borderColor: "#fff", borderWidth: 2 },
      label: { show: true, formatter: "{b}: {d}%" },
      data: props.offerPie || [],
    },
  ],
};

// Stacked horizontal: Avg time in each stage by department
const stageTimeOption = {
  tooltip: { trigger: "axis", axisPointer: { type: "shadow" } },
  legend: { top: 0 },
  grid: { left: 100, right: 20, bottom: 20, top: 40 },
  xAxis: { type: "value", name: "Avg Days" },
  yAxis: { type: "category", data: props.stageTimeByDept.categories || [] },
  series: (props.stageTimeByDept.series || []).map(s => ({
    ...s,
    type: "bar",
    stack: "time",
  })),
};

const summaryItems = computed(() => {
  const items = [];

  // Time-to-hire trend
  if (props.tthLine.series?.length) {
    const data = props.tthLine.series[0]?.data || [];
    const labels = props.tthLine.labels || [];
    const latestIndex = data.length - 1;
    const prevIndex = latestIndex - 1;

    if (latestIndex >= 0) {
      const latestMonth = labels[latestIndex];
      const avgTTH = Number(data[latestIndex] ?? 0);
      const prevTTH = prevIndex >= 0 ? Number(data[prevIndex] ?? 0) : null;

      if (prevTTH !== null) {
        if (avgTTH < prevTTH) {
          items.push(`Average time-to-hire in ${latestMonth} is ${avgTTH} days (↓ ${(prevTTH - avgTTH).toFixed(1)} days vs. prior).`);
        } else if (avgTTH > prevTTH) {
          items.push(`Average time-to-hire in ${latestMonth} is ${avgTTH} days (↑ ${(avgTTH - prevTTH).toFixed(1)} days vs. prior).`);
        } else {
          items.push(`Average time-to-hire in ${latestMonth} is ${avgTTH} days (no change vs. prior).`);
        }
      } else {
        items.push(`Average time-to-hire in ${latestMonth} is ${avgTTH} days.`);
      }
    }
  }

  // Funnel summary
  if (props.stageFunnel?.length) {
    const topStage = props.stageFunnel[0];
    const lastStage = props.stageFunnel[props.stageFunnel.length - 1];
    const topVal = Number(topStage?.value ?? 0);
    const lastVal = Number(lastStage?.value ?? 0);
    const conversionRate = topVal ? ((lastVal / topVal) * 100).toFixed(1) : '0.0';
    items.push(`${topVal} candidates entered the funnel; ${lastVal} reached ${lastStage?.name} (conversion ${conversionRate}%).`);
  }

  // Offer acceptance
  if (props.offerPie?.length) {
    const totalOffers = props.offerPie.reduce((acc, o) => acc + Number(o.value ?? 0), 0);
    const accepted = props.offerPie.find(o => (o.name || '').toLowerCase().includes('accepted'));
    const acceptedVal = Number(accepted?.value ?? 0);
    if (totalOffers > 0) {
      const acceptanceRate = ((acceptedVal / totalOffers) * 100).toFixed(1);
      items.push(`Offer acceptance: ${acceptedVal}/${totalOffers} accepted (${acceptanceRate}%).`);
    }
  }

  // Department performance (sum series per department index)
  if (props.stageTimeByDept?.series?.length && props.stageTimeByDept.categories?.length) {
    const cats = props.stageTimeByDept.categories;
    const series = props.stageTimeByDept.series;
    const totals = cats.map((_, idx) =>
      series.reduce((sum, s) => sum + Number(s.data?.[idx] ?? 0), 0)
    );
    const maxVal = Math.max(...totals);
    const slowestIdx = totals.indexOf(maxVal);
    if (slowestIdx >= 0 && cats[slowestIdx]) {
      items.push(`Slowest average stage progression: ${cats[slowestIdx]} (${maxVal} days total across stages).`);
    }
  }

  if (!items.length) {
    items.push('No insights yet. Adjust filters to widen the date range or include more jobs/departments.');
  }

  return items;
});

</script>

<template>
  <AppLayout title="Recruitment Efficiency">
    <div class="max-w-7xl mx-auto py-8 px-4 space-y-12">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">Recruitment Efficiency</h2>

      <!-- Filters -->
      <div class="bg-white rounded-xl shadow p-5 flex flex-wrap gap-5 items-end">
        <div>
          <label class="block text-[11px] font-semibold mb-1 text-gray-600">Date Range</label>
          <select v-model="local.date_preset" class="border rounded px-2 py-1 text-sm w-44">
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
          <input type="date" v-model="local.date_from" class="border rounded px-2 py-1 text-sm" />
        </div>
        <div v-if="local.date_preset==='custom'">
          <label class="block text-[11px] font-semibold mb-1 text-gray-600">To</label>
          <input type="date" v-model="local.date_to" class="border rounded px-2 py-1 text-sm" />
        </div>

        <div>
          <label class="block text-[11px] font-semibold mb-1 text-gray-600">Department</label>
          <select v-model="local.department" class="border rounded px-2 py-1 text-sm w-48">
            <option value="">All</option>
            <option v-for="d in departments" :key="d" :value="d">{{ d }}</option>
          </select>
        </div>

        <div>
          <label class="block text-[11px] font-semibold mb-1 text-gray-600">Job Posting / Position</label>
          <select v-model="local.job_id" class="border rounded px-2 py-1 text-sm w-56">
            <option value="">All</option>
            <option v-for="j in jobs" :key="j.id" :value="j.id">{{ j.job_title }}</option>
          </select>
        </div>

        <div>
          <label class="block text-[11px] font-semibold mb-1 text-gray-600">Program (Optional)</label>
          <select v-model="local.program_id" class="border rounded px-2 py-1 text-sm w-48">
            <option value="">All</option>
            <option v-for="p in programs" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
        </div>

        <div>
          <label class="block text-[11px] font-semibold mb-1 text-gray-600">Recruitment Stage</label>
          <select v-model="local.stage" class="border rounded px-2 py-1 text-sm w-44">
            <option value="">All</option>
            <option v-for="s in stages" :key="s.value" :value="s.value">{{ s.label }}</option>
          </select>
        </div>

        <div class="ml-auto">
          <button @click="resetFilters" class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold px-3 py-2 rounded">
            Reset
          </button>
        </div>
      </div>

      <!-- Time-to-Hire -->
      <div class="bg-white rounded-xl shadow p-8">
        <h3 class="text-lg font-semibold mb-4 text-gray-700">Average Time-to-Hire (Monthly)</h3>
        <div v-if="!tthLine.labels?.length" class="text-sm text-gray-500">No TTH data.</div>
        <VueECharts v-else :option="tthLineOption" style="height: 380px; width: 100%;" />
      </div>

      <!-- Stage Conversion Funnel -->
      <div class="bg-white rounded-xl shadow p-8">
        <h3 class="text-lg font-semibold mb-4 text-gray-700">Stage Conversion Funnel</h3>
        <div v-if="!stageFunnel?.length" class="text-sm text-gray-500">No stage conversion data.</div>
        <VueECharts v-else :option="funnelOption" style="height: 400px; width: 100%;" />
      </div>

      <!-- Offer Acceptance -->
      <div class="bg-white rounded-xl shadow p-8">
        <h3 class="text-lg font-semibold mb-4 text-gray-700">Offer Acceptance Rate</h3>
        <div v-if="!offerPie?.length" class="text-sm text-gray-500">No offer data.</div>
        <VueECharts v-else :option="pieOption" style="height: 360px; width: 100%;" />
      </div>

      <!-- Time in Each Stage by Department -->
      <div class="bg-white rounded-xl shadow p-8">
        <h3 class="text-lg font-semibold mb-4 text-gray-700">Average Time in Each Stage by Department</h3>
        <div v-if="!(stageTimeByDept.categories?.length)" class="text-sm text-gray-500">No stage time data.</div>
        <VueECharts v-else :option="stageTimeOption" style="height: 500px; width: 100%;" />
      </div>
      
      <!-- Summary Insights -->
      <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-900">
        <h3 class="font-semibold mb-2">📊 Summary Insight:</h3>
        <ul class="list-disc pl-5 text-sm text-gray-700 space-y-1">
          <li v-for="(line, i) in summaryItems" :key="i">{{ line }}</li>
        </ul>
      </div>
    </div>
  </AppLayout>
</template>