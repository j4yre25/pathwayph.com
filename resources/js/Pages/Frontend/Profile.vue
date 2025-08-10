<script setup>
import Graduate from '@/Layouts/AppLayout.vue';
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { isValid } from 'date-fns';
import General from './ProfileSettings/General.vue';
import Security from './ProfileSettings/Security.vue';
import ProfessionalBackground from './ProfileSettings/ProfessionalBackground.vue';
import SupportingDocuments from './ProfileSettings/SupportingDocuments.vue';
import CareerProfile from './ProfileSettings/CareerProfile.vue';
import Internship from './ProfileSettings/Internship.vue';
import BatchUpload from './ProfileSettings/BatchUpload.vue';
import ProfileFlyoutMenu from '@/Components/ProfileFlyoutMenu.vue';

const activeSection = ref(localStorage.getItem('activeSection') || 'general');
const activeSubsection = ref(localStorage.getItem('activeSubsection') || null);

const setActiveSection = (section) => {
  activeSection.value = section;
  localStorage.setItem('activeSection', section);
  openFlyout.value = null;
};

const openFlyout = ref(null);

const toggleFlyout = (flyout) => {
  if (openFlyout.value === flyout) {
    openFlyout.value = null; 
  } else {
    openFlyout.value = flyout; 
  }
};

const navigateToSubsection = (mainSection, subSection) => {
  setActiveSection(mainSection);
  
  localStorage.setItem('activeSubsection', subSection);
  activeSubsection.value = subSection;
  
  openFlyout.value = null;
};

const submenuItems = {
  'professional-background': [
    { name: 'Education', icon: 'fas fa-graduation-cap', section: 'education' },
    { name: 'Work Experience', icon: 'fas fa-briefcase', section: 'experience' },
    { name: 'Certifications', icon: 'fas fa-certificate', section: 'certifications' },
    { name: 'Achievements', icon: 'fas fa-trophy', section: 'achievements' },
    { name: 'Skills', icon: 'fas fa-tools', section: 'skills' },
    { name: 'Projects', icon: 'fas fa-project-diagram', section: 'projects' },
  ],
  'supporting-documents': [
    { name: 'Testimonials', icon: 'fas fa-comment-dots', section: 'testimonials' },
    { name: 'Alumni Stories', icon: 'fas fa-book-open', section: 'alumni-stories' },
    { name: 'Resume', icon: 'fas fa-file-alt', section: 'resume' },
  ],
  'career-profile': [
    { name: 'Career Goals', icon: 'fas fa-bullseye', section: 'careerGoals' },
    { name: 'Employment Preferences', icon: 'fas fa-briefcase', section: 'employmentPreferences' },
  ],
};

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

const closeAddEducationModal = () => { };
const closeUpdateEducationModal = () => { };
const closeSkillsAddedModal = () => { };
const closeAddExperienceModal = () => { };
const closeUpdateExperienceModal = () => { };
const closeAddCertificationModal = () => { };
const closeAddAchievementModal = () => { };
const closeUpdateAchievementModal = () => { };
const closeAddTestimonialsModal = () => { };
const closeUpdateTestimonialsModal = () => { };
const closeAddLocationModal = () => { };
const closeAddIndustryModal = () => { };
const closeUpdateCertificationModal = () => { };

const resetEducation = () => { };
const resetExperience = () => { };
const resetAchievement = () => { };
const resetCertification = () => { };
const resetTestimonials = () => { };
const resetCareerGoals = () => { };
const resetEmploymentPreferences = () => { };

const closeAllModals = () => {
  closeAddEducationModal();
  closeUpdateEducationModal();
  closeSkillsAddedModal();
  closeAddExperienceModal();
  closeUpdateExperienceModal();
  closeAddCertificationModal();
  closeAddAchievementModal();
  closeUpdateAchievementModal();
  closeAddTestimonialsModal();
  closeUpdateTestimonialsModal();
  closeAddLocationModal();
  closeAddIndustryModal();
  closeUpdateCertificationModal();
};

const resetAllStates = () => {
  resetEducation();
  resetExperience();
  resetAchievement();
  resetCertification();
  resetTestimonials();
  resetCareerGoals();
  resetEmploymentPreferences();
  console.log('All states reset.');
};

const initializeData = () => {
  console.log('Initializing data...');
};

onMounted(() => {
  initializeData();
});

const formatDate = (date) => {
  if (!date) return '';
  const parsedDate = new Date(date);
  if (isNaN(parsedDate.getTime())) return '';
  parsedDate.setHours(0, 0, 0, 0);
  return parsedDate.toISOString().split('T')[0];
};

const formatDisplayDate = (date) => {
  if (!date) return '';
  const d = new Date(date);
  if (!isValid(d)) return '';
  try {
    return d.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  } catch (error) {
    console.error('Date formatting error:', error);
    return '';
  }
};

const { props } = usePage();
const user = ref(props.user || {});
const profile = ref({
  fullName: `${props.user?.graduate_first_name || ''} ${props.user?.graduate_middle_initial || ''} ${props.user?.graduate_last_name || ''}`.trim(),
  graduate_first_name: props.user?.graduate_first_name || '',
  graduate_middle_initial: props.user?.graduate_middle_initial || '',
  graduate_last_name: props.user?.graduate_last_name || '',
  graduate_professional_title: props.user?.graduate_professional_title || '',
  email: props.user?.email || '',
  graduate_phone: props.user?.contact_number || '',
  graduate_location: props.user?.graduate_location || '',
  graduate_birthdate: props.user?.dob ? new Date(props.user.dob) : null,
  graduate_gender: props.user?.gender || '',
  graduate_ethnicity: props.user?.graduate_ethnicity || '',
  graduate_address: props.user?.graduate_address || '',
  graduate_about_me: props.user?.graduate_about_me || '',
  graduate_picture_url: props.user?.P || 'path/to/default/image.jpg',
});

const settingsForm = useForm({
  graduate_first_name: profile.value.graduate_first_name,
  graduate_middle_initial: profile.value.graduate_middle_initial,
  graduate_last_name: profile.value.graduate_last_name,
  graduate_professional_title: profile.value.graduate_professional_title,
  email: profile.value.email,
  graduate_phone: profile.value.graduate_phone,
  graduate_location: profile.value.graduate_location,
  dob: profile.value.dob,
  graduate_gender: profile.value.graduate_gender,
  graduate_ethnicity: profile.value.graduate_ethnicity,
  graduate_address: profile.value.graduate_address,
  graduate_about_me: profile.value.graduate_about_me,
  graduate_picture_url: profile.value.graduate_picture_url,
});

const parseFullName = () => {
  const fullName = profile.value.fullName.trim();
  const nameParts = fullName.split(' ');

  if (nameParts.length >= 1) {
    profile.value.graduate_first_name = nameParts[0];
  }
  if (nameParts.length >= 2) {
    profile.value.graduate_last_name = nameParts[nameParts.length - 1];
  }
  if (nameParts.length > 2) {
    profile.value.graduate_middle_initial = nameParts[1].charAt(0);
  }
};

watch(() => profile.value.fullName, (newFullName) => {
  const nameParts = newFullName.trim().split(' ');
  profile.value.graduate_first_name = nameParts[0] || '';
  profile.value.graduate_last_name = nameParts[nameParts.length - 1] || '';
  profile.value.graduate_middle_initial = nameParts.length > 2 ? nameParts[1].charAt(0) : '';
});

watch(() => profile.value.fullName, () => {
  parseFullName();
});

const saveProfile = () => {
  if (profile.value.graduate_birthdate) {
    const date = new Date(profile.value.graduate_birthdate);
    settingsForm.dob = date.toISOString().split('T')[0];
  } else {
    settingsForm.dob = null;
  }
  settingsForm.graduate_first_name = profile.value.graduate_first_name;
  settingsForm.graduate_middle_initial = profile.value.graduate_middle_initial;
  settingsForm.graduate_last_name = profile.value.graduate_last_name;
  settingsForm.email = profile.value.email;
  settingsForm.graduate_phone = profile.value.graduate_phone;
  settingsForm.graduate_professional_title = profile.value.graduate_professional_title;
  settingsForm.graduate_location = profile.value.graduate_location;
  settingsForm.graduate_gender = profile.value.graduate_gender;
  settingsForm.graduate_ethnicity = profile.value.graduate_ethnicity;
  settingsForm.graduate_address = profile.value.graduate_address;
  settingsForm.graduate_about_me = profile.value.graduate_about_me;
  settingsForm.graduate_picture_url = profile.value.graduate_picture_url;

  const hasChanges = Object.keys(settingsForm.data()).some(
    (key) => settingsForm[key] !== profile.value[key]
  );

  if (!hasChanges) {
    isNoChangesModalOpen.value = true;
    return;
  }

  settingsForm.post(route('profile.update'), {
    onSuccess: (response) => {
      Object.assign(profile.value, settingsForm.data());
      modalState.value.profile = true;
      showSuccessModal();

      console.log('Profile saved successfully on the backend:', response.user);
    },
    onError: (errors) => {
      console.error('Error updating profile:', errors);
      alert('An error occurred while updating the profile. Please try again.');
    },
  });
};

// Profile Picture Handler
const onFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = (e) => {
      profile.value.graduate_picture_url = e.target.result;
      console.log('Profile picture updated:', file.name);
    };
    reader.readAsDataURL(file);
  }
};

const showSuccessModal = () => {
  isSuccessModalOpen.value = true;
  setTimeout(() => {
    isSuccessModalOpen.value = false;
  }, 3000);
};

// Watch for changes in activeSection to reset activeSubsection when needed
watch(() => activeSection.value, (newSection) => {
  // If changing to a different main section, clear the subsection
  if (newSection !== 'supporting-documents' && 
      newSection !== 'professional-background' && 
      newSection !== 'career-profile') {
    activeSubsection.value = null;
    localStorage.removeItem('activeSubsection');
  }
});
</script>

<template>
  <Graduate>
    <div class="bg-gray-50 min-h-screen">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Profile Settings</h1>
        <p class="text-gray-600 mb-6">Manage your personal information and account settings</p>
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <!-- Tab Navigation - Modern Style -->
          <div class="flex border-b border-gray-200 overflow-x-auto scrollbar-hide">
            <button 
              class="py-3.5 px-5 font-medium text-sm transition-colors duration-200 focus:outline-none whitespace-nowrap"
              :class="activeSection === 'general' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-900'"
              @click="setActiveSection('general')">
              General
            </button>
            
            <button 
              class="py-3.5 px-5 font-medium text-sm transition-colors duration-200 focus:outline-none whitespace-nowrap"
              :class="activeSection === 'security' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-900'"
              @click="setActiveSection('security')">
              Security
            </button>
            
            <!-- Professional Background Tab -->
            <div class="relative">
              <button 
                id="professional-background-button"
                class="py-3.5 px-5 font-medium text-sm transition-colors duration-200 focus:outline-none whitespace-nowrap flex items-center gap-1.5"
                :class="activeSection === 'professional-background' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-900'"
                @click="toggleFlyout('professional-background')">
                Professional Background
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="{'rotate-180': openFlyout === 'professional-background'}" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
              
              <!-- Use the new ProfileFlyoutMenu component -->
              <ProfileFlyoutMenu
                :is-open="openFlyout === 'professional-background'"
                :menu-items="submenuItems['professional-background']"
                :active-section="activeSection"
                :active-subsection="activeSubsection"
                width="64"
                button-id="professional-background-button"
                @navigate="(section) => navigateToSubsection('professional-background', section)"
                @close="openFlyout = null"
              />
            </div>
            
            <!-- Supporting Documents Tab -->
            <div class="relative">
              <button 
                id="supporting-documents-button"
                class="py-3.5 px-5 font-medium text-sm transition-colors duration-200 focus:outline-none whitespace-nowrap flex items-center gap-1.5"
                :class="activeSection === 'supporting-documents' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-900'"
                @click="toggleFlyout('supporting-documents')">
                Supporting Documents
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="{'rotate-180': openFlyout === 'supporting-documents'}" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
              
              <!-- Use the new ProfileFlyoutMenu component -->
              <ProfileFlyoutMenu
                :is-open="openFlyout === 'supporting-documents'"
                :menu-items="submenuItems['supporting-documents']"
                :active-section="activeSection"
                :active-subsection="activeSubsection"
                width="64"
                button-id="supporting-documents-button"
                @navigate="(section) => navigateToSubsection('supporting-documents', section)"
                @close="openFlyout = null"
              />
            </div>
            
            <!-- Career Profile Tab -->
            <div class="relative">
              <button 
                id="career-profile-button"
                class="py-3.5 px-5 font-medium text-sm transition-colors duration-200 focus:outline-none whitespace-nowrap flex items-center gap-1.5"
                :class="activeSection === 'career-profile' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-900'"
                @click="toggleFlyout('career-profile')">
                Career Profile
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-200" :class="{'rotate-180': openFlyout === 'career-profile'}" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
              
              <!-- Use the new ProfileFlyoutMenu component -->
              <ProfileFlyoutMenu
                :is-open="openFlyout === 'career-profile'"
                :menu-items="submenuItems['career-profile']"
                :active-section="activeSection"
                :active-subsection="activeSubsection"
                width="64"
                button-id="career-profile-button"
                @navigate="(section) => navigateToSubsection('career-profile', section)"
                @close="openFlyout = null"
              />
            </div>
            <button 
              class="py-3.5 px-5 font-medium text-sm transition-colors duration-200 focus:outline-none whitespace-nowrap"
              :class="activeSection === 'internship' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-900'"
              @click="setActiveSection('internship')">
              Internship
            </button>
            <button 
              class="py-3.5 px-5 font-medium text-sm transition-colors duration-200 focus:outline-none whitespace-nowrap"
              :class="activeSection === 'batch-upload' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-900'"
              @click="setActiveSection('batch-upload')">
              Batch Upload
            </button>
          </div>

          <!-- Tab Content -->
          <div class="p-5">
            <!-- General Settings -->
            <General 
              v-if="activeSection === 'general'"
              :activeSection="activeSection" 
              :educationEntries="props.educationEntries" 
              @close-all-modals="closeAllModals"
              @reset-all-states="resetAllStates" 
            />

            <!-- Security Settings -->
            <Security 
              v-if="activeSection === 'security'"
              :activeSection="activeSection" 
              @close-all-modals="closeAllModals"
              @reset-all-states="resetAllStates" 
            />

            <!-- Professional Background Settings -->
            <ProfessionalBackground 
              v-if="activeSection === 'professional-background'"
              :activeSection="activeSection"
              :activeSubsection="activeSubsection"
              :educationEntries="props.educationEntries"
              :archivedEducationEntries="props.archivedEducationEntries"
              :institutions="props.institutions"
              :graduate="props.graduate"
              :skillEntries="props.skillEntries"
              :archivedSkillEntries="props.archivedSkillEntries"
              :experienceEntries="props.experienceEntries"
              :archivedExperienceEntries="props.archivedExperienceEntries"
              :projectsEntries="props.projectsEntries"
              :archivedProjectsEntries="props.archivedProjectsEntries"
              :certificationsEntries="props.certificationsEntries"
              :archivedCertificationsEntries="props.archivedCertificationsEntries"
              :achievementEntries="props.achievementEntries"
              :archivedAchievementEntries="props.archivedAchievementEntries"
              @close-all-modals="closeAllModals"
              @reset-all-states="resetAllStates"
              @refresh-education="refreshEducation"
              @refresh-skills="refreshSkills"
            />

            <!-- Supporting Documents Settings -->
            <SupportingDocuments 
              v-if="activeSection === 'supporting-documents'"
              :activeSection="activeSection"
              :activeSubsection="activeSubsection"
              :testimonialEntries="props.testimonialsEntries"
              :archivedTestimonialEntries="props.archivedTestimonialsEntries"
              :resumeEntries="props.resumeEntries"
              :archivedResumeEntries="props.archivedResumeEntries"
              :alumniStoriesEntries="props.alumniStoriesEntries"
              :archivedAlumniStoriesEntries="props.archivedAlumniStoriesEntries"
              @close-all-modals="closeAllModals"
              @reset-all-states="resetAllStates"
            />

            <!-- Career Profile Settings -->
            <CareerProfile 
              v-if="activeSection === 'career-profile'"
              :activeSection="activeSection"
              :activeSubsection="activeSubsection"
              :employmentPreferences="props.employmentPreferences"
              :careerGoals="props.careerGoals"
              @close-all-modals="closeAllModals" 
              @reset-all-states="resetAllStates" 
            />

            <!-- Internship Settings -->
            <Internship
              v-if="activeSection === 'internship'"
              :internships="props.internships"
              :programs="props.programs"
              :careerOpportunities="props.careerOpportunities"
            />

            <!-- Batch Upload Settings -->
            <BatchUpload
              v-if="activeSection === 'batch-upload'"
              :degreeCodes="props.degreeCodes"
              :programCodes="props.programCodes"
              :companyNames="props.companyNames"
              :companyNamesMap="props.companyNamesMap"
              :institutionSchoolYears="props.institutionSchoolYears"
            />
          </div>
        </div>
      </div>
    </div>
  </Graduate>
</template>

<style>
/* Add animation for submenu appearance */
.animate-fadeIn {
  animation: fadeIn 0.2s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Add styles for the active subsection */
.subsection-active {
  background-color: #EBF4FF;
  font-weight: 600;
}
</style>
