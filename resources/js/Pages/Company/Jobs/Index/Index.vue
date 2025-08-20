<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, usePage } from '@inertiajs/vue3'
import Container from '@/Components/Container.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import MyJobs from './MyJobs.vue'
import { ref, computed } from 'vue';
import '@fortawesome/fontawesome-free/css/all.css';

const page = usePage()

const props = defineProps({
    jobs: Array,
    sectors: Array,
    categories: Array,
})



// Action loading state
const isActionLoading = ref(false);
const activeJobId = ref(null);
const actionError = ref(null);

// Filters
const searchQuery = ref('');
const selectedJobType = ref('');
const selectedWorkEnvironment = ref('');
const selectedStatus = ref('');
const selectedExperienceLevel = ref('');

const workEnvironmentOptions = [
    { value: '', label: 'All Work Environments' },
    { value: '1', label: 'On-site' },
    { value: '2', label: 'Remote' },
    { value: '3', label: 'Hybrid' }
];

const statusOptions = [
    { value: '', label: 'All Statuses' },
    { value: 'open', label: 'Open' },
    { value: 'closed', label: 'Closed' },
    { value: 'expired', label: 'Expired' },
    { value: 'full', label: 'Full' }
];

const experienceLevelOptions = [
    { value: '', label: 'All Experience Levels' },
    { value: 'Entry-level', label: 'Entry-level' },
    { value: 'Intermediate', label: 'Intermediate' },
    { value: 'Mid-level', label: 'Mid-level' },
    { value: 'Senior/Executive', label: 'Senior/Executive-level' }
];

const filteredJobs = computed(() => {
    return props.jobs.filter(job => {
        const matchesSearch = job.job_title.toLowerCase().includes(searchQuery.value.toLowerCase());
        const matchesJobType = selectedJobType.value
            ? job.job_type == selectedJobType.value
            : true;
        const matchesWorkEnv = selectedWorkEnvironment.value
            ? String(job.work_environment) === selectedWorkEnvironment.value
            : true;
        const matchesStatus = selectedStatus.value
            ? job.status === selectedStatus.value
            : true;
        const matchesExperience = selectedExperienceLevel.value
            ? job.job_experience_level === selectedExperienceLevel.value
            : true;
        return matchesSearch && matchesJobType && matchesWorkEnv && matchesStatus && matchesExperience;
    });
});

function resetFilters() {
    searchQuery.value = '';
    selectedJobType.value = '';
    selectedWorkEnvironment.value = '';
    selectedStatus.value = '';
    selectedExperienceLevel.value = '';
}

const form = useForm({
    name: '',
    description: '',
    from_datetime: '',
    to_datetime: '',
    location: ''
});
</script>

<template>
    <AppLayout title="Jobs">
        <template #header>
            <div class="flex items-center">
                <i class="fas fa-briefcase text-blue-500 text-xl mr-2"></i>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Jobs
                </h2>
            </div>
        </template>

        <Container class="py-6">
            <!-- Action buttons -->
            <div class="flex flex-wrap gap-3 mb-6">
                <Link :href="route('company.jobs.create', { user: page.props.auth.user.id })" class="flex-shrink-0">
                    <PrimaryButton class="flex items-center">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Post Jobs
                    </PrimaryButton>
                </Link>

                <Link :href="route('company.jobs.manage', { user: page.props.auth.user.id })" class="flex-shrink-0">
                    <PrimaryButton class="flex items-center">
                        <i class="fas fa-tasks mr-2"></i>
                        Manage Posted Jobs
                    </PrimaryButton>
                </Link>

                <Link :href="route('jobs.archivedlist', { user: page.props.auth.user.id })" class="flex-shrink-0">
                    <PrimaryButton class="flex items-center">
                        <i class="fas fa-archive mr-2"></i>
                        Archived Jobs
                    </PrimaryButton>
                </Link>
            </div>

            <!-- Search and filters -->
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6 shadow-sm">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Search & Filter</h3>
                </div>
                <div class="p-4">
                    <div class="flex justify-end mb-2">
                        <PrimaryButton
                            class="bg-blue-200 text-gray-700 hover:bg-gray-300 border-none px-4 py-2 rounded"
                            @click="resetFilters"
                        >
                            <i class="fas fa-undo mr-2"></i> Reset Filters
                        </PrimaryButton>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10"
                                >
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

                        <!-- Status Dropdown -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <div class="relative">
                                <select 
                                    id="status"
                                    v-model="selectedStatus" 
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10"
                                >
                                    <option v-for="option in statusOptions" :key="option.value" :value="option.value">
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
                    </div>
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
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                    <div class="flex items-center">
                        <h3 class="text-lg font-semibold text-gray-800">List of Jobs</h3>
                        <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ filteredJobs.length }} jobs</span>
                    </div>
                </div>
                <MyJobs
                    v-if="filteredJobs.length > 0"
                    :jobs="filteredJobs"
                    :sectors="sectors"
                    :categories="categories"
                />
                <!-- Show a message if no jobs match the search/filter -->
                <div v-else class="p-8 text-center text-gray-500">
                    <i class="fas fa-search-minus text-3xl mb-2 text-gray-400"></i>
                    <div class="mt-2 text-lg font-semibold">No jobs found matching your search.</div>
                </div>
            </div>
        </Container>
    </AppLayout>
</template>