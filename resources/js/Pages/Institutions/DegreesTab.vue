<template>
    <div>
      <!-- Header -->
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Manage Degrees</h3>
        <div class="flex items-center gap-2">
          <select v-model="filter" @change="applyFilter" class="border px-3 py-1 rounded text-sm">
            <option value="active">Active</option>
            <option value="inactive">Archived</option>
          </select>
          <button @click="openModal" class="btn-primary">Add Degree</button>
        </div>
      </div>
  
      <!-- Table -->
      <table class="w-full border rounded text-sm bg-white shadow-sm">
        <thead class="bg-gray-100 text-left">
          <tr>
            <th class="px-4 py-2">Degree Name</th>
            <th class="px-4 py-2">Type</th>
            <th class="px-4 py-2 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="deg in degrees" :key="deg.id" class="border-t hover:bg-gray-50">
            <td class="px-4 py-2">{{ deg.name }}</td>
            <td class="px-4 py-2 capitalize">{{ deg.type }}</td>
            <td class="px-4 py-2 text-right space-x-2">
              <button @click="edit(deg)" class="text-blue-600 hover:underline">Edit</button>
              <button @click="archive(deg.id)" class="text-red-500 hover:underline">Archive</button>
            </td>
          </tr>
        </tbody>
      </table>
  
      <!-- Modal -->
      <DegreeModal
        :show="modalOpen"
        :editing="editing"
        :form="form"
        :errors="errors"
        @close="closeModal"
        @submit="submitForm"
      />
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  import { router } from '@inertiajs/vue3'
  import DegreeModal from './DegreeModal.vue'
  
  const props = defineProps({ degrees: Array, filter: String })
  
  const modalOpen = ref(false)
  const editing = ref(false)
  const currentId = ref(null)
  const filter = ref(props.filter || 'active')
  
  const form = ref({
    name: '',
    type: 'bachelor'
  })
  
  const errors = ref({})
  
  const openModal = () => {
    editing.value = false
    form.value = { name: '', type: 'bachelor' }
    errors.value = {}
    modalOpen.value = true
  }
  
  const closeModal = () => {
    modalOpen.value = false
    errors.value = {}
  }
  
  const edit = (deg) => {
    editing.value = true
    currentId.value = deg.id
    form.value = {
      name: deg.name,
      type: deg.type
    }
    modalOpen.value = true
  }
  
  const archive = (id) => {
    if (confirm('Are you sure you want to archive this degree?')) {
      router.delete(route('degrees.destroy', { degree: id }))
    }
  }
  
  const submitForm = () => {
    const method = editing.value ? 'patch' : 'post'
    const url = editing.value
      ? route('degrees.update', { degree: currentId.value })
      : route('degrees.store')
  
    router[method](url, form.value, {
      onSuccess: () => closeModal(),
      onError: (err) => {
        errors.value = err
      }
    })
  }
  
  const applyFilter = () => {
    router.get(route('degrees.index'), {
      status: filter.value
    }, { preserveState: true })
  }
  </script>
  
  <style scoped>
  .btn-primary {
    @apply bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700;
  }
  </style>
  