<script setup>
import Graduate from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { ref, onMounted, computed, watch } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import '@fortawesome/fontawesome-free/css/all.css';
import { debounce } from 'lodash';

// State Management
const searchQuery = ref('');
const selectedJobType = ref('');
const selectedLocation = ref('');
const selectedIndustry = ref('');
const selectedSalaryMin = ref('');
const selectedSalaryMax = ref('');
const selectedSkills = ref([]);
const selectedExperience = ref('');
const selectedCompany = ref('');
const isViewDetailsModalOpen = ref(false);
const isApplyModalOpen = ref(false);
const selectedJob = ref(null);
const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Props and Page Data
const { props } = usePage();

// Forms for data management
const jobsForm = useForm({
    jobs: props.jobs || [],
    loading: false
});

const employmentPreferences = ref({
  jobTypes: props.employmentPreferences?.job_types
    ? props.employmentPreferences.job_types.split(',')
    : [],
  salaryExpectations: props.employmentPreferences?.salary_expectations
    ? JSON.parse(props.employmentPreferences.salary_expectations)
    : { range: '', frequency: 'per year' },
  preferredLocations: props.employmentPreferences?.preferred_locations
    ? props.employmentPreferences.preferred_locations.split(',')
    : [],
  workEnvironment: props.employmentPreferences?.work_environment
    ? props.employmentPreferences.work_environment.split(',')
    : [],
  availability: props.employmentPreferences?.availability
    ? props.employmentPreferences.availability.split(',')
    : [],
  additionalNotes: props.employmentPreferences?.additional_notes || ''
});



// Fetch employment preferences
function fetchEmploymentPreferences() {
    router.get(route('profile.index'), {}, {
        onSuccess: (response) => {
            const data = response.props;
            if (data.preferences) {
                employmentPreferences.jobTypes = data.preferences.jobTypes || [];
                employmentPreferences.salaryExpectations = data.preferences.salaryExpectations || {
                    range: '',
                    frequency: 'per month'
                };
                employmentPreferences.preferredLocations = data.preferences.preferredLocations || [];
                employmentPreferences.workEnvironment = data.preferences.workEnvironment || [];
                employmentPreferences.availability = data.preferences.availability || [];
                employmentPreferences.additionalNotes = data.preferences.additionalNotes || '';
            }
        },
        preserveState: true
    });
}
function fetchJobs() {
  if (jobsForm.loading) return;
  jobsForm.loading = true;
  router.get(route('job.search'), {
    search: searchQuery.value,
    jobType: selectedJobType.value,
    location: selectedLocation.value,
    industry: selectedIndustry.value,
    salaryMin: selectedSalaryMin.value,
    salaryMax: selectedSalaryMax.value,
    skills: selectedSkills.value,
    experience: selectedExperience.value,
    company: selectedCompany.value,
  }, {
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
// Fetch jobs with filters

// Apply for a job
const applyForm = useForm({ job_id: null, cover_letter: '' });
function showApplyModal(job) {
    selectedJob.value = job;
    applyForm.job_id = job.id;
    applyForm.cover_letter = '';
    isApplyModalOpen.value = true;
}

function submitApplication() {
    applyForm.post(route('apply-for-job'), {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = 'Successfully applied for the job!';
            isApplyModalOpen.value = false;
            isSuccessModalOpen.value = true;
            fetchJobs();
        },
        onError: () => {
            errorMessage.value = 'Failed to apply for the job. Please try again.';
            isErrorModalOpen.value = true;
        }
    });
}

function closeApplyModal() {
    isApplyModalOpen.value = false;
    selectedJob.value = null;
}

// Visit company website
function visitCompanyWebsite(website) {
    if (website) {
        // Ensure URL has proper protocol
        let url = website;
        if (!url.startsWith('http://') && !url.startsWith('https://')) {
            url = 'https://' + url;
        }
        window.open(url, '_blank');
    }
}

// View job details
function viewJobDetails(job) {
    selectedJob.value = job;
    isViewDetailsModalOpen.value = true;
}

// Close modals
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

// Calculate match percentage based on employment preferences
function calculateMatchPercentage(job) {
  let matchPoints = 0;
  let totalPoints = 0;

  // Job type match
  if (employmentPreferences.value.jobTypes.includes(job.job_employment_type)) {
    matchPoints += 30;
  }
  totalPoints += 30;

  // Location match
  if (employmentPreferences.value.preferredLocations.some(loc =>
    job.job_location && job.job_location.toLowerCase().includes(loc.toLowerCase())
  )) {
    matchPoints += 25;
  }
  totalPoints += 25;

  // Salary match
  if (job.salary_range && employmentPreferences.value.salaryExpectations.range) {
    if (job.salary_range.includes(employmentPreferences.value.salaryExpectations.range.split(' ')[0]) ||
      employmentPreferences.value.salaryExpectations.range.includes(job.salary_range.split(' ')[0])) {
      matchPoints += 20;
    }
  }
  totalPoints += 20;

  // Work environment match
  if (job.job_work_environment && employmentPreferences.value.workEnvironment.includes(job.job_work_environment)) {
    matchPoints += 15;
  }
  totalPoints += 15;

  // Skills match (if available)
  if (job.related_skills && job.related_skills.length > 0) {
    matchPoints += 10;
  }
  totalPoints += 10;

  return Math.round((matchPoints / totalPoints) * 100);
}

// Format salary for display
function formatSalary(salaryRange) {
    if (!salaryRange) return 'Not specified';
    return salaryRange;
}

// Watch for search changes
const debouncedFetchJobs = debounce(fetchJobs, 400);

watch(
  [
    searchQuery,
    selectedJobType,
    selectedLocation,
    selectedIndustry,
    selectedSalaryMin,
    selectedSalaryMax,
    selectedSkills,
    selectedExperience,
    selectedCompany
  ],
  debouncedFetchJobs,
  { deep: false }
);

// Initialize data when component mounts
onMounted(() => {
    fetchEmploymentPreferences();
    fetchJobs();
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
                            <p class="text-sm text-[#374151] mt-1">Discover and apply for jobs that match your skills and interests</p>
                        </header>

                        <div class="mb-8">
                          <div class="flex flex-col md:flex-row gap-4">
                            <TextInput v-model="searchQuery" class="w-full" placeholder="Search jobs by title, company, or location" />
                            <select v-model="selectedJobType" class="border rounded-md px-3 py-2">
                              <option value="">Job Type</option>
                              <option v-for="type in jobTypes" :key="type" :value="type">{{ type }}</option>
                            </select>
                            <select v-model="selectedIndustry" class="border rounded-md px-3 py-2">
                              <option value="">Industry</option>
                              <option v-for="industry in props.industries" :key="industry.id" :value="industry.id">{{ industry.name }}</option>
                            </select>
                            <select v-model="selectedExperience" class="border rounded-md px-3 py-2">
                              <option value="">Experience Level</option>
                              <option v-for="level in experienceLevels" :key="level" :value="level">{{ level }}</option>
                            </select>
                            <select v-model="selectedCompany" class="border rounded-md px-3 py-2">
                              <option value="">Company</option>
                              <option v-for="company in props.companies" :key="company.id" :value="company.id">{{ company.company_name }}</option>
                            </select>
                            <TextInput v-model="selectedSalaryMin" type="number" class="w-24" placeholder="Min Salary" />
                            <TextInput v-model="selectedSalaryMax" type="number" class="w-24" placeholder="Max Salary" />
                            <!-- Skills multi-select (basic example) -->
                            <input v-model="selectedSkills" placeholder="Skills (comma separated)" class="border rounded-md px-3 py-2" />
                          </div>
                        </div>

                        <h2 class="text-xl font-semibold mb-4">Job Listings</h2>

                        <!-- Loading State -->
                        <div v-if="jobsForm.loading" class="flex justify-center items-center py-8">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                        </div>

                        <!-- Empty State -->
                        <div v-else-if="!jobsForm.jobs.length" class="text-center py-8">
                            <div class="text-gray-400">
                                <i class="fas fa-briefcase text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium">No Jobs Found</h3>
                                <p class="text-sm">Try adjusting your search criteria</p>
                            </div>
                        </div>

                        <!-- Job Listings -->
                        <div v-else class="grid grid-cols-1 gap-6">
                            <div v-for="job in jobsForm.jobs" :key="job.id" class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
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
                                        <span class="px-3 py-1 text-xs font-medium rounded-full" 
                                            :class="{
                                                'bg-indigo-100 text-indigo-800': calculateMatchPercentage(job) >= 90,
                                                'bg-blue-100 text-blue-800': calculateMatchPercentage(job) >= 80 && calculateMatchPercentage(job) < 90,
                                                'bg-green-100 text-green-800': calculateMatchPercentage(job) >= 70 && calculateMatchPercentage(job) < 80,
                                                'bg-yellow-100 text-yellow-800': calculateMatchPercentage(job) >= 50 && calculateMatchPercentage(job) < 70,
                                                'bg-gray-100 text-gray-800': calculateMatchPercentage(job) < 50
                                            }">
                                            {{ calculateMatchPercentage(job) }}% Match
                                        </span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-map-marker-alt mr-2"></i>
                                        {{ job.job_location }}
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-briefcase mr-2"></i>
                                        {{ job.job_employment_type }}
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-clock mr-2"></i>
                                        {{ job.job_experience_level }}
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-dollar-sign mr-2"></i>
                                        {{ formatSalary(job.salary_range) }}
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span v-for="skill in job.related_skills" :key="skill" class="px-3 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 rounded-full">
                                        {{ skill }}
                                    </span>
                                </div>
                                <div class="flex space-x-3">
                                    <PrimaryButton @click="viewJobDetails(job)" class="text-sm">
                                        View Details
                                    </PrimaryButton>
                                    <PrimaryButton @click="showApplyModal(job)" class="text-sm bg-green-600 hover:bg-green-700">
                                        Apply Now
                                    </PrimaryButton>
                                    <PrimaryButton @click="visitCompanyWebsite(job.company_website)" class="text-sm bg-gray-600 hover:bg-gray-700">
                                        View Company
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>

                        <!-- Job Details Modal -->
                        <Modal :show="isViewDetailsModalOpen" @close="closeDetailsModal" max-width="2xl">
                            <div class="p-6" v-if="selectedJob">
                                <div class="flex justify-between items-start mb-6">
                                    <div>
                                        <h2 class="text-xl font-bold text-gray-900">{{ selectedJob.title }}</h2>
                                        <p class="text-md text-gray-600">{{ selectedJob.company }}</p>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full"
                                        :class="{
                                            'bg-indigo-100 text-indigo-800': calculateMatchPercentage(selectedJob) >= 90,
                                            'bg-blue-100 text-blue-800': calculateMatchPercentage(selectedJob) >= 80 && calculateMatchPercentage(selectedJob) < 90,
                                            'bg-green-100 text-green-800': calculateMatchPercentage(selectedJob) >= 70 && calculateMatchPercentage(selectedJob) < 80,
                                            'bg-yellow-100 text-yellow-800': calculateMatchPercentage(selectedJob) >= 50 && calculateMatchPercentage(selectedJob) < 70,
                                            'bg-gray-100 text-gray-800': calculateMatchPercentage(selectedJob) < 50
                                        }">
                                        {{ calculateMatchPercentage(selectedJob) }}% Match
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-map-marker-alt mr-2"></i>
                                        {{ selectedJob.location }}
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-briefcase mr-2"></i>
                                        {{ selectedJob.type }}
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-clock mr-2"></i>
                                        {{ selectedJob.experience_level }}
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-dollar-sign mr-2"></i>
                                        {{ formatSalary(selectedJob.salary_range) }}
                                    </div>
                                </div>
                                <div class="mb-6">
                                    <h3 class="text-lg font-semibold mb-2">Job Description</h3>
                                    <p class="text-gray-700">{{ selectedJob.description }}</p>
                                </div>
                                <div class="mb-6">
                                    <h3 class="text-lg font-semibold mb-2">Required Skills</h3>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="skill in selectedJob.related_skills" :key="skill" class="px-3 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 rounded-full">
                                            {{ skill }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex justify-end space-x-3">
                                    <button @click="closeDetailsModal" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                        Close
                                    </button>
                                    <PrimaryButton @click="showApplyModal(selectedJob)" class="bg-green-600 hover:bg-green-700">
                                        Apply Now
                                    </PrimaryButton>
                                    <PrimaryButton @click="visitCompanyWebsite(selectedJob.company_website)" class="bg-gray-600 hover:bg-gray-700">
                                        View Company
                                    </PrimaryButton>
                                </div>
                            </div>
                        </Modal>

                        <!-- Success Modal -->
                        <Modal :show="isSuccessModalOpen" @close="closeSuccessModal">
                            <div class="p-6">
                                <div class="flex items-center justify-center mb-4">
                                    <div class="bg-green-100 rounded-full p-2">
                                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-lg font-medium text-center mb-4">{{ successMessage }}</h3>
                                <div class="flex justify-center">
                                    <button @click="closeSuccessModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        OK
                                    </button>
                                </div>
                            </div>
                        </Modal>

                        <!-- Error Modal -->
                        <Modal :show="isErrorModalOpen" @close="closeErrorModal">
                            <div class="p-6">
                                <div class="flex items-center justify-center mb-4">
                                    <div class="bg-red-100 rounded-full p-2">
                                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-lg font-medium text-center mb-4">{{ errorMessage }}</h3>
                                <div class="flex justify-center">
                                    <button @click="closeErrorModal" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        OK
                                    </button>
                                </div>
                            </div>
                        </Modal>
                        
                        <!-- Apply Job Modal -->
                        <Modal :show="isApplyModalOpen" @close="closeApplyModal" max-width="2xl">
                            <div class="p-6" v-if="selectedJob">
                                <div class="flex justify-between items-start mb-6">
                                    <div>
                                        <h2 class="text-xl font-bold text-gray-900">Apply for: {{ selectedJob.title }}</h2>
                                        <p class="text-md text-gray-600">{{ selectedJob.company }}</p>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full"
                                        :class="{
                                            'bg-indigo-100 text-indigo-800': calculateMatchPercentage(selectedJob) >= 90,
                                            'bg-blue-100 text-blue-800': calculateMatchPercentage(selectedJob) >= 80 && calculateMatchPercentage(selectedJob) < 90,
                                            'bg-green-100 text-green-800': calculateMatchPercentage(selectedJob) >= 70 && calculateMatchPercentage(selectedJob) < 80,
                                            'bg-yellow-100 text-yellow-800': calculateMatchPercentage(selectedJob) >= 50 && calculateMatchPercentage(selectedJob) < 70,
                                            'bg-gray-100 text-gray-800': calculateMatchPercentage(selectedJob) < 50
                                        }">
                                        {{ calculateMatchPercentage(selectedJob) }}% Match
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-map-marker-alt mr-2"></i>
                                        {{ selectedJob.location }}
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-briefcase mr-2"></i>
                                        {{ selectedJob.type }}
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-clock mr-2"></i>
                                        {{ selectedJob.experience_level }}
                                    </div>
                                    <div class="flex items-center text-gray-600">
                                        <i class="fas fa-dollar-sign mr-2"></i>
                                        {{ formatSalary(selectedJob.salary_range) }}
                                    </div>
                                </div>
                                
                                <div class="mb-6">
                                    <h3 class="text-lg font-semibold mb-2">Cover Letter (Optional)</h3>
                                    <textarea 
                                        v-model="applyForm.cover_letter"
                                        class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        rows="5"
                                        placeholder="Tell the employer why you're a good fit for this position..."
                                    ></textarea>
                                </div>
                                
                                <div class="flex justify-end space-x-3">
                                    <button @click="closeApplyModal" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                        Cancel
                                    </button>
                                    <PrimaryButton @click="submitApplication" class="bg-green-600 hover:bg-green-700">
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