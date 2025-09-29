<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref, watch, computed } from "vue";
import { router, Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import * as XLSX from 'xlsx'
import Papa from 'papaparse'

const props = defineProps({ 
    departments: Array,
    hr: Object
    
});

const showAddModal = ref(false);
const newDepartment = ref("");
const batchDepartments = ref([]);
const editing = ref(null);
const editName = ref("");
const uploadFile = ref(null)
const uploadError = ref('')

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

function startEdit(dep) {
  editing.value = dep.id;
  editName.value = dep.department_name;
}
function saveEdit(dep) {
  router.put(`/company/departments/${dep.id}`, { department_name: editName.value }, {
    onSuccess: () => editing.value = null
  });
}
function deleteDepartment(dep) {
  if (confirm("Delete this department?")) {
    router.delete(`/company/departments/${dep.id}`);
  }
}

// Pagination (10 per page)
const perPage = 10;
const currentPage = ref(1);
const totalPages = computed(() => Math.max(1, Math.ceil((props.departments?.length || 0) / perPage)));
const pages = computed(() => Array.from({ length: totalPages.value }, (_, i) => i + 1));
const paginatedDepartments = computed(() => {
  const list = props.departments || [];
  const start = (currentPage.value - 1) * perPage;
  return list.slice(start, start + perPage);
});

// Reset page when data changes
watch(() => props.departments, () => { currentPage.value = 1; });
</script>

<template>
  <AppLayout title="Manage Departments">
    <div class="max-w-6xl mx-auto py-8">
      <!-- Stats Summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-2xl p-6 flex flex-col items-center text-center shadow hover:shadow-lg hover:scale-105 transition-all duration-200">
          <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center mb-3">
            <i class="fas fa-building text-white text-xl"></i>
          </div>
          <h3 class="text-yellow-700 text-sm font-medium mb-2">Total Departments</h3>
          <p class="text-3xl font-bold text-yellow-900">{{ props.departments ? props.departments.length : 0 }}</p>
        </div>
        <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-6 flex flex-col items-center text-center shadow hover:shadow-lg hover:scale-105 transition-all duration-200">
          <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3">
            <i class="fas fa-user-tie text-white text-xl"></i>
          </div>
          <h3 class="text-blue-700 text-sm font-medium mb-2">Your HR Name</h3>
          <p class="text-xl font-bold text-blue-900"> {{ props.hr ? (props.hr.full_name ?? `${props.hr.first_name} ${props.hr.last_name}`) : 'N/A' }}</p>
        </div>
        <div class="bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl p-6 flex flex-col items-center text-center shadow hover:shadow-lg hover:scale-105 transition-all duration-200">
          <div class="w-12 h-12 bg-gray-500 rounded-full flex items-center justify-center mb-3">
            <i class="fas fa-tasks text-white text-xl"></i>
          </div>
          <h3 class="text-gray-700 text-sm font-medium mb-2">Actions</h3>
          <div class="flex flex-col gap-2">
            <Link href="/company/manage-hrs?tab=departments" class="flex-shrink-0">
              <PrimaryButton class="flex items-center bg-gray-700 hover:bg-gray-800">
                <i class="fas fa-list mr-2"></i>
                List of Departments
              </PrimaryButton>
            </Link>
            <Link href="/company/departments/archived" class="flex-shrink-0">
              <PrimaryButton class="flex items-center bg-gray-400 hover:bg-gray-500">
                <i class="fas fa-archive mr-2"></i>
                Archived Departments
              </PrimaryButton>
            </Link>
          </div>
        </div>
      </div>

      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
          <i class="fas fa-building text-yellow-500 mr-2"></i>
          Departments
        </h2>
        <PrimaryButton class="flex items-center" @click="showAddModal = true">
          <i class="fas fa-plus-circle mr-2"></i>
          Add Department
        </PrimaryButton>
      </div>

      <!-- Add Department Modal (Batch Add) -->
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

      <div class="overflow-x-auto">
        <table class="min-w-full mt-8 bg-white border border-gray-200 rounded-lg shadow-sm">
          <thead class="bg-gradient-to-r from-yellow-50 to-yellow-200 text-sm font-semibold text-gray-700">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Department Name</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Added By</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            <tr v-for="dep in paginatedDepartments" :key="dep.id" class="hover:bg-gradient-to-r hover:from-yellow-50 hover:to-yellow-100 transition-all duration-200 group">
              <td class="px-6 py-4 whitespace-nowrap">
                <template v-if="editing === dep.id">
                  <input v-model="editName" class="border rounded px-2 py-1 w-full" />
                </template>
                <template v-else>
                  <span class="text-sm font-semibold text-gray-900">{{ dep.department_name }}</span>
                </template>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm text-gray-700">{{ dep.hr_name }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-2">
                  <template v-if="editing === dep.id">
                    <PrimaryButton class="mr-2" @click="saveEdit(dep)">Save</PrimaryButton>
                    <SecondaryButton @click="editing = null">Cancel</SecondaryButton>
                  </template>
                  <template v-else>
                    <PrimaryButton class="mr-2" @click="startEdit(dep)">
                      <i class="fas fa-edit mr-1"></i> Edit
                    </PrimaryButton>
                    <DangerButton @click="deleteDepartment(dep)">
                      <i class="fas fa-trash mr-1"></i> Delete
                    </DangerButton>
                  </template>
                </div>
              </td>
            </tr>
            <tr v-if="(props.departments?.length || 0) === 0">
              <td colspan="3" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center">
                  <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-search text-gray-400 text-2xl"></i>
                  </div>
                  <p class="text-gray-500 text-lg font-medium">No departments found</p>
                  <p class="text-gray-400 text-sm mt-1">Try adding a new department</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="(props.departments?.length || 0) > 0 && totalPages > 1" class="flex items-center justify-between px-6 py-3 bg-white border-t border-gray-100">
        <button
          class="px-3 py-1 text-sm rounded border border-gray-300 hover:bg-gray-50 disabled:opacity-50"
          :disabled="currentPage === 1"
          @click="currentPage--"
        >
          Prev
        </button>
        <div class="space-x-1">
          <button
            v-for="p in pages"
            :key="p"
            class="px-3 py-1 text-sm rounded border"
            :class="p === currentPage ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-50'"
            @click="currentPage = p"
          >
            {{ p }}
          </button>
        </div>
        <button
          class="px-3 py-1 text-sm rounded border border-gray-300 hover:bg-gray-50 disabled:opacity-50"
          :disabled="currentPage === totalPages"
          @click="currentPage++"
        >
          Next
        </button>
      </div>
    </div>
  </AppLayout>
</template>