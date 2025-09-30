<script setup>
import Graduate from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { ref, onMounted, computed } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import '@fortawesome/fontawesome-free/css/all.css';
import axios from 'axios';

// Props and Page Data
const { props } = usePage();
const appliedJobIds = props.appliedJobIds ?? [];


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

// Forms for data management
const jobsForm = useForm({
    jobs: props.jobs?.data || [],
    loading: false
});


const jobResponsibilities = computed(() =>
    selectedJob.value && selectedJob.value.job_responsibilities
        ? selectedJob.value.job_responsibilities.split('\n').filter(r => r.trim())
        : []
);

const jobQualifications = computed(() =>
    selectedJob.value && selectedJob.value.job_qualifications
        ? selectedJob.value.job_qualifications.split('\n').filter(q => q.trim())
        : []
);

const jobBenefits = computed(() =>
    selectedJob.value && selectedJob.value.job_benefits
        ? selectedJob.value.job_benefits.split('\n').filter(b => b.trim())
        : []
);

// Filter jobs for main list
const filteredJobs = computed(() => {
    if (showApplied.value) return jobsForm.jobs;
    return jobsForm.jobs.filter(job => !appliedJobIds.includes(job.id));
});

// Jobs the user has applied for
const myApplications = ref(props.myApplications ?? []);

// Toggle to show/hide applied jobs
function toggleShowApplied() {
    router.get(window.location.pathname, { showApplied: showApplied.value }, { preserveScroll: true });
}

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
    fetchJobs();
}

// Fetch jobs with filters
function fetchJobs() {
    if (jobsForm.loading) return;
    jobsForm.loading = true;

    // Only include non-empty filters
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

    router.get(route('job.search'), params, {
        preserveScroll: true,
        onSuccess: (page) => {
            jobsForm.jobs = page.props.jobs.data || [];
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
                        <!-- Recommended Jobs Section (only for Job Listings tab) -->
                        <div v-if="activeTab === 'jobs'" class="mb-8">
                            <h2 class="text-xl font-semibold mb-2">Recommended for You</h2>
                            <div v-if="recommendationsLoading" class="py-4">Loading recommendations...</div>
                            <div v-else-if="!recommendations.length" class="text-gray-500 py-4">No recommendations yet.
                            </div>
                            <div v-else class="grid grid-cols-1 gap-4">
                              <div v-for="job in recommendations" :key="job.id"
                                   class="bg-blue-50 border border-blue-200 rounded p-4">
                                <div class="flex justify-between items-center">
                                  <div>
                                    <h3 class="font-bold">{{ job.job_title }}</h3>
                                    <p class="text-sm text-gray-600">{{ job.company?.company_name }}</p>
                                  </div>
                                  <div class="flex items-center gap-2">
                                    <PrimaryButton @click="viewJobDetails(job)" class="text-xs">View</PrimaryButton>
                                    <PrimaryButton
                                      v-if="!appliedJobIds.includes(job.id)"
                                      @click="showApplyModal(job)"
                                      class="text-xs bg-green-600 hover:bg-green-700"
                                    >
                                      Apply
                                    </PrimaryButton>
                                    <span v-else class="text-gray-400 text-xs font-semibold flex items-center">
                                      <i class="fas fa-check-circle mr-1 text-green-500"></i> Applied
                                    </span>
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
                              </div>
                            </div>
                        </div>

                        <!-- Job Listings Tab -->
                        <div v-if="activeTab === 'jobs'">
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
                                                    {{job.locations.map(l => l.address).join(', ')}}
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
                                        <PrimaryButton @click="viewJobDetails(job)" class="text-sm">
                                            View Details
                                        </PrimaryButton>
                                        <PrimaryButton v-if="!appliedJobIds.includes(job.id)"
                                            @click="showApplyModal(job)"
                                            class="text-sm bg-green-600 hover:bg-green-700">
                                            Apply Now
                                        </PrimaryButton>
                                        <span v-else class="text-gray-400 text-sm font-semibold flex items-center">
                                            <i class="fas fa-check-circle mr-1 text-green-500"></i> Already Applied
                                        </span>

                                        <!-- <PrimaryButton @click="oneClickApply(job)"
                                            class="text-sm bg-green-600 hover:bg-green-700">
                                            One-Click Apply
                                        </PrimaryButton> -->

                                        <PrimaryButton v-if="job.company && job.company.id"
                                            @click="goToCompanyProfile(job.company.id)"
                                            class="bg-gray-600 hover:bg-gray-700">

                                            View Company
                                        </PrimaryButton>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- My Applications Tab -->
                        <div v-else>
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
                                            <p class="text-sm text-gray-600">{{ job.company?.company_name || 'Unknown Company' }}</p> </div>
                                        <span
                                            class="px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700">
                                            Applied
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