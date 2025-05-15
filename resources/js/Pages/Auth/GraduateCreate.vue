<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref, computed } from 'vue';

defineProps({
  programs: Array,
  schoolYears: Array,
  institutions: Array,
});

const form = useForm({
  graduate_first_name: '',
  graduate_last_name: '',
  graduate_middle_initial: '',
  gender: '',
  dob: '',
  contact_number: '',
  email: '',
  graduate_school_graduated_from: '',
  graduate_program_completed: '',
  graduate_year_graduated: '',
  employment_status: '',
  current_job_title: '',
});

const formattedContactNumber = computed({
  get: () => {
    const raw = form.contact_number.replace(/\D/g, '');
    const cleaned = raw.length > 10 ? raw.slice(0, 10) : raw;
    return `+63 ${cleaned.replace(/(\d{3})(\d{3})(\d{4})/, "$1 $2 $3")}`.trim();
  },
  set: (value) => {
    let raw = value.replace(/\D/g, "");
    if (raw.startsWith("63")) raw = raw.slice(2);
    if (raw.startsWith("0")) raw = raw.slice(1);
    form.contact_number = raw.slice(0, 10);
  },
});

const handleEmploymentStatus = () => {
  if (form.employment_status === 'Unemployed') {
    form.current_job_title = 'N/A';
  }
};

const submit = () => {
  form.post(route('institution.graduate.store'), {
    onSuccess: () => {
      alert("Graduate added successfully. Login credentials were generated automatically.");
      form.reset();
    },
  });
};
</script>

<template>
    <Head title="Add Graduate" />
  
    <AuthenticationCard>
      <template #logo>
        <AuthenticationCardLogo />
      </template>
  
      <template #registerForm>
        <form @submit.prevent="submit" class="space-y-10 text-gray-700 max-w-4xl mx-auto">
          <!-- Header -->
          <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Graduate Registration</h1>
            <p class="text-sm text-gray-500">Fill in the graduate's personal and academic details</p>
          </div>
  
          <!-- Personal Info Section -->
          <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 space-y-4">
            <h3 class="text-lg font-semibold border-b pb-2">Personal Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <InputLabel for="graduate_first_name" value="First Name" />
                <TextInput id="graduate_first_name" v-model="form.graduate_first_name" type="text" class="mt-1 block w-full" required />
                <InputError :message="form.errors.graduate_first_name" />
              </div>
              <div>
                <InputLabel for="graduate_last_name" value="Last Name" />
                <TextInput id="graduate_last_name" v-model="form.graduate_last_name" type="text" class="mt-1 block w-full" required />
                <InputError :message="form.errors.graduate_last_name" />
              </div>
              <div>
                <InputLabel for="graduate_middle_initial" value="Middle Initial" />
                <TextInput id="graduate_middle_initial" v-model="form.graduate_middle_initial" type="text" class="mt-1 block w-full" required />
                <InputError :message="form.errors.graduate_middle_initial" />
              </div>
              <div>
                <InputLabel for="gender" value="Gender" />
                <select id="gender" v-model="form.gender" class="mt-1 block w-full border-gray-300 rounded-md" required>
                  <option value="">Select Gender</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                </select>
                <InputError :message="form.errors.gender" />
              </div>
              <div>
                <InputLabel for="dob" value="Date of Birth" />
                <TextInput id="dob" v-model="form.dob" type="date" class="mt-1 block w-full" required />
                <InputError :message="form.errors.dob" />
              </div>
              <div>
                <InputLabel for="email" value="Email Address" />
                <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" required />
                <InputError :message="form.errors.email" />
              </div>
              <div>
                <InputLabel for="contact_number" value="Contact Number" />
                <TextInput id="contact_number" v-model="formattedContactNumber" type="text" class="mt-1 block w-full" required />
                <InputError :message="form.errors.contact_number" />
              </div>
            </div>
          </div>
  
          <!-- Academic Info Section -->
          <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 space-y-4">
            <h3 class="text-lg font-semibold border-b pb-2">Academic Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <InputLabel for="graduate_school_graduated_from" value="School Graduated From" />
                <select id="graduate_school_graduated_from" v-model="form.graduate_school_graduated_from" class="mt-1 block w-full border-gray-300 rounded-md" required>
                  <option value="">Select School</option>
                  <option v-for="inst in institutions" :key="inst.id" :value="inst.id">{{ inst.institution_name }}</option>
                </select>
                <InputError :message="form.errors.graduate_school_graduated_from" />
              </div>
              <div>
                <InputLabel for="graduate_program_completed" value="Program Completed" />
                <select id="graduate_program_completed" v-model="form.graduate_program_completed" class="mt-1 block w-full border-gray-300 rounded-md" required>
                  <option value="">Select Program</option>
                  <option v-for="program in programs" :key="program.id" :value="program.name">{{ program.name }}</option>
                </select>
                <InputError :message="form.errors.graduate_program_completed" />
              </div>
              <div>
                <InputLabel for="graduate_year_graduated" value="Year Graduated" />
                <select id="graduate_year_graduated" v-model="form.graduate_year_graduated" class="mt-1 block w-full border-gray-300 rounded-md" required>
                  <option value="">Select Year</option>
                  <option v-for="year in schoolYears" :key="year.id" :value="year.school_year_range">{{ year.school_year_range }}</option>
                </select>
                <InputError :message="form.errors.graduate_year_graduated" />
              </div>
            </div>
          </div>
  
          <!-- Employment Info Section -->
          <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 space-y-4">
            <h3 class="text-lg font-semibold border-b pb-2">Employment Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <InputLabel for="employment_status" value="Employment Status" />
                <select id="employment_status" v-model="form.employment_status" @change="handleEmploymentStatus" class="mt-1 block w-full border-gray-300 rounded-md" required>
                  <option value="">Select Status</option>
                  <option value="Employed">Employed</option>
                  <option value="Underemployed">Underemployed</option>
                  <option value="Unemployed">Unemployed</option>
                </select>
                <InputError :message="form.errors.employment_status" />
              </div>
              <div v-if="form.employment_status !== 'Unemployed'">
                <InputLabel for="current_job_title" value="Current Job Title" />
                <TextInput id="current_job_title" v-model="form.current_job_title" type="text" class="mt-1 block w-full" required />
                <InputError :message="form.errors.current_job_title" />
              </div>
            </div>
          </div>
  
          <!-- Submit -->
          <div class="flex justify-end pt-4">
            <PrimaryButton class="px-6 py-2 text-lg" :disabled="form.processing">
              Add Graduate
            </PrimaryButton>
          </div>
        </form>
      </template>
    </AuthenticationCard>
  </template>
  