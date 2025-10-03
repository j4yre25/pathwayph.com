<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  internshipPrograms: Array,
  graduates: Array,
  programs: Array,
  careerOpportunities: Array,
  institutionCareerOpportunities: Array,
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

// Graduates Pagination state (Left Column)
const currentPage = ref(1);
const itemsPerPage = ref(10);

// Program Pagination state (Right Column - NEW)
const programCurrentPage = ref(1);
const programItemsPerPage = ref(4); // Show 2 programs at a time

// Computed properties
const filteredGraduates = computed(() => {
  let filtered = [...props.graduates];

  // Filter by program
  if (selectedProgram.value) {
    filtered = filtered.filter(g => String(g.program_id) === String(selectedProgram.value));
  }

  // Filter by career opportunity (job title)
  if (selectedCareerOpportunity.value) {
    filtered = filtered.filter(g => (g.current_job_title ?? '').toLowerCase() === selectedCareerOpportunity.value.toLowerCase());
  }

  return filtered;
});

// Graduates Pagination computed properties (Left Column)
const totalPages = computed(() => {
  return Math.ceil(filteredGraduates.value.length / itemsPerPage.value);
});

const paginatedGraduates = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredGraduates.value.slice(start, end);
});

// Program Pagination computed properties (Right Column - NEW)
const totalProgramPages = computed(() => {
  return Math.ceil(props.internshipPrograms.length / programItemsPerPage.value);
});

const paginatedPrograms = computed(() => {
  const start = (programCurrentPage.value - 1) * programItemsPerPage.value;
  const end = start + programItemsPerPage.value;
  return props.internshipPrograms.slice(start, end);
});

const stats = computed(() => {
  // Logic remains the same
  return [
    {
      title: 'Total Programs',
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
    route('internship-programs.assign'),
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

// Graduates Pagination methods
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

// Program Pagination methods (NEW)
const goToProgramPage = (page) => {
  if (page >= 1 && page <= totalProgramPages.value) {
    programCurrentPage.value = page;
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
      <div class="flex items-center justify-between py-4 border-b border-gray-200 bg-white shadow-sm px-4 sm:px-6 lg:px-8 -mx-4 sm:-mx-6 lg:-mx-8">
        <div class="flex items-center">
          <button @click="goBack" class="mr-4 p-2 rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition">
            <i class="fas fa-arrow-left"></i>
          </button>
          <div class="flex items-center">
            <i class="fas fa-user-friends text-blue-600 text-2xl mr-3"></i>
            <h2 class="text-2xl font-extrabold text-gray-900">Graduate Assignment Hub</h2>
          </div>
        </div>
        <p class="text-sm text-gray-500 hidden sm:block">Manage graduate assignments to internship programs.</p>
      </div>
    </template>

    <div class="py-10 bg-gray-50 min-h-screen">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div v-for="(stat, index) in stats" :key="index" 
               class="bg-white rounded-xl shadow-xl p-6 border-l-4 transition-transform duration-300 hover:scale-[1.01] hover:shadow-2xl"
               :class="`border-${stat.color.split('-')[1]}-500`">
            <div class="flex justify-between items-center">
              <div>
                <h3 class="text-gray-500 text-sm font-semibold uppercase tracking-wider mb-1">{{ stat.title }}</h3>
                <p class="text-4xl font-extrabold text-gray-900">{{ stat.value }}</p>
              </div>
              <div :class="[stat.bgColor, 'rounded-full p-4 flex items-center justify-center shadow-inner']">
                <i :class="[stat.icon, stat.color, 'text-xl']"></i>
              </div>
            </div>
          </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          
          <div class="lg:col-span-2 space-y-8">
            <div class="bg-white shadow-2xl rounded-xl overflow-hidden border border-gray-100">
              <div class="p-6 border-b border-gray-100 bg-white flex items-center justify-between">
                <div class="flex items-center">
                  <i class="fas fa-list-alt text-blue-600 mr-3 text-xl"></i>
                  <h2 class="font-bold text-xl text-gray-800">Available Graduates for Assignment</h2>
                </div>
                <button @click="confirmAssign" :disabled="!selectedInternshipProgram || selectedGraduates.length === 0"
                  class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-md text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                  <i class="fas fa-link mr-2"></i>
                  Assign ({{ selectedGraduates.length }})
                </button>
              </div>

              <div class="p-6 border-b border-gray-100 bg-gray-50">
                <h3 class="text-sm font-bold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-search text-gray-400 mr-2"></i> Filter & Selection
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                  
                  <div class="md:col-span-2">
                    <label for="internship-program" class="block text-xs font-medium text-gray-500 mb-1">Select Program to Assign To</label>
                    <select id="internship-program" v-model="selectedInternshipProgram"
                      class="w-full border-gray-300 rounded-lg text-sm shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500/50">
                      <option value="">Select an internship program</option>
                      <option v-for="program in internshipPrograms" :key="program.id" :value="program.id">
                        {{ program.title }}
                      </option>
                    </select>
                  </div>

                  <div>
                    <label for="program-filter" class="block text-xs font-medium text-gray-500 mb-1">Filter by Program</label>
                    <select id="program-filter" v-model="selectedProgram"
                      class="w-full border-gray-300 rounded-lg text-sm shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500/50">
                      <option value="">All Programs</option>
                      <option v-for="program in programs" :key="program.id" :value="program.id">{{ program.name }}</option>
                    </select>
                  </div>

                  <div>
                    <label for="career-filter" class="block text-xs font-medium text-gray-500 mb-1">Filter by Job Title</label>
                    <select id="career-filter" v-model="selectedCareerOpportunity"
                      class="w-full border-gray-300 rounded-lg text-sm shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500/50">
                      <option value="">All Job Titles</option>
                      <option v-for="career in careerOpportunities" :key="career.title" :value="career.title">{{ career.title }}</option>
                    </select>
                  </div>
                </div>
                <div class="flex justify-end mt-4">
                  <button @click="resetFilters"
                    class="inline-flex items-center px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 justify-center">
                    <i class="fas fa-undo-alt mr-2"></i>
                    Reset Filters
                  </button>
                </div>
              </div>

              <div class="p-6">
                <div class="overflow-x-auto shadow-inner border border-gray-100 rounded-lg">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                      <tr>
                        <th scope="col"
                          class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                          <input type="checkbox"
                            :checked="filteredGraduates.length > 0 && selectedGraduates.length === filteredGraduates.length"
                            @change="e => e.target.checked ? selectedGraduates = filteredGraduates.map(g => g.id) : selectedGraduates = []"
                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded-md shadow-sm cursor-pointer">
                        </th>
                        <th scope="col"
                          class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                          Name</th>
                        <th scope="col"
                          class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">
                          Program</th>
                        <th scope="col"
                          class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Job
                          Title</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                      <tr v-for="graduate in paginatedGraduates" :key="graduate.id"
                        class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <input type="checkbox" :value="graduate.id" v-model="selectedGraduates"
                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded-md shadow-sm cursor-pointer">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 bg-blue-500/10 rounded-full flex items-center justify-center">
                              <i class="fas fa-user text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                              <div class="text-sm font-medium text-gray-900">{{ graduate.first_name }} {{ graduate.last_name }}
                              </div>
                              <div class="text-xs text-gray-500">{{ graduate.email }}</div>
                            </div>
                          </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ graduate.program_name }}
                          </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm text-gray-700">{{ graduate.current_job_title ?? 'N/A' }}</div>
                        </td>
                      </tr>
                      <tr v-if="filteredGraduates.length === 0">
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                          <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-filter-slash text-gray-300 text-5xl mb-4"></i>
                            <p class="text-lg font-semibold">No graduates found</p>
                            <p class="text-sm text-gray-400 mt-1">Try clearing your filters.</p>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <div v-if="totalPages > 1"
                  class="px-6 py-4 bg-white flex items-center justify-between mt-4">
                  <div class="text-sm text-gray-600">
                    Showing <span class="font-semibold text-gray-800">{{ ((currentPage - 1) * itemsPerPage) + 1 }}</span> to
                    <span class="font-semibold text-gray-800">{{ Math.min(currentPage * itemsPerPage, filteredGraduates.length) }}</span> of
                    <span class="font-semibold text-gray-800">{{ filteredGraduates.length }}</span> results
                  </div>
                  <div class="flex space-x-2">
                    <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                      class="p-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                      <i class="fas fa-chevron-left"></i>
                    </button>
                    <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                      class="p-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                      <i class="fas fa-chevron-right"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="lg:col-span-1 space-y-4">
            <div class="bg-white shadow-2xl rounded-xl overflow-hidden border border-gray-100 h-full flex flex-col">
                <div class="p-4 border-b border-gray-100 bg-white flex items-center">
                    <i class="fas fa-check-double text-green-600 mr-3 text-xl"></i>
                    <h2 class="font-bold text-lg text-gray-800">Current Program Assignments</h2>
                </div>
                
                <div class="p-4 flex-grow space-y-6 overflow-y-auto">
                    <div v-if="internshipPrograms.length === 0" class="text-center py-10">
                        <i class="fas fa-exclamation-circle text-gray-400 text-3xl mb-3"></i>
                        <p class="text-gray-500 text-sm">No internship programs available.</p>
                    </div>

                    <div v-for="program in paginatedPrograms" :key="program.id"
                        class="border border-gray-200 rounded-lg p-3 shadow-md bg-white">
                        
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-bold text-gray-900 truncate flex-1 mr-2" :title="program.title">
                                {{ program.title }}
                            </h4>
                            <span class="text-xs font-semibold text-gray-600 bg-gray-100 px-2 py-0.5 rounded-full flex-shrink-0">
                                {{ program.graduates ? program.graduates.length : 0 }} Assigned
                            </span>
                        </div>

                        <div v-if="program.graduates && program.graduates.length > 0" 
                            class="max-h-60 overflow-y-auto border border-gray-100 rounded-md p-2 space-y-2 bg-gray-50">
                            <div v-for="graduate in program.graduates" :key="graduate.id" 
                                class="flex items-center justify-between text-xs p-1 rounded hover:bg-white transition-colors">
                                <span class="font-medium text-gray-700 truncate">{{ graduate.first_name }} {{ graduate.last_name }}</span>
                                <button @click="confirmRemove(graduate, program.id)"
                                    title="Remove Graduate"
                                    class="text-red-500 hover:text-red-700 transition-colors flex-shrink-0 ml-2">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </div>
                        </div>
                        <div v-else class="text-center py-4 text-xs text-gray-500 bg-gray-50 rounded-md border border-dashed border-gray-200">
                            No assignments yet.
                        </div>
                    </div>
                </div>

                <div v-if="totalProgramPages > 1" class="p-4 border-t border-gray-100 bg-gray-50 flex justify-between items-center">
                    <button @click="goToProgramPage(programCurrentPage - 1)" :disabled="programCurrentPage === 1"
                        class="p-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <span class="text-xs text-gray-600">Page {{ programCurrentPage }} of {{ totalProgramPages }}</span>
                    <button @click="goToProgramPage(programCurrentPage + 1)" :disabled="programCurrentPage === totalProgramPages"
                        class="p-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
          </div>
          </div>

        <div v-if="showAssignModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
          role="dialog" aria-modal="true">
          <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true"
              @click="showAssignModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
              class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
              <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                  <div
                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                    <i class="fas fa-user-plus text-blue-600"></i>
                  </div>
                  <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title">Confirm Assignment</h3>
                    <div class="mt-2">
                      <p class="text-sm text-gray-600">
                        Are you sure you want to assign <strong class="font-semibold text-blue-600">{{ selectedGraduates.length }}</strong> graduate(s) to the selected internship program?
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button"
                  class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm"
                  @click="assignGraduates" :disabled="isAssigning">
                  <i v-if="isAssigning" class="fas fa-spinner fa-spin mr-2"></i>
                  <span v-if="isAssigning">Assigning...</span>
                  <span v-else>Confirm & Assign</span>
                </button>
                <button type="button"
                  class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-100 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                  @click="showAssignModal = false">
                  Cancel
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-if="showRemoveModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
          role="dialog" aria-modal="true">
          <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" aria-hidden="true"
              @click="showRemoveModal = false"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
              class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
              <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                  <div
                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <i class="fas fa-trash-alt text-red-600"></i>
                  </div>
                  <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title">Remove Graduate</h3>
                    <div class="mt-2">
                      <p class="text-sm text-gray-600">
                        Are you sure you want to remove <strong class="font-semibold text-red-600">{{ graduateToRemove ? `${graduateToRemove.first_name}
                        ${graduateToRemove.last_name}` : 'this graduate' }}</strong> from this internship program?
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button"
                  class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                  @click="removeGraduate" :disabled="isRemoving">
                  <i v-if="isRemoving" class="fas fa-spinner fa-spin mr-2"></i>
                  <span v-if="isRemoving">Removing...</span>
                  <span v-else>Remove</span>
                </button>
                <button type="button"
                  class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-100 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                  @click="showRemoveModal = false">
                  Cancel
                </button>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>