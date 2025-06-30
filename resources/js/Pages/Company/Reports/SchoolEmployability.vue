<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import VueECharts from "vue-echarts";

const { props } = usePage();
const institutions = props.institutions;
const programs = props.programs;
const chartLabels = props.chartLabels;
const chartData = props.chartData;
const graduateList = props.graduateList;
const filters = ref({ ...props.filters });

// KPI cards
const totalHired = computed(() => graduateList.length);
const topSchool = computed(() =>
  chartLabels.length ? chartLabels[0] : "N/A"
);

// Pagination (client-side, optional)
const page = ref(1);
const pageSize = 10;
const paginatedGraduates = computed(() => {
  const start = (page.value - 1) * pageSize;
  return graduateList.slice(start, start + pageSize);
});
const totalPages = computed(() =>
  Math.ceil(graduateList.length / pageSize)
);

const paginationPages = computed(() => {
  const pages = [];
  if (totalPages.value <= 7) {
    for (let i = 1; i <= totalPages.value; i++) pages.push(i);
  } else {
    if (page.value <= 4) {
      pages.push(1, 2, 3, 4, 5, '...', totalPages.value);
    } else if (page.value >= totalPages.value - 3) {
      pages.push(1, '...', totalPages.value - 4, totalPages.value - 3, totalPages.value - 2, totalPages.value - 1, totalPages.value);
    } else {
      pages.push(1, '...', page.value - 1, page.value, page.value + 1, '...', totalPages.value);
    }
  }
  return pages;
});

function goToPage(p) {
  page.value = p;
}

function applyFilters() {
  router.get(route("company.reports.schoolEmployability"), filters.value);
}
</script>

<template>
  <AppLayout title="School-wise Graduate Employability">
    <div class="max-w-7xl mx-auto py-8 px-4">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">
        School-wise Graduate Employability
      </h2>

      <!-- FILTERS -->
      <div class="flex flex-wrap gap-4 mb-6">
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Institution</label>
          <select v-model="filters.institution_id" class="rounded border-gray-300">
            <option value="">All Institutions</option>
            <option v-for="inst in institutions" :key="inst.id" :value="inst.id">
              {{ inst.institution_name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Program</label>
          <select v-model="filters.program_id" class="rounded border-gray-300">
            <option value="">All Programs</option>
            <option v-for="prog in programs" :key="prog.id" :value="prog.id">
              {{ prog.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-700 mb-1">Timeline</label>
          <input v-model="filters.timeline" type="month" class="rounded border-gray-300" />
        </div>
        <button @click="applyFilters" class="bg-blue-600 text-white px-4 py-2 rounded mt-6">
          Apply
        </button>
      </div>

      <!-- KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-10">
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
          <span class="text-gray-500 text-sm">Total Hired Graduates</span>
          <span class="text-3xl font-bold text-blue-600">{{ totalHired }}</span>
        </div>
        <div class="bg-white rounded-lg shadow p-6 flex flex-col items-center">
          <span class="text-gray-500 text-sm">Top School</span>
          <span class="text-3xl font-bold text-green-600">{{ topSchool }}</span>
        </div>
      </div>

      <!-- Table -->
      <section class="bg-white rounded-2xl shadow p-6 space-y-6 mt-10 mb-10">
        <h3 class="text-2xl font-bold text-gray-700">List of Hired Graduates</h3>
        <div v-if="paginatedGraduates.length" class="mt-2">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead>
              <tr>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Name</th>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Institution</th>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Job Title</th>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Program</th>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Company</th>
                <th class="px-2 py-1 text-left font-medium text-gray-700">Hired At</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="grad in paginatedGraduates" :key="grad.name + grad.hired_at">
                <td class="px-2 py-1">{{ grad.name }}</td>
                <td class="px-2 py-1">{{ grad.institution }}</td>
                <td class="px-2 py-1">{{ grad.current_job_title }}</td>
                <td class="px-2 py-1">{{ grad.program }}</td>
                <td class="px-2 py-1">{{ grad.company_name }}</td>
                <td class="px-2 py-1">
                  {{ grad.hired_at ? new Date(grad.hired_at).toLocaleDateString() : '' }}
                </td>
              </tr>
            </tbody>
          </table>
          <!-- Pagination Controls -->
          <div class="flex justify-center mt-4" v-if="totalPages > 1">
            <button class="mx-1 px-3 py-1 rounded border bg-white text-blue-600" :disabled="page === 1"
              @click="goToPage(page - 1)">
              Prev
            </button>
            <template v-for="p in paginationPages">
              <button v-if="p !== '...'" :key="p" :class="[
                'mx-1 px-3 py-1 rounded border',
                { 'bg-blue-600 text-white': p === page, 'bg-white text-blue-600': p !== page }
              ]" @click="goToPage(p)">{{ p }}</button>
              <span v-else :key="'ellipsis-' + p" class="mx-1 px-3 py-1 text-gray-400">...</span>
            </template>
            <button class="mx-1 px-3 py-1 rounded border bg-white text-blue-600" :disabled="page === totalPages"
              @click="goToPage(page + 1)">
              Next
            </button>
          </div>
        </div>
        <div v-else class="text-gray-400">No graduates found for the selected filters.</div>
      </section>
      <!-- Bar Chart -->
      <div class="bg-white rounded-xl shadow p-8 mb-10">
        <h3 class="text-lg font-semibold mb-6 text-gray-700">Hires by School</h3>
        <div class="flex justify-center">
          <VueECharts v-if="chartLabels.length" :option="{
            xAxis: { type: 'category', data: chartLabels },
            yAxis: { type: 'value' },
            series: [{ data: chartData, type: 'bar', itemStyle: { color: '#4F46E5' } }]
          }" style="height: 350px; width: 100%; max-width: 100%;" />
          <div v-else class="text-gray-400 text-center py-8">No chart data available.</div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>