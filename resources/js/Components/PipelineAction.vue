<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  applicationId: { type: Number, required: true },
  currentStage:  { type: String, required: true },
  variant:       { type: String, default: 'panel' },   // panel | button
  label:         { type: String, default: 'Stage Actions' },
  transitionsOnly: { type: Boolean, default: false },
})

const emit = defineEmits(['open-modal','stage-changed','actions-loaded','error'])

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
  if (performing.value) return
  if (action.type === 'noop') {
    open.value = false
    return
  }

  if (['transition','action'].includes(action.type)) {
    performing.value = true
    try {
      const { data } = await axios.post(
        route('applications.actions.perform', props.applicationId),
        { action: action.key }
      )
      if (data?.stage) emit('stage-changed', data.stage)
      if (data?.actions) actions.value = data.actions
    } catch (e) {
      error.value = e.response?.data?.error || 'Action failed'
      console.error('Action perform error', e.response?.data || e)
      emit('error', e)
    } finally {
      performing.value = false
      open.value = false
    }
  } else if (action.type === 'modal') {
    emit('open-modal', action)
    open.value = false
  } else if (action.type === 'view') {
    router.get(route('applicants.show', props.applicationId))
    open.value = false
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
  if (props.transitionsOnly) {
    return actions.value.filter(a => a.type === 'transition')
  }
  return actions.value
})

onMounted(() => {
  document.addEventListener('mousedown', onClickOutside)
  if (props.variant === 'panel') load(true)
})
onBeforeUnmount(() => {
  document.removeEventListener('mousedown', onClickOutside)
})

// Reload when stage changes
watch(() => props.currentStage, () => {
  if (props.variant === 'panel') load(true)
  else if (open.value) load(true)
})
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
</template>