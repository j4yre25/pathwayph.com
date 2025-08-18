<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { useFormattedMobileNumber } from '@/Composables/useFormattedMobileNumber.js';
import { useFormattedTelephoneNumber } from '@/Composables/useFormattedTelephoneNumber.js';

const props = defineProps({
  email: String,
});

const form = useForm({
  institution_name: '',
  institution_type: '',
  institution_address: '',
  email: props.email || '',
  mobile_number: '',
  telephone_number: '',
  institution_president_first_name: '',
  institution_president_last_name: '',
  institution_career_officer_first_name: '',
  institution_career_officer_last_name: '',
  verification_file: null,
});

const showModal = ref(false);
const currentStep = ref(1);
const totalSteps = ref(2);

// Composables for formatted numbers
const { formattedMobileNumber } = useFormattedMobileNumber(form, 'mobile_number');
const { formattedTelephoneNumber } = useFormattedTelephoneNumber(form, 'telephone_number');

// Step navigation functions
function nextStep() {
  if (canProceed.value && currentStep.value < totalSteps.value) {
    currentStep.value++;
  }
}

function prevStep() {
  if (currentStep.value > 1) {
    currentStep.value--;
  }
}

function goToStep(step) {
  if (step >= 1 && step <= totalSteps.value) {
    currentStep.value = step;
  }
}

// Validation for each step
function isStepValid(step) {
  switch (step) {
    case 1:
      return form.institution_name && form.institution_type && form.institution_address && form.email && form.mobile_number;
    case 2:
      return form.institution_career_officer_first_name && form.institution_career_officer_last_name && form.institution_president_first_name && form.institution_president_last_name;
    case 3:
      return form.verification_file;
    default:
      return false;
  }
}

const canProceed = computed(() => {
  return isStepValid(currentStep.value);
});

function onFileChange(e) {
  form.verification_file = e.target.files[0];
}

function submit() {
  if (currentStep.value === totalSteps.value && canProceed.value) {
    form.post(route('institution.information.save'), {
      forceFormData: true,
      onSuccess: () => {
        showModal.value = true;
      }
    });
  }
}

function goToProfile() {
  window.location.href = route('institution.profile');
}
</script>

<template>
  <AppLayout>
    <div class="relative min-h-screen gradient-bg overflow-hidden">
      <!-- Floating Background Elements -->
      <div class="absolute top-20 left-10 w-32 h-32 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full opacity-20 animate-float"></div>
      <div class="absolute top-40 right-20 w-24 h-24 bg-gradient-to-r from-cyan-400 to-blue-400 rounded-full opacity-30 animate-float-reverse"></div>
      <div class="absolute bottom-32 left-1/4 w-40 h-40 bg-gradient-to-r from-green-400 to-teal-400 rounded-full opacity-15 animate-float"></div>
      <div class="absolute top-1/3 right-1/3 w-20 h-20 bg-gradient-to-r from-yellow-400 to-orange-400 rounded-full opacity-25 animate-float-reverse"></div>
      
      <!-- Morphing Background Shapes -->
      <div class="absolute top-0 left-0 w-96 h-96 bg-gradient-to-br from-purple-600/10 to-pink-600/10 animate-morph"></div>
      <div class="absolute bottom-0 right-0 w-80 h-80 bg-gradient-to-tl from-cyan-600/10 to-blue-600/10 animate-morph" style="animation-delay: 2s;"></div>
      
      <div class="relative z-10 max-w-4xl mx-auto py-12 px-6">
        <div class="text-center mb-12">
          <h1 class="text-5xl font-bold text-white mb-4 neon-text">Complete Your Institution Profile</h1>
          <p class="text-xl text-white/80">Join our network of educational institutions</p>
        </div>
        
        <!-- Step Progress Indicator -->
        <div class="flex justify-center mb-12">
          <div class="flex items-center space-x-4">
            <div v-for="step in totalSteps" :key="step" class="flex items-center">
              <div 
                @click="goToStep(step)"
                :class="[
                  'w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg cursor-pointer transition-all duration-300',
                  currentStep === step ? 'gradient-feature text-white scale-110 animate-pulse-glow' : 
                  currentStep > step ? 'bg-green-500 text-white' : 'glass border-white/30 text-white/60'
                ]"
              >
                <svg v-if="currentStep > step" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span v-else>{{ step }}</span>
              </div>
              <div v-if="step < totalSteps" class="w-12 h-0.5 bg-white/20 mx-2"></div>
            </div>
          </div>
        </div>
        
        <form @submit.prevent="submit" class="space-y-8">
        <!-- Step 1: Institution Details -->
        <div v-show="currentStep === 1" class="glass rounded-2xl p-8 reveal">
          <!-- ... (rest of your form content) ... -->
        </div>
        
        <!-- Step 2: Verification Document -->
        <div v-show="currentStep === 2" class="glass rounded-2xl p-8 reveal">
          <!-- ... (rest of your form content) ... -->
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between items-center mt-8">
          <!-- ... (navigation buttons) ... -->
        </div>
      </form>

      <Modal v-model="showModal">
        <template #header>
          <h2 class="text-2xl font-bold text-green-600">Profile Saved!</h2>
        </template>
        <template #body>
          <p class="mb-6 text-gray-700">
            Your institution information has been saved.<br>
            You will now be redirected to your profile page.
          </p>
        </template>
        <template #footer>
          <PrimaryButton @click="goToProfile">Go to Profile</PrimaryButton>
        </template>
      </Modal>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.gradient-bg {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.gradient-feature {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.gradient-cta {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.neon-text {
  text-shadow: 0 0 10px rgba(79, 172, 254, 0.5);
}

.float {
  animation: float 6s ease-in-out infinite;
}

.float-reverse {
  animation: float-reverse 8s ease-in-out infinite;
}

.pulse-glow {
  animation: pulse-glow 2s ease-in-out infinite alternate;
}

.morph {
  animation: morph 8s ease-in-out infinite;
}

.reveal {
  animation: reveal 0.6s ease-out;
}

@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(10deg); }
}

@keyframes float-reverse {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(20px) rotate(-5deg); }
}

@keyframes pulse-glow {
  from { box-shadow: 0 0 20px rgba(79, 172, 254, 0.4); }
  to { box-shadow: 0 0 30px rgba(79, 172, 254, 0.8); }
}

@keyframes morph {
  0%, 100% { border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; }
  50% { border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%; }
}

@keyframes reveal {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>