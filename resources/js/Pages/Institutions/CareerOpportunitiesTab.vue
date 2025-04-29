<template>
  <div>
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold">Manage Career Opportunities</h3>
      <button @click="openModal" class="btn-primary">Add Career</button>
    </div>

    <table class="w-full border rounded text-sm bg-white shadow-sm">
      <thead class="bg-gray-100 text-left">
        <tr>
          <th class="px-4 py-2">Title</th>
          <th class="px-4 py-2 text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="career in careerOpportunities" :key="career.id" class="border-t hover:bg-gray-50">
          <td class="px-4 py-2">{{ career.title }}</td>
          <td class="px-4 py-2 text-right">
            <button @click="edit(career)" class="text-blue-600 hover:underline">Edit</button>
            <button @click="archive(career.id)" class="text-red-500 hover:underline">Archive</button>
          </td>
        </tr>
      </tbody>
    </table>

    <CareerOpportunityModal
      :show="modalOpen"
      :form="form"
      :editing="editing"
      :errors="errors"
      @close="closeModal"
      @submit="submitForm"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import CareerOpportunityModal from './CareerOpportunityModal.vue'

const props = defineProps({ careerOpportunities: Array })

const modalOpen = ref(false)
const editing = ref(false)
const currentId = ref(null)
const form = ref({ title: '' })
const errors = ref({})

const openModal = () => {
  editing.value = false
  form.value = { title: '' }
  modalOpen.value = true
}

const closeModal = () => {
  modalOpen.value = false
  errors.value = {}
}

const edit = (career) => {
  editing.value = true
  currentId.value = career.id
  form.value = { title: career.title }
  modalOpen.value = true
}

const archive = (id) => {
  if (confirm('Are you sure you want to archive this career opportunity?')) {
    router.delete(route('career-opportunities.destroy', { careerOpportunity: id }))
  }
}

const submitForm = () => {
  const method = editing.value ? 'patch' : 'post'
  const url = editing.value
    ? route('career-opportunities.update', { careerOpportunity: currentId.value })
    : route('career-opportunities.store')

  router[method](url, form.value, {
    onSuccess: closeModal,
    onError: (e) => (errors.value = e)
  })
}
</script>

<style scoped>
.btn-primary {
  @apply bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700;
}
</style>
