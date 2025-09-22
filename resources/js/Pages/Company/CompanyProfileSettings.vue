<script setup>
import { ref, reactive, computed, watch } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  company: Object,
});

const page = usePage();
const user = page.props.auth.user;

// Form for company profile settings
const form = useForm({
  company_name: props.company?.company_name || '',
  company_email: props.company?.company_email || '',
  mobile_number: props.company?.mobile_number || '',
  telephone_number: props.company?.telephone_number || '',
  address: props.company?.address || '',
  sector: props.company?.sector || '',
  category: props.company?.category || '',
  description: props.company?.description || '',
  indeed_profile: props.company?.social_links?.indeed || '',
  facebook: props.company?.social_links?.facebook || '',
  twitter: props.company?.social_links?.twitter || '',
  linkedin: props.company?.social_links?.linkedin || '',
  instagram: props.company?.social_links?.instagram || '',
  website: props.company?.social_links?.website || '',
  logo: null,
});

// States for modals
const showSuccessModal = ref(false);
const showErrorModal = ref(false);
const errorMessage = ref('');

// Logo handling
const logoPreview = ref(props.company?.profile_photo_path || '/images/default-logo.png');
const logoInput = ref(null);

const handleLogoChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.logo = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      logoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const removeLogo = () => {
  form.logo = null;
  logoPreview.value = '/images/default-logo.png';
  if (logoInput.value) {
    logoInput.value.value = '';
  }
};

// Form submission
const submitForm = () => {
  form.post(route('company.profile.settings.update'), {
    onSuccess: () => {
      showSuccessModal.value = true;
    },
    onError: (errors) => {
      if (Object.keys(errors).length === 0) {
        errorMessage.value = 'An unexpected error occurred. Please try again.';
      } else {
        errorMessage.value = 'Please correct the errors in the form.';
      }
      showErrorModal.value = true;
    },
    preserveScroll: true,
  });
};

// Reset form to original values
const resetForm = () => {
  form.company_name = props.company?.company_name || '';
  form.company_email = props.company?.company_email || '';
  form.mobile_number = props.company?.mobile_number || '';
  form.telephone_number = props.company?.telephone_number || '';
  form.address = props.company?.address || '';
  form.sector = props.company?.sector || '';
  form.category = props.company?.category || '';
  form.description = props.company?.description || '';
  form.indeed_profile = props.company?.social_links?.indeed || '';
  form.facebook = props.company?.social_links?.facebook || '';
  form.twitter = props.company?.social_links?.twitter || '';
  form.linkedin = props.company?.social_links?.linkedin || '';
  form.instagram = props.company?.social_links?.instagram || '';
  form.website = props.company?.social_links?.website || '';
  form.logo = null;
  logoPreview.value = props.company?.profile_photo_path || '/images/default-logo.png';
  if (logoInput.value) {
    logoInput.value.value = '';
  }
  form.clearErrors();
};

// Close modals
const closeSuccessModal = () => {
  showSuccessModal.value = false;
};

const closeErrorModal = () => {
  showErrorModal.value = false;
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
const hasUpperCaseMet = ref(false);
const hasLowerCaseMet = ref(false);
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
  hasUpperCaseMet.value = hasUpperCase;
  hasLowerCaseMet.value = hasLowerCase;
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
    hasUpperCaseMet.value = false;
    hasLowerCaseMet.value = false;
    hasNumberMet.value = false;
    hasSpecialCharMet.value = false;
  }
});

// Password strength indicator
const passwordStrength = computed(() => {
  if (!passwordForm.password) return 0;
  
  let strength = 0;
  if (minLengthMet.value) strength++;
  if (hasUpperCaseMet.value) strength++;
  if (hasLowerCaseMet.value) strength++;
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

// Password modal states
const isPasswordSuccessModalOpen = ref(false);
const isPasswordErrorModalOpen = ref(false);
const passwordErrorMessage = ref('');

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
      passwordForm.reset();
      showPasswordSuccessModal();
    },
    onError: (errors) => {
      console.error('Password update errors:', errors);
      
      if (passwordForm.errors.password && passwordInput.value) {
        passwordForm.reset('password', 'password_confirmation');
        passwordInput.value.focus();
      }
      if (passwordForm.errors.current_password && currentPasswordInput.value) {
        passwordForm.reset('current_password');
        currentPasswordInput.value.focus();
      }
      
      if (Object.keys(errors).length > 0 && !passwordForm.errors.password && !passwordForm.errors.current_password) {
        showPasswordErrorModal('An error occurred while updating your password. Please try again.');
      }
    },
  });
};

// Show password success modal
const showPasswordSuccessModal = () => {
  isPasswordSuccessModalOpen.value = true;
  setTimeout(() => {
    isPasswordSuccessModalOpen.value = false;
    // Automatically log out the user after password change
    router.visit(route('logout'), { method: 'post' });
  }, 3000);
};

// Show password error modal
const showPasswordErrorModal = (message) => {
  passwordErrorMessage.value = message || 'An error occurred while updating your password.';
  isPasswordErrorModalOpen.value = true;
};
</script>

<template>
  <AppLayout title="Company Profile Settings">
    <template #header>
      <div class="flex items-center">
      <h2 class="text-xl font-semibold text-gray-800 flex items-center">
        <i class="fas fa-building text-blue-500 text-xl mr-2"></i> Company Profile Settings
      </h2>
      </div>
    </template>

    <!-- Main Content -->
      <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-9 mb-9 py-8">
        <div class="flex flex-col lg:flex-row lg:space-x-8">
          <!-- Left Column - Profile Picture and Social Links -->
          <div class="w-full lg:w-1/2 mb-8 lg:mb-0">
            <!-- Profile Picture Section -->
            <div class="bg-white p-6 rounded-lg border border-gray-200 mb-6">
              <h2 class="text-xl font-semibold mb-2">Company Logo</h2>
              <p class="text-gray-600 mb-4">Update your company logo</p>
              <div class="flex flex-col items-center">
                <div class="relative mb-4">
                  <img
                    :src="logoPreview || '/images/default-logo.png'"
                    alt="Company Logo"
                    class="rounded-full w-48 h-48 object-cover border border-gray-200 shadow-sm"
                  />
                  <div class="absolute bottom-0 right-0">
                    <label for="file-input"
                      class="bg-blue-600 hover:bg-blue-700 transition-colors text-white rounded-full p-2 cursor-pointer shadow-md">
                      <i class="fas fa-camera"></i>
                    </label>
                  </div>
                </div>
                <input
                  ref="logoInput"
                  id="file-input"
                  type="file"
                  accept="image/*"
                  @change="handleLogoChange"
                  class="hidden"
                />
                <label for="file-input"
                  class="text-blue-600 hover:text-blue-800 transition-colors font-medium cursor-pointer">
                  Choose an image
                </label>
              </div>
              <InputError :message="form.errors.logo" class="mt-2" />
            </div>

             <!-- Change Password Section -->
          <div>
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
                           :class="[minLengthMet ? 'fa-check-circle text-emerald-500' : 'fa-circle text-gray-300']"></i> 
                        At least 8 characters
                      </li>
                      <li class="flex items-center transition-all duration-300" :class="{'text-blue-600 font-medium': hasUpperCaseMet}">
                        <i class="fas mr-1.5 text-xs transition-all duration-300" 
                           :class="[hasUpperCaseMet ? 'fa-check-circle text-emerald-500' : 'fa-circle text-gray-300']"></i> 
                        Include uppercase letter (A-Z)
                      </li>
                      <li class="flex items-center transition-all duration-300" :class="{'text-blue-600 font-medium': hasLowerCaseMet}">
                        <i class="fas mr-1.5 text-xs transition-all duration-300" 
                           :class="[hasLowerCaseMet ? 'fa-check-circle text-emerald-500' : 'fa-circle text-gray-300']"></i> 
                        Include lowercase letter (a-z)
                      </li>
                      <li class="flex items-center transition-all duration-300" :class="{'text-blue-600 font-medium': hasNumberMet}">
                        <i class="fas mr-1.5 text-xs transition-all duration-300" 
                           :class="[hasNumberMet ? 'fa-check-circle text-emerald-500' : 'fa-circle text-gray-300']"></i> 
                        Include number (0-9)
                      </li>
                      <li class="flex items-center transition-all duration-300" :class="{'text-blue-600 font-medium': hasSpecialCharMet}">
                        <i class="fas mr-1.5 text-xs transition-all duration-300" 
                           :class="[hasSpecialCharMet ? 'fa-check-circle text-emerald-500' : 'fa-circle text-gray-300']"></i> 
                        Include special character (!@#$%^&*)
                      </li>
                    </ul>
                  </div>
                </div>

                <div class="mb-6">
                  <label class="block text-gray-700 text-sm font-medium mb-2" for="confirm-password">Confirm New Password</label>
                  <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                      <i class="fas fa-check-double text-sm"></i>
                    </span>
                    <input 
                      type="password" 
                      id="confirm-password" 
                      v-model="passwordForm.password_confirmation"
                      class="w-full pl-10 pr-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 transition-all duration-200"
                      :class="{
                        'border-rose-500 ring-1 ring-rose-500': passwordForm.errors.password_confirmation,
                        'border-emerald-500 ring-1 ring-emerald-500': passwordForm.password && passwordForm.password_confirmation && passwordsMatch,
                        'border-rose-500 ring-1 ring-rose-500': passwordForm.password && passwordForm.password_confirmation && !passwordsMatch,
                        'border-gray-200 focus:ring-blue-500 focus:border-blue-500': !passwordForm.password_confirmation || !passwordForm.password
                      }"
                      placeholder="Confirm your new password" />
                    <!-- Password match indicator -->
                    <span v-if="passwordForm.password && passwordForm.password_confirmation" 
                          class="absolute inset-y-0 right-0 flex items-center pr-3 transition-all duration-300">
                      <i v-if="passwordsMatch" class="fas fa-check text-emerald-500"></i>
                      <i v-else class="fas fa-times text-rose-500"></i>
                    </span>
                  </div>
                  <div v-if="passwordForm.errors.password_confirmation" class="text-rose-500 text-xs mt-1.5 flex items-center">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    {{ passwordForm.errors.password_confirmation }}
                  </div>
                  <!-- Password match message -->
                  <div v-else-if="passwordForm.password && passwordForm.password_confirmation" class="text-xs mt-1.5 flex items-center transition-all duration-300"
                       :class="passwordsMatch ? 'text-emerald-500' : 'text-rose-500'">
                    <i :class="passwordsMatch ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'" class="mr-1"></i>
                    {{ passwordsMatch ? 'Passwords match' : 'Passwords do not match' }}
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
          </div>

          <!-- Right Column - Form Fields -->
          <form @submit.prevent="submitForm" class="w-full lg:w-2/2 flex flex-col space-y-6">

            
            <!-- Company Information Section -->
            <div class="bg-white p-6 rounded-lg border border-gray-200">
              
              <div class="flex items-center mb-4">
                <i class="fas fa-building text-blue-600 mr-2"></i>
                <h2 class="text-xl font-semibold text-gray-900">Company Information</h2>
              </div>
              <p class="text-gray-600 mb-6">Basic details about your company</p>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Company Name -->
                <div>
                  <label for="company_name" class="block text-gray-700 font-medium mb-1">Company Name <span class="text-red-500">*</span></label>
                  <div class="relative">
                    <i class="fas fa-building absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput
                      id="company_name"
                      v-model="form.company_name"
                      type="text"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="Enter your company name"
                      required
                    />
                  </div>
                  <InputError :message="form.errors.company_name" class="mt-1" />
                </div>
                <!-- Industry Sector -->
                <div>
                  <label for="sector" class="block text-gray-700 font-medium mb-1">Industry Sector</label>
                  <div class="relative">
                    <i class="fas fa-industry absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput
                      id="sector"
                      v-model="form.sector"
                      type="text"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="Enter industry sector"
                    />
                  </div>
                  <InputError :message="form.errors.sector" class="mt-1" />
                </div>

                <!-- Category -->
                <div>
                  <label for="category" class="block text-gray-700 font-medium mb-1">Category</label>
                  <div class="relative">
                    <i class="fas fa-tags absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput
                      id="category"
                      v-model="form.category"
                      type="text"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="Enter category"
                    />
                  </div>
                  <InputError :message="form.errors.category" class="mt-1" />
                </div>

                <!-- Email -->
                <div>
                  <label for="company_email" class="block text-gray-700 font-medium mb-1">Email <span class="text-red-500">*</span></label>
                  <div class="relative">
                    <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput
                      id="company_email"
                      v-model="form.company_email"
                      type="email"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="Enter email address"
                      required
                    />
                  </div>
                  <InputError :message="form.errors.company_email" class="mt-1" />
                </div>

                 <!-- Mobile Number -->
                 <div>
                   <label for="mobile_number" class="block text-gray-700 font-medium mb-1">Mobile Number</label>
                   <div class="relative">
                     <i class="fas fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                     <TextInput
                       id="mobile_number"
                       v-model="form.mobile_number"
                       type="tel"
                       class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                       placeholder="Enter mobile number"
                     />
                   </div>
                   <InputError :message="form.errors.mobile_number" class="mt-1" />
                 </div>

                 <!-- Telephone Number -->
                 <div>
                   <label for="telephone_number" class="block text-gray-700 font-medium mb-1">Telephone Number</label>
                   <div class="relative">
                     <i class="fas fa-phone-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                     <TextInput
                       id="telephone_number"
                       v-model="form.telephone_number"
                       type="tel"
                       class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                       placeholder="Enter telephone number"
                     />
                   </div>
                   <InputError :message="form.errors.telephone_number" class="mt-1" />
                      </div>

                 <!-- Address -->
                 <div class="md:col-span-2">
                   <label for="address" class="block text-gray-700 font-medium mb-1">Address</label>
                   <div class="relative">
                     <i class="fas fa-map-marker-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                     <TextInput
                       id="address"
                       v-model="form.address"
                       type="text"
                       class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                       placeholder="Enter company address"
                     />
                   </div>
                   <InputError :message="form.errors.address" class="mt-1" />
                 </div>
               </div>
             </div>

            <!-- Company Description Section -->
            <div class="bg-white p-6 rounded-lg border border-gray-200">
              <div class="flex items-center mb-4">
                <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                <h2 class="text-xl font-semibold text-gray-900">Company Description</h2>
              </div>
              <p class="text-gray-600 mb-6">Provide a detailed description of your company</p>
              <div>
                <label for="description" class="block text-gray-700 font-medium mb-1">Description</label>
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="6"
                  class="w-full border border-gray-300 rounded-md p-3 outline-none focus:ring-1 focus:ring-blue-600 transition-all resize-none"
                  placeholder="Tell us about your company..."
                ></textarea>
                <InputError :message="form.errors.description" class="mt-1" />
              </div>
            </div>

            <!-- Contact and Social Links Section -->
            <div class="bg-white p-6 rounded-lg border border-gray-200">
              <h2 class="text-xl font-semibold mb-2">Contact and Social Links</h2>
              <p class="text-gray-600 mb-4">Add your professional networks and contact options</p>

              <div class="space-y-4">
                <!-- Indeed Profile -->
                <div class="relative">
                  <label for="indeed-url" class="block text-gray-700 font-medium mb-1">Indeed Profile</label>
                  <div class="relative">
                    <i class="fas fa-briefcase absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput
                      id="indeed-url"
                      v-model="form.indeed_profile"
                      type="url"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="https://www.indeed.com/company/..."
                    />
                  </div>
                  <InputError :message="form.errors.indeed_profile" class="mt-1" />
                </div>

                <!-- Facebook -->
                <div class="relative">
                  <label for="facebook-url" class="block text-gray-700 font-medium mb-1">Facebook Profile</label>
                  <div class="relative">
                    <i class="fab fa-facebook absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput
                      id="facebook-url"
                      v-model="form.facebook"
                      type="url"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="https://facebook.com/yourpage"
                    />
                  </div>
                  <InputError :message="form.errors.facebook" class="mt-1" />
                </div>

                <!-- Twitter -->
                <div class="relative">
                  <label for="twitter-url" class="block text-gray-700 font-medium mb-1">Twitter Profile</label>
                  <div class="relative">
                    <i class="fab fa-twitter absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput
                      id="twitter-url"
                      v-model="form.twitter"
                      type="url"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="https://twitter.com/yourhandle"
                    />
                  </div>
                  <InputError :message="form.errors.twitter" class="mt-1" />
                </div>

                <!-- LinkedIn -->
                <div class="relative">
                  <label for="linkedin-url" class="block text-gray-700 font-medium mb-1">LinkedIn Profile</label>
                  <div class="relative">
                    <i class="fab fa-linkedin absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput
                      id="linkedin-url"
                      v-model="form.linkedin"
                      type="url"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="https://linkedin.com/company/yourcompany"
                    />
                  </div>
                  <InputError :message="form.errors.linkedin" class="mt-1" />
                </div>

                <!-- Instagram -->
                <div class="relative">
                  <label for="instagram-url" class="block text-gray-700 font-medium mb-1">Instagram Profile</label>
                  <div class="relative">
                    <i class="fab fa-instagram absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput
                      id="instagram-url"
                      v-model="form.instagram"
                      type="url"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="https://instagram.com/yourpage"
                    />
                  </div>
                  <InputError :message="form.errors.instagram" class="mt-1" />
                </div>

                <!-- Website -->
                <div class="relative">
                  <label for="website-url" class="block text-gray-700 font-medium mb-1">Website</label>
                  <div class="relative">
                    <i class="fas fa-globe absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput
                      id="website-url"
                      v-model="form.website"
                      type="url"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="https://..."
                    />
                  </div>
                  <InputError :message="form.errors.website" class="mt-1" />
                </div>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4 pt-6">
              <button
                type="button"
                @click="resetForm"
                class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
              >
                Reset
              </button>
              <button
                type="submit"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50"
              >
                Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>

    <!-- Password Success Modal -->
    <Modal :show="isPasswordSuccessModalOpen" @close="isPasswordSuccessModalOpen = false">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-emerald-100 rounded-full p-3">
            <i class="fas fa-check text-emerald-500 text-xl"></i>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Success!</h3>
        <p class="text-center text-gray-600">Your password has been updated successfully.</p>
        <div class="mt-6 flex justify-center">
          <button
            type="button"
            class="bg-emerald-500 hover:bg-emerald-600 transition-all duration-200 text-white px-4 py-2 rounded-md shadow-sm hover:shadow-md"
            @click="isPasswordSuccessModalOpen = false">
            Close
          </button>
        </div>
      </div>
    </Modal>

    <!-- Password Error Modal -->
    <Modal :show="isPasswordErrorModalOpen" @close="isPasswordErrorModalOpen = false">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-rose-100 rounded-full p-3">
            <i class="fas fa-exclamation-triangle text-rose-500 text-xl"></i>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Error</h3>
        <p class="text-center text-gray-600">{{ passwordErrorMessage }}</p>
        <div class="mt-6 flex justify-center">
          <button
            type="button"
            class="bg-rose-500 hover:bg-rose-600 transition-all duration-200 text-white px-4 py-2 rounded-md shadow-sm hover:shadow-md"
            @click="isPasswordErrorModalOpen = false">
            Close
          </button>
        </div>
      </div>
    </Modal>

    <!-- Success Modal -->
    <Modal :show="showSuccessModal" @close="closeSuccessModal">
        <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-green-100 rounded-full p-3">
            <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Success!</h3>
        <p class="text-center text-gray-600">Your company profile has been updated successfully.</p>
        <div class="mt-6 flex justify-center">
          <PrimaryButton @click="closeSuccessModal">OK</PrimaryButton>
        </div>
      </div>
    </Modal>

    <!-- Error Modal -->
    <Modal :show="showErrorModal" @close="closeErrorModal">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-red-100 rounded-full p-3">
            <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Error</h3>
        <p class="text-center text-gray-600">{{ errorMessage }}</p>
        <div class="mt-6 flex justify-center">
          <PrimaryButton @click="closeErrorModal">OK</PrimaryButton>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.hover-effect {
  transition: all 0.3s ease;
}

.hover-effect:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style>