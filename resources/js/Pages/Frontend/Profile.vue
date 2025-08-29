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
    <!-- Modern Gradient Header Section -->
    <div class="relative min-h-screen bg-gradient-to-br from-purple-400 via-pink-500 to-red-500 overflow-hidden">
      <!-- Abstract Background Shapes -->
      <div class="absolute inset-0">
        <div class="absolute top-0 left-0 w-72 h-72 bg-white bg-opacity-10 rounded-full mix-blend-multiply filter blur-xl animate-blob"></div>
        <div class="absolute top-0 right-0 w-72 h-72 bg-yellow-300 bg-opacity-20 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 bg-opacity-20 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-4000"></div>
      </div>

      <!-- Navigation Breadcrumb -->
      <div class="relative z-10 pt-6 px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center space-x-2 text-white text-sm">
          <a href="#" class="hover:text-yellow-200 transition-colors">Home</a>
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
          </svg>
          <span class="text-yellow-200">User Profile</span>
        </nav>
      </div>

      <!-- Star Icon -->
      <div class="absolute top-20 right-8 text-yellow-300 animate-pulse">
        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
        </svg>
      </div>

      <!-- Profile Card -->
      <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-32">
        <div class="bg-white bg-opacity-95 backdrop-blur-sm rounded-3xl shadow-2xl p-8 transform hover:scale-105 transition-all duration-300">
          <!-- User Avatar and Info -->
          <div class="flex flex-col md:flex-row items-center md:items-start space-y-6 md:space-y-0 md:space-x-8">
            <!-- Avatar -->
            <div class="relative">
              <div class="w-32 h-32 rounded-full bg-gradient-to-r from-purple-400 to-pink-400 p-1">
                <img 
                  :src="props.graduate?.profile_picture || '/images/default-avatar.png'" 
                  :alt="props.graduate?.first_name + ' ' + props.graduate?.last_name"
                  class="w-full h-full rounded-full object-cover bg-white"
                >
              </div>
              <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-green-500 rounded-full border-4 border-white flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
              </div>
            </div>

            <!-- User Info -->
            <div class="flex-1 text-center md:text-left">
              <h1 class="text-3xl font-bold text-gray-900 mb-2">
                {{ props.graduate?.first_name }} {{ props.graduate?.last_name }}
              </h1>
              <p class="text-lg text-gray-600 mb-4">{{ props.graduate?.course || 'Software Developer' }}</p>
              
              <!-- Stats -->
              <div class="flex flex-wrap justify-center md:justify-start gap-6 mb-6">
                <div class="text-center">
                  <div class="text-2xl font-bold text-purple-600">{{ props.skillEntries?.length || 12 }}</div>
                  <div class="text-sm text-gray-500">Skills</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-pink-600">{{ props.experienceEntries?.length || 5 }}</div>
                  <div class="text-sm text-gray-500">Experience</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-red-600">{{ props.projectsEntries?.length || 8 }}</div>
                  <div class="text-sm text-gray-500">Projects</div>
                </div>
              </div>

              <!-- Social Links -->
              <div class="flex justify-center md:justify-start space-x-4 mb-6">
                <a href="#" class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white hover:bg-blue-600 transition-colors">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                  </svg>
                </a>
                <a href="#" class="w-10 h-10 bg-blue-700 rounded-full flex items-center justify-center text-white hover:bg-blue-800 transition-colors">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                  </svg>
                </a>
                <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center text-white hover:bg-gray-900 transition-colors">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                  </svg>
                </a>
              </div>

              <!-- Action Button -->
              <button class="bg-gradient-to-r from-purple-500 to-pink-500 text-white px-8 py-3 rounded-full font-semibold hover:from-purple-600 hover:to-pink-600 transition-all duration-300 transform hover:scale-105 shadow-lg">
                Edit Profile
              </button>
            </div>
          </div>

          <!-- Tab Navigation -->
          <div class="mt-8 border-t border-gray-200 pt-6">
            <nav class="flex space-x-8 overflow-x-auto">
              <button 
                class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 focus:outline-none whitespace-nowrap"
                :class="activeSection === 'profile' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                @click="setActiveSection('profile')">
                Profile
              </button>
              <button 
                class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 focus:outline-none whitespace-nowrap"
                :class="activeSection === 'experience' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                @click="setActiveSection('experience')">
                Experience
              </button>
              <button 
                class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 focus:outline-none whitespace-nowrap"
                :class="activeSection === 'projects' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                @click="setActiveSection('projects')">
                Projects
              </button>
              <button 
                class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 focus:outline-none whitespace-nowrap"
                :class="activeSection === 'gallery' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                @click="setActiveSection('gallery')">
                Gallery
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="bg-gray-50 min-h-screen -mt-16 relative z-0">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-12">
        
        <!-- About Section -->
        <div v-if="activeSection === 'profile'" class="bg-white rounded-xl shadow-lg border border-gray-100 mb-8 overflow-hidden">
          <div class="bg-gradient-to-r from-purple-500 to-pink-500 px-6 py-4">
            <h2 class="text-xl font-bold text-white flex items-center">
              <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
              About Me
            </h2>
          </div>
          <div class="p-6">
            <div class="grid md:grid-cols-2 gap-8">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h3>
                <div class="space-y-3">
                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-gray-700">{{ props.graduate?.email || 'john.doe@example.com' }}</span>
                  </div>
                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    <span class="text-gray-700">{{ props.graduate?.phone || '+1 (555) 123-4567' }}</span>
                  </div>
                  <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="text-gray-700">{{ props.graduate?.address || 'New York, USA' }}</span>
                  </div>
                </div>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Professional Summary</h3>
                <p class="text-gray-600 leading-relaxed">
                  {{ props.graduate?.bio || 'Passionate software developer with expertise in modern web technologies. Experienced in building scalable applications and working with cross-functional teams to deliver high-quality solutions.' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Experience Section -->
        <div v-if="activeSection === 'experience'" class="bg-white rounded-xl shadow-lg border border-gray-100 mb-8 overflow-hidden">
          <div class="bg-gradient-to-r from-blue-500 to-indigo-500 px-6 py-4">
            <h2 class="text-xl font-bold text-white flex items-center">
              <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
              </svg>
              Work Experience
            </h2>
          </div>
          <div class="p-6">
            <div class="space-y-6">
              <div v-for="(experience, index) in props.experienceEntries?.slice(0, 3) || []" :key="index" class="border-l-4 border-blue-500 pl-6 pb-6">
                <div class="flex justify-between items-start mb-2">
                  <h3 class="text-lg font-semibold text-gray-900">{{ experience.position || 'Senior Software Developer' }}</h3>
                  <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">{{ experience.duration || '2021 - Present' }}</span>
                </div>
                <p class="text-purple-600 font-medium mb-2">{{ experience.company || 'Tech Solutions Inc.' }}</p>
                <p class="text-gray-600">{{ experience.description || 'Led development of scalable web applications using modern frameworks and technologies.' }}</p>
              </div>
              <!-- Default experience if no data -->
              <div v-if="!props.experienceEntries?.length" class="border-l-4 border-blue-500 pl-6 pb-6">
                <div class="flex justify-between items-start mb-2">
                  <h3 class="text-lg font-semibold text-gray-900">Senior Software Developer</h3>
                  <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">2021 - Present</span>
                </div>
                <p class="text-purple-600 font-medium mb-2">Tech Solutions Inc.</p>
                <p class="text-gray-600">Led development of scalable web applications using modern frameworks and technologies.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Projects Section -->
        <div v-if="activeSection === 'projects'" class="bg-white rounded-xl shadow-lg border border-gray-100 mb-8 overflow-hidden">
          <div class="bg-gradient-to-r from-green-500 to-teal-500 px-6 py-4">
            <h2 class="text-xl font-bold text-white flex items-center">
              <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
              </svg>
              Featured Projects
            </h2>
          </div>
          <div class="p-6">
            <div class="grid md:grid-cols-2 gap-6">
              <div v-for="(project, index) in props.projectsEntries?.slice(0, 4) || []" :key="index" class="bg-gray-50 rounded-lg p-6 hover:shadow-md transition-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ project.title || 'E-Commerce Platform' }}</h3>
                <p class="text-gray-600 mb-4">{{ project.description || 'A full-stack e-commerce solution built with modern web technologies.' }}</p>
                <div class="flex flex-wrap gap-2">
                  <span v-for="tech in (project.technologies || ['Vue.js', 'Laravel', 'MySQL']).slice(0, 3)" :key="tech" class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                    {{ tech }}
                  </span>
                </div>
              </div>
              <!-- Default projects if no data -->
              <div v-if="!props.projectsEntries?.length" class="bg-gray-50 rounded-lg p-6 hover:shadow-md transition-shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">E-Commerce Platform</h3>
                <p class="text-gray-600 mb-4">A full-stack e-commerce solution built with modern web technologies.</p>
                <div class="flex flex-wrap gap-2">
                  <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Vue.js</span>
                  <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Laravel</span>
                  <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">MySQL</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Gallery Section -->
        <div v-if="activeSection === 'gallery'" class="bg-white rounded-xl shadow-lg border border-gray-100 mb-8 overflow-hidden">
          <div class="bg-gradient-to-r from-pink-500 to-rose-500 px-6 py-4">
            <h2 class="text-xl font-bold text-white flex items-center">
              <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              Portfolio Gallery
            </h2>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
              <div v-for="i in 6" :key="i" class="aspect-square bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg flex items-center justify-center hover:shadow-lg transition-shadow cursor-pointer">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Graduate>
</template>

<style>
/* Blob animation for background shapes */
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}

/* Scrollbar hide for horizontal scroll */
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

/* Hover effects */
.hover\:scale-105:hover {
  transform: scale(1.05);
}

/* Backdrop blur support */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
}

/* Custom gradient backgrounds */
.bg-gradient-to-br {
  background-image: linear-gradient(to bottom right, var(--tw-gradient-stops));
}

/* Transition effects */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

.transition-colors {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

.transition-shadow {
  transition-property: box-shadow;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Mix blend mode */
.mix-blend-multiply {
  mix-blend-mode: multiply;
}

/* Filter blur */
.filter {
  filter: var(--tw-filter);
}

.blur-xl {
  --tw-blur: blur(24px);
  filter: var(--tw-blur) var(--tw-brightness) var(--tw-contrast) var(--tw-grayscale) var(--tw-hue-rotate) var(--tw-invert) var(--tw-saturate) var(--tw-sepia) var(--tw-drop-shadow);
}

/* Pulse animation */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
