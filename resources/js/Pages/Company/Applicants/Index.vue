<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'
import Container from '@/Components/Container.vue'
import ListOfApplicants from './ListOfApplicants.vue'
import PipelineStageManager from '@/Components/PipelineStageManager.vue' // <-- added
import '@fortawesome/fontawesome-free/css/all.css'

const props = defineProps({
  jobs: Array,
  applicants: Array,
  statuses: Array,
  employmentTypes: Array,
  filters: { type: Object, default: () => ({}) },
  stats: {
    type: Object,
    default: () => ({
      this_month: 0,
      this_month_label: '',
      hired: 0,
      rejected: 0,
      interviews: 0,
      total_jobs: 0,
      total_applicants: 0,
    }),
  },
})

const statsDisplay = computed(() => [
  {
    title: 'Total Applicants',
    value: props.stats.total_applicants.toString(),
    period: props.stats.this_month_label,
    icon: 'fas fa-user-friends',
    iconBg: 'bg-[#E9D5FF]',
    iconColor: 'text-[#7C3AED]'
  },
  {
    title: 'Total Hired',
    value: props.stats.hired.toString(),
    period: props.stats.this_month_label,
    icon: 'fas fa-check-circle',
    iconBg: 'bg-[#D1FAE5]',
    iconColor: 'text-[#059669]'
  },
  {
    title: 'Total Rejected',
    value: props.stats.rejected.toString(),
    period: props.stats.this_month_label,
    icon: 'fas fa-times-circle',
    iconBg: 'bg-[#FECACA]',
    iconColor: 'text-[#DC2626]'
  },
  {
    title: 'Interviews Scheduled',
    value: props.stats.interviews.toString(),
    period: props.stats.this_month_label,
    icon: 'fas fa-calendar-alt',
    iconBg: 'bg-[#FEF3C7]',
    iconColor: 'text-[#B45309]'
  },
])

const filters = ref({ ...props.filters, keywords: '' })

// Date range preset
const selectedAppliedDate = ref('')
const customFromDate = ref('')
const customToDate = ref('')

// Derive date_from / date_to before backend request
function deriveDateRange() {
  const today = new Date()
  const pad = n => String(n).padStart(2,'0')
  const fmt = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`
  let from = ''
  let to = ''
  if (selectedAppliedDate.value === 'today') {
    from = to = fmt(today)
  } else if (selectedAppliedDate.value === 'week') {
    const d = new Date(today); d.setDate(d.getDate() - 7); from = fmt(d); to = fmt(today)
  } else if (selectedAppliedDate.value === 'month') {
    const d = new Date(today); d.setMonth(d.getMonth() - 1); from = fmt(d); to = fmt(today)
  } else if (selectedAppliedDate.value === '3months') {
    const d = new Date(today); d.setMonth(d.getMonth() - 3); from = fmt(d); to = fmt(today)
  } else if (selectedAppliedDate.value === 'custom') {
    from = customFromDate.value || ''
    to = customToDate.value || ''
  }
  filters.value.date_from = from
  filters.value.date_to = to
  filters.value.selected_applied_date = selectedAppliedDate.value
}

const filteredApplicants = computed(() => {
  let apps = props.applicants

  if (filters.value.job_id) {
    apps = apps.filter(a => a.job_id == filters.value.job_id)
  }
  if (filters.value.screening_label) {
    apps = apps.filter(a => a.screening_label === filters.value.screening_label)
  }
  if (filters.value.employment_type) {
    const f = filters.value.employment_type.toLowerCase()
    apps = apps.filter(a => (a.employment_type || '').toLowerCase().includes(f))
  }

  // Apply derived date range (uses applied_at_iso if present else parse applied_at)
  if (filters.value.date_from) {
    apps = apps.filter(a => {
      const iso = a.applied_at_iso || ''
      return iso ? iso >= filters.value.date_from : true
    })
  }
  if (filters.value.date_to) {
    apps = apps.filter(a => {
      const iso = a.applied_at_iso || ''
      return iso ? iso <= filters.value.date_to : true
    })
  }

  if (filters.value.keywords) {
    const kw = filters.value.keywords.toLowerCase()
    apps = apps.filter(a =>
      (a.name || '').toLowerCase().includes(kw) ||
      (a.job_title || '').toLowerCase().includes(kw) ||
      (a.screening_label || '').toLowerCase().includes(kw)
    )
  }

  if (filters.value.match_range) {
    if (filters.value.match_range === '70') {
      apps = apps.filter(a => (a.match_percentage || 0) >= 70)
    } else if (filters.value.match_range === '30') {
      apps = apps.filter(a => {
        const p = a.match_percentage || 0
        return p >= 30 && p < 70
      })
    } else if (filters.value.match_range === 'lt30') {
      apps = apps.filter(a => (a.match_percentage || 0) < 30)
    }
  }

  if (filters.value.stage) {
    const st = filters.value.stage.toLowerCase()
    apps = apps.filter(a => (a.stage || '').toLowerCase() === st)
  }

  return apps
})

const averageMatchPercent = computed(() => {
  if (!props.applicants.length) return 0
  return Math.round(
    props.applicants.reduce((s, a) => s + (a.match_percentage || 0), 0) /
    props.applicants.length
  )
})

function applyFilters() {
  deriveDateRange()
  router.get(window.location.pathname, { ...filters.value }, { preserveState: true, replace: true })
}

function resetFilters() {
  filters.value = {}
  selectedAppliedDate.value = ''
  customFromDate.value = ''
  customToDate.value = ''
  applyFilters()
}

const showStageManager = ref(false) // <-- added

function closeStageManager() { showStageManager.value = false }
// Optional ESC close
function escListener(e){ if(e.key==='Escape'){ closeStageManager() } }
onMounted(()=> document.addEventListener('keydown', escListener))
onBeforeUnmount(()=> document.removeEventListener('keydown', escListener))
</script>

<template>
  <AppLayout title="Manage Applicants">
    <template #header>
      <div class="flex items-center">
        <i class="fas fa-users-cog text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Manage Applicants
        </h2>
      </div>
    </template>

    <Container class="py-8">
      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div
          v-for="(stat, index) in statsDisplay"
          :key="index"
          class="bg-white rounded-lg p-4 border border-gray-200 relative overflow-hidden"
        >
          <div class="flex justify-between items-start">
            <div>
              <p class="text-sm text-gray-500 mb-1">{{ stat.title }}</p>
              <p class="text-2xl font-bold">{{ stat.value }}</p>
              <p class="text-xs text-gray-400 mt-1">{{ stat.period }}</p>
            </div>
            <div :class="[stat.iconBg,'rounded-full p-3 flex items-center justify-center']">
              <i :class="[stat.icon, stat.iconColor]"></i>
            </div>
          </div>
        </div>
        <div class="flex gap-6 mb-6">
          <div class="bg-white rounded-lg shadow p-4 flex-1 text-center">
            <div class="text-lg font-bold">Average Match %</div>
            <div class="text-3xl font-extrabold text-indigo-600">
              {{ averageMatchPercent }}%
            </div>
            <div class="text-xs text-gray-500">Across all applicants</div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6">
        <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center">
          <div class="flex w-full sm:w-auto space-x-3 mt-3 sm:mt-0">
            <div class="flex flex-wrap gap-4">
              <!-- Search -->
              <div class="flex flex-col">
                <label class="text-sm font-medium mb-1">Search</label>
                <input
                  v-model="filters.keywords"
                  @input="applyFilters"
                  placeholder="Applicants, job title, or result"
                  class="border px-2 py-1 rounded w-56"
                  type="text"
                />
              </div>

              <!-- Job -->
              <div class="flex flex-col">
                <label class="text-sm font-medium mb-1">Job Position</label>
                <select v-model="filters.job_id" @change="applyFilters" class="border px-2 py-1 rounded w-48">
                  <option value="">All Positions</option>
                  <option v-for="job in jobs || []" :key="job.id" :value="job.id">
                    {{ job.job_title || job.title }}
                  </option>
                </select>
              </div>

              <!-- Screening Result -->
              <div class="flex flex-col">
                <label class="text-sm font-medium mb-1">Screening Result</label>
                <select v-model="filters.screening_label" @change="applyFilters" class="border px-2 py-1 rounded w-44">
                  <option value="">All Results</option>
                  <option value="Shortlisted">Shortlisted</option>
                  <option value="Review Further">Review Further</option>
                  <option value="Not Recommended">Not Recommended</option>
                </select>
              </div>

              <!-- Match % -->
              <div class="flex flex-col">
                <label class="text-sm font-medium mb-1">Match %</label>
                <select v-model="filters.match_range" @change="applyFilters" class="border px-2 py-1 rounded w-40">
                  <option value="">All</option>
                  <option value="70">70% +</option>
                  <option value="30">30% - 69%</option>
                  <option value="lt30">&lt; 30%</option>
                </select>
              </div>

              <!-- Stage -->
              <div class="flex flex-col">
                <label class="text-sm font-medium mb-1">Stage</label>
                <select v-model="filters.stage" @change="applyFilters" class="border px-2 py-1 rounded w-40">
                  <option value="">All Stages</option>
                  <option value="applied">Applied</option>
                  <option value="screening">Screening</option>
                  <option value="assessment">Assessment</option>
                  <option value="interview">Interview</option>
                  <option value="offer">Offer</option>
                  <option value="hired">Hired</option>
                  <option value="rejected">Rejected</option>
                </select>
              </div>

              <!-- Applied Date Preset -->
              <div class="flex flex-col">
                <label class="text-sm font-medium mb-1">Applied Date</label>
                <select
                  v-model="selectedAppliedDate"
                  @change="() => { if(selectedAppliedDate !== 'custom'){ customFromDate=''; customToDate=''; } applyFilters(); }"
                  class="border px-2 py-1 rounded w-44"
                >
                  <option value="">All Dates</option>
                  <option value="today">Today</option>
                  <option value="week">This Week</option>
                  <option value="month">This Month</option>
                  <option value="3months">Last 3 Months</option>
                  <option value="custom">Custom Range…</option>
                </select>

                <!-- Custom Range -->
                <div v-if="selectedAppliedDate === 'custom'" class="flex gap-2 mt-2">
                  <div>
                    <label class="block text-xs font-medium text-gray-600">From</label>
                    <input
                      type="date"
                      v-model="customFromDate"
                      @change="applyFilters"
                      class="border px-2 py-1 rounded"
                    />
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-600">To</label>
                    <input
                      type="date"
                      v-model="customToDate"
                      :min="customFromDate || undefined"
                      @change="applyFilters"
                      class="border px-2 py-1 rounded"
                    />
                  </div>
                </div>
              </div>

              <!-- Reset + Customize -->
              <div class="flex flex-col justify-end space-y-2">
                <button
                  class="mt-5 text-xs text-gray-600 hover:underline"
                  @click="resetFilters"
                  type="button"
                >
                  Reset Filters
                </button>
                <button
                  class="text-xs text-indigo-600 hover:underline font-medium"
                  type="button"
                  @click="showStageManager = true"
                >
                  Customize Stage Order
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- List -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="flex items-center">
          <h3 class="text-lg font-semibold p-4 border-b border-gray-200">List of Applicants</h3>
        </div>
        <ListOfApplicants
          :jobs="jobs"
          :applicants="filteredApplicants"
          :statuses="statuses"
          :employmentTypes="employmentTypes"
          :filters="filters"
        />
      </div>

      <!-- Stage Order Modal -->
      <div
        v-if="showStageManager"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
      >
        <div class="bg-white w-full max-w-md rounded-lg shadow-xl p-5 relative">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700">Customize Pipeline Stage Order</h3>
            <button
              class="text-gray-400 hover:text-gray-600"
              @click="closeStageManager"
              type="button"
            >
              <i class="fas fa-times"></i>
            </button>
          </div>

          <PipelineStageManager />

            <div class="mt-5 flex justify-end gap-2">
              <button
                type="button"
                class="px-3 py-2 text-xs rounded border border-gray-300 text-gray-600"
                @click="closeStageManager"
              >
                Cancel
              </button>
              <button
                type="button"
                class="px-3 py-2 text-xs rounded bg-indigo-600 text-white"
                @click="closeStageManager"
              >
                Done
              </button>
            </div>
        </div>
      </div>
    </Container>
  </AppLayout>
</template>