<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link, router } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref, computed, watch } from 'vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import '@fortawesome/fontawesome-free/css/all.css';
import Papa from 'papaparse';
import * as XLSX from 'xlsx';

const page = usePage();

const props = defineProps({
  hrs: Array,
  departments: Array
});

// Tab system
const initialTab = (() => {
  try {
    const qs = new URLSearchParams(page.url.split('?')[1] || '');
    const t = qs.get('tab');
    return t === 'departments' ? 'departments' : 'hr-officers';
  } catch (e) {
    return 'hr-officers';
  }
})();
const activeTab = ref(initialTab);

// Filter states
const searchQuery = ref('');
const selectedStatus = ref('');
const selectedDepartment = ref('');

// Modal states
const showModal = ref(false);
const userToArchive = ref(null);
const showAddModal = ref(false);
const newDepartment = ref("");
const batchDepartments = ref([]);
const uploadFile = ref(null);
const uploadError = ref('');

// Filter options
const statusOptions = [
    { value: '', label: 'All Statuses' },
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' }
];

// Computed filtered data
const filteredHRs = computed(() => {
    return props.hrs.filter(hr => {
        const matchesSearch = hr.HR_Name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                            hr.User_Email.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesStatus = selectedStatus.value ? hr.Status === selectedStatus.value : true;
        return matchesSearch && matchesStatus;
    });
});
const filteredDepartments = computed(() => {
    if (!props.departments) return [];
    return props.departments.filter(dept => {
        const matchesSearch = dept.department_name.toLowerCase().includes(searchQuery.value.toLowerCase());
        return matchesSearch;
    });
});

// Pagination (10 per page)
const perPage = 10;

// HR pagination
const currentPageHR = ref(1);
const totalPagesHR = computed(() => Math.max(1, Math.ceil(filteredHRs.value.length / perPage)));
const pagesHR = computed(() => Array.from({ length: totalPagesHR.value }, (_, i) => i + 1));
const paginatedHRs = computed(() => {
  const start = (currentPageHR.value - 1) * perPage;
  return filteredHRs.value.slice(start, start + perPage);
});

// Departments pagination
const currentPageDept = ref(1);
const totalPagesDept = computed(() => Math.max(1, Math.ceil(filteredDepartments.value.length / perPage)));
const pagesDept = computed(() => Array.from({ length: totalPagesDept.value }, (_, i) => i + 1));
const paginatedDepartments = computed(() => {
  const start = (currentPageDept.value - 1) * perPage;
  return filteredDepartments.value.slice(start, start + perPage);
});

// Reset filters
function resetFilters() {
    searchQuery.value = '';
    selectedStatus.value = '';
    selectedDepartment.value = '';
}

// Reset page when filters change
watch(searchQuery, () => {
  currentPageHR.value = 1;
  currentPageDept.value = 1;
});
watch(selectedStatus, () => { currentPageHR.value = 1; });

// When switching tab, keep page sane
function switchTab(tab) {
    activeTab.value = tab;
    resetFilters();
    currentPageHR.value = 1;
    currentPageDept.value = 1;
}

// Department modal functions
function addToBatch() {
    if (newDepartment.value.trim() && !batchDepartments.value.includes(newDepartment.value.trim())) {
        batchDepartments.value.push(newDepartment.value.trim());
        newDepartment.value = "";
    }
}

function removeFromBatch(index) {
    batchDepartments.value.splice(index, 1);
}

function saveBatchDepartments() {
    if (batchDepartments.value.length === 0) return;
    router.post("/company/departments/batch", { department_names: batchDepartments.value }, {
        onSuccess: () => {
            batchDepartments.value = [];
            newDepartment.value = "";
            showAddModal.value = false;
        }
    });
}

function handleFileChange(e) {
  uploadError.value = ''
  const file = e.target.files[0]
  if (!file) return

  const ext = file.name.split('.').pop().toLowerCase()
  if (ext === 'csv') {
    Papa.parse(file, {
      complete: results => {
        const header = results.data[0].map(h => String(h).trim().toLowerCase())
        const nameIdx = header.indexOf('department_name')
        if (nameIdx === -1) {
          uploadError.value = 'CSV must have a "department_name" column.'
          return
        }
        results.data.slice(1).forEach(row => {
          const name = row[nameIdx] && String(row[nameIdx]).trim()
          if (name && !batchDepartments.value.includes(name)) {
            batchDepartments.value.push(name)
          }
        })
      },
      error: () => uploadError.value = 'Failed to parse CSV file.'
    })
  } else if (ext === 'xlsx' || ext === 'xls') {
    const reader = new FileReader()
    reader.onload = (evt) => {
      const workbook = XLSX.read(evt.target.result, { type: 'binary' })
      const sheet = workbook.Sheets[workbook.SheetNames[0]]
      const rows = XLSX.utils.sheet_to_json(sheet, { header: 1 })
      const header = rows[0].map(h => String(h).trim().toLowerCase())
      const nameIdx = header.indexOf('department_name')
      if (nameIdx === -1) {
        uploadError.value = 'Sheet must have a "department_name" column.'
        return
      }
      rows.slice(1).forEach(row => {
        const name = row[nameIdx] && String(row[nameIdx]).trim()
        if (name && !batchDepartments.value.includes(name)) {
          batchDepartments.value.push(name)
        }
      })
    }
    reader.readAsBinaryString(file)
  } else {
    uploadError.value = 'Only CSV, XLSX, or XLS files are supported.'
  }
}

const editHR = (hr) => {
  console.log('Edit HR', hr);
};


const deleteHR = async (hrId) => {
  if (confirm('Are you sure you want to delete this HR?')) {
    try {
      await axios.delete(`/api/hrs/${hrId}`);
      alert('HR deleted successfully!');
    } catch (error) {
      console.error(error);
      alert('An error occurred while deleting the HR.');
    }
  }
};

const archiveUser = () => {
    // Archive user logic
    showModal.value = false;
};
</script>


<template>
    <AppLayout title="Manage HR">
        <template #header>
            <div class="flex items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                    <i class="fas fa-users text-blue-500 mr-2"></i> Manage HR
                </h2>
            </div>
        </template>

        <Container class="py-8">
            <!-- Stats Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total HR Officers -->
                <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-users text-white text-xl"></i>
                        </div>
                        <h3 class="text-blue-700 text-sm font-medium mb-2">Total HR Officers</h3>
                        <p class="text-3xl font-bold text-blue-700">{{ props.hrs.length }}</p>
                    </div>
                </div>
                <!-- Active HR Officers -->
                <div class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-user-check text-white text-xl"></i>
                        </div>
                        <h3 class="text-green-700 text-sm font-medium mb-2">Active HR Officers</h3>
                        <p class="text-3xl font-bold text-green-900">
                            {{ props.hrs.filter(hr => hr.Status === 'active').length }}
                        </p>
                    </div>
                </div>
                <!-- Inactive HR Officers -->
                <div class="bg-gradient-to-br from-red-100 to-red-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-user-times text-white text-xl"></i>
                        </div>
                        <h3 class="text-red-700 text-sm font-medium mb-2">Inactive HR Officers</h3>
                        <p class="text-3xl font-bold text-red-900">
                            {{ props.hrs.filter(hr => hr.Status !== 'active').length }}
                        </p>
                    </div>
                </div>
                <!-- Total Departments -->
                <div class="bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-building text-white text-xl"></i>
                        </div>
                        <h3 class="text-yellow-700 text-sm font-medium mb-2">Total Departments</h3>
                        <p class="text-3xl font-bold text-yellow-900">
                            {{ props.departments ? props.departments.length : 0 }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Search and filters -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-200 flex items-center">
                    <i class="fas fa-filter text-blue-500 mr-2"></i>
                    <div>
                        <h3 class="font-medium text-gray-700">Search & Filter</h3>
                        <p class="text-sm text-gray-500">Find HR officers and departments by criteria below</p>
                    </div>
                    <div class="ml-auto">
                        <button
                            class="px-6 py-3 bg-white text-gray-700 rounded-xl text-sm font-medium flex items-center hover:bg-gray-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 shadow-sm border border-gray-300"
                            @click="resetFilters">
                            <i class="fas fa-undo mr-2"></i> Reset Filters
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Search Input -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                                <input 
                                    id="search"
                                    v-model="searchQuery" 
                                    type="text" 
                                    placeholder="Search HR officers or departments..." 
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                />
                            </div>
                        </div>

                        <!-- Status Dropdown -->
                        <div v-if="activeTab === 'hr-officers'">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <div class="relative">
                                <select 
                                    id="status"
                                    v-model="selectedStatus" 
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10"
                                >
                                    <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Spacing between filter and tabs -->
            <div class="my-6"></div>

            <!-- Tab Navigation -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8 px-6" aria-label="Tabs">
                        <button
                            @click="switchTab('hr-officers')"
                            :class="[
                                activeTab === 'hr-officers'
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center'
                            ]"
                        >
                            <i class="fas fa-users mr-2"></i>
                            HR Officers
                        </button>
                        <button
                            @click="switchTab('departments')"
                            :class="[
                                activeTab === 'departments'
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center'
                            ]"
                        >
                            <i class="fas fa-building mr-2"></i>
                            Departments
                        </button>
                    </nav>
                </div>

                <!-- Department Action Buttons (Only visible when departments tab is active) -->
                <div v-if="activeTab === 'departments'" class="bg-white rounded-2xl p-6 flex flex-wrap items-center justify-between gap-4 border-b border-gray-100">
                    <div class="flex items-center space-x-4">
                        <Link href="/company/departments/manage" class="flex-shrink-0">
                            <button class="px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border text-gray-600 hover:bg-gray-50 border-gray-200">
                                <i class="fas fa-tasks mr-2"></i>
                                <span>Manage Departments</span>
                            </button>
                        </Link>
                        <Link href="/company/departments/archived" class="flex-shrink-0">
                            <button class="px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border text-gray-600 hover:bg-gray-50 border-gray-200">
                                <i class="fas fa-archive mr-2"></i>
                                <span>Archived Departments</span>
                            </button>
                        </Link>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button 
                            @click="showAddModal = true"
                            class="px-4 py-2 rounded-md text-white font-medium transition-colors flex items-center shadow-sm bg-blue-500 hover:bg-blue-600"
                        >
                            <i class="fas fa-plus-circle mr-2"></i>
                            <span>Add Department</span>
                        </button>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- HR Officers Tab -->
                    <div v-if="activeTab === 'hr-officers'">
  <div class="overflow-x-auto">
    <table class="min-w-full">
      <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 text-sm font-semibold text-gray-700">
        <tr>
          <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Full Name</th>
          <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email Address</th>
          <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contact Details</th>
          <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 bg-white">
        <tr v-for="hr in paginatedHRs" :key="hr.User_Email" class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group">
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="text-sm font-semibold text-gray-900">{{ hr.HR_Name }}</span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="text-sm text-gray-700">{{ hr.User_Email }}</span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="text-sm text-gray-700">{{ hr.Mob_Num }}</span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span :class="hr.Status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border shadow-sm">
              <i :class="hr.Status === 'active' ? 'fas fa-check-circle mr-1.5' : 'fas fa-times-circle mr-1.5'"></i>
              {{ hr.Status }}
            </span>
          </td>
        </tr>
        <tr v-if="filteredHRs.length === 0">
          <td colspan="4" class="px-6 py-12 text-center">
            <div class="flex flex-col items-center">
              <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-search text-gray-400 text-2xl"></i>
              </div>
              <p class="text-gray-500 text-lg font-medium">No HR officers found</p>
              <p class="text-gray-400 text-sm mt-1">Try adjusting your search criteria</p>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- HR pagination -->
  <div v-if="filteredHRs.length > 0 && totalPagesHR > 1" class="flex items-center justify-between px-6 py-3 bg-white border-t border-gray-100">
    <button
      class="px-3 py-1 text-sm rounded border border-gray-300 hover:bg-gray-50 disabled:opacity-50"
      :disabled="currentPageHR === 1"
      @click="currentPageHR--"
    >
      Prev
    </button>
    <div class="space-x-1">
      <button
        v-for="p in pagesHR"
        :key="p"
        class="px-3 py-1 text-sm rounded border"
        :class="p === currentPageHR ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-50'"
        @click="currentPageHR = p"
      >
        {{ p }}
      </button>
    </div>
    <button
      class="px-3 py-1 text-sm rounded border border-gray-300 hover:bg-gray-50 disabled:opacity-50"
      :disabled="currentPageHR === totalPagesHR"
      @click="currentPageHR++"
    >
      Next
    </button>
  </div>
</div>

                    <!-- Departments Tab -->
                    <div v-if="activeTab === 'departments'">
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 text-sm font-semibold text-gray-700">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Department Name</th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Added By</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <tr v-for="dept in paginatedDepartments" :key="dept.id" class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-semibold text-gray-900">{{ dept.department_name }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm text-gray-700">{{ dept.hr_name }}</span>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredDepartments.length === 0">
                                        <td colspan="2" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center">
                                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                                    <i class="fas fa-search text-gray-400 text-2xl"></i>
                                                </div>
                                                <p class="text-gray-500 text-lg font-medium">No departments found</p>
                                                <p class="text-gray-400 text-sm mt-1">Try adjusting your search criteria</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Departments pagination -->
  <div v-if="filteredDepartments.length > 0 && totalPagesDept > 1" class="flex items-center justify-between px-6 py-3 bg-white border-t border-gray-100">
    <button
      class="px-3 py-1 text-sm rounded border border-gray-300 hover:bg-gray-50 disabled:opacity-50"
      :disabled="currentPageDept === 1"
      @click="currentPageDept--"
    >
      Prev
    </button>
    <div class="space-x-1">
      <button
        v-for="p in pagesDept"
        :key="p"
        class="px-3 py-1 text-sm rounded border"
        :class="p === currentPageDept ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-50'"
        @click="currentPageDept = p"
      >
        {{ p }}
      </button>
    </div>
    <button
      class="px-3 py-1 text-sm rounded border border-gray-300 hover:bg-gray-50 disabled:opacity-50"
      :disabled="currentPageDept === totalPagesDept"
      @click="currentPageDept++"
    >
      Next
    </button>
  </div>
                    </div>
                </div>
            </div>

            <!-- Add Department Modal -->
            <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
                <div class="bg-white rounded shadow-lg p-6 w-full max-w-sm" style="max-height: 90vh; overflow-y: auto;">
                <h3 class="text-lg font-semibold mb-4">Add Departments</h3>
                <form @submit.prevent="addToBatch" class="flex gap-2 mb-4">
                    <input v-model="newDepartment" class="border rounded px-2 py-1 flex-1" placeholder="Department Name" />
                    <button type="submit" class="px-3 py-1 rounded bg-blue-600 text-white">Add</button>
                </form>
                <!-- Batch Upload Section -->
                    <div class="mb-2">
                        <label class="block font-medium mb-1">Batch Upload (CSV/XLSX):</label>
                        <a
                        v-if="!uploadFile"
                        :href="route('company.departments.batch.template')"
                        class="inline-block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 mb-4"
                        download
                        >
                            ⬇ Download CSV Template
                        </a>
                        <input type="file" accept=".csv,.xlsx,.xls" @change="e => { handleFileChange(e); uploadFile.value = e.target.files[0] }" class="mb-1" />
                        <div v-if="uploadError" class="text-red-600 text-xs">{{ uploadError }}</div>
                        <div class="text-xs text-gray-500">File must have a <b>department_name</b> column.</div>
                    </div>
                    <ul class="mb-4">
                        <li v-for="(dep, idx) in batchDepartments" :key="dep" class="flex items-center gap-2 mb-1">
                        <span class="flex-1">{{ dep }}</span>
                        <button @click="removeFromBatch(idx)" class="text-red-600 text-xs">Remove</button>
                        </li>
                    </ul>
                    <div class="flex gap-2 justify-end">
                        <button type="button" class="px-3 py-1 rounded bg-gray-200" @click="showAddModal = false">Cancel</button>
                        <button type="button" class="px-3 py-1 rounded bg-blue-600 text-white"
                        :disabled="batchDepartments.length === 0"
                        @click="saveBatchDepartments">
                        Save All
                        </button>
                    </div>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <ConfirmationModal @close="showModal = false" :show="showModal">
                <template #title>
                    Are you sure?
                </template>

                <template #content>
                    Are you sure you want to archive this user {{ userToArchive?.company_hr_first_name }} {{ userToArchive?.company_hr_last_name }}?
                </template>

                <template #footer>
                    <DangerButton @click="archiveUser" class="mr-2">
                        Archive User
                    </DangerButton>
                    <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                </template>
            </ConfirmationModal>
        </Container>
    </AppLayout>
</template>
