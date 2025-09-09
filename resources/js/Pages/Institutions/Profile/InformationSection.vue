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
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 relative overflow-hidden">

      <div class="max-w-4xl mx-auto py-10 relative z-10">
        <div class="text-center mb-8 reveal">
          <h1 class="text-4xl font-bold text-slate-800 mb-4">
            Complete Your Institution Profile
          </h1>
          <p class="text-slate-600 text-lg mb-8">Join our network of educational institutions</p>

        <!-- Step Progress Indicator -->
          <div class="flex justify-center items-center space-x-4 mb-8">
            <div v-for="step in totalSteps" :key="step" class="flex items-center">
              <div 
                @click="goToStep(step)"
                :class="[
                  'w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold cursor-pointer transition-all duration-300',
                  currentStep >= step ? 'bg-emerald-500 text-white shadow-lg' : 'bg-gray-200 text-gray-500 hover:bg-gray-300'
                ]">
                {{ step }}
              </div>
              <div 
                v-if="step < totalSteps"
                :class="[
                  'w-16 h-1 mx-2 rounded transition-all duration-300',
                  currentStep > step ? 'bg-emerald-500' : 'bg-gray-300'
                ]">
              </div>
            </div>
          </div>
        </div>
      

        <form @submit.prevent="submit" class="space-y-8">
          <!-- Step 1: Institution Details -->
            <div v-show="currentStep === 1" class="bg-white rounded-2xl p-8 shadow-lg border border-gray-200 reveal">
            <div class="flex items-center mb-6">
              <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
              </svg>
              </div>
              <div>
              <h2 class="text-2xl font-bold text-slate-800">Institution Details</h2>
              <p class="text-slate-600">Tell us about your institution</p>
              </div>
            </div>
            <div class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <InputLabel for="institution_name" class="text-slate-800 font-medium">
                Institution Name <span class="text-red-500">*</span>
                </InputLabel>
                <TextInput id="institution_name" v-model="form.institution_name" type="text" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.institution_name" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="institution_type" class="text-slate-800 font-medium">
                Institution Type <span class="text-red-500">*</span>
                </InputLabel>
                <TextInput id="institution_type" v-model="form.institution_type" type="text" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.institution_type" class="text-red-600" />
              </div>
              </div>
              <div>
              <InputLabel for="institution_address" class="text-slate-800 font-medium">
                Address <span class="text-red-500">*</span>
              </InputLabel>
              <TextInput id="institution_address" v-model="form.institution_address" type="text" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" />
              <InputError :message="form.errors.institution_address" class="text-red-600" />
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <InputLabel for="email" class="text-slate-800 font-medium">
                Email <span class="text-red-500">*</span>
                </InputLabel>
                <TextInput id="email" v-model="form.email" type="email" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.email" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="mobile_number" class="text-slate-800 font-medium">
                Mobile Number <span class="text-red-500">*</span>
                </InputLabel>
                <TextInput id="mobile_number" v-model="form.mobile_number" type="text" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" :value="formattedMobileNumber" />
                <InputError :message="form.errors.mobile_number" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="telephone_number" class="text-slate-800 font-medium">
                Telephone Number
                </InputLabel>
                <TextInput id="telephone_number" v-model="form.telephone_number" type="text" class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" :value="formattedTelephoneNumber" />
                <InputError :message="form.errors.telephone_number" class="text-red-600" />
              </div>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <InputLabel for="institution_president_first_name" class="text-slate-800 font-medium">
                President First Name <span class="text-red-500">*</span>
                </InputLabel>
                <TextInput id="institution_president_first_name" v-model="form.institution_president_first_name" type="text" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.institution_president_first_name" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="institution_president_last_name" class="text-slate-800 font-medium">
                President Last Name <span class="text-red-500">*</span>
                </InputLabel>
                <TextInput id="institution_president_last_name" v-model="form.institution_president_last_name" type="text" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.institution_president_last_name" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="institution_career_officer_first_name" class="text-slate-800 font-medium">
                Career Officer First Name <span class="text-red-500">*</span>
                </InputLabel>
                <TextInput id="institution_career_officer_first_name" v-model="form.institution_career_officer_first_name" type="text" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.institution_career_officer_first_name" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="institution_career_officer_last_name" class="text-slate-800 font-medium">
                Career Officer Last Name <span class="text-red-500">*</span>
                </InputLabel>
                <TextInput id="institution_career_officer_last_name" v-model="form.institution_career_officer_last_name" type="text" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.institution_career_officer_last_name" class="text-red-600" />
              </div>
              </div>
            </div>
            </div>

          <!-- Step 2: Verification Document -->
          <div v-show="currentStep === 2" class="bg-white rounded-2xl p-8 shadow-lg border border-gray-200 reveal">
            <div class="flex items-center mb-6">
              <div class="w-12 h-12 gradient-card rounded-xl flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                  </path>
                </svg>
              </div>
              <div>
                <h2 class="text-2xl font-bold text-slate-800">Verification Document</h2>
                <p class="text-slate-600">Upload your company verification document</p>
              </div>
            </div>
            <div class="space-y-4">
              <div>
                <InputLabel for="verification_file" class="text-slate-800 font-medium">
                  Upload Document <span class="text-red-500">*</span>
                </InputLabel>
                <input id="verification_file" type="file"
                  class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-emerald-500 file:text-white hover:file:bg-emerald-600 focus:border-emerald-500 focus:ring-emerald-500"
                  @change="onFileChange" required accept=".pdf,.jpg,.jpeg,.png" />
                <InputError :message="form.errors.verification_file" class="text-red-600" />
                <div v-if="form.verification_file" class="mt-2 text-sm text-emerald-600">
                  Selected: {{ form.verification_file.name }}
                </div>
                <p class="text-slate-500 text-sm mt-2">Accepted formats: PDF, JPG, JPEG, PNG</p>
              </div>
            </div>
          </div>

          <!-- Navigation Buttons -->
          <div class="flex justify-between items-center mt-8">
            <button v-if="currentStep > 1" type="button" @click="prevStep"
              class="bg-gray-100 border-gray-300 text-slate-700 px-6 py-3 rounded-xl font-medium hover:bg-gray-200 transition-all duration-300 flex items-center space-x-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
              </svg>
              <span>Previous</span>
            </button>
            <div v-else></div>

            <div class="flex space-x-4">
              <button v-if="currentStep < totalSteps" type="button" @click="nextStep"
              :disabled="!canProceed"
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
                class="bg-emerald-500 px-8 py-3 rounded-xl font-bold text-white hover:bg-emerald-600 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2">
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



.reveal {
  animation: reveal 0.6s ease-out;
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