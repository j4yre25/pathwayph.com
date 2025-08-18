<script setup>
import { ref, computed, watch, defineAsyncComponent } from 'vue';

// Import the section components - using defineAsyncComponent for lazy loading
const EducationSection = defineAsyncComponent(() => import('./Education.vue'));
const ExperienceSection = defineAsyncComponent(() => import('./Experience.vue'));
const CertificationSection = defineAsyncComponent(() => import('./Certification.vue'));
const AchievementSection = defineAsyncComponent(() => import('./Achievement.vue'));
const SkillSection = defineAsyncComponent(() => import('./Skill.vue'));
const ProjectSection = defineAsyncComponent(() => import('./Project.vue'));
import SkillsChart from './SkillsChart.vue';

// Props from parent (Profile.vue)
const props = defineProps({
  activeSection: { type: String, default: 'professional-background' },
  activeSubsection: { type: String, default: null },
  // Education props
  educationEntries: Array,
  archivedEducationEntries: Array,
  institutions: Array,
  graduate: Object,
  // Skills props
  skillEntries: Array,
  archivedSkillEntries: Array,
  // Experience props
  experienceEntries: Array,
  archivedExperienceEntries: Array,
  // Projects props
  projectsEntries: Array,
  archivedProjectsEntries: Array,
  // Certifications props
  certificationsEntries: Array,
  archivedCertificationsEntries: Array,
  // Achievements props
  achievementEntries: Array,
  archivedAchievementEntries: Array,
});

// Emit for closing/resetting modals if needed
const emit = defineEmits(['close-all-modals', 'reset-all-states', 'refresh-education', 'refresh-skills']);

// Watch for changes in activeSubsection from parent
const activeSubsection = ref(props.activeSubsection);
watch(() => props.activeSubsection, (newValue) => {
  activeSubsection.value = newValue;
});

// State for collapsible sections
const expandedSections = ref({
  education: true,
  experience: true,
  skills: true,
  projects: true,
  certifications: true,
  achievements: true
});

// Toggle section expansion
const toggleSection = (section) => {
  expandedSections.value[section] = !expandedSections.value[section];
};

// Check if sections have data
const hasEducationData = computed(() => props.educationEntries && props.educationEntries.length > 0);
const hasExperienceData = computed(() => props.experienceEntries && props.experienceEntries.length > 0);
const hasSkillsData = computed(() => props.skillEntries && props.skillEntries.length > 0);
const hasProjectsData = computed(() => props.projectsEntries && props.projectsEntries.length > 0);
const hasCertificationsData = computed(() => props.certificationsEntries && props.certificationsEntries.length > 0);
const hasAchievementsData = computed(() => props.achievementEntries && props.achievementEntries.length > 0);

// Show skills chart modal
const isSkillsChartModalOpen = ref(false);
</script>

<template>
  <div v-if="activeSection === 'professional-background'" class="space-y-8">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-2xl font-bold text-blue-800">Professional Background</h2>
        <p class="text-blue-600 mt-1">Manage your education, work experience, skills, and more</p>
      </div>
    </div>
    
    <!-- In-page Jump Navigation -->
    <div class="bg-white p-4 rounded-lg shadow-sm border border-blue-100 mb-6">
      <div class="flex flex-wrap gap-2">
        <a href="#education" class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition-colors flex items-center">
          <i class="fas fa-graduation-cap mr-2"></i> Education
        </a>
        <a href="#experience" class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition-colors flex items-center">
          <i class="fas fa-briefcase mr-2"></i> Experience
        </a>
        <a href="#skills" class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition-colors flex items-center">
          <i class="fas fa-chart-bar mr-2"></i> Skills
        </a>
        <a href="#projects" class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition-colors flex items-center">
          <i class="fas fa-project-diagram mr-2"></i> Projects
        </a>
        <a href="#certifications" class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition-colors flex items-center">
          <i class="fas fa-certificate mr-2"></i> Certifications
        </a>
        <a href="#achievements" class="px-3 py-1.5 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition-colors flex items-center">
          <i class="fas fa-trophy mr-2"></i> Achievements
        </a>
      </div>
    </div>
    
    <!-- Display the appropriate component based on activeSubsection -->
    <div v-if="!activeSubsection" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Education Section -->
      <div v-if="hasEducationData || true" id="education" class="bg-white rounded-lg shadow-sm border border-blue-100 overflow-hidden transition-all duration-300">
        <div @click="toggleSection('education')" class="flex justify-between items-center p-4 cursor-pointer bg-gradient-to-r from-blue-50 to-white border-b border-blue-100">
          <div class="flex items-center">
            <div class="bg-blue-100 p-2 rounded-full mr-3">
              <i class="fas fa-graduation-cap text-blue-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-blue-800">Education</h3>
          </div>
          <i class="fas" :class="expandedSections.education ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </div>
        <div v-show="expandedSections.education" class="p-4">
          <EducationSection
            :activeSection="'education'"
            :educationEntries="educationEntries"
            :archivedEducationEntries="archivedEducationEntries"
            :institutions="institutions"
            :graduate="graduate"
            @close-all-modals="emit('close-all-modals')"
            @reset-all-states="emit('reset-all-states')"
            @refresh-education="emit('refresh-education')"
          />
        </div>
      </div>
      
      <!-- Experience Section -->
      <div v-if="hasExperienceData || true" id="experience" class="bg-white rounded-lg shadow-sm border border-blue-100 overflow-hidden transition-all duration-300">
        <div @click="toggleSection('experience')" class="flex justify-between items-center p-4 cursor-pointer bg-gradient-to-r from-blue-50 to-white border-b border-blue-100">
          <div class="flex items-center">
            <div class="bg-blue-100 p-2 rounded-full mr-3">
              <i class="fas fa-briefcase text-blue-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-blue-800">Work Experience</h3>
          </div>
          <i class="fas" :class="expandedSections.experience ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </div>
        <div v-show="expandedSections.experience" class="p-4">
          <ExperienceSection
            :activeSection="'experience'"
            :experienceEntries="experienceEntries"
            :archivedExperienceEntries="archivedExperienceEntries"
            @close-all-modals="emit('close-all-modals')"
            @reset-all-states="emit('reset-all-states')"
          />
        </div>
      </div>
      
      <!-- Skills Section -->
      <div v-if="hasSkillsData || true" id="skills" class="bg-white rounded-lg shadow-sm border border-blue-100 overflow-hidden transition-all duration-300">
        <div @click="toggleSection('skills')" class="flex justify-between items-center p-4 cursor-pointer bg-gradient-to-r from-blue-50 to-white border-b border-blue-100">
          <div class="flex items-center">
            <div class="bg-blue-100 p-2 rounded-full mr-3">
              <i class="fas fa-chart-bar text-blue-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-blue-800">Skills</h3>
          </div>
          <div class="flex items-center">
            <button 
              v-if="hasSkillsData"
              @click.stop="isSkillsChartModalOpen = true" 
              class="mr-3 text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 p-1.5 rounded-full transition-colors"
              title="View Skills Distribution Chart"
            >
              <i class="fas fa-chart-pie"></i>
            </button>
            <i class="fas" :class="expandedSections.skills ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
          </div>
        </div>
        <div v-show="expandedSections.skills" class="p-4">
          <SkillSection
            :activeSection="'skills'"
            :skillEntries="skillEntries"
            :archivedSkillEntries="archivedSkillEntries"
            @close-all-modals="emit('close-all-modals')"
            @reset-all-states="emit('reset-all-states')"
            @refresh-skills="emit('refresh-skills')"
          />
        </div>
      </div>
      
      <!-- Projects Section -->
      <div v-if="hasProjectsData || true" id="projects" class="bg-white rounded-lg shadow-sm border border-blue-100 overflow-hidden transition-all duration-300">
        <div @click="toggleSection('projects')" class="flex justify-between items-center p-4 cursor-pointer bg-gradient-to-r from-blue-50 to-white border-b border-blue-100">
          <div class="flex items-center">
            <div class="bg-blue-100 p-2 rounded-full mr-3">
              <i class="fas fa-project-diagram text-blue-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-blue-800">Projects</h3>
          </div>
          <i class="fas" :class="expandedSections.projects ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </div>
        <div v-show="expandedSections.projects" class="p-4">
          <ProjectSection
            :activeSection="'projects'"
            :projectsEntries="projectsEntries"
            :archivedProjectsEntries="archivedProjectsEntries"
            @close-all-modals="emit('close-all-modals')"
            @reset-all-states="emit('reset-all-states')"
          />
        </div>
      </div>
      
      <!-- Certifications Section -->
      <div v-if="hasCertificationsData || true" id="certifications" class="bg-white rounded-lg shadow-sm border border-blue-100 overflow-hidden transition-all duration-300">
        <div @click="toggleSection('certifications')" class="flex justify-between items-center p-4 cursor-pointer bg-gradient-to-r from-blue-50 to-white border-b border-blue-100">
          <div class="flex items-center">
            <div class="bg-blue-100 p-2 rounded-full mr-3">
              <i class="fas fa-certificate text-blue-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-blue-800">Certifications</h3>
          </div>
          <i class="fas" :class="expandedSections.certifications ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </div>
        <div v-show="expandedSections.certifications" class="p-4">
          <CertificationSection
            :activeSection="'certifications'"
            :certificationsEntries="certificationsEntries"
            :archivedCertificationsEntries="archivedCertificationsEntries"
            @close-all-modals="emit('close-all-modals')"
            @reset-all-states="emit('reset-all-states')"
          />
        </div>
      </div>
      
      <!-- Achievements Section -->
      <div v-if="hasAchievementsData || true" id="achievements" class="bg-white rounded-lg shadow-sm border border-blue-100 overflow-hidden transition-all duration-300">
        <div @click="toggleSection('achievements')" class="flex justify-between items-center p-4 cursor-pointer bg-gradient-to-r from-blue-50 to-white border-b border-blue-100">
          <div class="flex items-center">
            <div class="bg-blue-100 p-2 rounded-full mr-3">
              <i class="fas fa-trophy text-blue-600"></i>
            </div>
            <h3 class="text-lg font-semibold text-blue-800">Achievements</h3>
          </div>
          <i class="fas" :class="expandedSections.achievements ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </div>
        <div v-show="expandedSections.achievements" class="p-4">
          <AchievementSection
            :activeSection="'achievements'"
            :achievementEntries="achievementEntries"
            :archivedAchievementEntries="archivedAchievementEntries"
            @close-all-modals="emit('close-all-modals')"
            @reset-all-states="emit('reset-all-states')"
          />
        </div>
      </div>
    </div>
    
    <!-- Single Component Display Area when a specific subsection is selected -->
    <div v-else>
      <EducationSection
        v-if="activeSubsection === 'education'"
        :activeSection="'education'"
        :educationEntries="educationEntries"
        :archivedEducationEntries="archivedEducationEntries"
        :institutions="institutions"
        :graduate="graduate"
        @close-all-modals="emit('close-all-modals')"
        @reset-all-states="emit('reset-all-states')"
        @refresh-education="emit('refresh-education')"
      />
      
      <ExperienceSection
        v-if="activeSubsection === 'experience'"
        :activeSection="'experience'"
        :experienceEntries="experienceEntries"
        :archivedExperienceEntries="archivedExperienceEntries"
        @close-all-modals="emit('close-all-modals')"
        @reset-all-states="emit('reset-all-states')"
      />
      
      <CertificationSection
        v-if="activeSubsection === 'certifications'"
        :activeSection="'certifications'"
        :certificationsEntries="certificationsEntries"
        :archivedCertificationsEntries="archivedCertificationsEntries"
        @close-all-modals="emit('close-all-modals')"
        @reset-all-states="emit('reset-all-states')"
      />
      
      <AchievementSection
        v-if="activeSubsection === 'achievements'"
        :activeSection="'achievements'"
        :achievementEntries="achievementEntries"
        :archivedAchievementEntries="archivedAchievementEntries"
        @close-all-modals="emit('close-all-modals')"
        @reset-all-states="emit('reset-all-states')"
      />
      
      <SkillSection
        v-if="activeSubsection === 'skills'"
        :activeSection="'skills'"
        :skillEntries="skillEntries"
        :archivedSkillEntries="archivedSkillEntries"
        @close-all-modals="emit('close-all-modals')"
        @reset-all-states="emit('reset-all-states')"
        @refresh-skills="emit('refresh-skills')"
      />
      
      <ProjectSection
        v-if="activeSubsection === 'projects'"
        :activeSection="'projects'"
        :projectsEntries="projectsEntries"
        :archivedProjectsEntries="archivedProjectsEntries"
        @close-all-modals="emit('close-all-modals')"
        @reset-all-states="emit('reset-all-states')"
      />
    </div>
    
    <!-- Skills Chart Modal -->
    <div v-if="isSkillsChartModalOpen" class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl overflow-hidden">
        <div class="flex justify-between items-center p-4 border-b border-blue-100">
          <h3 class="text-lg font-semibold text-blue-800">Skills Distribution</h3>
          <button @click="isSkillsChartModalOpen = false" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="p-6">
          <SkillsChart :skills="skillEntries" />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Smooth transitions for collapsible sections */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Smooth scrolling for anchor links */
html {
  scroll-behavior: smooth;
}

/* Add some spacing between sections */
#education, #experience, #skills, #projects, #certifications, #achievements {
  scroll-margin-top: 1rem;
}
</style>