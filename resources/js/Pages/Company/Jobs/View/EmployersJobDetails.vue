<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import '@fortawesome/vue-fontawesome';
 
const page = usePage();
const props = defineProps({
    job: Object,
});
 
const activeTab = ref('details');
 
const statusClass = computed(() => {
    switch(props.job.status) {
        case 'active':
            return 'bg-green-100 text-green-800';
        case 'draft':
            return 'bg-yellow-100 text-yellow-800';
        case 'closed':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
});
 
const statusIcon = computed(() => {
    switch(props.job.status) {
        case 'active':
            return 'fa-check-circle';
        case 'draft':
            return 'fa-pencil-alt';
        case 'closed':
            return 'fa-times-circle';
        default:
            return 'fa-circle';
    }
});
 
const formatDate = (dateString) => {
    if (!dateString) return 'Not specified';
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
};
 
const goBack = () => {
    window.history.back();
};
</script>
 
<template>
    <AppLayout title="Job Details">
      <template #header>
        <div class="flex items-center">
          <button @click="goBack" class="flex items-center text-blue-600 hover:text-blue-800 transition-colors mr-4">
              <i class="fas fa-chevron-left mr-2"></i>
          </button>
          <h1 class="text-2xl font-bold text-gray-800">Job Details</h1>
        </div>
      </template>
 
        <Container class="py-10">
            <!-- Job Header Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">{{ job.job_title }}</h2>
                        <div class="flex flex-wrap items-center mt-2 text-sm text-gray-600">
                            <div v-if="job.company" class="flex items-center mr-4 mb-2 md:mb-0">
                                <i class="fas fa-building text-blue-500 mr-1"></i>
                                <span>{{ job.company.name }}</span>
                            </div>
                            <div class="flex items-center mr-4 mb-2 md:mb-0">
                                <i class="fas fa-calendar-alt text-green-500 mr-1"></i>
                                <span>Posted on {{ formatDate(job.created_at) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                              :class="statusClass">
                            <i class="fas mr-1" :class="statusIcon"></i>
                            {{ job.status ? job.status.charAt(0).toUpperCase() + job.status.slice(1) : 'Unknown' }}
                        </span>
                    </div>
                </div>
            </div>
 
            <!-- Tabs Navigation -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 overflow-hidden">
                <div class="flex border-b border-gray-200 overflow-x-auto">
                    <button
                        @click="activeTab = 'details'"
                        :class="['px-4 py-3 text-sm font-medium flex items-center whitespace-nowrap',
                                activeTab === 'details' ? 'text-blue-600 border-b-2 border-blue-500' : 'text-gray-600 hover:text-gray-800']"
                    >
                        <i class="fas fa-clipboard-list mr-2"></i>
                        Job Details
                    </button>
                    <button
                        @click="activeTab = 'info'"
                        :class="['px-4 py-3 text-sm font-medium flex items-center whitespace-nowrap',
                                activeTab === 'info' ? 'text-blue-600 border-b-2 border-blue-500' : 'text-gray-600 hover:text-gray-800']"
                    >
                        <i class="fas fa-info-circle mr-2"></i>
                        Job Information
                    </button>
                    <button
                        @click="activeTab = 'skills'"
                        :class="['px-4 py-3 text-sm font-medium flex items-center whitespace-nowrap',
                                activeTab === 'skills' ? 'text-blue-600 border-b-2 border-blue-500' : 'text-gray-600 hover:text-gray-800']"
                    >
                        <i class="fas fa-code mr-2"></i>
                        Skills
                    </button>
                </div>
            </div>
 
            <!-- Main Content Area -->
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Main Content -->
                <div class="lg:flex-1 space-y-6">
                    <!-- Job Details Tab -->
                    <div v-if="activeTab === 'details'" class="space-y-6">
                        <!-- Job Type and Experience Level -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">Job Type</h4>
                                    <div class="flex items-center">
                                        <i class="fas fa-business-time text-blue-500 mr-2"></i>
                                        <span class="text-gray-800 font-medium">{{ job.job_type || 'Not specified' }}</span>
                                    </div>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">Experience Level</h4>
                                    <div class="flex items-center">
                                        <i class="fas fa-chart-line text-purple-500 mr-2"></i>
                                        <span class="text-gray-800 font-medium">{{ job.experience_level || 'Not specified' }}</span>
                                    </div>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">Work Environment</h4>
                                    <div class="flex items-center">
                                        <i class="fas fa-laptop-house text-teal-500 mr-2"></i>
                                        <span class="text-gray-800 font-medium">{{ job.work_environment || 'Not specified' }}</span>
                                    </div>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">Vacancies</h4>
                                    <div class="flex items-center">
                                        <i class="fas fa-users text-green-500 mr-2"></i>
                                        <span class="text-gray-800 font-medium">{{ job.vacancy || 1 }}</span>
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
                            <div class="prose max-w-none text-gray-700" v-html="job.description"></div>
                        </div>
 
                        <!-- Job Requirements -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center border-b border-gray-200 pb-3">
                                <i class="fas fa-list-check text-blue-500 mr-2"></i>
                                Job Requirements
                            </h3>
                            <div class="prose max-w-none text-gray-700" v-html="job.job_requirements"></div>
                        </div>
                    </div>
 
                    <!-- Job Information Tab -->
                    <div v-if="activeTab === 'info'" class="space-y-6">
                        <!-- Company Information -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center border-b border-gray-200 pb-3">
                                <i class="fas fa-building text-blue-500 mr-2"></i>
                                Company Information
                            </h3>
                            <div v-if="job.company" class="space-y-4">
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">Company Name</h4>
                                    <div class="flex items-center">
                                        <i class="fas fa-building text-gray-400 mr-2"></i>
                                        <span class="text-gray-800">{{ job.company.name }}</span>
                                    </div>
                                </div>
                                <div v-if="job.company.website" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">Website</h4>
                                    <div class="flex items-center">
                                        <i class="fas fa-globe text-gray-400 mr-2"></i>
                                        <a :href="job.company.website" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline">
                                            {{ job.company.website }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-gray-500 italic">Company information not available</p>
                        </div>
 
                        <!-- Location Information -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center border-b border-gray-200 pb-3">
                                <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                                Location
                            </h3>
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div class="flex items-center">
                                    <i class="fas fa-map-pin text-gray-400 mr-2"></i>
                                    <span class="text-gray-800">{{ job.location || 'Location not specified' }}</span>
                                </div>
                            </div>
                        </div>
 
                        <!-- Salary Information -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center border-b border-gray-200 pb-3">
                                <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                                Salary Information
                            </h3>
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Salary Range</h4>
                                <div class="flex items-center">
                                    <i class="fas fa-dollar-sign text-gray-400 mr-2"></i>
                                    <span class="text-gray-800" v-if="job.min_salary && job.max_salary">
                                        ${{ job.min_salary.toLocaleString() }} - ${{ job.max_salary.toLocaleString() }}
                                    </span>
                                    <span class="text-gray-800" v-else-if="job.min_salary">
                                        From ${{ job.min_salary.toLocaleString() }}
                                    </span>
                                    <span class="text-gray-800" v-else-if="job.max_salary">
                                        Up to ${{ job.max_salary.toLocaleString() }}
                                    </span>
                                    <span class="text-gray-500 italic" v-else>Not specified</span>
                                </div>
                            </div>
                        </div>
 
                        <!-- Important Dates -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center border-b border-gray-200 pb-3">
                                <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                                Important Dates
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">Posted Date</h4>
                                    <div class="flex items-center">
                                        <i class="far fa-calendar-plus text-gray-400 mr-2"></i>
                                        <span>{{ formatDate(job.created_at) }}</span>
                                    </div>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">Last Updated</h4>
                                    <div class="flex items-center">
                                        <i class="far fa-calendar-check text-gray-400 mr-2"></i>
                                        <span>{{ formatDate(job.updated_at) }}</span>
                                    </div>
                                </div>
                                <div v-if="job.application_deadline" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">Application Deadline</h4>
                                    <div class="flex items-center">
                                        <i class="far fa-calendar-times text-gray-400 mr-2"></i>
                                        <span>{{ formatDate(job.application_deadline) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
 
                    <!-- Skills Tab -->
                    <div v-if="activeTab === 'skills'" class="space-y-6">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center border-b border-gray-200 pb-3">
                                <i class="fas fa-code text-blue-500 mr-2"></i>
                                Required Skills
                            </h3>
                           
                            <div v-if="job.skills && job.skills.length > 0" class="flex flex-wrap gap-2">
                                <span
                                    v-for="skill in job.skills"
                                    :key="skill.id"
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800"
                                >
                                    <i class="fas fa-check-circle mr-1 text-blue-500"></i>
                                    {{ skill.name }}
                                </span>
                            </div>
                            <p v-else class="text-gray-500 italic">No specific skills listed for this position</p>
                        </div>
                    </div>
                </div>
 
                <!-- Sidebar -->
                <div class="lg:w-80 space-y-6">
                    <!-- Job Information Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center border-b border-gray-200 pb-3">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                            Job Information
                        </h3>
                        <div class="space-y-4">
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Job ID</h4>
                                <div class="flex items-center">
                                    <i class="fas fa-id-card text-gray-400 mr-2"></i>
                                    <span class="text-gray-800">{{ job.id }}</span>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Posted by</h4>
                                <div class="flex items-center">
                                    <i class="fas fa-id-card text-gray-400 mr-2"></i>
                                    <span class="text-gray-800">{{ job.posted_by }}</span>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Vacancies</h4>
                                <div class="flex items-center">
                                    <i class="fas fa-users text-gray-400 mr-2"></i>
                                    <span class="text-gray-800">{{ job.vacancy || 1 }}</span>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Job Type</h4>
                                <div class="flex items-center">
                                    <i class="fas fa-business-time text-gray-400 mr-2"></i>
                                    <span class="text-gray-800">{{ job.job_type || 'Not specified' }}</span>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Experience Level</h4>
                                <div class="flex items-center">
                                    <i class="fas fa-chart-line text-gray-400 mr-2"></i>
                                    <span class="text-gray-800">{{ job.experience_level || 'Not specified' }}</span>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Location</h4>
                                <div class="flex items-center">
                                    <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                                    <span class="text-gray-800">{{ job.location || 'Not specified' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
 
                    <!-- Actions Card -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center border-b border-gray-200 pb-3">
                            <i class="fas fa-cog text-blue-500 mr-2"></i>
                            Actions
                        </h3>
                        <div class="space-y-3">
                            <Link :href="route('jobs.edit', { job: job.id })" class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-edit mr-2"></i>
                                Edit Job
                            </Link>
                            <Link :href="route('jobs.manage', { user: page.props.auth.user.id })" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-list mr-2"></i>
                                View All Jobs
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </Container>
    </AppLayout>
</template>
 