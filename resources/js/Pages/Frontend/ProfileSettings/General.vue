<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import Datepicker from 'vue3-datepicker';
import { isValid } from 'date-fns';
import '@fortawesome/fontawesome-free/css/all.css';
import './datepicker.css';


// Define props
const props = defineProps({
  activeSection: {
    type: String,
    default: 'general'
  }
});

const { props: pageProps } = usePage();
const user = ref(pageProps.user || {});

const { auth } = usePage().props;
const currentUser = computed(() => pageProps.user || auth.user);

const datepickerConfig = {
  format: 'yyyy-MM-dd',
  enableTime: false
};
const degreeCompleted = computed(() =>
  pageProps.graduate?.program?.degree?.type ||
  pageProps.graduate?.degree?.type ||
  'Not specified'
);

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

const profile = ref({
  fullName: `${pageProps.graduate?.first_name || ''} ${pageProps.graduate?.middle_name || ''} ${pageProps.graduate?.last_name || ''}`.trim(),
  first_name: pageProps.graduate?.first_name || '',
  middle_name: pageProps.graduate?.graduate_middle_name || '',
  last_name: pageProps.graduate?.last_name || '',
  dob: pageProps.graduate?.dob || '',
  graduate_picture_url: pageProps.graduate?.graduate_picture
    ? `/storage/${pageProps.graduate.graduate_picture}`
    : (pageProps.user?.profile_picture ? `/storage/${pageProps.user.profile_picture}` : 'path/to/default/image.jpg'),
  employment_status: pageProps.graduate?.employment_status || '',
  graduate_professional_title: pageProps.graduate?.current_job_title || '',
  email: pageProps.user?.email || '',
  graduate_phone: pageProps.graduate?.contact_number || '',
  graduate_location: pageProps.graduate?.graduate_location || '',
  graduate_birthdate: pageProps.graduate?.dob ? new Date(pageProps.graduate.dob) : null,
  graduate_gender: pageProps.graduate?.gender || '',
  graduate_ethnicity: pageProps.graduate?.ethnicity || '',
  graduate_address: pageProps.graduate?.address || '',
  graduate_about_me: pageProps.graduate?.about_me || '',
  // Fields from Register.vue
  graduate_school_graduated_from: pageProps.graduate?.institution?.institution_name || '',
  graduate_program_completed: pageProps.graduate?.program.name || '',
  graduate_year_graduated: pageProps.graduate?.school_year.school_year_range || '',
  // Social links and contact form settings
  linkedin_url: pageProps.graduate?.linkedin_url || '',
  github_url: pageProps.graduate?.github_url || '',
  personal_website: pageProps.graduate?.personal_website || '',
  other_social_links: pageProps.graduate?.other_social_links || '',
  enable_contact_form: pageProps.graduate?.enable_contact_form || false,
});

console.log('pageProps.user:', pageProps.graduate);



// Initialize form with profile data
const settingsForm = useForm({
  first_name: profile.value.first_name,
  middle_name: profile.value.middle_name,
  last_name: profile.value.last_name,
  graduate_professional_title: profile.value.graduate_professional_title,
  email: profile.value.email,
  graduate_phone: profile.value.graduate_phone,
  graduate_location: profile.value.graduate_location,
  dob: profile.value.dob,
  graduate_gender: profile.value.graduate_gender,
  employment_status: profile.value.employment_status || '',

  graduate_ethnicity: profile.value.graduate_ethnicity,
  graduate_address: profile.value.graduate_address,
  graduate_about_me: profile.value.graduate_about_me,
  graduate_picture_url: profile.value.graduate_picture_url,
  graduate_picture: null,
  // Fields from Register.vue
  graduate_school_graduated_from: profile.value.graduate_school_graduated_from,
  graduate_program_completed: profile.value.graduate_program_completed,
  graduate_year_graduated: profile.value.graduate_year_graduated,
  // Social links and contact form settings
  linkedin_url: profile.value.linkedin_url,
  github_url: profile.value.github_url,
  personal_website: profile.value.personal_website,
  other_social_links: profile.value.other_social_links,
  enable_contact_form: profile.value.enable_contact_form,
});
const emit = defineEmits(['close-all-modals', 'reset-all-states']);
const parseFullName = () => {
  const fullName = profile.value.fullName.trim();
  const nameParts = fullName.split(' ');

  if (nameParts.length >= 1) {
    profile.value.first_name = nameParts[0];
  }
  if (nameParts.length >= 2) {
    profile.value.last_name = nameParts[nameParts.length - 1];
  }
  if (nameParts.length > 2) {
    profile.value.middle_name = nameParts[1].charAt(0);
  }
};

watch(() => profile.value.fullName, (newFullName) => {
  const nameParts = newFullName.trim().split(' ');
  profile.value.first_name = nameParts[0] || '';
  profile.value.last_name = nameParts[nameParts.length - 1] || '';
  profile.value.middle_name = nameParts.length > 2 ? nameParts[1].charAt(0) : '';
});

watch(() => profile.value.fullName, (newFullName) => {
  const nameParts = newFullName.trim().split(' ');
  profile.value.first_name = nameParts[0] || '';
  profile.value.last_name = nameParts[nameParts.length - 1] || '';
  profile.value.middle_name = nameParts.length > 2 ? nameParts[1].charAt(0) : '';
});

watch(() => profile.value.fullName, () => {
  parseFullName();
});


const saveProfile = () => {
  // if (!validateForm()) {
  //   showErrorModal('Please correct the errors in the form.');
  //   return;
  // }

  // Format birthdate
  if (profile.value.graduate_birthdate) {
    const date = new Date(profile.value.graduate_birthdate);
    settingsForm.dob = date.toISOString().split('T')[0];
  } else {
    settingsForm.dob = null;
  }

  // Update form data from profile
  settingsForm.first_name = profile.value.first_name;
  settingsForm.middle_name = profile.value.middle_name;
  settingsForm.last_name = profile.value.last_name;
  settingsForm.email = profile.value.email;
  settingsForm.employment_status = profile.value.employment_status;
  settingsForm.contact_number = profile.value.graduate_phone;
  settingsForm.graduate_professional_title = profile.value.graduate_professional_title;
  settingsForm.graduate_location = profile.value.graduate_location;
  settingsForm.gender = profile.value.graduate_gender;
  settingsForm.graduate_ethnicity = profile.value.graduate_ethnicity;
  settingsForm.graduate_address = profile.value.graduate_address;
  settingsForm.graduate_about_me = profile.value.graduate_about_me;
  settingsForm.graduate_school_graduated_from = profile.value.graduate_school_graduated_from;
  settingsForm.graduate_program_completed = profile.value.graduate_program_completed;
  settingsForm.graduate_year_graduated = profile.value.graduate_year_graduated;
  settingsForm.linkedin_url = profile.value.linkedin_url;
  settingsForm.github_url = profile.value.github_url;
  settingsForm.personal_website = profile.value.personal_website;
  settingsForm.other_social_links = profile.value.other_social_links;
  settingsForm.enable_contact_form = profile.value.enable_contact_form;

  // Check if there are any changes
  const hasChanges = Object.keys(settingsForm.data()).some(
    (key) => settingsForm[key] !== profile.value[key]
  );

  if (!hasChanges) {
    isNoChangesModalOpen.value = true;
    return;
  }

  console.log('Submitting form:', settingsForm);

  // Submit form
  settingsForm.post(route('profile.update'), {
    forceFormData: true,
    onSuccess: (response) => {
      emit('close-all-modals');      profile.value.first_name = settingsForm.first_name;
      profile.value.middle_name = settingsForm.middle_name;
      profile.value.last_name = settingsForm.last_name;
      profile.value.graduate_professional_title = settingsForm.graduate_professional_title;
      profile.value.email = settingsForm.email;
      profile.value.graduate_phone = settingsForm.contact_number;
      profile.value.graduate_location = settingsForm.graduate_location;
      profile.value.graduate_gender = settingsForm.gender;
      profile.value.graduate_ethnicity = settingsForm.graduate_ethnicity;
      profile.value.graduate_address = settingsForm.graduate_address;
      profile.value.graduate_about_me = settingsForm.graduate_about_me;
      profile.value.graduate_school_graduated_from = settingsForm.graduate_school_graduated_from;
      profile.value.graduate_program_completed = settingsForm.graduate_program_completed;
      profile.value.graduate_year_graduated = settingsForm.graduate_year_graduated;
      profile.value.linkedin_url = settingsForm.linkedin_url;
      profile.value.github_url = settingsForm.github_url;
      profile.value.personal_website = settingsForm.personal_website;
      profile.value.other_social_links = settingsForm.other_social_links;
      profile.value.enable_contact_form = settingsForm.enable_contact_form;
      profile.value.fullName = `${profile.value.first_name} ${profile.value.middle_name} ${profile.value.last_name}`.trim();

      // Reset form
      settingsForm.reset();
      settingsForm.clearErrors();

      showSuccessModal(); // Show success modal
      console.log('Profile saved successfully on the backend:', response);
    },
    onError: (errors) => {
      console.error('Error updating profile:', errors);
      showErrorModal('An error occurred while updating the profile. Please try again.');
    },
  });
};

// Profile Picture Handler
const onFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    // Update the form with the file
    settingsForm.graduate_picture = file;
    console.log('Selected file:', file); // <-- Add this line


    // Preview the image
    const reader = new FileReader();
    reader.onload = (e) => {
      profile.value.graduate_picture_url = e.target.result;
      console.log('Profile picture updated:', file.name);
    };
    reader.readAsDataURL(file);
  }
};

// Modal states
const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const isNoChangesModalOpen = ref(false);
const errorMessage = ref('');
const modalState = ref({
  profile: false,
});

// Validation
const validateForm = () => {
  let isValid = true;
  const errors = {};

  // Validate required fields
  if (!profile.value.first_name) {
    errors.first_name = 'First name is required';
    isValid = false;
  }

  if (!profile.value.last_name) {
    errors.last_name = 'Last name is required';
    isValid = false;
  }

  if (!profile.value.email) {
    errors.email = 'Email is required';
    isValid = false;
  } else if (!/^\S+@\S+\.\S+$/.test(profile.value.email)) {
    errors.email = 'Please enter a valid email address';
    isValid = false;
  }

  // Validate phone number format if provided
  if (profile.value.graduate_phone && !/^[\d\s\(\)\-\+]+$/.test(profile.value.graduate_phone)) {
    errors.contact_number = 'Please enter a valid phone number';
    isValid = false;
  }

  // Validate professional title if required by backend
  if (!profile.value.graduate_professional_title) {
    errors.graduate_professional_title = 'Professional title is required';
    isValid = false;
  }

  // Validate location if required by backend
  if (!profile.value.graduate_location) {
    errors.graduate_location = 'Location is required';
    isValid = false;
  }

  // Validate URL formats if provided
  if (profile.value.linkedin_url && !isValidUrl(profile.value.linkedin_url)) {
    errors.linkedin_url = 'Please enter a valid LinkedIn URL';
    isValid = false;
  }

  if (profile.value.github_url && !isValidUrl(profile.value.github_url)) {
    errors.github_url = 'Please enter a valid GitHub URL';
    isValid = false;
  }

  if (profile.value.personal_website && !isValidUrl(profile.value.personal_website)) {
    errors.personal_website = 'Please enter a valid website URL';
    isValid = false;
  }

  // Set validation errors
  settingsForm.clearErrors();
  if (!isValid) {
    Object.keys(errors).forEach(key => {
      settingsForm.setError(key, errors[key]);
    });
  }

  return isValid;
};

const showSuccessModal = () => {
  isSuccessModalOpen.value = true;
  setTimeout(() => {
    isSuccessModalOpen.value = false;
  }, 3000);
};

const showErrorModal = (message) => {
  errorMessage.value = message || 'An error occurred while updating your profile.';
  isErrorModalOpen.value = true;
};

// Validate URL format
const isValidUrl = (url) => {
  try {
    new URL(url);
    return true;
  } catch (e) {
    return false;
  }
};

const initializeData = () => {
  console.log('Initializing data...');
};

// Call initialize function on component mount
onMounted(() => {
  initializeData();
}); 
</script>

<template>
  <div v-if="activeSection === 'general'" class="flex flex-col">
    <Modal :show="isSuccessModalOpen" @close="isSuccessModalOpen = false">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-green-100 rounded-full p-3">
            <i class="fas fa-check text-green-500 text-xl"></i>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Success!</h3>
        <p class="text-center text-gray-600">Your profile has been updated successfully.</p>
        <div class="mt-6 flex justify-center">
          <button type="button"
            class="bg-green-500 hover:bg-green-600 transition-colors text-white px-4 py-2 rounded-md"
            @click="isSuccessModalOpen = false">
            Close
          </button>
        </div>
      </div>
    </Modal>

    <!-- Error Modal -->
    <Modal :show="isErrorModalOpen" @close="isErrorModalOpen = false">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-red-100 rounded-full p-3">
            <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Error</h3>
        <p class="text-center text-gray-600">{{ errorMessage }}</p>
        <div class="mt-6 flex justify-center">
          <button type="button" class="bg-red-500 hover:bg-red-600 transition-colors text-white px-4 py-2 rounded-md"
            @click="isErrorModalOpen = false">
            Close
          </button>
        </div>
      </div>
    </Modal>

    <!-- No Changes Modal -->
    <Modal :show="isNoChangesModalOpen" @close="isNoChangesModalOpen = false">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-blue-100 rounded-full p-3">
            <i class="fas fa-info-circle text-blue-500 text-xl"></i>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">No Changes Detected</h3>
        <p class="text-center text-gray-600">No changes were made to your profile.</p>
        <div class="mt-6 flex justify-center">
          <button type="button" class="bg-blue-500 hover:bg-blue-600 transition-colors text-white px-4 py-2 rounded-md"
            @click="isNoChangesModalOpen = false">
            Close
          </button>
        </div>
      </div>
    </Modal>

    <div class="flex flex-col lg:flex-row lg:space-x-8">
      <!-- Profile Picture Section -->
      <div class="w-full lg:w-1/3 mb-8 lg:mb-0">
        <div class="bg-white p-6 rounded-lg border border-gray-200 mb-6">
          <h2 class="text-xl font-semibold mb-2">Profile Picture</h2>
          <p class="text-gray-600 mb-4">Update your profile picture</p>
          <div class="flex flex-col items-center">
            <div class="relative mb-4">
              <img id="profile-picture" :src="profile.graduate_picture_url" alt="Profile picture"
                class="rounded-full w-48 h-48 object-cover border border-gray-200 shadow-sm" />
              <div class="absolute bottom-0 right-0">
                <label for="file-input"
                  class="bg-indigo-600 hover:bg-indigo-700 transition-colors text-white rounded-full p-2 cursor-pointer shadow-md">
                  <i class="fas fa-camera"></i>
                </label>
              </div>
            </div>
            <input type="file" id="file-input" class="hidden" accept="image/*" @change="onFileChange" />
            <label for="file-input"
              class="text-indigo-600 hover:text-indigo-800 transition-colors font-medium cursor-pointer">
              Choose an image
            </label>
          </div>
        </div>

        <!-- Contact and Social Links Section -->
        <div class="bg-white p-6 rounded-lg border border-gray-200">
          <h2 class="text-xl font-semibold mb-2">Contact and Social Links</h2>
          <p class="text-gray-600 mb-4">Add your professional networks and contact options</p>

          <div class="space-y-4">
            <!-- LinkedIn URL -->
            <div class="relative">
              <label for="linkedin-url" class="block text-gray-700 font-medium mb-1">LinkedIn Profile</label>
              <div class="relative">
                <i class="fab fa-linkedin absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="url" id="linkedin-url"
                  class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                  :class="{ 'border-red-500 focus:ring-red-500': settingsForm.errors.linkedin_url }"
                  v-model="profile.linkedin_url" placeholder="https://linkedin.com/in/yourprofile" />
              </div>
              <div v-if="settingsForm.errors.linkedin_url" class="text-red-500 text-sm mt-1">
                {{ settingsForm.errors.linkedin_url }}
              </div>
            </div>

            <!-- GitHub URL -->
            <div class="relative">
              <label for="github-url" class="block text-gray-700 font-medium mb-1">GitHub Profile</label>
              <div class="relative">
                <i class="fab fa-github absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="url" id="github-url"
                  class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                  :class="{ 'border-red-500 focus:ring-red-500': settingsForm.errors.github_url }"
                  v-model="profile.github_url" placeholder="https://github.com/yourusername" />
              </div>
              <div v-if="settingsForm.errors.github_url" class="text-red-500 text-sm mt-1">
                {{ settingsForm.errors.github_url }}
              </div>
            </div>

            <!-- Personal Website -->
            <div class="relative">
              <label for="personal-website" class="block text-gray-700 font-medium mb-1">Personal Website</label>
              <div class="relative">
                <i class="fas fa-globe absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="url" id="personal-website"
                  class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                  :class="{ 'border-red-500 focus:ring-red-500': settingsForm.errors.personal_website }"
                  v-model="profile.personal_website" placeholder="https://yourwebsite.com" />
              </div>
              <div v-if="settingsForm.errors.personal_website" class="text-red-500 text-sm mt-1">
                {{ settingsForm.errors.personal_website }}
              </div>
            </div>

            <!-- Other Professional Networks -->
            <div class="relative">
              <label for="other-social-links" class="block text-gray-700 font-medium mb-1">Other Professional
                Networks</label>
              <div class="relative">
                <i class="fas fa-share-alt absolute left-3 top-3 text-gray-400"></i>
                <textarea id="other-social-links"
                  class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                  rows="2" v-model="profile.other_social_links"
                  placeholder="Add other professional networks (one per line)"></textarea>
              </div>
            </div>

            <!-- Contact Form Toggle -->
            <div class="relative">
              <label class="flex items-center">
                <input type="checkbox" v-model="profile.enable_contact_form"
                  class="form-checkbox h-5 w-5 text-indigo-600 transition duration-150 ease-in-out" />
                <span class="ml-2 text-gray-700">Enable contact form for networking opportunities</span>
              </label>
              <p class="text-gray-500 text-sm mt-1 ml-7">Allow other users to contact you through your profile</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Profile Information Section -->
      <div class="w-full lg:w-2/3">
        <form @submit.prevent="saveProfile" class="space-y-6">
          <!-- Personal Information Section -->
          <div class="bg-white p-6 rounded-lg border border-gray-200">
            <h2 class="text-xl font-semibold mb-2">Personal Information</h2>
            <p class="text-gray-600 mb-4">Basic details about you</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
              <!-- Full Name -->
              <div class="relative">
                <label for="full-name" class="block text-gray-700 font-medium mb-1">Full Name <span
                    class="text-red-500">*</span></label>
                <div class="relative">
                  <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <input type="text" id="full-name"
                    class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                    :class="{ 'border-red-500 focus:ring-red-500': settingsForm.errors.first_name || settingsForm.errors.last_name }"
                    v-model="profile.fullName" placeholder="Enter your full name" />
                </div>
                <div v-if="settingsForm.errors.first_name" class="text-red-500 text-sm mt-1">
                  {{ settingsForm.errors.first_name }}
                </div>
                <div v-if="settingsForm.errors.last_name" class="text-red-500 text-sm mt-1">
                  {{ settingsForm.errors.last_name }}
                </div>
              </div>

              <!-- Professional Title -->
              <div class="relative">
                <label for="professional-title" class="block text-gray-700 font-medium mb-1">Professional Title</label>
                <div class="relative">
                  <i class="fas fa-briefcase absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <input type="text" id="graduate_professional-title"
                    class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                    v-model="profile.graduate_professional_title" placeholder="Enter your professional title" />
                </div>
              </div>

              <!-- Gender -->
              <div class="relative">
                <label for="gender" class="block text-gray-700 font-medium mb-1">Gender</label>
                <div class="relative">
                  <i class="fas fa-venus-mars absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <select id="gender"
                    class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all appearance-none bg-white"
                    :class="{ 'border-red-500 focus:ring-red-500': settingsForm.errors.gender }"
                    v-model="profile.graduate_gender">
                    <option value="" disabled selected>Select gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Non-binary">Non-binary</option>
                    <option value="Prefer not to say">Prefer not to say</option>
                  </select>
                </div>
                <div v-if="settingsForm.errors.gender" class="text-red-500 text-sm mt-1">
                  {{ settingsForm.errors.gender }}
                </div>
              </div>

              <!-- Ethnicity -->
              <div class="relative">
                <label for="ethnicity" class="block text-gray-700 font-medium mb-1">Ethnicity</label>
                <div class="relative">
                  <i class="fas fa-users absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <input type="text" id="ethnicity"
                    class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                    v-model="profile.graduate_ethnicity" placeholder="Enter your ethnicity" />
                </div>
              </div>

              <!-- Birthdate -->
              <div class="relative">
                <label for="birthdate" class="block text-gray-700 font-medium mb-1">Birthdate</label>
                <div class="relative">
                  <i class="fas fa-calendar-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 z-10"></i>
                  <Datepicker v-model="profile.graduate_birthdate" :format="datepickerConfig.format"
                    :enable-time-picker="datepickerConfig.enableTime"
                    input-class-name="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                    placeholder="Select your birthdate" :input-attributes="{ style: 'box-shadow: none !important;' }"
                    class="datepicker-no-shadow" />
                </div>
              </div>

              <!-- Location -->
              <div class="relative">
                <label for="address" class="block text-gray-700 font-medium mb-1">Address</label>
                <div class="relative">
                  <i class="fas fa-map-marker-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <input type="text" id="address"
                    class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                    v-model="profile.graduate_address" placeholder="City, Country" />
                </div>
              </div>
            </div>
          </div>

          <!-- Education Information Section -->
          <div class="bg-white p-6 rounded-lg border border-gray-200">
            <h2 class="text-xl font-semibold mb-2">Education Information</h2>
            <p class="text-gray-600 mb-4">Your academic background</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
              <!-- School Graduated From -->
              <div class="relative">
                <label class="block text-gray-700 font-medium mb-1">School Graduated From</label>
                <div class="relative">
                  <i class="fas fa-university absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <div class="w-full border border-gray-300 rounded-md p-2 pl-10 bg-gray-50 text-gray-700">
                    {{ profile.graduate_school_graduated_from || 'Not specified' }}
                  </div>
                </div>
              </div>

              <!-- Year Graduated -->
              <div class="relative">
                <label class="block text-gray-700 font-medium mb-1">Year Graduated</label>
                <div class="relative">
                  <i class="fas fa-calendar-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <div class="w-full border border-gray-300 rounded-md p-2 pl-10 bg-gray-50 text-gray-700">
                    {{ profile.graduate_year_graduated || 'Not specified' }}
                  </div>
                </div>
              </div>

              <!-- Program Completed -->
              <div class="relative">
                <label class="block text-gray-700 font-medium mb-1">Program Completed</label>
                <div class="relative">
                  <i class="fas fa-graduation-cap absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <div class="w-full border border-gray-300 rounded-md p-2 pl-10 bg-gray-50 text-gray-700">
                    {{ profile.graduate_program_completed || 'Not specified' }}
                  </div>
                </div>
              </div>

              <!-- Degree Completed -->
              <div class="relative">
                <label class="block text-gray-700 font-medium mb-1">Degree Completed</label>
                <div class="relative">
                  <i class="fas fa-graduation-cap absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <div class="w-full border border-gray-300 rounded-md p-2 pl-10 bg-gray-50 text-gray-700">
                    {{ degreeCompleted }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Contact Information Section -->
          <div class="bg-white p-6 rounded-lg border border-gray-200">
            <h2 class="text-xl font-semibold mb-2">Contact Information</h2>
            <p class="text-gray-600 mb-4">How others can reach you</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
              <!-- Email Address -->
              <div class="relative">
                <label for="email-address" class="block text-gray-700 font-medium mb-1">Email Address <span
                    class="text-red-500">*</span></label>
                <div class="relative">
                  <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <input type="email" id="email-address"
                    class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                    :class="{ 'border-red-500 focus:ring-red-500': settingsForm.errors.email }" v-model="profile.email"
                    placeholder="Enter your email address" />
                </div>
                <div v-if="settingsForm.errors.email" class="text-red-500 text-sm mt-1">
                  {{ settingsForm.errors.email }}
                </div>
              </div>

              <!-- Phone Number -->
              <div class="relative">
                <label for="phone" class="block text-gray-700 font-medium mb-1">Phone Number <span
                    class="text-red-500">*</span></label>
                <div class="relative">
                  <i class="fas fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <input type="text" id="phone"
                    class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                    :class="{ 'border-red-500 focus:ring-red-500': settingsForm.errors.contact_number }"
                    v-model="profile.graduate_phone" placeholder="Enter your phone number" />
                </div>
                <div v-if="settingsForm.errors.contact_number" class="text-red-500 text-sm mt-1">
                  {{ settingsForm.errors.contact_number }}
                </div>
              </div>
            </div>
          </div>

          <!-- About Me Section -->
          <div class="bg-white p-6 rounded-lg border border-gray-200">
            <h2 class="text-xl font-semibold mb-2">About Me</h2>
            <p class="text-gray-600 mb-4">Tell others about yourself</p>
            <div class="relative">
              <div class="relative">
                <i class="fas fa-user-circle absolute left-3 top-3 text-gray-400"></i>
                <textarea id="about-me"
                  class="w-full border border-gray-300 rounded-md p-2 pl-10 outline-none focus:ring-1 focus:ring-indigo-600 transition-all"
                  rows="4" v-model="profile.graduate_about_me" placeholder="Tell us about yourself"
                  maxlength="1000"></textarea>
                <div class="text-xs text-gray-500 mt-1 text-right">
                  {{ profile.graduate_about_me ? profile.graduate_about_me.length : 0 }}/1000
                </div>
              </div>
            </div>

          </div>

          <!-- Form Actions -->
          <div class="flex justify-end mt-6">
            <button type="submit"
              class="bg-indigo-600 hover:bg-indigo-700 transition-colors text-white py-2 px-6 rounded-md shadow-sm flex items-center"
              :disabled="settingsForm.processing">
              <span v-if="settingsForm.processing" class="mr-2">
                <i class="fas fa-spinner fa-spin"></i>
              </span>
              <i v-else class="fas fa-save mr-2"></i>
              {{ settingsForm.processing ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>