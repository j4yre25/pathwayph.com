<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  applicationId: { type: Number, required: true },
  currentStage:  { type: String, required: true },
  variant:       { type: String, default: 'panel' },
  label:         { type: String, default: 'Stage Actions' },
  transitionsOnly: { type: Boolean, default: false },
  requireHireConfirm: { type: Boolean, default: true },
  alreadyOffered: { type: Boolean, default: false }, // <--- ADDED
})

// ADDED request-more-info & reject to emits; open-modal already present
const emit = defineEmits([
  'open-modal',
  'stage-changed',
  'actions-loaded',
  'error',
  'request-more-info',
  'reject',
  'hire',
  'action-done'          // ADDED
])

const loading = ref(false)
const performing = ref(false)
const actions = ref([])
const open = ref(false)
const error = ref(null)
const dropdownRoot = ref(null)

async function load(force = false) {
  if (loading.value) return
  if (!force && props.variant !== 'panel' && !open.value) return
  loading.value = true
  error.value = null
  try {
    const url = route('applications.actions.index', props.applicationId)
    const { data } = await axios.get(url, { params: { _ts: Date.now() } })
    actions.value = data.actions || []
    emit('actions-loaded', actions.value)
  } catch (e) {
    error.value = 'Failed to load actions'
    emit('error', e)
  } finally {
    loading.value = false
  }
}

async function perform(action) {
  console.debug('Perform clicked', action)
  if (performing.value) return
  if (action.type === 'noop') {
    open.value = false
    return
  }

  // Intercept custom simple action -> opens its own modal (Request More Info)
  if (action.key === 'request_more_info') {
    emit('request-more-info', { action, applicationId: props.applicationId })
    open.value = false
    return
  }

  // Intercept reject (confirmation modal upstream)
  if (action.key === 'reject' || action.key === 'reject_withdraw') {
    emit('reject', {
      action,
      actionKey: action.key,
      applicationId: props.applicationId
    })
    open.value = false
    return
  }

  // Intercept hire action
  if (action.key === 'hire' && props.requireHireConfirm) {
    emit('hire', { action, applicationId: props.applicationId })
    open.value = false
    return
  }

  // NEW: Generic handling for any pipeline action of type "modal"
  // (e.g. schedule_interview, record_test_results, reschedule_test, send_exam_instructions, etc.)
  if (action.type === 'modal') {
    emit('open-modal', {
      action: action.key,
      modal: action.modal || null,
      applicationId: props.applicationId
    })
    open.value = false
    return
  }

  // Standard server actions (transition / action)
  if (['transition','action','dynamic_transition'].includes(action.type)) {
    performing.value = true
    try {
      const { data } = await axios.post(
        route('applications.actions.perform', props.applicationId),
        { action: action.key }
      )
      if (data?.stage) emit('stage-changed', data.stage)
      if (data?.actions) actions.value = data.actions

      // Emit completion (message fallback)
      emit('action-done', {
        key: action.key,
        label: action.label || action.key,
        stage: data?.stage || null,
        message: data?.message || `Action "${action.label || action.key}" completed`
      })
    } catch (e) {
      console.error(e)
      emit('error', e)
    } finally {
      performing.value = false
      open.value = false
    }
  }
}

function toggle() {
  if (!open.value) {
    open.value = true
    load(true)
  } else {
    open.value = false
  }
}

function onClickOutside(e) {
  if (!open.value) return
  const root = dropdownRoot.value
  if (root && !root.contains(e.target)) open.value = false
}

const visibleActions = computed(() => {
  let list = actions.value
  if (props.transitionsOnly) {
    list = list.filter(a => a.type === 'transition' || a.type === 'dynamic_transition')
  }
  if (props.alreadyOffered) {
    list = list.filter(a => a.key !== 'send_offer') // hide send offer if already sent
  }
  return list
})

onMounted(() => {
  document.addEventListener('mousedown', onClickOutside)
  if (props.variant === 'panel') load(true)
})
onBeforeUnmount(() => {
  document.removeEventListener('mousedown', onClickOutside)
})

watch(() => props.currentStage, () => {
  if (props.variant === 'panel') load(true)
  else if (open.value) load(true)
})

const requestInfoOpen = ref(false)
const requestInfoAppId = ref(null)
const requested = ref([]) // ['resume','transcript_of_records','other']
const customMessage = ref('')

function onRequestMoreInfo({ applicationId }) {
  requestInfoAppId.value = applicationId
  requested.value = []
  customMessage.value = ''
  requestInfoOpen.value = true
}

async function sendRequestInfo() {
  await axios.post(route('applications.actions.perform', requestInfoAppId.value), {
    action: 'request_more_info',
    requested: requested.value,
    custom_message: customMessage.value,
  }, { preserveScroll: true, preserveState: true })
  requestInfoOpen.value = false
}
</script>

<template>
  <!-- Panel variant -->
  <div v-if="variant === 'panel'" class="space-y-2" aria-live="polite">
    <div v-if="loading" class="text-xs text-gray-400">Loading actions…</div>
    <div v-if="error" class="text-xs text-red-500">{{ error }}</div>

    <button
      v-for="a in visibleActions"
      :key="a.key"
      type="button"
      :disabled="performing"
      @click="perform(a)"
      class="w-full text-left px-3 py-2 rounded border text-xs flex items-center gap-2 hover:bg-indigo-50 disabled:opacity-50 disabled:cursor-not-allowed"
    >
      <i v-if="a.icon" :class="['fa', a.icon]"></i>
      <span class="flex-1">{{ a.label }}</span>
      <span
        v-if="a.type === 'transition'"
        class="text-[10px] uppercase tracking-wide text-indigo-600 font-semibold"
      >Stage</span>
    </button>

    <div
      v-if="!loading && !visibleActions.length"
      class="text-xs text-gray-400"
    >No actions available.</div>
  </div>

  <!-- Dropdown variant -->
  <div v-else class="relative inline-block" ref="dropdownRoot">
    <button
      type="button"
      @click="toggle"
      :disabled="performing"
      class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-full text-xs font-semibold flex items-center gap-2 disabled:opacity-60"
    >
      <i class="fa fa-cog text-[11px]"></i>
      <span>{{ label }}</span>
      <i :class="['fa', open ? 'fa-chevron-up' : 'fa-chevron-down','text-[10px]']"></i>
    </button>

    <div
      v-if="open"
      class="absolute right-0 mt-2 w-60 z-30 bg-white rounded-md shadow-lg border border-gray-200 py-1"
    >
      <div v-if="loading" class="px-3 py-2 text-xs text-gray-400">Loading...</div>
      <template v-else>
        <div v-if="error" class="px-3 py-2 text-xs text-red-500">{{ error }}</div>

        <button
          v-for="a in visibleActions"
            :key="a.key"
            @click="perform(a)"
            :disabled="performing"
            class="w-full text-left px-3 py-2 text-xs flex items-center gap-2 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <i v-if="a.icon" :class="['fa', a.icon]"></i>
          <span class="flex-1">{{ a.label }}</span>
          <span
            v-if="a.type === 'transition'"
            class="text-[9px] uppercase tracking-wide text-indigo-600 font-semibold"
          >Stage</span>
        </button>

        <div v-if="!visibleActions.length && !error" class="px-3 py-2 text-xs text-gray-400">
          No actions
        </div>
      </template>
    </div>
  </div>

  <!-- Request More Info Modal -->
  <Modal v-model="requestInfoOpen">
    <template #header>Request Additional Information</template>
    <template #body>
      <div class="space-y-3">
        <div class="text-sm text-gray-700">Select items to request:</div>
        <div class="grid grid-cols-2 gap-2 text-sm">
          <label class="flex items-center gap-2"><input type="checkbox" value="resume" v-model="requested" /> Resume</label>
          <label class="flex items-center gap-2"><input type="checkbox" value="transcript_of_records" v-model="requested" /> TOR</label>
          <label class="flex items-center gap-2"><input type="checkbox" value="portfolio" v-model="requested" /> Portfolio</label>
          <label class="flex items-center gap-2"><input type="checkbox" value="certificate_of_employment" v-model="requested" /> Certificate of Employment</label>
          <label class="flex items-center gap-2"><input type="checkbox" value="police_clearance" v-model="requested" /> Police Clearance</label>
          <label class="flex items-center gap-2"><input type="checkbox" value="other" v-model="requested" /> Other</label>
        </div>

        <div>
          <div class="text-sm text-gray-700 mb-1">Optional note:</div>
          <textarea v-model="customMessage" rows="3" class="w-full border rounded p-2 text-sm" placeholder="Add a note to the applicant..."></textarea>
        </div>
      </div>
    </template>
    <template #footer>
      <button class="px-3 py-2 text-sm" @click="requestInfoOpen = false">Cancel</button>
      <button class="px-3 py-2 text-sm bg-indigo-600 text-white rounded" @click="sendRequestInfo">Send Request</button>
    </template>
  </Modal>
</template>