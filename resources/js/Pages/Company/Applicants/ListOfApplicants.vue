<script setup>
import CandidatePipeline from '@/Components/CandidatePipeline.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  applicants: { type: Array, default: () => [] },
  jobs: { type: Array, default: () => [] },
  statuses: { type: Array, default: () => [] },
  employmentTypes: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({}) },
})

console.log(props.applicants)

const getStatusBadge = (label) => {
  if (label === 'Shortlisted') return 'bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold'
  if (label === 'Review Further') return 'bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-bold'
  if (label === 'Not Recommended') return 'bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold'
  return 'bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-bold'
}
const getStatusText = (label) => {
  if (label === 'Shortlisted') return 'Shortlisted'
  if (label === 'Review Further') return 'Review Further'
  if (label === 'Not Recommended') return 'Not Recommended'
  return label || 'Unknown'
}
const getMatchColor = (percent) => {
  if (percent >= 70) return '#059669'
  if (percent >= 30) return '#F59E42'
  return '#DC2626'
}
function saveNotes(applicant) {
  router.put(route('applicants.update', applicant.id), { notes: applicant.notes }, { preserveState: true })
}
function moveStage(applicant, stage) {
  // stage is canonical slug
  router.put(route('applicants.update', applicant.id), { stage }, { preserveState: true })
}
const viewProfile = (applicant) => {
  router.get(route('applicants.show', applicant.id))
}
function scheduleInterview(applicant) {
  // Should be POST (placeholder)
  // router.post(route('applicants.scheduleInterview', applicant.id), { scheduled_at: ... })
  router.get(route('applicants.show', applicant.id))
}

// (optional normalization)
const isTerminalStage = (stage) => {
  if (!stage) return false
  const s = stage.toString().toLowerCase()
  return ['hired','rejected','hire','reject','decline'].some(k => s.includes(k))
}
const isHired = (stage) => stage && stage.toString().toLowerCase().includes('hir')
const isRejected = (stage) => stage && (stage.toString().toLowerCase().includes('reject') || stage.toString().toLowerCase().includes('decline'))
</script>

<template>
  <div>
    <div v-if="applicants.length" class="space-y-4">
      <div
        v-for="applicant in applicants"
        :key="applicant.id"
        class="bg-white rounded-lg shadow border border-gray-200 p-6 flex flex-col md:flex-row md:items-center md:justify-between hover:shadow-md transition-shadow"
      >
        <!-- Left -->
        <div class="flex items-center space-x-4 w-full md:w-1/3">
          <img
            :src="applicant.profile_picture ? `/storage/${applicant.profile_picture}` : '/default-avatar.png'"
            alt="Profile"
            class="w-16 h-16 rounded-full object-cover border border-gray-300"
          />
          <div>
            <h3 class="text-lg font-bold text-gray-800">{{ applicant.name }}</h3>
            <div class="text-sm text-gray-500">{{ applicant.email }}</div>
            <div class="mt-1 flex flex-wrap gap-2">
              <span class="px-2 py-1 rounded bg-gray-100 text-xs text-gray-700 font-semibold">
                {{ applicant.job_title }}
              </span>
              <span v-if="applicant.employment_type" class="px-2 py-1 rounded bg-indigo-50 text-xs text-indigo-700 font-semibold">
                {{ applicant.employment_type }}
              </span>
            </div>
          </div>
        </div>

        <!-- Middle -->
        <div class="flex-1 mt-4 md:mt-0 md:px-6 flex flex-col gap-2">
          <!-- Match % -->
            <div class="flex items-center gap-2">
              <div class="relative inline-block w-12 h-12">
                <svg viewBox="0 0 36 36">
                  <path
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                    fill="none"
                    stroke="#eee"
                    stroke-width="3.5"
                  />
                  <path
                    :stroke-dasharray="(applicant.match_percentage || 0) + ', 100'"
                    d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                    fill="none"
                    :stroke="getMatchColor(applicant.match_percentage || 0)"
                    stroke-width="3.5"
                  />
                  <text x="18" y="22" text-anchor="middle" fill="#333" font-size="10">
                    {{ applicant.match_percentage || 0 }}%
                  </text>
                </svg>
              </div>
              <span class="ml-2 text-xs font-bold text-gray-700">Match %</span>
            </div>

          <!-- Screening Result -->
          <div>
            <span :class="getStatusBadge(applicant.screening_label)">
              {{ getStatusText(applicant.screening_label) }}
            </span>
          </div>

          <!-- Pipeline -->
          <div>
            <template v-if="!isTerminalStage(applicant.stage)">
              <CandidatePipeline :stage="applicant.stage" />
            </template>
            <template v-else>
              <span
                class="inline-block px-2.5 py-1 rounded-full text-[11px] font-semibold"
                :class="{
                  'bg-green-100 text-green-700': isHired(applicant.stage),
                  'bg-red-100 text-red-700': isRejected(applicant.stage)
                }"
              >
                {{ isHired(applicant.stage) ? 'Hired' : 'Rejected' }}
              </span>
            </template>
          </div>

          <!-- Date -->
          <div class="text-xs text-gray-500">
            <i class="fas fa-calendar mr-1"></i>
            Applied: {{ applicant.applied_at }}
          </div>

          <!-- Notes -->
          <div>
            <input
              v-model="applicant.notes"
              @blur="saveNotes(applicant)"
              class="border px-2 py-1 rounded w-full text-xs"
              placeholder="Add remarks..."
            />
          </div>
        </div>

        <!-- Right Actions -->
        <div class="flex flex-col gap-2 mt-4 md:mt-0 md:w-48 items-end">
          <button class="text-blue-600 hover:underline text-sm mb-1" @click="viewProfile(applicant)">View Profile</button>
          <button class="text-green-600 hover:underline text-sm mb-1" @click="scheduleInterview(applicant)">Schedule Interview</button>
          <Dropdown>
            <DropdownLink @click="moveStage(applicant, 'screening')">Move to Screening</DropdownLink>
            <DropdownLink @click="moveStage(applicant, 'assessment')">Move to Assessment</DropdownLink>
            <DropdownLink @click="moveStage(applicant, 'interview')">Move to Interview</DropdownLink>
            <DropdownLink @click="moveStage(applicant, 'offer')">Move to Offer</DropdownLink>
            <DropdownLink @click="moveStage(applicant, 'hired')">Hire</DropdownLink>
            <DropdownLink @click="moveStage(applicant, 'rejected')">Reject</DropdownLink>
          </Dropdown>
        </div>
      </div>
    </div>
    <div v-else class="bg-white rounded-lg border border-gray-200 p-10 text-center">
      <div class="flex flex-col items-center">
        <i class="fas fa-user-slash text-gray-300 text-4xl mb-3"></i>
        <p class="text-lg font-medium text-gray-800">No Applicants Found</p>
        <p class="text-sm text-gray-500 mt-1">There are currently no applicants.</p>
      </div>
    </div>
  </div>
</template>