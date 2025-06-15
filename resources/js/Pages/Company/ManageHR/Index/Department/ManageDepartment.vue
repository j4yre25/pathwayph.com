<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref } from "vue";
import { router, Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import * as XLSX from 'xlsx'
import Papa from 'papaparse'

// Props
const props = defineProps({ 
    departments: Array,
    hr: Object
});

// Modal state
const showAddModal = ref(false);
const newDepartment = ref("");
const batchDepartments = ref([]); // Array for batch add
const editing = ref(null);
const editName = ref("");

// Batch Uploading setup
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
//End Batch Uploading setup

// Add to batch list
function addToBatch() {
  if (newDepartment.value.trim() && !batchDepartments.value.includes(newDepartment.value.trim())) {
    batchDepartments.value.push(newDepartment.value.trim());
    newDepartment.value = "";
  }
}

// Remove from batch list
function removeFromBatch(index) {
  batchDepartments.value.splice(index, 1);
}

// Submit all new departments
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

// Edit logic (existing departments)
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
</script>

<template>
  <AppLayout title="Departments">
    <div class="max-w-4xl mx-auto py-8">
      <div class="flex gap-2 mb-6 justify-between">
        <div class="flex flex-wrap gap-3 mb-6">
            <PrimaryButton class="flex items-center" @click="showAddModal = true">
                <i class="fas fa-plus-circle mr-2"></i>
                Add Department
            </PrimaryButton>
            <Link href="/company/departments" class="flex-shrink-0">
                <PrimaryButton class="flex items-center bg-gray-700 hover:bg-gray-800">
                    <i class="fas fa-tasks mr-2"></i>
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

      <h2 class="text-2xl font-bold mb-4">Departments</h2>
      <table class="min-w-full bg-white border border-gray-300 table-fixed shadow rounded">
        <thead class="bg-blue-100 text-gray-700 uppercase text-sm">
          <tr>
            <th class="w-1/2 px-4 py-3 border text-left">Department Name</th>
            <th class="w-1/4 px-4 py-3 border text-center">Added By</th>
            <th class="w-1/4 px-4 py-3 border text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="dep in departments" :key="dep.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 border">
              <template v-if="editing === dep.id">
                <input v-model="editName" class="border rounded px-2 py-1 w-full" />
              </template>
              <template v-else>
                {{ dep.department_name }}
              </template>
            </td>
            <td class="px-4 py-3 border text-center">
              {{ dep.hr_name }}
            </td>
            <td class="px-4 py-3 border text-center">
              <template v-if="editing === dep.id">
                <button @click="saveEdit(dep)" class="text-green-600 mr-2">Save</button>
                <button @click="editing = null" class="text-gray-500">Cancel</button>
              </template>
              <template v-else>
                <button @click="startEdit(dep)" class="text-blue-600 mr-2">Edit</button>
                <button @click="deleteDepartment(dep)" class="text-red-600">Delete</button>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>