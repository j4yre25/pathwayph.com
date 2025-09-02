<script setup>
import axios from 'axios'
import { ref } from 'vue'

const props = defineProps({
  show: Boolean,
  applicationId: { type: Number, required: true },
  receiverId: { type: Number, required: true }
})
const emit = defineEmits(['close','sent'])

const examType = ref('')
const date = ref('')
const time = ref('')
const reason = ref('')
const error = ref(null)
const sending = ref(false)

async function submit() {
  if (sending.value) return
  error.value = null
  if (!examType.value || !date.value || !time.value) {
    error.value = 'Exam type, new date & time required.'
    return
  }
  sending.value = true
  try {
    await axios.post(route('assessment.reschedule'), {
      application_id: props.applicationId,
      receiver_id: props.receiverId,
      exam_type: examType.value,
      new_date: date.value,
      new_time: time.value,
      reason: reason.value || null
    })
    emit('sent')
    emit('close')
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed.'
  } finally {
    sending.value = false
  }
}
</script>
<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-lg shadow w-full max-w-sm p-5">
      <h3 class="text-lg font-semibold mb-4">Reschedule Exam</h3>
      <div class="space-y-3 text-sm">
        <div>
          <label class="font-medium text-xs">Exam Type</label>
          <input v-model="examType" class="mt-1 w-full border rounded px-2 py-1 text-sm">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="font-medium text-xs">New Date</label>
            <input type="date" v-model="date" class="mt-1 w-full border rounded px-2 py-1 text-sm">
          </div>
          <div>
            <label class="font-medium text-xs">New Time</label>
            <input type="time" v-model="time" class="mt-1 w-full border rounded px-2 py-1 text-sm">
          </div>
        </div>
        <div>
          <label class="font-medium text-xs">Reason</label>
            <textarea v-model="reason" rows="3" class="mt-1 w-full border rounded px-2 py-1 text-sm"></textarea>
        </div>
      </div>
      <div v-if="error" class="mt-3 text-xs text-red-600">{{ error }}</div>
      <div class="mt-5 flex justify-end gap-2">
        <button @click="$emit('close')" class="px-3 py-1.5 text-xs border rounded">Cancel</button>
        <button :disabled="sending" @click="submit"
          class="px-3 py-1.5 text-xs rounded bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-60">
          {{ sending ? 'Sending...' : 'Send' }}
        </button>
      </div>
    </div>
  </div>
</template>
