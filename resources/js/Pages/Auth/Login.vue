<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import Modal from '@/Components/Modal.vue';
import '@fortawesome/fontawesome-free/css/all.css';

defineProps({
  canResetPassword: Boolean,
  status: String,
});

// Password visibility toggle
const showPassword = ref(false);

// Modal state
const showModal = ref(false);
const modalTitle = ref('');
const modalMessage = ref('');

// Login form
const form = useForm({
  email: '',
  password: '',
  remember: false,
});

// Form submission
const submit = () => {
  form.transform(data => ({
    ...data,
    remember: form.remember ? 'on' : '',
  })).post(route('login'), {
    onFinish: () => form.reset('password'),
    onError: (errors) => {
      console.log('Errors:', errors); // Debugging
      if (errors.password) {
        modalTitle.value = 'Login Error';
        modalMessage.value = errors.password;
        showModal.value = true;
      } else if (errors.email) {
        modalTitle.value = 'Login Error';
        modalMessage.value = errors.email;
        showModal.value = true;
      }
    },
    onSuccess: () => {
      Inertia.visit(route('dashboard'));
    },
  });
};

// Toggle password visibility
const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
};
</script>

<template>
  <Head title="Sign In" />
  
  <!-- Modern Gradient Background -->
  <div class="min-h-screen gradient-bg flex items-center justify-center p-4 overflow-hidden relative">
    <!-- Floating Background Elements -->
    <div class="absolute inset-0">
      <div class="absolute top-10 left-10 w-32 h-32 gradient-card rounded-full opacity-10 animate-float"></div>
      <div class="absolute top-1/4 right-20 w-24 h-24 gradient-feature rounded-full opacity-15 animate-float-reverse"></div>
      <div class="absolute bottom-20 left-1/4 w-40 h-40 gradient-cta rounded-full opacity-8 animate-morph"></div>
      <div class="absolute top-1/2 right-1/3 w-16 h-16 bg-white rounded-full opacity-5 animate-pulse-glow"></div>
    </div>
    
    <div class="w-full max-w-md relative z-10">
      <!-- Logo Section -->
      <div class="text-center mb-8">
        <div class="w-16 h-16 gradient-card rounded-2xl flex items-center justify-center mx-auto mb-4 animate-pulse-glow">
          <span class="text-white font-bold text-2xl enhanced-text">P</span>
        </div>
        <h2 class="text-4xl font-bold text-white mb-3 enhanced-text">Welcome Back</h2>
        <p class="text-white/80 text-lg">Sign in to your <span class="text-blue-200 enhanced-text">Pathway</span> account</p>
      </div>

      <!-- Glass Card -->
      <div class="glass p-8 rounded-3xl shadow-2xl border border-white/20 backdrop-blur-xl">
          <form @submit.prevent="submit" class="space-y-6">
            <div>
              <InputLabel for="email" value="Email" class="text-lg font-semibold text-white mb-2" />
              <TextInput 
                id="email" 
                v-model="form.email" 
                type="email" 
                class="block w-full px-4 py-3 text-lg rounded-xl glass border border-white/30 focus:border-blue-400 focus:ring-2 focus:ring-blue-400 text-white placeholder-white/60" 
                placeholder="Enter your email"
                required 
                autofocus
                aria-autocomplete="username" />
              <InputError class="mt-2 text-red-300" :message="form.errors.email" />
            </div>

            <div class="relative">
              <InputLabel for="password" value="Password" class="text-lg font-semibold text-white mb-2" />
              <div class="relative">
                <TextInput 
                  id="password" 
                  v-model="form.password" 
                  :type="showPassword ? 'text' : 'password'"
                  class="block w-full px-4 py-3 pr-12 text-lg rounded-xl glass border border-white/30 focus:border-blue-400 focus:ring-2 focus:ring-blue-400 text-white placeholder-white/60" 
                  placeholder="Enter your password"
                  required 
                  autocomplete="current-password"/>
                <button type="button"
                  class="absolute inset-y-0 right-0 pr-4 flex items-center text-white/70 hover:text-white hover:enhanced-text focus:outline-none transition-all duration-200"
                  @click="togglePasswordVisibility">
                  <i :class="[showPassword ? 'fas fa-eye-slash' : 'fas fa-eye', 'text-lg']"></i>
                </button>
              </div>
              <InputError class="mt-2 text-red-300" :message="form.errors.password" />
            </div>

            <!-- <div class="flex items-center justify-between">
              <label class="flex items-center cursor-pointer">
                <Checkbox v-model:checked="form.remember" name="remember" class="rounded border-white/30 text-blue-400 focus:ring-blue-400" />
                <span class="ml-3 text-base text-white/80 hover:text-white transition-colors">Remember me</span>
              </label>
              <Link href="route('password.request')" class="text-base text-blue-300 hover:text-blue-200 hover:enhanced-text transition-all duration-200">
                Forgot password?
              </Link>
            </div> -->

            <div class="pt-4">
              <button type="submit"
                class="w-full px-8 py-4 gradient-cta text-white text-lg font-bold rounded-2xl hover-blue transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-blue-400 shadow-2xl disabled:opacity-50 disabled:transform-none animate-pulse-glow"
                :disabled="form.processing">
                <span v-if="form.processing">Processing...</span>
                <span v-else>Sign In</span>
              </button>
            </div>
          </form>

          <div class="mt-8 text-center">
            <p class="text-base text-white/80">
              Don't have an account?
              <Link href="/register" class="text-blue-300 hover:text-blue-200 hover:enhanced-text font-semibold transition-all duration-200 ml-2">
                Sign Up
              </Link>
            </p>
          </div>
        </div>
      </div>
    </div>

  <Modal v-if="showModal" :show="showModal" @close="showModal = false">
    <template #title>
      {{ modalTitle }}
    </template>
    <template #content>
      <p>{{ modalMessage }}</p>
    </template>
    <template #footer>
      <PrimaryButton @click="showModal = false">Close</PrimaryButton>
    </template>
  </Modal>
</template>

<style scoped>
/* Modern blue gradient backgrounds */
.gradient-bg {
    background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
}

.gradient-card {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.gradient-feature {
    background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%);
}

.gradient-cta {
    background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
}

/* Floating animations */
@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

@keyframes float-reverse {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(20px);
    }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

.animate-float-reverse {
    animation: float-reverse 4s ease-in-out infinite;
}

/* Pulse glow effect */
@keyframes pulse-glow {
    0%, 100% {
        box-shadow: 0 0 20px rgba(30, 64, 175, 0.3);
    }
    50% {
        box-shadow: 0 0 40px rgba(30, 64, 175, 0.5);
    }
}

.animate-pulse-glow {
    animation: pulse-glow 2s ease-in-out infinite;
}

/* Morphing shapes */
@keyframes morph {
    0%, 100% {
        border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
    }
    50% {
        border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%;
    }
}

.animate-morph {
    animation: morph 8s ease-in-out infinite;
}

/* Blue hover effects */
.hover-blue:hover {
    background: linear-gradient(45deg, #1e40af, #2563eb, #3b82f6, #60a5fa);
    background-size: 400% 400%;
    animation: blue-shift 2s ease infinite;
}

@keyframes blue-shift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Glass morphism effect */
.glass {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Subtle text enhancement */
.enhanced-text {
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Smooth transitions */
* {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>