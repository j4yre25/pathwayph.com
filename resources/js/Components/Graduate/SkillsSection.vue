<script setup>
import { ref } from 'vue';

const props = defineProps({
    skills: {
        type: Array,
        default: () => []
    },
    groupedSkills: {
        type: Object,
        default: () => ({})
    }
});

const showAllSkills = ref(false);
const SKILLS_LIMIT = 5;
</script>

<template>
    <div class="bg-white rounded-lg shadow-md p-6 transition-all duration-300 border border-indigo-50 hover:shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-code text-indigo-500 mr-2"></i>
                    Skills
                </h3>
            </div>
            <div class="flex items-center space-x-2">
                <span v-if="skills && skills.length > 0" class="text-xs text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-100 shadow-sm">
                    {{ skills.length }} {{ skills.length === 1 ? 'skill' : 'skills' }}
                </span>
                <button 
                    v-if="Object.keys(groupedSkills).length > 0 && Object.values(groupedSkills).flat().length > SKILLS_LIMIT"
                    @click="showAllSkills = !showAllSkills"
                    class="text-indigo-500 text-xs hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-full transition-colors duration-200 border border-indigo-100 shadow-sm flex items-center"
                >
                    <i :class="[showAllSkills ? 'fa-chevron-up' : 'fa-chevron-down', 'fas mr-1']"></i>
                    {{ showAllSkills ? 'Show Less' : 'Show All' }}
                </button>
            </div>
        </div>
        
        <div class="transition-all duration-500 ease-in-out">
            <div v-if="!skills || skills.length === 0" class="text-gray-500 text-center py-8 text-sm bg-gray-50 rounded-lg border border-gray-100">
                <i class="fas fa-code text-gray-300 text-3xl mb-2"></i>
                <p>No skills added yet</p>
                <p class="text-xs mt-1">Skills will appear here once added to the profile</p>
            </div>
            
            <!-- Skills grid with 2 columns -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <template v-for="(categorySkills, category) in groupedSkills" :key="category">
                    <!-- Category heading -->
                    <div class="md:col-span-2 mt-2 mb-4 first:mt-0">
                        <h4 class="font-medium text-gray-700 border-b border-indigo-100 pb-2 flex items-center">
                            <i class="fas fa-layer-group mr-2 text-indigo-500"></i>
                            {{ category }}
                            <span class="ml-2 text-xs text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-100">
                                {{ categorySkills.length }}
                            </span>
                        </h4>
                    </div>
                    
                    <!-- Skills in this category -->
                    <div 
                        v-for="skill in showAllSkills ? categorySkills : categorySkills.slice(0, SKILLS_LIMIT)" 
                        :key="skill.id"
                        class="space-y-3 group transition-all duration-300 hover:bg-indigo-50 p-4 rounded-lg border border-gray-100 hover:border-indigo-200 hover:shadow-md relative transform hover:-translate-y-1"
                    >
                        <div class="flex justify-between items-center">
                            <span class="text-gray-800 font-medium flex items-center">
                                <i :class="[`fas fa-${skill.icon || 'code'} mr-2 text-indigo-600`]"></i>
                                {{ skill.name }}
                            </span>
                            <span class="text-indigo-700 font-semibold bg-indigo-50 group-hover:bg-white px-3 py-1 rounded-full text-xs transition-colors duration-300 border border-indigo-100 shadow-sm">
                                {{ Math.round(skill.percentage) }}%
                            </span>
                        </div>
                        <div class="h-3 bg-gray-100 rounded-full overflow-hidden shadow-inner">
                            <div 
                                class="h-full rounded-full transition-all duration-500 transform group-hover:scale-x-105 relative overflow-hidden" 
                                :class="{
                                    'bg-gradient-to-r from-indigo-500 to-purple-500': skill.percentage >= 80,
                                    'bg-gradient-to-r from-blue-500 to-indigo-500': skill.percentage >= 40 && skill.percentage < 80,
                                    'bg-gradient-to-r from-indigo-400 to-blue-400': skill.percentage < 40
                                }"
                                :style="{ width: `${skill.percentage}%` }"
                            >
                                <div class="absolute inset-0 opacity-20 bg-stripes"></div>
                            </div>
                        </div>
                        <!-- Tooltip that appears on hover -->
                        <div v-if="skill.years_experience" 
                            class="hidden group-hover:block text-xs text-gray-700 mt-1 bg-white px-3 py-1.5 rounded-lg border border-indigo-100 shadow-md absolute -top-10 left-1/2 transform -translate-x-1/2 whitespace-nowrap z-10 before:content-[''] before:absolute before:top-full before:left-1/2 before:-translate-x-1/2 before:border-8 before:border-transparent before:border-t-white">
                            <i class="far fa-clock mr-1 text-indigo-500"></i>
                            {{ skill.years_experience }} {{ skill.years_experience === 1 ? 'year' : 'years' }} of experience
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<style scoped>
.bg-stripes {
    background-image: linear-gradient(45deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent);
    background-size: 1rem 1rem;
}
</style>