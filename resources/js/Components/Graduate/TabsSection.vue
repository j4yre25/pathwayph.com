<script setup>
import { ref, computed } from 'vue';












const props = defineProps({
    projects: {
        type: Array,
        default: () => []
    },
    testimonials: {
        type: Array,
        default: () => []
    },
    resume: {
        type: Object,
        default: null
    },
    graduate: {
        type: Object,
        required: true
    },
    experiences: {
        type: Array,
        default: () => []
    },
    education: {
        type: Array,
        default: () => []
    },
    skills: {
        type: Array,
        default: () => []
    }























































































});

// Compute grouped skills by category
const groupedSkillsByCategory = computed(() => {
    const grouped = {};
    if (!props.skills || !Array.isArray(props.skills)) return grouped;
    
    props.skills.forEach(skill => {
        if (!skill || !skill.category) return;
        
        if (!grouped[skill.category]) {
            grouped[skill.category] = [];
        }
        grouped[skill.category].push(skill);





    });
    return grouped;






















});

const showAllSkills = ref(false);
const SKILLS_LIMIT = 5;














const activeTab = ref('skills');



const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });





};

const formatUrl = (url) => {
    if (!url) return '';
    return url.startsWith('http://') || url.startsWith('https://') ? url : `https://${url}`;
};



// Computed properties for Career section
const groupedEducationByLevel = computed(() => {
    const grouped = {};
    props.education.forEach(edu => {
        if (!grouped[edu.level]) {
            grouped[edu.level] = [];
        }
        grouped[edu.level].push(edu);
    });
    return grouped;
});

const showAllEducation = ref(false);
const maxEducationToShow = 3;






























































const visibleEducation = computed(() => {
    if (showAllEducation.value || !props.education || props.education.length <= maxEducationToShow) {
        return props.education;
    }
    return props.education.slice(0, maxEducationToShow);




});
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 w-full">
        <!-- Tabs Navigation - Modern Minimalist Style -->
        <div class="border-b border-gray-200 bg-white overflow-x-auto sticky top-0 z-10">
            <nav class="flex" aria-label="Tabs">
                <button
                    @click="activeTab = 'career'"
                    :class="[
                        activeTab === 'career'
                            ? 'border-blue-600 text-blue-700 border-b-2 font-medium'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        'px-4 py-3 text-sm flex items-center transition-all duration-200 focus:outline-none'
                    ]"
                    role="tab"
                    aria-selected="false"
                    aria-controls="tab-career"
                    id="tab-button-career"
                >
                    <i class="fas fa-briefcase mr-2 text-gray-500"></i>
                    Career
                    <span v-if="experiences.length + education.length > 0" class="ml-2 bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">{{ experiences.length + education.length }}</span>
                </button>
                <button
                    @click="activeTab = 'resume'"
                    :class="[
                        activeTab === 'resume'
                            ? 'border-blue-600 text-blue-700 border-b-2 font-medium'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        'px-4 py-3 text-sm flex items-center transition-all duration-200 focus:outline-none'
                    ]"
                    aria-current="page"
                    role="tab"
                    aria-selected="true"
                    aria-controls="tab-resume"
                    id="tab-button-resume"
                >
                    <i class="far fa-file-alt mr-2 text-gray-500"></i>
                    Resume
                </button>
                <button
                    @click="activeTab = 'activities'"
                    :class="[
                        activeTab === 'activities'
                            ? 'border-blue-600 text-blue-700 border-b-2 font-medium'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        'px-4 py-3 text-sm flex items-center transition-all duration-200 focus:outline-none'
                    ]"
                    role="tab"
                    aria-selected="false"
                    aria-controls="tab-activities"
                    id="tab-button-activities"
                >
                    <i class="fas fa-chart-line mr-2 text-gray-500"></i>
                    Activities
                    <span v-if="projects && projects.length > 0" class="ml-2 bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">{{ projects ? projects.length : 0 }}</span>
                </button>
                <button
                    @click="activeTab = 'documents'"
                    :class="[
                        activeTab === 'documents'
                            ? 'border-blue-600 text-blue-700 border-b-2 font-medium'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        'px-4 py-3 text-sm flex items-center transition-all duration-200 focus:outline-none'
                    ]"
                    role="tab"
                    aria-selected="false"
                    aria-controls="tab-documents"
                    id="tab-button-documents"
                >
                    <i class="fas fa-folder mr-2 text-gray-500"></i>
                    Documents
                    <span class="ml-2 bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">0</span>
                </button>
                <button
                    @click="activeTab = 'testimonials'"
                    :class="[
                        activeTab === 'testimonials'
                            ? 'border-blue-600 text-blue-700 border-b-2 font-medium'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        'px-4 py-3 text-sm flex items-center transition-all duration-200 focus:outline-none'
                    ]"
                    role="tab"
                    aria-selected="false"
                    aria-controls="tab-testimonials"
                    id="tab-button-testimonials"
                >
                    <i class="fas fa-quote-right mr-2 text-gray-500"></i>
                    Testimonials
                    <span v-if="testimonials && testimonials.length > 0" class="ml-2 bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">
                        {{ testimonials.length }}
                    </span>
                </button>
                <button
                    @click="activeTab = 'feedback'"
                    :class="[
                        activeTab === 'feedback'
                            ? 'border-blue-600 text-blue-700 border-b-2 font-medium'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        'px-4 py-3 text-sm flex items-center transition-all duration-200 focus:outline-none'
                    ]"
                    role="tab"
                    aria-selected="false"
                    aria-controls="tab-feedback"
                    id="tab-button-feedback"
                >
                    <i class="fas fa-comment-alt mr-2 text-gray-500"></i>
                    Feedback
                </button>
                <button
                    @click="activeTab = 'skills'"
                    :class="[
                        activeTab === 'skills'
                            ? 'border-blue-600 text-blue-700 border-b-2 font-medium'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        'px-4 py-3 text-sm flex items-center transition-all duration-200 focus:outline-none'
                    ]"
                    role="tab"
                    aria-selected="false"
                    aria-controls="tab-skills"
                    id="tab-button-skills"
                >
                    <i class="fas fa-code mr-2 text-gray-500"></i>
                    Skills
                    <span v-if="skills && skills.length > 0" class="ml-2 bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">{{ skills.length }}</span>
                </button>
                <button
                    @click="activeTab = 'details'"
                    :class="[
                        activeTab === 'details'
                            ? 'border-blue-600 text-blue-700 border-b-2 font-medium'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        'px-4 py-3 text-sm flex items-center transition-all duration-200 focus:outline-none'
                    ]"
                    role="tab"
                    aria-selected="false"
                    aria-controls="tab-details"
                    id="tab-button-details"
                >
                    <i class="fas fa-user mr-2 text-gray-500"></i>
                    Details
                </button>
                <button
                    @click="activeTab = 'assessment'"
                    :class="[
                        activeTab === 'assessment'
                            ? 'border-blue-600 text-blue-700 border-b-2 font-medium'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                        'px-4 py-3 text-sm flex items-center transition-all duration-200 focus:outline-none'
                    ]"
                    role="tab"
                    aria-selected="false"
                    aria-controls="tab-assessment"
                    id="tab-button-assessment"
                >
                    <i class="fas fa-clipboard-check mr-2 text-gray-500"></i>
                    Assessment
                </button>
            </nav>
        </div>
        
        <!-- Tab Content -->
        <div class="p-5">
            <!-- Resume Tab -->
            <div v-if="activeTab === 'resume'" class="transition-opacity duration-300">
                <div v-if="resume" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-800 flex items-center">
                            <i class="fas fa-file-alt text-gray-500 mr-2"></i>
                            Resume
                        </h3>
                        <div class="text-sm text-gray-500">
                            Last updated: {{ formatDate(resume.updated_at) }}
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap gap-4">
                        <a 
                            :href="resume.file_url" 
                            download 
                            class="flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                        >
                            <i class="fas fa-download mr-2"></i>
                            Download Resume
                        </a>
                        <a 
                            :href="resume.file_url" 
                            target="_blank" 
                            class="flex items-center px-4 py-2 bg-white text-blue-600 border border-blue-600 rounded hover:bg-blue-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                        >
                            <i class="fas fa-external-link-alt mr-2"></i>
                            Open Resume
                        </a>
                    </div>
                    
                    <!-- PDF Viewer -->
                    <div v-if="resume.file_url && resume.file_url.endsWith('.pdf')" class="mt-4 border border-gray-200 rounded overflow-hidden">
                        <iframe 
                            :src="resume.file_url" 
                            class="w-full h-[600px]" 
                            frameborder="0"
                        ></iframe>
                    </div>
                </div>
                
                <div v-else class="text-center py-8">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-file-alt text-5xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-800 mb-2">No Resume Uploaded</h3>
                    <p class="text-gray-500">The graduate has not uploaded a resume yet.</p>
                </div>
            </div>

            <!-- Career Tab -->
            <div v-else-if="activeTab === 'career'" class="transition-opacity duration-300">
                <!-- Work Experience Section -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-briefcase text-gray-500 mr-2"></i>
                        Work Experience
                    </h3>
                    
                    <div v-if="sortedExperiences.length > 0" class="space-y-6">
                        <!-- Timeline Layout -->
                        <div class="relative border-l-2 border-gray-200 pl-6 ml-3">
                            <div 
                                v-for="(experience, index) in sortedExperiences" 
                                :key="index"
                                class="mb-8 relative transform transition-all duration-200 hover:translate-x-1"
                            >
                                <!-- Timeline Dot -->
                                <div class="absolute -left-[31px] mt-1.5 w-5 h-5 rounded-full border-4 border-white bg-blue-500 shadow-sm"></div>
                                
                                <!-- Experience Card -->
                                <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow transition-all duration-200">
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-2">
                                        <h4 class="text-base font-medium text-gray-900 mb-1 sm:mb-0">
                                            {{ experience.title || experience.position || 'Position' }}
                                            <span v-if="experience.ongoing" class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                                Current
                                            </span>
                                        </h4>
                                        <div class="text-sm text-gray-500">
                                            {{ formatDate(experience.start_date) }} - {{ experience.ongoing ? 'Present' : formatDate(experience.end_date) }}
                                        </div>
                                    </div>
                                    
                                    <div class="text-sm font-medium text-blue-600 mb-2">
                                        {{ experience.company || experience.company_name || 'Company' }}
                                    </div>
                                    
                                    <div class="text-sm text-gray-600 mb-3">
                                        {{ experience.location || 'Location' }}
                                    </div>
                                    
                                    <div class="text-sm text-gray-700 mb-3 whitespace-pre-line">
                                        {{ experience.description || 'No description provided' }}
                                    </div>
                                    
                                    <div v-if="experience.skills && experience.skills.length > 0" class="flex flex-wrap gap-2 mt-2">
                                        <span 
                                            v-for="(skill, skillIndex) in experience.skills" 
                                            :key="skillIndex"
                                            class="px-2 py-1 text-xs font-medium rounded-md bg-gray-100 text-gray-700 transition-all duration-200 hover:bg-gray-200"
                                        >
                                            {{ skill }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="text-center py-6 bg-gray-50 rounded-lg border border-gray-200 transition-all duration-200">
                        <div class="text-gray-400 mb-2">
                            <i class="fas fa-briefcase text-4xl"></i>
                        </div>
                        <h4 class="text-base font-medium text-gray-800 mb-1">No Work Experience</h4>
                        <p class="text-sm text-gray-500">The graduate has not added any work experience yet.</p>
                    </div>
                </div>
                
                <!-- Education Section -->
                <div>
                    <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-graduation-cap text-gray-500 mr-2"></i>
                        Education
                    </h3>
                    
                    <div v-if="education && education.length > maxEducationToShow">
                        <button 
                            @click="showAllEducation = !showAllEducation"
                            class="text-blue-600 text-sm hover:text-blue-800 transition-all duration-200 flex items-center focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 rounded-md px-2 py-1"
                        >
                            <i :class="[showAllEducation ? 'fa-chevron-up' : 'fa-chevron-down', 'fas mr-1']"></i>
                            {{ showAllEducation ? 'Show Less' : 'Show All' }}
                        </button>
                    </div>
                    
                    <!-- Highest Education Highlight -->
                    <div v-if="highestEducation" class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200 shadow-sm transition-all duration-200">
                        <h4 class="font-medium text-gray-800 mb-3 flex items-center">
                            <i class="fas fa-award mr-2 text-gray-500"></i>
                            Highest Education
                        </h4>
                        <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm transition-all duration-200">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-2">
                                <h5 class="text-base font-medium text-gray-900 mb-1 sm:mb-0">
                                    {{ highestEducation.degree || 'Degree' }}
                                    <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                        {{ highestEducation.level || 'Level' }}
                                    </span>
                                </h5>
                                <div class="text-sm text-gray-500">
                                    {{ formatDate(highestEducation.start_date) }} - {{ highestEducation.is_current ? 'Present' : formatDate(highestEducation.end_date) }}
                                </div>
                            </div>
                            
                            <div class="text-sm font-medium text-blue-600 mb-2">
                                {{ highestEducation.institution || 'Institution' }}
                            </div>
                            
                            <div class="text-sm text-gray-600 mb-3">
                                {{ highestEducation.location || 'Location' }}
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="sortedEducation.length > 0" class="space-y-6">
                        <!-- Timeline Layout -->
                        <div class="relative border-l-2 border-gray-200 pl-6 ml-3">
                            <div 
                                v-for="(edu, index) in showAllEducation ? sortedEducation : sortedEducation.slice(0, maxEducationToShow)" 
                                :key="index"
                                class="mb-8 relative transform transition-all duration-200 hover:translate-x-1"
                            >
                                <!-- Timeline Dot -->
                                <div class="absolute -left-[31px] mt-1.5 w-5 h-5 rounded-full border-4 border-white bg-blue-500 shadow-sm"></div>
                                
                                <!-- Education Card -->
                                <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow transition-all duration-200">
                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-2">
                                        <h4 class="text-base font-medium text-gray-900 mb-1 sm:mb-0">
                                            {{ edu.degree || edu.graduate_education_program || 'Degree' }}
                                            <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                                {{ edu.level || edu.graduate_education_field_of_study || 'Level' }}
                                            </span>
                                        </h4>
                                        <div class="text-sm text-gray-500">
                                            {{ formatDate(edu.start_date || edu.graduate_education_start_date) }} - {{ edu.is_current ? 'Present' : formatDate(edu.end_date || edu.graduate_education_end_date) }}
                                        </div>
                                    </div>
                                    
                                    <div class="text-sm font-medium text-blue-600 mb-2">
                                        {{ edu.institution || edu.graduate_education_institution_id || 'Institution' }}
                                    </div>
                                    
                                    <div class="text-sm text-gray-600 mb-3">
                                        {{ edu.location || 'Location' }}
                                    </div>
                                    
                                    <div class="text-sm text-gray-700 mb-3 whitespace-pre-line">
                                        {{ edu.description || 'No description provided' }}
                                    </div>
                                    
                                    <div v-if="edu.achievements && edu.achievements.length > 0" class="mt-3">
                                        <h5 class="text-sm font-medium text-gray-800 mb-2">Achievements</h5>
                                        <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                                            <li v-for="(achievement, achievementIndex) in edu.achievements" :key="achievementIndex">
                                                {{ achievement }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="text-center py-6 bg-gray-50 rounded-lg border border-gray-200 transition-all duration-300 hover:border-indigo-100 hover:bg-gray-100">
                        <div class="text-gray-400 mb-2">
                            <i class="fas fa-graduation-cap text-4xl"></i>
                        </div>
                        <h4 class="text-base font-medium text-gray-800 mb-1">No Education</h4>
                        <p class="text-sm text-gray-500">The graduate has not added any education yet.</p>
                    </div>
                </div>
            </div>
            
            <!-- Activities Tab -->
            <div v-else-if="activeTab === 'activities'" class="transition-opacity duration-300">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-800 flex items-center">
                        <i class="fas fa-project-diagram text-indigo-500 mr-2"></i>
                        Projects
                        <span v-if="projects && projects.length > 0" class="ml-2 px-2 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700">
                            {{ projects.length }}
                        </span>
                    </h3>
                </div>
                
                <div v-if="projects && projects.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div 
                        v-for="(project, index) in projects" 
                        :key="index"
                        class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-300 transform hover:-translate-y-1"
                    >
                        <div v-if="project.image_url" class="h-40 overflow-hidden">
                            <img :src="project.image_url" :alt="project.title" class="w-full h-full object-cover">
                        </div>
                        <div class="p-4">
                            <h4 class="text-base font-medium text-gray-900 mb-1">{{ project.title || 'Project Title' }}</h4>
                            
                            <div v-if="project.role" class="text-sm font-medium text-indigo-600 mb-2">
                                {{ project.role }}
                            </div>
                            
                            <div class="text-sm text-gray-500 mb-3">
                                {{ formatDate(project.start_date) }} - {{ project.ongoing ? 'Present' : formatDate(project.end_date) }}
                            </div>
                            
                            <div class="text-sm text-gray-700 mb-3 line-clamp-3">
                                {{ project.description || 'No description provided' }}
                            </div>
                            
                            <div v-if="project.url" class="mb-3">
                                <a 
                                    :href="formatUrl(project.url)" 
                                    target="_blank" 
                                    class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 rounded-md px-2 py-1 inline-block"
                                >
                                    <i class="fas fa-external-link-alt mr-1"></i>
                                    View Project
                                </a>
                            </div>
                            
                            <div v-if="project.accomplishments && project.accomplishments.length > 0" class="mt-3">
                                <h5 class="text-sm font-medium text-gray-800 mb-2">Key Accomplishments</h5>
                                <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                                    <li v-for="(accomplishment, accomplishmentIndex) in project.accomplishments" :key="accomplishmentIndex">
                                        {{ accomplishment }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-else class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200 transition-all duration-300 hover:border-indigo-100 hover:bg-gray-100">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-project-diagram text-5xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-800 mb-2">No Projects</h3>
                    <p class="text-gray-500">The graduate has not added any projects yet.</p>
































                </div>

            </div>
















            
            <!-- Documents Tab -->
            <div v-else-if="activeTab === 'documents'" class="transition-opacity duration-300">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-800 flex items-center">
                        <i class="fas fa-file text-indigo-500 mr-2"></i>
                        Documents
                    </h3>
                </div>
                
                <div v-if="resume" class="bg-white rounded-lg border border-gray-200 p-4 mb-4 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-300">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 mr-4">
                            <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                                <i class="fas fa-file-pdf text-indigo-600"></i>
                            </div>
                        </div>
                        <div class="flex-grow">
                            <h4 class="text-base font-medium text-gray-900">Resume</h4>
                            <div class="text-sm text-gray-500">
                                Last updated: {{ formatDate(resume.updated_at) }}
                            </div>
                        </div>
                        <div class="flex-shrink-0 flex space-x-2">
                            <a 
                                v-if="resume.file_url"
                                :href="resume.file_url" 
                                download 
                                class="p-2 text-indigo-600 hover:text-indigo-800 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 rounded-full"
                                title="Download"
                            >
                                <i class="fas fa-download"></i>
                            </a>
                            <a 
                                v-if="resume.file_url"
                                :href="resume.file_url" 
                                target="_blank" 
                                class="p-2 text-indigo-600 hover:text-indigo-800 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 rounded-full"
                                title="View"
                            >
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div v-if="!resume" class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200 transition-all duration-300 hover:border-indigo-100 hover:bg-gray-100">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-file text-5xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-800 mb-2">No Documents</h3>
                    <p class="text-gray-500">The graduate has not uploaded any documents yet.</p>
                </div>
            </div>
            
            <!-- Skills Section (Added) -->
            <div v-else-if="activeTab === 'skills'" class="mb-8 mt-6">
                <div class="bg-white rounded-lg shadow-sm p-6 transition-all duration-200 border border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-800 flex items-center">
                                <i class="fas fa-code text-gray-500 mr-2"></i>
                                Skills
                            </h3>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span v-if="skills && skills.length > 0" class="text-xs text-gray-600 bg-gray-100 px-3 py-1 rounded-full">
                                {{ skills.length }} {{ skills.length === 1 ? 'skill' : 'skills' }}
                            </span>
                            <button 
                                v-if="Object.keys(groupedSkillsByCategory).length > 0 && Object.values(groupedSkillsByCategory).flat().length > SKILLS_LIMIT"
                                @click="showAllSkills = !showAllSkills"
                                class="text-blue-500 text-xs hover:text-blue-700 bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded-full transition-colors duration-200 flex items-center"
                            >
                                <i :class="[showAllSkills ? 'fa-chevron-up' : 'fa-chevron-down', 'fas mr-1']"></i>
                                {{ showAllSkills ? 'Show Less' : 'Show All' }}
                            </button>
                        </div>
                    </div>
                    
                    <div class="transition-all duration-500 ease-in-out">
                        <div v-if="!skills || skills.length === 0" class="text-gray-500 text-center py-8 text-sm bg-gray-50 rounded-lg border border-gray-200">
                            <i class="fas fa-code text-gray-300 text-3xl mb-2"></i>
                            <p>No skills added yet</p>
                            <p class="text-xs mt-1">Skills will appear here once added to the profile</p>
                        </div>
                        
                        <!-- Skills grid with 2 columns -->
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <template v-for="(categorySkills, category) in groupedSkillsByCategory" :key="category">
                                <!-- Category heading -->
                                <div class="md:col-span-2 mt-2 mb-4 first:mt-0">
                                    <h4 class="font-medium text-gray-700 border-b border-gray-200 pb-2 flex items-center">
                                        <i class="fas fa-layer-group mr-2 text-gray-500"></i>
                                        {{ category }}
                                        <span class="ml-2 text-xs text-gray-600 bg-gray-100 px-2 py-0.5 rounded-full">
                                            {{ categorySkills.length }}
                                        </span>
                                    </h4>
                                </div>
                                
                                <!-- Skills in this category -->
                                <div 
                                    v-for="skill in showAllSkills ? categorySkills : categorySkills.slice(0, SKILLS_LIMIT)" 
                                    :key="skill.id"
                                    class="space-y-3 group transition-all duration-200 hover:bg-gray-50 p-4 rounded-lg border border-gray-200 hover:shadow relative"
                                >
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-800 font-medium flex items-center">
                                            <i :class="[`fas fa-${skill.icon || 'code'} mr-2 text-blue-600`]"></i>
                                            {{ skill.name }}
                                        </span>
                                        <span class="text-blue-700 font-medium bg-gray-100 group-hover:bg-white px-3 py-1 rounded-full text-xs transition-colors duration-200">
                                            {{ Math.round(skill.percentage) }}%
                                        </span>
                                    </div>
                                    <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                                        <div 
                                            class="h-full rounded-full transition-all duration-500" 
                                            :class="{
                                                'bg-blue-500': skill.percentage >= 80,
                                                'bg-blue-400': skill.percentage >= 40 && skill.percentage < 80,
                                                'bg-blue-300': skill.percentage < 40
                                            }"
                                            :style="{ width: `${skill.percentage}%` }"
                                        ></div>
                                    </div>
                                    <!-- Tooltip that appears on hover -->
                                    <div v-if="skill.years_experience" 
                                        class="hidden group-hover:block text-xs text-gray-700 mt-1 bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-sm absolute -top-10 left-1/2 transform -translate-x-1/2 whitespace-nowrap z-10 before:content-[''] before:absolute before:top-full before:left-1/2 before:-translate-x-1/2 before:border-8 before:border-transparent before:border-t-white">
                                        <i class="far fa-clock mr-1 text-gray-500"></i>
                                        {{ skill.years_experience }} {{ skill.years_experience === 1 ? 'year' : 'years' }} of experience
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Personal Details Section -->
            <div v-else-if="activeTab === 'details'" class="mb-8">
                <div class="bg-white rounded-lg shadow-sm p-6 transition-all duration-200 border border-gray-200">
                    <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user text-gray-500 mr-2"></i>
                        Personal Details
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-300">
                            <h4 class="text-base font-medium text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-id-card text-gray-500 mr-2"></i>
                                Basic Information
                            </h4>
                            
                            <div class="space-y-3">
                                <div class="flex">
                                    <div class="w-1/3 text-sm font-medium text-gray-500">Full Name</div>
                                    <div class="w-2/3 text-sm text-gray-800">
                                        {{ graduate.first_name }} {{ graduate.middle_name ? graduate.middle_name + ' ' : '' }}{{ graduate.last_name }}
                                    </div>
                                </div>
                                
                                <div class="flex">
                                    <div class="w-1/3 text-sm font-medium text-gray-500">Date of Birth</div>
                                    <div class="w-2/3 text-sm text-gray-800">
                                        {{ graduate.dob ? formatDate(graduate.dob) : 'Not provided' }}
                                    </div>
                                </div>
                                
                                <div class="flex">
                                    <div class="w-1/3 text-sm font-medium text-gray-500">Gender</div>
                                    <div class="w-2/3 text-sm text-gray-800">
                                        {{ graduate.gender || 'Not provided' }}
                                    </div>
                                </div>
                                
                                <div class="flex">
                                    <div class="w-1/3 text-sm font-medium text-gray-500">Nationality</div>
                                    <div class="w-2/3 text-sm text-gray-800">
                                        {{ graduate.nationality || 'Not provided' }}
                                    </div>
                                </div>
                                
                                <div class="flex">
                                    <div class="w-1/3 text-sm font-medium text-gray-500">Ethnicity</div>
                                    <div class="w-2/3 text-sm text-gray-800">
                                        {{ graduate.graduate_ethnicity || 'Not provided' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-300">
                            <h4 class="text-base font-medium text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-map-marker-alt text-gray-500 mr-2"></i>
                                Location
                            </h4>
                            
                            <div class="space-y-3">
                                <div class="flex">
                                    <div class="w-1/3 text-sm font-medium text-gray-500">Address</div>
                                    <div class="w-2/3 text-sm text-gray-800">
                                        {{ graduate.graduate_address || 'Not provided' }}
                                    </div>
                                </div>
                                
                                <div class="flex">
                                    <div class="w-1/3 text-sm font-medium text-gray-500">City</div>
                                    <div class="w-2/3 text-sm text-gray-800">
                                        {{ graduate.city || 'Not provided' }}
                                    </div>
                                </div>
                                
                                <div class="flex">
                                    <div class="w-1/3 text-sm font-medium text-gray-500">State/Province</div>
                                    <div class="w-2/3 text-sm text-gray-800">
                                        {{ graduate.state || 'Not provided' }}
                                    </div>
                                </div>
                                
                                <div class="flex">
                                    <div class="w-1/3 text-sm font-medium text-gray-500">Country</div>
                                    <div class="w-2/3 text-sm text-gray-800">
                                        {{ graduate.country || 'Not provided' }}
                                    </div>
                                </div>
                                
                                <div class="flex">
                                    <div class="w-1/3 text-sm font-medium text-gray-500">Postal Code</div>
                                    <div class="w-2/3 text-sm text-gray-800">
                                        {{ graduate.postal_code || 'Not provided' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-300">
                            <h4 class="text-base font-medium text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-envelope text-gray-500 mr-2"></i>
                                Contact Information
                            </h4>
                            
                            <div class="space-y-3">
                                <div class="flex">
                                    <div class="w-1/3 text-sm font-medium text-gray-500">Email</div>
                                    <div class="w-2/3 text-sm text-gray-800">
                                        {{ graduate.email || 'Not provided' }}
                                    </div>
                                </div>
                                
                                <div class="flex">
                                    <div class="w-1/3 text-sm font-medium text-gray-500">Phone</div>
                                    <div class="w-2/3 text-sm text-gray-800">
                                        {{ graduate.contact_number || graduate.phone || 'Not provided' }}
                                    </div>
                                </div>
                                
                                <div class="flex">
                                    <div class="w-1/3 text-sm font-medium text-gray-500">Website</div>
                                    <div class="w-2/3 text-sm text-gray-800">
                                        <a 
                                            v-if="graduate.personal_website" 
                                            :href="formatUrl(graduate.personal_website)" 
                                            target="_blank" 
                                            class="text-indigo-600 hover:text-indigo-800 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 rounded-md px-2 py-1 inline-block"
                                        >
                                            {{ graduate.personal_website }}
                                        </a>
                                        <span v-else>Not provided</span>
                                    </div>
                                </div>
                                
                                <div class="flex">
                                    <div class="w-1/3 text-sm font-medium text-gray-500">LinkedIn</div>
                                    <div class="w-2/3 text-sm text-gray-800">
                                        <a 
                                            v-if="graduate.linkedin_url" 
                                            :href="formatUrl(graduate.linkedin_url)" 
                                            target="_blank" 
                                            class="text-indigo-600 hover:text-indigo-800 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 rounded-md px-2 py-1 inline-block"
                                        >
                                            <i class="fab fa-linkedin mr-1"></i> LinkedIn Profile
                                        </a>
                                        <span v-else>Not provided</span>
                                    </div>
                                </div>
                                
                                <div class="flex">
                                    <div class="w-1/3 text-sm font-medium text-gray-500">GitHub</div>
                                    <div class="w-2/3 text-sm text-gray-800">
                                        <a 
                                            v-if="graduate.github_url" 
                                            :href="formatUrl(graduate.github_url)" 
                                            target="_blank" 
                                            class="text-indigo-600 hover:text-indigo-800 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 rounded-md px-2 py-1 inline-block"
                                        >
                                            <i class="fab fa-github mr-1"></i> GitHub Profile
                                        </a>
                                        <span v-else>Not provided</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-300">
                            <h4 class="text-base font-medium text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-graduation-cap text-gray-500 mr-2"></i>
                                Program Information
                            </h4>
                            
                            <div class="space-y-3">
                                <div class="flex">
                                    <div class="w-1/3 text-sm font-medium text-gray-500">Institution</div>
                                    <div class="w-2/3 text-sm text-gray-800">
                                        {{ graduate.institution_name || 'Not provided' }}
                                    </div>
                                </div>
                                
                                <div class="flex">
                                    <div class="w-1/3 text-sm font-medium text-gray-500">Program</div>
                                    <div class="w-2/3 text-sm text-gray-800">
                                        {{ graduate.program_name || 'Not provided' }}
                                    </div>
                                </div>
                                
                                <div class="flex">
                                    <div class="w-1/3 text-sm font-medium text-gray-500">Year Graduated</div>
                                    <div class="w-2/3 text-sm text-gray-800">
                                        {{ graduate.year_graduated || 'Not provided' }}
                                    </div>
                                </div>
                                
                                <div class="flex">
                                    <div class="w-1/3 text-sm font-medium text-gray-500">Employment Status</div>
                                    <div class="w-2/3 text-sm text-gray-800">
                                        {{ graduate.employment_status || 'Not provided' }}
                                    </div>
                                </div>
                                
                                <div class="flex">
                                    <div class="w-1/3 text-sm font-medium text-gray-500">Current Job</div>
                                    <div class="w-2/3 text-sm text-gray-800">
                                        {{ graduate.current_job_title || 'Not provided' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Feedback Tab -->
            <div v-else-if="activeTab === 'feedback'" class="transition-opacity duration-300">
                <div class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200 transition-all duration-300 hover:border-indigo-100 hover:bg-gray-100">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-comment-alt text-5xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-800 mb-2">No Feedback</h3>
                    <p class="text-gray-500 mb-4">There is no feedback available for this graduate.</p>
                    
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-all duration-300 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50">
                        <i class="fas fa-plus mr-2"></i>
                        Add Feedback
                    </button>


                </div>

            </div>
            
            <!-- Testimonials Tab -->
            <div v-else-if="activeTab === 'testimonials'" class="transition-opacity duration-300">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-800 flex items-center">
                        <i class="fas fa-quote-left text-indigo-500 mr-2"></i>
                        Testimonials
                        <span v-if="testimonials && testimonials.length > 0" class="ml-1 px-1.5 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700">
                            {{ testimonials.length }}
                        </span>
                    </h3>






















































                </div>
                
                <div v-if="testimonials && testimonials.length > 0" class="space-y-6">
                    <div 
                        v-for="(testimonial, index) in testimonials" 
                        :key="index"
                        class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-300 transform hover:-translate-y-1"
                    >
                        <div class="flex items-start mb-4">
                            <div class="flex-shrink-0 mr-4">
                                <div v-if="testimonial.author_avatar" class="w-12 h-12 rounded-full overflow-hidden">
                                    <img :src="testimonial.author_avatar" :alt="testimonial.author_name" class="w-full h-full object-cover">
                                </div>
                                <div v-else class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center">
                                    <i class="fas fa-user text-indigo-600"></i>
                                </div>
                            </div>
                            <div class="flex-grow">
                                <h4 class="text-base font-medium text-gray-900">{{ testimonial.author_name || 'Anonymous' }}</h4>
                                <div class="text-sm text-gray-600">{{ testimonial.author_title || 'No title' }}</div>
                                <div class="flex items-center mt-1">
                                    <div class="flex">
                                        <template v-for="i in 5" :key="i">
                                            <i 
                                                :class="i <= (testimonial.rating || 0) ? 'fas fa-star text-yellow-400' : 'far fa-star text-gray-300'"
                                                class="text-sm mr-0.5"
                                            ></i>
                                        </template>
                                    </div>
                                    <span class="text-xs text-gray-500 ml-2">{{ testimonial.rating || 0 }}/5</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 text-sm text-gray-500">
                                {{ formatDate(testimonial.date) }}
                            </div>
                        </div>
                        
                        <div class="text-sm text-gray-700 mb-2 whitespace-pre-line">
                            <i class="fas fa-quote-left text-indigo-300 mr-1 opacity-50"></i>
                            {{ testimonial.content || 'No content provided' }}
                            <i class="fas fa-quote-right text-indigo-300 ml-1 opacity-50"></i>
                        </div>
                    </div>
                </div>
                
                <div v-else class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200 transition-all duration-300 hover:border-indigo-100 hover:bg-gray-100">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-quote-left text-5xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-800 mb-2">No Testimonials</h3>
                    <p class="text-gray-500 mb-4">There are no testimonials available for this graduate.</p>
                    
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-all duration-300 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50">
                        <i class="fas fa-plus mr-2"></i>
                        Request Testimonial
                    </button>
                </div>



            </div>
            
            <!-- Assessment Tab -->
            <div v-else-if="activeTab === 'assessment'" class="transition-opacity duration-300">
                <div class="text-center py-8 bg-gray-50 rounded-lg border border-gray-200 transition-all duration-300 hover:border-indigo-100 hover:bg-gray-100">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-clipboard-check text-5xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-800 mb-2">No Assessment</h3>
                    <p class="text-gray-500 mb-4">There is no assessment available for this graduate.</p>
                    
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-all duration-300 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50">
                        <i class="fas fa-plus mr-2"></i>
                        Request Assessment
                    </button>








                </div>
































            </div>

        </div>

    </div>
</template>
