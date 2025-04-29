<template>
  <div>
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Manage Programs</h3>
      <button @click="openModal" class="btn-primary">Add Program</button>
    </div>

    <table class="w-full border rounded text-sm bg-white shadow-sm">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2">Program Name</th>
          <th class="px-4 py-2">Degree</th>
          <th class="px-4 py-2 text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="program in programs" :key="program.id" class="border-t hover:bg-gray-50">
          <td class="px-4 py-2">{{ program.name }}</td>
          <td class="px-4 py-2">{{ degreeName(program.degree_id) }}</td>
          <td class="px-4 py-2 text-right">
            <button @click="edit(program)" class="text-blue-600 hover:underline">Edit</button>
            <button @click="archive(program.id)" class="text-red-500 hover:underline">Archive</button>
          </td>
        </tr>
      </tbody>
    </table>

    <ProgramModal
      :show="modalOpen"
      :form="form"
      :editing="editing"
      :degrees="degrees"
      :errors="errors"
      @close="closeModal"
      @submit="submitForm"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import ProgramModal from './ProgramModal.vue'

const props = defineProps({ programs: Array, degrees: Array })

const modalOpen = ref(false)
const editing = ref(false)
const currentId = ref(null)
const form = ref({ name: '', degree_id: '' })
const errors = ref({})

const openModal = () => {
  editing.value = false
  form.value = { name: '', degree_id: '' }
  modalOpen.value = true
}

const closeModal = () => {
  modalOpen.value = false
  errors.value = {}
}

const edit = (program) => {
  editing.value = true
  currentId.value = program.id
  form.value = { name: program.name, degree_id: program.degree_id }
  modalOpen.value = true
}

const archive = (id) => {
  if (confirm('Are you sure you want to archive this program?')) {
    router.delete(route('programs.destroy', { program: id }))
  }
}

const submitForm = () => {
  const method = editing.value ? 'patch' : 'post'
  const url = editing.value
    ? route('programs.update', { program: currentId.value })
    : route('programs.store')

  router[method](url, form.value, {
    onSuccess: closeModal,
    onError: (e) => (errors.value = e)
  })
}

const degreeName = (id) => {
  const deg = props.degrees.find((d) => d.id === id)
  return deg ? deg.name : 'Unknown'
}
</script>

<style scoped>
.btn-primary {
  @apply bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700;
}
</style>
