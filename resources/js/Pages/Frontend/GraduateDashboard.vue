<script setup>
import { defineProps, onMounted, ref } from "vue";
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import ProfileCardDashboard from '@/Components/Graduate/ProfileCardDashboard.vue';
import { router } from '@inertiajs/vue3';



const props = defineProps({
  summary: Object,
  kpi: Object,
  recommendedJobs: Array,
  graduate: Object,
  aboutMe: String,
  currentJob: Object,
  highestEducation: Object,
  employmentPreferences: Object,
  careerGoals: Object,
  featuredCompanies: Array,

});


const recentActivities = [
  'Applied for Software Engineer at TechCorp',
  'Referred Dustin Green for Marketing Specialist at MarketInc',
  'Updated profile information',
  'Viewed job details for Data Analyst at DataSolutions',
];
console.log('KPI:', props.kpi);
console.log('Jobs', props.recommendedJobs)
console.log('Graduate', props.graduate)

const isViewDetailsModalOpen = ref(false);
const isApplyModalOpen = ref(false);
const selectedJob = ref(null);
const successMessage = ref('');
const isSuccessModalOpen = ref(false);

const isCompanyModalOpen = ref(false);
const selectedCompany = ref(null);

function viewCompanyDetails(company) {
  selectedCompany.value = company;
  isCompanyModalOpen.value = true;
}
function closeCompanyModal() {
  isCompanyModalOpen.value = false;
  selectedCompany.value = null;
}

// Show job details modal
function viewJobDetails(job) {
  selectedJob.value = job;
  isViewDetailsModalOpen.value = true;
}
function goToCompanyProfile(companyId) {
  if (companyId) {
    router.visit(route('company.profile.public', { id: companyId }));
  }
}
// Show apply modal


// Close modals
function closeDetailsModal() {
  isViewDetailsModalOpen.value = false;
  selectedJob.value = null;
}
function closeApplyModal() {
  isApplyModalOpen.value = false;
  selectedJob.value = null;
}
function closeSuccessModal() {
  isSuccessModalOpen.value = false;
}

const applyForm = useForm({ job_id: null, cover_letter: '', cover_letter_file: null });


function showApplyModal(job) {
  selectedJob.value = job;
  applyForm.job_id = job.id;
  applyForm.cover_letter = '';
  isApplyModalOpen.value = true;
}

function onCoverLetterFileChange(e) {
  applyForm.cover_letter_file = (e.target.files && e.target.files[0]) ? e.target.files[0] : null;
}

function submitApplication() {
  applyForm.post(route('apply-for-job'), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      successMessage.value = 'Successfully applied for the job and Referral sent to PESO!';
      isApplyModalOpen.value = false;
      isSuccessModalOpen.value = true;
    },
    onError: () => {
      errorMessage.value = 'Failed to apply for the job. Please try again.';
      isErrorModalOpen.value = true;
    }
  });
}

onMounted(() => {
  console.log('Summary:', props.summary);

});
</script>

<template>
  <div class="flex min-h-screen bg-gray">


    <!-- Main Content -->
    <main class="flex-1 flex flex-col">
      <!-- KPI Cards -->
      <div class="max-w-7xl mx-auto w-full px-4 py-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Jobs Applied -->
        <div
          class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-6 flex flex-col items-center justify-center min-h-[120px]">
          <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mb-2">
            <i class="fas fa-file-alt text-white"></i>
          </div>
          <span class="text-blue-700 text-xs font-medium text-center">Jobs Applied</span>
          <span class="text-blue-900 text-3xl font-bold">{{ kpi.jobsApplied ?? 0 }}</span>
        </div>
        <!-- Referrals Made -->
        <div
          class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 flex flex-col items-center justify-center min-h-[120px]">
          <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mb-2">
            <i class="fas fa-share-square text-white"></i>
          </div>
          <span class="text-green-700 text-xs font-medium text-center">Referrals Made</span>
          <span class="text-green-900 text-3xl font-bold">{{ kpi.referralsMade ?? 0 }}</span>
        </div>
        <!-- Jobs Aligned -->
        <div
          class="bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-2xl p-6 flex flex-col items-center justify-center min-h-[120px]">
          <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center mb-2">
            <i class="fas fa-check-circle text-white"></i>
          </div>
          <span class="text-indigo-700 text-xs font-medium text-center">Jobs Aligned</span>
          <span class="text-indigo-900 text-3xl font-bold">{{ kpi.jobsAligned ?? 0 }}</span>
        </div>
      </div>

      <div class="max-w-7xl mx-auto w-full px-4 grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Left: Profile Card & Recent Activities -->
        <div class="lg:col-span-1 space-y-6">
          <ProfileCardDashboard :graduate="graduate" :about-me="aboutMe" />
          <!-- Recent Activities (placeholder) -->
          <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-gray-700 mb-3 flex items-center">
              <i class="fas fa-history mr-2 text-indigo-500"></i>
              Recent Activities
            </h3>
            <ul class="text-sm space-y-2">
              <li v-for="(activity, idx) in recentActivities" :key="idx" class="flex items-center">
                <i class="fas fa-dot-circle text-indigo-400 mr-2"></i>
                <span>{{ activity }}</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Center: Chart & Jobs -->
        <div class="lg:col-span-3 space-y-8">
          <!-- Chart Placeholder -->
          <div class="bg-white rounded-lg shadow p-6 mb-4">
            <h3 class="font-semibold text-gray-700 mb-3 flex items-center">
              <i class="fas fa-chart-line mr-2 text-indigo-500"></i>
              Vacancy Stats
            </h3>
            <div class="h-48 flex items-center justify-center text-gray-400">
              <!-- Replace with your chart component -->
              <span>Chart Placeholder</span>
            </div>
          </div>

          <!-- Jobs Based on Preference -->
          <div>
            <h2 class="text-lg font-semibold mb-4">Jobs Based on Preference</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 ">
              <div v-for="job in recommendedJobs" :key="job.id"
                class="bg-white rounded-lg shadow p-4 flex flex-col justify-between transition-all duration-300 hover:shadow-lg border border-indigo-50">
                <div class="flex items-center mb-2">
                  <img v-if="job.logo" :src="job.logo" alt="Logo" class="h-8 w-8 rounded-full mr-2" />
                  <span class="font-bold text-base truncate">{{ job.job_title }}</span>
                </div>
                <div class="text-sm text-gray-600 mb-1">{{ job.company.company_name }}</div>
                <div class="flex items-center text-xs text-gray-500 mb-1">
                  <i class="fas fa-briefcase mr-1"></i> {{ job.job_experience_level }}
                </div>
                <div class="flex items-center text-xs text-gray-500 mb-1">
                  <i class="fas fa-map-marker-alt mr-1"></i> {{ job.location }}
                </div>
                <div class="flex justify-between items-center mt-auto">
                  <PrimaryButton @click="viewJobDetails(job)" class="text-xs bg-green-600 hover:bg-green-700">
                    View Details
                  </PrimaryButton>
                </div>
                <div class="text-xs text-gray-400 mt-2">{{ job.posted_at }}</div>
              </div>
            </div>
          </div>

          <div class="mt-10">
            <h2 class="text-lg font-semibold mb-4">Featured Companies</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div v-for="company in featuredCompanies" :key="company.id"
                class="bg-white rounded-lg shadow p-4 flex flex-col items-center transition-all duration-300 hover:shadow-lg border border-indigo-50">
                <img v-if="company.logo" :src="`/storage/${company.logo}`" alt="Logo"
                  class="h-12 w-12 rounded-full mb-2 object-cover" />
                <div class="font-bold text-base text-center mb-1">{{ company.company_name }}</div>
                <div class="text-xs text-gray-500 mb-2">{{ company.location }}</div>
                <div class="text-xs text-gray-400 mb-2">{{ company.jobs_count }} Vacancy</div>
                <PrimaryButton v-if="company.id" @click="goToCompanyProfile(company.id)"
                  class="text-xs bg-indigo-600 hover:bg-indigo-700">
                  View Company
                </PrimaryButton>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
  <Modal :modelValue="isViewDetailsModalOpen" @close="closeDetailsModal" max-width="2xl">
    <template #body>
      <div class="p-6" v-if="selectedJob">
        <div class="flex justify-between items-start mb-6">
          <div>
            <h2 class="text-xl font-bold text-gray-900">{{ selectedJob.job_title }}</h2>
            <span class="px-2 py-1 text-xs rounded-full font-semibold bg-blue-100 text-blue-800 border border-blue-200"
              v-if="selectedJob.match_percentage !== undefined">
              {{ selectedJob.match_percentage }}% Match
            </span>
            <p class="text-md text-gray-600">
              <template v-if="selectedJob.company">
                {{ selectedJob.company.company_name }}
              </template>
              <template v-else>
                Unknown
              </template>
            </p>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
          <div class="flex items-center text-gray-600">
            <i class="fas fa-map-marker-alt mr-2"></i>
            <span>
              <template v-if="selectedJob.location && selectedJob.location.length">
                {{ selectedJob.location }}
              </template>
              <template v-else>
                Not specified
              </template>
            </span>
          </div>
          <div class="flex items-center text-gray-600">
            <i class="fas fa-briefcase mr-2"></i>
            <span>
              <template v-if="selectedJob.jobTypes && selectedJob.jobTypes.length">
                {{selectedJob.jobTypes.map(jt => jt.type).join(', ')}}
              </template>
              <template v-else>
                Not specified
              </template>
            </span>
          </div>
          <div class="flex items-center text-gray-600">
            <i class="fas fa-clock mr-2"></i>
            {{ selectedJob.job_experience_level || 'Not specified' }}
          </div>
          <div class="flex items-center text-gray-600">
            <i class="fas fa-peso-sign mr-2"></i>
            {{ selectedJob.salary ? `₱${selectedJob.salary.job_min_salary} -
            ₱${selectedJob.salary.job_max_salary}
            (${selectedJob.salary.salary_type})` : 'Not specified' }}
          </div>
        </div>
        <div class="mb-6">
          <h3 class="text-lg font-semibold mb-2">Job Description</h3>
          <p class="text-gray-700 whitespace-pre-line">{{ selectedJob.job_description || 'Not specified.' }}
          </p>
        </div>
        <div class="mb-6" v-if="selectedJob.job_roles">
          <h3 class="text-lg font-semibold mb-2">Roles</h3>
          <p class="text-gray-700 whitespace-pre-line">{{ selectedJob.job_roles }}</p>
        </div>
        <div class="mb-6" v-if="selectedJob.job_responsibilities">
          <h3 class="text-lg font-semibold mb-2">Responsibilities</h3>
          <ul class="list-disc pl-5 text-gray-700">
            <li v-for="(resp, idx) in selectedJob.job_responsibilities.split('\n').filter(r => r.trim())" :key="idx">
              {{ resp }}
            </li>
          </ul>
        </div>
        <div class="mb-6" v-if="selectedJob.job_qualifications">
          <h3 class="text-lg font-semibold mb-2">Required Qualifications</h3>
          <ul class="list-disc pl-5 text-gray-700">
            <li v-for="(qual, idx) in selectedJob.job_qualifications.split('\n').filter(q => q.trim())" :key="idx">
              {{ qual }}
            </li>
          </ul>
        </div>
        <div class="mb-6" v-if="selectedJob.job_benefits">
          <h3 class="text-lg font-semibold mb-2">Benefits</h3>
          <ul class="list-disc pl-5 text-gray-700">
            <li v-for="(benefit, idx) in selectedJob.job_benefits.split('\n').filter(b => b.trim())" :key="idx">
              {{ benefit }}
            </li>
          </ul>
        </div>
        <div class="mb-6" v-if="selectedJob.skills && selectedJob.skills.length">
          <h3 class="text-lg font-semibold mb-2">Required Skills</h3>
          <div class="flex flex-wrap gap-2">
            <span v-for="skill in Array.isArray(selectedJob.skills)
              ? selectedJob.skills
              : selectedJob.skills.split(',').map(s => s.trim()).filter(Boolean)" :key="skill"
              class="px-3 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 rounded-full">
              {{ skill }}
            </span>
          </div>
        </div>
        <div class="flex justify-end space-x-3">
          <button @click="closeDetailsModal"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
            Close
          </button>
          <PrimaryButton @click="showApplyModal(selectedJob)" class="bg-green-600 hover:bg-green-700">
            Apply Now
          </PrimaryButton>
        </div>
      </div>
    </template>
  </Modal>

  <Modal :modelValue="isApplyModalOpen" @close="closeApplyModal" max-width="2xl">
    <template #body>
      <div class="p-6" v-if="selectedJob">
        <h2 class="text-xl font-bold mb-2">Apply for: {{ selectedJob.job_title }}</h2>
        <div class="text-gray-600 mb-2">
          <template v-if="selectedJob.company">
            {{ selectedJob.company.company_name }}
          </template>
          <template v-else>
            Unknown
          </template>
        </div>
        <div class="mb-6">
          <h3 class="text-lg font-semibold mb-2">Cover Letter</h3>
          <textarea v-model="applyForm.cover_letter"
            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="5"
            placeholder="Tell the employer why you're a good fit for this position..."></textarea>
          <div class="mt-3">
            <label class="block text-sm text-gray-700 mb-1">Upload Cover Letter (optional)</label>
            <input type="file" accept=".pdf,.doc,.docx" @change="onCoverLetterFileChange"
              class="block w-full text-sm border rounded p-1" />
            <div v-if="applyForm.cover_letter_file" class="text-xs text-gray-500 mt-1">
              {{ applyForm.cover_letter_file.name }}
            </div>
          </div>
        </div>
        <div class="flex justify-end space-x-3">
          <PrimaryButton @click="closeApplyModal" class="bg-gray-600 hover:bg-gray-700">Cancel</PrimaryButton>
          <PrimaryButton @click="submitApplication" class="bg-green-600 hover:bg-green-700">Submit Application
          </PrimaryButton>
        </div>
      </div>
    </template>
  </Modal>

  <Modal :modelValue="isSuccessModalOpen" @close="closeSuccessModal">
    <template #body>
      <div class="p-6">
        <h3 class="text-lg font-medium text-center mb-4">{{ successMessage }}</h3>
        <div class="flex justify-center">
          <PrimaryButton @click="closeSuccessModal" class="bg-indigo-600 hover:bg-indigo-700">OK
          </PrimaryButton>
        </div>
      </div>
    </template>
  </Modal>

  <Modal :modelValue="isCompanyModalOpen" @close="closeCompanyModal" max-width="2xl">
    <template #body>
      <div class="p-6" v-if="selectedCompany">
        <div class="flex items-center mb-4">
          <img v-if="selectedCompany.logo" :src="`/storage/${selectedCompany.logo}`" alt="Logo"
            class="h-16 w-16 rounded-full mr-4 object-cover" />
          <div>
            <h2 class="text-xl font-bold">{{ selectedCompany.company_name }}</h2>
            <div class="text-gray-500 text-sm">{{ selectedCompany.location }}</div>
          </div>
        </div>
        <div class="mb-4">
          <h3 class="font-semibold text-gray-700 mb-1">About</h3>
          <p class="text-gray-600 text-sm">{{ selectedCompany.description || 'No description available.' }}</p>
        </div>
        <div class="mb-4">
          <h3 class="font-semibold text-gray-700 mb-1">Open Vacancies</h3>
          <div class="text-gray-600 text-sm">{{ selectedCompany.jobs_count }} open position(s)</div>
        </div>
        <div class="flex justify-end">
          <PrimaryButton @click="closeCompanyModal" class="bg-gray-600 hover:bg-gray-700">Close</PrimaryButton>
        </div>
      </div>
    </template>
  </Modal>

</template>
