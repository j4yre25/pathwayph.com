<script setup>
import Graduate from '@/Layouts/AppLayout.vue';
import { ref, computed, onMounted, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import TextArea from '@/Components/TextArea.vue';
import SelectInput from '@/Components/SelectInput.vue';
import axios from 'axios';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Datepicker from 'vue3-datepicker';
import { isValid } from 'date-fns';
import VueApexCharts from 'vue3-apexcharts';
import SkillsChart from '@/Components/SkillsChart.vue';
import General from './ProfileSettings/General.vue';
import Security from './ProfileSettings/Security.vue';
import Education from './ProfileSettings/Education.vue';
import Skill from './ProfileSettings/Skill.vue';
import GraduateAccomplishments from './ProfileSettings/GraduateAccomplishments.vue';
import Employment from './ProfileSettings/Employment.vue';
import CareerGoals from './ProfileSettings/CareerGoals.vue';
import Resume from './ProfileSettings/Resume.vue';
import Internship from './ProfileSettings/Internship.vue';

const activeSection = ref(localStorage.getItem('activeSection') || 'general'); // Initialize from localStorage or default to 'general'
const setActiveSection = (section) => {
  activeSection.value = section;
  // Store in localStorage for persistence across page refreshes
  localStorage.setItem('activeSection', section);
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

// Additional cleanup for resetting states
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

// Function to initialize data on component mount


// Call initialize function on component mount
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
      showSuccessModal(); // Show success modal

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

const initializeData = () => {
  // Fetch initial data or set defaults
  console.log('Initializing data...');
};

// Call initialize function on component mount
onMounted(() => {
  initializeData();
});

</script>

<template>
  <Graduate>
    <div class="bg-gray-100 min-h-screen p-6">
      <div class="container mx-auto">
        <h1 class="text-3xl font-bold mb-4">Profile Settings</h1>
        <p class="text-gray-600 mb-6">Manage your personal information and account settings</p>
        <div class="bg-white rounded-lg shadow-md p-6">
          <!-- Tab Navigation -->
          <div class="flex flex-wrap border-b border-gray-200 mb-6 overflow-x-auto">
            <button class="py-2 px-4 whitespace-nowrap"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'general' }"
              @click="setActiveSection('general')">
              General
            </button>
            <button class="py-2 px-4 whitespace-nowrap"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'security' }"
              @click="setActiveSection('security')">
              Security
            </button>
            <button class="py-2 px-4 whitespace-nowrap"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'education' }"
              @click="setActiveSection('education')">
              Education
            </button>
            <!-- Combined Skills and Experiences tab -->
            <button class="py-2 px-4 whitespace-nowrap"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'skills-experience' }"
              @click="setActiveSection('skills-experience')">
              Skills and Experiences
            </button>
            <!-- Graduate Accomplishments tab button (after Skills and Experiences) -->
            <button class="py-2 px-4 whitespace-nowrap"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'graduate-accomplishments' }"
              @click="setActiveSection('graduate-accomplishments')">
              Accomplishments
            </button>
            <button class="py-2 px-4 whitespace-nowrap"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'employment' }"
              @click="setActiveSection('employment')">
              Employment
            </button>
            <button class="py-2 px-4 whitespace-nowrap"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'career-goals' }"
              @click="setActiveSection('career-goals')">
              Career Goals
            </button>
            <button class="py-2 px-4 whitespace-nowrap"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'resume' }"
              @click="setActiveSection('resume')">
              Resume
            </button>
            <!-- Add this button to your tab navigation -->
            <button class="py-2 px-4 whitespace-nowrap"
              :class="{ 'text-indigo-600 border-b-2 border-indigo-600': activeSection === 'internship' }"
              @click="setActiveSection('internship')">
              Internship
            </button>
          </div>


          <!-- Tab Content -->
          <div class="mt-6">
            <!-- General Settings -->
            <General :activeSection="activeSection" :educationEntries="props.educationEntries" @close-all-modals="closeAllModals"
              @reset-all-states="resetAllStates" />

            <!-- Security Settings -->
            <Security :activeSection="activeSection" @close-all-modals="closeAllModals"
              @reset-all-states="resetAllStates" />

            <!-- Education Settings -->
            <Education :activeSection="activeSection" :educationEntries="props.educationEntries"
              :archivedEducationEntries="props.archivedEducationEntries" :institutions="props.institutions"
              :graduate="props.graduate"
              @close-all-modals="closeAllModals" @reset-all-states="resetAllStates"
              @refresh-education="refreshEducation" />

            <!-- Skills and Experience Settings (combined) -->
            <Skill
              v-if="activeSection === 'skills-experience'"
              :activeSection="activeSection"
              :skillEntries="props.skillEntries"
              :archivedSkillEntries="props.archivedSkillEntries"
              :experienceEntries="props.experienceEntries"
              :archivedExperienceEntries="props.archivedExperienceEntries"
              @close-all-modals="closeAllModals"
              @reset-all-states="resetAllStates"
              @refresh-skills="refreshSkills"
            />

            <!-- Graduate Accomplishments Settings -->
            <GraduateAccomplishments
              v-if="activeSection === 'graduate-accomplishments'"
              :activeSection="activeSection"
              :projectsEntries="props.projectsEntries"
              :archivedProjectsEntries="props.archivedProjectsEntries"
              :certificationsEntries="props.certificationsEntries"
              :archivedCertificationsEntries="props.archivedCertificationsEntries"
              :achievementEntries="props.achievementEntries"
              :archivedAchievementEntries="props.archivedAchievementEntries"
              :testimonialEntries="props.testimonialsEntries"
              :archivedTestimonialEntries="props.archivedTestimonialsEntries"
              @close-all-modals="closeAllModals"
              @reset-all-states="resetAllStates"
            />

            <!-- Employment Settings -->
            <Employment :activeSection="activeSection" :employmentPreferences="props.employmentPreferences"
              @close-all-modals="closeAllModals" @reset-all-states="resetAllStates" />

            <!-- Career Goals Settings -->
            <CareerGoals :activeSection="activeSection" :careerGoals="props.careerGoals"
              @close-all-modals="closeAllModals" @reset-all-states="resetAllStates" />

            <!-- Resume Settings -->
            <Resume :activeSection="activeSection" :resume="props.resume" @close-all-modals="closeAllModals"
              @reset-all-states="resetAllStates" />

            <!-- Internship Settings -->
            <Internship
              v-if="activeSection === 'internship'"
              :internships="props.internships"
              :programs="props.programs"
              :careerOpportunities="props.careerOpportunities"
            />
          </div>
        </div>
      </div>
    </div>
  </Graduate>
</template>
