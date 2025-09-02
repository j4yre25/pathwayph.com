<script setup>
import axios from 'axios'
import { ref } from 'vue'

const props = defineProps({
  show: Boolean,
  applicationId: { type: Number, required: true },
  receiverId: { type: Number, required: true },
})
const emit = defineEmits(['close','sent'])

const examType = ref('')
const date = ref('')
const time = ref('')
const venue = ref('')
const link = ref('')
const requirements = ref('')
const notes = ref('')
const error = ref(null)
const sending = ref(false)

async function submit() {
  if (sending.value) return
  error.value = null
  if (!examType.value || !date.value || !time.value) {
    error.value = 'Exam type, date and time are required.'
    return
  }
  sending.value = true
  try {
    await axios.post(route('assessment.instructions.send'), {
      application_id: props.applicationId,
      receiver_id: props.receiverId,
      exam_type: examType.value,
      schedule_date: date.value,
      schedule_time: time.value,
      venue: venue.value || null,
      link: link.value || null,
      requirements: requirements.value || null,
      notes: notes.value || null
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
    <div class="bg-white rounded-lg shadow w-full max-w-md p-5">
      <h3 class="text-lg font-semibold mb-4">Send Exam Instructions</h3>
      <div class="space-y-3 text-sm">
        <div>
          <label class="font-medium text-xs">Exam Type</label>
          <input v-model="examType" class="mt-1 w-full border rounded px-2 py-1 text-sm">
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="font-medium text-xs">Date</label>
            <input type="date" v-model="date" class="mt-1 w-full border rounded px-2 py-1 text-sm">
          </div>
            <div>
            <label class="font-medium text-xs">Time</label>
            <input type="time" v-model="time" class="mt-1 w-full border rounded px-2 py-1 text-sm">
          </div>
        </div>
        <div>
          <label class="font-medium text-xs">Venue (optional)</label>
          <input v-model="venue" class="mt-1 w-full border rounded px-2 py-1 text-sm">
        </div>
        <div>
          <label class="font-medium text-xs">Link (optional)</label>
          <input v-model="link" class="mt-1 w-full border rounded px-2 py-1 text-sm">
        </div>
        <div>
          <label class="font-medium text-xs">Requirements</label>
          <textarea v-model="requirements" rows="2" class="mt-1 w-full border rounded px-2 py-1 text-sm"></textarea>
        </div>
        <div>
          <label class="font-medium text-xs">Notes</label>
          <textarea v-model="notes" rows="2" class="mt-1 w-full border rounded px-2 py-1 text-sm"></textarea>
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
