<script setup>
import axios from 'axios'
import { ref } from 'vue'

const props = defineProps({
  show: Boolean,
  applicationId: { type: Number, required: true }
})
const emit = defineEmits(['close','saved'])

const examType = ref('')
const score = ref('')
const status = ref('pending')
const remarks = ref('')
const error = ref(null)
const saving = ref(false)

async function save() {
  if (saving.value) return
  error.value = null
  if (!examType.value) {
    error.value = 'Exam type required.'
    return
  }
  saving.value = true
  try {
    await axios.post(route('assessment.result.record'), {
      application_id: props.applicationId,
      exam_type: examType.value,
      score: score.value || null,
      status: status.value,
      remarks: remarks.value || null
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
<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-lg shadow w-full max-w-sm p-5">
      <h3 class="text-lg font-semibold mb-4">Record Test Result</h3>
      <div class="space-y-3 text-sm">
        <div>
          <label class="font-medium text-xs">Exam Type</label>
          <input v-model="examType" class="mt-1 w-full border rounded px-2 py-1 text-sm">
        </div>
        <div>
          <label class="font-medium text-xs">Score</label>
          <input v-model="score" type="number" step="0.01" class="mt-1 w-full border rounded px-2 py-1 text-sm">
        </div>
        <div>
          <label class="font-medium text-xs">Status</label>
          <select v-model="status" class="mt-1 w-full border rounded px-2 py-1 text-sm">
            <option value="pending">Pending</option>
            <option value="passed">Passed</option>
            <option value="failed">Failed</option>
          </select>
        </div>
        <div>
          <label class="font-medium text-xs">Remarks</label>
          <textarea v-model="remarks" rows="3" class="mt-1 w-full border rounded px-2 py-1 text-sm"></textarea>
        </div>
      </div>
      <div v-if="error" class="mt-3 text-xs text-red-600">{{ error }}</div>
      <div class="mt-5 flex justify-end gap-2">
        <button @click="$emit('close')" class="px-3 py-1.5 text-xs border rounded">Cancel</button>
        <button :disabled="saving" @click="save"
          class="px-3 py-1.5 text-xs rounded bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-60">
          {{ saving ? 'Saving...' : 'Save' }}
        </button>
      </div>
    </div>
  </div>
</template>
