<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    projects: {
        type: Array,
        default: () => []
    }
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
};

const formatUrl = (url) => {
    if (!url) return '';
    return url.startsWith('http://') || url.startsWith('https://') ? url : `https://${url}`;
};
</script>

<template>
    <div>
        <div class="flex items-center justify-between bg-indigo-50 p-3 rounded-lg border border-indigo-100 mb-6">
            <h4 class="font-medium text-gray-800 flex items-center">
                <i class="fas fa-project-diagram text-indigo-600 mr-2"></i>
                Projects
            </h4>
            <span class="text-xs text-indigo-600 bg-white px-2 py-1 rounded-md shadow-sm border border-indigo-100">
                {{ projects ? projects.length : 0 }} {{ projects && projects.length === 1 ? 'project' : 'projects' }}
            </span>
        </div>
        
        <div v-if="projects && projects.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div 
                v-for="project in projects" 
                :key="project.id"
                class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-all duration-300 hover:border-indigo-200 overflow-hidden flex flex-col"
            >
                <!-- Project Image (if available) -->
                <div v-if="project.graduate_project_file" class="h-48 overflow-hidden bg-gray-100 border-b border-gray-200">
                    <img 
                        :src="`/storage/${project.graduate_project_file}`" 
                        :alt="project.graduate_projects_title" 
                        class="w-full h-full object-cover object-center transition-transform duration-500 transform hover:scale-105"
                    >
                </div>
                <div v-else class="h-32 bg-gradient-to-r from-indigo-100 to-purple-100 flex items-center justify-center border-b border-gray-200">
                    <i class="fas fa-project-diagram text-indigo-300 text-4xl"></i>
                </div>
                
                <!-- Project Content -->
                <div class="p-4 flex-grow">
                    <h5 class="font-semibold text-gray-800 mb-1">{{ project.graduate_projects_title }}</h5>
                    <p v-if="project.graduate_projects_role" class="text-gray-600 text-sm mb-2">{{ project.graduate_projects_role }}</p>
                    
                    <div class="flex items-center text-xs text-gray-500 mb-3">
                        <i class="far fa-calendar-alt mr-1 text-indigo-400"></i>
                        <span>
                            {{ formatDate(project.graduate_projects_start_date) }} - 
                            {{ project.graduate_projects_end_date === null ? 'Present' : formatDate(project.graduate_projects_end_date) }}
                        </span>
                    </div>
                    
                    <p v-if="project.graduate_projects_description" class="text-gray-600 text-sm mb-3 line-clamp-3 hover:line-clamp-none transition-all duration-300">
                        {{ project.graduate_projects_description }}
                    </p>
                    
                    <div v-if="project.graduate_projects_url" class="mb-3">
                        <a :href="formatUrl(project.graduate_projects_url)" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-sm hover:underline flex items-center">
                            <i class="fas fa-external-link-alt mr-1"></i>
                            View Project
                        </a>
                    </div>
                    
                    <div v-if="project.graduate_projects_key_accomplishments" class="mt-3">
                        <h6 class="text-xs font-semibold text-gray-700 mb-2 uppercase">Key Accomplishments</h6>
                        <p class="text-gray-600 text-sm whitespace-pre-line">{{ project.graduate_projects_key_accomplishments }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="text-center py-12 bg-gray-50 rounded-lg border border-gray-200">
            <i class="fas fa-project-diagram text-gray-300 text-5xl mb-4"></i>
            <h3 class="text-gray-700 font-medium mb-2">No Projects Added</h3>
            <p class="text-gray-500 text-sm">Projects will appear here once added to the profile.</p>
        </div>
    </div>
</template>