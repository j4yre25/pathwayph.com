<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: Boolean,
  applicationId: { type: Number, required: true },
  applicantName: { type: String, required: true },
  actionKey: { type: String, default: 'reject' } // 'reject' | 'reject_withdraw'
})
const emit = defineEmits(['close','rejected'])

const loading = ref(false)
const error = ref(null)
const note = ref('')

// Reset note/error when reopened
watch(() => props.show, v => {
  if (v) {
    error.value = null
    note.value = ''
  }
})

const isWithdraw = computed(() => props.actionKey === 'reject_withdraw')
const title = computed(() => isWithdraw.value ? 'Confirm Withdraw / Reject' : 'Confirm Rejection')
const bodyText = computed(() =>
  isWithdraw.value
    ? 'You are about to withdraw and reject'
    : 'You are about to reject'
)
const buttonLabel = computed(() =>
  loading.value
    ? (isWithdraw.value ? 'Processing...' : 'Rejecting...')
    : (isWithdraw.value ? 'Confirm Withdraw / Reject' : 'Confirm Reject')
)

async function confirm() {
  if (loading.value) return
  loading.value = true
  error.value = null
  try {
    const { data } = await axios.post(
      route('applications.actions.perform', props.applicationId),
      { action: props.actionKey, note: note.value || null }
    )
    emit('rejected', data.stage || 'rejected')
    emit('close')
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to process.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-lg shadow w-full max-w-md p-6">
      <h3 class="text-lg font-semibold mb-2 flex items-center gap-2">
        <i class="fas fa-exclamation-triangle text-red-500"></i>
        {{ title }}
      </h3>
      <p class="text-sm text-gray-600 mb-4 leading-relaxed">
        {{ bodyText }} <strong>{{ applicantName }}</strong>. This will move the application to the Rejected stage.
      </p>

      <div class="mb-4">
        <label class="block text-xs font-medium mb-1">Internal Reason (optional)</label>
        <textarea v-model="note" rows="3" class="w-full border rounded px-2 py-1 text-sm"></textarea>
      </div>

      <div v-if="error" class="mb-3 text-xs text-red-600 bg-red-50 border border-red-200 px-3 py-2 rounded">
        {{ error }}
      </div>

      <div class="flex justify-end gap-3">
        <button
          class="px-4 py-2 text-sm rounded border border-gray-300 text-gray-700 hover:bg-gray-100"
          :disabled="loading"
          @click="$emit('close')"
        >Cancel</button>
        <button
          class="px-4 py-2 text-sm rounded bg-red-600 text-white font-semibold hover:bg-red-700 disabled:opacity-60"
          :disabled="loading"
          @click="confirm"
        >{{ buttonLabel }}</button>
      </div>
    </div>
  </div>
</template>
