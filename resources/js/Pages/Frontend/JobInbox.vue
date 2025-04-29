<script setup>
import Graduate from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { ref, onMounted, computed } from 'vue';
import { useForm, usePage, Link, router } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import TextArea from '@/Components/TextArea.vue';
import SelectInput from '@/Components/SelectInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import '@fortawesome/fontawesome-free/css/all.css';

// State Management
const activeSection = ref(localStorage.getItem('activeSection') || 'opportunities');
const setActiveSection = (section) => {
    activeSection.value = section;
    localStorage.setItem('activeSection', section);
};

// Modal States
const isViewDetailsModalOpen = ref(false);
const selectedApplication = ref(null);

// Props and Page Data
const { props } = usePage();

// Forms for data management
const opportunitiesForm = useForm({
    opportunities: props.jobOpportunities || [],
    loading: false
});

const applicationsForm = useForm({
    applications: props.jobApplications || [],
    loading: false
});

const notificationsForm = useForm({
    notifications: props.notifications || [],
    loading: false
});

// Computed Properties
const activeApplications = computed(() => applicationsForm.applications.filter(app => app.status !== 'archived'));
const interviewScheduled = computed(() => applicationsForm.applications.filter(app => app.status === 'interview_scheduled'));
const totalApplications = computed(() => applicationsForm.applications.length);
const offersReceived = computed(() => applicationsForm.applications.filter(app => app.status === 'offer_received'));

// Apply for a job
const applyForm = useForm({ job_id: null });
const applyForJob = (jobId) => {
    applyForm.job_id = jobId;
    applyForm.post(route('apply-for-job'), {
        preserveScroll: true,
        onSuccess: () => {
            opportunitiesForm.get(route('job-opportunities'));
            applicationsForm.get(route('job-applications'));
        }
    });
};

// Archive a job opportunity
const archiveForm = useForm({ job_id: null });
const archiveJobOpportunity = (jobId) => {
    archiveForm.job_id = jobId;
    archiveForm.post(route('archive-job-opportunity'), {
        preserveScroll: true,
        onSuccess: () => {
            opportunitiesForm.get(route('job-opportunities'));
        }
    });
};

// Mark notification as read
const notificationForm = useForm({ notification_id: null });
const markNotificationAsRead = (notificationId) => {
    notificationForm.notification_id = notificationId;
    notificationForm.post(route('mark-notification-as-read'), {
        preserveScroll: true,
        onSuccess: () => {
            notificationsForm.get(route('notifications'));
        }
    });
};

// Initialize data when component mounts
onMounted(() => {
    opportunitiesForm.get(route('job-opportunities'));
    applicationsForm.get(route('job-applications'));
    notificationsForm.get(route('notifications'));
});
</script>

<template>
    <Graduate>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                            <button
                                @click="setActiveSection('opportunities')"
                                :class="[
                                    activeSection === 'opportunities'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                ]"
                            >
                                Opportunities
                            </button>
                            <button
                                @click="setActiveSection('applications')"
                                :class="[
                                    activeSection === 'applications'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                ]"
                            >
                                Applications
                            </button>
                            <button
                                @click="setActiveSection('notifications')"
                                :class="[
                                    activeSection === 'notifications'
                                        ? 'border-indigo-500 text-indigo-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm relative'
                                ]"
                            >
                                Notifications
                                <span v-if="notificationsForm.notifications.length" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                    {{ notificationsForm.notifications.length }}
                                </span>
                            </button>
                        </nav>
                    </div>
                    
                    <div class="mt-6">
                        <div class="max-w-[1280px] mx-auto px-4 py-6">
                            <header class="flex justify-between items-center pb-3 mb-6">
                                <div>
                                    <h1 class="text-lg font-extrabold leading-6">
                                        {{ activeSection === 'opportunities' ? 'Job Opportunities' :
                                           activeSection === 'applications' ? 'My Applications' :
                                           'Notifications' }}
                                    </h1>
                                    <p class="text-xs text-[#374151] mt-1">
                                        {{ activeSection === 'opportunities' ? 'Browse and apply for available positions' :
                                           activeSection === 'applications' ? 'Track your job applications' :
                                           'Stay updated with your application status' }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <button
                                        v-if="activeSection !== 'notifications'"
                                        type="button"
                                        class="flex items-center gap-1 text-xs text-[#374151] border border-[#d1d5db] rounded px-3 py-[6px] hover:bg-gray-100"
                                    >
                                        <i class="fas fa-filter text-[10px]"></i> Filter
                                    </button>
                                </div>
                            </header>

                            <!-- Job Opportunities Section -->
                            <div v-if="activeSection === 'opportunities'" class="space-y-6">
                                <!-- Loading State -->
                                <div v-if="opportunitiesForm.loading" class="flex justify-center items-center py-8">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                                </div>

                                <!-- Empty State -->
                                <div v-else-if="!opportunitiesForm.opportunities.length" class="text-center py-8">
                                    <div class="text-gray-400">
                                        <i class="fas fa-briefcase text-4xl mb-4"></i>
                                        <h3 class="text-lg font-medium">No Job Opportunities</h3>
                                        <p class="text-sm">Check back later for new opportunities</p>
                                    </div>
                                </div>

                                <!-- Job Opportunities List -->
                                <div v-else v-for="opportunity in opportunitiesForm.opportunities" :key="opportunity.id" class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">{{ opportunity.title }}</h3>
                                            <p class="text-sm text-gray-600">{{ opportunity.company }}</p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <PrimaryButton @click="applyForJob(opportunity.id)" class="text-sm">
                                                Apply Now
                                            </PrimaryButton>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-map-marker-alt mr-2"></i>
                                            {{ opportunity.location }}
                                        </div>
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-briefcase mr-2"></i>
                                            {{ opportunity.type }}
                                        </div>
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-clock mr-2"></i>
                                            {{ opportunity.experience_level }}
                                        </div>
                                        <div class="flex items-center text-gray-600">
                                            <i class="fas fa-dollar-sign mr-2"></i>
                                            {{ opportunity.salary_range }}
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-4">{{ opportunity.description }}</p>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="skill in opportunity.required_skills" :key="skill" class="px-3 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 rounded-full">
                                            {{ skill }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Applications Section -->
                            <div v-if="activeSection === 'applications'" class="space-y-6">
                                <!-- Loading State -->
                                <div v-if="applicationsForm.loading" class="flex justify-center items-center py-8">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                                </div>

                                <!-- Empty State -->
                                <div v-else-if="!applicationsForm.applications.length" class="text-center py-8">
                                    <div class="text-gray-400">
                                        <i class="fas fa-file-alt text-4xl mb-4"></i>
                                        <h3 class="text-lg font-medium">No Applications</h3>
                                        <p class="text-sm">You haven't applied to any jobs yet</p>
                                    </div>
                                </div>

                                <!-- Applications List -->
                                <div v-else v-for="application in activeApplications" :key="application.id" class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">{{ application.job_title }}</h3>
                                            <p class="text-sm text-gray-600">{{ application.company }}</p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span :class="[
                                                'px-3 py-1 text-xs font-medium rounded-full',
                                                application.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                                application.status === 'interview_scheduled' ? 'bg-blue-100 text-blue-800' :
                                                application.status === 'offer_received' ? 'bg-green-100 text-green-800' :
                                                'bg-gray-100 text-gray-800'
                                            ]">
                                                {{ application.status.replace('_', ' ').charAt(0).toUpperCase() + application.status.slice(1).replace('_', ' ') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-600 mb-4">
                                        <div class="flex items-center space-x-4">
                                            <span class="flex items-center">
                                                <i class="far fa-calendar mr-2"></i>
                                                Applied {{ application.applied_date }}
                                            </span>
                                            <span v-if="application.interview_date" class="flex items-center">
                                                <i class="far fa-clock mr-2"></i>
                                                Interview on {{ application.interview_date }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex space-x-3">
                                        <button @click="selectedApplication = application; isViewDetailsModalOpen = true" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Notifications Section -->
                            <div v-if="activeSection === 'notifications'" class="space-y-6">
                                <!-- Loading State -->
                                <div v-if="notificationsForm.loading" class="flex justify-center items-center py-8">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                                </div>

                                <!-- Empty State -->
                                <div v-else-if="!notificationsForm.notifications.length" class="text-center py-8">
                                    <div class="text-gray-400">
                                        <i class="fas fa-bell text-4xl mb-4"></i>
                                        <h3 class="text-lg font-medium">No Notifications</h3>
                                        <p class="text-sm">You're all caught up!</p>
                                    </div>
                                </div>

                                <!-- Notifications List -->
                                <div v-else v-for="notification in notificationsForm.notifications" :key="notification.id" 
                                    class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow-md transition-shadow duration-200"
                                    :class="{ 'bg-gray-50': notification.read }"
                                >
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900">{{ notification.title }}</p>
                                            <p class="text-sm text-gray-600 mt-1">{{ notification.message }}</p>
                                            <p class="text-xs text-gray-500 mt-2">{{ notification.created_at }}</p>
                                        </div>
                                        <button 
                                            v-if="!notification.read"
                                            @click="markNotificationAsRead(notification.id)"
                                            class="ml-4 text-xs text-gray-500 hover:text-gray-700"
                                        >
                                            Mark as read
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Application Details Modal -->
        <Modal :show="isViewDetailsModalOpen" @close="isViewDetailsModalOpen = false">
            <div class="p-6" v-if="selectedApplication">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Application Details</h2>
                <div class="space-y-4">
                    <div>
                        <h3 class="font-medium text-gray-900">{{ selectedApplication.job_title }}</h3>
                        <p class="text-sm text-gray-600">{{ selectedApplication.company }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Status</p>
                            <p class="font-medium text-gray-900">{{ selectedApplication.status.replace('_', ' ').charAt(0).toUpperCase() + selectedApplication.status.slice(1).replace('_', ' ') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Applied Date</p>
                            <p class="font-medium text-gray-900">{{ selectedApplication.applied_date }}</p>
                        </div>
                        <div v-if="selectedApplication.interview_date">
                            <p class="text-gray-500">Interview Date</p>
                            <p class="font-medium text-gray-900">{{ selectedApplication.interview_date }}</p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            @click="isViewDetailsModalOpen = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-500"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </Modal>
    </Graduate>
</template>