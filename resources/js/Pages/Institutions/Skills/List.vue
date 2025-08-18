<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
  skills: Array,
  careerOpportunities: Array,
  selectedCareerOpportunity: String,
});

const userId = usePage().props.auth.user.id;
const selectedCareerOpportunity = ref(props.selectedCareerOpportunity || '');
const searchQuery = ref('');
const selectedStatus = ref('all');

const goBack = () => {
  window.history.back();
};

const uniqueCareerOpportunities = computed(() => {
  const seenTitles = new Set();
  return (props.careerOpportunities || []).filter((opportunity) => {
    if (seenTitles.has(opportunity.title)) {
      return false;
    }
    seenTitles.add(opportunity.title);
    return true;
  });
});

const filteredSkills = computed(() => {
  let filtered = props.skills;
  
  // Filter by career opportunity
  if (selectedCareerOpportunity.value) {
    filtered = filtered.filter(
      (skill) => String(skill.career_opportunity_id) === String(selectedCareerOpportunity.value)
    );
  }
  
  // Filter by status
  if (selectedStatus.value !== 'all') {
    filtered = filtered.filter(skill => 
      selectedStatus.value === 'active' ? !skill.deleted_at : skill.deleted_at
    );
  }
  
  // Filter by search query
  if (searchQuery.value?.trim()) {
    const query = searchQuery.value.toLowerCase().trim();
    filtered = filtered.filter(skill => 
      skill.skill?.name.toLowerCase().includes(query)
    );
  }
  
  return filtered;
});

// Pagination
const currentPage = ref(1);
const itemsPerPage = 30;
const totalPages = computed(() => Math.ceil(filteredSkills.value.length / itemsPerPage));
const paginatedSkills = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  return filteredSkills.value.slice(startIndex, endIndex);
});

const goToPage = (page) => {
  currentPage.value = page;
};

// Reset pagination when filters change
onMounted(() => {
  currentPage.value = 1;
});

// Watch for changes to apply filters immediately
watch([selectedCareerOpportunity, selectedStatus, searchQuery], () => {
  currentPage.value = 1; // Reset to first page when filters change
});
</script>

<template>
  <AppLayout title="Skills List">
    <template #header>
      <div>
        <div class="flex items-center">
          <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
          <i class="fas fa-chevron-left"></i>
        </button>
          <i class="fas fa-cogs text-purple-500 text-xl mr-2"></i>
          <h2 class="text-2xl font-semibold text-gray-800">Skills</h2>
        </div>
      </div>
    </template>

    <Container class="py-8 space-y-6">
      <!-- Filters -->
      <div class="flex gap-4 flex-wrap items-center bg-white p-4 rounded-md shadow-sm">
        <div>
          <label class="block text-sm font-medium text-gray-700">Filter by Career Opportunity:</label>
          <select v-model="selectedCareerOpportunity" class="mt-1 border-gray-300 rounded-md">
            <option value="">All Career Opportunities</option>
            <option
              v-for="opportunity in uniqueCareerOpportunities"
              :key="opportunity.id"
              :value="opportunity.id"
            >
              {{ opportunity.title }}
            </option>
          </select>
        </div>
      </div>

      <!-- Filter and Search -->
      <div class="bg-white p-4 rounded-lg shadow-sm flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
          <div class="flex items-center">
            <i class="fas fa-filter text-gray-500 mr-2"></i>
            <label for="statusFilter" class="font-medium text-gray-700">Filter by Status:</label>
          </div>
          <select
            id="statusFilter"
            v-model="selectedStatus"
            class="border border-gray-300 rounded-md px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"
          >
            <option value="all">All</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
          </div>
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search skills..."
            class="pl-10 border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none w-full md:w-64"
          />
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-700">
          <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-600">
            <tr>
              <th class="px-6 py-4">Career Opportunity</th>
              <th class="px-6 py-4">Skill Name</th>
              <th class="px-6 py-4">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="skill in paginatedSkills"
              :key="skill.id"
              class="border-t hover:bg-gray-50 transition"
            >
              <td class="px-6 py-4">{{ skill.career_opportunity?.title }}</td>
              <td class="px-6 py-4">{{ skill.skill?.name }}</td>
              <td class="px-6 py-4 font-semibold" :class="skill.deleted_at ? 'text-red-600' : 'text-green-600'">
                {{ skill.deleted_at ? 'Inactive' : 'Active' }}
              </td>
            </tr>
            <tr v-if="filteredSkills.length === 0">
              <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                No skills found.
              </td>
            </tr>
          </tbody>
        </table>
        
        <!-- Pagination Controls -->
        <div v-if="totalPages > 1" class="px-6 py-4 bg-white border-t border-gray-200 flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing <span class="font-medium">{{ ((currentPage - 1) * itemsPerPage) + 1 }}</span> to
            <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredSkills.length) }}</span> of
            <span class="font-medium">{{ filteredSkills.length }}</span> results
          </div>
          <div class="flex space-x-2">
            <button 
              @click="goToPage(currentPage - 1)" 
              :disabled="currentPage === 1"
              class="px-3 py-1 rounded border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>
            <div class="flex space-x-1">
              <template v-if="totalPages <= 7">
                <button 
                  v-for="page in totalPages" 
                  :key="page" 
                  @click="goToPage(page)"
                  :class="{
                    'bg-blue-600 text-white': page === currentPage,
                    'bg-white text-gray-700 hover:bg-gray-50': page !== currentPage
                  }"
                  class="px-3 py-1 rounded border border-gray-300 text-sm font-medium"
                >
                  {{ page }}
                </button>
              </template>
              <template v-else>
                <!-- First page -->
                <button 
                  @click="goToPage(1)"
                  :class="{
                    'bg-blue-600 text-white': 1 === currentPage,
                    'bg-white text-gray-700 hover:bg-gray-50': 1 !== currentPage
                  }"
                  class="px-3 py-1 rounded border border-gray-300 text-sm font-medium"
                >
                  1
                </button>
                
                <!-- Ellipsis if needed -->
                <span v-if="currentPage > 3" class="px-3 py-1 text-gray-500">...</span>
                
                <!-- Pages around current page -->
                <template v-for="page in totalPages" :key="page">
                  <button 
                    v-if="page !== 1 && page !== totalPages && Math.abs(page - currentPage) < 2"
                    @click="goToPage(page)"
                    :class="{
                      'bg-blue-600 text-white': page === currentPage,
                      'bg-white text-gray-700 hover:bg-gray-50': page !== currentPage
                    }"
                    class="px-3 py-1 rounded border border-gray-300 text-sm font-medium"
                  >
                    {{ page }}
                  </button>
                </template>
                
                <!-- Ellipsis if needed -->
                <span v-if="currentPage < totalPages - 2" class="px-3 py-1 text-gray-500">...</span>
                
                <!-- Last page -->
                <button 
                  @click="goToPage(totalPages)"
                  :class="{
                    'bg-blue-600 text-white': totalPages === currentPage,
                    'bg-white text-gray-700 hover:bg-gray-50': totalPages !== currentPage
                  }"
                  class="px-3 py-1 rounded border border-gray-300 text-sm font-medium"
                >
                  {{ totalPages }}
                </button>
              </template>
            </div>
            <button 
              @click="goToPage(currentPage + 1)" 
              :disabled="currentPage === totalPages"
              class="px-3 py-1 rounded border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </Container>
  </AppLayout>
</template>