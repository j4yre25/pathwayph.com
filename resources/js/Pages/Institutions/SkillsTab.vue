<template>
    <div>
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Manage Skills</h3>
        <button @click="openModal" class="btn-primary">Add Skill</button>
      </div>
  
      <table class="w-full border rounded text-sm bg-white shadow-sm">
        <thead class="bg-gray-100 text-left">
          <tr>
            <th class="px-4 py-2">Skill Name</th>
            <th class="px-4 py-2 text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="skill in skills" :key="skill.id" class="border-t hover:bg-gray-50">
            <td class="px-4 py-2">{{ skill.name }}</td>
            <td class="px-4 py-2 text-right">
              <button @click="edit(skill)" class="text-blue-600 hover:underline">Edit</button>
              <button @click="archive(skill.id)" class="text-red-500 hover:underline">Archive</button>
            </td>
          </tr>
        </tbody>
      </table>
  
      <SkillModal
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
  import SkillModal from './SkillModal.vue'
  
  const props = defineProps({ skills: Array })
  
  const modalOpen = ref(false)
  const editing = ref(false)
  const currentId = ref(null)
  const form = ref({ name: '' })
  const errors = ref({})
  
  const openModal = () => {
    editing.value = false
    form.value = { name: '' }
    modalOpen.value = true
  }
  
  const closeModal = () => {
    modalOpen.value = false
    errors.value = {}
  }
  
  const edit = (skill) => {
    editing.value = true
    currentId.value = skill.id
    form.value = { name: skill.name }
    modalOpen.value = true
  }
  
  const archive = (id) => {
    if (confirm('Are you sure you want to archive this skill?')) {
      router.delete(route('skills.destroy', { skill: id }))
    }
  }
  
  const submitForm = () => {
    const method = editing.value ? 'patch' : 'post'
    const url = editing.value
      ? route('skills.update', { skill: currentId.value })
      : route('skills.store')
  
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
  