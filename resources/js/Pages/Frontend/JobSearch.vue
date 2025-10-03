<script setup>
import Graduate from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { ref, onMounted, computed, watch } from 'vue';
import { useForm, usePage, router, Link  } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import '@fortawesome/fontawesome-free/css/all.css';
import axios from 'axios';

// Props and Page Data
const { props } = usePage();
const appliedJobIds = props.appliedJobIds ?? [];
const jobOffers = ref(props.jobOffers ?? []);

// Tabs and toggle state
const activeTab = ref('jobs'); // 'jobs' or 'applications'
const showApplied = ref(false);

// State Management
const searchQuery = ref('');
const selectedJobType = ref('');
const selectedLocation = ref('');
const selectedIndustry = ref('');
const selectedSalaryMin = ref('');
const selectedSalaryMax = ref('');
const selectedSkillsInput = ref('');
const selectedExperience = ref('');
const selectedCompany = ref('');
const isViewDetailsModalOpen = ref(false);
const isApplyModalOpen = ref(false);
const selectedJob = ref(null);
const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Pagination state
const pageSize = ref(props.jobs?.meta?.per_page ?? 10);
const jobsForm = useForm({
    jobs: props.jobs?.data || [],
    loading: false
});
const jobsMeta = ref(props.jobs?.meta ?? null);
const jobsLinks = ref(props.jobs?.links ?? []);
const page = ref(jobsMeta.value?.current_page ?? 1);
const totalPages = computed(() => {
  const total = jobsMeta.value?.total ?? jobsForm.jobs.length;
  return Math.max(1, Math.ceil(total / pageSize.value));
});
const pages = computed(() => Array.from({ length: totalPages.value }, (_, i) => i + 1));
const jobsCountFrom = computed(() => jobsMeta.value?.from ?? (jobsForm.jobs.length ? ((page.value - 1) * pageSize.value) + 1 : 0));
const jobsCountTo = computed(() => jobsMeta.value?.to ?? (Math.min(page.value * pageSize.value, jobsForm.jobs.length)));
const jobsCountTotal = computed(() => jobsMeta.value?.total ?? jobsForm.jobs.length);

// Filter jobs for main list
const filteredJobs = computed(() => jobsForm.jobs);

// Jobs the user has applied for
const myApplications = ref(props.myApplications ?? []);

// Pagination fetch
function goToPage(p) {
  if (!p || p < 1 || p > totalPages.value) return;
  router.get(route('job.search'), currentFilters({ page: p }), {
    preserveScroll: true,
    onSuccess: (pageResp) => {
      const pld = pageResp.props.jobs;
      if (Array.isArray(pld)) {
        jobsForm.jobs = pld;
        jobsMeta.value = { from: pld.length ? 1 : 0, to: pld.length, total: pld.length, current_page: 1 };
        jobsLinks.value = [];
        page.value = 1;
      } else {
        jobsForm.jobs = pld?.data || [];
        jobsMeta.value = pld?.meta ?? jobsMeta.value;
        jobsLinks.value = pld?.links ?? [];
        page.value = jobsMeta.value?.current_page ?? p;
        pageSize.value = jobsMeta.value?.per_page ?? pageSize.value;
      }
    }
  });
}
function goToPrev() { goToPage(page.value - 1); }
function goToNext() { goToPage(page.value + 1); }

// Reset page when filters change
watch([searchQuery, selectedJobType, selectedLocation, selectedIndustry, selectedSalaryMin, selectedSalaryMax, selectedSkillsInput, selectedExperience, selectedCompany, showApplied], () => {
  page.value = 1;
});

// Build current filters for reuse
function currentFilters(overrides = {}) {
  const params = {};
  if (searchQuery.value) params.keywords = searchQuery.value;
  if (selectedJobType.value) params.jobType = selectedJobType.value;
  if (selectedLocation.value) params.location = selectedLocation.value;
  if (selectedIndustry.value) params.industry = selectedIndustry.value;
  if (selectedSalaryMin.value) params.salaryMin = selectedSalaryMin.value;
  if (selectedSalaryMax.value) params.salaryMax = selectedSalaryMax.value;
  if (selectedSkillsInput.value) {
    params.skills = selectedSkillsInput.value.split(',').map(s => s.trim()).filter(Boolean);
  }
  if (selectedExperience.value) params.experience = selectedExperience.value;
  if (selectedCompany.value) params.company = selectedCompany.value;
  params.showApplied = showApplied.value;
  params.page = page.value;
  return { ...params, ...overrides };
}

// Toggle to show/hide applied jobs
function toggleShowApplied() {
    router.get(route('job.search'), currentFilters({ page: 1 }), { preserveScroll: true });
}


console.log("Jobs", filteredJobs.value)

// Clear filters
function clearFilters() {
    searchQuery.value = '';
    selectedJobType.value = '';
    selectedLocation.value = '';
    selectedIndustry.value = '';
    selectedSalaryMin.value = '';
    selectedSalaryMax.value = '';
    selectedSkillsInput.value = '';
    selectedExperience.value = '';
    selectedCompany.value = '';
    page.value = 1;
    fetchJobs();
}

// Fetch jobs with filters
function fetchJobs() {
    if (jobsForm.loading) return;
    jobsForm.loading = true;

    const params = currentFilters({ page: page.value });

    router.get(route('job.search'), params, {
        preserveScroll: true,
        onSuccess: (pageResp) => {
            const p = pageResp.props.jobs;
            if (Array.isArray(p)) {
                jobsForm.jobs = p;
                jobsMeta.value = { from: p.length ? 1 : 0, to: p.length, total: p.length, current_page: 1 };
                jobsLinks.value = [];
                page.value = 1;
            } else {
                jobsForm.jobs = p?.data || [];
                jobsMeta.value = p?.meta ?? { from: jobsForm.jobs.length ? 1 : 0, to: jobsForm.jobs.length, total: jobsForm.jobs.length, current_page: 1 };
                jobsLinks.value = p?.links ?? [];
                page.value = jobsMeta.value?.current_page ?? 1;
                pageSize.value = jobsMeta.value?.per_page ?? pageSize.value;
            }
            jobOffers.value = pageResp.props.jobOffers ?? jobOffers.value;
            jobsForm.loading = false;
        },
        onFinish: () => {
            jobsForm.loading = false;
        }
    });
}


function calculateMatchPercentage(job) {
    return job.match_percentage ?? 0;
}

// Apply for a job
const applyForm = useForm({ job_id: null, cover_letter: '', cover_letter_file: null })

function onCoverLetterFileChange(e) {
    applyForm.cover_letter_file = (e.target.files && e.target.files[0]) ? e.target.files[0] : null
}

// Fallback-friendly getters for the Apply modal
const applyJobTypes = computed(() => {
    const j = selectedJob.value
    if (!j) return []
    if (Array.isArray(j.jobTypes) && j.jobTypes.length) return j.jobTypes.map(jt => jt.type).filter(Boolean)
    if (Array.isArray(j.job_types) && j.job_types.length) return j.job_types.map(jt => jt.type).filter(Boolean)
    return j.job_type ? [j.job_type] : []
})

const applySalary = computed(() => {
    const j = selectedJob.value
    if (!j) return null
    if (j.salary) return j.salary
    // fallback if salary fields are on job record
    if (j.job_min_salary || j.job_max_salary || j.salary_type) {
        return {
            job_min_salary: j.job_min_salary || null,
            job_max_salary: j.job_max_salary || null,
            salary_type: j.salary_type || null,
        }
    }
    return null
})

// Apply for a job
function showApplyModal(job) {
    selectedJob.value = job;
    applyForm.job_id = job.id;
    applyForm.cover_letter = '';
    isApplyModalOpen.value = true;
}
function submitApplication() {
    applyForm.post(route('apply-for-job'), {
        preserveScroll: true,
        forceFormData: true, // ensure file upload goes as multipart/form-data
        onSuccess: () => {
            successMessage.value = 'Successfully applied for the job and Referral sent to PESO!'
            isApplyModalOpen.value = false
            isSuccessModalOpen.value = true
            fetchJobs()

            if (selectedJob.value && selectedJob.value.company && selectedJob.value.company.id) {
                requestReferral(selectedJob.value.company.id, selectedJob.value.id)
            }
        },
        onError: () => {
            errorMessage.value = 'Failed to apply for the job. Please try again.'
            isErrorModalOpen.value = true;
        }
    })
}

// const oneClickApply = (job) => {
//     const form = useForm({ job_id: job.id });
//     console.log(route('jobs.oneClickApply')); // Is this outputting '/jobs/one-click-apply' exactly?

//     form.post(route('jobs.oneClickApply'), {
//         onSuccess: () => alert('Applied with your latest resume and cover letter!'),
//         onError: () => alert('Something went wrong.'),

//     });
// };


function closeApplyModal() {
    isApplyModalOpen.value = false;
    selectedJob.value = null;
}

function goToCompanyProfile(companyId) {
    if (companyId) {
        router.visit(route('company.profile.public', { id: companyId }));
    }
}

// View job details
function viewJobDetails(job) {
    router.visit(route('graduate.jobs.show', { job: job.id }));
}
function closeDetailsModal() {
    isViewDetailsModalOpen.value = false;
    selectedJob.value = null;
}
function closeSuccessModal() {
    isSuccessModalOpen.value = false;
}
function closeErrorModal() {
    isErrorModalOpen.value = false;
}


const recommendations = ref([]);
const recommendationsLoading = ref(false);

function fetchRecommendations() {
    recommendationsLoading.value = true;
    axios.get(route('graduate-jobs.recommendations'))
        .then(res => {
            recommendations.value = res.data.recommendations || [];
        })
        .finally(() => {
            recommendationsLoading.value = false;
        });
}


// Format salary for display
function formatSalary(salary) {
    if (!salary) return 'Not specified';
    if (salary.job_min_salary && salary.job_max_salary) {
        return `₱${salary.job_min_salary} - ₱${salary.job_max_salary} (${salary.salary_type || ''})`;
    }
    return 'Not specified';
}

function requestReferral(companyId, jobId) {
    router.post(route('graduate.referral.request'), {
        company_id: companyId,
        job_id: jobId,
    }, {
        onSuccess: () => {
        },
        onError: () => {
        }
    });
}

// Only fetch jobs when user clicks Search
onMounted(() => {
    fetchRecommendations();
});
</script>

<template>
    <Graduate>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="max-w-[1280px] mx-auto px-4 py-6">
                        <header class="pb-6 mb-6">
                            <h1 class="text-2xl font-extrabold leading-6">Apply for Jobs</h1>
                            <p class="text-sm text-[#374151] mt-1">Discover and apply for jobs that match your skills
                                and interests</p>

                        </header>

                        <!-- Tabs -->
                        <div class="flex gap-2 mb-8">
                            <button :class="[
                                'px-4 py-2 rounded-t-lg font-semibold transition',
                                activeTab === 'jobs' ? 'bg-indigo-600 text-white shadow' : 'bg-gray-100 text-gray-700'
                            ]" @click="activeTab = 'jobs'">
                                <i class="fas fa-briefcase mr-2"></i> Job Listings
                            </button>
                            <button :class="[
                                'px-4 py-2 rounded-t-lg font-semibold transition',
                                activeTab === 'applications' ? 'bg-indigo-600 text-white shadow' : 'bg-gray-100 text-gray-700'
                            ]" @click="activeTab = 'applications'">
                                <i class="fas fa-file-alt mr-2"></i> My Applications
                                <span v-if="appliedJobIds.length"
                                    class="ml-2 bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-bold">
                                    {{ appliedJobIds.length }}
                                </span>
                            </button>
                            <button :class="[

                                'px-4 py-2 rounded-t-lg font-semibold transition',
                                activeTab === 'offers' ? 'bg-indigo-600 text-white shadow' : 'bg-gray-100 text-gray-700'
                            ]" @click="activeTab = 'offers'">
                                <i class="fas fa-gift mr-2"></i> Job Offers
                                <span v-if="props.jobOffers && props.jobOffers.length"
                                    class="ml-2 bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full text-xs font-bold">
                                    {{ props.jobOffers.length }}
                                </span>
                            </button>
                        </div>

                        <!-- Toggle Checkbox (only for Job Listings tab) -->
                        <div v-if="activeTab === 'jobs'" class="mb-4 flex items-center">
                            <input type="checkbox" id="showApplied" v-model="showApplied" @change="toggleShowApplied"
                                class="accent-indigo-600 h-4 w-4 rounded border-gray-300 focus:ring-indigo-500" />
                            <label for="showApplied" class="ml-2 text-sm text-gray-700 cursor-pointer">
                                Show applied jobs in list
                            </label>
                        </div>

                        <!-- FILTER BAR (only for Job Listings tab) -->
                        <div v-if="activeTab === 'jobs'" class="mb-8">
                            <form @submit.prevent="fetchJobs">
                                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                    <TextInput v-model="searchQuery" class="w-full"
                                        placeholder="Search jobs by title or description" />

                                    <select v-model="selectedJobType" class="border rounded-md px-3 py-2 w-full">
                                        <option value="">Job Type</option>
                                        <option v-for="(type, id) in props.jobTypes" :key="id" :value="id">{{ type }}
                                        </option>
                                    </select>

                                    <select v-model="selectedIndustry" class="border rounded-md px-3 py-2 w-full">
                                        <option value="">Industry</option>
                                        <option v-for="industry in props.industries" :key="industry.id"
                                            :value="industry.id">{{ industry.name }}</option>
                                    </select>

                                    <select v-model="selectedExperience" class="border rounded-md px-3 py-2 w-full">
                                        <option value="">Experience Level</option>
                                        <option v-for="level in props.experienceLevels" :key="level" :value="level">{{
                                            level }}</option>
                                    </select>

                                    <select v-model="selectedCompany" class="border rounded-md px-3 py-2 w-full">
                                        <option value="">Company</option>
                                        <option v-for="company in props.companies" :key="company.id"
                                            :value="company.id">{{ company.company_name }}</option>
                                    </select>

                                    <select v-model="selectedLocation" class="border rounded-md px-3 py-2 w-full">
                                        <option value="">Location</option>
                                        <option v-for="(address, id) in props.locations" :key="id" :value="id">{{
                                            address }}</option>
                                    </select>

                                    <TextInput v-model="selectedSalaryMin" type="number" class="w-full"
                                        placeholder="Min Salary" />
                                    <TextInput v-model="selectedSalaryMax" type="number" class="w-full"
                                        placeholder="Max Salary" />
                                    <TextInput v-model="selectedSkillsInput" class="w-full"
                                        placeholder="Skills (comma separated)" />
                                </div>
                                <div class="flex justify-end mt-4">
                                    <PrimaryButton type="submit" class="bg-indigo-600 hover:bg-indigo-700">
                                        Seek
                                    </PrimaryButton>
                                    <PrimaryButton type="button" class="ml-2 bg-gray-400 hover:bg-gray-500"
                                        @click="clearFilters">
                                        Reset
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                        <!-- JOBS: Main area with recommendations sidebar -->
                        <div v-if="activeTab === 'jobs'" class="mb-8">
                        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                            <!-- Main listings (takes 3 cols on large screens) -->
                            <div class="lg:col-span-3">
                            <h2 class="text-xl font-semibold mb-4">Job Listings</h2>

                            <!-- Loading State -->
                            <div v-if="jobsForm.loading" class="flex justify-center items-center py-8">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                            </div>

                            <!-- Empty State -->
                            <div v-else-if="!filteredJobs.length" class="text-center py-8">
                                <div class="text-gray-400">
                                <i class="fas fa-briefcase text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium">No Jobs Found</h3>
                                <p class="text-sm">Try adjusting your search criteria</p>
                                </div>
                            </div>

                            <!-- Job Listings -->
                            <div v-else class="grid grid-cols-1 gap-6">
                                <div v-for="job in filteredJobs" :key="job.id"
                                    class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                        <h3 class="text-lg font-semibold text-gray-900">{{ job.job_title }}</h3>
                                        <p class="text-sm text-gray-600">
                                            <template v-if="job.company">
                                            {{ job.company.company_name }}
                                            </template>
                                            <template v-else-if="job.institution">
                                            {{ job.institution.institution_name }}
                                            </template>
                                            <template v-else-if="job.peso">
                                            {{ job.peso.peso_name }}
                                            </template>
                                            <template v-else>
                                            Unknown
                                            </template>
                                        </p>
                                        </div>
                                        <div class="flex items-center">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                                        <div class="flex items-center text-gray-600">
                                        <i class="fas fa-map-marker-alt mr-2"></i>
                                        <span>
                                            <template v-if="job.locations && job.locations.length">
                                            {{ job.locations.map(l => l.address).join(', ') }}
                                            </template>
                                            <template v-else>
                                            Not specified
                                            </template>
                                        </span>
                                        </div>
                                        <div class="flex items-center text-gray-600">
                                        <i class="fas fa-briefcase mr-2"></i>
                                        <span>
                                            <template v-if="job.job_type_names && job.job_type_names.length">
                                            {{ job.job_type_names.join(', ') }}
                                            </template>
                                            <template v-else>
                                            Not specified
                                            </template>
                                        </span>
                                        </div>
                                        <div class="flex items-center text-gray-600">
                                        <i class="fas fa-clock mr-2"></i>
                                        {{ job.job_experience_level || 'Not specified' }}
                                        </div>
                                        <div class="flex items-center text-gray-600">
                                        <i class="fas fa-peso-sign mr-2"></i>
                                        {{ formatSalary(job.salary) }}
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <span v-for="skill in job.skills" :key="skill"
                                            class="px-3 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 rounded-full">
                                        {{ skill }}
                                        </span>
                                    </div>

                                    <div class="flex space-x-3">
                                        <PrimaryButton @click="viewJobDetails(job)" class="text-sm">View Details</PrimaryButton>

                                        <PrimaryButton v-if="!appliedJobIds.includes(job.id)"
                                                    @click="showApplyModal(job)"
                                                    class="text-sm bg-green-600 hover:bg-green-700">
                                        Apply Now
                                        </PrimaryButton>

                                        <span v-else class="text-gray-400 text-sm font-semibold flex items-center">
                                        <i class="fas fa-check-circle mr-1 text-green-500"></i> Already Applied
                                        </span>

                                        <PrimaryButton v-if="job.company && job.company.id"
                                                    @click="goToCompanyProfile(job.company.id)"
                                                    class="bg-gray-600 hover:bg-gray-700">
                                        View Company
                                        </PrimaryButton>
                                    </div>
                                    </div>
                                </div>
                                <!-- Improved Pagination controls -->
                                <div v-if="totalPages > 1" class="flex items-center justify-between px-6 py-3 bg-white border-t border-gray-100">
                                <button
                                    class="px-3 py-1 text-sm rounded border border-gray-300 hover:bg-gray-50 disabled:opacity-50"
                                    :disabled="page === 1"
                                    @click="goToPrev"
                                >
                                    Prev
                                </button>
                                <div class="space-x-1">
                                    <button
                                    v-for="p in pages"
                                    :key="p"
                                    class="px-3 py-1 text-sm rounded border"
                                    :class="p === page ? 'bg-indigo-600 text-white border-indigo-600' : 'border-gray-300 hover:bg-gray-50'"
                                    @click="goToPage(p)"
                                    >
                                    {{ p }}
                                    </button>
                                </div>
                                <button
                                    class="px-3 py-1 text-sm rounded border border-gray-300 hover:bg-gray-50 disabled:opacity-50"
                                    :disabled="page === totalPages"
                                    @click="goToNext"
                                >
                                    Next
                                </button>
                                <div class="text-sm text-gray-500 ml-4">
                                    Showing {{ jobsCountFrom }}-{{ jobsCountTo }} of {{ jobsCountTotal }}
                                </div>
                                </div>
                            </div>

                            <!-- Sidebar: Recommended Jobs (1 col on large screens) -->
                            <aside class="lg:col-span-1">
                            <div class="bg-white rounded-lg border border-gray-200 p-4 sticky top-6">
                                <h3 class="text-lg font-semibold mb-3">Recommended for You</h3>

                                <div v-if="recommendationsLoading" class="py-4 text-sm text-gray-500">Loading recommendations...</div>

                                <div v-else-if="!recommendations.length" class="text-sm text-gray-500 py-2">
                                No recommendations yet.
                                </div>

                                <div v-else class="space-y-3">
                                <div v-for="job in recommendations" :key="job.id" class="border rounded p-3 bg-blue-50">
                                    <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-semibold text-sm">{{ job.job_title }}</h4>
                                        <p class="text-xs text-gray-600">{{ job.company?.company_name }}</p>
                                    </div>
                                    </div>

                                    <div class="text-xs text-gray-700 mt-2">
                                    <span v-if="job.locations && job.locations.length">
                                        {{ job.locations.map(l => l.address).join(', ') }}
                                    </span>
                                    </div>

                                    <div class="mt-2 flex flex-wrap gap-2">
                                    <span v-for="label in job.match_labels" :key="label"
                                            class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 border border-green-200">
                                        Match with {{ label }}
                                    </span>
                                    <span class="ml-2 px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                        {{ job.match_percentage !== undefined ? job.match_percentage + '%' : 'N/A' }} Match
                                    </span>
                                    </div>

                                    <div class="mt-3 flex gap-2">
                                    <PrimaryButton @click="viewJobDetails(job)" class="text-xs">View</PrimaryButton>
                                    <PrimaryButton v-if="!appliedJobIds.includes(job.id)"
                                                    @click="showApplyModal(job)"
                                                    class="text-xs bg-green-600 hover:bg-green-700">
                                        Apply
                                    </PrimaryButton>
                                    <span v-else class="text-gray-400 text-xs font-semibold flex items-center">
                                        <i class="fas fa-check-circle mr-1 text-green-500"></i> Applied
                                    </span>
                                    </div>
                                </div>
                                </div>
                            </div>
                            </aside>
                        </div>
                        </div>

                        <!-- My Applications Tab -->
                        <div v-if="activeTab === 'applications'">
                            <h2 class="text-xl font-semibold mb-4">My Applications</h2>
                            <div v-if="!myApplications.length" class="text-center py-8 text-gray-500">
                                <i class="fas fa-info-circle text-2xl mb-2"></i>
                                You have not applied to any jobs yet.
                            </div>
                            <div v-else class="grid grid-cols-1 gap-6">
                                <div v-for="job in myApplications" :key="job.id"
                                    class="bg-indigo-50 border border-indigo-200 rounded-lg p-6 shadow-sm">
                                    <div class="flex justify-between items-center mb-2">
                                        <div>
                                            <h3 class="text-lg font-semibold text-indigo-900">{{ job.job_title }}</h3>
                                            <p class="text-sm text-gray-600">{{ job.company?.company_name || 'Unknown Company' }}</p>
                                        </div>
                                        <span class="px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700">
                                            {{ job.application ? job.application.status : 'applied' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center text-gray-600 mb-2">
                                        <i class="fas fa-map-marker-alt mr-2"></i>
                                        <span>
                                            <template v-if="job.locations && job.locations.length">
                                                {{job.locations.map(l => l.address).join(', ')}}
                                            </template>
                                            <template v-else>
                                                Not specified
                                            </template>
                                        </span>
                                    </div>
                                    <div class="flex items-center text-gray-600 mb-2">
                                        <i class="fas fa-briefcase mr-2"></i>
                                        <span>
                                            <template v-if="job.jobTypes && job.jobTypes.length">
                                                {{job.jobTypes.map(jt => jt.type).join(', ')}}
                                            </template>
                                            <template v-else>
                                                Not specified
                                            </template>
                                        </span>
                                    </div>
                                    <div class="flex items-center text-gray-600 mb-2">
                                        <i class="fas fa-clock mr-2"></i>
                                        {{ job.job_experience_level || 'Not specified' }}
                                    </div>
                                    <div class="flex items-center text-gray-600 mb-2">
                                        <i class="fas fa-dollar-sign mr-2"></i>
                                        {{ formatSalary(job.salary) }}
                                    </div>
                                    <div class="flex space-x-3 mt-4">
                                        <PrimaryButton @click="viewJobDetails(job)" class="text-sm">
                                            View Details
                                        </PrimaryButton>
                                        <PrimaryButton v-if="job.company && job.company.id"
                                            @click="goToCompanyProfile(job.company.id)"
                                            class="bg-gray-600 hover:bg-gray-700">
                                            View Company
                                        </PrimaryButton>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Job Offers Tab -->
                        <div v-if="activeTab === 'offers'">
                          <h2 class="text-xl font-semibold mb-4">Job Offers</h2>
                          <div v-if="!jobOffers.length" class="text-center py-8 text-gray-500">
                            <i class="fas fa-gift text-2xl mb-2"></i>
                            You have no job offers yet.
                          </div>
                          <div v-else class="grid grid-cols-1 gap-6">
                           <div v-for="offer in jobOffers" :key="offer.id">
                                <Link :href="route('graduate.job.offers.show', offer.id)" class="block bg-yellow-50 border border-yellow-200 rounded-lg p-6 shadow-sm hover:shadow-md transition">
                                <div class="flex justify-between items-center mb-2">
                                    <div>
                                    <h3 class="text-lg font-semibold text-yellow-900">{{ offer.job_title }}</h3>
                                    <p class="text-sm text-gray-600">{{ offer.company?.company_name || 'Unknown Company' }}</p>
                                    </div>
                                    <span :class="[
                                        'px-3 py-1 text-xs font-bold rounded-full',
                                        offer.status === 'accepted' ? 'bg-green-100 text-green-700' :
                                        offer.status === 'declined' ? 'bg-red-100 text-red-700' :
                                        'bg-yellow-100 text-yellow-700'
                                        ]">
                                        {{ offer.status === 'accepted' ? 'Accepted' : offer.status === 'declined' ? 'Declined' : 'Offer' }}
                                    </span>
                                </div>

                                <div class="text-gray-700 mb-2">
                                    <div v-if="offer.body">
                                    <strong>Message:</strong> {{ offer.body }}
                                    </div>
                                    <div v-if="offer.file_url" class="mt-3">
                                    <template v-if="offer.file_url.match(/\.(png|jpe?g|gif)$/i)">
                                        <img :src="offer.file_url" class="w-32 h-20 object-cover rounded" />
                                    </template>
                                    <template v-else-if="offer.file_url.match(/\.pdf$/i)">
                                        <div class="w-32 h-20 flex items-center justify-center bg-white border rounded text-sm text-gray-700">
                                        PDF Preview
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div class="w-32 h-20 flex items-center justify-center bg-white border rounded text-sm text-gray-700">
                                        Attachment
                                        </div>
                                    </template>
                                    </div>
                                </div>
                                </Link>
                            </div>
                          </div>
                        </div>

                        <!-- Job Details Modal -->
                        <Modal :modelValue="isViewDetailsModalOpen" @close="closeDetailsModal" max-width="2xl">
                            <div class="p-6" v-if="selectedJob">
                                <div class="flex justify-between items-start mb-6">
                                    <div>
                                        <h2 class="text-xl font-bold text-gray-900">{{ selectedJob.job_title }}</h2>
                                        <span
                                            class="px-2 py-1 text-xs rounded-full font-semibold bg-blue-100 text-blue-800 border border-blue-200"
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
                                            <template v-if="selectedJob.locations && selectedJob.locations.length">
                                                {{selectedJob.locations.map(l => l.address).join(', ')}}
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
                                        <i class="fas fa-dollar-sign mr-2"></i>
                                        {{ formatSalary(selectedJob.salary) }}
                                    </div>
                                </div>

                                <!-- Detailed Sections -->
                                <div class="mb-6">
                                    <h3 class="text-lg font-semibold mb-2">Job Description</h3>
                                    <p class="text-gray-700 whitespace-pre-line">{{ selectedJob.job_description ||
                                        'Notspecified.' }}</p>
                                </div>
                                <div class="mb-6" v-if="selectedJob.job_roles">
                                    <h3 class="text-lg font-semibold mb-2">Roles</h3>
                                    <p class="text-gray-700 whitespace-pre-line">{{ selectedJob.job_roles }}</p>
                                </div>
                                <div class="mb-6" v-if="jobResponsibilities.length">
                                    <h3 class="text-lg font-semibold mb-2">Responsibilities</h3>
                                    <ul class="list-disc pl-5 text-gray-700">
                                        <li v-for="(resp, idx) in jobResponsibilities" :key="idx">
                                            {{ resp }}
                                        </li>
                                    </ul>
                                </div>
                                <div class="mb-6" v-if="jobQualifications.length">
                                    <h3 class="text-lg font-semibold mb-2">Required Qualifications</h3>
                                    <ul class="list-disc pl-5 text-gray-700">
                                        <li v-for="(qual, idx) in jobQualifications" :key="idx">
                                            {{ qual }}
                                        </li>
                                    </ul>
                                </div>
                                <div class="mb-6" v-if="jobBenefits.length">
                                    <h3 class="text-lg font-semibold mb-2">Benefits</h3>
                                    <ul class="list-disc pl-5 text-gray-700">
                                        <li v-for="(benefit, idx) in jobBenefits" :key="idx">
                                            {{ benefit }}
                                        </li>
                                    </ul>
                                </div>
                                <div class="mb-6" v-if="selectedJob.skills && selectedJob.skills.length">
                                    <h3 class="text-lg font-semibold mb-2">Required Skills</h3>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="skill in Array.isArray(selectedJob.skills)
                                            ? selectedJob.skills
                                            : selectedJob.skills.split(',').map(s => s.trim()).filter(Boolean)"
                                            :key="skill"
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
                                    <template v-if="!appliedJobIds.includes(selectedJob.id)">
                                        <PrimaryButton @click="showApplyModal(selectedJob)"
                                            class="bg-green-600 hover:bg-green-700">
                                            Apply Now
                                        </PrimaryButton>
                                        <!-- <PrimaryButton @click="oneClickApply(selectedJob)"
                                            class="text-sm bg-green-600 hover:bg-green-700">
                                            One-Click Apply
                                        </PrimaryButton> -->
                                    </template>
                                    <template v-else>
                                        <span class="text-gray-400 text-sm font-semibold flex items-center">
                                            <i class="fas fa-check-circle mr-1 text-green-500"></i> Already Applied
                                        </span>
                                    </template>
                                    <PrimaryButton v-if="selectedJob && selectedJob.company && selectedJob.company.id"
                                        @click="goToCompanyProfile(selectedJob.company.id)"
                                        class="bg-gray-600 hover:bg-gray-700">
                                        View Company
                                    </PrimaryButton>
                                </div>
                            </div>

                        </Modal>

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

                        <!-- Error Modal -->
                        <Modal :modelValue="isErrorModalOpen" @close="closeErrorModal">
                            <div class="p-6">
                                <div class="flex items-center justify-center mb-4">
                                    <div class="bg-red-100 rounded-full p-2">
                                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-lg font-medium text-center mb-4">{{ errorMessage }}</h3>
                                <div class="flex justify-center">
                                    <button @click="closeErrorModal"
                                        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        OK
                                    </button>
                                </div>
                            </div>
                        </Modal>

                        <!-- Apply Job Modal -->
                        <Modal :modelValue="isApplyModalOpen" @close="closeApplyModal" max-width="2xl">
                            
                                <div class="p-6" v-if="selectedJob">
                                    <div class="flex justify-between items-start mb-6">
                                        <div>
                                            <h2 class="text-xl font-bold text-gray-900">Apply for: {{
                                                selectedJob.job_title
                                                }}
                                            </h2>
                                            <p class="text-md text-gray-600">
                                                <template v-if="selectedJob.company">
                                                    {{ selectedJob.company.company_name }}
                                                </template>
                                                <template v-else>
                                                    Unknown
                                                </template>
                                            </p>
                                        </div>
                                        <span class="px-3 py-1 text-xs font-medium rounded-full" :class="{
                                            'bg-indigo-100 text-indigo-800': calculateMatchPercentage(selectedJob) >= 90,
                                            'bg-blue-100 text-blue-800': calculateMatchPercentage(selectedJob) >= 80 && calculateMatchPercentage(selectedJob) < 90,
                                            'bg-green-100 text-green-800': calculateMatchPercentage(selectedJob) >= 70 && calculateMatchPercentage(selectedJob) < 80,
                                            'bg-yellow-100 text-yellow-800': calculateMatchPercentage(selectedJob) >= 50 && calculateMatchPercentage(selectedJob) < 70,
                                            'bg-gray-100 text-gray-800': calculateMatchPercentage(selectedJob) < 50
                                        }">
                                            {{ selectedJob.match_percentage !== undefined ? selectedJob.match_percentage
                                            + '%' : 'N/A' }} Match
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-map-marker-alt mr-2"></i>
                                            <span>
                                                <template v-if="selectedJob.locations && selectedJob.locations.length">
                                                    {{selectedJob.locations.map(l => l.address).join(', ')}}
                                                </template>
                                                <template v-else>Not specified</template>
                                            </span>
                                        </div>
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-briefcase mr-2"></i>
                                            <span>
                                                <template v-if="applyJobTypes.length">
                                                    {{ applyJobTypes.join(', ') }}
                                                </template>
                                                <template v-else>Not specified</template>
                                            </span>
                                        </div>
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-clock mr-2"></i>
                                            {{ selectedJob.job_experience_level || 'Not specified' }}
                                        </div>
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-peso-sign mr-2"></i>
                                            {{ formatSalary(applySalary) }}
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <h3 class="text-lg font-semibold mb-2">Cover Letter</h3>
                                        <textarea v-model="applyForm.cover_letter"
                                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            rows="5"
                                            placeholder="Tell the employer why you're a good fit for this position..."></textarea>

                                        <!-- Optional file upload -->
                                        <div class="mt-3">
                                            <label class="block text-sm text-gray-700 mb-1">Upload Cover Letter
                                                (optional)</label>
                                            <input type="file" accept=".pdf,.doc,.docx"
                                                @change="onCoverLetterFileChange"
                                                class="block w-full text-sm border rounded p-1" />
                                            <div v-if="applyForm.cover_letter_file" class="text-xs text-gray-500 mt-1">
                                                {{ applyForm.cover_letter_file.name }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex justify-end space-x-3">
                                        <button @click="closeApplyModal"
                                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                            Cancel
                                        </button>
                                        <PrimaryButton @click="submitApplication"
                                            class="bg-green-600 hover:bg-green-700">
                                            Submit Application
                                        </PrimaryButton>
                                    </div>
                                </div>
                        </Modal>
                    </div>
                </div>
            </div>
        </div>
    </Graduate>
</template>