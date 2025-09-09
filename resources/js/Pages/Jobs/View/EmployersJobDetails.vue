<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import '@fortawesome/fontawesome-free/css/all.css';
 
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
        <div>
        <div class="flex items-center">
          <i class="fas fa-briefcase text-blue-500 mr-2 text-2xl"></i>
          <h1 class="text-2xl font-bold text-gray-800">Job Details</h1>
        </div>
        <p class="text-gray-500 text-sm mt-1">View full job information and requirements</p>
        </div>
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
                                <span>Posted on {{ formatDate(job.posted_at) }}</span>
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
 
             <!-- Unified White Background Card for Tabs, Main Content, and Sidebar -->
            <div class="bg-white rounded-lg shadow-sm border border-white-200 mb-6 overflow-hidden p-6">
                 <div class="flex items-center justify-between border-b border-gray-200 overflow-x-auto mb-6">
                    <!-- Tabs Navigation -->
                    <div class="flex">
                        <h2 class="px-4 py-3 text-lg font-bold flex items-center whitespace-nowrap text-blue-600 border-b-2 border-blue-500">
                            <i class="fas fa-clipboard-list mr-2"></i>
                            Job Details
                        </h2>
                    </div>
                    <!-- Actions Card -->
                    <div class="flex items-center gap-2">
                        <Link :href="route('jobs.edit', { job: job.id })" class="flex items-center px-8 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Job
                        </Link>
                        <Link :href="route('jobs.manage', { user: page.props.auth.user.id })" class="flex items-center px-8 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-list mr-2"></i>
                            View All Jobs
                        </Link>
                    </div>
                </div>

    
                <!-- Main Content Area -->
                <div class="flex flex-col lg:flex-row gap-6">
                    <!-- Main Content -->
                    <div class="lg:flex-1 space-y-6">
                        <!-- Job Details Tab -->
                        <div v-if="activeTab === 'details'" class="space-y-6">
                            <!-- Job Type and Experience Level -->
                            <div class="bg-white rounded-lg shadow-sm border border-white-200 p-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div class="bg-white p-4 rounded-lg border border-white-200">
                                        <h4 class="text-sm font-medium text-white-500 mb-1">Job Type</h4>
                                        <div class="flex items-center">
                                            <i class="fas fa-business-time text-blue-500 mr-2"></i>
                                            <span class="text-white-800 font-medium">
                                                <template v-if="Array.isArray(job.job_type)">
                                                    {{ job.job_type.length ? job.job_type.join(', ') : 'Not specified' }}
                                                </template>
                                                <template v-else>
                                                    {{ job.job_type || 'Not specified' }}
                                                </template>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="bg-white p-4 rounded-lg border border-white-200">
                                        <h4 class="text-sm font-medium text-white-500 mb-1">Experience Level</h4>
                                        <div class="flex items-center">
                                            <i class="fas fa-chart-line text-purple-500 mr-2"></i>
                                            <span class="text-white-800 font-medium">{{ job.job_experience_level || 'Not specified' }}</span>
                                        </div>
                                    </div>
                                    <div class="bg-white p-4 rounded-lg border border-white-200">
                                        <h4 class="text-sm font-medium text-white-500 mb-1">Work Environment</h4>
                                        <div class="flex items-center">
                                            <i class="fas fa-laptop-house text-teal-500 mr-2"></i>
                                            <span class="text-w-800 font-medium">
                                                <template v-if="Array.isArray(job.work_environment)">
                                                    {{ job.work_environment.length ? job.work_environment.join(', ') : 'Not specified' }}
                                                </template>
                                                <template v-else>
                                                    {{ job.work_environment || 'Not specified' }}
                                                </template>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="bg-white p-4 rounded-lg border border-white-200">
                                        <h4 class="text-sm font-medium text-white-500 mb-1">Vacancies</h4>
                                        <div class="flex items-center">
                                            <i class="fas fa-users text-green-500 mr-2"></i>
                                            <span class="text-white-800 font-medium">{{ job.job_vacancies || 1 }}</span>
                                        </div>
                                    </div>
                                    <!-- Salary Information -->
                                    <div class="bbg-white p-4 rounded-lg border border-white-200">
                                         <h4 class="text-sm font-medium text-white-500 mb-1">
                                             Salary Range
                                            </h4>
                                        <div class="flex items-center">
                                            <i class="fas fa-dollar-sign text-white-400 mr-2"></i>
                                            <span class="text-white-800">
                                                {{ job.salary_range || 'Negotiable' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Job Description -->
                            <div class="bg-white rounded-lg shadow-sm border border-white-200 p-6">
                                <h3 class="text-lg font-medium text-white-800 mb-4 flex items-center border-b border-white-200 pb-3">
                                    <i class="fas fa-align-left text-blue-500 mr-2"></i>
                                    Job Description
                                </h3>
                                <div class="prose max-w-none text-white-700" v-html="job.job_description"></div>
                            </div>
    
                            <!-- Job Requirements -->
                            <div class="bg-white rounded-lg shadow-sm border border-white-200 p-6">
                                <h3 class="text-lg font-medium text-white-800 mb-4 flex items-center border-b border-white-200 pb-3">
                                    <i class="fas fa-list-check text-blue-500 mr-2"></i>
                                    Job Requirements
                                </h3>
                                <div class="prose max-w-none text-white-700" v-html="job.job_requirements"></div>
                            </div>

                            <div class="bg-white rounded-lg shadow-sm border border-white-200 p-6">
                                <h3 class="text-lg font-medium text-white-800 mb-4 flex items-center border-b border-white-200 pb-3">
                                    <i class="fas fa-code text-white-500 mr-2"></i>                 
                                    Required Skills
                                </h3>
                                <div v-if="job.skills && job.skills.length > 0" class="flex flex-wrap gap-2">
                                    <span
                                        v-for="(skill, idx) in job.skills"
                                        :key="skill"
                                        :class="[
                                            'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                            bubbleColors[idx % bubbleColors.length]
                                        ]"
                                    >
                                        <i class="fas fa-check-circle mr-1 text-blue-500"></i>
                                        {{ skill }}
                                    </span>
                                </div>
                                <p v-else class="text-white-500 italic">No specific skills listed for this position</p>
                            </div>

                            <div class="bg-white rounded-lg shadow-sm border border-white-200 p-6">
                                <h3 class="text-lg font-medium text-white-800 mb-4 flex items-center border-b border-white-200 pb-3">
                                    <i class="fas fa-graduation-cap text-indigo-400 mr-2"></i>
                                    Programs
                                </h3>
                                <div class="flex flex-wrap gap-2">
                                    <template v-if="Array.isArray(job.programs) && job.programs.length">
                                    <span
                                        v-for="(program, idx) in job.programs"
                                        :key="program"
                                        :class="[
                                        'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold',
                                        bubbleColors[idx % bubbleColors.length]
                                        ]"
                                    >
                                        {{ program }}
                                    </span>
                                    </template>
                                    <span v-else class="text-white-500">Not specified</span>
                                </div>
                            </div>
    
                        </div>
                    </div>
    
                    <!-- Sidebar -->
                    <div class="lg:w-80 space-y-6">
                        <!-- Job Information Card -->
                        <div class="bg-white rounded-lg shadow-sm border border-white-200 p-6">
                            <h3 class="text-lg font-medium text-white-800 mb-4 flex items-center border-b border-white-200 pb-3">   
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                Job Information
                            </h3>
                            <div class="space-y-4">
                                <div class="bg-white p-3 rounded-lg border border-white-200">
                                    <h4 class="text-sm font-medium text-white-500 mb-1">Posted by</h4>
                                    <div class="flex items-center">
                                        <i class="fas fa-id-card text-white-400 mr-2"></i>
                                        <span class="text-white-800">{{ job.posted_by }}</span>
                                    </div>
                                </div>
                                 
                                <div v-if="job.company" class="space-y-4">
                                    <div class="bg-white p-4 rounded-lg border border-white-200">
                                        <h4 class="text-sm font-medium text-white-500 mb-1">Company Name</h4>
                                        <div class="flex items-center">
                                            <i class="fas fa-building text-white-400 mr-2"></i>
                                            <span class="text-white-800">{{ job.company.name }}</span>
                                        </div>
                                    </div>
                                    <div v-if="job.company.website" class="bg-white p-4 rounded-lg border border-white-200">
                                        <h4 class="text-sm font-medium text-white-500 mb-1">Website</h4>
                                        <div class="flex items-center">
                                            <i class="fas fa-globe text-white-400 mr-2"></i>
                                            <a :href="job.company.website" target="_blank" class="text-white-600 hover:text-white-800 hover:underline">
                                                {{ job.company.website }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-white-500 italic">Company information not available</p>
                                <div class="bg-white p-3 rounded-lg border border-white-200">
                                    <h4 class="text-sm font-medium text-white-500 mb-1">Location</h4>
                                    <div class="flex items-center">
                                        <i class="fas fa-map-marker-alt text-white-400 mr-2"></i>
                                        <span class="text-white-800">
                                            <template v-if="Array.isArray(job.location)">
                                                {{ job.location.length ? job.location.join(', ') : 'Location not specified' }}
                                            </template>
                                            <template v-else>
                                                {{ job.location || 'Location not specified' }}
                                            </template>
                                        </span>
                                    </div>
                                </div>
                                <div class="bg-white p-4 rounded-lg border border-white-200">
                                    <h4 class="text-sm font-medium text-white-500 mb-1">Posted Date</h4>
                                    <div class="flex items-center">
                                        <i class="far fa-calendar-plus text-white-400 mr-2"></i>
                                        <span>{{ formatDate(job.posted_at) }}</span>
                                    </div>
                                </div>
                                <div class="bg-white p-4 rounded-lg border border-white-200">
                                    <h4 class="text-sm font-medium text-white-500 mb-1">Last Updated</h4>
                                    <div class="flex items-center">
                                        <i class="far fa-calendar-check text-white-400 mr-2"></i>
                                        <span>{{ formatDate(job.updated_at)}}</span>
                                    </div>
                                </div>
                                <div class="bg-white p-4 rounded-lg border border-white-200">
                                    <h4 class="text-sm font-medium text-white-500 mb-1">Application Deadline</h4>
                                    <div class="flex items-center">
                                        <i class="far fa-calendar-times text-white-400 mr-2"></i>
                                        <span>{{ formatDate(job.job_deadline) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Container>
    </AppLayout>
</template>
 