<script setup>
import Graduate from '@/Layouts/AppLayout.vue';
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import { isValid } from 'date-fns';
import General from '@/Pages/Frontend/ProfileSettings/General.vue';
import Security from '@/Pages/Frontend/ProfileSettings/Security.vue';
import ProfessionalBackground from '@/Pages/Frontend/ProfileSettings/ProfessionalBackground.vue';
import SupportingDocuments from '@/Pages/Frontend/ProfileSettings/SupportingDocuments.vue';
import CareerProfile from '@/Pages/Frontend/ProfileSettings/CareerProfile.vue';
import Internship from '@/Pages/Frontend/ProfileSettings/Internship.vue';
import BatchUpload from '@/Pages/Frontend/ProfileSettings/BatchUpload.vue';

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
    <!-- Header Section with Colorful Gradient -->
    <div class="relative bg-gradient-to-br from-cyan-400 via-blue-500 to-purple-600 overflow-hidden">
      <!-- Abstract Background Shapes -->
      <div class="absolute inset-0">
        <div class="absolute top-0 left-0 w-96 h-96 bg-gradient-to-br from-yellow-300 to-orange-400 rounded-full opacity-30 -translate-x-32 -translate-y-32"></div>
        <div class="absolute top-20 right-0 w-80 h-80 bg-gradient-to-br from-pink-400 to-purple-500 rounded-full opacity-40 translate-x-32 -translate-y-20"></div>
        <div class="absolute bottom-0 left-1/2 w-72 h-72 bg-gradient-to-br from-green-300 to-blue-400 rounded-full opacity-25 -translate-x-36 translate-y-36"></div>
      <!-- Removed invalid end tag or replaced with correct tag if needed -->
      
      <!-- Navigation Breadcrumb -->
      <div class="relative z-10 pt-6 px-6">
        <div class="flex items-center text-white/80 text-sm">
          <span>User App</span>
          <svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
          </svg>
          <span>Home</span>
          <svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
          </svg>
          <span class="text-white">UserProfile</span>
        </div>
        
        <!-- Star Icon -->
        <div class="absolute top-6 right-6">
          <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.602-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>
          </div>
        </div>
      </div>
      
      <!-- Profile Card -->
      <div class="relative z-10 pt-16 pb-32 px-6">
        <div class="max-w-4xl mx-auto">
          <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
            <!-- Profile Header -->
            <div class="px-8 py-6">
              <div class="flex flex-col items-center text-center">
                <!-- Avatar -->
                <div class="relative mb-4">
                  <img
                    :src="props.graduate?.profile_picture || '/images/default-avatar.png'"
                    :alt="props.graduate?.first_name + ' ' + props.graduate?.last_name"
                    class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg"
                  />
                  <span
                    v-if="props.graduate?.is_featured"
                    class="absolute -top-2 -right-2 bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded-full"
                  >
                    Featured
                  </span>
                </div>
                
                <!-- User Info -->
                <h1 class="text-2xl font-bold text-gray-900 mb-1">
                  {{ props.graduate?.first_name }} {{ props.graduate?.last_name }}
                </h1>
                <p class="text-gray-600 mb-4">{{ props.graduate?.course || 'Designer' }}</p>
                
                <!-- Stats -->
                <div class="flex items-center space-x-8 mb-6">
                  <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ props.skillEntries?.length || 0 }}</div>
                    <div class="text-sm text-gray-500">Posts</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">3,586</div>
                    <div class="text-sm text-gray-500">Followers</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">2,659</div>
                    <div class="text-sm text-gray-500">Following</div>
                  </div>
                </div>
                
                <!-- Social Links and Action Button -->
                <div class="flex items-center space-x-4 mb-6">
                  <div class="flex space-x-3">
                    <a href="#" class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white hover:bg-blue-600 transition-colors">
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                      </svg>
                    </a>
                    <a href="#" class="w-10 h-10 bg-pink-500 rounded-full flex items-center justify-center text-white hover:bg-pink-600 transition-colors">
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.347-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.402.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.357-.629-2.746-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24.009 12.017 24.009c6.624 0 11.99-5.367 11.99-11.988C24.007 5.367 18.641.001.012.001z"/>
                      </svg>
                    </a>
                    <a href="#" class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center text-white hover:bg-red-600 transition-colors">
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                      </svg>
                    </a>
                  </div>
                  <button class="bg-blue-600 text-white px-6 py-2 rounded-full font-medium hover:bg-blue-700 transition-colors">
                    Add To Story
                  </button>
                </div>
                
                <!-- Tab Navigation -->
                <div class="flex items-center space-x-6 border-b border-gray-200 w-full">
                  <button 
                    class="pb-3 px-1 border-b-2 font-medium text-sm transition-colors duration-200 focus:outline-none whitespace-nowrap"
                    :class="activeSection === 'profile' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    @click="setActiveSection('profile')">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Profile
              </button>
                  <button 
                    class="pb-3 px-1 border-b-2 font-medium text-sm transition-colors duration-200 focus:outline-none whitespace-nowrap"
                    :class="activeSection === 'followers' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    @click="setActiveSection('followers')">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.196-2.121M9 12a4 4 0 100-8 4 4 0 000 8zm8 0a4 4 0 100-8 4 4 0 000 8zm-8 8a6 6 0 0112 0H9z"></path>
                    </svg>
                    Followers
                  </button>
                  <button 
                    class="pb-3 px-1 border-b-2 font-medium text-sm transition-colors duration-200 focus:outline-none whitespace-nowrap"
                    :class="activeSection === 'friends' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    @click="setActiveSection('friends')">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    Friends
                  </button>
                  <button 
                    class="pb-3 px-1 border-b-2 font-medium text-sm transition-colors duration-200 focus:outline-none whitespace-nowrap"
                    :class="activeSection === 'gallery' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    @click="setActiveSection('gallery')">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Gallery
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="bg-gray-50 min-h-screen relative z-0">
      <div class="max-w-4xl mx-auto px-6 py-8">
        
        <!-- Introduction Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8 overflow-hidden">
          <div class="p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Introduction</h2>
            <p class="text-gray-600 mb-4">
              {{ props.graduate?.bio || 'I am a passionate developer who loves making websites and graphics. Lorem ipsum dolor sit amet, consectetur adipiscing elit.' }}
            </p>
            
            <div class="space-y-3">
              <div class="flex items-center text-gray-600">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <span>{{ props.graduate?.institution || 'Sir P.P Institute Of Science' }}</span>
              </div>
              <div class="flex items-center text-gray-600">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <span>{{ props.graduate?.email || 'xyjonathan@gmail.com' }}</span>
              </div>
              <div class="flex items-center text-gray-600">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"></path>
                </svg>
                <span>{{ props.graduate?.website || 'www.xyz.com' }}</span>
              </div>
              <div class="flex items-center text-gray-600">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>{{ props.graduate?.location || 'Newyork, USA - 100001' }}</span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Post Input Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8 overflow-hidden">
          <div class="p-6">
            <div class="flex items-center space-x-4">
              <img
                :src="props.graduate?.profile_picture || '/images/default-avatar.png'"
                :alt="props.graduate?.first_name"
                class="w-12 h-12 rounded-full object-cover"
              />
              <div class="flex-1">
                <input
                  type="text"
                  placeholder="Share your thoughts"
                  class="w-full bg-gray-50 border border-gray-200 rounded-full px-4 py-3 text-gray-700 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
            </div>
            
            <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
              <div class="flex items-center space-x-6">
                <button class="flex items-center text-gray-600 hover:text-blue-600 transition-colors">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                  Photo / Video
                </button>
                <button class="flex items-center text-gray-600 hover:text-green-600 transition-colors">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  Article
                </button>
              </div>
              <button class="bg-blue-600 text-white px-6 py-2 rounded-full font-medium hover:bg-blue-700 transition-colors">
                Post
              </button>
            </div>
          </div>
        </div>
        
        <!-- Sample Post -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8 overflow-hidden">
          <div class="p-6">
            <div class="flex items-start space-x-4 mb-4">
              <img
                src="/images/default-avatar.png"
                alt="Macky Dawn"
                class="w-12 h-12 rounded-full object-cover"
              />
              <div class="flex-1">
                <div class="flex items-center space-x-2">
                  <h3 class="font-semibold text-gray-900">Macky Dawn</h3>
                  <span class="text-gray-500 text-sm">15 min ago</span>
                </div>
              </div>
            </div>
            
            <p class="text-gray-700 mb-4">
              Just finished an amazing project! Really excited to share the results with everyone.
            </p>
            
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
              <div class="flex items-center space-x-6">
                <button class="flex items-center text-gray-600 hover:text-blue-600 transition-colors">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                  </svg>
                  Like
                </button>
                <button class="flex items-center text-gray-600 hover:text-green-600 transition-colors">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                  </svg>
                  Comment
                </button>
                <button class="flex items-center text-gray-600 hover:text-purple-600 transition-colors">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                  </svg>
                  Share
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
        
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
