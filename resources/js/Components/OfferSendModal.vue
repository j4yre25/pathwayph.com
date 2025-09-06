<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-lg shadow w-full max-w-lg p-6">
      <h3 class="text-lg font-semibold mb-4">Send Offer</h3>
      <div class="grid gap-4 text-sm">
        <div>
          <label class="text-xs font-medium">Job Title</label>
          <input v-model="jobTitle" class="mt-1 w-full border rounded px-2 py-1 text-sm">
        </div>
        <div>
          <label class="text-xs font-medium">Start Date</label>
          <input type="date" v-model="startDate" :min="today" class="mt-1 w-full border rounded px-2 py-1 text-sm">
        </div>
        <div>
          <label class="text-xs font-medium">Offer Letter (PDF optional)</label>
          <input type="file" accept="application/pdf" @change="onFile">
          <div v-if="fileName" class="text-[11px] text-gray-500 mt-1">Selected: {{ fileName }}</div>
        </div>
        <div>
          <label class="text-xs font-medium">Message</label>
          <textarea v-model="message" rows="5" class="mt-1 w-full border rounded px-2 py-1 text-sm"></textarea>
        </div>
      </div>
      <div v-if="error" class="mt-3 text-xs text-red-600">{{ error }}</div>
      <div class="mt-5 flex justify-end gap-2">
        <button @click="$emit('close')" class="px-3 py-1.5 text-xs border rounded">Cancel</button>
        <button :disabled="sending" @click="submit"
          class="px-4 py-1.5 text-xs bg-indigo-600 text-white rounded disabled:opacity-60">
          {{ sending ? 'Sending...' : 'Send Offer' }}
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
  jobTitleDefault: { type: String, default: '' },
})
const emit = defineEmits(['close','sent'])

const jobTitle = ref('')
const startDate = ref('')
const message = ref('')
const sending = ref(false)
const error = ref(null)
const file = ref(null)
const fileName = ref('')
const today = computed(()=> new Date().toISOString().slice(0,10))

watch(()=>props.show, v => {
  if (v) {
    jobTitle.value = props.jobTitleDefault
    message.value = `We are pleased to extend you an offer for the position of ${props.jobTitleDefault || '[Job Title]'}. Please review the details and confirm your acceptance.`
  } else {
    startDate.value = ''
    file.value = null
    fileName.value = ''
  }
})

function onFile(e) {
  const f = e.target.files[0]
  if (f) {
    file.value = f
    fileName.value = f.name
  }
}

async function submit() {
  if (sending.value) return
  error.value = null
  if (!jobTitle.value || !startDate.value || !message.value) {
    error.value = 'Job title, start date, and message are required.'
    return
  }
  sending.value = true
  try {
    const form = new FormData()
    form.append('application_id', props.applicationId)
    form.append('receiver_id', props.receiverId)
    form.append('job_title', jobTitle.value)
    form.append('start_date', startDate.value)
    form.append('message', message.value)
    if (file.value) form.append('file', file.value)
    await axios.post(route('offer.send'), form, {
      headers: { 'Content-Type':'multipart/form-data' }
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