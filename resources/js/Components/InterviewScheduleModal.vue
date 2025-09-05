<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-lg shadow w-full max-w-md p-6">
      <h3 class="text-lg font-semibold mb-4">Schedule Interview</h3>
      <div class="space-y-3 text-sm">
        <div>
          <label class="text-xs font-medium">Interview Type</label>
          <select v-model="type" class="mt-1 w-full border rounded px-2 py-1 text-sm">
            <option value="in-person">In-Person</option>
            <option value="online">Online</option>
            <option value="phone">Phone</option>
          </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-xs font-medium">Date</label>
            <input type="date" v-model="date" :min="todayDate" class="mt-1 w-full border rounded px-2 py-1 text-sm">
          </div>
            <div>
            <label class="text-xs font-medium">Time</label>
            <input type="time" v-model="time" :min="date === todayDate ? minTime : null" class="mt-1 w-full border rounded px-2 py-1 text-sm">
          </div>
        </div>
        <div>
          <label class="text-xs font-medium">Location / Link</label>
          <input v-model="location" class="mt-1 w-full border rounded px-2 py-1 text-sm">
        </div>
        <div>
          <label class="text-xs font-medium">Message</label>
          <textarea v-model="message" rows="3" class="mt-1 w-full border rounded px-2 py-1 text-sm"></textarea>
        </div>
      </div>
      <div v-if="error" class="mt-3 text-xs text-red-600">{{ error }}</div>
      <div class="mt-6 flex justify-end gap-2">
        <button @click="$emit('close')" class="px-3 py-1.5 text-xs border rounded">Cancel</button>
        <button :disabled="sending" @click="submit" class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded disabled:opacity-60">
          {{ sending ? 'Sending...' : 'Send' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { ref, watch, computed } from 'vue'

const props = defineProps({
  show: Boolean,
  applicationId: { type: Number, required: true },
  receiverId: { type: Number, required: true },
  applicantName: { type: String, default: '' }
})
const emit = defineEmits(['close','sent'])

const type = ref('in-person')
const date = ref('')
const time = ref('')
const location = ref('')
const message = ref('')
const error = ref(null)
const sending = ref(false)

const todayDate = computed(() => new Date().toISOString().slice(0,10))
const minTime = ref((() => {
  const d = new Date()
  return `${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`
})())

let intervalId = null
watch(() => props.show, v => {
  if (v) {
    message.value = `Dear ${props.applicantName}, we would like to invite you to an interview on [date/time]. Please confirm your availability.`
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
  if (!date.value || !time.value) return 'Date and time required.'
  const chosen = new Date(`${date.value}T${time.value}:00`)
  if (chosen.getTime() < Date.now() - 1000 * 30) return 'Selected date/time is in the past.'
  return null
}

async function submit() {
  if (sending.value) return
  error.value = null
  const vErr = validate()
  if (vErr) { error.value = vErr; return }
  sending.value = true
  try {
    await axios.post(route('interview.invite'), {
      application_id: props.applicationId,
      receiver_id: props.receiverId,
      interview_type: type.value,
      date: date.value,
      time: time.value,
      location: location.value,
      link: location.value,
      message: message.value
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