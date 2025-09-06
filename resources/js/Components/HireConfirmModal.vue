<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-lg shadow w-full max-w-md p-6">
      <h3 class="text-lg font-semibold mb-2">Confirm Hire</h3>
      <p class="text-sm text-gray-600 mb-4">
        Are you sure you want to mark <strong>{{ applicantName }}</strong> as Hired?
      </p>
      <div>
        <label class="block text-xs font-medium mb-1">Internal Note (optional)</label>
        <textarea v-model="note" rows="3" class="w-full border rounded px-2 py-1 text-sm"></textarea>
      </div>
      <div v-if="error" class="mt-3 text-xs text-red-600">{{ error }}</div>
      <div class="mt-5 flex justify-end gap-2">
        <button @click="$emit('close')" class="px-3 py-1.5 text-xs border rounded">Cancel</button>
        <button :disabled="loading" @click="confirm"
          class="px-3 py-1.5 text-xs bg-green-600 text-white rounded disabled:opacity-60">
          {{ loading ? 'Saving...' : 'Confirm Hire' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { ref } from 'vue'

const props = defineProps({
  show: Boolean,
  applicationId: { type: Number, required: true },
  applicantName: { type: String, required: true }
})
const emit = defineEmits(['close','hired'])
const note = ref('')
const loading = ref(false)
const error = ref(null)

async function confirm() {
  if (loading.value) return
  loading.value = true
  error.value = null
  try {
    const { data } = await axios.post(
      route('applications.actions.perform', props.applicationId),
      { action: 'hire', note: note.value || null }
    )
    emit('hired', data.stage)
    emit('close')
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to hire.'
  } finally {
    loading.value = false
  }
}
</script>