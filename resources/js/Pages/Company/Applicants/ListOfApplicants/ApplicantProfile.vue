<script setup>
import { ref, onMounted, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import Datepicker from 'vue3-datepicker'
import AppLayout from '@/Layouts/AppLayout.vue'
import CandidatePipeline from '@/Components/CandidatePipeline.vue'
import PipelineAction from '@/Components/PipelineAction.vue'
import useApplicantNote from '@/Composables/useApplicantNote'
import { useVisitWebsite } from '@/Composables/useVisitWebsite'
import RequestMoreInfoModal from '@/Components/RequestMoreInfoModal.vue'
import axios from 'axios'
import ExamInstructionsModal from '@/Components/ExamInstructionsModal.vue'
import ExamResultModal from '@/Components/ExamResultModal.vue'
import ExamRescheduleModal from '@/Components/ExamRescheduleModal.vue'
import InterviewScheduleModal from '@/Components/InterviewScheduleModal.vue'
import InterviewRescheduleModal from '@/Components/InterviewRescheduleModal.vue'
import InterviewFeedbackModal from '@/Components/InterviewFeedbackModal.vue'
import HireConfirmModal from '@/Components/HireConfirmModal.vue'
import OfferSendModal from '@/Components/OfferSendModal.vue'
import RejectConfirmModal from '@/Components/RejectConfirmModal.vue'

const props = defineProps({
  applicant: Object,
  skills: { type: Array, default: () => [] },
  education: { type: Array, default: () => [] },
  experiences: { type: Array, default: () => [] },
  projects: { type: Array, default: () => [] },
  achievements: { type: Array, default: () => [] },
  certifications: { type: Array, default: () => [] },
  testimonials: { type: Array, default: () => [] },
  employmentPreferences: { type: Object, default: null },
  careerGoals: { type: Object, default: null },
  resume: { type: Object, default: null },
  referralCertificates: { type: Array, default: () => [] }, // NEW
})

// Canonical stage order
const stageOrder = ['applied','screening','assessment','interview','offer','hired','rejected']
const currentStage = ref(props.applicant.stage || 'applied')

// Status mapping helper (front-end mirror of backend logic)
function mapStatusFromStage(stage) {
  const s = stage?.toLowerCase()
  if (s === 'applied') return 'applied'
  if (['screening','assessment','interview'].includes(s)) return 'screening'
  if (s === 'offer') return 'offered'
  if (s === 'hired') return 'hired'
  if (s === 'rejected') return 'rejected'
  return props.applicant?.status
}

// Ensure local applicant.status is aligned on mount
if (props.applicant) {
  props.applicant.status = mapStatusFromStage(props.applicant.stage)
}

// Replace onStageChanged to also sync status
function onStageChanged(newStage) {
  currentStage.value = newStage
  if (props.applicant) {
    props.applicant.stage = newStage
    props.applicant.status = mapStatusFromStage(newStage)
  }
}

// Map stage slug to readable label
const stageLabels = {
  applied: 'Applied',
  screening: 'Screening',
  assessment: 'Assessment / Exam',
  interview: 'Interview',
  offer: 'Offer',
  hired: 'Hired',
  rejected: 'Rejected'
}

const displayStage = computed(() => stageLabels[currentStage.value] || currentStage.value)

// Optional: retain original applicant.status separately

const showHireConfirmation = ref(false);


// Copy to clipboard function
function copyToClipboard(text) {
  if (!text) return;
  navigator.clipboard.writeText(text);
}
//End of copy to clipboard function

// Fetch tab-specific data on viewApplicantDetails()
const resume = ref(props.resume);

//For note section
const {
  note,
  editingNote,
  noteInput,
  startEditNote,
  cancelEditNote,
  saveNote
} = useApplicantNote(props.applicant);
//End of note section

//For social media links
const { formatUrl } = useVisitWebsite();

function formatDate(dateStr) {
  if (!dateStr) return 'N/A';
  const date = new Date(dateStr);
  if (isNaN(date)) return dateStr;
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
}


const totalYearsExperience = computed(() => {
  if (!props.experiences.length) return 'No work experience';
  let totalMonths = 0;
  props.experiences.forEach(exp => {
    if (exp.start_date) {
      const start = new Date(exp.start_date);
      const end = exp.is_current ? new Date() : (exp.end_date ? new Date(exp.end_date) : new Date());
      const months = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());
      totalMonths += months > 0 ? months : 0;
    }
  });
  const years = Math.floor(totalMonths / 12);
  return years > 0 ? `${years} Year${years > 1 ? 's' : ''}` : '< 1 Year';
});

const goBack = () => {
    window.history.back();
};

const showRequestInfoModal = ref(false)

function openRequestInfo() {
  showRequestInfoModal.value = true
}

function handleRequestInfoSent() {
  showRequestInfoModal.value = false
  // Optionally refresh messages / badge here
}

// Reject applicant modal
const showRejectModal = ref(false)
const rejectActionKey = ref('reject')

function openReject(payload) {
  rejectActionKey.value = payload?.actionKey || 'reject'
  showRejectModal.value = true
}

function onRejected(stage) {
  onStageChanged(stage || 'rejected')
  showRejectModal.value = false
}

// Interview stage auxiliary status (not persisted; optional)
const interviewStageStatus = ref(props.applicant?.status || '')

// After scheduling or rescheduling we can reflect a friendly status locally
function setInterviewStatus(label) {
  interviewStageStatus.value = label
  props.applicant.status = label
}

const showExamInstructions = ref(false)
const showExamResult = ref(false)
const showExamReschedule = ref(false)
const showInterviewSchedule = ref(false)
const showInterviewReschedule = ref(false)
const showInterviewFeedback = ref(false)
const showOfferSendModal = ref(false)
const showHireModal = ref(false)

function handlePipelineModal(e) {
  console.log('Pipeline modal event:', e)
  if (e.action === 'send_exam_instructions') showExamInstructions.value = true
  if (e.action === 'record_test_results')     showExamResult.value = true
  if (e.action === 'reschedule_test')         showExamReschedule.value = true
  if (e.action === 'schedule_interview')      showInterviewSchedule.value = true
  if (e.action === 'reschedule_interview')    showInterviewReschedule.value = true
  if (e.action === 'record_interview_feedback') showInterviewFeedback.value = true
  if (e.action === 'send_offer')              showOfferSendModal.value = true
}

// Exam callbacks
function onExamInstructionsSent() { /* refresh messages if needed */ }
function onExamResultSaved() { /* refresh results if needed */ }
function onExamRescheduleSent() { /* refresh messages if needed */ }

// Interview callbacks
function onInterviewInvitationSent() {
  setInterviewStatus('Interview Scheduled')
  showInterviewSchedule.value = false
  showConfirmationModal.value = true
}

function onInterviewRescheduled() {
  setInterviewStatus('Interview Rescheduled')
  showInterviewReschedule.value = false
}

function onInterviewFeedbackSaved(recommendation) {
  // Optionally react to recommendation
  if (recommendation === 'move_forward') {
    // could trigger a stage change request here if desired
    // router.post(...); or just set a label:
    setInterviewStatus('Recommended to Move Forward')
  } else if (recommendation === 'reject') {
    setInterviewStatus('Interview - Rejection Recommended')
  } else {
    setInterviewStatus('Interview - On Hold')
  }
  showInterviewFeedback.value = false
}

function openHire() {
  showHireModal.value = true
}

function onHired(stage) {
  onStageChanged(stage || 'hired')
  console.debug('Hired confirmed → stage/status synced')
}

function onOfferSent() {
  // placeholder for refresh (messages / timeline)
  console.log('Offer sent')
}

// Add after other refs near top (but before export end)
const activeTab = ref('education')

const tabs = [
  { key: 'education', label: 'Education' },
  { key: 'experience', label: 'Work Experience' },
  { key: 'certs', label: 'Certifications/Achievements' },
  { key: 'skills', label: 'Skills' },
  { key: 'documents', label: 'Supporting Documents' },
  { key: 'career', label: 'Career Profile' },
]

function setTab(key) {
  activeTab.value = key
}

const allSkillItems = computed(() => props.skills || [])

// Supporting document helpers (adjust property names if different)
const resumeDoc = computed(() => props.resume || null)
const coverLetterDoc = computed(() => props.applicant?.cover_letter ? {
  file_name: 'Cover Letter',
  file_url: props.applicant.cover_letter,
  file_type: 'pdf'
} : null)

// Example placeholders; replace with real backend fields if available.
const referralDoc = computed(() => null)        // e.g. props.applicant.referral_certificate
const alumniStories = computed(() => props.testimonials || []) // or a dedicated alumniStories relation
const testimonialsList = computed(() => props.testimonials || [])

function formatShortDate(d) {
  if (!d) return ''
  const dt = new Date(d)
  if (isNaN(dt)) return d
  return dt.toLocaleDateString('en-US', { year: 'numeric', month: 'short' })
}

// Add helper to display cleaner credential URL (optional)
function formatCredentialUrl(raw) {
  if (!raw) return ''
  let url = raw.trim()
  if (!/^https?:\/\//i.test(url)) url = 'https://' + url
  try {
    const u = new URL(url)
    const host = u.hostname.replace(/^www\./,'')
    const path = u.pathname === '/' ? '' : u.pathname
    return host + path
  } catch {
    return raw
  }
}

// PATCH: Replace groupedSkills computed with version that prefers backend 'group_label'
const groupedSkills = computed(() => {
  const groups = {}
  ;(allSkillItems.value || []).forEach(s => {
    const raw =
      s.group_label ||
      s.type ||
      s.category ||
      s.skill_type ||
      s.group ||
      s.skillCategory ||
      'Uncategorized'
    const label = (typeof raw === 'string' && raw.trim().length)
      ? raw.trim()
      : 'Uncategorized'
    if (!groups[label]) groups[label] = []
    groups[label].push(s)
  })
  return Object.keys(groups)
    .sort((a,b) => a.localeCompare(b))
    .map(label => ({
      label,
      items: groups[label].sort((a,b) =>
        (a.skill_name || '').localeCompare(b.skill_name || '')
      )
    }))
})

// referralCerts computed
const referralCerts = computed(() => props.referralCertificates || [])
</script>

<template>
  <AppLayout>
    <template #header>
      <div class="flex items-center">
         <button @click="goBack" class="flex items-center text-blue-600 hover:text-blue-800 transition-colors mr-4">
              <i class="fas fa-chevron-left mr-2"></i>
          </button>
        <i class="fas fa-users-cog text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{ props.applicant.graduate.first_name }} {{ props.applicant.graduate.last_name }}&#39;s Profile
        </h2>
      </div>
    </template>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 md:pt-20">
      <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar -->
        <aside class="md:w-1/3 w-full space-y-6 relative">
          <!-- Social Icons -->
          <div class=" border border-gray-200 bg-white rounded-xl shadow-lg p-4 flex flex-col items-center">
            <div class="flex justify-center space-x-3 mb-2">
              <a
                :href="applicant.graduate.linkedin_url ? formatUrl(applicant.graduate.linkedin_url, 'linkedin.com') : '#'"
                target="_blank"
                class="text-blue-700 text-xl"
                :class="{ 'opacity-40 pointer-events-none cursor-not-allowed': !applicant.graduate.linkedin_url }"
                title="LinkedIn"
              >
                <i class="fab fa-linkedin"></i>
              </a>
              <a
                :href="applicant.graduate.github_url ? formatUrl(applicant.graduate.github_url, 'github.com') : '#'"
                target="_blank"
                class="text-gray-800 text-xl"
                :class="{ 'opacity-40 pointer-events-none cursor-not-allowed': !applicant.graduate.github_url }"
                title="GitHub"
              >
                <i class="fab fa-github"></i>
              </a>
            </div>
            <hr class="    w-full border-gray-200" />
            <!-- Profile Picture -->
            <div class="flex flex-col items-center mt-2">
              <img :src="applicant.graduate.graduate_picture ? `/storage/${applicant.graduate.graduate_picture}` : '/images/default-avatar.png'"
                alt="Graduate Picture"
                class="w-24 h-24 rounded-full object-cover border-4 border-white shadow mb-2" />
              <div class="text-lg font-semibold text-gray-900">{{ applicant.graduate.first_name }} {{ applicant.graduate.last_name }}</div>
              <div class="text-sm text-gray-500 mb-2">{{ applicant.graduate.current_job_title || ' ' }}</div>
              <div class="flex items-center text-xs text-gray-500 space-x-2 mb-2">
                <span>{{ totalYearsExperience }}</span>
                <span>|</span>
                <span>{{ applicant.graduate.address || 'Not Specified' }}</span>
              </div>
              <div class="flex items-center text-xs text-gray-500 space-x-2">
                <span>{{ applicant.graduate.contact_number || 'Not Specified' }}</span>
                <span>|</span>
                <span>{{ applicant.graduate.user?.email || 'Not Specified' }}</span>
                <button class="ml-1 text-gray-400 hover:text-indigo-500" @click="copyToClipboard(applicant.graduate.user?.email)">
                  <i class="far fa-copy"></i>
                </button>
              </div>
            </div>
            <hr class="my-4 w-full border-gray-200" />

            <!-- Current -->
            <div class="w-full flex justify-between text-sm text-gray-700 mb-2">
              <div>
                <div class="font-semibold">Year Graduated</div>
                <div>{{ applicant.graduate.notice_period || 'Not Specified' }}</div>
              </div>
              <div>
                <div class="font-semibold">Highest Education</div>
                <div>{{ education[0]?.education || 'Not Specified' }}</div>
              </div>
            </div>
            <hr class="my-2 w-full border-gray-200" />

            <!-- Employment Preference (replaces old Preferences block) -->
            <div class="w-full text-sm text-gray-700 mb-2">
              <div class="font-semibold text-gray-800 mb-2">Employment Preference</div>

              <div class="flex justify-between mb-2">
                <div>
                  <div class="font-semibold">Pref. Salary</div>
                  <div>
                    <template v-if="employmentPreferences?.employment_min_salary || employmentPreferences?.employment_max_salary">
                      {{ employmentPreferences?.employment_min_salary ?? 'N/A' }} -
                      {{ employmentPreferences?.employment_max_salary ?? 'N/A' }}
                    </template>
                    <template v-else>Not Specified</template>
                  </div>
                </div>
                <div>
                  <div class="font-semibold">Pref. Location</div>
                  <div>
                    <template v-if="employmentPreferences?.locations?.length">
                      {{ employmentPreferences.locations.map(l => l.address || l.name).join(', ') }}
                    </template>
                    <template v-else>Not Specified</template>
                  </div>
                </div>
              </div>

              <div class="flex justify-between mb-2">
                <div>
                  <div class="font-semibold">Job Type</div>
                  <div>
                    <template v-if="employmentPreferences?.job_types?.length">
                      {{ employmentPreferences.job_types.map(j => j.name).join(', ') }}
                    </template>
                    <template v-else>Not Specified</template>
                  </div>
                </div>
                <div>
                  <div class="font-semibold">Work Environment</div>
                  <div>
                    <template v-if="employmentPreferences?.work_environments?.length">
                      {{ employmentPreferences.work_environments.map(w => w.name).join(', ') }}
                    </template>
                    <template v-else>Not Specified</template>
                  </div>
                </div>
              </div>

              <div>
                <div class="font-semibold">Additional Notes</div>
                <div>
                  <template v-if="employmentPreferences?.additional_notes">
                    {{ employmentPreferences.additional_notes }}
                  </template>
                  <template v-else>Not Specified</template>
                </div>
              </div>
            </div>
            <hr class="my-2 w-full border-gray-200" />
            
            <!-- Notes -->
            <div class="w-full text-sm text-gray-700">
              <div class="font-semibold">Remarks</div>
              <div v-if="!editingNote">
                <div>{{ note || 'No notes yet.' }}</div>
                <a href="#" class="text-indigo-600 hover:underline text-xs mt-1 inline-block" @click.prevent="startEditNote">
                  {{ note ? 'Edit note' : 'Add note' }}
                </a>
              </div>
              <div v-else>
                <textarea v-model="noteInput" rows="3" class="w-full border rounded p-2 text-sm"></textarea>
                <div class="flex gap-2 mt-2">
                  <button @click="saveNote" class="bg-indigo-600 text-white px-3 py-1 rounded text-xs hover:bg-indigo-700">Save</button>
                  <button @click="cancelEditNote" class="bg-gray-300 text-gray-700 px-3 py-1 rounded text-xs hover:bg-gray-400">Cancel</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Skills -->
            <div class="bg-white rounded-lg shadow-lg p-6">
              <h4 class="text-lg font-semibold text-gray-800 mb-3">About Me</h4>
              <div
                v-if="applicant.graduate.about_me"
                class="text-sm text-gray-700 leading-relaxed whitespace-pre-line"
              >
                {{ applicant.graduate.about_me }}
              </div>
              <div v-else class="text-sm text-gray-400 italic">
                No description provided.
              </div>
          </div>

          <!-- Resume -->
          <div v-if="resume && resume.file_url" class="bg-white rounded-lg shadow-lg p-6">
              <h4 class="text-lg font-semibold text-gray-800 mb-3">Documents</h4>
              <div class="flex items-center gap-4 mb-4">
                  <i v-if="resume.file_type && resume.file_type.includes('pdf')"
                      class="fas fa-file-pdf text-3xl text-red-500"></i>
                  <i v-else-if="resume.file_type && resume.file_type.includes('word')"
                      class="fas fa-file-word text-3xl text-blue-500"></i>
                  <i v-else class="fas fa-file-alt text-3xl text-gray-500"></i>
                  <span class="font-semibold">{{ resume.file_name }}</span>
                  <a :href="resume.file_url" download target="_blank"
                      class="inline-flex items-center text-indigo-600 hover:underline font-semibold ml-4">
                      <i class="fas fa-file-download mr-2"></i>
                      Download
                  </a>
              </div>
          </div>
          <div v-else class="bg-white rounded-lg shadow-lg p-6 text-gray-600">
              <h4 class="text-lg font-semibold text-gray-800 mb-3">Resume</h4>
              No resume uploaded.
          </div>
        </aside>

        <!-- Main Content -->
        <main class="md:w-2/3 w-full space-y-8 pt-8">
          <!-- Hiring Process -->
          <section class="bg-white rounded-lg shadow-lg p-6 mb-6">
              <div class="flex items-start justify-between mb-4 gap-4 flex-wrap">
                <h4 class="text-base font-semibold text-gray-800">
                  Applied for
                  <span class="text-indigo-600">
                    {{ applicant.job?.job_title || 'Position' }}
                  </span>
                  | Hiring Process
                </h4>
                <span
                  class="inline-block px-3 py-1 rounded-full text-xs font-semibold"
                  :class="{
                    'bg-indigo-100 text-indigo-700': currentStage === 'screening' || currentStage === 'assessment' || currentStage === 'interview',
                    'bg-yellow-100 text-yellow-800': currentStage === 'offer',
                    'bg-green-100 text-green-800': currentStage === 'hired',
                    'bg-red-100 text-red-800': currentStage === 'rejected',
                    'bg-gray-100 text-gray-700': currentStage === 'applied'
                  }"
                >
                  Stage: {{ displayStage }}
                </span>
              </div>

            <div v-if="!['rejected','hired'].includes(currentStage)" class="space-y-4">
              <div class="flex items-center justify-between gap-4 flex-wrap">
                <!-- Pipeline -->
                <div class="flex-1 min-w-[260px]">
                  <CandidatePipeline :stage="currentStage" />
                </div>

                <!-- Dropdown Actions -->
                <div class="shrink-0">
                  <PipelineAction
                    :application-id="applicant.id"
                    :current-stage="currentStage"
                    variant="button"
                    label="Stage Actions"
                    :transitions-only="false"
                    @stage-changed="onStageChanged"
                    @request-more-info="openRequestInfo"
                    @reject="openReject"
                    @hire="openHire"
                    @open-modal="handlePipelineModal"
                  />
                </div>
              </div>
            </div>
          </section>

          <!-- Professional Skills -->
          <section class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h4 class="text-base font-semibold text-gray-800 mb-4">Professional Skills</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
              <div
                v-for="skill in skills"
                :key="skill.id"
                class="bg-gray-50 rounded-lg p-3 shadow-sm border border-gray-100 flex flex-col justify-between"
              >
                <div class="flex items-start justify-between mb-2">
                  <div class="flex items-center gap-2">
                    <h3 class="text-base font-medium text-gray-900">
                      {{ skill.skill_name }}
                    </h3>
                  </div>
                  <span
                  class="inline-flex px-3 py-1 rounded-full text-xs font-medium"
                  :class="{
                    'bg-blue-100 text-blue-800': skill.proficiency_type === 'Beginner',
                    'bg-green-100 text-green-800': skill.proficiency_type === 'Intermediate',
                    'bg-purple-100 text-purple-800': skill.proficiency_type === 'Advanced',
                    'bg-indigo-100 text-indigo-800': skill.proficiency_type === 'Expert'
                  }"
                  >
                  {{ skill.proficiency_type || skill.graduate_skills_proficiency_type }}
                </span>
              </div>
              <div class="text-xs text-gray-500 font-normal">
                ({{ skill.years_experience || skill.graduate_skills_years_experience || 0 }} yr<span v-if="(skill.years_experience || skill.graduate_skills_years_experience || 0) != 1">s</span>)
              </div>
                <div class="w-full bg-gray-200 rounded-full h-2 mt-2 mb-2">
                  <div
                    class="h-2 rounded-full transition-all duration-300"
                    :class="{
                      'w-1/4 bg-blue-500': (skill.proficiency_type || skill.graduate_skills_proficiency_type) === 'Beginner',
                      'w-2/4 bg-green-500': (skill.proficiency_type || skill.graduate_skills_proficiency_type) === 'Intermediate',
                      'w-3/4 bg-purple-500': (skill.proficiency_type || skill.graduate_skills_proficiency_type) === 'Advanced',
                      'w-full bg-indigo-500': (skill.proficiency_type || skill.graduate_skills_proficiency_type) === 'Expert'
                    }"
                  ></div>
                </div>
              </div>
            </div>
          </section>

          <!-- Profile Detail Tabs -->
          <section class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex flex-wrap gap-2 border-b pb-2 mb-4">
              <button
                v-for="t in tabs"
                :key="t.key"
                @click="setTab(t.key)"
                class="px-3 py-1.5 text-xs font-medium rounded border transition"
                :class="activeTab === t.key
                  ? 'bg-indigo-600 text-white border-indigo-600'
                  : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'"
              >
                {{ t.label }}
              </button>
            </div>

            <!-- Education -->
            <div v-if="activeTab === 'education'">
              <h4 class="text-base font-semibold text-gray-800 mb-3">Education</h4>
              <div v-if="education.length" class="space-y-4">
                <div
                  v-for="ed in education"
                  :key="ed.id"
                  class="border border-gray-100 rounded-lg p-4 bg-gray-50"
                >
                  <div class="flex justify-between items-start gap-4 flex-wrap">
                    <div>
                      <div class="text-sm font-semibold text-gray-800">
                        {{ ed.education || ed.degree || 'Education' }}
                      </div>
                      <div class="text-xs text-gray-600">
                        {{ ed.field_of_study || ed.program || '' }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ ed.institution || 'Institution N/A' }}
                      </div>
                    </div>
                    <div class="text-xs text-gray-500 text-right">
                      <div v-if="ed.school_year">{{ ed.school_year }}</div>
                      <div v-else>
                        {{ ed.start_date ? formatDate(ed.start_date) : 'N/A' }} -
                        {{ ed.end_date ? formatDate(ed.end_date) : 'N/A' }}
                      </div>
                      <div v-if="ed.graduation_year">Grad: {{ ed.graduation_year }}</div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-sm text-gray-500 italic">No education records.</div>
            </div>

            <!-- Work Experience -->
            <div v-else-if="activeTab === 'experience'">
              <h4 class="text-base font-semibold text-gray-800 mb-3">Work Experience</h4>
              <div v-if="experiences.length" class="space-y-4">
                <div
                  v-for="exp in experiences"
                  :key="exp.id"
                  class="border border-gray-100 rounded-lg p-4 bg-gray-50"
                >
                  <div class="flex justify-between gap-4 flex-wrap">
                    <div>
                      <div class="text-sm font-semibold text-gray-800">
                        {{ exp.title || exp.position || 'Job Title' }}
                      </div>
                      <div class="text-xs text-gray-600">
                        {{ exp.company_name || exp.company || 'Company N/A' }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ exp.address || '' }}
                      </div>
                    </div>
                    <div class="text-xs text-gray-500 text-right">
                      {{ exp.start_date ? formatDate(exp.start_date) : 'N/A' }} -
                      <span>
                        {{ exp.is_current ? 'Present' : (exp.end_date ? formatDate(exp.end_date) : 'N/A') }}
                      </span>
                    </div>
                  </div>
                  <div v-if="exp.description" class="mt-2 text-xs text-gray-600 whitespace-pre-line">
                    {{ exp.description }}
                  </div>
                </div>
              </div>
              <div v-else class="text-sm text-gray-500 italic">No work experience.</div>
            </div>

            <!-- Certifications & Achievements -->
            <div v-else-if="activeTab === 'certs'">
              <h4 class="text-base font-semibold text-gray-800 mb-4">Certifications & Achievements</h4>

              <!-- Certifications Section (mirrors Certification.vue style) -->
              <div class="mb-8">
                <div class="text-sm font-semibold text-gray-700 mb-3">Certifications</div>

                <div v-if="certifications.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div
                    v-for="c in certifications"
                    :key="'cert-' + (c.id || c.name || c.title)"
                    class="bg-white border border-blue-100 rounded-lg p-4 shadow-sm hover:shadow transition"
                  >
                    <div class="flex justify-between items-start gap-4">
                      <div class="flex-1 space-y-2">
                        <div class="border-b border-blue-100 pb-1">
                          <h3 class="text-base font-semibold text-blue-900">
                            {{ c.name || c.title || 'Certification' }}
                          </h3>
                          <p class="text-xs text-gray-600 flex items-center mt-0.5">
                            <i class="fas fa-certificate text-blue-500 mr-1"></i>
                            {{ c.issuer || c.organization || 'Issuer N/A' }}
                          </p>
                        </div>

                        <!-- Issue / Expiry -->
                        <div class="flex items-center text-[11px] text-gray-600 bg-blue-50 px-2 py-1 rounded-full w-fit">
                          <i class="far fa-calendar-alt mr-1 text-blue-600"></i>
                          <span>
                            {{ c.issue_date ? formatDate(c.issue_date) : (c.issued_at ? formatDate(c.issued_at) : 'N/A') }}
                            -
                            {{
                              (c.expiry_date === null || c.expiry_date === '' || c.noExpiryDate)
                                ? 'No Expiry'
                                : formatDate(c.expiry_date)
                            }}
                          </span>
                        </div>

                        <!-- Credential URL -->
                        <div class="text-xs">
                          <span class="font-semibold text-gray-700">
                            <i class="fas fa-link text-blue-600 mr-1"></i> Credential URL:
                          </span>
                          <span v-if="c.credential_url">
                            <a
                              :href="c.credential_url.startsWith('http') ? c.credential_url : 'https://' + c.credential_url"
                              target="_blank"
                              class="text-indigo-600 hover:underline break-all"
                            >
                              {{ c.credential_url }}
                            </a>
                          </span>
                          <span v-else class="text-gray-500">No credential URL provided</span>
                        </div>

                        <!-- Credential ID -->
                        <div class="text-xs bg-gray-50 p-2 rounded-md border border-gray-100">
                          <span class="font-semibold text-gray-700">
                            <i class="fas fa-id-badge text-blue-600 mr-1"></i> Credential ID:
                          </span>
                          <span class="font-mono">
                            {{ c.credential_id || 'N/A' }}
                          </span>
                        </div>

                        <!-- Certificate File -->
                        <div v-if="c.file_path || c.file || c.document_path" class="pt-1">
                          <a
                            :href="c.file_path ? `/storage/${c.file_path}` : (c.file ? `/storage/${c.file}` : `/storage/${c.document_path}`)"
                            target="_blank"
                            class="inline-flex items-center text-xs text-blue-600 hover:underline bg-blue-50 px-2 py-1 rounded border border-blue-100"
                          >
                            <i class="fas fa-file-pdf mr-1"></i> View Certificate
                          </a>
                        </div>
                      </div>

                      <!-- Optional date badge (issue date only) -->
                      <div v-if="c.issue_date || c.issued_at" class="text-[10px] text-gray-500 text-right min-w-[70px]">
                        {{ formatDate(c.issue_date || c.issued_at) }}
                      </div>
                    </div>
                  </div>
                </div>

                <div v-else class="text-xs text-gray-500 italic">No certifications.</div>
              </div>

              <!-- Achievements Section (mirrors Achievement.vue style) -->
              <div>
                <div class="text-sm font-semibold text-gray-700 mb-3">Achievements</div>

                <div v-if="achievements.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div
                    v-for="a in achievements"
                    :key="'ach-' + (a.id || a.title || a.name)"
                    class="bg-white border border-amber-100 rounded-lg p-4 shadow-sm hover:shadow transition"
                  >
                    <div class="flex justify-between items-start gap-4">
                      <div class="flex-1 space-y-2">
                        <div class="border-b border-amber-100 pb-1">
                          <h3 class="text-base font-semibold text-amber-800">
                            {{ a.title || a.graduate_achievement_title || a.name || 'Achievement' }}
                          </h3>
                          <p v-if="a.type || a.graduate_achievement_type" class="text-[10px] inline-block px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-semibold">
                            {{ a.type || a.graduate_achievement_type }}
                          </p>
                          <p v-if="a.issuer || a.graduate_achievement_issuer || a.organization" class="text-xs text-gray-600 mt-1">
                            <i class="fas fa-university text-amber-600 mr-1"></i>
                            {{ a.issuer || a.graduate_achievement_issuer || a.organization }}
                          </p>
                        </div>

                        <!-- Date -->
                        <div class="flex items-center text-[11px] text-gray-600 bg-amber-50 px-2 py-1 rounded-full w-fit">
                          <i class="far fa-calendar-alt mr-1 text-amber-600"></i>
                          <span>
                            {{
                              a.date
                                ? formatDate(a.date)
                                : (a.graduate_achievement_date
                                    ? formatDate(a.graduate_achievement_date)
                                    : (a.awarded_at
                                        ? formatDate(a.awarded_at)
                                        : (a.created_at ? formatDate(a.created_at) : 'N/A')))
                            }}
                          </span>
                        </div>

                        <!-- Description -->
                        <div
                          v-if="a.description || a.graduate_achievement_description || a.details || a.summary"
                          class="text-xs text-gray-600 whitespace-pre-line"
                        >
                          {{ a.description || a.graduate_achievement_description || a.details || a.summary }}
                        </div>

                        <!-- URL -->
                        <div class="text-xs">
                          <span class="font-semibold text-gray-700">
                            <i class="fas fa-link text-amber-600 mr-1"></i> URL:
                          </span>
                          <span v-if="a.url || a.graduate_achievement_url">
                            <a
                              :href="(a.url || a.graduate_achievement_url).startsWith('http') ? (a.url || a.graduate_achievement_url) : 'https://' + (a.url || a.graduate_achievement_url)"
                              target="_blank"
                              class="text-indigo-600 hover:underline break-all"
                            >
                              {{ a.url || a.graduate_achievement_url }}
                            </a>
                          </span>
                          <span v-else class="text-gray-500">No URL</span>
                        </div>

                        <!-- Image -->
                        <div v-if="a.credential_picture || a.credential_picture_url || a.picture_url || a.image || a.photo" class="mt-2">
                          <img
                            :src="a.credential_picture
                                  ? `/storage/${a.credential_picture}`
                                  : (a.credential_picture_url
                                      ? `/storage/${a.credential_picture_url}`
                                      : (a.picture_url || a.image_url || (a.image ? `/storage/${a.image}` : (a.photo ? `/storage/${a.photo}` : ''))))"
                            alt="Achievement Image"
                            class="max-h-40 rounded border object-contain shadow-sm"
                          />
                        </div>
                      </div>

                      <!-- Date (small badge on side optional) -->
                      <div class="text-[10px] text-gray-500 text-right min-w-[70px]">
                        {{
                          a.date
                            ? formatDate(a.date)
                            : (a.graduate_achievement_date
                                ? formatDate(a.graduate_achievement_date)
                                : (a.awarded_at
                                    ? formatDate(a.awarded_at)
                                    : (a.created_at ? formatDate(a.created_at) : '')))

                        }}
                      </div>
                    </div>
                  </div>
                </div>

                <div v-else class="text-xs text-gray-500 italic">No achievements.</div>
              </div>
            </div>

            <!-- All Skills -->
            <div v-else-if="activeTab === 'skills'">
              <h4 class="text-base font-semibold text-gray-800 mb-3">All Skills </h4>

              <div v-if="groupedSkills.length" class="space-y-8">
                <div
                  v-for="group in groupedSkills"
                  :key="group.label"
                  class="space-y-4"
                >
                  <div class="flex items-center gap-2">
                    <h5 class="text-sm font-semibold text-indigo-700 tracking-wide">
                      {{ group.label }} ({{ group.items.length }})
                    </h5>
                    <div class="h-px flex-1 bg-gradient-to-r from-indigo-200 to-transparent"></div>
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <div
                      v-for="skill in group.items"
                      :key="skill.id"
                      class="bg-gray-50 rounded-lg p-3 shadow-sm border border-gray-100"
                    >
                      <div class="flex items-start justify-between mb-1">
                        <div class="text-sm font-medium text-gray-800">
                          {{ skill.skill_name }}
                        </div>
                        <span
                          class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-medium"
                          :class="{
                            'bg-blue-100 text-blue-800': (skill.proficiency_type || skill.graduate_skills_proficiency_type) === 'Beginner',
                            'bg-green-100 text-green-800': (skill.proficiency_type || skill.graduate_skills_proficiency_type) === 'Intermediate',
                            'bg-purple-100 text-purple-800': (skill.proficiency_type || skill.graduate_skills_proficiency_type) === 'Advanced',
                            'bg-indigo-100 text-indigo-800': (skill.proficiency_type || skill.graduate_skills_proficiency_type) === 'Expert'
                          }"
                        >
                          {{ skill.proficiency_type || skill.graduate_skills_proficiency_type || 'N/A' }}
                        </span>
                      </div>

                      <div class="text-[11px] text-gray-500 mb-2">
                        {{ (skill.years_experience || skill.graduate_skills_years_experience || 0) }} yr<span
                          v-if="(skill.years_experience || skill.graduate_skills_years_experience || 0) != 1"
                        >s</span>
                      </div>

                      <div class="w-full bg-gray-200 rounded-full h-1.5">
                        <div
                          class="h-1.5 rounded-full transition-all duration-300"
                          :class="{
                            'w-1/4 bg-blue-500': (skill.proficiency_type || skill.graduate_skills_proficiency_type) === 'Beginner',
                            'w-2/4 bg-green-500': (skill.proficiency_type || skill.graduate_skills_proficiency_type) === 'Intermediate',
                            'w-3/4 bg-purple-500': (skill.proficiency_type || skill.graduate_skills_proficiency_type) === 'Advanced',
                            'w-full bg-indigo-500': (skill.proficiency_type || skill.graduate_skills_proficiency_type) === 'Expert'
                          }"
                        ></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else class="text-sm text-gray-500 italic">No skills listed.</div>
            </div>

            <!-- Supporting Documents -->
            <div v-else-if="activeTab === 'documents'">
              <h4 class="text-base font-semibold text-gray-800 mb-4">Supporting Documents</h4>

              <!-- Resume -->
              <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                  <div class="font-semibold text-sm text-gray-800">Resume</div>
                  <a
                    v-if="resumeDoc && resumeDoc.file_url"
                    :href="resumeDoc.file_url"
                    download
                    target="_blank"
                    class="text-indigo-600 text-xs hover:underline font-medium"
                  >Download</a>
                </div>
                <div v-if="resumeDoc && resumeDoc.file_url" class="bg-gray-50 rounded p-4 border">
                  <div class="flex items-center gap-2 mb-3">
                    <i v-if="resumeDoc.file_type && resumeDoc.file_type.includes('pdf')" class="fas fa-file-pdf text-xl text-red-500"></i>
                    <i v-else-if="resumeDoc.file_type && resumeDoc.file_type.includes('word')" class="fas fa-file-word text-xl text-blue-500"></i>
                    <i v-else class="fas fa-file-alt text-xl text-gray-500"></i>
                    <span class="text-sm font-semibold">{{ resumeDoc.file_name }}</span>
                  </div>
                  <div
                    v-if="resumeDoc.file_type && resumeDoc.file_type.includes('pdf')"
                    class="border rounded overflow-hidden h-64"
                  >
                    <iframe :src="resumeDoc.file_url" width="100%" height="100%"></iframe>
                  </div>
                </div>
                <div v-else class="text-xs text-gray-500 italic">No resume uploaded.</div>
              </div>

              <!-- Cover Letter -->
              <div class="mb-6">
                <div class="font-semibold text-sm text-gray-800 mb-2">Cover Letter</div>
                <div v-if="coverLetterDoc && coverLetterDoc.file_url" class="bg-gray-50 p-4 rounded border">
                  <a :href="coverLetterDoc.file_url" target="_blank" class="text-indigo-600 text-sm hover:underline">
                    View Cover Letter
                  </a>
                </div>
                <div v-else class="text-xs text-gray-500 italic">No cover letter provided.</div>
              </div>

              <!-- Referral Certificates -->
              <div class="mb-6">
                <div class="font-semibold text-sm text-gray-800 mb-2">Referral Certificates</div>

                <div v-if="referralCerts.length" class="space-y-3">
                  <div
                    v-for="rc in referralCerts"
                    :key="rc.id"
                    class="border rounded p-3 bg-gray-50 flex items-start justify-between gap-4"
                  >
                    <div class="space-y-1">
                      <div class="text-sm font-medium text-gray-800 flex items-center gap-2">
                        <i class="fas fa-file-certificate text-indigo-500"></i>
                        {{ rc.file_name || 'Certificate' }}
                      </div>
                      <div class="text-[11px] text-gray-500">
                        Uploaded: {{ rc.uploaded_at ? formatShortDate(rc.uploaded_at) : 'N/A' }}
                      </div>
                      <div v-if="rc.file_url" class="flex gap-3 text-xs mt-1">
                        <a :href="rc.file_url" target="_blank" class="text-indigo-600 hover:underline font-medium">View</a>
                        <a :href="rc.file_url" download class="text-indigo-600 hover:underline font-medium">Download</a>
                      </div>
                      <div v-else class="text-xs text-red-500">
                        File not publicly accessible. 
                      </div>
                    </div>
                  </div>
                </div>

                <div v-else class="text-xs text-gray-500 italic">
                  No referral certificates uploaded.
                </div>
              </div>

              <!-- Testimonials / Alumni Stories -->
              <div class="mb-2">
                <div class="font-semibold text-sm text-gray-800 mb-2">Testimonials / Alumni Stories</div>
                <div v-if="testimonialsList.length || alumniStories.length" class="space-y-3">
                  <div
                    v-for="t in testimonialsList"
                    :key="'testimonial-' + t.id"
                    class="border rounded p-3 bg-gray-50"
                  >
                    <div class="text-xs text-gray-500 mb-1">
                      {{ formatShortDate(t.created_at) }}
                    </div>
                    <div class="text-sm text-gray-700 whitespace-pre-line">
                      {{ t.content || t.text || t.body }}
                    </div>
                  </div>
                </div>
                <div v-else class="text-xs text-gray-500 italic">No testimonials or alumni stories.</div>
              </div>
            </div>

            <!-- Career Profile -->
            <div v-else-if="activeTab === 'career'">
              <h4 class="text-base font-semibold text-gray-800 mb-4">Career Profile</h4>
              <div v-if="careerGoals">
                <div class="mb-4">
                  <div class="text-sm font-semibold text-gray-700 mb-1">Short Term Goal</div>
                  <div class="text-sm text-gray-600 whitespace-pre-line">
                    {{ careerGoals.short_term_goal || 'Not specified' }}
                  </div>
                </div>
                <div class="mb-4">
                  <div class="text-sm font-semibold text-gray-700 mb-1">Long Term Goal</div>
                  <div class="text-sm text-gray-600 whitespace-pre-line">
                    {{ careerGoals.long_term_goal || 'Not specified' }}
                  </div>
                </div>
                <div>
                  <div class="text-sm font-semibold text-gray-700 mb-1">Motivation</div>
                  <div class="text-sm text-gray-600 whitespace-pre-line">
                    {{ careerGoals.motivation || 'Not specified' }}
                  </div>
                </div>
              </div>
              <div v-else class="text-sm text-gray-500 italic">No career profile data.</div>
            </div>
          </section>

          <!-- Confirmation Modal -->
          <div v-if="showConfirmationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
            <div class="bg-white rounded-xl shadow-lg w-96 p-6 relative flex flex-col items-center">
              <div class="text-2xl text-green-600 mb-2">
                <i class="fas fa-check-circle"></i>
              </div>
              <div class="text-lg font-semibold mb-2 text-center">
                Interview Scheduled!
              </div>
              <div class="text-gray-600 mb-4 text-center">
                The interview date has been set successfully.
              </div>
              <button @click="showConfirmationModal = false" class="px-4 py-2 rounded bg-indigo-600 text-white">
                OK
              </button>
            </div>
          </div>

          <div v-if="showHireConfirmation" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
            <div class="bg-white rounded-xl shadow-lg w-96 p-6 relative flex flex-col items-center">
              <div class="text-2xl text-green-600 mb-2">
                <i class="fas fa-check-circle"></i>
              </div>
              <div class="text-lg font-semibold mb-2 text-center">
                Successfully hired {{ props.applicant.graduate.first_name }} {{ props.applicant.graduate.last_name }}!
              </div>
              <button @click="showHireConfirmation = false" class="px-4 py-2 rounded bg-green-600 text-white mt-2">
                OK
              </button>
            </div>
          </div>

          <RequestMoreInfoModal
            :show="showRequestInfoModal"
            :application-id="applicant.id"
            :receiver-id="applicant.graduate.user_id"
            @close="showRequestInfoModal = false"
            @request-sent="handleRequestInfoSent"
          />

          
          <!-- Exam Instructions Modal -->
           <ExamInstructionsModal
            :show="showExamInstructions"
            :application-id="applicant.id"
            :receiver-id="applicant.graduate.user_id"
            @close="showExamInstructions = false"
            @sent="onExamInstructionsSent"
          />

          <!-- Exam Result Modal -->
          <ExamResultModal
            :show="showExamResult"
            :application-id="applicant.id"
            @close="showExamResult = false"
            @saved="onExamResultSaved"
          />

          <!-- Exam Reschedule Modal -->
          <ExamRescheduleModal
            :show="showExamReschedule"
            :application-id="applicant.id"
            :receiver-id="applicant.graduate.user_id"
            @close="showExamReschedule = false"
            @sent="onExamRescheduleSent"
          />

          <!-- Interview Schedule Modal -->
          <InterviewScheduleModal
            :show="showInterviewSchedule"
            :application-id="applicant.id"
            :receiver-id="applicant.graduate.user_id"
            :applicant-name="applicant.graduate.first_name"
            @close="showInterviewSchedule = false"
            @sent="onInterviewInvitationSent"
          />

          <!-- Interview Reschedule Modal -->
          <InterviewRescheduleModal
            :show="showInterviewReschedule"
            :application-id="applicant.id"
            :receiver-id="applicant.graduate.user_id"
            @close="showInterviewReschedule = false"
            @sent="onInterviewRescheduled"
          />

          <!-- Interview Feedback Modal -->
          <InterviewFeedbackModal
            :show="showInterviewFeedback"
            :application-id="applicant.id"
            @close="showInterviewFeedback = false"
            @saved="onInterviewFeedbackSaved"
          />

          <HireConfirmModal
            :show="showHireModal"
            :application-id="applicant.id"
            :applicant-name="applicant.graduate.first_name + ' ' + applicant.graduate.last_name"
            @close="showHireModal = false"
            @hired="onHired"
          />

          <RejectConfirmModal
            :show="showRejectModal"
            :application-id="applicant.id"
            :applicant-name="applicant.graduate.first_name + ' ' + applicant.graduate.last_name"
            :action-key="rejectActionKey"
            @close="showRejectModal = false"
            @rejected="onRejected"
          />

          <!-- New Send Offer Modal -->
          <OfferSendModal
            :show="showOfferSendModal"
            :application-id="applicant.id"
            :receiver-id="applicant.graduate.user_id"
            :job-title-default="applicant.job?.job_title || 'Position'"
            @close="showOfferSendModal = false"
            @sent="onOfferSent"
          />
        </main>
      </div>
    </div>
  </AppLayout>
</template>