<script setup>
import { ref, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';

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
        <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
          <i class="fas fa-chevron-left"></i>
        </button>
        <i class="fas fa-list text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Internship Programs</h2>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500 relative overflow-hidden">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-gray-600 text-sm font-medium mb-2">Total Programs</h3>
                <p class="text-3xl font-bold text-gray-800">{{ stats.total }}</p>
              </div>
              <div class="bg-blue-100 rounded-full p-3 flex items-center justify-center">
                <i class="fas fa-briefcase text-blue-600"></i>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500 relative overflow-hidden">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-gray-600 text-sm font-medium mb-2">Active Programs</h3>
                <p class="text-3xl font-bold text-gray-800">{{ stats.active }}</p>
              </div>
              <div class="bg-green-100 rounded-full p-3 flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600"></i>
              </div>
            </div>
          </div>
          <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500 relative overflow-hidden">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-gray-600 text-sm font-medium mb-2">Archived Programs</h3>
                <p class="text-3xl font-bold text-gray-800">{{ stats.archived }}</p>
              </div>
              <div class="bg-purple-100 rounded-full p-3 flex items-center justify-center">
                <i class="fas fa-archive text-purple-600"></i>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Filter and Search -->
        <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-filter text-indigo-500 mr-2"></i>
            Filter Options
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <!-- Program Filter -->
            <div>
              <label for="programFilter" class="block text-sm font-medium text-gray-700 mb-1">Program</label>
              <select
                id="programFilter"
                v-model="selectedProgram"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
              >
                <option value="">All Programs</option>
                <option v-for="program in programs" :key="program.id" :value="program.id.toString()">
                  {{ program.name }}
                </option>
              </select>
            </div>
            
            <!-- Career Opportunity Filter -->
            <div>
              <label for="careerFilter" class="block text-sm font-medium text-gray-700 mb-1">Career Opportunity</label>
              <select
                id="careerFilter"
                v-model="selectedCareerOpportunity"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
              >
                <option value="">All Opportunities</option>
                <option v-for="opportunity in careerOpportunities" :key="opportunity.id" :value="opportunity.id.toString()">
                  {{ opportunity.title }}
                </option>
              </select>
            </div>
            
            <!-- Skill Filter -->
            <div>
              <label for="skillFilter" class="block text-sm font-medium text-gray-700 mb-1">Skill</label>
              <select
                id="skillFilter"
                v-model="selectedSkill"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
              >
                <option value="">All Skills</option>
                <option v-for="skill in skills" :key="skill.id" :value="skill.id.toString()">
                  {{ skill.name }}
                </option>
              </select>
            </div>
            
            <!-- Status Filter -->
            <div>
              <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select
                id="statusFilter"
                v-model="selectedStatus"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
              >
                <option value="all">All</option>
                <option value="active">Active</option>
                <option value="archived">Archived</option>
              </select>
            </div>
          </div>
          
          <!-- Search -->
          <div class="flex flex-col md:flex-row items-center gap-4">
            <div class="relative w-full md:w-1/2">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
              </div>
              <input
                type="text"
                v-model="searchQuery"
                placeholder="Search internship programs..."
                class="pl-10 w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none"
              />
            </div>
          </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
          <!-- Action Buttons -->
          <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-200">
            <div class="flex items-center">
              <h3 class="text-lg font-semibold text-gray-800">All Internship Programs</h3>
              <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ filteredPrograms.length }} found</span>
            </div>
          </div>

          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead>
                <tr class="bg-gray-50">
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Programs</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Career Opportunities</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skills</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="internship in paginatedPrograms" :key="internship.id" class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ internship.title }}</td>
                  <td class="px-6 py-4 text-sm text-gray-600">
                    <div v-for="program in internship.programs" :key="program.id" class="mb-1 flex items-center">
                      <span class="inline-block w-2 h-2 rounded-full bg-blue-400 mr-2"></span>
                      {{ program.name }}
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-600">
                    <div v-for="careerOpportunity in internship.career_opportunities" :key="careerOpportunity.id" class="mb-1 flex items-center">
                      <span class="inline-block w-2 h-2 rounded-full bg-green-400 mr-2"></span>
                      {{ careerOpportunity.title }}
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-600">
                    <div v-for="skill in internship.skills" :key="skill.id" class="mb-1 flex items-center">
                      <span class="inline-block w-2 h-2 rounded-full bg-purple-400 mr-2"></span>
                      {{ skill.name }}
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm">
                    <span v-if="internship.deleted_at" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                      <i class="fas fa-archive mr-1"></i> Archived
                    </span>
                    <span v-else class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                      <i class="fas fa-check-circle mr-1"></i> Active
                    </span>
                  </td>
                </tr>
                <tr v-if="filteredPrograms.length === 0">
                  <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">
                    <div class="flex flex-col items-center justify-center">
                      <i class="fas fa-search text-gray-400 text-3xl mb-2"></i>
                      <p>No internship programs found.</p>
                      <p class="text-xs text-gray-400 mt-1">Try adjusting your filters</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Pagination -->
          <div v-if="totalPages > 1" class="px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="flex-1 flex justify-between sm:hidden">
              <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Previous
              </button>
              <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Next
              </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Showing <span class="font-medium">{{ (currentPage - 1) * itemsPerPage + 1 }}</span> to <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredPrograms.length) }}</span> of <span class="font-medium">{{ filteredPrograms.length }}</span> results
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    <span class="sr-only">Previous</span>
                    <i class="fas fa-chevron-left h-5 w-5"></i>
                  </button>
                  <button v-for="page in totalPages" :key="page" @click="goToPage(page)" :class="[currentPage === page ? 'z-10 bg-indigo-500 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50', 'relative inline-flex items-center px-4 py-2 border text-sm font-medium']">
                    {{ page }}
                  </button>
                  <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    <span class="sr-only">Next</span>
                    <i class="fas fa-chevron-right h-5 w-5"></i>
                  </button>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>