<script setup>
import Graduate from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { ref, onMounted, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { useJobs } from '@/Composables/useJobs';
import { useNotifications } from '@/Composables/useNotifications';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import '@fortawesome/fontawesome-free/css/all.css';

// Props and Page Data
const { props } = usePage();

// State Management
const activeSection = ref(localStorage.getItem('activeSection') || 'applications');
const setActiveSection = (section) => {
    activeSection.value = section;
    localStorage.setItem('activeSection', section);
};

const selectedApplication = ref(null);
const isViewDetailsModalOpen = ref(false);
function openApplicationDetails(application) {
    selectedApplication.value = application;
    isViewDetailsModalOpen.value = true;
}

// useJobs composable
const {
    opportunities,
    applications,
    hasApplied,
    applyForJob,
    archiveJobOpportunity,
    activeApplications,
} = useJobs(props.jobOpportunities, props.jobApplications);

// useNotifications composable
const { notifications, markNotificationAsRead } = useNotifications(props.notifications);

// Loading states (you may want to add loading flags inside composables for better UX)
const opportunitiesLoading = ref(false);
const applicationsLoading = ref(false);
const notificationsLoading = ref(false);

// Initialize data when component mounts (you can replace router.reload with proper API calls if available)
onMounted(() => {
    router.reload();
});

const goToNotification = (notification) => {
    markNotificationAsRead(notification.id);
    router.visit(route('notifications.show', notification.id));
};
</script>

<template>
    <Graduate>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8" aria-label="Tabs">

                            <button @click="setActiveSection('applications')" :class="[
                                activeSection === 'applications'
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                            ]">
                                Applications
                            </button>
                            <!-- <button @click="setActiveSection('notifications')" :class="[
                                activeSection === 'notifications'
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm relative'
                            ]">
                                Notifications
                                <span v-if="notifications.length"
                                    class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                    {{ notifications.length }}
                                </span>
                            </button> -->
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
                                        {{ activeSection === 'opportunities' ? 'Browse and apply for availabl positions'
                                            :
                                            activeSection === 'applications' ? 'Track your job applications' :
                                                'Stay updated with your application status' }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <button v-if="activeSection !== 'notifications'" type="button"
                                        class="flex items-center gap-1 text-xs text-[#374151] border border-[#d1d5db] rounded px-3 py-[6px] hover:bg-gray-100">
                                        <i class="fas fa-filter text-[10px]"></i> Filter
                                    </button>
                                </div>
                            </header>

                            <!-- Job Opportunities Section -->
                            <div v-if="activeSection === 'opportunities'" class="space-y-6">
                                <!-- Loading State -->
                                <div v-if="opportunitiesLoading" class="flex justify-center items-center py-8">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                                </div>

                                <!-- Empty State -->
                                <div v-else-if="!opportunities.length" class="text-center py-8">
                                    <div class="text-gray-400">
                                        <i class="fas fa-briefcase text-4xl mb-4"></i>
                                        <h3 class="text-lg font-medium">No Job Opportunities</h3>
                                        <p class="text-sm">Check back later for new opportunities</p>
                                    </div>
                                </div>

                                <!-- Job Opportunities List -->
                                <div v-else v-for="opportunity in opportunities" :key="opportunity.id"
                                    class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">{{ opportunity.title }}</h3>
                                            <p class="text-sm text-gray-600">{{ opportunity.company }}</p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <PrimaryButton :disabled="hasApplied(opportunity.id)"
                                                @click="applyForJob(opportunity.id)" class="text-sm">
                                                {{ hasApplied(opportunity.id) ? 'Already Applied' : 'Apply Now' }}
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
                                            {{ opportunity.salary }}
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-4">{{ opportunity.description }}</p>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="skills in opportunity.required_skills" :key="skills"
                                            class="px-3 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 rounded-full">
                                            {{ skills }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Applications Section -->
                            <div v-if="activeSection === 'applications'" class="space-y-6">
                                <!-- Loading State -->
                                <div v-if="applicationsLoading" class="flex justify-center items-center py-8">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                                </div>

                                <!-- Empty State -->
                                <div v-else-if="!applications.length" class="text-center py-8">
                                    <div class="text-gray-400">
                                        <i class="fas fa-file-alt text-4xl mb-4"></i>
                                        <h3 class="text-lg font-medium">No Applications</h3>
                                        <p class="text-sm">You haven't applied to any jobs yet</p>
                                    </div>
                                </div>

                                <!-- Applications List -->
                                <div v-else v-for="application in activeApplications" :key="application.id"
                                    class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-200">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">{{ application.job_title }}
                                            </h3>
                                            <p class="text-sm text-gray-600">{{ application.company }}</p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span
                                                :class="[
                                                    'px-3 py-1 text-xs font-medium rounded-full',
                                                    application.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                                        application.status === 'accepted' ? 'bg-green-100 text-green-800' :
                                                            application.status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800']">
                                                {{ application.status }}
                                            </span>
                                            <PrimaryButton @click="openApplicationDetails(application)" class="text-xs">
                                                View Details
                                            </PrimaryButton>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">{{ application.applied_on }}</p>
                                    <p class="text-sm text-gray-600">{{ application.notes }}</p>
                                </div>
                            </div>

                            <!-- Notifications Section -->
                            <!-- <div v-if="activeSection === 'notifications'" class="space-y-4 max-h-[60vh] overflow-auto">
                                <div v-if="notificationsLoading" class="flex justify-center items-center py-8">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                                </div>
                                <div v-else-if="!notifications.length" class="text-center text-gray-400 py-8">
                                    <i class="fas fa-bell-slash text-4xl mb-4"></i>
                                    <p>No new job notifications</p>
                                </div>
                               <div v-else>
                                    <div v-for="notification in notifications" :key="notification.id"
                                        :class="[
                                            'border rounded p-3 hover:bg-yellow-100 cursor-pointer',
                                            notification.read
                                            ? 'border-gray-200 bg-gray-50 text-gray-400'
                                            : 'border-yellow-300 bg-yellow-50 text-gray-700'
                                        ]"
                                        @click="goToNotification(notification)">
                                        <p class="text-sm text-gray-700"> -->
                                            <!-- Application Status Updated Notification -->
                                            <!-- <span v-if="notification.type.includes('ApplicationStatusUpdated')">
                                                Your application for
                                                <b>{{ notification.data.job_title }}</b>
                                                has been updated to:
                                                <b>{{ notification.data.status }}</b>.
                                            </span> -->
                                            <!-- Interview Scheduled Notification -->
                                            <!-- <span v-else-if="notification.type.includes('InterviewScheduledNotification')">
                                                Interview scheduled
                                                <span v-if="notification.data.job_title">
                                                    for <b>{{ notification.data.job_title }}</b>
                                                </span>
                                                <span v-if="notification.data.company">
                                                    at <b>{{ notification.data.company }}</b>
                                                </span>
                                                on <b>{{ new Date(notification.data.scheduled_at).toLocaleString('en-US', {
                                                    year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
                                                }) }}</b>
                                                <span v-if="notification.data.location"> ({{ notification.data.location }})</span>.
                                            </span> -->
                                            <!-- Fallback for other notifications -->
                                            <!-- <span v-else>
                                                <span class="font-bold">{{ notification.data?.title }}</span>
                                                <span v-if="notification.data?.company"> at {{ notification.data?.company }}</span>
                                            </span>
                                        </p>
                                        <p class="text-xs text-gray-500">{{ notification.created_at }}</p>
                                        <PrimaryButton v-if="notification.data?.job_id"
                                            @click="router.visit(`/jobs/${notification.data?.job_id}`)"
                                            class="mt-2 text-xs">View Job</PrimaryButton>
                                        <PrimaryButton v-else-if="notification.data?.action_url"
                                            @click="router.visit(notification.data?.action_url)" class="mt-2 text-xs">
                                            View Application</PrimaryButton>
                                    </div>
                                </div>
                            </div> -->
                        </div>
                    </div>

                    <Modal v-model:show="isViewDetailsModalOpen" maxWidth="4xl">
                        <template #title>
                            Application Details
                        </template>
                        <template #content>
                            <div v-if="selectedApplication">
                                <p><strong>Job Title:</strong> {{ selectedApplication.job_title }}</p>
                                <p><strong>Company:</strong> {{ selectedApplication.company }}</p>
                                <p><strong>Status:</strong> {{ selectedApplication.status }}</p>
                                <p><strong>Applied On:</strong> {{ selectedApplication.applied_on }}</p>
                                <p><strong>Notes:</strong> {{ selectedApplication.notes }}</p>
                                <!-- Add more details as needed -->
                            </div>
                        </template>
                        <template #footer>
                            <PrimaryButton @click="isViewDetailsModalOpen = false">Close</PrimaryButton>
                        </template>
                    </Modal>
                </div>
            </div>
        </div>
    </Graduate>
</template>