<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ToastNotification from '@/Components/Graduate/ToastNotification.vue';
import HeaderSection from '@/Components/Graduate/HeaderSection.vue';
import ProfileCard from '@/Components/Graduate/ProfileCard.vue';
import SkillsSection from '@/Components/Graduate/SkillsSection.vue';
import TabsSection from '@/Components/Graduate/TabsSection.vue';

const props = defineProps({
    graduate: Object,
    experiences: Array,
    education: Array,
    skills: Array,
    projects: Array,
    testimonials: Array,
    resume: Object,
    isCompany: Boolean,
    isHiring: Boolean,
    hiringProcess: Object,
});

// Toast notification state
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('success');

// Helper functions
const copyToClipboard = (text) => {
    if (!text) return;

    navigator.clipboard.writeText(text)
        .then(() => {
            toastMessage.value = 'Copied to clipboard!';
            toastType.value = 'success';
            showToast.value = true;

            // Auto-hide toast after 3 seconds
            setTimeout(() => {
                showToast.value = false;
            }, 3000);
        })
        .catch(() => {
            toastMessage.value = 'Failed to copy';
            toastType.value = 'error';
            showToast.value = true;
        });
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
};


// Computed properties
const fullName = computed(() => {
    if (!props.graduate) return '';
    return `${props.graduate.first_name || ''} ${props.graduate.last_name || ''}`.trim();
});

const aboutMe = computed(() => {
    return props.graduate?.about_me || 'No information provided';
});

const groupedSkillsByType = computed(() => {
    const grouped = {};
    if (!props.skills || !Array.isArray(props.skills)) return grouped;

    props.skills.forEach(skill => {
        if (!skill || !skill.type) return; // <-- use 'type' instead of 'category'

        if (!grouped[skill.type]) {
            grouped[skill.type] = [];
        }
        grouped[skill.type].push(skill);
    });
    return grouped;
});

const mappedProficiency = computed(() => {
    const proficiencyMap = {
        1: 'Beginner',
        2: 'Elementary',
        3: 'Intermediate',
        4: 'Advanced',
        5: 'Expert'
    };

    return (level) => proficiencyMap[level] || 'Not specified';
});

const groupedEducationByLevel = computed(() => {
    const grouped = {};
    if (!props.education || !Array.isArray(props.education)) return grouped;

    props.education.forEach(edu => {
        if (!edu || !edu.level) return;

        if (!grouped[edu.level]) {
            grouped[edu.level] = [];
        }
        grouped[edu.level].push(edu);
    });
    return grouped;
});

const highestEducation = computed(() => {
    if (!props.education || !props.education.length) return null;

    // Priority order of education levels (highest to lowest)
    const levelPriority = {
        'Doctorate': 1,
        'Master\'s': 2,
        'Bachelor\'s': 3,
        'Associate\'s': 4,
        'Certificate': 5,
        'High School': 6,
        'Other': 7
    };

    return props.education.reduce((highest, current) => {
        if (!highest) return current;

        const currentPriority = levelPriority[current.level] || 999;
        const highestPriority = levelPriority[highest.level] || 999;

        return currentPriority < highestPriority ? current : highest;
    }, null);
});

const currentJob = computed(() => {
    if (!props.experiences || !props.experiences.length) return null;

    // Find current job (ongoing = true)
    const current = props.experiences.find(exp => exp.ongoing);

    // If no current job, return the most recent one
    if (!current) {
        return props.experiences.sort((a, b) => {
            const dateA = a.end_date ? new Date(a.end_date) : new Date(0);
            const dateB = b.end_date ? new Date(b.end_date) : new Date(0);
            return dateB - dateA;
        })[0];
    }

    return current;
});

const yearsOfExperience = computed(() => {
    if (!props.experiences || !props.experiences.length) return 0;

    // Calculate total years of experience
    let totalMonths = 0;

    props.experiences.forEach(exp => {
        if (!exp.start_date) return;

        const startDate = new Date(exp.start_date);
        const endDate = exp.ongoing ? new Date() : (exp.end_date ? new Date(exp.end_date) : new Date());

        // Calculate months between dates
        const months = (endDate.getFullYear() - startDate.getFullYear()) * 12 +
            (endDate.getMonth() - startDate.getMonth());

        totalMonths += Math.max(0, months);
    });

    return Math.round(totalMonths / 12 * 10) / 10; // Round to 1 decimal place
});
</script>

<template>
    <AppLayout>

        <Head :title="fullName" />

        <!-- Toast Notification -->
        <ToastNotification :show="showToast" :message="toastMessage" :type="toastType" @close="showToast = false"
            class="z-50" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header Section -->
            <HeaderSection :graduate="graduate" :full-name="fullName" @copy-email="copyToClipboard(graduate.email)"
                @copy-phone="copyToClipboard(graduate.phone)"
                class="mb-8 transition-all duration-300 hover:shadow-md rounded-lg overflow-hidden" />

            <!-- Company Actions (if viewing as company) -->
            <div v-if="isCompany"
                class="mb-8 bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:border-indigo-200 transition-all duration-300">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-2">Company Actions</h2>
                        <p class="text-gray-600">Take action on this graduate's profile</p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-all duration-300 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 flex items-center">
                            <i class="fas fa-user-plus mr-2"></i>
                            Invite to Apply
                        </button>

                        <button
                            class="px-4 py-2 bg-white text-indigo-600 border border-indigo-600 rounded-md hover:bg-indigo-50 transition-all duration-300 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 flex items-center">
                            <i class="fas fa-envelope mr-2"></i>
                            Contact
                        </button>

                        <button
                            class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-all duration-300 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 flex items-center">
                            <i class="fas fa-star mr-2 text-yellow-400"></i>
                            Add to Favorites
                        </button>
                    </div>
                </div>

                <!-- Hiring Process (if in progress) -->
                <div v-if="isHiring && hiringProcess" class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">Hiring Process</h3>

                    <div class="relative">
                        <!-- Progress Bar -->
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-600 transition-all duration-500 ease-in-out"
                                :style="{ width: `${(hiringProcess.current_stage / hiringProcess.total_stages) * 100}%` }">
                            </div>
                        </div>

                        <!-- Stages -->
                        <div class="flex justify-between mt-4">
                            <div v-for="stage in hiringProcess.total_stages" :key="stage"
                                class="flex flex-col items-center transition-all duration-300" :class="{
                                    'text-indigo-600 font-medium': stage <= hiringProcess.current_stage,
                                    'text-gray-400': stage > hiringProcess.current_stage
                                }">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center mb-2 transition-all duration-300 cursor-pointer transform hover:scale-110"
                                    :class="{
                                        'bg-indigo-600 text-white': stage < hiringProcess.current_stage,
                                        'bg-indigo-100 text-indigo-600 ring-2 ring-indigo-600 ring-offset-2': stage === hiringProcess.current_stage,
                                        'bg-gray-200 text-gray-400': stage > hiringProcess.current_stage
                                    }">
                                    <i v-if="stage < hiringProcess.current_stage" class="fas fa-check"></i>
                                    <span v-else>{{ stage }}</span>
                                </div>
                                <span class="text-sm whitespace-nowrap">
                                    {{ hiringProcess.stage_names?.[stage - 1] || `Stage ${stage}` }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column -->
                <div class="lg:col-span-1 space-y-8">
                    <!-- Profile Card -->
                    <ProfileCard :graduate="graduate" :full-name="fullName" :about-me="aboutMe"
                        :current-job="currentJob" :highest-education="highestEducation"
                        :years-of-experience="yearsOfExperience" :format-date="formatDate"
                        class="transition-all duration-300 hover:shadow-lg rounded-lg overflow-hidden" />

                    <!-- Skills Section -->
                    <SkillsSection :skills="skills" :grouped-skills="groupedSkillsByType"
                        :mapped-proficiency="mappedProficiency"
                        class="transition-all duration-300 hover:shadow-md rounded-lg overflow-hidden" />
                </div>

                <!-- Right Column (Tabs) -->
                <div class="lg:col-span-2">
                    <TabsSection :graduate="graduate" :experiences="experiences" :education="education"
                        :projects="projects" :testimonials="testimonials" :resume="resume" :skills="skills"
                        class="transition-all duration-300" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>