<template>
  <AppLayout title="Graduate Management">
    <!-- Page Header -->
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Graduate Management</h2>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-xl rounded-2xl p-6">

          <!-- Action Row -->
          <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-4">
              <label class="text-sm text-gray-700">Filter:</label>
              <select v-model="filter" @change="applyFilter" class="border rounded px-2 py-1 text-sm">
                <option value="active">Active</option>
                <option value="inactive">Archived</option>
              </select>
            </div>

            <div class="flex items-center space-x-2">
              <button @click="openAddModal" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Graduate</button>
              <button @click="openBatchUploadModal" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Batch Upload</button>
            </div>
          </div>

          <!-- Graduate Table -->
          <table class="min-w-full table-auto border rounded-lg overflow-hidden">
            <thead class="bg-gray-100 text-sm text-left">
              <tr>
                <th class="p-3">Name</th>
                <th class="p-3">Program</th>
                <th class="p-3">Year Graduated</th>
                <th class="p-3">Current Job Title</th>
                <th class="p-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="grad in graduates" :key="grad.id" class="border-b hover:bg-gray-50 text-sm">
                <td class="p-3">{{ grad.full_name }}</td>
                <td class="p-3">{{ grad.program_name }}</td>
                <td class="p-3">{{ grad.year_graduated }}</td>
                <td class="p-3">{{ grad.current_job_title ?? 'N/A' }}</td>
                <td class="p-3 text-right">
                  <Menu as="div" class="relative inline-block text-left">
                    <MenuButton class="text-gray-600 hover:text-gray-900">
                      &#x22EE;
                    </MenuButton>
                    <MenuItems class="absolute right-0 mt-1 w-36 bg-white border rounded-md shadow-lg z-10">
                      <MenuItem v-slot="{ active }">
                        <button @click="editGraduate(grad)" class="block w-full text-left px-4 py-2 text-sm" :class="{ 'bg-gray-100': active }">Edit</button>
                      </MenuItem>
                      <MenuItem v-slot="{ active }">
                        <button @click="deleteGraduate(grad.id)" class="block w-full text-left px-4 py-2 text-sm text-red-600" :class="{ 'bg-gray-100': active }">Archive</button>
                      </MenuItem>
                    </MenuItems>
                  </Menu>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Graduate Modal -->
          <GraduateModal
            :show="isModalOpen"
            :graduate="selectedGraduate"
            :programs="programs"
            :years="years"
            @close="closeModal"
          />

          <!-- Batch Upload Modal -->
          <BatchUploadModal
            :show="isBatchUploadModalOpen"
            @close="isBatchUploadModalOpen = false"
          />

        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'

import AppLayout from '@/Layouts/AppLayout.vue'
import GraduateModal from './GraduateModal.vue'
import BatchUploadModal from './BatchUploadModal.vue'

const props = defineProps({
  graduates: Array,
  programs: Array,
  years: Array,
  filter: String
})

const isModalOpen = ref(false)
const isBatchUploadModalOpen = ref(false)
const selectedGraduate = ref(null)
const filter = ref(props.filter ?? 'active')

function openAddModal() {
  selectedGraduate.value = null
  isModalOpen.value = true
}

function editGraduate(grad) {
  selectedGraduate.value = { ...grad }
  isModalOpen.value = true
}

function closeModal() {
  isModalOpen.value = false
  selectedGraduate.value = null
}

function deleteGraduate(id) {
  if (confirm('Are you sure you want to archive this graduate?')) {
    router.delete(route('graduates.destroy', { graduate: id }))
  }
}

function applyFilter() {
  router.get(route('graduates.index'), { status: filter.value }, { preserveState: true })
}

function openBatchUploadModal() {
  isBatchUploadModalOpen.value = true
}
</script>
