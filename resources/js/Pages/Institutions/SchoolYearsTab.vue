<template>
  <div>
    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Manage School Years</h3>
      <div class="flex items-center gap-2">
        <select v-model="filter" @change="applyFilter" class="border px-3 py-1 rounded text-sm">
          <option value="active">Active</option>
          <option value="inactive">Archived</option>
        </select>
        <button @click="openModal" class="btn-primary">Add School Year</button>
      </div>
    </div>

    <!-- Table -->
    <table class="w-full border rounded text-sm bg-white shadow-sm">
      <thead class="bg-gray-100 text-left">
        <tr>
          <th class="px-4 py-2">School Year</th>
          <th class="px-4 py-2">Term</th>
          <th class="px-4 py-2 text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="sy in schoolYears"
          :key="sy.id"
          class="border-t hover:bg-gray-50"
        >
          <td class="px-4 py-2">{{ sy.school_year_range }}</td>
          <td class="px-4 py-2">{{ sy.term }}</td>
          <td class="px-4 py-2 text-right space-x-2">
            <button @click="edit(sy)" class="text-blue-600 hover:underline">Edit</button>
            <button @click="archive(sy.id)" class="text-red-500 hover:underline">Archive</button>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Modal -->
    <SchoolYearModal
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
import SchoolYearModal from './SchoolYearModal.vue' // Adjust if path differs

const props = defineProps({
  schoolYears: Array,
  filter: String
})

const modalOpen = ref(false)
const editing = ref(false)
const currentId = ref(null)
const filter = ref(props.filter || 'active')

const form = ref({
  school_year_range: '',
  term: ''
})

const errors = ref({})

// Open modal for adding
const openModal = () => {
  editing.value = false
  form.value = { school_year_range: '', term: '' }
  errors.value = {}
  modalOpen.value = true
}

// Edit an entry
const edit = (sy) => {
  editing.value = true
  currentId.value = sy.id
  form.value = {
    school_year_range: sy.school_year_range,
    term: sy.term
  }
  errors.value = {}
  modalOpen.value = true
}

// Close modal
const closeModal = () => {
  modalOpen.value = false
  errors.value = {}
}

// Archive
const archive = (id) => {
  if (confirm('Are you sure you want to archive this entry?')) {
    router.delete(route('school-years.destroy', { schoolYear: id }))
  }
}

// Submit form
const submitForm = () => {
  const method = editing.value ? 'patch' : 'post'
  const url = editing.value
    ? route('school-years.update', { schoolYear: currentId.value })
    : route('school-years.store')

  router[method](url, form.value, {
    onSuccess: () => closeModal(),
    onError: (err) => {
      errors.value = err
    }
  })
}

// Filter change
const applyFilter = () => {
  router.get(route('school-years'), {
    status: filter.value
  }, { preserveState: true })
}
</script>

<style scoped>
.btn-primary {
  @apply bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700;
}
</style>
