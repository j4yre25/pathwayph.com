<script setup>
import { ref, computed } from "vue";
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
  }
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
</script>

<template>
  <div class="min-h-screen bg-[#eaf6f6] p-6">
    <!-- Top Overview Cards -->
    <section
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"
    >
      <div class="flex items-center bg-[#d6f5f5] p-5 rounded-xl shadow">
        <Briefcase class="h-8 w-8 text-teal-700" />
        <div class="ml-4">
          <p class="text-sm text-gray-600">Total Programs</p>
          <p class="text-2xl font-bold">
            {{ props.summary.total_programs }}
          </p>
        </div>
      </div>
      <div class="flex items-center bg-[#d6f5f5] p-5 rounded-xl shadow">
        <Users class="h-8 w-8 text-blue-700" />
        <div class="ml-4">
          <p class="text-sm text-gray-600">Total Graduates</p>
          <p class="text-2xl font-bold">
            {{ props.summary.total_graduates }}
          </p>
        </div>
      </div>
      <div class="flex items-center bg-[#d6f5f5] p-5 rounded-xl shadow">
        <Briefcase class="h-8 w-8 text-green-700" />
        <div class="ml-4">
          <p class="text-sm text-gray-600">Employed Graduates</p>
          <p class="text-2xl font-bold">{{ props.summary.employed }}</p>
        </div>
      </div>
      <div class="flex items-center bg-[#ffe6e6] p-5 rounded-xl shadow">
        <Ban class="h-8 w-8 text-red-600" />
        <div class="ml-4">
          <p class="text-sm text-gray-600">Unemployed Graduates</p>
          <p class="text-2xl font-bold">{{ props.summary.unemployed }}</p>
        </div>
      </div>
    </section>

    <!-- Middle Section -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <!-- Employment Percentage -->
      <div
        class="bg-white p-5 rounded-xl shadow flex flex-col items-center justify-center"
        style="min-height: 380px"
      >
        <h4 class="text-lg font-semibold text-gray-600 mb-4">
          Employment Percentage
        </h4>
        <VueECharts
          :option="employmentPieOption"
          style="height: 260px; width: 100%; max-width: 350px"
        />
        <div class="mt-4 text-center">
          <div class="text-2xl font-bold text-green-700">
            {{ employmentRate }}%
          </div>
          <div class="text-gray-500">Employment Rate</div>
        </div>
      </div>

      <!-- Top Programs -->
      <div
        class="bg-white p-5 rounded-xl shadow lg:col-span-2"
        style="min-height: 380px"
      >
        <p class="text-sm font-semibold text-gray-700 mb-4">
          Top Programs with High Employment
        </p>
        <ul class="space-y-4">
          <li
            v-for="prog in props.topProgramsEmployment"
            :key="prog.program_name"
            class="flex flex-col"
          >
            <div
              class="flex justify-between items-center mb-1 flex-wrap gap-2"
            >
              <span class="font-semibold text-gray-800">{{
                prog.program_name
              }}</span>
              <span class="text-xs text-gray-500">{{ prog.degree }}</span>
              <span class="text-sm text-green-700 font-bold">{{
                prog.percent
              }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
              <div
                class="bg-green-400 h-3 rounded-full transition-all duration-300"
                :style="{ width: prog.percent + '%' }"
              ></div>
            </div>
            <div
              class="flex justify-between text-xs text-gray-500 mt-1"
            >
              <span>Employed: {{ prog.employed }}</span>
              <span>Total Graduates: {{ prog.total }}</span>
            </div>
          </li>
          <li
            v-if="props.topProgramsEmployment.length === 0"
            class="text-center text-gray-400 py-6"
          >
            No program data available.
          </li>
        </ul>
      </div>
    </section>
  </div>
</template>
