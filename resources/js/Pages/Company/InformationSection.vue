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
});

const showModal = ref(false);

// Composables for formatted numbers
const { formattedMobileNumber } = useFormattedMobileNumber(form, 'company_mobile_phone');
const { formattedTelephoneNumber } = useFormattedTelephoneNumber(form, 'telephone_number');
const { formattedMobileNumber: formattedHRMobileNumber } = useFormattedMobileNumber(form, 'mobile_number');

// Filter categories based on selected sector
const filteredCategories = computed(() =>
  props.categories.filter(cat => cat.sector_id == form.sector_id)
);

function onFileChange(e) {
  form.verification_file = e.target.files[0];
}

function submit() {
  form.post(route('company.information.save'), {
    onSuccess: () => {
      showModal.value = true;
    }
  });
}

function goToProfile() {
  window.location.href = route('company.profile');
}
</script>

<template>
  <AppLayout>
    <div class="max-w-3xl mx-auto py-10">
      <h1 class="text-3xl font-bold mb-6">Complete Your Company Profile</h1>
      <form @submit.prevent="submit" class="space-y-8" enctype="multipart/form-data">
        <!-- Company Details -->
        <div class="bg-white rounded shadow p-6">
          <h2 class="text-xl font-semibold mb-4">Business Details</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <InputLabel for="company_name">
                Business Name <span class="text-red-500">*</span>
              </InputLabel>
              <TextInput id="company_name" v-model="form.company_name" type="text" required class="mt-1 block w-full" />
              <InputError :message="form.errors.company_name" />
            </div>
            <div>
              <InputLabel for="sector_id">
                Sector <span class="text-red-500">*</span>
              </InputLabel>
              <select id="sector_id" v-model="form.sector_id" required class="mt-1 block w-full border-gray-300 rounded">
                <option value="">Select Sector</option>
                <option v-for="sector in props.sectors" :key="sector.id" :value="sector.id">
                  {{ sector.name }}
                </option>
              </select>
              <InputError :message="form.errors.sector_id" />
            </div>
            <div>
              <InputLabel for="category_id">
                Category <span class="text-red-500">*</span>
              </InputLabel>
              <select id="category_id" v-model="form.category_id" :disabled="!form.sector_id" required class="mt-1 block w-full border-gray-300 rounded">
                <option value="">Select Category</option>
                <option v-for="cat in filteredCategories" :key="cat.id" :value="cat.id">
                  {{ cat.name }}
                </option>
              </select>
              <InputError :message="form.errors.category_id" />
            </div>
            <div>
              <InputLabel for="company_street_address">
                Street Address <span class="text-red-500">*</span>
              </InputLabel>
              <TextInput id="company_street_address" v-model="form.company_street_address" type="text" required class="mt-1 block w-full" />
              <InputError :message="form.errors.company_street_address" />
            </div>
            <div>
              <InputLabel for="company_brgy">
                Barangay <span class="text-red-500">*</span>
              </InputLabel>
              <TextInput id="company_brgy" v-model="form.company_brgy" type="text" required class="mt-1 block w-full" />
              <InputError :message="form.errors.company_brgy" />
            </div>
            <div>
              <InputLabel for="company_city">
                City <span class="text-red-500">*</span>
              </InputLabel>
              <TextInput id="company_city" v-model="form.company_city" type="text" required class="mt-1 block w-full" />
              <InputError :message="form.errors.company_city" />
            </div>
            <div>
              <InputLabel for="company_province">
                Province <span class="text-red-500">*</span>
              </InputLabel>
              <TextInput id="company_province" v-model="form.company_province" type="text" required class="mt-1 block w-full" />
              <InputError :message="form.errors.company_province" />
            </div>
            <div>
              <InputLabel for="company_zip_code">
                ZIP Code <span class="text-red-500">*</span>
              </InputLabel>
              <TextInput id="company_zip_code" v-model="form.company_zip_code" type="text" required class="mt-1 block w-full" />
              <InputError :message="form.errors.company_zip_code" />
            </div>
          </div>
        </div>

        <!-- Company Contact -->
        <div class="bg-white rounded shadow p-6">
          <h2 class="text-xl font-semibold mb-4">Company Contact Information</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <InputLabel for="company_email">
                Email Address <span class="text-red-500">*</span>
              </InputLabel>
              <TextInput id="company_email" v-model="form.company_email" type="email" required class="mt-1 block w-full" />
              <InputError :message="form.errors.company_email" />
            </div>
            <div>
              <InputLabel for="company_mobile_phone">
                Mobile Number <span class="text-red-500">*</span>
              </InputLabel>
              <TextInput
                id="company_mobile_phone"
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
          </div>
        </div>

        <!-- HR Details -->
        <div class="bg-white rounded shadow p-6">
          <h2 class="text-xl font-semibold mb-4">HR Officer Information</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <InputLabel for="first_name">
                First Name <span class="text-red-500">*</span>
              </InputLabel>
              <TextInput id="first_name" v-model="form.first_name" type="text" required class="mt-1 block w-full" />
              <InputError :message="form.errors.first_name" />
            </div>
            <div>
              <InputLabel for="last_name">
                Last Name <span class="text-red-500">*</span>
              </InputLabel>
              <TextInput id="last_name" v-model="form.last_name" type="text" required class="mt-1 block w-full" />
              <InputError :message="form.errors.last_name" />
            </div>
            <div>
              <InputLabel for="middle_name" value="Middle Name" />
              <TextInput id="middle_name" v-model="form.middle_name" type="text" class="mt-1 block w-full" />
              <InputError :message="form.errors.middle_name" />
            </div>
            <div>
              <InputLabel for="gender">
                Gender <span class="text-red-500">*</span>
              </InputLabel>
              <select id="gender" v-model="form.gender" required class="mt-1 block w-full border-gray-300 rounded">
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
              <InputError :message="form.errors.gender" />
            </div>
            <div>
              <InputLabel for="dob">
                Date of Birth <span class="text-red-500">*</span>
              </InputLabel>
              <TextInput id="dob" v-model="form.dob" type="date" required class="mt-1 block w-full" />
              <InputError :message="form.errors.dob" />
            </div>
            <div>
              <InputLabel for="email">
                HR Email Address <span class="text-red-500">*</span>
              </InputLabel>
              <TextInput id="email" v-model="form.email" type="email" required class="mt-1 block w-full" />
              <InputError :message="form.errors.email" />
            </div>
            <div>
              <InputLabel for="mobile_number">
                HR Mobile Number <span class="text-red-500">*</span>
              </InputLabel>
              <TextInput
                id="mobile_number"
                v-model="formattedHRMobileNumber"
                type="text"
                required
                class="mt-1 block w-full"
                placeholder="+63 912 345 6789"
              />
              <InputError :message="form.errors.mobile_number" />
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
              Your company information has been saved.<br>
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
