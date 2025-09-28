<script setup>
import { ref, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import '@fortawesome/fontawesome-free/css/all.css';

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
  <AppLayout title="Internship Programs">
    <template #header>
      <div class="flex items-center">
        <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
          <i class="fas fa-chevron-left"></i>
        </button>
        <i class="fas fa-briefcase text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Internship Programs</h2>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <!-- Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Total Programs Card -->
          <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
            <div class="flex flex-col items-center text-center">
              <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3">
                <i class="fas fa-briefcase text-white text-lg"></i>
              </div>
              <h3 class="text-blue-700 text-sm font-medium mb-2">Total Programs</h3>
              <p class="text-2xl font-bold text-blue-900">{{ stats.total }}</p>
            </div>
          </div>

          <!-- Active Programs Card -->
          <div class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
            <div class="flex flex-col items-center text-center">
              <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-3">
                <i class="fas fa-check-circle text-white text-lg"></i>
              </div>
              <h3 class="text-green-700 text-sm font-medium mb-2">Active Programs</h3>
              <p class="text-2xl font-bold text-green-900">{{ stats.active }}</p>
            </div>
          </div>

          <!-- Archived Programs Card -->
          <div class="bg-gradient-to-br from-purple-100 to-purple-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
            <div class="flex flex-col items-center text-center">
              <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mb-3">
                <i class="fas fa-archive text-white text-lg"></i>
              </div>
              <h3 class="text-purple-700 text-sm font-medium mb-2">Archived Programs</h3>
              <p class="text-2xl font-bold text-purple-900">{{ stats.archived }}</p>
            </div>
          </div>
        </div>
        
        <!-- Filter and Search -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-filter text-blue-500 mr-2"></i>
            Filter Internship Programs
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <!-- Search -->
            <div>
              <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
              <div class="relative">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>
                <input
                  id="search"
                  type="text"
                  v-model="searchQuery"
                  placeholder="Search internship programs..."
                  class="pl-9 w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200"
                />
              </div>
            </div>
          
            <!-- Program Filter -->
            <div>
              <label for="programFilter" class="block text-sm font-medium text-gray-700 mb-2">Program</label>
              <select
                id="programFilter"
                v-model="selectedProgram"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200"
              >
                <option value="">All Programs</option>
                <option v-for="program in programs" :key="program.id" :value="program.id.toString()">
                  {{ program.name }}
                </option>
              </select>
            </div>
            
            <!-- Career Opportunity Filter -->
            <div>
              <label for="careerFilter" class="block text-sm font-medium text-gray-700 mb-2">Career Opportunity</label>
              <select
                id="careerFilter"
                v-model="selectedCareerOpportunity"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200"
              >
                <option value="">All Opportunities</option>
                <option v-for="opportunity in careerOpportunities" :key="opportunity.id" :value="opportunity.id.toString()">
                  {{ opportunity.title }}
                </option>
              </select>
            </div>
            
            <!-- Skill Filter -->
            <div>
              <label for="skillFilter" class="block text-sm font-medium text-gray-700 mb-2">Skill</label>
              <select
                id="skillFilter"
                v-model="selectedSkill"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200"
              >
                <option value="">All Skills</option>
                <option v-for="skill in skills" :key="skill.id" :value="skill.id.toString()">
                  {{ skill.name }}
                </option>
              </select>
            </div>
            
            <!-- Status Filter -->
            <div>
              <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
              <select
                id="statusFilter"
                v-model="selectedStatus"
                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-colors duration-200"
              >
                <option value="all">All</option>
                <option value="active">Active</option>
                <option value="archived">Archived</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
          <!-- List Header -->
          <div class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center">
              <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                <i class="fas fa-table text-white"></i>
              </div>
              <div>
                <h3 class="text-xl font-bold text-gray-800">Internship Programs</h3>
                <p class="text-sm text-gray-600">Browse and filter internship programs</p>
                <span class="text-sm font-semibold text-gray-700">{{ filteredPrograms.length }} found</span>
              </div>
            </div>
          </div>

          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full table-auto">
              <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 text-sm font-semibold text-gray-700">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Title</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Programs</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Career Opportunities</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Skills</th>
                  <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr v-for="internship in paginatedPrograms" :key="internship.id" :class="{ 'bg-gray-50': internship.deleted_at }" class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200">
                  <td class="px-6 py-5 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center text-blue-600 shadow-sm">
                        <i class="fas fa-briefcase"></i>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-semibold text-gray-900">{{ internship.title }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-5 whitespace-nowrap">
                    <div class="flex flex-wrap gap-1">
                      <span v-for="program in internship.programs" :key="program.id" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ program.name }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-5 whitespace-nowrap">
                    <div class="flex flex-wrap gap-1">
                      <span v-for="careerOpportunity in internship.career_opportunities" :key="careerOpportunity.id" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ careerOpportunity.title }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-5 whitespace-nowrap">
                    <div class="flex flex-wrap gap-1">
                      <span v-for="skill in internship.skills" :key="skill.id" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                        {{ skill.name }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-5 whitespace-nowrap">
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
          <div v-if="totalPages > 1" class="flex items-center justify-center mt-8 mb-6 px-6">
            <div class="flex items-center space-x-2">
              <button
                @click="goToPage(Math.max(1, currentPage - 1))"
                :disabled="currentPage === 1"
                :class="[
                  'px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                  currentPage === 1 
                    ? 'text-gray-400 bg-gray-100 cursor-not-allowed' 
                    : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 hover:border-gray-400'
                ]"
              >
                Previous
              </button>
              <div class="flex space-x-1">
                <button 
                  v-for="pageNum in totalPages" 
                  :key="pageNum" 
                  @click="goToPage(pageNum)"
                  :class="[
                    'px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                    pageNum === currentPage 
                      ? 'bg-blue-600 text-white shadow-md' 
                      : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 hover:border-gray-400'
                  ]"
                >
                  {{ pageNum }}
                </button>
              </div>
              <button
                @click="goToPage(Math.min(totalPages, currentPage + 1))"
                :disabled="currentPage === totalPages"
                :class="[
                  'px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200',
                  currentPage === totalPages 
                    ? 'text-gray-400 bg-gray-100 cursor-not-allowed' 
                    : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 hover:border-gray-400'
                ]"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>