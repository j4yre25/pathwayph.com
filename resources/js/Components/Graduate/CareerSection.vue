<script setup>
import { ref } from 'vue';


const props = defineProps({
    experiences: {
        type: Array,
        default: () => []
    },
    education: {
        type: Array,
        default: () => []
    },
    groupedEducation: {
        type: Object,
        default: () => ({})
    },
    highestEducation: {
        type: Object,
        default: null
    }
});

const activeCareerTab = ref('experience');
const showAllEducation = ref(false);
const EDUCATION_LIMIT = 3;

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
};
</script>

<template>
    <div class="bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg">
        <!-- Section header -->
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 p-4">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-briefcase mr-2 text-indigo-500"></i>
                Career
            </h3>
        </div>
        
        <!-- Career content -->
        <div class="transition-all duration-500">
            <!-- Tab Navigation -->
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button 
                        @click="activeCareerTab = 'experience'" 
                        :class="[activeCareerTab === 'experience' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'flex-1 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-300']"
                        role="tab"
                        aria-selected="true"
                        aria-controls="tab-experience"
                        id="tab-button-experience"
                    >
                        <i class="fas fa-briefcase mr-2"></i>
                        Work Experience
                    </button>
                    <button 
                        @click="activeCareerTab = 'education'" 
                        :class="[activeCareerTab === 'education' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300', 'flex-1 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-300']"
                        role="tab"
                        aria-selected="false"
                        aria-controls="tab-education"
                        id="tab-button-education"
                    >
                        <i class="fas fa-graduation-cap mr-2"></i>
                        Education
                    </button>
                </nav>
            </div>
            
            <!-- Work Experience section -->
            <div 
                v-show="activeCareerTab === 'experience'" 
                class="p-6 border-b border-gray-100"
                role="tabpanel"
                aria-labelledby="tab-button-experience"
                id="tab-experience"
            >
                <div v-if="!experiences || experiences.length === 0" class="bg-gray-50 text-gray-500 text-center py-6 rounded-lg">
                    <i class="fas fa-briefcase text-gray-300 text-3xl mb-2"></i>
                    <p>No work experience added yet</p>
                </div>
                
                <!-- Experience timeline -->
                <div v-else class="space-y-6 text-sm">
                    <div class="relative pl-6 space-y-6">
                        <!-- Timeline line -->
                        <div class="absolute top-0 left-2 h-full w-0.5 bg-indigo-100"></div>
                        
                        <div 
                            v-for="experience in sortedExperiences" 
                            :key="experience.id" 
                            class="relative"
                        >
                            <!-- Timeline dot -->
                            <div class="absolute -left-4 top-1 w-3 h-3 rounded-full bg-indigo-500 border-2 border-white shadow-md"></div>
                            
                            <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow-md transition-all duration-300 hover:border-indigo-200">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-semibold text-gray-800">{{ experience.job_title }}</h4>
                                        <p class="text-gray-600 mt-1">{{ experience.company_name }}</p>
                                    </div>
                                    <span v-if="experience.current_job" class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full font-medium">Current</span>
                                </div>
                                
                                <div class="mt-2 text-gray-500 flex items-center">
                                    <i class="far fa-calendar-alt mr-2 text-indigo-400"></i>
                                    <p>{{ formatDate(experience.start_date) }} - {{ experience.current_job ? 'Present' : formatDate(experience.end_date) }}</p>
                                </div>
                                
                                <div v-if="experience.description" class="mt-3 text-gray-600">
                                    <p class="whitespace-pre-line">{{ experience.description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Education section -->
            <div 
                v-show="activeCareerTab === 'education'" 
                class="p-6"
                role="tabpanel"
                aria-labelledby="tab-button-education"
                id="tab-education"
            >
                <div class="flex justify-between items-center mb-4">
                    <button 
                        v-if="Object.keys(groupedEducation).length > 0 && Object.values(groupedEducation).flat().length > EDUCATION_LIMIT"
                        @click="showAllEducation = !showAllEducation"
                        class="text-indigo-500 text-xs hover:text-indigo-700 transition-colors duration-300 flex items-center ml-auto"
                    >
                        <i :class="[showAllEducation ? 'fa-chevron-up' : 'fa-chevron-down', 'fas mr-1']"></i>
                        {{ showAllEducation ? 'Show Less' : 'Show All' }}
                    </button>
                </div>
                
                <div v-if="!education || education.length === 0" class="bg-gray-50 text-gray-500 text-center py-6 rounded-lg">
                    <i class="fas fa-graduation-cap text-gray-300 text-3xl mb-2"></i>
                    <p>No education added yet</p>
                </div>
                
                <!-- Highest Education Highlight -->
                <div v-else-if="highestEducation" class="mb-6 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg p-4 border border-indigo-100 shadow-md">
                    <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                        <i class="fas fa-award mr-2 text-indigo-500"></i>
                        Highest Education
                    </h4>
                    <div class="bg-white rounded-lg border border-indigo-200 p-4 shadow-sm">
                        <div class="flex justify-between items-start">
                            <h5 class="font-semibold text-gray-800">{{ highestEducation.program }}</h5>
                            <span v-if="highestEducation.is_current" class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full font-medium">Current</span>
                        </div>
                        <p class="text-gray-600 mt-1">{{ highestEducation.education }}</p>
                        <div class="mt-2 text-gray-500 flex items-center">
                            <i class="far fa-calendar-alt mr-2 text-indigo-400"></i>
                            <p>{{ formatDate(highestEducation.start_date) }} - {{ highestEducation.is_current ? 'Present' : formatDate(highestEducation.end_date) }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Education timeline by level -->
                <div v-else class="space-y-6 text-sm">
                    <div v-for="(educationList, level) in groupedEducation" :key="level" class="space-y-4">
                        <h4 class="font-medium text-gray-700 border-b border-indigo-100 pb-2 flex items-center">
                            <i class="fas fa-award mr-2 text-indigo-400"></i>
                            {{ level }}
                        </h4>
                        
                        <!-- Timeline for this education level -->
                        <div class="relative pl-6 space-y-4">
                            <!-- Timeline line -->
                            <div class="absolute top-0 left-2 h-full w-0.5 bg-indigo-100"></div>
                            
                            <div 
                                v-for="(edu, index) in showAllEducation ? educationList : educationList.slice(0, EDUCATION_LIMIT)" 
                                :key="edu.id" 
                                class="relative"
                            >
                                <!-- Timeline dot -->
                                <div class="absolute -left-4 top-1 w-3 h-3 rounded-full bg-indigo-500 border-2 border-white shadow-md"></div>
                                
                                <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow-md transition-all duration-300 hover:border-indigo-200">
                                    <div class="flex justify-between items-start">
                                        <h5 class="font-semibold text-gray-800">{{ edu.program }}</h5>
                                        <span v-if="edu.is_current" class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full font-medium">Current</span>
                                    </div>
                                    <p class="text-gray-600 mt-1">{{ edu.education }}</p>
                                    <div class="mt-2 text-gray-500 flex items-center">
                                        <i class="far fa-calendar-alt mr-2 text-indigo-400"></i>
                                        <p>{{ formatDate(edu.start_date) }} - {{ edu.is_current ? 'Present' : formatDate(edu.end_date) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>