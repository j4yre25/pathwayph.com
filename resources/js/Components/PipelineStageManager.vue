<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import draggable from 'vuedraggable'

const LOCKED_SLUGS = ['applied','offer','hired','rejected']

const stages = ref([])               // full ordered list from backend
const reorderableStages = ref([])    // subset user can drag
const loading = ref(false)
const saving = ref(false)
const message = ref(null)
const error = ref(null)
const lastStatus = ref(null)

function splitReorderable() {
  reorderableStages.value = stages.value.filter(
    s => !LOCKED_SLUGS.includes(s.slug.toLowerCase())
  )
}

async function load() {
  loading.value = true
  error.value = null
  message.value = null
  lastStatus.value = null
  try {
    const url = (typeof route === 'function' && route().current)
      ? route('pipeline.stages.index')
      : '/pipeline-stages'
    const { data, status } = await axios.get(url)
    lastStatus.value = status
    stages.value = Array.isArray(data) ? data : []
    if (!stages.value.length) {
      message.value = 'No stages available (empty result).'
    }
    splitReorderable()
  } catch (e) {
    lastStatus.value = e.response?.status
    error.value = 'Failed to load stages'
    console.error('PipelineStageManager load error', {
      status: e.response?.status,
      data: e.response?.data,
      url: e.config?.url
    })
  } finally {
    loading.value = false
  }
}

async function save() {
  saving.value = true
  message.value = null
  try {
    // Reconstruct final order: Applied + reordered middle + (Offer,Hired,Rejected)
    const bySlug = Object.fromEntries(stages.value.map(s => [s.slug.toLowerCase(), s]))
    const finalOrder = []

    // Applied (if present)
    if (bySlug.applied) finalOrder.push(bySlug.applied)

    // Reordered middle stages
    reorderableStages.value.forEach(s => finalOrder.push(s))

    // Tail locked in fixed sequence
    ;['offer','hired','rejected'].forEach(sl => {
      if (bySlug[sl]) finalOrder.push(bySlug[sl])
    })

    // Safety: if anything else existed (unexpected), append in original order excluding already added
    stages.value.forEach(s => {
      if (!finalOrder.find(x => x.id === s.id)) finalOrder.push(s)
    })

    const url = (typeof route === 'function' && route().current)
      ? route('pipeline.stages.reorder')
      : '/pipeline-stages/reorder'

    await axios.put(url, {
      order: finalOrder.map(s => ({ id: s.id }))
    })

    // Replace local master list with new order
    stages.value = finalOrder
    splitReorderable()
    message.value = 'Order saved'
  } catch (e) {
    console.error('PipelineStageManager save error', e.response?.status, e.response?.data)
    message.value = 'Save failed'
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<template>
  <div>
    <p class="text-[11px] text-gray-500 mb-2">
      Drag to reorder only the middle stages. Boundary stages are fixed.
      <span v-if="lastStatus" class="text-gray-400">(HTTP {{ lastStatus }})</span>
    </p>

    <div v-if="loading" class="text-xs text-gray-400">Loading...</div>
    <div v-else-if="error" class="text-xs text-red-500">{{ error }}</div>

    <!-- Fixed first (Applied) -->
    <div v-if="!loading && !error" class="space-y-2 mb-3">
      <template v-for="s in stages.filter(st => st.slug.toLowerCase()==='applied')" :key="s.id">
        <div class="flex items-center justify-between px-3 py-2 border rounded bg-gray-100">
          <div class="flex items-center gap-2">
            <i class="fas fa-lock text-gray-400 text-xs"></i>
            <span class="text-sm font-medium">{{ s.name }}</span>
          </div>
          <span class="text-[10px] uppercase tracking-wide text-gray-500">{{ s.slug }}</span>
        </div>
      </template>
    </div>

    <!-- Draggable middle stages -->
    <draggable
      v-if="!loading && !error"
      v-model="reorderableStages"
      item-key="id"
      handle=".handle"
      class="space-y-2 mb-3"
    >
      <template #item="{ element }">
        <div class="flex items-center justify-between px-3 py-2 border rounded bg-indigo-50">
          <div class="flex items-center gap-2">
            <span class="cursor-grab handle text-gray-400">
              <i class="fas fa-grip-vertical"></i>
            </span>
            <span class="text-sm font-medium">{{ element.name }}</span>
          </div>
          <span class="text-[10px] uppercase tracking-wide text-gray-500">{{ element.slug }}</span>
        </div>
      </template>
    </draggable>

    <!-- Fixed tail (Offer, Hired, Rejected) -->
    <div v-if="!loading && !error" class="space-y-2 mb-4">
      <template
        v-for="s in stages.filter(st => ['offer','hired','rejected'].includes(st.slug.toLowerCase()))"
        :key="s.id"
      >
        <div class="flex items-center justify-between px-3 py-2 border rounded bg-gray-100">
          <div class="flex items-center gap-2">
            <i class="fas fa-lock text-gray-400 text-xs"></i>
            <span class="text-sm font-medium">{{ s.name }}</span>
          </div>
          <span class="text-[10px] uppercase tracking-wide text-gray-500">{{ s.slug }}</span>
        </div>
      </template>
    </div>

    <div class="mt-2 flex gap-2">
      <button
        @click="save"
        :disabled="saving || loading || !reorderableStages.length"
        class="px-3 py-2 text-xs font-semibold rounded bg-indigo-600 text-white disabled:opacity-50"
      >
        {{ saving ? 'Saving...' : 'Save Order' }}
      </button>
      <button
        @click="load"
        :disabled="saving || loading"
        class="px-3 py-2 text-xs font-semibold rounded bg-gray-200 disabled:opacity-50"
      >
        Reload
      </button>
    </div>
    <p v-if="message" class="text-xs mt-2 text-gray-600">{{ message }}</p>
  </div>
</template>