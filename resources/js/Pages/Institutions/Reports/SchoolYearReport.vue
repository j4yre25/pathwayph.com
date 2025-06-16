<template>
  <AppLayout>
    <div class="max-w-7xl mx-auto py-10 px-4">
      <h1 class="text-3xl font-bold mb-6 text-gray-800 flex items-center gap-2">
        <Calendar class="w-8 h-8 text-cyan-500" />
        School Year Report
      </h1>

      <!-- Card for total school year entries -->
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
          <span class="text-2xl font-bold text-gray-800">{{ schoolYears.length }}</span>
          <span class="text-gray-500">Total School Year Entries</span>
        </div>
      </div>

      <!-- Table 1: List of School Years -->
      <div class="mb-10">
        <h2 class="text-xl font-semibold mb-4">All School Years</h2>
        <div class="bg-white rounded-lg shadow overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">School Year</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="sy in schoolYears" :key="sy.id">
                <td class="px-4 py-2">{{ sy.school_year_range }}</td>
                <td class="px-4 py-2">{{ sy.term }}</td>
                <td class="px-4 py-2">{{ sy.status }}</td>
              </tr>
              <tr v-if="schoolYears.length === 0">
                <td colspan="3" class="text-center text-gray-400 py-6">No school years found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Filters for graduates table -->
      <div class="bg-white rounded-xl shadow p-6 mb-8 grid grid-cols-1 md:grid-cols-3 gap-4">
        <input
          v-model="filters.name"
          type="text"
          placeholder="Search by name"
          class="rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500"
        />
        <select v-model="filters.schoolYear" class="rounded-lg border-gray-300">
          <option value="">All School Years</option>
          <option v-for="sy in schoolYears" :key="sy.school_year_id" :value="sy.school_year_id">{{ sy.school_year_range }}</option>
        </select>
        <select v-model="filters.degree" class="rounded-lg border-gray-300">
          <option value="">All Degrees</option>
          <option v-for="deg in degrees" :key="deg.id" :value="deg.id">{{ deg.type }}</option>
        </select>
        <select v-model="filters.program" class="rounded-lg border-gray-300">
          <option value="">All Programs</option>
          <option v-for="prog in programs" :key="prog.id" :value="prog.id">{{ prog.name }}</option>
        </select>
        <select v-model="filters.employmentStatus" class="rounded-lg border-gray-300">
          <option value="">All Employment Status</option>
          <option value="Employed">Employed</option>
          <option value="Underemployed">Underemployed</option>
          <option value="Unemployed">Unemployed</option>
        </select>
      </div>

      <!-- Table 2: List of Graduates -->
      <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year Graduated</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Term</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Degree</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Program</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employment Status</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Job Title</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="g in paginatedGraduates" :key="g.id">
              <td class="px-4 py-2">{{ g.first_name }} {{ g.middle_name }} {{ g.last_name }}</td>
              <td class="px-4 py-2">{{ g.school_year_range || 'N/A' }}</td>
              <td class="px-4 py-2">{{ g.term || 'N/A' }}</td>
              <td class="px-4 py-2">{{ g.degree || 'N/A' }}</td>
              <td class="px-4 py-2">{{ g.program || 'N/A' }}</td>
              <td class="px-4 py-2">{{ g.employment_status }}</td>
              <td class="px-4 py-2">{{ g.current_job_title }}</td>
            </tr>
            <tr v-if="paginatedGraduates.length === 0">
              <td colspan="7" class="text-center text-gray-400 py-6">No graduates found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="mt-6 flex justify-center gap-2">
        <button
          class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300"
          :disabled="page === 1"
          @click="page--"
        >Prev</button>
        <span class="px-3 py-1">{{ page }} / {{ totalPages }}</span>
        <button
          class="px-3 py-1 rounded bg-gray-200 hover:bg-gray-300"
          :disabled="page === totalPages"
          @click="page++"
        >Next</button>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Calendar } from 'lucide-vue-next';

const props = defineProps({
  schoolYears: Array,
  graduates: Array,
  degrees: Array,
  programs: Array,
});

// Filters for graduates table
const filters = ref({
  name: '',
  schoolYear: '',
  degree: '',
  program: '',
  employmentStatus: '',
});

// Real-time filtered graduates
const filteredGraduates = computed(() => {
  return props.graduates.filter(g => {
    const name = `${g.first_name} ${g.middle_name ?? ''} ${g.last_name}`.toLowerCase();
    if (filters.value.name && !name.includes(filters.value.name.toLowerCase())) return false;
    if (filters.value.schoolYear && g.school_year_id != filters.value.schoolYear) return false;
    if (filters.value.degree && g.degree_id != filters.value.degree) return false;
    if (filters.value.program && g.program_id != filters.value.program) return false;
    if (filters.value.employmentStatus && g.employment_status !== filters.value.employmentStatus) return false;
    return true;
  });
});

// Pagination
const page = ref(1);
const perPage = 10;
const totalPages = computed(() => Math.ceil(filteredGraduates.value.length / perPage));
const paginatedGraduates = computed(() => {
  const start = (page.value - 1) * perPage;
  return filteredGraduates.value.slice(start, start + perPage);
});
watch(filteredGraduates, () => { page.value = 1; });
</script>