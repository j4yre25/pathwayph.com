<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { Link, router, useForm } from '@inertiajs/vue3'; // added router
import { ref, computed } from 'vue';
import '@fortawesome/fontawesome-free/css/all.css';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  job: Object,
  application: Object 
});

const activeTab = ref('details');

const statusClass = computed(() => {
  switch(props.job?.status) {
    case 'active': return 'bg-green-100 text-green-800';
    case 'draft': return 'bg-yellow-100 text-yellow-800';
    case 'closed': return 'bg-red-100 text-red-800';
    default: return 'bg-gray-100 text-gray-800';
  }
});
const statusIcon = computed(() => {
  switch(props.job?.status) {
    case 'active': return 'fa-check-circle';
    case 'draft': return 'fa-pencil-alt';
    case 'closed': return 'fa-times-circle';
    default: return 'fa-circle';
  }
});

const formatDate = (dateString) => {
  if (!dateString) return 'Not specified';
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

const bubbleColors = [
  'bg-blue-100 text-blue-800',
  'bg-green-100 text-green-800',
  'bg-yellow-100 text-yellow-800',
  'bg-purple-100 text-purple-800',
  'bg-pink-100 text-pink-800',
  'bg-indigo-100 text-indigo-800',
  'bg-red-100 text-red-800',
  'bg-teal-100 text-teal-800',
];

// Application progress
const stages = ['Applying', 'Screening', 'Interview', 'Offer', 'Hired'];

function normalizeStage(app) {
  if (!app) return { index: -1, label: 'Not applied', rejected: false };
  const stageRaw = (app.stage || '').toString().toLowerCase();
  const statusRaw = (app.status || '').toString().toLowerCase();

  if (statusRaw === 'rejected') return { index: 1, label: 'Rejected', rejected: true };
  if (statusRaw === 'hired') return { index: 4, label: 'Hired', rejected: false };
  if (statusRaw === 'applied') return { index: 0, label: 'Applying', rejected: false };
  if (statusRaw === 'shortlisted') return { index: 1, label: 'Screening', rejected: false };
  if (statusRaw === 'interview') return { index: 2, label: 'Interview', rejected: false };
  if (statusRaw === 'offered' || statusRaw === 'offer') return { index: 3, label: 'Offer', rejected: false };

  // fallback to stage
  if (stageRaw.includes('screen')) return { index: 1, label: 'Screening', rejected: false };
  if (stageRaw.includes('interview')) return { index: 2, label: 'Interview', rejected: false };
  if (stageRaw.includes('offer')) return { index: 3, label: 'Offer', rejected: false };
  if (stageRaw.includes('apply')) return { index: 0, label: 'Applying', rejected: false };

  return { index: 0, label: 'Applying', rejected: false };
}

const current = computed(() => normalizeStage(props.application));
const progressPercent = computed(() => {
  if (current.value.index < 0) return 0;
  return (current.value.index / (stages.length - 1)) * 100;
});

const hasApplied = ref(!!props.application);
const isApplying = ref(false);
const isSuccessModalOpen = ref(false);
const successMessage = ref('');
const isApplyModalOpen = ref(false);
const applyForm = useForm({ job_id: null, cover_letter: '', cover_letter_file: null })

function applyNow() {
  if (hasApplied.value) return;
  openApplyModal();
}
function closeSuccessModal() {
  isSuccessModalOpen.value = false;
}
function openApplyModal() {
  isApplyModalOpen.value = true;
}
function closeApplyModal() {
  isApplyModalOpen.value = false;
  applyForm.cover_letter = '';
}

function onCoverLetterFileChange(e) {
  applyForm.cover_letter_file = (e.target.files && e.target.files[0]) ? e.target.files[0] : null;
}

function submitApplication() {
  isApplying.value = true;
  const formData = new FormData();
  formData.append('job_id', props.job.id);
  formData.append('cover_letter', applyForm.cover_letter);
  if (applyForm.cover_letter_file) {
    formData.append('cover_letter_file', applyForm.cover_letter_file);
  }
  router.post(route('apply-for-job'), formData, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      hasApplied.value = true;
      isApplying.value = false;
      isApplyModalOpen.value = false;
      successMessage.value = 'Successfully applied for the job and Referral sent to PESO!';
      isSuccessModalOpen.value = true;
    },
    onError: () => {
      isApplying.value = false;
      isApplyModalOpen.value = false;
      successMessage.value = '';
      isSuccessModalOpen.value = false;
    }
  });
}

const goBack = () => window.history.back();

const matchLabel = computed(() => {
  const percent = props.application?.match_percentage ?? props.job?.match_percentage ?? 0;
  if (percent >= 60) return { text: 'High Match', color: 'bg-green-100 text-green-800' };
  if (percent >= 30) return { text: 'Medium Match', color: 'bg-yellow-100 text-yellow-800' };
  if (percent < 30) return { text: 'Low Match', color: 'bg-red-100 text-red-800' };
  return { text: 'No Match Data', color: 'bg-gray-100 text-gray-800' };
});
</script>

<template>
  <AppLayout title="Job Details">
    <template #header>
      <div class="flex items-center">
        <button @click="goBack" class="flex items-center text-blue-600 hover:text-blue-800 transition-colors mr-4">
          <i class="fas fa-chevron-left mr-2"></i>
        </button>
        <div>
          <div class="flex items-center">
            <i class="fas fa-briefcase text-blue-500 mr-2 text-2xl"></i>
            <h1 class="text-2xl font-bold text-gray-800">Job Details</h1>
          </div>
          <p class="text-gray-500 text-sm mt-1">View full job information and track your application</p>
        </div>
      </div>
    </template>

    <Container class="py-10">
      <!-- Header Card -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
          <div>
            <h2 class="text-xl font-semibold text-gray-800">{{ job.job_title }}</h2>
            <div class="flex flex-wrap items-center mt-2 text-sm text-gray-600">
              <div v-if="job.company" class="flex items-center mr-4 mb-2 md:mb-0">
                <i class="fas fa-building text-blue-500 mr-1"></i>
                <span>{{ job.company.company_name || job.company.name }}</span>
              </div>
              <div class="flex items-center mr-4 mb-2 md:mb-0">
                <i class="fas fa-calendar-alt text-green-500 mr-1"></i>
                <span>Posted on {{ formatDate(job.posted_at) }}</span>
              </div>
            </div>
          </div>
          <div class="mt-4 md:mt-0 flex items-center gap-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" :class="statusClass">
              <i class="fas mr-1" :class="statusIcon"></i>
              {{ job.status ? job.status.charAt(0).toUpperCase() + job.status.slice(1) : 'Unknown' }}
            </span>
            <!-- Always show Match Percentage if available -->
            <span
              class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ml-2"
              :class="matchLabel.color"
              title="Match Level"
            >
              <i class="fas fa-bullseye mr-1"></i>
              {{ matchLabel.text }}
            </span>
            <button
              v-if="!hasApplied"
              @click="applyNow"
              :disabled="isApplying"
              class="inline-flex items-center px-3 py-2 rounded bg-green-600 hover:bg-green-700 text-white text-sm disabled:opacity-60"
            >
              <i class="fas fa-paper-plane mr-2"></i>
              {{ isApplying ? 'Applying...' : 'Apply Now' }}
            </button>
            <span v-else class="text-gray-500 text-sm inline-flex items-center">
              <i class="fas fa-check-circle mr-1 text-green-500"></i> Already Applied
            </span>
          </div>
        </div>
      </div>

      <!-- Progress + Content -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 overflow-hidden p-6">
        <!-- Application Progress (only if applied) -->
        <div v-if="application" class="mb-6">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
              <i class="fas fa-route text-indigo-500 mr-2"></i>
              Application Progress
            </h3>
            <span class="text-sm" :class="current.rejected ? 'text-red-600' : 'text-gray-600'">
              {{ current.label }}
            </span>
          </div>

          <div class="relative">
            <div class="h-2 bg-gray-200 rounded">
              <div class="h-2 bg-indigo-600 rounded transition-all" :style="{ width: progressPercent + '%' }"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-600 mt-2">
              <span v-for="(s, idx) in stages" :key="s" class="flex-1 text-center"
                    :class="idx <= current.index ? 'font-semibold text-indigo-700' : ''">
                {{ s }}
              </span>
            </div>
          </div>
        </div>

        <!-- Main Content -->
        <div class="flex flex-col lg:flex-row gap-6">
          <!-- Main -->
          <div class="lg:flex-1 space-y-6">
            <!-- Quick Facts -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                  <h4 class="text-sm font-medium text-gray-500 mb-1">Job Type</h4>
                  <div class="flex items-center">
                    <i class="fas fa-business-time text-blue-500 mr-2"></i>
                    <span class="text-gray-800 font-medium">
                      <template v-if="Array.isArray(job.job_type_names) && job.job_type_names.length">
                        {{ job.job_type_names.join(', ') }}
                      </template>
                      <template v-else>
                        Not specified
                      </template>
                    </span>
                  </div>
                </div>
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                  <h4 class="text-sm font-medium text-gray-500 mb-1">Experience Level</h4>
                  <div class="flex items-center">
                    <i class="fas fa-chart-line text-purple-500 mr-2"></i>
                    <span class="text-gray-800 font-medium">{{ job.job_experience_level || 'Not specified' }}</span>
                  </div>
                </div>
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                  <h4 class="text-sm font-medium text-gray-500 mb-1">Work Environment</h4>
                  <div class="flex items-center">
                    <i class="fas fa-laptop-house text-teal-500 mr-2"></i>
                    <span class="text-gray-800 font-medium">
                      <template v-if="Array.isArray(job.work_environment_labels) && job.work_environment_labels.length">
                        {{ job.work_environment_labels.join(', ') }}
                      </template>
                      <template v-else>
                        Not specified
                      </template>
                    </span>
                  </div>
                </div>
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                  <h4 class="text-sm font-medium text-gray-500 mb-1">Vacancies</h4>
                  <div class="flex items-center">
                    <i class="fas fa-users text-green-500 mr-2"></i>
                    <span class="text-gray-800 font-medium">{{ job.job_vacancies || 1 }}</span>
                  </div>
                </div>
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                  <h4 class="text-sm font-medium text-gray-500 mb-1">Salary Range</h4>
                  <div class="flex items-center">
                    <i class="fas fa-peso-sign text-gray-400 mr-2"></i>
                    <span class="text-gray-800">{{ job.salary?.job_min_salary && job.salary?.job_max_salary
                      ? `₱${job.salary.job_min_salary} - ₱${job.salary.job_max_salary} (${job.salary.salary_type || ''})`
                      : 'Negotiable' }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Job Description -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center border-b border-gray-200 pb-3">
                <i class="fas fa-align-left text-blue-500 mr-2"></i>
                Job Description
              </h3>
              <div class="prose max-w-none text-gray-700" v-html="job.job_description"></div>
            </div>

            <!-- Job Requirements -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center border-b border-gray-200 pb-3">
                <i class="fas fa-list-check text-blue-500 mr-2"></i>
                Job Requirements
              </h3>
              <div class="prose max-w-none text-gray-700" v-html="job.job_requirements"></div>
            </div>

            <!-- Skills -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center border-b border-gray-200 pb-3">
                <i class="fas fa-code text-gray-500 mr-2"></i>
                Required Skills
              </h3>
              <div v-if="job.skills && job.skills.length > 0" class="flex flex-wrap gap-2">
                <span v-for="(skill, idx) in job.skills" :key="skill"
                      :class="['inline-flex items-center px-3 py-1 rounded-full text-sm font-medium', bubbleColors[idx % bubbleColors.length]]">
                  <i class="fas fa-check-circle mr-1 text-blue-500"></i>
                  {{ skill }}
                </span>
              </div>
              <p v-else class="text-gray-500 italic">No specific skills listed for this position</p>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="lg:w-80 space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center border-b border-gray-200 pb-3">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                Job Information
              </h3>
              <div class="space-y-4">
                <div class="bg-white p-3 rounded-lg border border-gray-200">
                  <h4 class="text-sm font-medium text-gray-500 mb-1">Posted by</h4>
                  <div class="flex items-center">
                    <i class="fas fa-id-card text-gray-400 mr-2"></i>
                    <span class="text-gray-800">{{ job.posted_by || 'N/A' }}</span>
                  </div>
                </div>

                <div v-if="job.company" class="space-y-4">
                  <div class="bg-white p-4 rounded-lg border border-gray-200">
                    <h4 class="text-sm font-medium text-gray-500 mb-1">Company Name</h4>
                    <div class="flex items-center">
                      <i class="fas fa-building text-gray-400 mr-2"></i>
                      <span class="text-gray-800">{{ job.company.company_name || job.company.name }}</span>
                    </div>
                  </div>
                  <div v-if="job.company.website" class="bg-white p-4 rounded-lg border border-gray-200">
                    <h4 class="text-sm font-medium text-gray-500 mb-1">Website</h4>
                    <div class="flex items-center">
                      <i class="fas fa-globe text-gray-400 mr-2"></i>
                      <a :href="job.company.website" target="_blank" class="text-gray-600 hover:text-gray-800 hover:underline">
                        {{ job.company.website }}
                      </a>
                    </div>
                  </div>
                </div>
                <p v-else class="text-gray-500 italic">Company information not available</p>

                <div class="bg-white p-3 rounded-lg border border-gray-200">
                  <h4 class="text-sm font-medium text-gray-500 mb-1">Location</h4>
                  <div class="flex items-center">
                    <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                    <span class="text-gray-800">
                      <template v-if="Array.isArray(job.locations) && job.locations.length">
                        {{ job.locations.map(l => l.address).join(', ') }}
                      </template>
                      <template v-else>
                        Not specified
                      </template>
                    </span>
                  </div>
                </div>
                <div class="bg-white p-4 rounded-lg border border-gray-200" v-if="job.job_deadline">
                  <h4 class="text-sm font-medium text-gray-500 mb-1">Application Deadline</h4>
                  <div class="flex items-center">
                    <i class="far fa-calendar-times text-gray-400 mr-2"></i>
                    <span>{{ formatDate(job.job_deadline) }}</span>
                  </div>
                </div>
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                  <h4 class="text-sm font-medium text-gray-500 mb-1">Posted Date</h4>
                  <div class="flex items-center">
                    <i class="far fa-calendar-plus text-gray-400 mr-2"></i>
                    <span>{{ formatDate(job.posted_at) }}</span>
                  </div>
                </div>
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                  <h4 class="text-sm font-medium text-gray-500 mb-1">Last Updated</h4>
                  <div class="flex items-center">
                    <i class="far fa-calendar-check text-gray-400 mr-2"></i>
                    <span>{{ formatDate(job.updated_at) }}</span>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- Success Modal -->
      <Modal :modelValue="isSuccessModalOpen" @close="closeSuccessModal">
        <div class="p-6">
          <div class="flex items-center justify-center mb-4">
            <div class="bg-green-100 rounded-full p-2">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor"
                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
          </div>
          <h3 class="text-lg font-medium text-center mb-4">{{ successMessage }}</h3>
          <div class="flex justify-center">
            <button @click="closeSuccessModal"
              class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              OK
            </button>
          </div>
        </div>
      </Modal>

      <!-- Apply Modal -->
      <Modal :modelValue="isApplyModalOpen" @close="closeApplyModal">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-800 mb-4">Apply for {{ job.job_title }}</h3>
          <div class="mb-4">
            <label for="cover_letter" class="block text-sm font-medium text-gray-700 mb-2">Cover Letter</label>
            <textarea id="cover_letter" v-model="applyForm.cover_letter" rows="4"
              class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none"
              placeholder="Write your cover letter here..."></textarea>
          </div>
          <!-- Dropbox for file upload -->
          <div class="mb-4">
            <label class="block text-sm text-gray-700 mb-1">Upload Cover Letter (optional)</label>
            <input type="file" accept=".pdf,.doc,.docx"
              @change="onCoverLetterFileChange"
              class="block w-full text-sm border rounded p-1" />
            <div v-if="applyForm.cover_letter_file" class="text-xs text-gray-500 mt-1">
              {{ applyForm.cover_letter_file.name }}
            </div>
          </div>
          <div class="flex justify-end gap-3">
            <button @click="closeApplyModal"
              class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 focus:outline-none">
              Cancel
            </button>
            <button @click="submitApplication"
              :disabled="isApplying"
              class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white focus:outline-none disabled:opacity-50">
              <i class="fas fa-paper-plane mr-2"></i>
              {{ isApplying ? 'Applying...' : 'Submit Application' }}
            </button>
          </div>
        </div>
      </Modal>
    </Container>
  </AppLayout>
</template>