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

// Composables for formatted numbers
const { formattedMobileNumber } = useFormattedMobileNumber(form, 'mobile_number');
const { formattedTelephoneNumber } = useFormattedTelephoneNumber(form, 'telephone_number');
const { formattedMobileNumber: formattedHRMobileNumber } = useFormattedMobileNumber(form, 'mobile_number');

function onFileChange(e) {
  form.verification_file = e.target.files[0];
console.log('Selected file:', form.verification_file); // <-- Add this
}

function submit() {
  console.log('Submitting...'); // Debug
  form.post(route('institution.information.save'), {
    forceFormData: true,
    onSuccess: () => {
      showModal.value = true;
    }
  });
}

function goToProfile() {
  window.location.href = route('institution.profile');
}
</script>

<template>
  <AppLayout>
    <div class="max-w-3xl mx-auto py-10">
      <h1 class="text-3xl font-bold mb-6">Complete Your Institution Profile</h1>
      <form @submit.prevent="submit" class="space-y-8">
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
              <InputLabel for="institution_address">Institution Address <span class="text-red-500">*</span></InputLabel>
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
              <TextInput
                id="mobile_number"
                v-model="formattedMobileNumber"
                type="text"
                required
                class="mt-1 block w-full"
                placeholder="+63 912 345 6789"
              />
              <InputError :message="form.errors.company_mobile_phone" />
            </div>
            <div>
              <InputLabel for="telephone_number" value="Telephone Number" />
              <TextInput
                id="telephone_number"
                v-model="formattedTelephoneNumber"
                type="text"
                class="mt-1 block w-full"
                placeholder="(02) 123-4567"
              />
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
              <InputLabel for="institution_career_officer_last_name">Career Officer Last Name <span class="text-red-500">*</span>
              </InputLabel>
              <TextInput id="institution_career_officer_last_name" v-model="form.institution_career_officer_last_name" type="text"
                required class="mt-1 block w-full" />
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
              <InputLabel for="institution_president_last_name">President Last Name <span class="text-red-500">*</span>
              </InputLabel>
              <TextInput id="institution_president_last_name" v-model="form.institution_president_last_name" type="text"
                required class="mt-1 block w-full" />
              <InputError :message="form.errors.institution_president_last_name" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded shadow p-6">
          <h2 class="text-xl font-semibold mb-4">Verification Document <span class="text-red-500">*</span></h2>
          <div>
            <InputLabel for="verification_file">
              Upload Document <span class="text-red-500">*</span>
            </InputLabel>
            <input
              id="verification_file"
              type="file"
              class="mt-1 block w-full border border-gray-300 rounded"
              @change="onFileChange"
              required
              accept=".pdf,.jpg,.jpeg,.png"
            />
            <InputError :message="form.errors.verification_file" />
            <div v-if="form.verification_file" class="mt-2 text-sm text-gray-600">
              Selected: {{ form.verification_file.name }}
            </div>
          </div>
        </div>

        <div v-if="Object.keys(form.errors).length" class="mb-4 text-red-600">
          <div v-for="(error, key) in form.errors" :key="key">{{ error }}</div>
        </div>

        <div class="flex justify-end">
          <PrimaryButton :disabled="form.processing">Save Information</PrimaryButton>
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
  </AppLayout>
</template>