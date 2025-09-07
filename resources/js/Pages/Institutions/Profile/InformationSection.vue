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
      <div
        class="absolute top-20 left-10 w-32 h-32 bg-gradient-to-r from-blue-400 to-blue-600 rounded-full opacity-10 animate-float">
      </div>
      <div
        class="absolute top-40 right-20 w-24 h-24 bg-gradient-to-r from-blue-500 to-blue-700 rounded-full opacity-15 animate-float-reverse">
      </div>
      <div
        class="absolute bottom-32 left-1/4 w-40 h-40 bg-gradient-to-r from-blue-300 to-blue-500 rounded-full opacity-8 animate-float">
      </div>
      <div
        class="absolute top-1/3 right-1/3 w-20 h-20 bg-gradient-to-r from-blue-600 to-blue-800 rounded-full opacity-12 animate-float-reverse">
      </div>

      <!-- Morphing Background Shapes -->
      <div class="absolute top-0 left-0 w-96 h-96 bg-gradient-to-br from-blue-600/5 to-blue-800/5 animate-morph">
      </div>
      <div class="absolute bottom-0 right-0 w-80 h-80 bg-gradient-to-tl from-blue-500/5 to-blue-700/5 animate-morph"
        style="animation-delay: 2s;"></div>

      <div class="relative z-10 max-w-4xl mx-auto py-12 px-6">
        <div class="text-center mb-12">
          <h1 class="text-5xl font-bold text-white mb-4 enhanced-text">Complete Your Institution Profile</h1>
          <p class="text-xl text-white/80">Join our network of educational institutions</p>
        </div>

        <!-- Step Progress Indicator -->
        <div class="flex justify-center mb-12">
          <div class="flex items-center space-x-4">
            <div v-for="step in totalSteps" :key="step" class="flex items-center">
              <div @click="goToStep(step)" :class="[
                'w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg cursor-pointer transition-all duration-300',
                currentStep === step ? 'bg-gradient-to-br from-teal-500 to-teal-600 text-white scale-110 animate-pulse-glow' :
                  currentStep > step ? 'bg-emerald-500 text-white' : 'glass border-white/30 text-white/60'
              ]">
                <svg v-if="currentStep > step" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd"></path>
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
            <div class="bg-white rounded shadow p-6">
              <h2 class="text-xl font-semibold mb-4">Institution Details</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <InputLabel for="institution_name">Institution Name <span class="text-red-500">*</span></InputLabel>
                  <TextInput id="institution_name" v-model="form.institution_name" type="text" required
                    class="mt-1 block w-full" />
                  <InputError :message="form.errors.institution_name" />
                </div>
                <div>
                  <div class="flex items-center gap-1">
                    <InputLabel for="institution_type">Institution Type <span class="text-red-500">*</span></InputLabel>
                  </div>
                  <select id="institution_type" v-model="form.institution_type"
                    class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm transition duration-300 ease-in-out transform hover:shadow-lg"
                    required>
                    <option value="college">College</option>
                    <option value="university">University</option>
                    <option value="institution">Institution</option>
                  </select>
                  <InputError class="mt-2" :message="form.errors.institution_type" />
                </div>
                <div>
                  <InputLabel for="institution_address">Institution Address <span class="text-red-500">*</span>
                  </InputLabel>
                  <TextInput id="institution_address" v-model="form.institution_address" type="text" required
                    class="mt-1 block w-full" />
                  <InputError :message="form.errors.institution_address" />
                </div>
                <div>
                  <InputLabel for="email">Email <span class="text-red-500">*</span></InputLabel>
                  <TextInput id="email" v-model="form.email" type="email" required class="mt-1 block w-full" />
                  <InputError :message="form.errors.email" />
                </div>
                <div>
                  <InputLabel for="mobile_number">
                    Mobile Number <span class="text-red-500">*</span>
                  </InputLabel>
                  <TextInput id="mobile_number" v-model="formattedMobileNumber" type="text" required
                    class="mt-1 block w-full" placeholder="+63 912 345 6789" />
                  <InputError :message="form.errors.company_mobile_phone" />
                </div>
                <div>
                  <InputLabel for="telephone_number" value="Telephone Number" />
                  <TextInput id="telephone_number" v-model="formattedTelephoneNumber" type="text"
                    class="mt-1 block w-full" placeholder="(02) 123-4567" />
                  <InputError :message="form.errors.telephone_number" />
                </div>

                <div>
                  <InputLabel for="institution_career_officer_first_name">Career Officer First Name <span
                      class="text-red-500">*</span></InputLabel>
                  <TextInput id="institution_president_first_name" v-model="form.institution_career_officer_first_name"
                    type="text" required class="mt-1 block w-full" />
                  <InputError :message="form.errors.institution_career_officer_first_name" />
                </div>
                <div>
                  <InputLabel for="institution_career_officer_last_name">Career Officer Last Name <span
                      class="text-red-500">*</span>
                  </InputLabel>
                  <TextInput id="institution_career_officer_last_name"
                    v-model="form.institution_career_officer_last_name" type="text" required
                    class="mt-1 block w-full" />
                  <InputError :message="form.errors.institution_president_last_name" />
                </div>

                <div>
                  <InputLabel for="institution_president_first_name">President First Name <span
                      class="text-red-500">*</span></InputLabel>
                  <TextInput id="institution_president_first_name" v-model="form.institution_president_first_name"
                    type="text" required class="mt-1 block w-full" />
                  <InputError :message="form.errors.institution_president_first_name" />
                </div>
                <div>
                  <InputLabel for="institution_president_last_name">President Last Name <span
                      class="text-red-500">*</span>
                  </InputLabel>
                  <TextInput id="institution_president_last_name" v-model="form.institution_president_last_name"
                    type="text" required class="mt-1 block w-full" />
                  <InputError :message="form.errors.institution_president_last_name" />
                </div>
              </div>
            </div>

          </div>

          <!-- Step 2: Verification Document -->
          <div v-show="currentStep === 2" class="glass rounded-2xl p-8 reveal">
            <div class="flex items-center mb-6">
              <div class="w-12 h-12 gradient-card rounded-xl flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                  </path>
                </svg>
              </div>
              <div>
                <h2 class="text-2xl font-bold text-white neon-text">Verification Document</h2>
                <p class="text-white/70">Upload your company verification document</p>
              </div>
            </div>
            <div class="space-y-4">
              <div>
                <InputLabel for="verification_file" class="text-white font-medium">
                  Upload Document <span class="text-pink-400">*</span>
                </InputLabel>
                <input id="verification_file" type="file"
                  class="mt-2 block w-full glass border-white/20 text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gradient-to-r file:from-cyan-500 file:to-purple-500 file:text-white hover:file:from-cyan-600 hover:file:to-purple-600 focus:border-cyan-400 focus:ring-cyan-400"
                  @change="onFileChange" required accept=".pdf,.jpg,.jpeg,.png" />
                <InputError :message="form.errors.verification_file" class="text-pink-300" />
                <div v-if="form.verification_file" class="mt-2 text-sm text-cyan-300">
                  Selected: {{ form.verification_file.name }}
                </div>
                <p class="text-white/50 text-sm mt-2">Accepted formats: PDF, JPG, JPEG, PNG</p>
              </div>
            </div>
          </div>

          <!-- Navigation Buttons -->
          <div class="flex justify-between items-center mt-8">
            <button v-if="currentStep > 1" type="button" @click="prevStep"
              class="glass border-white/20 text-white px-6 py-3 rounded-xl font-medium hover:bg-white/10 transition-all duration-300 flex items-center space-x-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
              <span>Previous</span>
            </button>
            <div v-else></div>

            <div class="flex space-x-4">
              <button v-if="currentStep < totalSteps" type="button" @click="nextStep" :disabled="!canProceed"
                class="gradient-cta px-6 py-3 rounded-xl font-medium text-white hover:scale-105 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2">
                <span>Next Step</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </button>

              <div v-if="Object.keys(form.errors).length" class="mb-4 text-red-600">
                <div v-for="(error, key) in form.errors" :key="key">{{ error }}</div>
              </div>

              <button v-if="currentStep === totalSteps" type="submit" :disabled="form.processing || !canProceed"
                class="gradient-cta hover-rainbow px-8 py-3 rounded-xl font-bold text-white transform hover:scale-105 animate-pulse-glow disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2">
                <svg v-if="form.processing" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor"
                    d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                  </path>
                </svg>
                <span v-if="form.processing">Saving...</span>
                <span v-else>Save Information</span>
              </button>
            </div>
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

  0%,
  100% {
    transform: translateY(0px) rotate(0deg);
  }

  50% {
    transform: translateY(-20px) rotate(10deg);
  }
}

@keyframes float-reverse {

  0%,
  100% {
    transform: translateY(0px) rotate(0deg);
  }

  50% {
    transform: translateY(20px) rotate(-5deg);
  }
}

@keyframes pulse-glow {
  from {
    box-shadow: 0 0 20px rgba(20, 184, 166, 0.4);
  }

  to {
    box-shadow: 0 0 30px rgba(20, 184, 166, 0.8);
  }
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

  0%,
  100% {
    border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
  }

  50% {
    border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%;
  }
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