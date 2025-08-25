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
  return (props.schoolYears || []).filter(sy => {
    if (!sy.term || seen.has(sy.term)) return false;
    seen.add(sy.term);
    return true;
  });
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
    <div class="min-h-screen bg-[#eaf6f6] p-6">
      <!-- Filters aligned right -->
      <div class="flex mb-6">
        <div class="flex gap-4 ml-auto items-center">
          <!-- School Year Range Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
            <select v-model="schoolYearFilter" class="rounded border-gray-300">
              <option value="">All</option>
              <option v-for="sy in uniqueSchoolYears" :key="sy.id" :value="sy.id">
                {{ sy.range }}
              </option>
            </select>
          </div>
          <!-- Term Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Term</label>
            <select v-model="termFilter" class="rounded border-gray-300">
              <option value="">All</option>
              <option v-for="sy in uniqueTerms" :key="sy.term" :value="sy.term">
                {{ sy.term }}
              </option>
            </select>
          </div>
          <!-- Gender Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
            <select v-model="genderFilter" class="rounded border-gray-300">
              <option value="">All</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Top Overview Cards -->
      <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="flex items-center bg-[#d6f5f5] p-5 rounded-xl shadow-sm">
          <Briefcase class="h-8 w-8 text-teal-700" />
          <div class="ml-4">
            <p class="text-sm text-gray-600">Total Programs</p>
            <p class="text-2xl font-bold">{{ props.summary.total_programs }}</p>
          </div>
        </div>
        <div class="flex items-center bg-[#d6f5f5] p-5 rounded-xl shadow-sm">
          <Users class="h-8 w-8 text-blue-700" />
          <div class="ml-4">
            <p class="text-sm text-gray-600">Total Graduates</p>
            <p class="text-2xl font-bold">{{ props.summary.total_graduates }}</p>
          </div>
        </div>
        <div class="flex items-center bg-[#d6f5f5] p-5 rounded-xl shadow-sm">
          <Briefcase class="h-8 w-8 text-green-700" />
          <div class="ml-4">
            <p class="text-sm text-gray-600">Employed Graduates</p>
            <p class="text-2xl font-bold">{{ props.summary.employed }}</p>
          </div>
        </div>
        <div class="flex items-center bg-[#ffe6e6] p-5 rounded-xl shadow-sm">
          <Ban class="h-8 w-8 text-red-600" />
          <div class="ml-4">
            <p class="text-sm text-gray-600">Unemployed Graduates</p>
            <p class="text-2xl font-bold">{{ props.summary.unemployed }}</p>
          </div>
        </div>
      </section>
      <!-- Middle Section -->
      <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <!-- Employment Percentage -->
        <div class="bg-white p-6 rounded-xl shadow-sm flex flex-col items-center justify-center w-full max-w-md mx-auto">
          <h4 class="text-lg font-semibold text-gray-600 mb-6">
            Employment Percentage
          </h4>
          <VueECharts
            :option="employmentPieOption"
            style="height: 220px; width: 100%; max-width: 280px"
          />
          <div class="mt-6 text-center">
            <div class="text-3xl font-bold text-green-700">{{ employmentRate }}%</div>
            <div class="text-gray-500">Employment Rate</div>
          </div>
        </div>

        <!-- Top Programs -->
        <div class="bg-white p-6 rounded-xl shadow-sm lg:col-span-2">
          <p class="text-base font-semibold text-gray-700 mb-4">
            Top Programs with High Employment
          </p>
          <ul class="space-y-5">
            <li
              v-for="prog in props.topProgramsEmployment"
              :key="prog.program_name"
              class="flex flex-col"
            >
              <div class="flex justify-between items-center mb-1 flex-wrap gap-2">
                <span class="font-semibold text-gray-800">{{ prog.program_name }}</span>
                <span class="text-xs text-gray-500">{{ prog.degree }}</span>
                <span class="text-sm text-green-700 font-bold">{{ prog.percent }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div
                  class="bg-green-400 h-3 rounded-full transition-all duration-300"
                  :style="{ width: prog.percent + '%' }"
                ></div>
              </div>
              <div class="flex justify-between text-xs text-gray-500 mt-1">
                <span>Employed: {{ prog.employed }}</span>
                <span>Total Graduates: {{ prog.total }}</span>
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
