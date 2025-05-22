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
  degrees: Array,
  programs: Array,
  schoolYears: Array,
});

const form = useForm({
  first_name: '',
  last_name: '',
  middle_name: '',
  gender: '',
  dob: '',
  contact_number: '',
  email: '',
  degree_id: '', // NEW
  graduate_program_completed: '',
  graduate_year_graduated: '',
  employment_status: '',
  current_job_title: '',
});

const handleEmploymentStatus = () => {
  if (form.employment_status === 'Unemployed') {
    form.current_job_title = 'N/A';
  }
};

// Filter programs based on selected degree
const filteredPrograms = computed(() => {
  if (!form.degree_id) return [];
  return props.programs.filter(p => String(p.degree_id) === String(form.degree_id));
});

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
              <InputLabel for="first_name" value="First Name" />
              <TextInput id="first_name" v-model="form.first_name" type="text" class="mt-1 block w-full" required />
              <InputError :message="form.errors.first_name" />
            </div>
            <div>
              <InputLabel for="last_name" value="Last Name" />
              <TextInput id="last_name" v-model="form.last_name" type="text" class="mt-1 block w-full" required />
              <InputError :message="form.errors.last_name" />
            </div>
            <div>
              <InputLabel for="middle_name" value="Middle Name" />
              <TextInput id="middle_name" v-model="form.middle_name" type="text" class="mt-1 block w-full" />
              <InputError :message="form.errors.middle_name" />
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
              <TextInput id="contact_number" v-model="form.contact_number" type="text" class="mt-1 block w-full" required />
              <InputError :message="form.errors.contact_number" />
            </div>
          </div>
        </div>
        <!-- Academic Info Section -->
        <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100 space-y-4">
          <h3 class="text-lg font-semibold border-b pb-2">Academic Information</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Degree Field -->
            <div>
              <InputLabel for="degree_id" value="Degree" />
              <select id="degree_id" v-model="form.degree_id" class="mt-1 block w-full border-gray-300 rounded-md" required>
                <option value="">Select Degree</option>
                <option v-for="degree in degrees" :key="degree.id" :value="degree.id">{{ degree.type }}</option>
              </select>
              <InputError :message="form.errors.degree_id" />
            </div>
            <!-- Program Completed -->
            <div>
              <InputLabel for="graduate_program_completed" value="Program Completed" />
              <select id="graduate_program_completed" v-model="form.graduate_program_completed" class="mt-1 block w-full border-gray-300 rounded-md" :disabled="!form.degree_id" required>
                <option value="">Select Program</option>
                <option v-for="program in programs.filter(p => String(p.degree_id) === String(form.degree_id))" :key="program.id" :value="program.name">
                  {{ program.name }}
                </option>
              </select>
              <InputError :message="form.errors.graduate_program_completed" />
            </div>
            <!-- Year Graduated -->
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
              <TextInput
                id="current_job_title"
                v-model="form.current_job_title"
                type="text"
                class="mt-1 block w-full"
                required
              />
              <InputError :message="form.errors.current_job_title" />
            </div>
          </div>
        </div>
        <!-- Submit Button -->
        <div class="flex justify-center">
          <PrimaryButton type="submit" class="w-full max-w-xs">
            Register Graduate
          </PrimaryButton>
        </div>
      </form>
    </template>
  </AuthenticationCard>
</template>
