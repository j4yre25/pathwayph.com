<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    resume: {
        type: Object,
        default: null
    }
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between bg-indigo-50 p-3 rounded-lg border border-indigo-100">
            <h4 class="font-medium text-gray-800 flex items-center">
                <i class="far fa-file-alt text-indigo-600 mr-2 text-lg"></i>
                Resume
            </h4>
            <span class="text-xs text-indigo-600 bg-white px-2 py-1 rounded-md shadow-sm border border-indigo-100">Last updated: {{ formatDate(new Date()) }}</span>
        </div>
        
        <div v-if="resume && resume.file_name">
            <div class="flex items-center justify-between border border-indigo-200 rounded-lg p-4 mb-4 hover:border-indigo-300 hover:bg-indigo-50 transition-all duration-300 shadow-sm">
                <div class="flex items-center">
                    <div class="bg-indigo-100 p-3 rounded-lg mr-4 shadow-sm border border-indigo-200">
                        <i class="fas fa-file-pdf text-indigo-600 text-xl"></i>
                    </div>
                    <div>
                        <a :href="resume.file_url" target="_blank" class="text-indigo-600 hover:underline font-medium">
                            {{ resume.file_name }}
                        </a>
                        <p class="text-xs text-gray-500 mt-1">PDF Document</p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a :href="resume.file_url" download class="text-indigo-600 hover:text-indigo-800 p-2 rounded-full hover:bg-indigo-100 transition-all duration-300 tooltip transform hover:scale-105 border border-transparent hover:border-indigo-200" title="Download">
                        <i class="fas fa-download"></i>
                    </a>
                    <a :href="resume.file_url" target="_blank" class="text-indigo-600 hover:text-indigo-800 p-2 rounded-full hover:bg-indigo-100 transition-all duration-300 tooltip transform hover:scale-105 border border-transparent hover:border-indigo-200" title="Open in new tab">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
            </div>
            
            <!-- PDF Viewer -->
            <div class="border border-indigo-200 rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-all duration-300">
                <div class="bg-indigo-50 p-4 border-b border-indigo-200 flex justify-between items-center">
                    <h3 class="font-medium text-gray-700 flex items-center">
                        <i class="fas fa-eye text-indigo-600 mr-2"></i>
                        Resume Preview
                    </h3>
                    <a :href="resume.file_url" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-sm hover:underline transition-all duration-300 bg-white px-3 py-1.5 rounded-md shadow-sm border border-indigo-100 flex items-center">
                        Open in new tab <i class="fas fa-external-link-alt ml-1"></i>
                    </a>
                </div>
                <iframe 
                    :src="resume.file_url" 
                    class="w-full h-[600px]" 
                    frameborder="0"
                ></iframe>
            </div>
        </div>
        <div v-else class="text-center py-12 bg-gray-50 rounded-lg border border-gray-200">
            <i class="far fa-file-alt text-gray-300 text-5xl mb-4"></i>
            <h3 class="text-gray-700 font-medium mb-2">No Resume Uploaded</h3>
            <p class="text-gray-500 text-sm">The graduate has not uploaded a resume yet.</p>
        </div>
    </div>
</template>