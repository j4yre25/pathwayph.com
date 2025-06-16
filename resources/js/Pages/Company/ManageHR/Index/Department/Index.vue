<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { ref } from "vue";
import { router, Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

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

</script>

<template>
  <AppLayout title="Departments">
    <div class="max-w-4xl mx-auto py-8">
        <div class="flex flex-wrap gap-3 mb-6">
            <PrimaryButton class="flex items-center" @click="showAddModal = true">
                <i class="fas fa-plus-circle mr-2"></i>
                Add Department
            </PrimaryButton>
            <Link href="/company/departments/manage" class="flex-shrink-0">
                <PrimaryButton class="flex items-center bg-gray-700 hover:bg-gray-800">
                    <i class="fas fa-tasks mr-2"></i>
                    Manage Departments
                </PrimaryButton>
            </Link>
            <Link href="/company/departments/archived" class="flex-shrink-0">
                <PrimaryButton class="flex items-center bg-gray-400 hover:bg-gray-500">
                    <i class="fas fa-archive mr-2"></i>
                    Archived Departments
                </PrimaryButton>
            </Link>
        </div>

      <!-- Add Department Modal (Batch Add) -->
      <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
        <div class="bg-white rounded shadow-lg p-6 w-full max-w-sm">
          <h3 class="text-lg font-semibold mb-4">Add Departments</h3>
          <form @submit.prevent="addToBatch" class="flex gap-2 mb-4">
            <input v-model="newDepartment" class="border rounded px-2 py-1 flex-1" placeholder="Department Name" />
            <button type="submit" class="px-3 py-1 rounded bg-blue-600 text-white">Add</button>
          </form>
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
            <th class="w-2/3 px-4 py-3 border text-left">Department Name</th>
            <th class="w-1/3 px-4 py-3 border text-center">Added By</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="dep in departments" :key="dep.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 border">
                {{ dep.department_name }}
            </td>
            <td class="px-4 py-3 border text-center">
              {{ dep.hr_name }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>