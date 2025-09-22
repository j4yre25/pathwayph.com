<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm, router } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";
import '@fortawesome/fontawesome-free/css/all.css';
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
  peso: Object,
});

const logoForm = useForm({
  logo: null,
});
// Form data
const form = useForm({
  peso_first_name: props.peso.peso_first_name || '',
  peso_last_name: props.peso.peso_last_name || '',
  email: props.peso.email || '',
  contact_number: props.peso.contact_number || '',
  description: props.peso.description || '',
  address: props.peso.address || '',
  social_links: {
    facebook: props.peso.social_links?.facebook || '',
    twitter: props.peso.social_links?.twitter || '',
    linkedin: props.peso.social_links?.linkedin || '',
    instagram: props.peso.social_links?.instagram || '',
  },
});

// Modal states
const showSaveModal = ref(false);
const showUpdateModal = ref(false);
const showErrorModal = ref(false);
const showDuplicateModal = ref(false);
const showArchiveModal = ref(false);

// Logo preview
const logoPreview = ref(props.peso.logo ? `/storage/${props.peso.logo}` : null);

// Handle logo file selection
const handleLogoChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    logoForm.logo = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      logoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

// Submit form
const submitForm = () => {
  form.put(route('peso.profile.update'), {
    onSuccess: () => {
      showUpdateModal.value = true;
    },
    onError: () => {
      showErrorModal.value = true;
    },
  });
};

const submitLogo = () => {

  if (!logoForm.logo) {
    // Show a message or prevent submission
    alert('Please select an image to upload.');
    return;
  }
  logoForm.post(route('peso.profile.updateLogo'), {
    onSuccess: () => {
      showUpdateModal.value = true;
    },
    onError: () => {
      showErrorModal.value = true;
    },
    preserveScroll: true,
  });
};

// Reset form
const resetForm = () => {
  form.reset();
  logoPreview.value = props.peso.logo ? `/storage/${props.peso.logo}` : null;
};

// Password Management
const passwordInput = ref(null);
const currentPasswordInput = ref(null);

// Create form for password update
const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: ''
});

// Password validation criteria refs for real-time feedback
const minLengthMet = ref(false);
const hasUppercaseMet = ref(false);
const hasLowercaseMet = ref(false);
const hasNumberMet = ref(false);
const hasSpecialCharMet = ref(false);

// Password validation
const validatePassword = (password) => {
  if (!password) return { valid: false, message: 'Password is required' };
  
  const minLength = 8;
  const hasUpperCase = /[A-Z]/.test(password);
  const hasLowerCase = /[a-z]/.test(password);
  const hasNumber = /[0-9]/.test(password);
  const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
  
  // Update reactive refs for real-time feedback
  minLengthMet.value = password.length >= minLength;
  hasUppercaseMet.value = hasUpperCase;
  hasLowercaseMet.value = hasLowerCase;
  hasNumberMet.value = hasNumber;
  hasSpecialCharMet.value = hasSpecialChar;
  
  if (password.length < minLength) {
    return { valid: false, message: 'Password must be at least 8 characters long' };
  }
  if (!hasUpperCase) {
    return { valid: false, message: 'Password must contain at least one uppercase letter' };
  }
  if (!hasLowerCase) {
    return { valid: false, message: 'Password must contain at least one lowercase letter' };
  }
  if (!hasNumber) {
    return { valid: false, message: 'Password must contain at least one number' };
  }
  if (!hasSpecialChar) {
    return { valid: false, message: 'Password must contain at least one special character' };
  }
  
  return { valid: true, message: '' };
};

// Watch for password changes to update validation in real-time
watch(() => passwordForm.password, (newPassword) => {
  if (newPassword) {
    validatePassword(newPassword);
  } else {
    // Reset all validation states when password is empty
    minLengthMet.value = false;
    hasUppercaseMet.value = false;
    hasLowercaseMet.value = false;
    hasNumberMet.value = false;
    hasSpecialCharMet.value = false;
  }
});

// Password strength indicator
const passwordStrength = computed(() => {
  if (!passwordForm.password) return 0;
  
  let strength = 0;
  if (minLengthMet.value) strength++;
  if (hasUppercaseMet.value) strength++;
  if (hasLowercaseMet.value) strength++;
  if (hasNumberMet.value) strength++;
  if (hasSpecialCharMet.value) strength++;
  
  return strength;
});

// Password strength text and color
const strengthText = computed(() => {
  const strength = passwordStrength.value;
  if (strength === 0) return { text: '', color: 'bg-gray-200' };
  if (strength === 1) return { text: 'Very Weak', color: 'bg-red-500' };
  if (strength === 2) return { text: 'Weak', color: 'bg-orange-500' };
  if (strength === 3) return { text: 'Medium', color: 'bg-yellow-500' };
  if (strength === 4) return { text: 'Strong', color: 'bg-blue-500' };
  return { text: 'Very Strong', color: 'bg-emerald-500' };
});

// Password match validation
const passwordsMatch = computed(() => {
  if (!passwordForm.password || !passwordForm.password_confirmation) return true;
  return passwordForm.password === passwordForm.password_confirmation;
});

// Form validation computed property
const isPasswordFormValid = computed(() => {
  return passwordForm.current_password && 
         passwordForm.password && 
         passwordForm.password_confirmation &&
         passwordsMatch.value &&
         minLengthMet.value &&
         hasUppercaseMet.value &&
         hasLowercaseMet.value &&
         hasNumberMet.value &&
         hasSpecialCharMet.value;
});

// Reset password form
const resetPasswordForm = () => {
  passwordForm.reset();
  minLengthMet.value = false;
  hasUppercaseMet.value = false;
  hasLowercaseMet.value = false;
  hasNumberMet.value = false;
  hasSpecialCharMet.value = false;
};

// Form validation for password
const validatePasswordForm = () => {
  let isValid = true;
  
  // Validate current password
  if (!passwordForm.current_password) {
    passwordForm.setError('current_password', 'Current password is required');
    isValid = false;
  }
  
  // Validate new password
  const passwordValidation = validatePassword(passwordForm.password);
  if (!passwordValidation.valid) {
    passwordForm.setError('password', passwordValidation.message);
    isValid = false;
  }
  
  // Validate password confirmation
  if (!passwordForm.password_confirmation) {
    passwordForm.setError('password_confirmation', 'Please confirm your new password');
    isValid = false;
  } else if (!passwordsMatch.value) {
    passwordForm.setError('password_confirmation', 'Passwords do not match');
    isValid = false;
  }
  
  return isValid;
};


const passwordErrorMessage = ref('');

// Password modal states
const showPasswordSuccessModal = ref(false);
const showPasswordErrorModal = ref(false);

// Update password function
const updatePassword = () => {
  // Clear previous errors
  passwordForm.clearErrors();
  
  // Validate form
  if (!validatePasswordForm()) {
    return;
  }
  
  passwordForm.put(route('user-password.update'), {
    preserveScroll: true,
    onSuccess: () => {
      showPasswordSuccessModal.value = true;
      resetPasswordForm();
      // Auto logout after 3 seconds for security
      setTimeout(() => {
        router.post(route('logout'));
      }, 3000);
    },
    onError: (errors) => {
      console.error('Password update errors:', errors);
      passwordErrorMessage.value = errors.current_password || errors.password || 'Failed to update password. Please try again.';
      showPasswordErrorModal.value = true;
    },
  });
};

// Password modal functions
const showPasswordSuccessModalFunc = () => {
  showPasswordSuccessModal.value = true;
};

const showPasswordErrorModalFunc = (message) => {
  passwordErrorMessage.value = message;
  showPasswordErrorModal.value = true;
};

const closePasswordSuccessModal = () => {
  showPasswordSuccessModal.value = false;
};

const closePasswordErrorModal = () => {
  showPasswordErrorModal.value = false;
  passwordErrorMessage.value = '';
};
</script>

<template>
  <AppLayout title="PESO Profile Settings">
    <!-- Header Section with Blue Background -->
    <template #header>
      <div class="flex items-center">
        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
          <i class="fas fa-user text-blue-500 text-xl mr-2"></i> Peso Profile Settings
        </h2>
      </div>
    </template>

    <!-- Main Content -->
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-9 mb-9 py-8">
      <div class="flex flex-col lg:flex-row lg:space-x-8">
        <!-- Left Column - Profile Picture and Social Links -->
        <div class="w-full lg:w-1/3 mb-8 lg:mb-0">
          <!-- Profile Picture Section -->
          <div class="bg-white p-6 rounded-lg border border-gray-200 mb-6">
            <h2 class="text-xl font-semibold mb-2">Profile Picture</h2>
            <p class="text-gray-600 mb-4">Update your profile picture</p>
            <form @submit.prevent="submitLogo">
              <div class="flex flex-col items-center">
                <div class="relative mb-4">
                  <img :src="logoPreview || '/images/default-logo.png'" alt="PESO Logo"
                    class="rounded-full w-48 h-48 object-cover border border-gray-200 shadow-sm" />
                  <div class="absolute bottom-0 right-0">
                    <label for="file-input"
                      class="bg-blue-600 hover:bg-blue-700 transition-colors text-white rounded-full p-2 cursor-pointer shadow-md">
                      <i class="fas fa-camera"></i>
                    </label>
                  </div>
                </div>
                <input ref="logoInput" id="file-input" type="file" accept="image/*" @change="handleLogoChange"
                  class="hidden" />
                <label for="file-input"
                  class="text-blue-600 hover:text-blue-800 transition-colors font-medium cursor-pointer">
                  Choose an image
                </label>
              </div>
              <InputError :message="logoForm.errors.logo" class="mt-2" />
            </form>
          </div>

          <!-- Change Password Section -->
          <div class="bg-white p-6 rounded-lg border border-gray-200">
            <div class="flex items-center mb-4">
              <i class="fas fa-shield-alt text-blue-600 mr-2"></i>
              <h2 class="text-xl font-semibold text-gray-900">Change Password</h2>
            </div>
            <p class="text-gray-600 mb-6">Update your password to maintain account security</p>
            
            <form @submit.prevent="updatePassword">
              <div class="mb-5">
                <label class="block text-gray-700 text-sm font-medium mb-2" for="current-password">Current Password</label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fas fa-lock text-sm"></i>
                  </span>
                  <input 
                    type="password" 
                    id="current-password" 
                    ref="currentPasswordInput"
                    v-model="passwordForm.current_password"
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                    :class="{'border-rose-500 ring-1 ring-rose-500': passwordForm.errors.current_password}"
                    placeholder="Enter your current password" />
                </div>
                <div v-if="passwordForm.errors.current_password" class="text-rose-500 text-xs mt-1.5 flex items-center">
                  <i class="fas fa-exclamation-circle mr-1"></i>
                  {{ passwordForm.errors.current_password }}
                </div>
              </div>

              <div class="mb-5">
                <label class="block text-gray-700 text-sm font-medium mb-2" for="new-password">New Password</label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fas fa-key text-sm"></i>
                  </span>
                  <input 
                    type="password" 
                    id="new-password" 
                    ref="passwordInput"
                    v-model="passwordForm.password"
                    class="w-full pl-10 pr-10 py-2.5 border rounded-lg focus:outline-none focus:ring-2 transition-all duration-200"
                    :class="{
                      'border-rose-500 ring-1 ring-rose-500': passwordForm.errors.password,
                      'border-emerald-500 ring-1 ring-emerald-500': passwordStrength === 5,
                      'border-blue-500 ring-1 ring-blue-500': passwordStrength === 4,
                      'border-yellow-500 ring-1 ring-yellow-500': passwordStrength === 3,
                      'border-orange-500 ring-1 ring-orange-500': passwordStrength === 2,
                      'border-red-500 ring-1 ring-red-500': passwordStrength === 1,
                      'border-gray-200 focus:ring-blue-500 focus:border-blue-500': !passwordForm.password
                    }"
                    placeholder="Enter your new password" />
                  <!-- Password strength indicator icon -->
                  <span v-if="passwordForm.password" 
                        class="absolute inset-y-0 right-0 flex items-center pr-3 transition-all duration-300">
                    <i class="fas" :class="{
                      'fa-shield-alt text-emerald-500': passwordStrength === 5,
                      'fa-shield-alt text-blue-500': passwordStrength === 4,
                      'fa-shield-alt text-yellow-500': passwordStrength === 3,
                      'fa-shield-alt text-orange-500': passwordStrength === 2,
                      'fa-shield-alt text-red-500': passwordStrength === 1
                    }"></i>
                  </span>
                </div>
                <div v-if="passwordForm.errors.password" class="text-rose-500 text-xs mt-1.5 flex items-center">
                  <i class="fas fa-exclamation-circle mr-1"></i>
                  {{ passwordForm.errors.password }}
                </div>
                
                <!-- Password strength indicator -->
                <div v-if="passwordForm.password" class="mt-3 mb-2">
                  <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium" :class="{
                      'text-red-500': passwordStrength === 1,
                      'text-orange-500': passwordStrength === 2,
                      'text-yellow-500': passwordStrength === 3,
                      'text-blue-500': passwordStrength === 4,
                      'text-emerald-500': passwordStrength === 5
                    }">{{ strengthText.text }}</span>
                    <span class="text-xs text-gray-500">{{ passwordStrength }}/5</span>
                  </div>
                  <div class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full transition-all duration-500 ease-out" 
                         :class="strengthText.color"
                         :style="{ width: (passwordStrength * 20) + '%' }"></div>
                  </div>
                </div>
                
                <!-- Password requirements -->
                <div 
                  v-if="passwordForm.password"
                  class="mt-3 p-3 bg-gray-50 rounded-md border border-gray-100 transition-all duration-300"
                  :class="{'bg-blue-50 border-blue-100': passwordForm.password}">
                  <p class="text-xs text-gray-500 mb-2 font-medium">Password Requirements:</p>
                  <ul class="text-xs text-gray-500 space-y-1 pl-1">
                    <li class="flex items-center transition-all duration-300" :class="{'text-blue-600 font-medium': minLengthMet}">
                      <i class="fas mr-1.5 text-xs transition-all duration-300" 
                         :class="minLengthMet ? 'fa-check-circle text-blue-600' : 'fa-circle text-gray-400'"></i>
                      At least 8 characters
                    </li>
                    <li class="flex items-center transition-all duration-300" :class="{'text-blue-600 font-medium': hasUppercaseMet}">
                      <i class="fas mr-1.5 text-xs transition-all duration-300" 
                         :class="hasUppercaseMet ? 'fa-check-circle text-blue-600' : 'fa-circle text-gray-400'"></i>
                      One uppercase letter
                    </li>
                    <li class="flex items-center transition-all duration-300" :class="{'text-blue-600 font-medium': hasLowercaseMet}">
                      <i class="fas mr-1.5 text-xs transition-all duration-300" 
                         :class="hasLowercaseMet ? 'fa-check-circle text-blue-600' : 'fa-circle text-gray-400'"></i>
                      One lowercase letter
                    </li>
                    <li class="flex items-center transition-all duration-300" :class="{'text-blue-600 font-medium': hasNumberMet}">
                      <i class="fas mr-1.5 text-xs transition-all duration-300" 
                         :class="hasNumberMet ? 'fa-check-circle text-blue-600' : 'fa-circle text-gray-400'"></i>
                      One number
                    </li>
                    <li class="flex items-center transition-all duration-300" :class="{'text-blue-600 font-medium': hasSpecialCharMet}">
                      <i class="fas mr-1.5 text-xs transition-all duration-300" 
                         :class="hasSpecialCharMet ? 'fa-check-circle text-blue-600' : 'fa-circle text-gray-400'"></i>
                      One special character
                    </li>
                  </ul>
                </div>
              </div>

              <div class="mb-5">
                <label class="block text-gray-700 text-sm font-medium mb-2" for="confirm-password">Confirm New Password</label>
                <div class="relative">
                  <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fas fa-check-double text-sm"></i>
                  </span>
                  <input 
                    type="password" 
                    id="confirm-password" 
                    ref="confirmPasswordInput"
                    v-model="passwordForm.password_confirmation"
                    class="w-full pl-10 pr-10 py-2.5 border rounded-lg focus:outline-none focus:ring-2 transition-all duration-200"
                    :class="{
                      'border-rose-500 ring-1 ring-rose-500': passwordForm.password_confirmation && !passwordsMatch,
                      'border-emerald-500 ring-1 ring-emerald-500': passwordForm.password_confirmation && passwordsMatch,
                      'border-gray-200 focus:ring-blue-500 focus:border-blue-500': !passwordForm.password_confirmation
                    }"
                    placeholder="Confirm your new password" />
                  <span v-if="passwordForm.password_confirmation" 
                        class="absolute inset-y-0 right-0 flex items-center pr-3 transition-all duration-300">
                    <i class="fas" :class="{
                      'fa-check-circle text-emerald-500': passwordsMatch,
                      'fa-times-circle text-rose-500': !passwordsMatch
                    }"></i>
                  </span>
                </div>
                <div v-if="passwordForm.password_confirmation && !passwordsMatch" class="text-rose-500 text-xs mt-1.5 flex items-center">
                  <i class="fas fa-exclamation-circle mr-1"></i>
                  Passwords do not match
                </div>
                <div v-if="passwordForm.password_confirmation && passwordsMatch" class="text-emerald-500 text-xs mt-1.5 flex items-center">
                  <i class="fas fa-check-circle mr-1"></i>
                  Passwords match perfectly
                </div>
              </div>

              
                <button 
                  type="submit"
                  class="w-full bg-blue-600 text-white py-2.5 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200 shadow-sm hover:shadow-md flex items-center justify-center"
                  :disabled="passwordForm.processing">
                  <i class="fas fa-shield-alt mr-2"></i> 
                  <span v-if="passwordForm.processing" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Updating Password...
                  </span>
                  <span v-else>Change Password</span>
                </button>
            </form>
          </div>
        </div>

        <!-- Right Column - Form Fields -->
        <div class="w-full lg:w-2/3 flex flex-col space-y-6">
          <!-- Personal Information Section -->
          <div class="bg-white p-6 rounded-lg border border-gray-200">
            <form @submit.prevent="submitForm">
              <div>
                <div class="flex items-center mb-4">
                  <i class="fas fa-user text-blue-600 mr-2"></i>
                  <h2 class="text-xl font-semibold text-gray-900">Personal Information</h2>
                </div>
                <p class="text-gray-600 mb-6">Basic details about you</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- First Name -->
                  <div>
                    <label for="peso_first_name" class="block text-gray-700 font-medium mb-1">First Name <span
                        class="text-red-500">*</span></label>
                    <div class="relative">
                      <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                      <TextInput id="peso_first_name" v-model="form.peso_first_name" type="text"
                        class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                        placeholder="Enter your first name" required />
                    </div>
                    <InputError :message="form.errors.peso_first_name" class="mt-1" />
                  </div>
                  <!-- Last Name -->
                  <div>
                    <label for="peso_last_name" class="block text-gray-700 font-medium mb-1">Last Name <span
                        class="text-red-500">*</span></label>
                    <div class="relative">
                      <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                      <TextInput id="peso_last_name" v-model="form.peso_last_name" type="text"
                        class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                        placeholder="Enter your last name" />
                    </div>
                    <InputError :message="form.errors.peso_last_name" class="mt-1" />
                  </div>
                  <!-- Email -->
                  <div>
                    <label for="email" class="block text-gray-700 font-medium mb-1">Email Address <span
                        class="text-red-500">*</span></label>
                    <div class="relative">
                      <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                      <TextInput id="email" v-model="form.email" type="email"
                        class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                        placeholder="peso@example.com" required />
                    </div>
                    <InputError :message="form.errors.email" class="mt-1" />
                  </div>
                  <!-- Contact Number -->
                  <div>
                    <label for="contact_number" class="block text-gray-700 font-medium mb-1">Phone Number <span
                        class="text-red-500">*</span></label>
                    <div class="relative">
                      <i class="fas fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                      <TextInput id="contact_number" v-model="form.contact_number" type="text"
                        class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                        placeholder="9013604441" />
                    </div>
                    <InputError :message="form.errors.contact_number" class="mt-1" />
                  </div>
                </div>
                <!-- Address -->
                <div class="mt-6">
                  <label for="address" class="block text-gray-700 font-medium mb-1">Address</label>
                  <div class="relative">
                    <i class="fas fa-map-marker-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput id="address" v-model="form.address" type="text"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="Enter your Office Address" />
                  </div>
                  <InputError :message="form.errors.address" class="mt-1" />
                </div>
              </div>
            </form>
          </div>

          <!-- About Me Section -->
          <div class="bg-white p-6 rounded-lg border border-gray-200">
            <div class="flex items-center mb-4">
              <i class="fas fa-info-circle text-blue-600 mr-2"></i>
              <h2 class="text-xl font-semibold text-gray-900">About Me</h2>
            </div>
            <p class="text-gray-600 mb-6">Tell others about Peso</p>
            <div>
              <label for="description" class="block text-gray-700 font-medium mb-1">Description</label>
              <textarea id="description" v-model="form.description" rows="4"
                class="w-full border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all resize-none"
                placeholder="Describe your PESO office and services..."></textarea>
              <InputError :message="form.errors.description" class="mt-1" />
            </div>
          </div>

           <!-- Contact and Social Links Section -->
          <div class="bg-white p-6 rounded-lg border border-gray-200">
            <h2 class="text-xl font-semibold mb-2">Contact and Social Links</h2>
            <p class="text-gray-600 mb-4">Add your professional networks and contact options</p>

            <div class="space-y-4">
              <!-- Facebook -->
              <div class="relative">
                <label for="facebook-url" class="block text-gray-700 font-medium mb-1">Facebook Profile</label>
                <div class="relative">
                  <i class="fab fa-facebook absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <TextInput id="facebook-url" v-model="form.social_links.facebook" type="url"
                    class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                    placeholder="https://facebook.com/yourpage" />
                </div>
                <InputError :message="form.errors['social_links.facebook']" class="mt-1" />
              </div>

              <!-- Twitter -->
              <div class="relative">
                <label for="twitter-url" class="block text-gray-700 font-medium mb-1">Twitter Profile</label>
                <div class="relative">
                  <i class="fab fa-twitter absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <TextInput id="twitter-url" v-model="form.social_links.twitter" type="url"
                    class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                    placeholder="https://twitter.com/yourhandle" />
                </div>
                <InputError :message="form.errors['social_links.twitter']" class="mt-1" />
              </div>

              <!-- LinkedIn -->
              <div class="relative">
                <label for="linkedin-url" class="block text-gray-700 font-medium mb-1">LinkedIn Profile</label>
                <div class="relative">
                  <i class="fab fa-linkedin absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <TextInput id="linkedin-url" v-model="form.social_links.linkedin" type="url"
                    class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                    placeholder="https://linkedin.com/company/yourcompany" />
                </div>
                <InputError :message="form.errors['social_links.linkedin']" class="mt-1" />
              </div>

              <div class="relative">
                <label for="indeed-url" class="block text-gray-700 font-medium mb-1">Indeed Profile</label>
                <div class="relative">
                  <i class="fab fa-info absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <TextInput id="indeed-url" v-model="form.social_links.indeed" type="url"
                    class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                    placeholder="https://indeed.com/company/yourcompany" />
                </div>
                <InputError :message="form.errors['social_links.indeed']" class="mt-1" />
              </div>

              <!-- Instagram -->
              <div class="relative">
                <label for="instagram-url" class="block text-gray-700 font-medium mb-1">Instagram Profile</label>
                <div class="relative">
                  <i class="fab fa-instagram absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <TextInput id="instagram-url" v-model="form.social_links.instagram" type="url"
                    class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                    placeholder="https://instagram.com/yourpage" />
                </div>
                <InputError :message="form.errors['social_links.instagram']" class="mt-1" />
              </div>
            </div>
          </div>

            <!-- Save/Reset Buttons Section -->
            <div class="bg-white p-6 rounded-lg border border-gray-200 flex justify-end space-x-3">
              <SecondaryButton @click="resetForm" :disabled="form.processing">
                Reset
              </SecondaryButton>
              <PrimaryButton @click="submitForm" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700">
                <span v-if="form.processing">Saving Changes...</span>
                <span v-else>Save Changes</span>
              </PrimaryButton>
            </div>
          </div>
        </div>
      </div>
      

    <!-- Password Success Modal -->
    <Modal :show="showPasswordSuccessModal" @close="closePasswordSuccessModal">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-green-100 rounded-full p-3">
            <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Password Updated!</h3>
        <p class="text-center text-gray-600">Your password has been changed successfully. You will be logged out for security reasons.</p>
        <div class="mt-6 flex justify-center">
          <PrimaryButton @click="closePasswordSuccessModal">OK</PrimaryButton>
        </div>
      </div>
    </Modal>

    <!-- Password Error Modal -->
    <Modal :show="showPasswordErrorModal" @close="closePasswordErrorModal">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-red-100 rounded-full p-3">
            <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Password Update Failed</h3>
        <p class="text-center text-gray-600">{{ passwordErrorMessage }}</p>
        <div class="mt-6 flex justify-center">
          <PrimaryButton @click="closePasswordErrorModal">OK</PrimaryButton>
        </div>
      </div>
    </Modal>

    <!-- Success Modal -->
    <Modal :show="showUpdateModal" @close="showUpdateModal = false">
      <div class="p-6">
        <div class="flex items-center">
          <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
            <i class="fas fa-check text-green-600 text-xl"></i>
          </div>
        </div>
        <div class="mt-3 text-center">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Profile Updated Successfully</h3>
          <div class="mt-2">
            <p class="text-sm text-gray-500">
              Your PESO profile has been updated successfully.
            </p>
          </div>
        </div>
        <div class="mt-5">
          <PrimaryButton @click="showUpdateModal = false" class="w-full justify-center">
            Continue
          </PrimaryButton>
        </div>
      </div>
    </Modal>



    <!-- Error Modal -->
    <Modal :show="showErrorModal" @close="showErrorModal = false">
      <div class="p-6">
        <div class="flex items-center">
          <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
          </div>
        </div>
        <div class="mt-3 text-center">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Update Failed</h3>
          <div class="mt-2">
            <p class="text-sm text-gray-500">
              There was an error updating your profile. Please check the form and try again.
            </p>
          </div>
        </div>
        <div class="mt-5">
          <SecondaryButton @click="showErrorModal = false" class="w-full justify-center">
            Close
          </SecondaryButton>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>

<style scoped>
/* Glow effect animations */
@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.5);
  }

  70% {
    box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
  }

  100% {
    box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
  }
}

/* Input focus effects with glow */
input:focus,
textarea:focus,
select:focus {
  animation: pulse 1.5s ease-in-out;
  transition: all 0.3s ease;
}

/* Button hover effects */
button:not(:disabled) {
  transition: all 0.3s ease;
}

button:not(:disabled):hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>