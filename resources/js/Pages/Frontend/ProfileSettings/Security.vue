<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import '@fortawesome/fontawesome-free/css/all.css';

// Define props
const props = defineProps({
  activeSection: {
    type: String,
    default: 'security'
  }
});
const emit = defineEmits(['close-all-modals', 'reset-all-states']);
// Password Management
const passwordInput = ref(null);
const currentPasswordInput = ref(null);

// Create form for password update
const settingsForm = useForm({
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
watch(() => settingsForm.password, (newPassword) => {
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
  if (!settingsForm.password) return 0;
  
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
  if (!settingsForm.password || !settingsForm.password_confirmation) return true;
  return settingsForm.password === settingsForm.password_confirmation;
});

// Form validation
const validateForm = () => {
  let isValid = true;
  
  // Validate current password
  if (!settingsForm.current_password) {
    settingsForm.setError('current_password', 'Current password is required');
    isValid = false;
  }
  
  // Validate new password
  const passwordValidation = validatePassword(settingsForm.password);
  if (!passwordValidation.valid) {
    settingsForm.setError('password', passwordValidation.message);
    isValid = false;
  }
  
  // Validate password confirmation
  if (!settingsForm.password_confirmation) {
    settingsForm.setError('password_confirmation', 'Please confirm your new password');
    isValid = false;
  } else if (!passwordsMatch.value) {
    settingsForm.setError('password_confirmation', 'Passwords do not match');
    isValid = false;
  }
  
  return isValid;
};

// Update password function
const updatePassword = () => {
  // Clear previous errors
  settingsForm.clearErrors();
  
  // Validate form
  if (!validateForm()) {
    return;
  }
  
  settingsForm.put(route('user-password.update'), {
    preserveScroll: true,
    onSuccess: () => {
      settingsForm.reset();
      showSuccessModal();
    },
    onError: (errors) => {
      console.error('Password update errors:', errors);
      
      if (settingsForm.errors.password && passwordInput.value) {
        settingsForm.reset('password', 'password_confirmation');
        passwordInput.value.focus();
      }
      if (settingsForm.errors.current_password && currentPasswordInput.value) {
        settingsForm.reset('current_password');
        currentPasswordInput.value.focus();
      }
      
      if (Object.keys(errors).length > 0 && !settingsForm.errors.password && !settingsForm.errors.current_password) {
        showErrorModal('An error occurred while updating your password. Please try again.');
      }
    },
  });
};

// Modal states
const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const errorMessage = ref('');

// Show success modal
const showSuccessModal = () => {
  isSuccessModalOpen.value = true;
  setTimeout(() => {
    isSuccessModalOpen.value = false;
    // Automatically log out the user after password change
    router.visit(route('logout'), { method: 'post' });
  }, 3000);
};

// Show error modal
const showErrorModal = (message) => {
  errorMessage.value = message || 'An error occurred while updating your password.';
  isErrorModalOpen.value = true;
};
</script>

<template>
  <div v-if="activeSection === 'security'" class="flex flex-col lg:flex-row">
    <!-- Success Modal -->
    <Modal :modelValue="isSuccessModalOpen" @close="isSuccessModalOpen = false">
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
          <div class="bg-rose-100 rounded-full p-3">
            <i class="fas fa-exclamation-triangle text-rose-500 text-xl"></i>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Error</h3>
        <p class="text-center text-gray-600">{{ errorMessage }}</p>
        <div class="mt-6 flex justify-center">
          <button
            type="button"
            class="bg-rose-500 hover:bg-rose-600 transition-all duration-200 text-white px-4 py-2 rounded-md shadow-sm hover:shadow-md"
            @click="isErrorModalOpen = false">
            Close
          </button>
        </div>
      </div>
    </Modal>

    <div class="w-full lg:w-1/1 mb-6 lg:mb-0 bg-white p-6 rounded-lg shadow-sm border border-gray-100">
      <h1 class="text-xl font-semibold mb-2 text-gray-800">Change Password</h1>
      <p class="text-gray-500 mb-6 text-sm">Update your password to maintain account security</p>
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
            v-model="settingsForm.current_password"
            class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
            :class="{'border-rose-500 ring-1 ring-rose-500': settingsForm.errors.current_password}"
            placeholder="Enter your current password" />
          </div>
          <div v-if="settingsForm.errors.current_password" class="text-rose-500 text-xs mt-1.5 flex items-center">
            <i class="fas fa-exclamation-circle mr-1"></i>
            {{ settingsForm.errors.current_password }}
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
              v-model="settingsForm.password"
              class="w-full pl-10 pr-10 py-2.5 border rounded-lg focus:outline-none focus:ring-2 transition-all duration-200"
              :class="{
                'border-rose-500 ring-1 ring-rose-500': settingsForm.errors.password,
                'border-emerald-500 ring-1 ring-emerald-500': passwordStrength === 5,
                'border-blue-500 ring-1 ring-blue-500': passwordStrength === 4,
                'border-yellow-500 ring-1 ring-yellow-500': passwordStrength === 3,
                'border-orange-500 ring-1 ring-orange-500': passwordStrength === 2,
                'border-red-500 ring-1 ring-red-500': passwordStrength === 1,
                'border-gray-200 focus:ring-blue-500 focus:border-blue-500': !settingsForm.password
              }"
              placeholder="Enter your new password" />
            <!-- Password strength indicator icon -->
            <span v-if="settingsForm.password" 
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
          <div v-if="settingsForm.errors.password" class="text-rose-500 text-xs mt-1.5 flex items-center">
            <i class="fas fa-exclamation-circle mr-1"></i>
            {{ settingsForm.errors.password }}
          </div>
          <!-- Password strength indicator -->
          <div v-if="settingsForm.password" class="mt-3 mb-2">
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
          
            <div 
            v-if="settingsForm.password"
            class="mt-3 p-3 bg-gray-50 rounded-md border border-gray-100 transition-all duration-300"
            :class="{'bg-blue-50 border-blue-100': settingsForm.password}">
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
              v-model="settingsForm.password_confirmation"
              class="w-full pl-10 pr-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 transition-all duration-200"
              :class="{
                'border-rose-500 ring-1 ring-rose-500': settingsForm.errors.password_confirmation,
                'border-emerald-500 ring-1 ring-emerald-500': settingsForm.password && settingsForm.password_confirmation && passwordsMatch,
                'border-rose-500 ring-1 ring-rose-500': settingsForm.password && settingsForm.password_confirmation && !passwordsMatch,
                'border-gray-200 focus:ring-blue-500 focus:border-blue-500': !settingsForm.password_confirmation || !settingsForm.password
              }"
              placeholder="Confirm your new password" />
            <!-- Password match indicator -->
            <span v-if="settingsForm.password && settingsForm.password_confirmation" 
                  class="absolute inset-y-0 right-0 flex items-center pr-3 transition-all duration-300">
              <i v-if="passwordsMatch" class="fas fa-check text-emerald-500"></i>
              <i v-else class="fas fa-times text-rose-500"></i>
            </span>
          </div>
          <div v-if="settingsForm.errors.password_confirmation" class="text-rose-500 text-xs mt-1.5 flex items-center">
            <i class="fas fa-exclamation-circle mr-1"></i>
            {{ settingsForm.errors.password_confirmation }}
          </div>
          <!-- Password match message -->
          <div v-else-if="settingsForm.password && settingsForm.password_confirmation" class="text-xs mt-1.5 flex items-center transition-all duration-300"
               :class="passwordsMatch ? 'text-emerald-500' : 'text-rose-500'">
            <i :class="passwordsMatch ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'" class="mr-1"></i>
            {{ passwordsMatch ? 'Passwords match' : 'Passwords do not match' }}
          </div>
        </div>
        <button 
          type="submit"
          class="w-full bg-blue-600 text-white py-2.5 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200 shadow-sm hover:shadow-md flex items-center justify-center"
          :disabled="settingsForm.processing">
          <i class="fas fa-shield-alt mr-2"></i> 
          <span v-if="settingsForm.processing" class="flex items-center">
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
</template>

<style scoped>
/* Animations and transitions */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

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

@keyframes checkmark {
  0% { transform: scale(0); opacity: 0; }
  50% { transform: scale(1.2); opacity: 1; }
  100% { transform: scale(1); opacity: 1; }
}

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

/* Input focus effects */
input:focus {
  animation: pulse 1.5s ease-in-out;
}

/* Button hover effects */
button:not(:disabled) {
  transition: all 0.3s ease;
}

button:not(:disabled):hover {
  transform: translateY(-2px);
}

/* Form element transitions */
form div {
  animation: fadeIn 0.3s ease-out;
}

/* Password requirement animations */
.fa-check-circle {
  animation: checkmark 0.5s ease-in-out;
}

/* Password strength bar animation */
.w-full > div[class*="bg-"] {
  transition: width 0.5s ease-out, background-color 0.5s ease;
}

/* Very strong password effect */
.bg-emerald-500 {
  background: linear-gradient(90deg, #10b981, #34d399, #10b981);
  background-size: 200% 100%;
  animation: shimmer 2s infinite;
}

/* Loading spinner animation */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>