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
  website: '',
  mobile_number: '',
  institution_president_first_name: '',
  institution_president_last_name: '',
  telephone_number: '',
});

const showModal = ref(false);

// Composables for formatted numbers
const { formattedMobileNumber } = useFormattedMobileNumber(form, 'company_mobile_phone');
const { formattedTelephoneNumber } = useFormattedTelephoneNumber(form, 'telephone_number');
const { formattedMobileNumber: formattedHRMobileNumber } = useFormattedMobileNumber(form, 'mobile_number');


function submit() {
  form.post(route('institution.information.save'), {
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
              <TextInput id="institution_name" v-model="form.institution_name" type="text" required class="mt-1 block w-full" />
              <InputError :message="form.errors.institution_name" />
            </div>
            <div>
              <InputLabel for="institution_type">Institution Type <span class="text-red-500">*</span></InputLabel>
              <TextInput id="institution_type" v-model="form.institution_type" type="text" required class="mt-1 block w-full" />
              <InputError :message="form.errors.institution_type" />
            </div>
            <div>
              <InputLabel for="institution_address">Institution Address <span class="text-red-500">*</span></InputLabel>
              <TextInput id="institution_address" v-model="form.institution_address" type="text" required class="mt-1 block w-full" />
              <InputError :message="form.errors.institution_address" />
            </div>
            <div>
              <InputLabel for="email">Email <span class="text-red-500">*</span></InputLabel>
              <TextInput id="email" v-model="form.email" type="email" required class="mt-1 block w-full" />
              <InputError :message="form.errors.email" />
            </div>
            <div>
              <InputLabel for="website">Website</InputLabel>
              <TextInput id="website" v-model="form.website" type="text" class="mt-1 block w-full" />
              <InputError :message="form.errors.website" />
            </div>
            <div>
              <InputLabel for="mobile_number">Mobile Number <span class="text-red-500">*</span></InputLabel>
              <TextInput id="mobile_number" v-model="form.mobile_number" type="text" required class="mt-1 block w-full" />
              <InputError :message="form.errors.mobile_number" />
            </div>
            <div>
              <InputLabel for="institution_president_first_name">President First Name <span class="text-red-500">*</span></InputLabel>
              <TextInput id="institution_president_first_name" v-model="form.institution_president_first_name" type="text" required class="mt-1 block w-full" />
              <InputError :message="form.errors.institution_president_first_name" />
            </div>
            <div>
              <InputLabel for="institution_president_last_name">President Last Name <span class="text-red-500">*</span></InputLabel>
              <TextInput id="institution_president_last_name" v-model="form.institution_president_last_name" type="text" required class="mt-1 block w-full" />
              <InputError :message="form.errors.institution_president_last_name" />
            </div>
            <div>
              <InputLabel for="telephone_number">Telephone Number</InputLabel>
              <TextInput id="telephone_number" v-model="form.telephone_number" type="text" class="mt-1 block w-full" />
              <InputError :message="form.errors.telephone_number" />
            </div>
          </div>
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