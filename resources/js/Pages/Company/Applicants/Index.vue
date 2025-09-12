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

console.log('Applicants:', props.applicants)
// Stats are now displayed using direct props access in template

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
  // Defensive cast in case backend returns an object (keyed collection)
  const raw = props.applicants || []
  let apps = Array.isArray(raw) ? raw : Object.values(raw)

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
      <!-- Stats Summary -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <!-- Total Applicants -->
        <div class="bg-gradient-to-br from-purple-100 to-purple-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-user-friends text-white text-xl"></i>
            </div>
            <h3 class="text-purple-700 text-sm font-medium mb-2">Total Applicants</h3>
            <p class="text-3xl font-bold text-purple-700">{{ props.stats.total_applicants }}</p>
          </div>
        </div>

        <!-- Total Hired -->
        <div class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-check-circle text-white text-xl"></i>
            </div>
            <h3 class="text-green-800 text-sm font-medium mb-2">Total Hired</h3>
            <p class="text-3xl font-bold text-green-900">{{ props.stats.hired }}</p>
          </div>
        </div>

        <!-- Total Rejected -->
        <div class="bg-gradient-to-br from-red-100 to-red-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-times-circle text-white text-xl"></i>
            </div>
            <h3 class="text-red-700 text-sm font-medium mb-2">Total Rejected</h3>
            <p class="text-3xl font-bold text-red-900">{{ props.stats.rejected }}</p>
          </div>
        </div>

        <!-- Interviews -->
        <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-comments text-white text-xl"></i>
            </div>
            <h3 class="text-blue-700 text-sm font-medium mb-2">Interviews</h3>
            <p class="text-3xl font-bold text-blue-700">{{ props.stats.interviews }}</p>
          </div>
        </div>

        <!-- Average Match % -->
        <div class="bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-percentage text-white text-xl"></i>
            </div>
            <h3 class="text-indigo-700 text-sm font-medium mb-2">Average Match</h3>
            <p class="text-3xl font-bold text-indigo-700">{{ averageMatchPercent }}%</p>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-200 flex items-center">
          <i class="fas fa-filter text-blue-500 mr-2"></i>
          <div>
        <h3 class="font-medium text-gray-700">Search & Filter</h3>
        <p class="text-sm text-gray-500">Find applicants by criteria below</p>
          </div>
          <div class="ml-auto">
        <button
          class="px-6 py-3 bg-white text-gray-700 rounded-xl text-sm font-medium flex items-center hover:bg-gray-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 shadow-sm border border-gray-300"
          @click="resetFilters"
          type="button"
        >
          <i class="fas fa-undo mr-2"></i> Reset Filters
        </button>
          </div>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
        <!-- Search Input -->
        <div>
          <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
          <i class="fas fa-search text-gray-400"></i>
            </div>
            <input
          id="search"
          v-model="filters.keywords"
          @input="applyFilters"
          type="text"
          placeholder="Applicants, job title, or result"
          class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm"
            />
          </div>
        </div>

        <!-- Job Position Dropdown -->
        <div>
          <label for="jobPosition" class="block text-sm font-medium text-gray-700 mb-1">Job Position</label>
          <div class="relative">
            <select
          id="jobPosition"
          v-model="filters.job_id"
          @change="applyFilters"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10"
            >
          <option value="">All Positions</option>
          <option v-for="job in jobs || []" :key="job.id" :value="job.id">
            {{ job.job_title || job.title }}
          </option>
            </select>
          </div>
        </div>

        <!-- Screening Result Dropdown -->
        <div>
          <label for="screeningResult" class="block text-sm font-medium text-gray-700 mb-1">Screening Result</label>
          <div class="relative">
            <select
          id="screeningResult"
          v-model="filters.screening_label"
          @change="applyFilters"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10"
            >
          <option value="">All Results</option>
          <option value="Shortlisted">Shortlisted</option>
          <option value="Review Further">Review Further</option>
          <option value="Not Recommended">Not Recommended</option>
            </select>
          </div>
        </div>

        <!-- Match % Dropdown -->
        <div>
          <label for="matchPercent" class="block text-sm font-medium text-gray-700 mb-1">Match %</label>
          <div class="relative">
            <select
          id="matchPercent"
          v-model="filters.match_range"
          @change="applyFilters"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10"
            >
          <option value="">All</option>
          <option value="70">70% +</option>
          <option value="30">30% - 69%</option>
          <option value="lt30">&lt; 30%</option>
            </select>
          </div>
        </div>

        <!-- Stage Dropdown -->
        <div>
          <label for="stage" class="block text-sm font-medium text-gray-700 mb-1">Stage</label>
          <div class="relative">
            <select
          id="stage"
          v-model="filters.stage"
          @change="applyFilters"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10"
            >
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
        </div>

        <!-- Applied Date Preset Dropdown -->
        <div>
          <label for="appliedDate" class="block text-sm font-medium text-gray-700 mb-1">Applied Date</label>
          <div class="relative">
            <select
          id="appliedDate"
          v-model="selectedAppliedDate"
          @change="() => { if(selectedAppliedDate !== 'custom'){ customFromDate=''; customToDate=''; } applyFilters(); }"
          class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10"
            >
          <option value="">All Dates</option>
          <option value="today">Today</option>
          <option value="week">This Week</option>
          <option value="month">This Month</option>
          <option value="3months">Last 3 Months</option>
          <option value="custom">Custom Range…</option>
            </select>
          </div>
          <!-- Custom Range Fields -->
          <div v-if="selectedAppliedDate === 'custom'" class="flex gap-2 mt-2">
            <div>
          <label class="block text-xs font-medium text-gray-600">From</label>
          <input
            type="date"
            v-model="customFromDate"
            @change="applyFilters"
            class="border border-gray-300 rounded px-2 py-1 focus:ring-blue-500 focus:border-blue-500"
          />
            </div>
            <div>
          <label class="block text-xs font-medium text-gray-600">To</label>
          <input
            type="date"
            v-model="customToDate"
            :min="customFromDate || undefined"
            @change="applyFilters"
            class="border border-gray-300 rounded px-2 py-1 focus:ring-blue-500 focus:border-blue-500"
          />
            </div>
          </div>
        </div>

        <!-- Customize Stage Order Button -->
        <div class="flex flex-col justify-end">
          <button
            class="text-xs text-indigo-600 hover:underline font-medium mt-2"
            type="button"
            @click="showStageManager = true"
          >
            <i class="fas fa-cogs mr-1"></i>
            Customize Stage Order
          </button>
        </div>
          </div>
        </div>
      </div>

      <!-- Applicants List -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">List of Applicants</h3>
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