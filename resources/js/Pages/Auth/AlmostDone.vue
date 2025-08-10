<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref, computed } from 'vue';

const { props } = usePage(); // Access the page props
const user = props.auth?.user || {};

// Define the form with profile fields
const form = useForm({
  first_name: user.first_name || '',
  last_name: user.last_name || '',
  location: '',
  has_experience: false,
  job_title: '',
  company_name: '',
  started_month: '',
  started_year: '',
  ended_month: '',
  ended_year: '',
  still_in_role: false,
  classification: '',
  sub_classification: '',
  visibility: 'standard', // Default visibility setting
});

// Available options for dropdowns
const months = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December'
];

const years = Array.from({ length: 50 }, (_, i) => new Date().getFullYear() - i);

// Classification options would typically come from props
const classifications = props.classifications || [];
const subClassifications = props.sub_classifications || [];

// Filtered options based on selections
const filteredSubClassifications = computed(() => {
  if (!form.classification) return [];
  return subClassifications.filter(subClass => subClass.classification_id == form.classification);
});

// Submit the form
const submit = () => {
  form.post(route('graduate.almostdone.store'), {
    onSuccess: () => {
      // Redirect to verification success page
      window.location.href = route('verification.success');
    },
  });
};
</script>

<template>
  <Head title="Almost Done" />

  <AuthenticationCard>
    <template #logo>
      <AuthenticationCardLogo />
    </template>

    <div class="p-6">
      <h1 class="text-2xl font-bold mb-1">Almost done</h1>
      <p class="text-gray-600 mb-6">Be found by employers. Start your Profile.</p>

      <form @submit.prevent="submit">
        <div class="grid grid-cols-2 gap-4 mb-4">
          <!-- First Name -->
          <div>
            <InputLabel for="first_name" value="First name" />
            <TextInput id="first_name" v-model="form.first_name" type="text" class="mt-1 block w-full" required />
            <InputError class="mt-1" :message="form.errors.first_name" />
          </div>

          <!-- Last Name -->
          <div>
            <InputLabel for="last_name" value="Last name" />
            <TextInput id="last_name" v-model="form.last_name" type="text" class="mt-1 block w-full" required />
            <InputError class="mt-1" :message="form.errors.last_name" />
          </div>
        </div>

        <!-- Location -->
        <div class="mb-4">
          <InputLabel for="location" value="Location" />
          <TextInput id="location" v-model="form.location" type="text" class="mt-1 block w-full" placeholder="Enter city or town" required />
          <InputError class="mt-1" :message="form.errors.location" />
        </div>

        <!-- Recent Experience Toggle -->
        <div class="mb-4">
          <h2 class="font-semibold mb-2">Recent experience</h2>
          <div class="flex items-center">
            <div class="form-check form-switch">
              <input 
                class="form-check-input appearance-none w-9 -ml-10 rounded-full float-left h-5 align-top bg-white bg-no-repeat bg-contain focus:outline-none cursor-pointer shadow-sm"
                type="checkbox"
                role="switch"
                id="has_experience"
                v-model="form.has_experience"
              >
              <label class="form-check-label inline-block text-gray-800 ml-2" for="has_experience">
                I have experience
              </label>
            </div>
          </div>
        </div>

        <!-- Experience Fields (shown only if has_experience is true) -->
        <div v-if="form.has_experience" class="space-y-4">
          <!-- Job Title -->
          <div>
            <InputLabel for="job_title" value="Job title" />
            <TextInput id="job_title" v-model="form.job_title" type="text" class="mt-1 block w-full" required />
            <InputError class="mt-1" :message="form.errors.job_title" />
          </div>

          <!-- Company Name -->
          <div>
            <InputLabel for="company_name" value="Company name" />
            <TextInput id="company_name" v-model="form.company_name" type="text" class="mt-1 block w-full" required />
            <InputError class="mt-1" :message="form.errors.company_name" />
          </div>

          <!-- Start Date -->
          <div>
            <InputLabel value="Started" />
            <div class="grid grid-cols-2 gap-4">
              <div>
                <select v-model="form.started_month" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                  <option value="" disabled>Month</option>
                  <option v-for="month in months" :key="month" :value="month">{{ month }}</option>
                </select>
              </div>
              <div>
                <select v-model="form.started_year" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                  <option value="" disabled>Year</option>
                  <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                </select>
              </div>
            </div>
          </div>

          <!-- End Date -->
          <div>
            <InputLabel value="Ended" />
            <div class="grid grid-cols-2 gap-4">
              <div>
                <select v-model="form.ended_month" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" :disabled="form.still_in_role">
                  <option value="" disabled>Month</option>
                  <option v-for="month in months" :key="month" :value="month">{{ month }}</option>
                </select>
              </div>
              <div>
                <select v-model="form.ended_year" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" :disabled="form.still_in_role">
                  <option value="" disabled>Year</option>
                  <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                </select>
              </div>
            </div>
            <div class="mt-2">
              <label class="inline-flex items-center">
                <input type="checkbox" v-model="form.still_in_role" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <span class="ml-2 text-sm text-gray-600">Still in role</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Preferred Classification -->
        <div class="mt-6">
          <h2 class="font-semibold mb-2">Preferred classification</h2>
          <div class="mb-4">
            <InputLabel for="classification" value="Classification" />
            <select id="classification" v-model="form.classification" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
              <option value="" disabled>Select classification</option>
              <option v-for="classification in classifications" :key="classification.id" :value="classification.id">
                {{ classification.name }}
              </option>
            </select>
          </div>

          <div class="mb-4">
            <InputLabel for="sub_classification" value="Sub-classification" />
            <select id="sub_classification" v-model="form.sub_classification" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
              <option value="" disabled>Select sub-classification</option>
              <option v-for="subClass in filteredSubClassifications" :key="subClass.id" :value="subClass.id">
                {{ subClass.name }}
              </option>
            </select>
          </div>
        </div>

        <!-- Be Found By Employers -->
        <div class="mt-6">
          <h2 class="font-semibold mb-2">Be found by employers</h2>
          <p class="text-sm text-gray-600 mb-4">Select your profile visibility. This can be changed or updated by employers and recruiters with job opportunities.</p>

          <div class="space-y-4">
            <!-- Standard Option -->
            <label class="flex items-start space-x-3 cursor-pointer">
              <input type="radio" v-model="form.visibility" value="standard" class="mt-1 border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
              <div>
                <span class="font-medium">Standard</span>
                <p class="text-sm text-gray-600">Employers can view my Profile and resume and can contact me about job opportunities.</p>
              </div>
            </label>

            <!-- Limited Option -->
            <label class="flex items-start space-x-3 cursor-pointer">
              <input type="radio" v-model="form.visibility" value="limited" class="mt-1 border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
              <div>
                <span class="font-medium">Limited</span>
                <p class="text-sm text-gray-600">Employers can view my Profile, but not my resume and can only contact me when I apply to their jobs.</p>
              </div>
            </label>

            <!-- Hidden Option -->
            <label class="flex items-start space-x-3 cursor-pointer">
              <input type="radio" v-model="form.visibility" value="hidden" class="mt-1 border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
              <div>
                <span class="font-medium">Hidden</span>
                <p class="text-sm text-gray-600">Employers cannot search for me. My Profile can only be seen by employers when I apply to their jobs.</p>
              </div>
            </label>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="mt-6">
          <PrimaryButton type="submit" class="w-auto px-6 py-2 bg-pink-600 hover:bg-pink-700">
            Save & continue
          </PrimaryButton>
        </div>
      </form>
    </div>
  </AuthenticationCard>
</template>

<style scoped>
.form-check-input:checked {
  background-color: #4f46e5;
  border-color: #4f46e5;
}

.form-check-input {
  background-position: left center;
  border: 1px solid rgba(0, 0, 0, 0.25);
  transition: background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-check-input:checked {
  background-position: right center;
}
</style>