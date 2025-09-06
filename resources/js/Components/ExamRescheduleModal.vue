<script setup>
import axios from 'axios'
import { ref, watch, computed } from 'vue'

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

const todayDate = computed(() => new Date().toISOString().slice(0,10))
const minTime = ref((() => {
  const d = new Date()
  return `${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`
})())

let intervalId = null
watch(() => props.show, (v) => {
  if (v) {
    intervalId = setInterval(() => {
      if (date.value === todayDate.value) {
        const d = new Date()
        minTime.value = `${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`
      }
    }, 60000)
  } else if (intervalId) {
    clearInterval(intervalId)
  }
})

watch(date, (nv) => {
  if (nv && nv < todayDate.value) date.value = todayDate.value
  if (date.value === todayDate.value && time.value && time.value < minTime.value) {
    time.value = minTime.value
  }
})
watch(time, (nv) => {
  if (!nv) return
  if (date.value === todayDate.value && nv < minTime.value) {
    time.value = minTime.value
  }
})

function validate() {
  if (!examType.value || !date.value || !time.value) return 'Exam type, new date & time required.'
  const chosen = new Date(`${date.value}T${time.value}:00`)
  if (chosen.getTime() < Date.now() - 1000 * 30) return 'Selected date/time is in the past.'
  return null
}

async function submit() {
  if (sending.value) return
  error.value = null
  const vErr = validate()
  if (vErr) {
    error.value = vErr
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
            <input type="date" v-model="date" :min="todayDate" class="mt-1 w-full border rounded px-2 py-1 text-sm">
          </div>
          <div>
            <label class="font-medium text-xs">New Time</label>
            <input type="time" v-model="time" :min="date === todayDate ? minTime : null" class="mt-1 w-full border rounded px-2 py-1 text-sm">
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
        <button :disabled="sending" @click="submit" class="px-3 py-1.5 text-xs rounded bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-60">
          {{ sending ? 'Sending...' : 'Send' }}
        </button>
      </div>
    </div>
  </div>
</template>
