<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import { useForm, usePage, router } from '@inertiajs/vue3';

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

// Password validation
const validatePassword = (password) => {
  if (!password) return { valid: false, message: 'Password is required' };
  
  const minLength = 8;
  const hasUpperCase = /[A-Z]/.test(password);
  const hasLowerCase = /[a-z]/.test(password);
  const hasNumber = /[0-9]/.test(password);
  const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
  
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
    <Modal :show="isSuccessModalOpen" @close="isSuccessModalOpen = false">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-green-100 rounded-full p-3">
            <i class="fas fa-check text-green-500 text-xl"></i>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Success!</h3>
        <p class="text-center text-gray-600">Your password has been updated successfully.</p>
        <div class="mt-6 flex justify-center">
          <button
            type="button"
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
          <button
            type="button"
            class="bg-red-500 hover:bg-red-600 transition-colors text-white px-4 py-2 rounded-md"
            @click="isErrorModalOpen = false">
            Close
          </button>
        </div>
      </div>
    </Modal>

    <div class="w-full lg:w-1/1 mb-6 lg:mb-0">
      <h1 class="text-xl font-semibold mb-4">Change Password</h1>
      <p class="text-gray-600 mb-4">Update your password to maintain account security</p>
      <form @submit.prevent="updatePassword">
        <div class="mb-4">
          <label class="block text-gray-700 mb-2" for="current-password">Current Password</label>
          <input 
            type="password" 
            id="current-password" 
            ref="currentPasswordInput"
            v-model="settingsForm.current_password"
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
            :class="{'border-red-500': settingsForm.errors.current_password}"
            placeholder="Current Password" />
          <div v-if="settingsForm.errors.current_password" class="text-red-500 text-sm mt-1">
            {{ settingsForm.errors.current_password }}
          </div>
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 mb-2" for="new-password">New Password</label>
          <input 
            type="password" 
            id="new-password" 
            ref="passwordInput"
            v-model="settingsForm.password"
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
            :class="{'border-red-500': settingsForm.errors.password}"
            placeholder="New Password" />
          <div v-if="settingsForm.errors.password" class="text-red-500 text-sm mt-1">
            {{ settingsForm.errors.password }}
          </div>
          <div class="text-sm text-gray-600 mt-1">
            Password must be at least 8 characters and include uppercase, lowercase, number, and special character.
          </div>
        </div>
        <div class="mb-6">
          <label class="block text-gray-700 mb-2" for="confirm-password">Confirm New Password</label>
          <input 
            type="password" 
            id="confirm-password" 
            v-model="settingsForm.password_confirmation"
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-600"
            :class="{'border-red-500': settingsForm.errors.password_confirmation}"
            placeholder="Confirm New Password" />
          <div v-if="settingsForm.errors.password_confirmation" class="text-red-500 text-sm mt-1">
            {{ settingsForm.errors.password_confirmation }}
          </div>
        </div>
        <button 
          type="submit"
          class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-600"
          :disabled="settingsForm.processing">
          <i class="fas fa-key mr-2"></i> 
          <span v-if="settingsForm.processing">Updating...</span>
          <span v-else>Change Password</span>
        </button>
      </form>
    </div>
  </div> 
</template>