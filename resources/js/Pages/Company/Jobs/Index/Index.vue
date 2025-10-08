<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, usePage } from '@inertiajs/vue3'
import Container from '@/Components/Container.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Link } from '@inertiajs/vue3';
import MyJobs from './MyJobs.vue'
import { ref, computed } from 'vue';
import '@fortawesome/fontawesome-free/css/all.css';

const page = usePage()
const props = defineProps({
    jobs: Object,
    sectors: Array,
    categories: Array,
    totalJobs: Number,
    openJobs: Number,
    closedJobs: Number,
    expiredJobs: Number,
})

// Added: actionError ref to remove Vue warning
const actionError = ref(null)

// Filters
const searchQuery = ref('');
const selectedJobType = ref('');
const selectedWorkEnvironment = ref('');
// Status filter removed from UI logic; still kept variable if needed
const selectedStatus = ref(''); 
const selectedExperienceLevel = ref('');
const selectedOpenDate = ref('');
const customFromDate = ref('');
const customToDate = ref('');


const workEnvironmentOptions = [
    { value: '', label: 'All Work Environments' },
    { value: '1', label: 'On-site' },
    { value: '2', label: 'Remote' },
    { value: '3', label: 'Hybrid' }
];

// (Status options no longer shown – list is forced to OPEN jobs)
// const statusOptions = [...]

const experienceLevelOptions = [
    { value: '', label: 'All Experience Levels' },
    { value: 'Entry-level', label: 'Entry-level' },
    { value: 'Intermediate', label: 'Intermediate' },
    { value: 'Mid-level', label: 'Mid-level' },
    { value: 'Senior/Executive', label: 'Senior/Executive-level' }
];

// Only OPEN jobs, newest first (client-side safeguard)
const jobData = computed(() => {
    // Accept both paginator object {data:[]} or plain array []
    if (Array.isArray(props.jobs)) return props.jobs
    return Array.isArray(props.jobs?.data) ? props.jobs.data : []
})

// UPDATE filteredJobs to use jobData and safer status check
const filteredJobs = computed(() => {
    const base = jobData.value.filter(job =>
        ['open','pending'].includes((job.status || '').toLowerCase())
    )

    return base
        .filter(job => {
            const title = (job.job_title || '').toLowerCase()
            const matchesSearch = title.includes(searchQuery.value.toLowerCase())
            const matchesJobType = selectedJobType.value
                ? String(job.job_type) === String(selectedJobType.value)
                : true
            const matchesWorkEnv = selectedWorkEnvironment.value
                ? String(job.work_environment) === String(selectedWorkEnvironment.value)
                : true
            const matchesExperience = selectedExperienceLevel.value
                ? job.job_experience_level === selectedExperienceLevel.value
                : true

            // Posted date filter
            const createdRaw = job.created_at || job.posted_at
            const createdDate = createdRaw ? new Date(createdRaw) : null
            let matchesOpenDate = true
            if (createdDate) {
                const today = new Date()
                if (selectedOpenDate.value === 'today') {
                    matchesOpenDate = createdDate.toDateString() === today.toDateString()
                } else if (selectedOpenDate.value === 'week') {
                    const weekAgo = new Date(); weekAgo.setDate(today.getDate() - 7)
                    matchesOpenDate = createdDate >= weekAgo
                } else if (selectedOpenDate.value === 'month') {
                    const monthAgo = new Date(); monthAgo.setMonth(today.getMonth() - 1)
                    matchesOpenDate = createdDate >= monthAgo
                } else if (selectedOpenDate.value === '3months') {
                    const threeMonthsAgo = new Date(); threeMonthsAgo.setMonth(today.getMonth() - 3)
                    matchesOpenDate = createdDate >= threeMonthsAgo
                } else if (selectedOpenDate.value === 'custom') {
                    if (customFromDate.value && customToDate.value) {
                        const from = new Date(customFromDate.value)
                        const to = new Date(customToDate.value); to.setHours(23,59,59,999)
                        matchesOpenDate = createdDate >= from && createdDate <= to
                    }
                }
            }

            return matchesSearch &&
                   matchesJobType &&
                   matchesWorkEnv &&
                   matchesExperience &&
                   matchesOpenDate
        })
        .sort((a,b) => new Date(b.created_at || b.posted_at) - new Date(a.created_at || a.posted_at))
})

// Pagination helpers
const jobsMeta = computed(() => props.jobs.meta ?? {});
const jobsLinks = computed(() => props.jobs.links ?? []);
const goTo = (url) => { if (url) router.get(url); };

function resetFilters() {
    searchQuery.value = '';
    selectedJobType.value = '';
    selectedWorkEnvironment.value = '';
    selectedExperienceLevel.value = '';
    selectedOpenDate.value = '';
    customFromDate.value = '';
    customToDate.value = '';
}

// Helper flags
const hasAnyJobs = computed(() => jobData.value.length > 0)
const hasFilteredJobs = computed(() => filteredJobs.value.length > 0)
</script>

<template>
    <AppLayout title="Jobs">
        <template #header>
            <div class="flex items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                    <i class="fas fa-briefcase text-blue-500 mr-2"></i> Jobs
                </h2>
            </div>
        </template>

        <Container  class="py-8 max-w-screen-2xl mx-auto px-8">

            <!-- Stats Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Jobs -->
                <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-briefcase text-white text-xl"></i>
                        </div>
                        <h3 class="text-blue-700 text-sm font-medium mb-2">Total Jobs</h3>
                        <p class="text-3xl font-bold text-blue-700">{{ props.totalJobs }}</p>
                    </div>
                </div>
                <!-- Open Jobs -->
                <div class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-unlock text-white text-xl"></i>
                        </div>
                        <h3 class="text-green-700 text-sm font-medium mb-2">Open Jobs</h3>
                        <p class="text-3xl font-bold text-green-900">
                            {{ props.openJobs }}
                        </p>
                    </div>
                </div>
                <!-- Closed Jobs -->
                <div class="bg-gradient-to-br from-red-100 to-red-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-lock text-white text-xl"></i>
                        </div>
                        <h3 class="text-red-700 text-sm font-medium mb-2">Closed Jobs</h3>
                        <p class="text-3xl font-bold text-red-900">
                            {{ props.closedJobs }}
                        </p>
                    </div>
                </div>
                <!-- Expired Jobs -->
                <div class="bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-hourglass-end text-white text-xl"></i>
                        </div>
                        <h3 class="text-yellow-700 text-sm font-medium mb-2">Expired Jobs</h3>
                        <p class="text-3xl font-bold text-yellow-900">
                            {{ props.expiredJobs }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Search and filters -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-200 flex items-center">
                     <i class="fas fa-filter text-blue-500 mr-2"></i>
                    <div>
                        <h3 class="font-medium text-gray-700">Search & Filter</h3>
                        <p class="text-sm text-gray-500">Find jobs by criteria below</p>
                    </div>
                    <div class="ml-auto">
                        <PrimaryButton
                            class="px-6 py-3 bg-white text-gray-700 rounded-xl text-sm font-medium flex items-center hover:bg-gray-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 shadow-sm border border-gray-300"
                            @click="resetFilters">
                            <i class="fas fa-undo mr-2"></i> Reset Filters
                        </PrimaryButton>
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
                                    v-model="searchQuery" 
                                    type="text" 
                                    placeholder="Search jobs..." 
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                />
                            </div>
                        </div>

                        <!-- Job Type Dropdown -->
                        <div>
                            <label for="jobType" class="block text-sm font-medium text-gray-700 mb-1">Employment Type</label>
                            <div class="relative">
                                <select 
                                    id="jobType"
                                    v-model="selectedJobType" 
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10">
                                    <option value="">All Employment Types</option>
                                    <option value="1">Full-time</option>
                                    <option value="2">Part-time</option>
                                    <option value="3">Contract</option>
                                    <option value="4">Freelance</option>
                                    <option value="5">Internship</option>
                                </select>
                            </div>
                        </div>

                        <!-- Work Environment Dropdown -->
                        <div>
                            <label for="workEnv" class="block text-sm font-medium text-gray-700 mb-1">Work Environment</label>
                            <div class="relative">
                                <select 
                                    id="workEnv"
                                    v-model="selectedWorkEnvironment" 
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10"
            >
                                    <option v-for="option in workEnvironmentOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <!-- Experience Level Dropdown -->
                        <div>
                            <label for="expLevel" class="block text-sm font-medium text-gray-700 mb-1">Experience Level</label>
                            <div class="relative">
                                <select 
                                    id="expLevel"
                                    v-model="selectedExperienceLevel" 
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10"
                                >
                                    <option v-for="option in experienceLevelOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label for="openDate" class="block text-sm font-medium text-gray-700 mb-1">
                                Posted Date
                            </label>
                            <div class="relative">
                                <select 
                                id="openDate"
                                v-model="selectedOpenDate" 
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none 
                                        focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10"
                                >
                                    <option value="">All Dates</option>
                                    <option value="today">Opened Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="3months">In Last 3 Months</option>
                                    <option value="custom">Custom Range…</option>
                                </select>
                            </div>
                            <!-- Custom Range Fields -->
                            <div v-if="selectedOpenDate === 'custom'" class="flex gap-2 mt-2">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600">From</label>
                                    <input 
                                    type="date" 
                                    v-model="customFromDate" 
                                    class="border border-gray-300 rounded px-2 py-1 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600">To</label>
                                    <input 
                                    type="date" 
                                    v-model="customToDate" 
                                    :min="customFromDate || undefined"
                                    class="border border-gray-300 rounded px-2 py-1 focus:ring-blue-500 focus:border-blue-500"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Spacing between filter and action buttons -->
            <div class="my-6"></div>

            <!-- Action Buttons -->
            <div class="bg-white rounded-2xl p-6 flex flex-wrap items-center justify-between gap-4 border border-gray-100 mb-8">
                <div class="flex items-center space-x-4">
                    <Link :href="route('company.jobs.manage', { user: page.props.auth.user.id })"
                        :class="['px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border',
                            'text-gray-600 hover:bg-gray-50 border-gray-200']">
                        <i class="fas fa-tasks mr-2"></i>
                        <span>Manage Posted Jobs</span>
                    </Link>
                    <Link :href="route('company.jobs.archivedlist', { user: page.props.auth.user.id })"
                        :class="['px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border',
                            'text-gray-600 hover:bg-gray-50 border-gray-200']">
                        <i class="fas fa-archive mr-2"></i>
                        <span>Archived Jobs</span>
                    </Link>
                </div>
               <div class="flex justify-end space-x-3">
                    <!-- Post Jobs -->
                    <Link 
                        :href="route('company.jobs.create', { user: page.props.auth.user.id })"
                        :class="[route().current('company.jobs.create') ? 'bg-blue-600' : 'bg-blue-500 hover:bg-blue-600',
                            'px-4 py-2 rounded-md text-white font-medium transition-colors flex items-center shadow-sm']">
                        <i class="fas fa-plus-circle mr-2"></i>
                        <span>Post Jobs</span>
                    </Link>

                    <!-- Batch Upload Jobs -->
                    <Link
                        :href="route('company.jobs.batch.page')"
                        class="px-4 py-2 rounded-md text-white font-medium transition-colors flex items-center shadow-sm bg-blue-500 hover:bg-blue-600">
                        <i class="fas fa-upload mr-2"></i>
                        <span>Batch Upload Jobs</span>
                    </Link>
                </div>

            </div>

            <!-- Error message -->
            <div v-if="actionError" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline ml-1">{{ actionError }}</span>
                <button @click="actionError = null" class="absolute top-0 bottom-0 right-0 px-4 py-3" aria-label="Close alert">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Jobs listing -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-briefcase text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">My Jobs</h3>
                            <p class="text-sm text-gray-500">View and manage your job postings</p>
                        </div>
                    </div>
                    <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ props.totalJobs }} jobs</span>
                </div>
                <!-- Jobs listing and empty states -->
                <div>
                    <!-- No posted jobs at all -->
                    <div v-if="props.jobs.data.length === 0" class="p-8 text-center text-gray-500">
                        <i class="fas fa-briefcase text-3xl mb-2 text-gray-400"></i>
                        <div class="mt-2 text-lg font-semibold">You have not posted any jobs yet.</div>
                        <div class="mt-1 text-sm">Click <span class="font-bold text-blue-600">Post Jobs</span> above to create your first job posting.</div>
                    </div>
                    <!-- Jobs exist, but none match filter -->

                    <div v-else class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                        <MyJobs
                            v-if="filteredJobs.length > 0"
                            :jobs="filteredJobs"
                            :sectors="sectors"
                            :categories="categories"
                        />
                        <div v-else class="p-8 text-center text-gray-500">
                            <i class="fas fa-search-minus text-3xl mb-2 text-gray-400"></i>
                            <div class="mt-2 text-lg font-semibold">No jobs found matching your search.</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6 flex justify-center">
              <nav v-if="jobsLinks && jobsLinks.length > 3" aria-label="Page navigation">
                <ul class="inline-flex items-center -space-x-px rounded-md shadow-sm">
                  <li v-for="link in jobsLinks" :key="link.url">
                    <a v-if="link.url" @click.prevent="goTo(link.url)"
                      :class="[

                        'relative inline-flex items-center px-4 py-2 text-sm font-medium border',
                        link.active
                          ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                          : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                        link.label.includes('Previous') ? 'rounded-l-md' : '',
                        link.label.includes('Next') ? 'rounded-r-md' : ''
                      ]"
                      :aria-current="link.active ? 'page' : undefined">
                      <span v-html="link.label"></span>
                    </a>
                    <span v-else
                      class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-300 cursor-not-allowed">
                      <span v-html="link.label"></span>
                    </span>
                  </li>
                </ul>
              </nav>
            </div>
        </Container>
    </AppLayout>
</template>