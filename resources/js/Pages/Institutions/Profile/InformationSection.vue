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
      <div class="absolute top-20 left-10 w-32 h-32 bg-gradient-to-r from-blue-400 to-blue-600 rounded-full opacity-10 animate-float"></div>
      <div class="absolute top-40 right-20 w-24 h-24 bg-gradient-to-r from-blue-500 to-blue-700 rounded-full opacity-15 animate-float-reverse"></div>
      <div class="absolute bottom-32 left-1/4 w-40 h-40 bg-gradient-to-r from-blue-300 to-blue-500 rounded-full opacity-8 animate-float"></div>
      <div class="absolute top-1/3 right-1/3 w-20 h-20 bg-gradient-to-r from-blue-600 to-blue-800 rounded-full opacity-12 animate-float-reverse"></div>
      
      <!-- Morphing Background Shapes -->
      <div class="absolute top-0 left-0 w-96 h-96 bg-gradient-to-br from-blue-600/5 to-blue-800/5 animate-morph"></div>
      <div class="absolute bottom-0 right-0 w-80 h-80 bg-gradient-to-tl from-blue-500/5 to-blue-700/5 animate-morph" style="animation-delay: 2s;"></div>
      
      <div class="relative z-10 max-w-4xl mx-auto py-12 px-6">
        <div class="text-center mb-12">
          <h1 class="text-5xl font-bold text-white mb-4 enhanced-text">Complete Your Institution Profile</h1>
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
                  currentStep === step ? 'bg-gradient-to-br from-teal-500 to-teal-600 text-white scale-110 animate-pulse-glow' : 
                  currentStep > step ? 'bg-emerald-500 text-white' : 'glass border-white/30 text-white/60'
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
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
              </svg>
            </div>
            <div>
              <h2 class="text-2xl font-bold text-white enhanced-text">Institution Details</h2>
              <p class="text-white/70">Tell us about your educational institution</p>
            </div>
          </div>
          <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <InputLabel for="institution_name" class="text-white font-medium">
                  Institution Name <span class="text-teal-400">*</span>
                </InputLabel>
                <TextInput id="institution_name" v-model="form.institution_name" type="text" required class="mt-2 block w-full glass border-white/20 text-white placeholder-white/50 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.institution_name" class="text-red-300" />
              </div>
              <div>
                <InputLabel for="institution_type" class="text-white font-medium">
                  Institution Type <span class="text-teal-400">*</span>
                </InputLabel>
                <select id="institution_type" v-model="form.institution_type" required class="mt-2 block w-full glass border-white/20 text-white bg-transparent focus:border-blue-400 focus:ring-blue-400 rounded-lg">
                  <option value="" class="bg-gray-800">Select Institution Type</option>
                  <option value="University" class="bg-gray-800">University</option>
                  <option value="College" class="bg-gray-800">College</option>
                  <option value="Technical School" class="bg-gray-800">Technical School</option>
                  <option value="Community College" class="bg-gray-800">Community College</option>
                </select>
                <InputError :message="form.errors.institution_type" class="text-red-300" />
              </div>
            </div>
            <div>
              <InputLabel for="institution_address" class="text-white font-medium">
                Institution Address <span class="text-teal-400">*</span>
              </InputLabel>
              <TextInput id="institution_address" v-model="form.institution_address" type="text" required class="mt-2 block w-full glass border-white/20 text-white placeholder-white/50 focus:border-blue-400 focus:ring-blue-400" />
              <InputError :message="form.errors.institution_address" class="text-red-300" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <InputLabel for="email" class="text-white font-medium">
                  Institution Email <span class="text-teal-400">*</span>
                </InputLabel>
                <TextInput id="email" v-model="form.email" type="email" required class="mt-2 block w-full glass border-white/20 text-white placeholder-white/50 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.email" class="text-red-300" />
              </div>
              <div>
                <InputLabel for="mobile_number" class="text-white font-medium">
                  Mobile Number <span class="text-teal-400">*</span>
                </InputLabel>
                <TextInput
                  id="mobile_number"
                  v-model="formattedMobileNumber"
                  type="text"
                  required
                  class="mt-2 block w-full glass border-white/20 text-white placeholder-white/50 focus:border-blue-400 focus:ring-blue-400"
                  placeholder="09XX-XXX-XXXX"
                />
                <InputError :message="form.errors.mobile_number" class="text-red-300" />
              </div>
            </div>
            <div>
              <InputLabel for="telephone_number" class="text-white font-medium">
                Telephone Number (Optional)
              </InputLabel>
              <TextInput
                id="telephone_number"
                v-model="formattedTelephoneNumber"
                type="text"
                class="mt-2 block w-full glass border-white/20 text-white placeholder-white/50 focus:border-blue-400 focus:ring-blue-400"
                placeholder="(02) XXXX-XXXX"
              />
              <InputError :message="form.errors.telephone_number" class="text-red-300" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <InputLabel for="institution_president_first_name" class="text-white font-medium">
                  President First Name <span class="text-teal-400">*</span>
                </InputLabel>
                <TextInput id="institution_president_first_name" v-model="form.institution_president_first_name" type="text" required class="mt-2 block w-full glass border-white/20 text-white placeholder-white/50 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.institution_president_first_name" class="text-red-300" />
              </div>
              <div>
                <InputLabel for="institution_president_last_name" class="text-white font-medium">
                  President Last Name <span class="text-teal-400">*</span>
                </InputLabel>
                <TextInput id="institution_president_last_name" v-model="form.institution_president_last_name" type="text" required class="mt-2 block w-full glass border-white/20 text-white placeholder-white/50 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.institution_president_last_name" class="text-red-300" />
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <InputLabel for="institution_career_officer_first_name" class="text-white font-medium">
                  Career Officer First Name <span class="text-teal-400">*</span>
                </InputLabel>
                <TextInput id="institution_career_officer_first_name" v-model="form.institution_career_officer_first_name" type="text" required class="mt-2 block w-full glass border-white/20 text-white placeholder-white/50 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.institution_career_officer_first_name" class="text-red-300" />
              </div>
              <div>
                <InputLabel for="institution_career_officer_last_name" class="text-white font-medium">
                  Career Officer Last Name <span class="text-teal-400">*</span>
                </InputLabel>
                <TextInput id="institution_career_officer_last_name" v-model="form.institution_career_officer_last_name" type="text" required class="mt-2 block w-full glass border-white/20 text-white placeholder-white/50 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.institution_career_officer_last_name" class="text-red-300" />
              </div>
            </div>
          </div>
        </div>
        
        <!-- Step 2: Verification Document -->
        <div v-show="currentStep === 2" class="glass rounded-2xl p-8 reveal">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
            <div>
              <h2 class="text-2xl font-bold text-white enhanced-text">Verification Document</h2>
              <p class="text-white/70">Upload your institution verification document</p>
            </div>
          </div>
          <div class="space-y-6">
            <div>
              <InputLabel for="verification_file" class="text-white font-medium">
                Upload Document <span class="text-amber-400">*</span>
              </InputLabel>
              <input
                id="verification_file"
                type="file"
                @change="onFileChange"
                accept=".pdf,.jpg,.jpeg,.png"
                required
                class="mt-2 block w-full text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-500 file:text-white hover:file:bg-blue-600 file:cursor-pointer cursor-pointer glass border-white/20 rounded-lg focus:border-blue-400 focus:ring-blue-400"
              />
              <p class="text-white/60 text-sm mt-2">Accepted formats: PDF, JPG, JPEG, PNG (Max: 2MB)</p>
              <InputError :message="form.errors.verification_file" class="text-red-300" />
            </div>
          </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between items-center mt-8">
          <PrimaryButton
            v-if="currentStep > 1"
            @click="prevStep"
            type="button"
            class="px-8 py-3 bg-white/20 text-white border border-white/30 rounded-xl hover:bg-white/30 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-white/20"
          >
            <i class="fas fa-arrow-left mr-2"></i>
            Previous
          </PrimaryButton>
          <div v-else></div>
          
          <PrimaryButton
            v-if="currentStep < totalSteps"
            @click="nextStep"
            :disabled="!canProceed"
            type="button"
            class="px-8 py-3 gradient-cta text-white rounded-xl hover-blue transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-blue-400 shadow-lg disabled:opacity-50 disabled:transform-none"
          >
            Next Step
            <i class="fas fa-arrow-right ml-2"></i>
          </PrimaryButton>
          
          <PrimaryButton
            v-else
            @click="submit"
            :disabled="!canProceed || form.processing"
            type="submit"
            class="px-8 py-3 gradient-cta text-white rounded-xl hover-blue transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-blue-400 shadow-lg disabled:opacity-50 disabled:transform-none animate-pulse-glow"
          >
            <span v-if="form.processing">Saving...</span>
            <span v-else>Save Information</span>
          </PrimaryButton>
        </div>
      </form>

      <Modal v-model="showModal">
        <template #header>
          <h2 class="text-2xl font-bold text-teal-600">Profile Saved!</h2>
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
  background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 25%, #2563eb 50%, #3b82f6 75%, #60a5fa 100%);
}

.gradient-feature {
  background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
}

.gradient-cta {
  background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
}

.hover-blue:hover {
  background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
}

.enhanced-text {
  text-shadow: 0 0 10px rgba(59, 130, 246, 0.3);
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
  from { box-shadow: 0 0 20px rgba(20, 184, 166, 0.4); }
  to { box-shadow: 0 0 30px rgba(20, 184, 166, 0.8); }
}

.animate-pulse-glow {
  animation: pulse-glow 2s ease-in-out infinite alternate;
}

.animate-float {
  animation: float 6s ease-in-out infinite;
}

.animate-float-reverse {
  animation: float-reverse 8s ease-in-out infinite;
}

.animate-morph {
  animation: morph 8s ease-in-out infinite;
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