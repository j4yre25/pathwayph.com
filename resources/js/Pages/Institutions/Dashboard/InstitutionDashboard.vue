<script setup>
import { ref, computed, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { Users, Briefcase, LineChart as LineIcon, Ban } from "lucide-vue-next";
import VueECharts from "vue-echarts";
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Filler
} from "chart.js";
import Applayout from "@/Layouts/AppLayout.vue";

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  ArcElement,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Filler
);

const props = defineProps({
  summary: {
    type: Object,
    required: true,
    default: () => ({
      total_programs: 0,
      total_graduates: 0,
      employed: 0,
      underemployed: 0,
      unemployed: 0
    })
  },
  topProgramsEmployment: {
    type: Array,
    default: () => []
  },
  schoolYears: Array,
  selectedSchoolYear: [String, Number, null],
  selectedTerm: [String, null],
  selectedGender: [String, null],
});

// Unique school year ranges (by range)
const uniqueSchoolYears = computed(() => {
  const seen = new Set();
  return (props.schoolYears || []).filter(sy => {
    if (!sy.range || seen.has(sy.range)) return false;
    seen.add(sy.range);
    return true;
  });
});

// Unique terms
const uniqueTerms = computed(() => {
  const seen = new Set();
  return (props.schoolYears || [])
    .map(sy => sy.term)
    .filter(term => term && !seen.has(term) && seen.add(term));
});

const employed = computed(() => props.summary.employed || 0);
const underemployed = computed(() => props.summary.underemployed || 0);
const unemployed = computed(() => props.summary.unemployed || 0);
const totalGraduates = computed(() =>
  employed.value + underemployed.value + unemployed.value
);

const employmentRate = computed(() =>
  totalGraduates.value
    ? (
        ((employed.value + underemployed.value) / totalGraduates.value) *
        100
      ).toFixed(1)
    : 0
);

const employmentPieOption = computed(() => ({
  tooltip: { trigger: "item", formatter: "{b}: {c} ({d}%)" },
  legend: { orient: "vertical", left: "left" },
  series: [
    {
      name: "Employment Status",
      type: "pie",
      radius: ["40%", "55%"],
      avoidLabelOverlap: false,
      label: {
        show: true,
        formatter: "{b}: {d}%",
        fontWeight: "bold"
      },
      data: [
        {
          value: employed.value,
          name: "Employed",
          itemStyle: { color: "#22c55e" }
        },
        {
          value: underemployed.value,
          name: "Underemployed",
          itemStyle: { color: "#facc15" }
        },
        {
          value: unemployed.value,
          name: "Unemployed",
          itemStyle: { color: "#ef4444" }
        }
      ]
    }
  ]
}));

// Filter state
const schoolYearFilter = ref(props.selectedSchoolYear || "");
const termFilter = ref(props.selectedTerm || "");
const genderFilter = ref(props.selectedGender || "");

// Watch for filter changes and refetch data
watch([schoolYearFilter, termFilter, genderFilter], ([sy, term, gender]) => {
  router.get(
    route("dashboard"),
    {
      school_year_id: sy || undefined,
      term: term || undefined,
      gender: gender || undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  );
});
</script>
<template>
  <Applayout>
    <div class="min-h-screen bg-gray-50 p-6">
      <!-- Filters -->
      <div class="flex justify-end gap-4 mb-8">
        <!-- School Year -->
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">School Year</label>
          <select
            v-model="schoolYearFilter"
            class="w-40 rounded-lg border-gray-300 shadow-sm focus:ring-teal-500 focus:border-teal-500 text-sm"
          >
            <option value="">All</option>
            <option v-for="sy in uniqueSchoolYears" :key="sy.id" :value="sy.id">
              {{ sy.range }}
            </option>
          </select>
        </div>
        <!-- Term -->
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Term</label>
          <select
            v-model="termFilter"
            class="w-32 rounded-lg border-gray-300 shadow-sm focus:ring-teal-500 focus:border-teal-500 text-sm"
          >
            <option value="">All</option>
            <option v-for="term in uniqueTerms" :key="term" :value="term">
              {{ term }}
            </option>
          </select>
        </div>
        <!-- Gender -->
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Gender</label>
          <select
            v-model="genderFilter"
            class="w-32 rounded-lg border-gray-300 shadow-sm focus:ring-teal-500 focus:border-teal-500 text-sm"
          >
            <option value="">All</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </div>
      </div>

      <!-- Overview Cards -->
      <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-10">
        <div class="bg-white rounded-xl p-5 shadow hover:shadow-md transition flex items-center">
          <Briefcase class="h-10 w-10 text-teal-600" />
          <div class="ml-4">
            <p class="text-xs text-gray-500">Total Programs</p>
            <p class="text-2xl font-bold text-gray-800">{{ props.summary.total_programs }}</p>
          </div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow hover:shadow-md transition flex items-center">
          <Users class="h-10 w-10 text-blue-600" />
          <div class="ml-4">
            <p class="text-xs text-gray-500">Total Graduates</p>
            <p class="text-2xl font-bold text-gray-800">{{ props.summary.total_graduates }}</p>
          </div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow hover:shadow-md transition flex items-center">
          <Briefcase class="h-10 w-10 text-green-600" />
          <div class="ml-4">
            <p class="text-xs text-gray-500">Employed</p>
            <p class="text-2xl font-bold text-gray-800">{{ props.summary.employed }}</p>
          </div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow hover:shadow-md transition flex items-center">
          <Briefcase class="h-10 w-10 text-yellow-500" />
          <div class="ml-4">
            <p class="text-xs text-gray-500">Underemployed</p>
            <p class="text-2xl font-bold text-gray-800">{{ props.summary.underemployed }}</p>
          </div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow hover:shadow-md transition flex items-center">
          <Ban class="h-10 w-10 text-red-500" />
          <div class="ml-4">
            <p class="text-xs text-gray-500">Unemployed</p>
            <p class="text-2xl font-bold text-gray-800">{{ props.summary.unemployed }}</p>
          </div>
        </div>
      </section>

      <!-- Middle Section -->
      <section class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- Employment Pie -->
        <div class="bg-white p-6 rounded-xl shadow hover:shadow-md transition flex flex-col items-center">
          <h4 class="text-base font-semibold text-gray-700 mb-4">Employment Distribution</h4>
          <VueECharts
            :option="employmentPieOption"
            style="height: 220px; width: 100%; max-width: 280px"
          />
          <div class="mt-6 text-center">
            <p class="text-3xl font-bold text-green-600">{{ employmentRate }}%</p>
            <p class="text-sm text-gray-500">Employment Rate</p>
          </div>
        </div>

        <!-- Top Programs -->
        <div class="bg-white p-6 rounded-xl shadow hover:shadow-md transition lg:col-span-2">
          <h4 class="text-base font-semibold text-gray-700 mb-4">Top Programs with High Employment</h4>
          <ul class="space-y-6">
            <li
              v-for="prog in props.topProgramsEmployment"
              :key="prog.program_name"
              class="flex flex-col"
            >
              <div class="flex justify-between items-center mb-2 flex-wrap gap-2">
                <span class="font-medium text-gray-800">{{ prog.program_name }}</span>
                <span class="text-xs text-gray-400 italic">{{ prog.degree }}</span>
                <span class="text-sm font-semibold text-green-600">{{ prog.percent }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="bg-gradient-to-r from-green-500 to-teal-400 h-2 rounded-full transition-all duration-300"
                  :style="{ width: prog.percent + '%' }"
                ></div>
              </div>
              <div class="flex justify-between text-xs text-gray-400 mt-1">
                <span>Employed: {{ prog.employed }}</span>
                <span>Total: {{ prog.total }}</span>
              </div>
            </li>
            <li v-if="props.topProgramsEmployment.length === 0" class="text-center text-gray-400 py-6">
              No program data available.
            </li>
          </ul>
        </div>
      </section>
    </div>
  </Applayout>
</template>
