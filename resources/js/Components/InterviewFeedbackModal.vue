<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-lg shadow w-full max-w-md p-6">
      <h3 class="text-lg font-semibold mb-4">Interview Feedback</h3>
      <div class="space-y-3 text-sm">
        <div>
          <label class="text-xs font-medium">Rating (1–5 / pass / fail)</label>
          <input v-model="rating" class="mt-1 w-full border rounded px-2 py-1 text-sm">
        </div>
        <div>
          <label class="text-xs font-medium">Strengths</label>
          <textarea v-model="strengths" rows="2" class="mt-1 w-full border rounded px-2 py-1 text-sm"></textarea>
        </div>
        <div>
          <label class="text-xs font-medium">Weaknesses</label>
          <textarea v-model="weaknesses" rows="2" class="mt-1 w-full border rounded px-2 py-1 text-sm"></textarea>
        </div>
        <div>
          <label class="text-xs font-medium">Recommendation</label>
          <select v-model="recommendation" class="mt-1 w-full border rounded px-2 py-1 text-sm">
            <option value="move_forward">Move Forward</option>
            <option value="reject">Reject</option>
            <option value="hold">Keep on Hold</option>
          </select>
        </div>
      </div>
      <div v-if="error" class="mt-3 text-xs text-red-600">{{ error }}</div>
      <div class="mt-6 flex justify-end gap-2">
        <button @click="$emit('close')" class="px-3 py-1.5 text-xs border rounded">Cancel</button>
        <button :disabled="saving" @click="save" class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded disabled:opacity-60">
          {{ saving ? 'Saving...' : 'Save' }}
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
  applicationId: { type: Number, required: true }
})
const emit = defineEmits(['close','saved'])

const rating = ref('')
const strengths = ref('')
const weaknesses = ref('')
const recommendation = ref('move_forward')
const error = ref(null)
const saving = ref(false)

async function save() {
  if (saving.value) return
  error.value = null
  saving.value = true
  try {
    await axios.post(route('interview.feedback'), {
      application_id: props.applicationId,
      rating: rating.value || null,
      strengths: strengths.value || null,
      weaknesses: weaknesses.value || null,
      recommendation: recommendation.value
    })
    emit('saved')
    emit('close')
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed.'
  } finally {
    saving.value = false
  }
}
</script>