<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { useFormattedMobileNumber } from '@/Composables/useFormattedMobileNumber.js';
import { useFormattedTelephoneNumber } from '@/Composables/useFormattedTelephoneNumber.js';
import { useFormattedBIRTIN } from '@/Composables/useFormattedBIRTIN.js';
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const props = defineProps({
  email: String,
  sectors: Array,
  categories: Array,
});

const form = useForm({
  company_name: '',
  sector_id: '',
  category_id: '',
  company_street_address: '',
  company_brgy: '',
  company_city: '',
  company_province: '',
  company_zip_code: '',
  company_email: '',
  company_mobile_phone: '',
  telephone_number: '',
  first_name: '',
  last_name: '',
  middle_name: '',
  gender: '',
  dob: '',
  email: props.email || '',
  mobile_number: '',
  verification_file: null,
  bir_tin: '',
  company_logo: null,
});

const showModal = ref(false);

// Step-by-step functionality
const currentStep = ref(1);
const totalSteps = ref(4);

// Composables for formatted numbers
const { formattedMobileNumber } = useFormattedMobileNumber(form, 'company_mobile_phone');
const { formattedTelephoneNumber } = useFormattedTelephoneNumber(form, 'telephone_number');
const { formattedMobileNumber: formattedHRMobileNumber } = useFormattedMobileNumber(form, 'mobile_number');
const { formattedBIRTIN } = useFormattedBIRTIN(form, 'bir_tin');

// Filter categories based on selected sector
const filteredCategories = computed(() =>
  props.categories.filter(cat => cat.sector_id == form.sector_id)
);

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

// Step validation
const isStepValid = computed(() => {
  switch (currentStep.value) {
    case 1: // Company Details
      return form.company_name && form.sector_id && form.category_id && 
             form.company_street_address && form.company_brgy && 
             form.company_city && form.company_province && form.company_zip_code;
    case 2: // Company Contact
      return form.company_email && form.company_mobile_phone;
    case 3: // HR Details
      return form.first_name && form.last_name && form.gender && 
             form.dob && form.email && form.mobile_number;
    case 4: // Verification
      // Require BIR TIN, Mayor/Business Permit, and Company Logo
      return !!form.bir_tin && !!form.verification_file && !!form.company_logo;
    default:
      return false;
  }
});

const canProceed = computed(() => {
  return isStepValid.value;
});

const today = new Date();
const maxDob = computed(() => {
  return new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
});

function onFileChange(e) {
  form.verification_file = e.target.files[0];
}

function onAnyFileChange(field, e) {
  form[field] = e.target.files[0];
}

function submit() {
  if (currentStep.value === totalSteps.value && canProceed.value) {
    form.post(route('company.information.save'), {
      onSuccess: () => {
        showModal.value = true;
      }
    });
  }
}

function goToProfile() {
  window.location.href = route('company.profile');
}
</script>

<template>
  <AppLayout>
    <!-- Clean gradient background -->
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 relative overflow-hidden">
      
      <div class="max-w-3xl mx-auto py-10 relative z-10">
        <!-- Modern header with neon text -->
        <div class="text-center mb-8 reveal">
          <h1 class="text-4xl font-bold text-slate-800 mb-4">
            Complete Your Company Profile
          </h1>
          <p class="text-slate-600 text-lg mb-8">Build your business presence with us</p>
        

          <!-- Step Progress Bar -->
          <div class="flex justify-center items-center space-x-4 mb-8">
            <div v-for="step in totalSteps" :key="step"class="flex items-center">
              <div 
                @click="goToStep(step)"
                :class="['w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold cursor-pointer transition-all duration-300',
                currentStep >= step ? 'bg-emerald-500 text-white shadow-lg' : 'bg-gray-200 text-gray-500 hover:bg-gray-300']">
                {{ step }}
              </div>
              <div v-if="step < totalSteps":class="['w-16 h-1 mx-2 rounded transition-all duration-300',currentStep > step ? 'bg-emerald-500' : 'bg-gray-300']">
              </div>
            </div>
          </div>
        </div>
        
        <form @submit.prevent="submit" class="space-y-8" enctype="multipart/form-data">
        <!-- Step 1: Company Details -->
        <div v-show="currentStep === 1" class="bg-white rounded-2xl p-8 shadow-lg border border-gray-200 reveal">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
              </svg>
            </div>
            <div>
              <h2 class="text-2xl font-bold text-slate-800">Business Details</h2>
              <p class="text-slate-600">Tell us about your company</p>
            </div>
          </div>
          <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
              <div>
                <InputLabel for="company_name" class="text-slate-800 font-medium">
                  Business Name <span class="text-red-500">*</span>
                </InputLabel>
                <TextInput id="company_name" v-model="form.company_name" type="text" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.company_name" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="sector_id" class="text-slate-800 font-medium">
                  Sector <span class="text-red-500">*</span>
                </InputLabel>
                <select id="sector_id" v-model="form.sector_id" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 focus:border-blue-400 focus:ring-blue-400 rounded-lg">
                  <option value="" class="bg-white">Select Sector</option>
                  <option v-for="sector in props.sectors" :key="sector.id" :value="sector.id" class="bg-white">
                    {{ sector.name }}
                  </option>
                </select>
                <InputError :message="form.errors.sector_id" class="text-red-600" />
              </div>
            </div>
            <div>
              <InputLabel for="category_id" class="text-slate-800 font-medium">
                Category <span class="text-red-500">*</span>
              </InputLabel>
              <select id="category_id" v-model="form.category_id" :disabled="!form.sector_id" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 focus:border-blue-400 focus:ring-blue-400 rounded-lg disabled:opacity-50">
                <option value="" class="bg-white">Select Category</option>
                <option v-for="cat in filteredCategories" :key="cat.id" :value="cat.id" class="bg-white">
                  {{ cat.name }}
                </option>
              </select>
              <InputError :message="form.errors.category_id" class="text-red-600" />
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="md:col-span-2">
                <InputLabel for="company_street_address" class="text-slate-800 font-medium">
                  Street Address <span class="text-red-500">*</span>
                </InputLabel>
                <TextInput id="company_street_address" v-model="form.company_street_address" type="text" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.company_street_address" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="company_brgy" class="text-slate-800 font-medium">
                  Barangay <span class="text-red-500">*</span>
                </InputLabel>
                <TextInput id="company_brgy" v-model="form.company_brgy" type="text" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.company_brgy" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="company_city" class="text-slate-800 font-medium">
                  City <span class="text-red-500">*</span>
                </InputLabel>
                <TextInput id="company_city" v-model="form.company_city" type="text" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.company_city" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="company_province" class="text-slate-800 font-medium">
                  Province <span class="text-red-500">*</span>
                </InputLabel>
                <TextInput id="company_province" v-model="form.company_province" type="text" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.company_province" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="company_zip_code" class="text-slate-800 font-medium">
                  ZIP Code <span class="text-red-500">*</span>
                </InputLabel>
                <TextInput id="company_zip_code" v-model="form.company_zip_code" type="text" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.company_zip_code" class="text-red-600" />
              </div>
            </div>
          </div>
        </div>

        <!-- Step 2: Company Contact -->
        <div v-show="currentStep === 2" class="bg-white rounded-2xl p-8 shadow-lg border border-gray-200 reveal">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
              </svg>
            </div>
            <div>
              <h2 class="text-2xl font-bold text-slate-800">Company Contact Information</h2>
              <p class="text-slate-600">How can we reach your company?</p>
            </div>
          </div>
          <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <InputLabel for="company_email" class="text-slate-800 font-medium">
                  Email Address <span class="text-red-500">*</span>
                </InputLabel>
                <TextInput id="company_email" v-model="form.company_email" type="email" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.company_email" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="company_mobile_phone" class="text-slate-800 font-medium">
                  Mobile Number <span class="text-red-500">*</span>
                </InputLabel>
                <TextInput
                  id="company_mobile_phone"
                  v-model="formattedMobileNumber"
                  type="text"
                  required
                  class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400"
                  placeholder="+63 912 345 6789"
                />
                <InputError :message="form.errors.company_mobile_phone" class="text-red-600" />
              </div>
            </div>
            <div>
              <InputLabel for="telephone_number" class="text-slate-800 font-medium" value="Telephone Number" />
              <TextInput
                id="telephone_number"
                v-model="formattedTelephoneNumber"
                type="text"
                class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400"
                placeholder="(02) 123-4567"
              />
              <InputError :message="form.errors.telephone_number" class="text-red-600" />
            </div>
          </div>
        </div>

        <!-- Step 3: HR Details -->
        <div v-show="currentStep === 3" class="bg-white rounded-2xl p-8 shadow-lg border border-gray-200 reveal">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
            </div>
            <div>
              <h2 class="text-2xl font-bold text-slate-800">HR Officer Information</h2>
              <p class="text-slate-600">Details about your HR representative</p>
            </div>
          </div>
          <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <InputLabel for="first_name" class="text-slate-800 font-medium">
                  First Name <span class="text-red-500">*</span>
                </InputLabel>
                <TextInput id="first_name" v-model="form.first_name" type="text" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.first_name" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="middle_name" class="text-slate-800 font-medium" value="Middle Name" />
                <TextInput id="middle_name" v-model="form.middle_name" type="text" class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.middle_name" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="last_name" class="text-slate-800 font-medium">
                  Last Name <span class="text-red-500">*</span>
                </InputLabel>
                <TextInput id="last_name" v-model="form.last_name" type="text" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.last_name" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="gender" class="text-slate-800 font-medium">
                  Gender <span class="text-red-500">*</span>
                </InputLabel>
                <select id="gender" v-model="form.gender" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 focus:border-blue-400 focus:ring-blue-400 rounded-lg">
                  <option value="" class="bg-white">Select Gender</option>
                  <option value="Male" class="bg-white">Male</option>
                  <option value="Female" class="bg-white">Female</option>
                </select>
                <InputError :message="form.errors.gender" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="dob" class="text-slate-800 font-medium">
                  Date of Birth <span class="text-red-500">*</span>
                </InputLabel>
                <Datepicker
                  v-model="form.dob"
                  :enable-time-picker="false"
                  :max-date="maxDob"
                  input-class-name="w-full p-2 border rounded-lg mt-2 bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400"
                  placeholder="Select date"
                  required
                />
                <InputError :message="form.errors.dob" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="email" class="text-slate-800 font-medium">
                  HR Email Address <span class="text-red-500">*</span>
                </InputLabel>
                <TextInput id="email" v-model="form.email" type="email" required class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400" />
                <InputError :message="form.errors.email" class="text-red-600" />
              </div>
              <div>
                <InputLabel for="mobile_number" class="text-slate-800 font-medium">
                  HR Mobile Number <span class="text-red-500">*</span>
                </InputLabel>
                <TextInput
                  id="mobile_number"
                  v-model="formattedHRMobileNumber"
                  type="text"
                  required
                  class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400"
                  placeholder="+63 912 345 6789"
                />
                <InputError :message="form.errors.mobile_number" class="text-red-600" />
              </div>
            </div>
          </div>
        </div>

        <!-- Step 4: Verification Document -->
        <div v-show="currentStep === 4" class="bg-white rounded-2xl p-8 shadow-lg border border-gray-200 reveal">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
            <div>
              <h2 class="text-2xl font-bold text-slate-800">Verification Document</h2>
              <p class="text-slate-600">
                Please upload the required verification documents for your company, including your Mayor’s Permit/Business Permit, BIR TIN, and Company Logo.
              </p>
            </div>
          </div>
          <div class="space-y-6">
            <!-- BIR TIN -->
            <div>
              <InputLabel for="bir_tin" class="text-slate-800 font-medium">
                BIR TIN <span class="text-red-500">*</span>
              </InputLabel>
              <TextInput
                id="bir_tin"
                v-model="formattedBIRTIN"
                type="text"
                required
                inputmode="numeric"
                maxlength="15"  
                class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 placeholder-gray-400 focus:border-blue-400 focus:ring-blue-400"
                placeholder="___-___-___-___"
              />
              <InputError :message="form.errors.bir_tin" class="text-red-600" />
            </div>

            <!-- Mayor’s/Business Permit -->
            <div>
              <InputLabel for="verification_file" class="text-slate-800 font-medium">
                Upload Mayor’s/Business Permit <span class="text-red-500">*</span>
              </InputLabel>
              <input
                id="verification_file"
                type="file"
                class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gradient-to-r file:from-blue-500 file:to-blue-600 file:text-white hover:file:from-blue-600 hover:file:to-blue-700 focus:border-blue-400 focus:ring-blue-400"
                @change="e => onAnyFileChange('verification_file', e)"
                required
                accept=".pdf,.jpg,.jpeg,.png"
              />
              <InputError :message="form.errors.verification_file" class="text-red-600" />
              <div v-if="form.verification_file" class="mt-2 text-sm text-blue-600">
                Selected: {{ form.verification_file.name }}
              </div>
              <p class="text-slate-500 text-sm mt-2">Accepted formats: PDF, JPG, JPEG, PNG</p>
            </div>

            <!-- Company Logo -->
            <div>
              <InputLabel for="company_logo" class="text-slate-800 font-medium">
                Company Logo <span class="text-red-500">*</span>
              </InputLabel>
              <input
                id="company_logo"
                type="file"
                class="mt-2 block w-full bg-gray-50 border-gray-300 text-slate-800 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gradient-to-r file:from-blue-500 file:to-blue-600 file:text-white hover:file:from-blue-600 hover:file:to-blue-700 focus:border-blue-400 focus:ring-blue-400"
                @change="e => onAnyFileChange('company_logo', e)"
                required
                accept=".jpg,.jpeg,.png"
              />
              <InputError :message="form.errors.company_logo" class="text-red-600" />
              <div v-if="form.company_logo" class="mt-2 text-sm text-blue-600">
                Selected: {{ form.company_logo.name }}
              </div>
              <p class="text-slate-500 text-sm mt-2">Accepted formats: JPG, JPEG, PNG</p>
            </div>
          </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between items-center mt-8">
          <button
            v-if="currentStep > 1"
            type="button"
            @click="prevStep"
            class="bg-gray-100 border-gray-300 text-slate-700 px-6 py-3 rounded-xl font-medium hover:bg-gray-200 transition-all duration-300 flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            <span>Previous</span>
          </button>
          <div v-else></div>

          <div class="flex space-x-4">
            <button
              v-if="currentStep < totalSteps"
              type="button"
              @click="nextStep"
              :disabled="!canProceed"
              class="gradient-cta px-6 py-3 rounded-xl font-medium text-white hover:scale-105 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2">
              <span>Next Step</span>
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>
            
            <button
              v-if="currentStep === totalSteps"
              type="submit"
              :disabled="form.processing || !canProceed"
              class="gradient-cta hover-rainbow px-8 py-3 rounded-xl font-bold text-white transform hover:scale-105 animate-pulse-glow disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2">
              <svg v-if="form.processing" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span v-if="form.processing">Saving...</span>
              <span v-else>Save Information</span>
            </button>
          </div>
        </div>
      </form>

       <Modal v-model="showModal">
          <template #header>
              <h2 class="text-2xl font-bold text-emerald-600">Profile Saved!</h2>
          </template>
          <template #body>
              <p class="mb-6 text-gray-700">
                Your company information has been saved successfully!<br>
                You will now be redirected to your profile page.
              </p>
          </template>
          <template #footer>
              <button @click="goToProfile">Go to Profile</button>
          </template>
        </Modal>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Modern gradient backgrounds */
.gradient-bg {
    background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 25%, #2563eb 50%, #3b82f6 75%, #60a5fa 100%);
}

.gradient-card {
    background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
}

.gradient-feature {
    background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
}

.gradient-cta {
    background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
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
        box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
    }
    50% {
        box-shadow: 0 0 40px rgba(59, 130, 246, 0.6);
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

/* Reveal animation */
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

.reveal {
    animation: reveal 0.6s ease-out;
}

/* Blue hover effects */
.hover-blue:hover {
    background: linear-gradient(45deg, #1e40af, #2563eb, #3b82f6, #60a5fa, #93c5fd, #dbeafe);
    background-size: 400% 400%;
    animation: blue-shift 2s ease infinite;
}

@keyframes blue-shift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Light theme card styling */
.light-card {
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(0, 0, 0, 0.1);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Smooth transitions */
* {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Custom select styling */
select option {
    background: white;
    color: #1e293b;
}

/* Input focus effects */
input:focus, select:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    border-color: rgba(59, 130, 246, 0.5);
}
</style>

