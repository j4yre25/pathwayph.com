<script setup>
import { ref } from 'vue';

const props = defineProps({
    graduate: {
        type: Object,
        required: true
    },
    aboutMe: {
        type: String,
        default: ''
    },
    currentJob: {
        type: Object,
        default: null
    },
    highestEducation: {
        type: Object,
        default: null
    },
    employmentPreferences: {
        type: Object,
        default: null
    },
    careerGoals: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['copy-to-clipboard']);


const copyToClipboard = (text) => {
    emit('copy-to-clipboard', text);
};

// Collapsible sections
const showContactInfo = ref(true);
const showEmploymentPreferences = ref(true);
const showCareerGoals = ref(true);
const showCompanyInfo = ref(true);

function formatUrl(url) {
    if (!url) return '';
    if (/^https?:\/\//i.test(url)) {
        return url;
    }
    return 'https://' + url;
}

</script>

<template>
    <div
        class="bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg border border-indigo-50">
        <!-- Profile Image and Name -->
        <div class="relative">
            <div class="h-32 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
            <div class="absolute bottom-0 left-0 w-full transform translate-y-1/2 flex justify-center">
                <div class="h-24 w-24 rounded-full border-4 border-white shadow-md overflow-hidden bg-white">
                    <img :src="graduate.graduate_picture
                        ? `/storage/${graduate.graduate_picture}`
                        : (graduate.user?.profile_picture
                            ? `/storage/${graduate.user.profile_picture}`
                            : 'path/to/default/image.jpg')" :alt="graduate.first_name + ' ' + graduate.last_name"
                        class="h-full w-full object-cover">
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="pt-16">
            <!-- About Me Section -->
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-semibold mb-3 text-gray-700 flex items-center">
                    <i class="fas fa-user mr-2 text-blue-500"></i>
                    About Me
                </h3>
                <div class="text-sm">
                    <p v-if="aboutMe"
                        class="text-gray-600 whitespace-pre-line line-clamp-4 hover:line-clamp-none transition-all duration-300">
                        {{ aboutMe }}
                    </p>
                    <p v-else class="text-gray-500 italic">No information provided</p>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="border-b border-gray-100">
                <div @click="showContactInfo = !showContactInfo"
                    class="p-4 cursor-pointer hover:bg-gray-50 transition-colors duration-200">
                    <h3 class="font-semibold text-gray-700 flex items-center justify-between">
                        <span class="flex items-center">
                            <i class="fas fa-address-card mr-2 text-blue-500"></i>
                            Contact Information
                        </span>
                        <i :class="[showContactInfo ? 'fa-chevron-up' : 'fa-chevron-down', 'fas text-gray-400']"
                            class="transition-transform duration-300"></i>
                    </h3>
                </div>
                <div v-show="showContactInfo" class="p-4 pt-0 space-y-3 text-sm">
                    <div class="flex items-center group">
                        <i
                            class="fas fa-phone-alt text-gray-400 w-6 group-hover:text-blue-500 transition-colors duration-300"></i>
                        <span class="text-gray-700">{{ graduate.contact_number || 'No phone number' }}</span>
                        <button v-if="graduate.contact_number"
                            class="ml-2 text-gray-400 hover:text-blue-600 transition-colors duration-300 opacity-0 group-hover:opacity-100"
                            @click="copyToClipboard(graduate.contact_number)" title="Copy phone to clipboard">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <div class="flex items-center group">
                        <i
                            class="fas fa-envelope text-gray-400 w-6 group-hover:text-blue-500 transition-colors duration-300"></i>
                        <span class="text-gray-700">{{ graduate.user?.email || 'Email not available' }}</span>
                        <button v-if="graduate.user?.email"
                            class="ml-2 text-gray-400 hover:text-blue-600 transition-colors duration-300 opacity-0 group-hover:opacity-100"
                            @click="copyToClipboard(graduate.user?.email)" title="Copy email to clipboard">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <div class="flex items-center group">
                        <i
                            class="fas fa-globe text-gray-400 w-6 group-hover:text-blue-500 transition-colors duration-300"></i>
                        <a v-if="graduate.personal_website" :href="formatUrl(graduate.personal_website)" target="_blank"
                            class="text-blue-600 hover:text-blue-800 hover:underline transition-colors duration-300">
                            {{ graduate.personal_website }}
                        </a>
                        <span v-else class="text-gray-500 italic">No website</span>
                    </div>
                </div>
            </div>

            <!-- Current status & Preferences -->
            <div v-if="employmentPreferences" class="border-b border-gray-100">
                <div @click="showEmploymentPreferences = !showEmploymentPreferences"
                    class="p-4 cursor-pointer hover:bg-gray-50 transition-colors duration-200">
                    <h3 class="font-semibold text-gray-700 flex items-center justify-between">
                        <span class="flex items-center">
                            <i class="fas fa-briefcase mr-2 text-blue-500"></i>
                            Employment Preferences
                        </span>
                        <i :class="[showEmploymentPreferences ? 'fa-chevron-up' : 'fa-chevron-down', 'fas text-gray-400']"
                            class="transition-transform duration-300"></i>
                    </h3>
                </div>
                <div v-show="showEmploymentPreferences" class="p-4 pt-0 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-gray-500 text-xs uppercase font-medium mb-1">Preferred Job Type</p>
                        <p class="text-gray-700 font-medium">
                            {{ employmentPreferences.job_type || 'Not specified' }}
                        </p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-gray-500 text-xs uppercase font-medium mb-1">Preferred Salary</p>
                        <p class="text-gray-700 font-medium">
                            ₱{{ employmentPreferences.employment_min_salary ?
                                `${employmentPreferences.employment_min_salary} -
                            ₱${employmentPreferences.employment_max_salary || ''}` : 'Not specified' }} {{
                                employmentPreferences.salary_type ? (employmentPreferences.salary_type === 'monthly' ? '/Month' : '/ Hour') : '' }}
                        </p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-gray-500 text-xs uppercase font-medium mb-1">Preferred Location</p>
                        <p class="text-gray-700 font-medium">
                            {{ employmentPreferences.location || 'Not specified' }}
                        </p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-gray-500 text-xs uppercase font-medium mb-1">Preferred Work Environment</p>
                        <p class="text-gray-700 font-medium">
                            {{ employmentPreferences.work_environment || 'Not specified' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Career Goals section -->
            <div v-if="careerGoals" class="border-b border-gray-100">
                <div @click="showCareerGoals = !showCareerGoals"
                    class="p-4 cursor-pointer hover:bg-gray-50 transition-colors duration-200">
                    <h3 class="font-semibold text-gray-700 flex items-center justify-between">
                        <span class="flex items-center">
                            <i class="fas fa-bullseye mr-2 text-blue-500"></i>
                            Career Goals
                        </span>
                        <i :class="[showCareerGoals ? 'fa-chevron-up' : 'fa-chevron-down', 'fas text-gray-400']"
                            class="transition-transform duration-300"></i>
                    </h3>
                </div>
                <div v-show="showCareerGoals" class="p-4 pt-0 space-y-3 text-sm">
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-gray-500 text-xs uppercase font-medium mb-1">Short-term Goals</p>
                        <p class="text-gray-700 font-medium whitespace-pre-line">
                            {{ careerGoals.short_term_goals || 'Not specified' }}
                        </p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-gray-500 text-xs uppercase font-medium mb-1">Long-term Goals</p>
                        <p class="text-gray-700 font-medium whitespace-pre-line">
                            {{ careerGoals.long_term_goals || 'Not specified' }}
                        </p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-gray-500 text-xs uppercase font-medium mb-1">Industries of Interest</p>
                        <p class="text-gray-700 font-medium">
                            <span v-if="careerGoals.industries_of_interest">
                                {{ careerGoals.industries_of_interest.includes(',') ?
                                    careerGoals.industries_of_interest.split(',').join(', ') :
                                careerGoals.industries_of_interest }}
                            </span>
                            <span v-else>Not specified</span>
                        </p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-gray-500 text-xs uppercase font-medium mb-1">Career Path</p>
                        <p class="text-gray-700 font-medium whitespace-pre-line">
                            {{ careerGoals.career_path || 'Not specified' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Company-only sections -->
            <div
                v-if="$page && $page.props && $page.props.auth && $page.props.auth.user && $page.props.auth.user.role === 'company'">
                <div @click="showCompanyInfo = !showCompanyInfo"
                    class="p-4 cursor-pointer hover:bg-gray-50 transition-colors duration-200">
                    <h3 class="font-semibold text-gray-700 flex items-center justify-between">
                        <span class="flex items-center">
                            <i class="fas fa-building mr-2 text-blue-500"></i>
                            Company Information
                        </span>
                        <i :class="[showCompanyInfo ? 'fa-chevron-up' : 'fa-chevron-down', 'fas text-gray-400']"
                            class="transition-transform duration-300"></i>
                    </h3>
                </div>
                <div v-show="showCompanyInfo" class="space-y-4 p-4 pt-0">
                    <!-- Source - Only visible to company users -->
                    <div class="text-sm">
                        <h4 class="font-semibold mb-2 text-gray-700 flex items-center">
                            <i class="fas fa-database mr-2 text-blue-500"></i>
                            Source
                        </h4>
                        <p class="text-gray-600">
                            {{ graduate.source || 'No source information available' }}
                        </p>
                        <p class="text-gray-500 text-xs" v-if="graduate.updated_at">
                            Last edited: {{ new Date(graduate.updated_at).toLocaleDateString() }}
                        </p>
                    </div>

                    <!-- Notes - Only visible to company users -->
                    <div class="text-sm">
                        <h4 class="font-semibold mb-2 text-gray-700 flex items-center">
                            <i class="fas fa-sticky-note mr-2 text-blue-500"></i>
                            Notes
                        </h4>
                        <p class="text-gray-600" v-if="graduate.notes">
                            {{ graduate.notes }}
                        </p>
                        <p class="text-gray-600 italic" v-else>
                            No notes available for this graduate.
                        </p>
                        <button
                            class="mt-2 text-blue-500 hover:text-blue-700 text-xs flex items-center transition-colors duration-300">
                            <i class="fas fa-plus-circle mr-1"></i>
                            Add note
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>