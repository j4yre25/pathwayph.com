<script setup>
import { ref, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import InternshipDetails from '@/Pages/Institutions/InternshipPrograms/InternshipDetails.vue';


const page = usePage();
const userId = page.props.auth.user.id;

const props = defineProps({
  internshipPrograms: Array,
  programs: Array,
  careerOpportunities: Array,
  skills: Array,
});

const selectedProgram = ref('');
const selectedCareerOpportunity = ref('');
const selectedSkill = ref('');
const selectedStatus = ref('all');
const searchQuery = ref('');
const showDetailsModal = ref(false);
const selectedProgramDetails = ref(null);

function viewProgramDetails(program) {
  selectedProgramDetails.value = program;
  showDetailsModal.value = true;
}

function closeDetailsModal() {
  showDetailsModal.value = false;
  selectedProgramDetails.value = null;
}

// Pagination
const currentPage = ref(1);
const itemsPerPage = 10;

// Computed property for filtering internship programs
const filteredPrograms = computed(() => {
  let filtered = props.internshipPrograms;

  // Filter by program
  if (selectedProgram.value) {
    filtered = filtered.filter(program =>
      program.programs.some(p => p.id.toString() === selectedProgram.value)
    );
  }

  // Filter by career opportunity
  if (selectedCareerOpportunity.value) {
    filtered = filtered.filter(program =>
      program.career_opportunities.some(co => co.id.toString() === selectedCareerOpportunity.value)
    );
  }

  // Filter by skill
  if (selectedSkill.value) {
    filtered = filtered.filter(program =>
      program.skills.some(s => s.id.toString() === selectedSkill.value)
    );
  }

  // Filter by status
  if (selectedStatus.value !== 'all') {
    filtered = filtered.filter(program =>
      selectedStatus.value === 'active' ? !program.deleted_at : program.deleted_at
    );
  }

  // Filter by search query
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase().trim();
    filtered = filtered.filter(program =>
      program.title.toLowerCase().includes(query) ||
      program.description.toLowerCase().includes(query)
    );
  }

  return filtered;
});

// Computed property for paginated programs
const paginatedPrograms = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filteredPrograms.value.slice(start, end);
});

// Computed property for total pages
const totalPages = computed(() => {
  return Math.ceil(filteredPrograms.value.length / itemsPerPage);
});

// Stats for cards
const stats = computed(() => {
  const total = props.internshipPrograms.length;
  const active = props.internshipPrograms.filter(program => !program.deleted_at).length;
  const archived = props.internshipPrograms.filter(program => program.deleted_at).length;

  return { total, active, archived };
});

// Function to go back
function goBack() {
  window.history.back();
}

// Function to go to a specific page
function goToPage(page) {
  currentPage.value = page;
}

// Watch for changes to apply filters immediately
watch([selectedProgram, selectedCareerOpportunity, selectedSkill, selectedStatus, searchQuery], () => {
  currentPage.value = 1; // Reset to first page when filters change
});
</script>

<template>
  <AppLayout title="Internship Programs List">
    
    <template #header>
      <div class="flex items-center">
        <button @click="goBack" class="mr-4 text-slate-500 hover:text-indigo-600 transition p-2 rounded-full hover:bg-slate-100" title="Go back">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
        </button>
        <h2 class="font-extrabold text-3xl text-slate-800 leading-tight tracking-tight flex items-center">
          <svg class="w-7 h-7 text-indigo-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.513 23.513 0 0112 15c-1.399 0-2.77-.282-4.032-.823l-3.264 3.264a1 1 0 01-1.414 0l-1.414-1.414a1 1 0 010-1.414l3.264-3.264C4.282 14.77 3 13.399 3 12c0-4.057 2.943-7.847 7-9.042V4a2 2 0 012 2v1a2 2 0 012-2h1a2 2 0 012 2v1h-1a2 2 0 00-2 2v1a2 2 0 002 2h1a2 2 0 012 2v1z"></path>
          </svg>
          Internship Programs
        </h2>
      </div>
    </template>

    <div class="py-10 bg-slate-50 min-h-screen">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          
          <div class="bg-white rounded-xl shadow-xl p-6 border-b-4 border-indigo-500 transition-all duration-300 hover:shadow-2xl">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-slate-500 text-sm font-semibold uppercase tracking-wider mb-2">Total Programs</h3>
                <p class="text-4xl font-extrabold text-slate-800">{{ stats.total }}</p>
              </div>
              <div class="bg-indigo-100 rounded-full p-3 flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.513 23.513 0 0112 15c-1.399 0-2.77-.282-4.032-.823l-3.264 3.264a1 1 0 01-1.414 0l-1.414-1.414a1 1 0 010-1.414l3.264-3.264C4.282 14.77 3 13.399 3 12c0-4.057 2.943-7.847 7-9.042V4a2 2 0 012 2v1a2 2 0 012-2h1a2 2 0 012 2v1h-1a2 2 0 00-2 2v1a2 2 0 002 2h1a2 2 0 012 2v1z"></path>
                </svg>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-xl shadow-xl p-6 border-b-4 border-emerald-500 transition-all duration-300 hover:shadow-2xl">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-slate-500 text-sm font-semibold uppercase tracking-wider mb-2">Active Programs</h3>
                <p class="text-4xl font-extrabold text-slate-800">{{ stats.active }}</p>
              </div>
              <div class="bg-emerald-100 rounded-full p-3 flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.204A9.956 9.956 0 0112 20C7.265 20 4 17.735 4 12c0-2.386.671-4.708 1.956-6.791m0 0L4 7m1.956-2.791L7 4"></path>
                </svg>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-xl shadow-xl p-6 border-b-4 border-red-500 transition-all duration-300 hover:shadow-2xl">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-slate-500 text-sm font-semibold uppercase tracking-wider mb-2">Archived Programs</h3>
                <p class="text-4xl font-extrabold text-slate-800">{{ stats.archived }}</p>
              </div>
              <div class="bg-red-100 rounded-full p-3 flex items-center justify-center shadow-inner">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg border border-slate-100">
          <h3 class="text-xl font-bold text-slate-800 mb-5 flex items-center">
            <svg class="w-5 h-5 text-indigo-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-4.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
            </svg>
            Advanced Filtering
          </h3>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            
            <div>
              <label for="programFilter" class="block text-xs font-medium text-slate-600 uppercase mb-2">Program</label>
              <select id="programFilter" v-model="selectedProgram"
                class="w-full border-slate-300 rounded-lg shadow-sm text-sm py-2.5 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                <option value="">All Programs</option>
                <option v-for="program in programs" :key="program.id" :value="program.id.toString()">
                  {{ program.name }}
                </option>
              </select>
            </div>

            <div>
              <label for="careerFilter" class="block text-xs font-medium text-slate-600 uppercase mb-2">Career Opportunity</label>
              <select id="careerFilter" v-model="selectedCareerOpportunity"
                class="w-full border-slate-300 rounded-lg shadow-sm text-sm py-2.5 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                <option value="">All Opportunities</option>
                <option v-for="opportunity in careerOpportunities" :key="opportunity.id"
                  :value="opportunity.id.toString()">
                  {{ opportunity.title }}
                </option>
              </select>
            </div>

            <div>
              <label for="skillFilter" class="block text-xs font-medium text-slate-600 uppercase mb-2">Skill</label>
              <select id="skillFilter" v-model="selectedSkill"
                class="w-full border-slate-300 rounded-lg shadow-sm text-sm py-2.5 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                <option value="">All Skills</option>
                <option v-for="skill in skills" :key="skill.id" :value="skill.id.toString()">
                  {{ skill.name }}
                </option>
              </select>
            </div>

            <div>
              <label for="statusFilter" class="block text-xs font-medium text-slate-600 uppercase mb-2">Status</label>
              <select id="statusFilter" v-model="selectedStatus"
                class="w-full border-slate-300 rounded-lg shadow-sm text-sm py-2.5 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150">
                <option value="all">All</option>
                <option value="active">Active</option>
                <option value="archived">Archived</option>
              </select>
            </div>
          </div>
          
          <div class="mt-6">
            <label for="searchQuery" class="sr-only">Search</label>
            <div class="relative w-full">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
              <input type="text" id="searchQuery" v-model="searchQuery" placeholder="Search by program title or description..."
                class="pl-10 w-full border-slate-300 rounded-lg shadow-sm text-sm py-2.5 focus:ring-indigo-500 focus:border-indigo-500 transition duration-150" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-slate-100 overflow-hidden">
          
          <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-slate-200 bg-slate-50/50">
            <div class="flex items-center">
              <h3 class="text-xl font-bold text-slate-800">Program List</h3>
              <span class="ml-3 text-xs font-medium text-slate-600 bg-slate-200 rounded-full px-3 py-1">{{
                filteredPrograms.length }} total results</span>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
              <thead>
                <tr class="bg-slate-50">
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Title</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Programs
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Career
                    Opportunities</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Skills</th>
                  <th class="px-6 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-slate-200">
                <tr v-for="internship in paginatedPrograms" :key="internship.id"
                  class="hover:bg-indigo-50/30 transition-colors duration-150">
                  <td class="px-6 py-4 text-sm font-medium text-slate-900 w-1/5">
                      <p class="truncate max-w-xs" :title="internship.title">{{ internship.title }}</p>
                  </td>
                  <td class="px-6 py-4 text-xs text-slate-600 w-1/5">
                    <div class="flex flex-wrap gap-1.5">
                        <span v-for="program in internship.programs.slice(0, 2)" :key="program.id"
                            class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                            {{ program.name }}
                        </span>
                        <span v-if="internship.programs.length > 2" class="text-xs text-slate-500 font-medium">+{{ internship.programs.length - 2 }} more</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-xs text-slate-600 w-1/5">
                    <div class="flex flex-wrap gap-1.5">
                        <span v-for="careerOpportunity in internship.career_opportunities.slice(0, 2)" :key="careerOpportunity.id"
                            class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200">
                            {{ careerOpportunity.title }}
                        </span>
                         <span v-if="internship.career_opportunities.length > 2" class="text-xs text-slate-500 font-medium">+{{ internship.career_opportunities.length - 2 }} more</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-xs text-slate-600 w-1/5">
                    <div class="flex flex-wrap gap-1.5">
                        <span v-for="skill in internship.skills.slice(0, 2)" :key="skill.id"
                            class="px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 border border-purple-200">
                            {{ skill.name }}
                        </span>
                        <span v-if="internship.skills.length > 2" class="text-xs text-slate-500 font-medium">+{{ internship.skills.length - 2 }} more</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-center text-sm w-1/12">
                    <span v-if="internship.deleted_at"
                      class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 shadow-sm">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg> 
                      Archived
                    </span>
                    <span v-else
                      class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 shadow-sm">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.204A9.956 9.956 0 0112 20C7.265 20 4 17.735 4 12c0-2.386.671-4.708 1.956-6.791m0 0L4 7m1.956-2.791L7 4"></path></svg>
                      Active
                    </span>
                  </td>
                   <td class="px-6 py-4 text-center text-sm text-slate-600 w-1/12">
                    <button @click="viewProgramDetails(internship)" class="p-2 text-indigo-500 hover:text-indigo-700 transition duration-150 rounded-full hover:bg-indigo-100"
                      title="View Details">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                    </button>
                  </td>
                </tr>
                <tr v-if="filteredPrograms.length === 0">
                  <td colspan="6" class="px-6 py-10 text-center text-sm text-slate-500 bg-slate-50/70">
                    <div class="flex flex-col items-center justify-center">
                      <svg class="w-8 h-8 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                      </svg>
                      <p class="text-lg font-medium text-slate-600">No internship programs found.</p>
                      <p class="text-sm text-slate-500 mt-1">Try adjusting or clearing your filters.</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="totalPages > 1"
            class="px-4 py-4 flex items-center justify-between border-t border-slate-200 bg-slate-50">
            
            <div class="flex-1 flex justify-between sm:hidden">
              <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                class="relative inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-100 disabled:opacity-50 transition shadow-sm">
                Previous
              </button>
              <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-100 disabled:opacity-50 transition shadow-sm">
                Next
              </button>
            </div>
            
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-slate-600">
                  Showing <span class="font-semibold">{{ (currentPage - 1) * itemsPerPage + 1 }}</span> to <span
                    class="font-semibold">{{ Math.min(currentPage * itemsPerPage, filteredPrograms.length) }}</span> of
                  <span class="font-semibold">{{ filteredPrograms.length }}</span> results
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-lg shadow-md -space-x-px" aria-label="Pagination">
                  <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                    class="relative inline-flex items-center px-3 py-2 rounded-l-lg border border-slate-300 bg-white text-sm font-medium text-slate-500 hover:bg-slate-100 disabled:opacity-50 transition">
                    <span class="sr-only">Previous</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                  </button>
                  
                  <button v-for="page in totalPages" :key="page" @click="goToPage(page)"
                    :class="[
                      currentPage === page 
                        ? 'z-10 bg-indigo-600 border-indigo-600 text-white shadow-inner font-bold' 
                        : 'bg-white border-slate-300 text-slate-700 hover:bg-indigo-50/70', 
                      'relative inline-flex items-center px-4 py-2 border text-sm font-medium transition duration-150'
                    ]">
                    {{ page }}
                  </button>
                  
                  <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                    class="relative inline-flex items-center px-3 py-2 rounded-r-lg border border-slate-300 bg-white text-sm font-medium text-slate-500 hover:bg-slate-100 disabled:opacity-50 transition">
                    <span class="sr-only">Next</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                  </button>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <InternshipDetails
      :show="showDetailsModal"
      :program="selectedProgramDetails"
      @close="closeDetailsModal" />
  </AppLayout>
</template>