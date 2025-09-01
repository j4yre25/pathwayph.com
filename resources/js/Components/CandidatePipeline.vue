<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  stage: { type: String, required: true },
  // Optional: pass pre-fetched ordered stages to skip API call
  orderedStages: { type: Array, default: () => [] },
})

// Variant slugs normalization
const variantMap = {
  applying: 'applied',
  applied: 'applied',
  screened: 'screening',
  screening: 'screening',
  testing: 'assessment',
  test: 'assessment',
  assessment: 'assessment',
  exam: 'assessment',
  interview: 'interview',
  'final interview': 'interview',
  final_interview: 'interview',
  offer: 'offer',
  offered: 'offer',
  hired: 'hired',
  onboarding: 'hired',
  onboarded: 'hired',
  rejected: 'rejected',
  decline: 'rejected',
}

// Static default fallback (used if API fails)
const defaultFallback = [
  { slug: 'applied',    name: 'Applied' },
  { slug: 'screening',  name: 'Screening' },
  { slug: 'assessment', name: 'Assessment / Exam' },
  { slug: 'interview',  name: 'Interview' },
  { slug: 'offer',      name: 'Offer' },
  { slug: 'hired',      name: 'Hired' },
  { slug: 'rejected',   name: 'Rejected' },
]

// Past tense overrides (else reuse name)
const pastLabelMap = {
  applied: 'Applied',
  screening: 'Screened',
  assessment: 'Assessed',
  interview: 'Interviewed',
  offer: 'Offered',
  hired: 'Hired',
  rejected: 'Rejected',
}

const loading = ref(false)
const error = ref(null)
const stages = ref([])

function buildStageObjects(rows) {
  return rows.map(r => {
    const slug = r.slug.toLowerCase()
    return {
      slug,
      liveLabel: r.name,
      pastLabel: pastLabelMap[slug] || r.name,
      position: r.position ?? 0,
      terminal: !!r.is_terminal,
    }
  })
}

async function load() {
  if (props.orderedStages.length) {
    stages.value = buildStageObjects(props.orderedStages)
    return
  }
  loading.value = true
  error.value = null
  try {
    // Use Ziggy route if available else fallback literal
    const url = (typeof route === 'function' && route().has && route().has('pipeline.stages.index'))
      ? route('pipeline.stages.index')
      : '/pipeline-stages'
    const { data } = await axios.get(url)
    if (Array.isArray(data) && data.length) {
      stages.value = buildStageObjects(data)
    } else {
      stages.value = buildStageObjects(defaultFallback)
    }
  } catch (e) {
    error.value = 'Failed to load pipeline'
    stages.value = buildStageObjects(defaultFallback)
    console.error('CandidatePipeline load error', e)
  } finally {
    loading.value = false
  }
}

const normalizedSlug = computed(() => {
  const raw = props.stage?.toString()?.toLowerCase() || 'applied'
  return variantMap[raw] || raw
})

const currentIndex = computed(() => {
  return stages.value.findIndex(s => s.slug === normalizedSlug.value)
})

watch(() => props.orderedStages, (nv) => {
  if (nv && nv.length) {
    stages.value = buildStageObjects(nv)
  }
}, { deep: true })

onMounted(load)
</script>

<template>
  <div class="w-full">
    <div v-if="loading" class="text-[11px] text-gray-400">Loading pipeline…</div>
    <div v-else class="flex w-full">
      <template v-for="(s, i) in stages" :key="s.slug">
        <div class="flex flex-col flex-1 min-w-0">
          <span
            class="text-[11px] tracking-wide mb-1 whitespace-nowrap"
            :class="{
              'font-semibold text-indigo-600': i === currentIndex,
              'text-gray-500': i !== currentIndex
            }"
          >
            {{ i < currentIndex ? s.pastLabel : s.liveLabel }}
          </span>
          <div class="flex items-center">
            <div
              class="h-2 w-2 rounded-full shrink-0"
              :class="{
                'bg-indigo-600': i <= currentIndex && normalizedSlug !== 'rejected',
                'bg-red-500': normalizedSlug === 'rejected' && i === currentIndex,
                'bg-gray-300': i > currentIndex
              }"
            ></div>
            <div
              v-if="i < stages.length - 1"
              class="flex-1 h-[3px] ml-1 rounded transition-colors"
              :class="{
                'bg-indigo-500': i < currentIndex && normalizedSlug !== 'rejected',
                'bg-gray-300': i >= currentIndex || normalizedSlug === 'rejected'
              }"
            ></div>
          </div>
        </div>
      </template>
    </div>
    <div v-if="error" class="mt-1 text-[10px] text-red-500">
      {{ error }}
    </div>
  </div>
</template>


