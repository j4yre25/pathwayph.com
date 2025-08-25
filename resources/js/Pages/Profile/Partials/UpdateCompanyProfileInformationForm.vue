<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  user: {
    type: Object,
    default: () => ({}),
  },
  company: {
    type: Object,
    default: () => ({}),
  },
});

const form = useForm({
  _method: 'PUT',
  photo: null,
  cover_photo: null,
  company_name: props.company.company_name || '',
  company_email: props.company.company_email || '',
  company_street_address: props.company.company_street_address || '',
  company_brgy: props.company.company_brgy || '',
  company_city: props.company.company_city || '',
  company_province: props.company.company_province || '',
  company_zip_code: props.company.company_zip_code || '',
  company_contact_number: props.company.company_contact_number || '',
  telephone_number: props.company.telephone_number || '',
  company_description: props.company.company_description || '',
  profile_photo_path: props.company.profile_photo || '',
  cover_photo_path: props.company.cover_photo || '',
});

const photoPreview = ref(null);
const photoInput = ref(null);
const coverPhotoPreview = ref(null);
const coverPhotoInput = ref(null);

const showModal = ref(false);

console.log('User:', props.user);
console.log('Company:', props.user.company);

function updateProfileInformation() {
  if (photoInput.value?.files[0]) {
    form.photo = photoInput.value.files[0];
  }
  if (coverPhotoInput.value?.files[0]) {
    form.cover_photo = coverPhotoInput.value.files[0];
  }
  form.put(route('company-profile.update'), {
    preserveScroll: true,
    onSuccess: () => {
      clearPhotoFileInput();
      showModal.value = true;
    }
  });
}

function selectNewPhoto() {
  photoInput.value.click();
}
function selectNewCoverPhoto() {
  coverPhotoInput.value.click();
}

function updatePhotoPreview() {
  const photo = photoInput.value.files[0];
  if (!photo) return;
  const reader = new FileReader();
  reader.onload = (e) => {
    photoPreview.value = e.target.result;
  };
  reader.readAsDataURL(photo);
}

function updateCoverPhotoPreview() {
  const photo = coverPhotoInput.value.files[0];
  if (!photo) return;
  const reader = new FileReader();
  reader.onload = (e) => {
    coverPhotoPreview.value = e.target.result;
  };
  reader.readAsDataURL(photo);
}

function clearPhotoFileInput() {
  if (photoInput.value?.value) photoInput.value.value = null;
  if (coverPhotoInput.value?.value) coverPhotoInput.value.value = null;
}

function reloadPage() {
  location.reload();
}
</script>

<template>
  <form @submit.prevent="updateProfileInformation" class="space-y-8">
    <!-- Profile Photo -->
    <div class="flex items-center space-x-4">
      <div class="relative">
        <img
          :src="photoPreview || form.profile_photo_path || '/images/default-avatar.png'"
          class="rounded-full size-24 object-cover border shadow"
          alt="Profile Photo"
        />
        <input
          ref="photoInput"
          type="file"
          class="hidden"
          @change="updatePhotoPreview"
          accept="image/*"
        />
        <button
          type="button"
          @click.prevent="selectNewPhoto"
          class="absolute bottom-2 right-2 bg-indigo-600 text-white rounded-full p-2 shadow hover:bg-indigo-700"
        >
          <i class="fas fa-camera"></i>
        </button>
      </div>
      <InputError :message="form.errors.photo" />
    </div>

    <!-- Cover Photo -->
    <div class="relative mt-6">
      <img
        :src="coverPhotoPreview || form.cover_photo_path || '/images/default-cover.jpg'"
        class="rounded object-cover border shadow w-full h-32"
        alt="Cover Photo"
      />
      <input
        ref="coverPhotoInput"
        type="file"
        class="hidden"
        @change="updateCoverPhotoPreview"
        accept="image/*"
      />
      <button
        type="button"
        @click.prevent="selectNewCoverPhoto"
        class="absolute bottom-4 right-8 bg-indigo-600 text-white rounded-full p-2 shadow hover:bg-indigo-700"
      >
        <i class="fas fa-camera"></i>
      </button>
      <InputError :message="form.errors.cover_photo" />
    </div>

    <!-- Company Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
      <div>
        <InputLabel for="company_name" value="Company Name" />
        <TextInput id="company_name" v-model="form.company_name" type="text" required class="mt-1 block w-full" />
        <InputError :message="form.errors.company_name" />
      </div>
      <div>
        <InputLabel for="company_email" value="Email Address" />
        <TextInput id="company_email" v-model="form.company_email" type="email" required class="mt-1 block w-full" />
        <InputError :message="form.errors.company_email" />
      </div>
      <div>
        <InputLabel for="company_street_address" value="Street Address" />
        <TextInput id="company_street_address" v-model="form.company_street_address" type="text" required class="mt-1 block w-full" />
        <InputError :message="form.errors.company_street_address" />
      </div>
      <div>
        <InputLabel for="company_brgy" value="Barangay" />
        <TextInput id="company_brgy" v-model="form.company_brgy" type="text" required class="mt-1 block w-full" />
        <InputError :message="form.errors.company_brgy" />
      </div>
      <div>
        <InputLabel for="company_city" value="City / Municipality" />
        <TextInput id="company_city" v-model="form.company_city" type="text" required class="mt-1 block w-full" />
        <InputError :message="form.errors.company_city" />
      </div>
      <div>
        <InputLabel for="company_province" value="Province" />
        <TextInput id="company_province" v-model="form.company_province" type="text" required class="mt-1 block w-full" />
        <InputError :message="form.errors.company_province" />
      </div>
      <div>
        <InputLabel for="company_zip_code" value="ZIP Code" />
        <TextInput id="company_zip_code" v-model="form.company_zip_code" type="text" maxlength="4" required class="mt-1 block w-full" />
        <InputError :message="form.errors.company_zip_code" />
      </div>
      <div>
        <InputLabel for="company_contact_number" value="Mobile Contact Number" />
        <TextInput id="company_contact_number" v-model="form.company_contact_number" type="text" required class="mt-1 block w-full" />
        <InputError :message="form.errors.company_contact_number" />
      </div>
      <div>
        <InputLabel for="telephone_number" value="Telephone Number (Landline)" />
        <TextInput id="telephone_number" v-model="form.telephone_number" type="text" class="mt-1 block w-full" />
        <InputError :message="form.errors.telephone_number" />
      </div>
    </div>

   

    <!-- Actions Slot -->
    <slot name="actions"></slot>

    <!-- Success Modal -->
    <Modal :show="showModal" @close="reloadPage">
      <template #title>
        Profile Updated
      </template>
      <template #content>
        <p>Your profile information has been successfully updated.</p>
      </template>
      <template #footer>
        <PrimaryButton @click="reloadPage">Close</PrimaryButton>
      </template>
    </Modal>
  </form>
</template>