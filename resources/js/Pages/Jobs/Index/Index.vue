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

const searchQuery = ref('');
const selectedDate = ref(''); // filter by posted date
const selectedJobType = ref('');
const selectedJobLevel= ref('');
const selectedVacancy = ref('');
const selectedCategory = ref('');

// Action loading state
const isActionLoading = ref(false);
const activeJobId = ref(null);
const actionError = ref(null);

const filteredJobs = computed(() => {
    const today = new Date();

    return props.jobs.filter(job => {
        // Normalize job.created_at to Date
        const jobDate = new Date(job.created_at);

        // ---- Search Filter ----
        const matchesSearch = job.job_title
            .toLowerCase()
            .includes(searchQuery.value.toLowerCase());

        // ---- Date Filter ----
        let matchesDate = true;
        if (selectedDate.value === 'today') {
            matchesDate = jobDate.toDateString() === today.toDateString();
        } else if (selectedDate.value === 'week') {
            const startOfWeek = new Date(today);
            startOfWeek.setDate(today.getDate() - today.getDay()); // Sunday
            matchesDate = jobDate >= startOfWeek && jobDate <= today;
        } else if (selectedDate.value === 'month') {
            matchesDate = jobDate.getMonth() === today.getMonth() &&
                          jobDate.getFullYear() === today.getFullYear();
        } else if (selectedDate.value === '3months') {
            const threeMonthsAgo = new Date(today);
            threeMonthsAgo.setMonth(today.getMonth() - 3);
            matchesDate = jobDate >= threeMonthsAgo && jobDate <= today;
        }

        // ---- Job Type Filter ----
        const matchesJobType = selectedJobType.value
            ? job.type === selectedJobType.value
            : true;

        // ---- Job Level Filter ----
        const matchesJobLevel = selectedJobLevel.value
            ? job.level === selectedJobLevel.value
            : true;

        // ---- Vacancy Filter ----
        const matchesVacancy = selectedVacancy.value
            ? job.vacancy == selectedVacancy.value
            : true;
            
        // ---- Category Filter ----
        const matchesCategory = selectedCategory.value
            ? job.category === selectedCategory.value
            : true;

        return matchesSearch && matchesDate && matchesJobType && matchesJobLevel && matchesVacancy && matchesCategory; 
    });
});

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
                <Link :href="route('jobs.create', { user: page.props.auth.user.id })" class="flex-shrink-0">
                    <PrimaryButton class="flex items-center">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Post Jobs
                    </PrimaryButton>
                </Link>

                <Link :href="route('jobs.manage', { user: page.props.auth.user.id })" class="flex-shrink-0">
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
                    <h3 class="text-lg font-semibold text-gray-800">Search & Filters</h3>
                </div>
                
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Search Input -->
                        <div class="col-span-1 md:col-span-2 lg:col-span-1">
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

                        <!-- Category Dropdown -->
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <div class="relative">
                                <select 
                                    id="category"
                                    v-model="selectedCategory" 
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10"
                                >
                                    <option value="">All Categories</option>
                                    <option v-for="(category, index) in categories" :key="index" :value="index + 1">
                                        {{ category }}
                                    </option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Date Dropdown -->
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date Posted</label>
                            <div class="relative">
                                <select 
                                    id="date"
                                    v-model="selectedDate" 
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10"
                                >
                                    <option value="">All Dates</option>
                                    <option v-for="(sector, index) in sectors" :key="index" :value="index + 1">
                                        {{ sector }}
                                    </option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Job Type Dropdown -->
                        <div>
                            <label for="jobType" class="block text-sm font-medium text-gray-700 mb-1">Job Type</label>
                            <div class="relative">
                                <select 
                                    id="jobType"
                                    v-model="selectedJobType" 
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2 appearance-none focus:ring-blue-500 focus:border-blue-500 shadow-sm pr-10"
                                >
                                    <option value="">All Job Types</option>
                                    <option value="Full-time">Full-time</option>
                                    <option value="Part-time">Part-time</option>
                                    <option value="Contract">Contract</option>
                                    <option value="Internship">Internship</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
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
                                <option value="week">Opened This Week</option>
                                <option value="month">Opened This Month</option>
                                <option value="3months">Opened in Last 3 Months</option>
                                <option value="custom">Custom Range…</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
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
                        <h3 class="text-lg font-semibold text-gray-800">Available Jobs</h3>
                        <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ filteredJobs.length }} jobs</span>
                    </div>
                </div>
                <MyJobs :jobs="filteredJobs.length > 0 ? filteredJobs : jobs" :sectors="sectors" :categories="categories" />
            </div>
        </Container>
    </AppLayout>
</template>