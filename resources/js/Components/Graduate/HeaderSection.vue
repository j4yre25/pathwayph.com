<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    graduate: {
        type: Object,
        required: true
    },
    fullName: {
        type: String,
        required: true
    }
});

const formatSocialMediaUrl = (url) => {
    if (!url) return '';
    return url.startsWith('http://') || url.startsWith('https://') ? url : `https://${url}`;
};
</script>

<template>
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-6 rounded-t-lg shadow-md">
        <div class="container mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <h1 class="text-2xl md:text-3xl font-bold">{{ fullName }}</h1>
                    <p v-if="graduate.current_job" class="text-indigo-100 mt-1">
                        {{ graduate.current_job }}
                    </p>
                </div>
                
                <!-- Social Media Links -->
                <div class="flex space-x-3">
                    <a 
                        v-if="graduate.linkedin_url" 
                        :href="formatSocialMediaUrl(graduate.linkedin_url)" 
                        target="_blank"
                        class="bg-white bg-opacity-20 hover:bg-opacity-30 p-2 rounded-full transition-all duration-300 transform hover:scale-110 hover:shadow-lg"
                        title="LinkedIn Profile"
                    >
                        <i class="fab fa-linkedin-in text-white"></i>
                    </a>
                    <a 
                        v-if="graduate.github_url" 
                        :href="formatSocialMediaUrl(graduate.github_url)" 
                        target="_blank"
                        class="bg-white bg-opacity-20 hover:bg-opacity-30 p-2 rounded-full transition-all duration-300 transform hover:scale-110 hover:shadow-lg"
                        title="GitHub Profile"
                    >
                        <i class="fab fa-github text-white"></i>
                    </a>
                    <a 
                        v-if="graduate.twitter_url" 
                        :href="formatSocialMediaUrl(graduate.twitter_url)" 
                        target="_blank"
                        class="bg-white bg-opacity-20 hover:bg-opacity-30 p-2 rounded-full transition-all duration-300 transform hover:scale-110 hover:shadow-lg"
                        title="Twitter Profile"
                    >
                        <i class="fab fa-twitter text-white"></i>
                    </a>
                    <a 
                        v-if="graduate.facebook_url" 
                        :href="formatSocialMediaUrl(graduate.facebook_url)" 
                        target="_blank"
                        class="bg-white bg-opacity-20 hover:bg-opacity-30 p-2 rounded-full transition-all duration-300 transform hover:scale-110 hover:shadow-lg"
                        title="Facebook Profile"
                    >
                        <i class="fab fa-facebook-f text-white"></i>
                    </a>
                    <a 
                        v-if="graduate.instagram_url" 
                        :href="formatSocialMediaUrl(graduate.instagram_url)" 
                        target="_blank"
                        class="bg-white bg-opacity-20 hover:bg-opacity-30 p-2 rounded-full transition-all duration-300 transform hover:scale-110 hover:shadow-lg"
                        title="Instagram Profile"
                    >
                        <i class="fab fa-instagram text-white"></i>
                    </a>
                    <a 
                        v-if="graduate.personal_website" 
                        :href="formatSocialMediaUrl(graduate.personal_website)" 
                        target="_blank"
                        class="bg-white bg-opacity-20 hover:bg-opacity-30 p-2 rounded-full transition-all duration-300 transform hover:scale-110 hover:shadow-lg"
                        title="Personal Website"
                    >
                        <i class="fas fa-globe text-white"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>