<script setup>
import { defineProps, onMounted, ref, computed, watch } from "vue";
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import ProfileCardDashboard from '@/Components/Graduate/ProfileCardDashboard.vue';
import { router, usePage } from '@inertiajs/vue3';
import VueECharts from "vue-echarts";

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
  vacancyStats: Object,
});

function goToReferrals() {
  router.get(route('graduate.referrals'));
}

function goToJobsAligned() {
  router.get(route('job.search'));
}

function goToInterviewsScheduled() {
  router.get(route('job.search'), { tab: 'applications' });
}

// Filter toggles
const showApplicationSent = ref(true);
const showInterviews = ref(true);
const showRejected = ref(false);

// Date range filter
const dateFilter = ref("this_month"); // "this_year", "this_month", "this_week"
const dateOptions = [
  { value: "this_year", label: "This Year" },
  { value: "this_month", label: "This Month" },
  { value: "this_week", label: "This Week" },
];

// Derived labels from backend
const labels = computed(() => props.vacancyStats?.labels ?? [])

// Normalize series to match labels length and be numeric
function normalizeSeries(lbls, data) {
  const out = Array(lbls.length).fill(0)
  const src = Array.isArray(data) ? data : []
  for (let i = 0; i < out.length; i++) out[i] = Number(src[i] ?? 0)
  return out
}

// Chart options with axes
const chartOption = computed(() => {
  const apps = normalizeSeries(labels.value, props.vacancyStats?.applicationSent)
  const interviews = normalizeSeries(labels.value, props.vacancyStats?.interviews)
  const rejected = normalizeSeries(labels.value, props.vacancyStats?.rejected)

  const series = []
  if (showApplicationSent.value) {
    series.push({
      name: "Application Sent",
      type: "line",
      smooth: true,
      symbol: "circle",
      showSymbol: false,
      lineStyle: { color: "#4F46E5", width: 3 },
      itemStyle: { color: "#4F46E5" },
      data: apps,
    })
  }
  if (showInterviews.value) {
    series.push({
      name: "Interviews",
      type: "line",
      smooth: true,
      symbol: "circle",
      showSymbol: false,
      lineStyle: { color: "#10B981", width: 3 },
      itemStyle: { color: "#10B981" },
      data: interviews,
    })
  }
  if (showRejected.value) {
    series.push({
      name: "Rejected",
      type: "line",
      smooth: true,
      symbol: "circle",
      showSymbol: false,
      lineStyle: { color: "#EF4444", width: 3, type: "dashed" },
      itemStyle: { color: "#EF4444" },
      data: rejected,
    })
  }

  const opt = {
    tooltip: { trigger: "axis" },
    grid: { left: 40, right: 20, top: 30, bottom: 30 },
    xAxis: {
      type: "category",
      boundaryGap: false,
      data: labels.value,
      axisLine: { lineStyle: { color: "#9CA3AF" } },
      axisLabel: { color: "#6B7280" },
    },
    yAxis: {
      type: "value",
      minInterval: 1,
      axisLine: { show: false },
      splitLine: { lineStyle: { color: "#E5E7EB" } },
      axisLabel: { color: "#6B7280" },
    },
    legend: {
      data: ["Application Sent", "Interviews", "Rejected"],
      top: 0,
      textStyle: { color: "#374151", fontSize: 12 },
    },
    series,
  }

  // Debug chart config
  console.log('Chart Option:', opt)
  return opt
})


// Reload data when date filter changes
watch(dateFilter, (v) => {
  console.log('Date filter changed:', v)
  router.reload({
    only: ['vacancyStats', 'kpi'],
    data: { date_filter: v },
    preserveScroll: true,
    preserveState: true,
  })
})

const recentActivities = computed(() => {
  const activities = usePage().props.recent_activities || [];
  return activities.slice(0, 4);
});

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

function goToJobsApplied() {
  router.get(route('job.search'), { tab: 'applications' });
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

const carouselIndex = ref(0);

const maxVisible = 3;
const totalJobs = computed(() => props.recommendedJobs.length);

const visibleJobs = computed(() => {
  if (totalJobs.value <= maxVisible) return props.recommendedJobs;
  return props.recommendedJobs.slice(carouselIndex.value, carouselIndex.value + maxVisible);
});

function prevJobs() {
  if (carouselIndex.value > 0) carouselIndex.value--;
}
function nextJobs() {
  if (carouselIndex.value < totalJobs.value - maxVisible) carouselIndex.value++;
}

const companyCarouselIndex = ref(0);
const maxVisibleCompanies = 3;
const totalCompanies = computed(() => props.featuredCompanies.length);

const visibleCompanies = computed(() => {
  if (totalCompanies.value <= maxVisibleCompanies) return props.featuredCompanies;
  return props.featuredCompanies.slice(companyCarouselIndex.value, companyCarouselIndex.value + maxVisibleCompanies);
});

function prevCompanies() {
  if (companyCarouselIndex.value > 0) companyCarouselIndex.value--;
}
function nextCompanies() {
  if (companyCarouselIndex.value < totalCompanies.value - maxVisibleCompanies) companyCarouselIndex.value++;
}

onMounted(() => {
  console.log('Summary:', props.summary);

});
</script>

<template>
  <div class="min-h-screen bg-gray">
    <!-- Main Content -->
    <main class="flex flex-col w-full h-fi ">
      <!-- KPI Cards -->
      <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6 bg-gray">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <!-- Jobs Applied -->
          <div
            class="bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-2xl p-4 flex flex-col items-center justify-center min-h-[120px] relative overflow-hidden cursor-pointer hover:shadow-lg transition"
            @click="goToJobsApplied" title="View jobs you have applied for">
            <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center mb-2">
              <i class="fas fa-file-alt text-white"></i>
            </div>
            <span class="text-indigo-700 text-xs font-medium text-center">Jobs Applied</span>
            <span class="text-indigo-900 text-lg font-bold">{{ kpi.jobsApplied }}</span>
          </div>
          <!-- Referrals Made -->
          <div
            class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 flex flex-col items-center justify-center min-h-[120px] cursor-pointer hover:shadow-lg transition"
            @click="goToReferrals" title="View your job referrals">
            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mb-2">
              <i class="fas fa-share-square text-white"></i>
            </div>
            <span class="text-green-700 text-xs font-medium text-center">Referrals Made</span>
            <span class="text-green-900 text-3xl font-bold">{{ kpi.referralsMade ?? 0 }}</span>
          </div>
          <!-- Jobs Aligned -->
          <div
            class="bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-2xl p-6 flex flex-col items-center justify-center min-h-[120px] cursor-pointer hover:shadow-lg transition"
            @click="goToJobsAligned" title="View jobs aligned to your profile">
            <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center mb-2">
              <i class="fas fa-check-circle text-white"></i>
            </div>
            <span class="text-indigo-700 text-xs font-medium text-center">Jobs Aligned</span>
            <span class="text-indigo-900 text-3xl font-bold">{{ kpi.jobsAligned ?? 0 }}</span>
          </div>

          <!-- Interviews Scheduled -->
          <div
            class="bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-2xl p-6 flex flex-col items-center justify-center min-h-[120px] cursor-pointer hover:shadow-lg transition"
            @click="goToInterviewsScheduled" title="View your scheduled interviews">
            <div class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center mb-2">
              <i class="fas fa-calendar-check text-white"></i>
            </div>
            <span class="text-yellow-700 text-xs font-medium text-center">Interviews Scheduled</span>
            <span class="text-yellow-900 text-3xl font-bold">{{ kpi.interviewsScheduled ?? 0 }}</span>
          </div>
        </div>
      </div>

      <div class="container mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Left: Profile Card & Recent Activities -->
        <div class="lg:col-span-1 space-y-6 w-full">
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
                <span>{{ activity.data.message }}</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Center: Chart & Jobs -->
        <div class="lg:col-span-3 space-y-8 w-full">
          <!-- Chart Placeholder -->
          <div class="bg-white rounded-lg shadow p-6 mb-4">
            <div class="flex flex-wrap items-center justify-between mb-2">
              <h3 class="font-semibold text-gray-700 flex items-center">
                <i class="fas fa-chart-line mr-2 text-indigo-500"></i>
                Application Stats
              </h3>
              <div class="flex items-center gap-4">
                <!-- Filter toggles -->
                <button @click="showApplicationSent = !showApplicationSent"
                  :class="['flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold transition', showApplicationSent ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-500']">
                  <span class="w-3 h-3 rounded-full" :style="{ background: '#4F46E5' }"></span>
                  Application Sent
                </button>
                <button @click="showInterviews = !showInterviews"
                  :class="['flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold transition', showInterviews ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500']">
                  <span class="w-3 h-3 rounded-full" :style="{ background: '#10B981' }"></span>
                  Interviews
                </button>
                <button @click="showRejected = !showRejected"
                  :class="['flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold transition', showRejected ? 'bg-gray-200 text-gray-700' : 'bg-gray-100 text-gray-500']">
                  <span class="w-3 h-3 rounded-full" :style="{ background: '#EF4444' }"></span>
                  Rejected
                </button>
                <!-- Date filter dropdown -->
                <select v-model="dateFilter"
                  class="ml-4 px-3 py-1 rounded-md border border-gray-300 text-xs font-semibold text-gray-700 bg-white focus:outline-none focus:ring-indigo-500">
                  <option v-for="opt in dateOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
              </div>
            </div>
            <VueECharts :option="chartOption" style="height:320px;width:100%;" />
          </div>
          <!-- Jobs Based on Preference -->
          <div>
            <h2 class="text-lg font-semibold mb-4">Jobs for You</h2>
            <div class="flex justify-center items-center space-x-4">
              <!-- Prev Button -->
              <button v-if="totalJobs > maxVisible" @click="prevJobs" :disabled="carouselIndex === 0"
                class="p-2 rounded-full bg-gray-200 hover:bg-gray-300 disabled:opacity-50 transition">
                <i class="fas fa-chevron-left"></i>
              </button>

              <!-- Carousel Items with Animation -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-4xl relative overflow-hidden">
                <Transition name="carousel-fade" mode="out-in">
                  <div :key="carouselIndex" class="contents">
                    <div v-for="job in visibleJobs" :key="job.id"
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
                </Transition>
              </div>

              <!-- Next Button -->
              <button v-if="totalJobs > maxVisible" @click="nextJobs"
                :disabled="carouselIndex >= totalJobs - maxVisible"
                class="p-2 rounded-full bg-gray-200 hover:bg-gray-300 disabled:opacity-50 transition">
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
            <!-- Carousel indicators -->
            <div v-if="totalJobs > maxVisible" class="flex justify-center mt-4 space-x-2">
              <span v-for="i in totalJobs - maxVisible + 1" :key="i" class="w-3 h-3 rounded-full transition"
                :class="carouselIndex === i - 1 ? 'bg-indigo-600' : 'bg-gray-300'"></span>
            </div>
          </div>


          <!-- Featured Companies Carousel -->
          <div class="mt-10">
            <h2 class="text-lg font-semibold mb-4">Companies For You</h2>
            <div class="flex justify-center items-center space-x-4">
              <!-- Prev Button -->
              <button v-if="totalCompanies > maxVisibleCompanies" @click="prevCompanies"
                :disabled="companyCarouselIndex === 0"
                class="p-2 rounded-full bg-gray-200 hover:bg-gray-300 disabled:opacity-50 transition">
                <i class="fas fa-chevron-left"></i>
              </button>

              <!-- Carousel Items with Animation -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-4xl relative overflow-hidden">
                <Transition name="carousel-fade" mode="out-in">
                  <div :key="companyCarouselIndex" class="contents">
                    <div v-for="company in visibleCompanies" :key="company.id"
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
                </Transition>
              </div>

              <!-- Next Button -->
              <button v-if="totalCompanies > maxVisibleCompanies" @click="nextCompanies"
                :disabled="companyCarouselIndex >= totalCompanies - maxVisibleCompanies"
                class="p-2 rounded-full bg-gray-200 hover:bg-gray-300 disabled:opacity-50 transition">
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
            <!-- Carousel indicators -->
            <div v-if="totalCompanies > maxVisibleCompanies" class="flex justify-center mt-4 space-x-2">
              <span v-for="i in totalCompanies - maxVisibleCompanies + 1" :key="i"
                class="w-3 h-3 rounded-full transition"
                :class="companyCarouselIndex === i - 1 ? 'bg-indigo-600' : 'bg-gray-300'"></span>
            </div>
          </div>


        </div>
      </div>
    </main>
  </div>



  <Modal :modelValue="isViewDetailsModalOpen" @close="closeDetailsModal" max-width="2xl">
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
  </Modal>

  <Modal :modelValue="isApplyModalOpen" @close="closeApplyModal" max-width="2xl">
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
  </Modal>

  <Modal :modelValue="isSuccessModalOpen" @close="closeSuccessModal">
    <div class="p-6">
      <h3 class="text-lg font-medium text-center mb-4">{{ successMessage }}</h3>
      <div class="flex justify-center">
        <PrimaryButton @click="closeSuccessModal" class="bg-indigo-600 hover:bg-indigo-700">OK
        </PrimaryButton>
      </div>
    </div>
  </Modal>

  <Modal :modelValue="isCompanyModalOpen" @close="closeCompanyModal" max-width="2xl">
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
  </Modal>

</template>

<style scoped>
.carousel-fade-enter-active,
.carousel-fade-leave-active {
  transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.carousel-fade-enter-from,
.carousel-fade-leave-to {
  opacity: 0;
}

.carousel-fade-enter-to,
.carousel-fade-leave-from {
  opacity: 1;
}
</style>