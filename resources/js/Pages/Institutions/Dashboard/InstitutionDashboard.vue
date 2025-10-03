
<script setup>
import { ref, computed, watch } from "vue";
import { router } from "@inertiajs/vue3";
import { Users, Briefcase, LineChart as LineIcon, Ban, GraduationCap } from "lucide-vue-next";
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
    tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
    legend: {
        orient: 'vertical',
        left: 'left',
        top: 'center',
        itemGap: 12,
        textStyle: {
            color: '#4B5563', // gray-600
        },
    },
    series: [
        {
            name: 'Employment Status',
            type: 'pie',
            radius: ['50%', '70%'],
            center: ['68%', '50%'],
            avoidLabelOverlap: false,
            label: {
                show: false,
                position: 'center',
            },
            emphasis: {
                label: {
                    show: true,
                    fontSize: '18',
                    fontWeight: 'bold',
                    formatter: '{d}%',
                }
            },
            labelLine: {
                show: false,
            },
            data: [
                {
                    value: employed.value,
                    name: 'Employed',
                    itemStyle: { color: '#22c55e' } // green-500
                },
                {
                    value: underemployed.value,
                    name: 'Underemployed',
                    itemStyle: { color: '#facc15' } // yellow-400
                },
                {
                    value: unemployed.value,
                    name: 'Unemployed',
                    itemStyle: { color: '#ef4444' } // red-500
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
  <Applayout title="Dashboard">
    <div class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8">
      <div class="max-w-7xl mx-auto space-y-8">
        <header class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Alumni Employment Dashboard</h1>
                <p class="text-sm text-slate-500">Overview of graduate employment statistics.</p>
            </div>
          <div class="flex items-center gap-3 bg-white p-2 rounded-xl border border-slate-200 shadow-sm">
            <div>
              <label for="schoolYear" class="sr-only">School Year</label>
              <select
                id="schoolYear"
                v-model="schoolYearFilter"
                class="block w-full rounded-lg border-0 py-1.5 pl-3 pr-8 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-teal-600 sm:text-sm sm:leading-6 transition"
              >
                <option value="">All Years</option>
                <option v-for="sy in uniqueSchoolYears" :key="sy.id" :value="sy.id">
                  {{ sy.range }}
                </option>
              </select>
            </div>
            <div>
              <label for="term" class="sr-only">Term</label>
              <select
                id="term"
                v-model="termFilter"
                class="block w-full rounded-lg border-0 py-1.5 pl-3 pr-8 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-teal-600 sm:text-sm sm:leading-6 transition"
              >
                <option value="">All Terms</option>
                <option v-for="term in uniqueTerms" :key="term" :value="term">
                  {{ term }}
                </option>
              </select>
            </div>
            <div>
              <label for="gender" class="sr-only">Gender</label>
              <select
                id="gender"
                v-model="genderFilter"
                class="block w-full rounded-lg border-0 py-1.5 pl-3 pr-8 text-slate-900 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-inset focus:ring-teal-600 sm:text-sm sm:leading-6 transition"
              >
                <option value="">All Genders</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
          </div>
        </header>

        <section class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-6">
          <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-lg shadow-slate-200/50 hover:scale-[1.02] transition-transform duration-300 flex items-center gap-5">
              <div class="bg-teal-100/70 p-3 rounded-full"><GraduationCap class="h-6 w-6 text-teal-600" /></div>
              <div>
                  <p class="text-sm font-medium text-slate-500">Total Programs</p>
                  <p class="text-2xl font-bold text-slate-800">{{ props.summary.total_programs }}</p>
              </div>
          </div>
          <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-lg shadow-slate-200/50 hover:scale-[1.02] transition-transform duration-300 flex items-center gap-5">
              <div class="bg-blue-100/70 p-3 rounded-full"><Users class="h-6 w-6 text-blue-600" /></div>
              <div>
                  <p class="text-sm font-medium text-slate-500">Total Graduates</p>
                  <p class="text-2xl font-bold text-slate-800">{{ props.summary.total_graduates }}</p>
              </div>
          </div>
          <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-lg shadow-slate-200/50 hover:scale-[1.02] transition-transform duration-300 flex items-center gap-5">
              <div class="bg-green-100/70 p-3 rounded-full"><Briefcase class="h-6 w-6 text-green-600" /></div>
              <div>
                  <p class="text-sm font-medium text-slate-500">Employed</p>
                  <p class="text-2xl font-bold text-slate-800">{{ props.summary.employed }}</p>
              </div>
          </div>
          <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-lg shadow-slate-200/50 hover:scale-[1.02] transition-transform duration-300 flex items-center gap-5">
              <div class="bg-yellow-100/70 p-3 rounded-full"><Briefcase class="h-6 w-6 text-yellow-500" /></div>
              <div>
                  <p class="text-sm font-medium text-slate-500">Underemployed</p>
                  <p class="text-2xl font-bold text-slate-800">{{ props.summary.underemployed }}</p>
              </div>
          </div>
          <div class="bg-white rounded-xl p-5 border border-slate-200/80 shadow-lg shadow-slate-200/50 hover:scale-[1.02] transition-transform duration-300 flex items-center gap-5">
              <div class="bg-red-100/70 p-3 rounded-full"><Ban class="h-6 w-6 text-red-500" /></div>
              <div>
                  <p class="text-sm font-medium text-slate-500">Unemployed</p>
                  <p class="text-2xl font-bold text-slate-800">{{ props.summary.unemployed }}</p>
              </div>
          </div>
        </section>

        <section class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
          <div class="bg-white p-6 rounded-xl border border-slate-200/80 shadow-lg shadow-slate-200/50 lg:col-span-1">
              <h4 class="text-lg font-semibold text-slate-800 mb-1">Employment Distribution</h4>
              <p class="text-sm text-slate-500 mb-4">A breakdown of graduate employment status.</p>
              <div class="flex flex-col md:flex-row lg:flex-col items-center gap-6">
                <VueECharts :option="employmentPieOption" style="height: 200px; width: 100%; min-width: 250px;" />
                <div class="text-center md:text-left lg:text-center shrink-0">
                    <p class="text-4xl font-bold text-green-600">{{ employmentRate }}%</p>
                    <p class="text-base font-medium text-slate-600">Employment Rate</p>
                    <p class="text-xs text-slate-400 mt-1">(Employed + Underemployed)</p>
                </div>
              </div>
          </div>

          <div class="bg-white rounded-xl border border-slate-200/80 shadow-lg shadow-slate-200/50 lg:col-span-2">
            <div class="p-6">
                <h4 class="text-lg font-semibold text-slate-800">Top Programs by Employment Rate</h4>
                <p class="text-sm text-slate-500 mt-1">Ranking programs with the highest percentage of employed graduates.</p>
            </div>
            <div class="overflow-x-auto">
                <table v-if="props.topProgramsEmployment.length > 0" class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Program</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Employment Rate</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider text-right">Graduates (Emp/Total)</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        <tr v-for="(prog, index) in props.topProgramsEmployment" :key="index" class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-slate-900">{{ prog.program_name }}</div>
                                <div class="text-xs text-slate-500">{{ prog.degree }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-full bg-slate-200 rounded-full h-2.5">
                                        <div class="bg-gradient-to-r from-teal-400 to-green-500 h-2.5 rounded-full" :style="{ width: prog.percent + '%' }"></div>
                                    </div>
                                    <span class="text-sm font-semibold text-green-600 w-12 text-right">{{ prog.percent }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-medium text-slate-800">{{ prog.employed }} / {{ prog.total }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                 <div v-else class="text-center text-slate-400 py-16">
                    <p>No program data available for the selected filters.</p>
                </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </Applayout>
</template>
```