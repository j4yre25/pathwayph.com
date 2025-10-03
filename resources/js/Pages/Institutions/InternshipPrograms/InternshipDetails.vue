<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  show: Boolean,
  program: Object,
});

const emit = defineEmits(['close']);

// Pagination state for each list
const itemsPerPage = 5;

const programPage = ref(1);
const careerPage = ref(1);
const skillPage = ref(1);
const graduatePage = ref(1);

// Computed paginated lists
const paginatedPrograms = computed(() => {
  if (!props.program || !props.program.programs) return [];
  const start = (programPage.value - 1) * itemsPerPage;
  return props.program.programs.slice(start, start + itemsPerPage);
});
const totalProgramPages = computed(() =>
  props.program && props.program.programs ? Math.ceil(props.program.programs.length / itemsPerPage) : 1
);

const paginatedCareers = computed(() => {
  if (!props.program || !props.program.career_opportunities) return [];
  const start = (careerPage.value - 1) * itemsPerPage;
  return props.program.career_opportunities.slice(start, start + itemsPerPage);
});
const totalCareerPages = computed(() =>
  props.program && props.program.career_opportunities ? Math.ceil(props.program.career_opportunities.length / itemsPerPage) : 1
);

const paginatedSkills = computed(() => {
  if (!props.program || !props.program.skills) return [];
  const start = (skillPage.value - 1) * itemsPerPage;
  return props.program.skills.slice(start, start + itemsPerPage);
});
const totalSkillPages = computed(() =>
  props.program && props.program.skills ? Math.ceil(props.program.skills.length / itemsPerPage) : 1
);

const paginatedGraduates = computed(() => {
  if (!props.program || !props.program.graduates) return [];
  const start = (graduatePage.value - 1) * itemsPerPage;
  return props.program.graduates.slice(start, start + itemsPerPage);
});
const totalGraduatePages = computed(() =>
  props.program && props.program.graduates ? Math.ceil(props.program.graduates.length / itemsPerPage) : 1
);

// Reset page when modal opens/closes
watch(() => props.show, (val) => {
  if (val) {
    programPage.value = 1;
    careerPage.value = 1;
    skillPage.value = 1;
    graduatePage.value = 1;
  }
});
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-opacity duration-300">
    
    <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full relative transform transition-all duration-300 scale-100 opacity-100 overflow-hidden border border-slate-100">
      
      <button @click="$emit('close')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-800 transition-colors z-10 p-2 rounded-full hover:bg-slate-100">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>

      <header class="p-5 bg-indigo-600 text-white shadow-xl">
        <h2 class="text-2xl font-extrabold tracking-tight flex items-center">
          <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
          </svg>
          Program Details
        </h2>
        <p class="mt-1 text-sm text-indigo-200">Detailed information about the selected internship program.</p>
      </header>

      <div v-if="program" class="p-6 max-h-[60vh] overflow-y-auto space-y-6">
        
        <div class="pb-4 border-b border-slate-200">
          <span class="text-xs font-medium uppercase text-slate-500 tracking-wider">Internship Program Name</span>
          <p class="text-xl font-bold text-slate-800 mt-1">{{ program.title }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

          <div class="bg-indigo-50/50 p-8 rounded-xl border border-indigo-100 shadow-md">
            <h3 class="text-base font-bold text-indigo-800 mb-3 flex items-center">
                <span class="mr-2">📚</span> Relevant Programs
            </h3>
            <div v-if="paginatedPrograms.length">
              <div class="space-y-2">
                <p v-for="p in paginatedPrograms" :key="p.id" class="text-sm text-slate-700 bg-white px-3 py-2 rounded-lg border border-indigo-200/50 shadow-sm transition-shadow hover:shadow-md">
                  {{ p.name }}
                </p>
              </div>
              <div v-if="totalProgramPages > 1" class="flex justify-end items-center mt-3 text-xs">
                <button @click="programPage--" :disabled="programPage === 1" 
                        class="text-indigo-600 hover:text-indigo-800 disabled:text-slate-400 font-medium px-2 py-1 rounded transition">
                  &larr; Prev
                </button>
                <span class="text-slate-600 mx-2">{{ programPage }} / {{ totalProgramPages }}</span>
                <button @click="programPage++" :disabled="programPage === totalProgramPages" 
                        class="text-indigo-600 hover:text-indigo-800 disabled:text-slate-400 font-medium px-2 py-1 rounded transition">
                  Next &rarr;
                </button>
              </div>
            </div>
            <p v-else class="text-sm text-slate-500 italic p-2">No associated programs listed.</p>
          </div>

          <div class="bg-teal-50/50 p-4 rounded-xl border border-teal-100 shadow-md">
            <h3 class="text-base font-bold text-teal-800 mb-3 flex items-center">
                <span class="mr-2">💼</span> Career Opportunities
            </h3>
            <div v-if="paginatedCareers.length">
              <div class="space-y-2">
                <p v-for="c in paginatedCareers" :key="c.id" class="text-sm text-slate-700 bg-white px-3 py-2 rounded-lg border border-teal-200/50 shadow-sm transition-shadow hover:shadow-md">
                  {{ c.title }}
                </p>
              </div>
              <div v-if="totalCareerPages > 1" class="flex justify-end items-center mt-3 text-xs">
                <button @click="careerPage--" :disabled="careerPage === 1" 
                        class="text-teal-600 hover:text-teal-800 disabled:text-slate-400 font-medium px-2 py-1 rounded transition">
                  &larr; Prev
                </button>
                <span class="text-slate-600 mx-2">{{ careerPage }} / {{ totalCareerPages }}</span>
                <button @click="careerPage++" :disabled="careerPage === totalCareerPages" 
                        class="text-teal-600 hover:text-teal-800 disabled:text-slate-400 font-medium px-2 py-1 rounded transition">
                  Next &rarr;
                </button>
              </div>
            </div>
            <p v-else class="text-sm text-slate-500 italic p-2">No career opportunities listed.</p>
          </div>

          <div class="bg-purple-50/50 p-4 rounded-xl border border-purple-100 shadow-md">
            <h3 class="text-base font-bold text-purple-800 mb-3 flex items-center">
                <span class="mr-2">🧠</span>Skills
            </h3>
            <div v-if="paginatedSkills.length">
              <div class="space-y-2">
                <p v-for="s in paginatedSkills" :key="s.id" class="text-sm text-slate-700 bg-white px-3 py-2 rounded-lg border border-purple-200/50 shadow-sm transition-shadow hover:shadow-md">
                  {{ s.name }}
                </p>
              </div>
              <div v-if="totalSkillPages > 1" class="flex justify-end items-center mt-3 text-xs">
                <button @click="skillPage--" :disabled="skillPage === 1" 
                        class="text-purple-600 hover:text-purple-800 disabled:text-slate-400 font-medium px-2 py-1 rounded transition">
                  &larr; Prev
                </button>
                <span class="text-slate-600 mx-2">{{ skillPage }} / {{ totalSkillPages }}</span>
                <button @click="skillPage++" :disabled="skillPage === totalSkillPages" 
                        class="text-purple-600 hover:text-purple-800 disabled:text-slate-400 font-medium px-2 py-1 rounded transition">
                  Next &rarr;
                </button>
              </div>
            </div>
            <p v-else class="text-sm text-slate-500 italic p-2">No specific skills listed.</p>
          </div>

          <div class="bg-amber-50/50 p-4 rounded-xl border border-amber-100 shadow-md">
            <h3 class="text-base font-bold text-amber-800 mb-3 flex items-center">
                <span class="mr-2">🧑‍🎓</span> Assigned Graduates
            </h3>
            <div v-if="paginatedGraduates.length">
              <div class="space-y-2">
                <div v-for="g in paginatedGraduates" :key="g.id" class="text-sm text-slate-700 bg-white px-3 py-2 rounded-lg border border-amber-200/50 shadow-sm flex justify-between transition-shadow hover:shadow-md">
                  <span class="font-medium truncate">{{ g.first_name }} {{ g.last_name }}</span>
                  <span class="text-xs text-slate-500 truncate ml-4 hidden sm:inline">{{ g.email }}</span>
                </div>
              </div>
              <div v-if="totalGraduatePages > 1" class="flex justify-end items-center mt-3 text-xs">
                <button @click="graduatePage--" :disabled="graduatePage === 1" 
                        class="text-amber-600 hover:text-amber-800 disabled:text-slate-400 font-medium px-2 py-1 rounded transition">
                  &larr; Prev
                </button>
                <span class="text-slate-600 mx-2">{{ graduatePage }} / {{ totalGraduatePages }}</span>
                <button @click="graduatePage++" :disabled="graduatePage === totalGraduatePages" 
                        class="text-amber-600 hover:text-amber-800 disabled:text-slate-400 font-medium px-2 py-1 rounded transition">
                  Next &rarr;
                </button>
              </div>
            </div>
            <p v-else class="text-sm text-slate-500 italic p-2">No graduates currently assigned.</p>
          </div>

        </div> </div>
      
      <footer class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end">
        <button @click="$emit('close')"
          class="px-6 py-2.5 rounded-xl bg-slate-200 text-slate-700 font-semibold hover:bg-slate-300 transition-colors shadow-sm hover:shadow-md">
          Close View
        </button>
      </footer>

    </div>
  </div>
</template>