<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  internshipPrograms: Array,
  graduates: Array,
  programs: Array,
  careerOpportunities: Array,
  assignedGraduates: Object,
});

// State variables
const selectedGraduates = ref([]);
const selectedInternshipProgram = ref(null);
const showAssignModal = ref(false);
const showRemoveModal = ref(false);
const graduateToRemove = ref(null);
const internshipProgramToRemoveFrom = ref(null);
const isAssigning = ref(false);
const isRemoving = ref(false);
const selectedProgram = ref('');
const selectedCareerOpportunity = ref('');

// Pagination state
const currentPage = ref(1);
const itemsPerPage = ref(10);

// Computed properties
const filteredGraduates = computed(() => {
  let filtered = [...props.graduates];

  if (selectedProgram.value) {
    filtered = filtered.filter(graduate => {
      return graduate.program && graduate.program.id == selectedProgram.value;
    });
  }

  if (selectedCareerOpportunity.value) {
    filtered = filtered.filter(graduate => {
      return graduate.career_opportunity && graduate.career_opportunity.id == selectedCareerOpportunity.value;
    });
  }

  return filtered;
});

// Pagination computed properties
const totalPages = computed(() => {
  return Math.ceil(filteredGraduates.value.length / itemsPerPage.value);
});

const paginatedGraduates = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredGraduates.value.slice(start, end);
});

const stats = computed(() => {
  return [
    {
      title: 'Total Internship Programs',
      value: props.internshipPrograms.length,
      icon: 'fas fa-briefcase',
      color: 'text-blue-600',
      bgColor: 'bg-blue-100'
    },
    {
      title: 'Total Graduates',
      value: props.graduates.length,
      icon: 'fas fa-user-graduate',
      color: 'text-green-600',
      bgColor: 'bg-green-100'
    },
    {
      title: 'Total Assignments',
      value: props.assignedGraduates ? Object.values(props.assignedGraduates).reduce((acc, curr) => acc + curr.length, 0) : 0,
      icon: 'fas fa-link',
      color: 'text-purple-600',
      bgColor: 'bg-purple-100'
    }
  ];
});

// Methods
const confirmAssign = () => {
  if (!selectedInternshipProgram.value || selectedGraduates.value.length === 0) {
    return;
  }

  showAssignModal.value = true;
};

const assignGraduates = () => {
  if (!selectedInternshipProgram.value || selectedGraduates.value.length === 0) {
    return;
  }

  isAssigning.value = true;

  router.post(
    route('internship-programs.assign-graduates'),
    {
      internship_program_id: selectedInternshipProgram.value,
      graduate_ids: selectedGraduates.value,
    },
    {
      onSuccess: () => {
        selectedGraduates.value = [];
        showAssignModal.value = false;
        isAssigning.value = false;
      },
      onFinish: () => {
        isAssigning.value = false;
      },
    }
  );
};

const confirmRemove = (graduate, internshipProgramId) => {
  graduateToRemove.value = graduate;
  internshipProgramToRemoveFrom.value = internshipProgramId;
  showRemoveModal.value = true;
};

const removeGraduate = () => {
  if (!graduateToRemove.value || !internshipProgramToRemoveFrom.value) {
    return;
  }

  isRemoving.value = true;

  router.post(
    route('internship-programs.remove-graduate'),
    {
      internship_program_id: internshipProgramToRemoveFrom.value,
      graduate_id: graduateToRemove.value.id,
    },
    {
      onSuccess: () => {
        showRemoveModal.value = false;
        isRemoving.value = false;
      },
      onFinish: () => {
        isRemoving.value = false;
      },
    }
  );
};

const resetFilters = () => {
  selectedProgram.value = '';
  selectedCareerOpportunity.value = '';
  currentPage.value = 1; // Reset to first page when filters change
};

const goBack = () => {
  window.history.back();
};

// Pagination methods
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

// Watch for changes to reset selection when filters change
watch([selectedProgram, selectedCareerOpportunity], () => {
  selectedGraduates.value = [];
  currentPage.value = 1; // Reset to first page when filters change
});
</script>

<template>
  <AppLayout title="Assign Graduates to Internship Programs">
    <template #header>
      <div>
        <div class="flex items-center">
          <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
            <i class="fas fa-chevron-left"></i>
          </button>
          <i class="fas fa-link text-blue-500 text-xl mr-2"></i>
          <h2 class="text-2xl font-bold text-gray-800">Assign Graduates to Internship Programs</h2>
        </div>
        <p class="text-sm text-gray-500 mb-1">Manage graduate assignments to internship programs.</p>
      </div>
    </template>

    <Container class="py-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div v-for="(stat, index) in stats" :key="index" 
             class="bg-white rounded-lg shadow-sm p-6 border-l-4" 
             :class="`border-${stat.color.split('-')[1]}-500`">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">{{ stat.title }}</h3>
              <p class="text-3xl font-bold text-gray-800">{{ stat.value }}</p>
            </div>
            <div :class="[stat.bgColor, 'rounded-full p-3 flex items-center justify-center']">
              <i :class="[stat.icon, stat.color]"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Assignment Section -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200 mb-6">
        <div class="p-4 border-b border-gray-200 bg-gray-50 flex items-center">
          <i class="fas fa-user-plus text-blue-500 mr-2"></i>
          <h2 class="font-semibold text-gray-800">Assign Graduates</h2>
        </div>
        
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Internship Program Selection -->
            <div>
              <label for="internship-program" class="block text-sm font-medium text-gray-700 mb-2">Select Internship Program</label>
              <select
                id="internship-program"
                v-model="selectedInternshipProgram"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
              >
                <option value="">Select an internship program</option>
                <option v-for="program in internshipPrograms" :key="program.id" :value="program.id">
                  {{ program.title }}
                </option>
              </select>
            </div>

            <!-- Graduate Filters -->
            <div>
              <label for="program-filter" class="block text-sm font-medium text-gray-700 mb-2">Filter by Program</label>
              <select
                id="program-filter"
                v-model="selectedProgram"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
              >
                <option value="">All Programs</option>
                <option v-for="program in programs" :key="program.id" :value="program.id">
                  {{ program.name }}
                </option>
              </select>
            </div>

            <div>
              <label for="career-filter" class="block text-sm font-medium text-gray-700 mb-2">Filter by Career Opportunity</label>
              <select
                id="career-filter"
                v-model="selectedCareerOpportunity"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
              >
                <option value="">All Career Opportunities</option>
                <option v-for="career in careerOpportunities" :key="career.id" :value="career.id">
                  {{ career.title }}
                </option>
              </select>
            </div>
          </div>

          <div class="flex justify-end space-x-3 mb-6">
            <button 
              @click="resetFilters"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <i class="fas fa-undo-alt mr-2"></i>
              Reset Filters
            </button>
            <button 
              @click="confirmAssign"
              :disabled="!selectedInternshipProgram || selectedGraduates.length === 0"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <i class="fas fa-user-plus mr-2"></i>
              Assign Selected Graduates
            </button>
          </div>

          <!-- Graduates Table -->
          <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <input 
                      type="checkbox" 
                      :checked="filteredGraduates.length > 0 && selectedGraduates.length === filteredGraduates.length"
                      @change="e => e.target.checked ? selectedGraduates = filteredGraduates.map(g => g.id) : selectedGraduates = []"
                      class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                    >
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Career Opportunity</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year Graduated</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="graduate in paginatedGraduates" :key="graduate.id" class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <input 
                      type="checkbox" 
                      :value="graduate.id"
                      v-model="selectedGraduates"
                      class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
                    >
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-gray-500"></i>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ graduate.first_name }} {{ graduate.last_name }}</div>
                        <div class="text-sm text-gray-500">{{ graduate.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ graduate.program ? graduate.program.name : 'N/A' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ graduate.career_opportunity ? graduate.career_opportunity.title : 'N/A' }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ graduate.year_graduated }}
                  </td>
                </tr>
                <tr v-if="filteredGraduates.length === 0">
                  <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                    No graduates found matching the selected filters.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Pagination Controls -->
          <div v-if="totalPages > 1" class="px-6 py-4 bg-white border-t border-gray-200 flex items-center justify-between mt-4">
            <div class="text-sm text-gray-700">
              Showing <span class="font-medium">{{ ((currentPage - 1) * itemsPerPage) + 1 }}</span> to
              <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredGraduates.length) }}</span> of
              <span class="font-medium">{{ filteredGraduates.length }}</span> results
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

          <!-- Assigned Graduates Section -->
          <div>
            <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200 mb-6">
              <div class="p-4 border-b border-gray-200 bg-gray-50 flex items-center">
                <i class="fas fa-users text-green-500 mr-2"></i>
                <h2 class="font-semibold text-gray-800">Assigned Graduates</h2>
              </div>
              
              <div class="p-6 space-y-8">
                <div v-for="program in internshipPrograms" :key="program.id" class="border-b border-gray-200 pb-6 last:border-b-0 last:pb-0">
                  <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-briefcase text-blue-500 mr-2"></i>
                    {{ program.title }}
                    <span class="ml-2 text-sm text-gray-500">({{ props.assignedGraduates && props.assignedGraduates[program.id] ? props.assignedGraduates[program.id].length : 0 }} graduates)</span>
                  </h3>
                  
                  <div v-if="props.assignedGraduates && props.assignedGraduates[program.id] && props.assignedGraduates[program.id].length > 0" class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50">
                        <tr>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Career Opportunity</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year Graduated</th>
                          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                      </thead>
                      <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="graduate in props.assignedGraduates && props.assignedGraduates[program.id] ? props.assignedGraduates[program.id] : []" :key="graduate.id" class="hover:bg-gray-50 transition-colors">
                          <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                              <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-gray-500"></i>
                              </div>
                              <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ graduate.first_name }} {{ graduate.last_name }}</div>
                                <div class="text-sm text-gray-500">{{ graduate.email }}</div>
                              </div>
                            </div>
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ graduate.program ? graduate.program.name : 'N/A' }}</div>
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ graduate.career_opportunity ? graduate.career_opportunity.title : 'N/A' }}</div>
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ graduate.year_graduated }}
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button 
                              @click="confirmRemove(graduate, program.id)"
                              class="text-red-600 hover:text-red-900 focus:outline-none focus:underline"
                            >
                              <i class="fas fa-user-minus mr-1"></i>
                              Remove
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <div v-else class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200">
                    <i class="fas fa-users text-gray-300 text-5xl mb-4"></i>
                    <p class="text-gray-500">No graduates assigned to this internship program yet</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          

          <!-- Assign Modal -->
          <div v-if="showAssignModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
              <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showAssignModal = false"></div>
              <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
              <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                  <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                      <i class="fas fa-user-plus text-blue-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                      <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Assign Graduates</h3>
                      <div class="mt-2">
                        <p class="text-sm text-gray-500">
                          Are you sure you want to assign {{ selectedGraduates.length }} graduate(s) to this internship program?
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                  <button 
                    type="button" 
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                    @click="assignGraduates"
                    :disabled="isAssigning"
                  >
                    <span v-if="isAssigning">Assigning...</span>
                    <span v-else>Assign</span>
                  </button>
                  <button 
                    type="button" 
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    @click="showAssignModal = false"
                  >
                    Cancel
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Remove Modal -->
          <div v-if="showRemoveModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
              <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showRemoveModal = false"></div>
              <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
              <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                  <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                      <i class="fas fa-user-minus text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                      <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Remove Graduate</h3>
                      <div class="mt-2">
                        <p class="text-sm text-gray-500">
                          Are you sure you want to remove {{ graduateToRemove ? `${graduateToRemove.first_name} ${graduateToRemove.last_name}` : '' }} from this internship program?
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                  <button 
                    type="button" 
                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                    @click="removeGraduate"
                    :disabled="isRemoving"
                  >
                    <span v-if="isRemoving">Removing...</span>
                    <span v-else>Remove</span>
                  </button>
                  <button 
                    type="button" 
                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    @click="showRemoveModal = false"
                  >
                    Cancel
                  </button>
                </div>
              </div>
            </div>
          </div>
      </Container>
    </AppLayout>
</template>