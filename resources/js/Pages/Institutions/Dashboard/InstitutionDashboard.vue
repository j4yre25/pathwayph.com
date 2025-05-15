<script setup>
import { defineProps, computed, ref } from "vue";
import { Users, Briefcase, LineChart, Ban } from "lucide-vue-next";

const props = defineProps({
  summary: Object,
  graduates: Array,
  programs: Array,
  careerOpportunities: Array,
});

const selectedProgram = ref("");
const selectedCareer = ref("");
const selectedGender = ref("");

// Filtered graduates based on selected filters
const filteredGraduates = computed(() => {
  const graduates = props.graduates ?? [];
  return graduates.filter(g => {
    const matchProgram = selectedProgram.value ? g.program_id === Number(selectedProgram.value) : true;
    const matchCareer = selectedCareer.value ? g.current_job_title === selectedCareer.value : true;
    const matchGender = selectedGender.value ? g.gender === selectedGender.value : true;
    return matchProgram && matchCareer && matchGender;
  });
});

const totalGraduates = computed(() => filteredGraduates.value.length);
const employed = computed(() => filteredGraduates.value.filter(g => g.employment_status === "Employed").length);
const underemployed = computed(() => filteredGraduates.value.filter(g => g.employment_status === "Underemployed").length);
const unemployed = computed(() => filteredGraduates.value.filter(g => g.employment_status === "Unemployed").length);

const employmentRate = computed(() => {
  if (totalGraduates.value === 0) return 0;
  return (((employed.value + underemployed.value) / totalGraduates.value) * 100).toFixed(1);
});
</script>

<template>
  <section class="bg-gray-100 rounded-3xl p-6 sm:p-10 shadow-inner">
    <div class="max-w-7xl mx-auto">
      <!-- Filters -->
      <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <h2 class="text-xl font-bold text-gray-700">Graduate Employment Summary</h2>
        <div class="flex flex-wrap justify-end gap-4">
          <!-- Program Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Program</label>
            <select v-model="selectedProgram" class="rounded-md border-gray-300 focus:border-green-500 focus:ring-green-500">
              <option value="">All</option>
              <option v-for="program in props.programs" :key="program.id" :value="program.id">{{ program.name }}</option>
            </select>
          </div>
          <!-- Career Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Career</label>
            <select v-model="selectedCareer" class="rounded-md border-gray-300 focus:border-green-500 focus:ring-green-500">
              <option value="">All</option>
              <option v-for="career in props.careerOpportunities" :key="career" :value="career">{{ career }}</option>
            </select>
          </div>
          <!-- Gender Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
            <select v-model="selectedGender" class="rounded-md border-gray-300 focus:border-green-500 focus:ring-green-500">
              <option value="">All</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Dashboard -->
      <div class="flex flex-col lg:flex-row gap-8 justify-center items-stretch">
        <!-- Employment Rate Card -->
        <div class="bg-white p-8 rounded-2xl shadow-lg flex flex-col items-center justify-center w-full max-w-sm mx-auto">
          <div class="relative mb-4" style="width: 140px; height: 140px;">
            <svg width="140" height="140">
              <circle cx="70" cy="70" r="60" fill="transparent" stroke="#e5e7eb" stroke-width="12" />
              <circle
                cx="70" cy="70" r="60" fill="transparent" stroke="#22c55e"
                stroke-width="12" stroke-linecap="round"
                :stroke-dasharray="2 * Math.PI * 60"
                :stroke-dashoffset="2 * Math.PI * 60 * (1 - employmentRate / 100)"
                style="transition: stroke-dashoffset 0.6s;"
              />
            </svg>
            <span
              class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-3xl font-bold text-green-600"
            >
              {{ employmentRate }}%
            </span>
          </div>
          <div class="text-center">
            <h3 class="text-lg font-semibold text-gray-700">Employment Rate</h3>
            <p class="text-sm text-gray-500">
              ({{ employed + underemployed }} of {{ totalGraduates }} graduates)
            </p>
          </div>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4 flex-1">
          <!-- Total Graduates -->
          <div class="bg-white p-6 rounded-2xl shadow-lg text-center flex flex-col items-center">
            <Users class="w-8 h-8 text-indigo-500 mb-2" />
            <h4 class="text-gray-600 text-lg font-medium mb-1">Total Graduates</h4>
            <p class="text-3xl font-bold text-indigo-600">{{ totalGraduates }}</p>
          </div>
          <!-- Employed -->
          <div class="bg-white p-6 rounded-2xl shadow-lg text-center flex flex-col items-center">
            <Briefcase class="w-8 h-8 text-green-500 mb-2" />
            <h4 class="text-gray-600 text-lg font-medium mb-1">Employed</h4>
            <p class="text-3xl font-bold text-green-600">{{ employed }}</p>
          </div>
          <!-- Underemployed -->
          <div class="bg-white p-6 rounded-2xl shadow-lg text-center flex flex-col items-center">
            <LineChart class="w-8 h-8 text-yellow-500 mb-2" />
            <h4 class="text-gray-600 text-lg font-medium mb-1">Underemployed</h4>
            <p class="text-3xl font-bold text-yellow-600">{{ underemployed }}</p>
          </div>
          <!-- Unemployed -->
          <div class="bg-white p-6 rounded-2xl shadow-lg text-center flex flex-col items-center">
            <Ban class="w-8 h-8 text-red-500 mb-2" />
            <h4 class="text-gray-600 text-lg font-medium mb-1">Unemployed</h4>
            <p class="text-3xl font-bold text-red-600">{{ unemployed }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
