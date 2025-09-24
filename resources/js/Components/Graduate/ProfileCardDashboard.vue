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

console.log(props.graduate)
</script>

<template>

    <div
        class="bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg border border-indigo-50">
        <!-- Profile Image and Name -->
        <div class="relative">
            <div class="h-32 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
            <div class="absolute bottom-0 left-0 w-full transform translate-y-1/2 flex flex-col items-center">
                <div class="h-24 w-24 rounded-full border-4 border-white shadow-md overflow-hidden bg-white">
                    <img :src="graduate.graduate_picture
                        ? `/storage/${graduate.graduate_picture}`
                        : (graduate.user?.profile_picture
                            ? `/storage/${graduate.user.profile_picture}`
                            : 'path/to/default/image.jpg')" :alt="graduate.first_name + ' ' + graduate.last_name"
                        class="h-full w-full object-cover">
                </div>
                <!-- Full Name -->
                <h2 class="mt-4 text-lg font-bold text-gray-900 text-center">
                    {{ graduate.first_name }} {{ graduate.last_name }}
                </h2>
                <!-- Profession -->
                <p class="text-sm text-gray-500 mt-1 text-center" v-if="graduate.profession">
                    <i class="fas fa-briefcase mr-1"></i>
                    {{ graduate.profession }}
                </p>
            </div>

        </div>

        <!-- Profile Content -->
        <div class="pt-16">
            <!-- About Me Section -->


            <!-- Contact Information -->
            <div class="border-b border-gray-100 mt-6">
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

          
        </div>
    </div>
</template>