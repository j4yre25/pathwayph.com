<script setup>
import { ref } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { isValid } from 'date-fns';
import Graduate from '@/Layouts/AppLayout.vue';

// Import all the necessary components
import General from './General.vue';
import Security from './Security.vue';
import Internship from './Internship.vue';
import BatchUpload from './BatchUpload.vue';
import CareerProfile from './CareerProfile.vue';

// Import specific subsection components for direct access
import EducationSection from './Education.vue';
import ExperienceSection from './Experience.vue';
import CertificationSection from './Certification.vue';
import AchievementSection from './Achievement.vue';
import SkillSection from './Skill.vue';
import ProjectSection from './Project.vue';
import TestimonialSection from './Testimonial.vue';
import ResumeSection from './Resume.vue';
import AlumniStoriesSection from './AlumniStories.vue';
import CareerGoalsSection from './CareerGoals.vue';
import EmploymentSection from './Employment.vue';

// State management - only keep activeSection, remove activeSubsection
const activeSection = ref(localStorage.getItem('activeSection') || 'general');

const setActiveSection = (section) => {
  activeSection.value = section;
  localStorage.setItem('activeSection', section);
};

// Define the menu structure - remove subsections since all are displayed on the same page
const menuStructure = {
  'general': { title: 'General', icon: 'fas fa-user' },
  'security': { title: 'Security', icon: 'fas fa-lock' },
  'professional-background': { 
    title: 'Professional Background', 
    icon: 'fas fa-briefcase'
  },
  'supporting-documents': { 
    title: 'Supporting Documents', 
    icon: 'fas fa-file-alt'
  },
  'career-profile': { 
    title: 'Career Profile', 
    icon: 'fas fa-bullseye'
  },
  'internship': { title: 'Internship', icon: 'fas fa-user-graduate' },
  'batch-upload': { title: 'Batch Upload', icon: 'fas fa-upload' },
};

// Helper functions for refreshing data
const refreshSkills = () => {
  router.visit(route('profile.index'), {
    only: ['skillEntries', 'archivedSkillEntries'],
    preserveScroll: true,
  });
};

const refreshEducation = () => {
  router.visit(route('profile.index'), {
    only: ['educationEntries', 'archivedEducationEntries'],
    preserveScroll: true,
  });
};

// Modal and state management functions
const closeAllModals = () => {
  // This function would be implemented to close all modals
  console.log('Closing all modals');
};

const resetAllStates = () => {
  // This function would be implemented to reset all states
  console.log('Resetting all states');
};

// Get props from the page
const { props } = usePage();
</script>

<template>
  <Graduate>
    <div class="gradient-bg min-h-screen relative overflow-hidden">
      <!-- Floating background elements -->
      <div class="absolute top-20 left-10 w-64 h-64 gradient-card rounded-full opacity-20 animate-float"></div>
      <div class="absolute top-40 right-20 w-48 h-48 gradient-feature rounded-full opacity-30 animate-float-reverse"></div>
      <div class="absolute bottom-20 left-1/4 w-72 h-72 gradient-cta rounded-full opacity-15 animate-morph"></div>
      <div class="absolute top-1/3 right-1/3 w-32 h-32 bg-white rounded-full opacity-10 animate-pulse-glow"></div>
      
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 relative z-10">
        <!-- Header Section with Background -->
        <div class="relative mb-8">
          <!-- Floating background elements -->
          <div class="absolute inset-0 overflow-hidden rounded-lg">
            <div class="absolute top-4 left-8 w-32 h-32 gradient-card rounded-full opacity-15 animate-float"></div>
            <div class="absolute top-8 right-12 w-24 h-24 gradient-feature rounded-full opacity-20 animate-float-reverse"></div>
            <div class="absolute bottom-4 left-1/3 w-40 h-40 gradient-cta rounded-full opacity-10 animate-morph"></div>
            <div class="absolute top-1/2 right-1/4 w-16 h-16 bg-white rounded-full opacity-15 animate-pulse-glow"></div>
          </div>
          
          <div class="relative bg-gradient-to-r from-blue-700 to-blue-900 p-8 rounded-lg text-white shadow-lg">
            <div class="text-center">
              <h1 class="text-4xl font-bold mb-4 enhanced-text">Profile Settings</h1>
              <p class="text-white/90 text-lg animate-fade-in">Manage your personal information and account settings</p>
            </div>
          </div>
        </div>

        <div class="flex flex-col md:flex-row gap-6 relative">
          <!-- Collapsible Sidebar Navigation -->
          <div class="w-full md:w-16 md:hover:w-64 bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden h-fit mb-6 md:mb-0 transition-all duration-300 sidebar-nav group">
            <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-blue-700 to-blue-900 relative overflow-hidden">
              <div class="absolute inset-0 bg-white opacity-10 transform -skew-x-12"></div>
              <div class="relative z-10 flex items-center">
                <i class="fas fa-cog text-white text-lg"></i>
                <h2 class="font-medium text-white ml-3 md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300">
                  Settings
                </h2>
              </div>
            </div>

            <div class="p-2">
              <!-- Main sections -->
              <div v-for="(item, key) in menuStructure" :key="key" class="mb-1">
                <!-- Main section button -->
                <button @click="setActiveSection(key)"
                  class="w-full text-left px-4 py-3 rounded-md flex items-center transition-all duration-200 mb-1 hover:shadow-sm"
                  :class="{
                    'bg-gradient-to-r from-blue-50 to-blue-100 text-blue-700 shadow-sm border border-blue-200': activeSection === key,
                    'text-gray-700 hover:bg-gray-50 hover:text-blue-600': activeSection !== key
                  }">
                  <div class="flex items-center w-full">
                    <div :class="{'bg-blue-100 text-blue-600': activeSection === key, 'text-blue-500': activeSection !== key}"
                      class="min-w-[32px] flex justify-center p-2 rounded-full transition-colors duration-200">
                      <i :class="[item.icon, 'w-4 text-center']"></i>
                    </div>
                    <span class="text-sm font-medium ml-3 whitespace-nowrap md:opacity-0 md:group-hover:opacity-100 transition-opacity duration-300">
                      {{ item.title }}
                    </span>
                  </div>
                </button>
              </div>
            </div>
          </div>

          <!-- Content Area -->
          <div class="flex-1 bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6">
              <!-- General Settings -->
              <General v-if="activeSection === 'general'" :activeSection="activeSection"
                :educationEntries="props.educationEntries" @close-all-modals="closeAllModals"
                @reset-all-states="resetAllStates" />

              <!-- Security Settings -->
              <Security v-if="activeSection === 'security'" :activeSection="activeSection"
                @close-all-modals="closeAllModals" @reset-all-states="resetAllStates" />

              <!-- Professional Background Settings - All subsections in one page -->
              <div v-if="activeSection === 'professional-background'" class="space-y-12">
                <div class="flex justify-between items-center bg-gradient-to-r from-blue-700 to-blue-900 p-6 rounded-lg text-white shadow-lg">
                  <div>
                    <h2 class="text-2xl font-bold">Professional Background</h2>
                    <p class="mt-1 opacity-90">Manage your education, work experience, skills, and more</p>
                  </div>
                  <div class="hidden md:block">
                    <i class="fas fa-user-tie text-4xl opacity-80"></i>
                  </div>
                </div>

                <!-- Education Section - Most Important -->
                <div
                  class="bg-white p-6 rounded-lg border border-gray-200 shadow-md hover:shadow-lg transition-shadow duration-300">
                  <div class="flex items-center mb-4 border-b pb-3">
                    <div class="bg-indigo-100 p-2 rounded-full mr-3">
                      <i class="fas fa-graduation-cap text-blue-700"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Education</h3>
                  </div>
                  <EducationSection :activeSection="'education'" :educationEntries="props.educationEntries"
                    :archivedEducationEntries="props.archivedEducationEntries" :institutions="props.institutions"
                    :graduate="props.graduate" @close-all-modals="closeAllModals" @reset-all-states="resetAllStates"
                    @refresh-education="refreshEducation" />
                </div>

                <!-- Experience Section - Second Most Important -->
                <div
                  class="bg-white p-6 rounded-lg border border-gray-200 shadow-md hover:shadow-lg transition-shadow duration-300">
                  <div class="flex items-center mb-4 border-b pb-3">
                    <div class="bg-indigo-100 p-2 rounded-full mr-3">
                      <i class="fas fa-briefcase text-blue-700"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Work Experience</h3>
                  </div>
                  <ExperienceSection :activeSection="'experience'" :experienceEntries="props.experienceEntries"
                    :archivedExperienceEntries="props.archivedExperienceEntries" @close-all-modals="closeAllModals"
                    @reset-all-states="resetAllStates" />
                </div>

                <!-- Skills Section - Third Most Important -->
                <div
                  class="bg-white p-6 rounded-lg border border-gray-200 shadow-md hover:shadow-lg transition-shadow duration-300">
                  <div class="flex items-center mb-4 border-b pb-3">
                    <div class="bg-indigo-100 p-2 rounded-full mr-3">
                      <i class="fas fa-tools text-blue-700"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Skills</h3>
                  </div>
                  <SkillSection :activeSection="'skills'" :skillEntries="props.skillEntries"
                    :archivedSkillEntries="props.archivedSkillEntries" @close-all-modals="closeAllModals"
                    @reset-all-states="resetAllStates" @refresh-skills="refreshSkills" />
                </div>

                <!-- Projects Section - Fourth Most Important -->
                <div
                  class="bg-white p-6 rounded-lg border border-gray-200 shadow-md hover:shadow-lg transition-shadow duration-300">
                  <div class="flex items-center mb-4 border-b pb-3">
                    <div class="bg-indigo-100 p-2 rounded-full mr-3">
                      <i class="fas fa-project-diagram text-blue-700"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Projects</h3>
                  </div>
                  <ProjectSection :activeSection="'projects'" :projectsEntries="props.projectsEntries"
                    :archivedProjectsEntries="props.archivedProjectsEntries" @close-all-modals="closeAllModals"
                    @reset-all-states="resetAllStates" />
                </div>

                <!-- Certifications Section - Fifth Most Important -->
                <div
                  class="bg-white p-6 rounded-lg border border-gray-200 shadow-md hover:shadow-lg transition-shadow duration-300">
                  <div class="flex items-center mb-4 border-b pb-3">
                    <div class="bg-indigo-100 p-2 rounded-full mr-3">
                      <i class="fas fa-certificate text-blue-700"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Certifications</h3>
                  </div>
                  <CertificationSection :activeSection="'certifications'"
                    :certificationsEntries="props.certificationsEntries"
                    :archivedCertificationsEntries="props.archivedCertificationsEntries"
                    @close-all-modals="closeAllModals" @reset-all-states="resetAllStates" />
                </div>

                <!-- Achievements Section - Least Important -->
                <div
                  class="bg-white p-6 rounded-lg border border-gray-200 shadow-md hover:shadow-lg transition-shadow duration-300">
                  <div class="flex items-center mb-4 border-b pb-3">
                    <div class="bg-indigo-100 p-2 rounded-full mr-3">
                      <i class="fas fa-trophy text-blue-600"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Achievements</h3>
                  </div>
                  <AchievementSection :activeSection="'achievements'" :achievementEntries="props.achievementEntries"
                    :archivedAchievementEntries="props.archivedAchievementEntries" @close-all-modals="closeAllModals"
                    @reset-all-states="resetAllStates" />
                </div>
              </div>

              <!-- Supporting Documents Settings - All subsections in one page -->
              <div v-if="activeSection === 'supporting-documents'" class="space-y-12">
                <div class="flex justify-between items-center bg-gradient-to-r from-blue-700 to-blue-900 p-6 rounded-lg text-white shadow-lg">
                  <div>
                    <h2 class="text-2xl font-bold">Supporting Documents</h2>
                    <p class="mt-1 opacity-90">Manage your testimonials, resumes, and other supporting documents</p>
                  </div>
                  <div class="hidden md:block">
                    <i class="fas fa-file-alt text-4xl opacity-80"></i>
                  </div>
                </div>

                <!-- Resume Section - Most Important -->
                <div
                  class="bg-white p-6 rounded-lg border border-gray-200 shadow-md hover:shadow-lg transition-shadow duration-300">
                  <div class="flex items-center mb-4 border-b pb-3">
                    <div class="bg-indigo-100 p-2 rounded-full mr-3">
                      <i class="fas fa-file-alt text-blue-700"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Resume</h3>
                  </div>
                  <ResumeSection :activeSection="'resume'" :resume="props.resume" @close-all-modals="closeAllModals"
                    @reset-all-states="resetAllStates" />
                </div>

                <!-- Testimonials Section - Second Most Important -->
                <div
                  class="bg-white p-6 rounded-lg border border-gray-200 shadow-md hover:shadow-lg transition-shadow duration-300">
                  <div class="flex items-center mb-4 border-b pb-3">
                    <div class="bg-indigo-100 p-2 rounded-full mr-3">
                      <i class="fas fa-comment-dots text-blue-700"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Testimonials</h3>
                  </div>
                  <TestimonialSection :activeSection="'testimonials'" :testimonialEntries="props.testimonialsEntries"
                    :archivedTestimonialEntries="props.archivedTestimonialsEntries" @close-all-modals="closeAllModals"
                    @reset-all-states="resetAllStates" />
                </div>

                <!-- Alumni Stories Section - Least Important -->
                <!-- <div
                  class="bg-white p-6 rounded-lg border border-gray-200 shadow-md hover:shadow-lg transition-shadow duration-300">
                  <div class="flex items-center mb-4 border-b pb-3">
                    <div class="bg-indigo-100 p-2 rounded-full mr-3">
                      <i class="fas fa-book-open text-blue-700"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-800">Alumni Stories</h3>
                  </div>
                  <AlumniStoriesSection :activeSection="'alumni-stories'"
                    :alumniStoriesEntries="props.alumniStoriesEntries"
                    :archivedAlumniStoriesEntries="props.archivedAlumniStoriesEntries"
                    @close-all-modals="closeAllModals" @reset-all-states="resetAllStates" />
                </div> -->
              </div>

              <!-- Career Profile Settings -->
              <CareerProfile v-if="activeSection === 'career-profile'" :activeSection="activeSection"
                :careerGoals="props.careerGoals" :employmentPreferences="props.employmentPreferences"
                @close-all-modals="closeAllModals" @reset-all-states="resetAllStates" />

              <!-- Internship Settings -->
              <Internship v-if="activeSection === 'internship'" :internships="props.internships"
                :programs="props.programs" :careerOpportunities="props.careerOpportunities" />

              <!-- Batch Upload Settings -->
              <BatchUpload v-if="activeSection === 'batch-upload'" :degreeCodes="props.degreeCodes"
                :programCodes="props.programCodes" :companyNames="props.companyNames"
                :companyNamesMap="props.companyNamesMap" :institutionSchoolYears="props.institutionSchoolYears" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </Graduate>
</template>

<style>
/* Add animation for subsection appearance */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Add styles for the active subsection */
.subsection-active {
  background-color: #EBF4FF;
  font-weight: 600;
}

/* Enhanced styling for the new single-page layout */
.space-y-12 > div {
  margin-bottom: 2rem;
}

.bg-white.p-6.rounded-lg {
  transition: all 0.3s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.bg-white.p-6.rounded-lg:hover {
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Section headers */
.flex.items-center.mb-4 {
  border-bottom: 1px solid #edf2f7;
  padding-bottom: 0.75rem;
  margin-bottom: 1.25rem;
}

/* Improve spacing between sections */
.space-y-12 > div:not(:last-child) {
  padding-bottom: 1.5rem;
}

/* Sidebar hover effect */
.sidebar-nav {
  overflow: hidden;
  transition: width 0.3s ease;
}

.sidebar-nav:hover {
  width: 16rem !important;
}

/* Make the sidebar a container for hover effects */
.sidebar-nav {
  position: relative;
}

/* Apply hover effect to the entire sidebar */
.sidebar-nav:hover .md\:opacity-0 {
  opacity: 1;
}

/* Enhanced styling for sections */
.section-header {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #e5edff;
}

.section-header-icon {
  background-color: #e0e7ff;
  color: #4f46e5;
  padding: 0.75rem;
  border-radius: 9999px;
  margin-right: 1rem;
}

.section-header-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e40af;
}

.section-card {
  background-color: white;
  border-radius: 0.5rem;
  border: 1px solid #e5e7eb;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.section-card:hover {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  border-color: #d1d5db;
}

.blue-accent {
  color: #1e40af;
  border-color: #93c5fd;
}

.blue-bg-light {
  background-color: #eff6ff;
}

.blue-text {
  color: #1e40af;
}

.tooltip {
  position: relative;
  display: inline-block;
}

.tooltip .tooltip-text {
  visibility: hidden;
  width: 120px;
  background-color: #1e40af;
  color: white;
  text-align: center;
  border-radius: 6px;
  padding: 5px;
  position: absolute;
  z-index: 1;
  bottom: 125%;
  left: 50%;
  margin-left: -60px;
  opacity: 0;
  transition: opacity 0.3s;
}

.tooltip:hover .tooltip-text {
  visibility: visible;
  opacity: 1;
}

.call-to-action {
  background-color: #eff6ff;
  border: 1px dashed #93c5fd;
  border-radius: 0.5rem;
  padding: 1rem;
  text-align: center;
  margin: 1rem 0;
}

.call-to-action p {
  color: #1e40af;
  margin-bottom: 0.5rem;
}

.archive-toggle {
  display: inline-flex;
  align-items: center;
  font-size: 0.875rem;
  color: #4b5563;
  background-color: #f3f4f6;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  margin-left: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.archive-toggle:hover {
  background-color: #e5e7eb;
}

.archive-toggle input {
  margin-right: 0.5rem;
}

/* Gradient backgrounds for floating elements */
.gradient-card {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.gradient-feature {
  background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
}

.gradient-cta {
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

/* Animations */
@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(5deg); }
}

@keyframes float-reverse {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(20px) rotate(-5deg); }
}

@keyframes pulse-glow {
  0%, 100% { opacity: 0.15; transform: scale(1); }
  50% { opacity: 0.25; transform: scale(1.1); }
}

@keyframes morph {
  0%, 100% { border-radius: 50%; transform: rotate(0deg); }
  25% { border-radius: 30% 70% 70% 30%; transform: rotate(90deg); }
  50% { border-radius: 70% 30% 30% 70%; transform: rotate(180deg); }
  75% { border-radius: 40% 60% 60% 40%; transform: rotate(270deg); }
}

.animate-float {
  animation: float 6s ease-in-out infinite;
}

.animate-float-reverse {
  animation: float-reverse 8s ease-in-out infinite;
}

.animate-pulse-glow {
  animation: pulse-glow 4s ease-in-out infinite;
}

.animate-morph {
  animation: morph 12s ease-in-out infinite;
}
</style>