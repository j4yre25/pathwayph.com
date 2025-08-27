<script setup>
import CandidatePipeline from '@/Components/CandidatePipeline.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  applicants: {
    type: Array,
    default: () => [],
  },
  jobs: {
    type: Array,
    default: () => [],
  },
  statuses: {
    type: Array,
    default: () => [],
  },
  employmentTypes: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});

const getStatusBadge = (status) => {
  if (status === 'shortlisted') return 'bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold';
  if (status === 'under_review') return 'bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-bold';
  if (status === 'not_recommended') return 'bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold';
  return 'bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs font-bold';
};
const getStatusText = (status) => {
  if (status === 'shortlisted') return 'Shortlisted';
  if (status === 'under_review') return 'Under Review';
  if (status === 'not_recommended') return 'Not Recommended';
  return status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Unknown';
};
const getMatchColor = (percent) => {
  if (percent >= 70) return '#059669'; // green
  if (percent >= 30) return '#F59E42'; // yellow
  return '#DC2626'; // red
};
function saveNotes(applicant) {
  router.put(route('applicants.update', applicant.id), { notes: applicant.notes }, { preserveState: true });
}
function moveStage(applicant, stage) {
  router.put(route('applicants.update', applicant.id), { stage }, { preserveState: true });
}
const viewProfile = (applicant) => {
  router.get(route('applicants.show', applicant.id));
};
function scheduleInterview(applicant) {
  router.get(route('applicants.scheduleInterview', applicant.id));
}
</script>

<template>
  <div>
    <div v-if="applicants.length > 0" class="space-y-4">
      <div
        v-for="applicant in applicants"
        :key="applicant.id"
        class="bg-white rounded-lg shadow border border-gray-200 p-6 flex flex-col md:flex-row md:items-center md:justify-between hover:shadow-md transition-shadow"
      >
        <!-- Left: Profile & Main Info -->
        <div class="flex items-center space-x-4 w-full md:w-1/3">
          <img
            :src="applicant.profile_picture || '/default-avatar.png'"
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
              <span class="px-2 py-1 rounded bg-indigo-50 text-xs text-indigo-700 font-semibold">
                {{ applicant.employment_type }}
              </span>
            </div>
          </div>
        </div>

        <!-- Middle: Match %, Status, Stage, Date, Notes -->
        <div class="flex-1 mt-4 md:mt-0 md:px-6 flex flex-col gap-2">
          <!-- Match % -->
          <div class="flex items-center gap-2">
            <div class="relative inline-block w-12 h-12">
              <svg viewBox="0 0 36 36" class="circular-chart">
                <path class="circle-bg"
                  d="M18 2.0845
                    a 15.9155 15.9155 0 0 1 0 31.831
                    a 15.9155 15.9155 0 0 1 0 -31.831"
                  fill="none"
                  stroke="#eee"
                  stroke-width="3.5"/>
                <path class="circle"
                  :stroke-dasharray="applicant.match_percentage + ', 100'"
                  d="M18 2.0845
                    a 15.9155 15.9155 0 0 1 0 31.831
                    a 15.9155 15.9155 0 0 1 0 -31.831"
                  fill="none"
                  :stroke="getMatchColor(applicant.match_percentage)"
                  stroke-width="3.5"/>
                <text x="18" y="22" text-anchor="middle" fill="#333" font-size="10">{{ applicant.match_percentage }}%</text>
              </svg>
            </div>
            <span class="ml-2 text-xs font-bold text-gray-700">Match %</span>
          </div>
          <!-- Status Badge -->
          <div>
            <span :class="getStatusBadge(applicant.status)">
              {{ getStatusText(applicant.status) }}
            </span>
          </div>
          <!-- Stage/Progress -->
          <div>
            <CandidatePipeline :stage="applicant.stage" />
          </div>
          <!-- Date Applied -->
          <div class="text-xs text-gray-500">
            <i class="fas fa-calendar mr-1"></i>
            Applied: {{ applicant.applied_at }}
          </div>
          <!-- Notes/Remarks -->
          <div>
            <input
              v-model="applicant.notes"
              @blur="saveNotes(applicant)"
              class="border px-2 py-1 rounded w-full text-xs"
              placeholder="Add remarks..."
            />
          </div>
        </div>

        <!-- Right: Actions -->
        <div class="flex flex-col gap-2 mt-4 md:mt-0 md:w-48 items-end">
          <!-- View Profile -->
          <button
            class="text-blue-600 hover:underline text-sm mb-1"
            @click="viewProfile(applicant)"
          >
            View Profile
          </button>

          <!-- Schedule Interview -->
          <button
            class="text-green-600 hover:underline text-sm mb-1"
            @click="scheduleInterview(applicant)"
          >
            Schedule Interview
          </button>
          <Dropdown>
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
        <i class="fas fa-user-slash text-gray-300 text-4xl mb-3" aria-hidden="true"></i>
        <p class="text-lg font-medium text-gray-800">No Applicants Found</p>
        <p class="text-sm text-gray-500 mt-1">There are currently no applicants for this job.</p>
      </div>
    </div>
  </div>
</template>