<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
  peso: Object,
});

// Form data
const form = useForm({
  peso_first_name: props.peso.peso_first_name || '',
  peso_last_name: props.peso.peso_last_name || '',
  email: props.peso.email || '',
  contact_number: props.peso.contact_number || '',
  description: props.peso.description || '',
  address: props.peso.address || '',
  logo: null,
  social_links: {
    facebook: props.peso.social_links?.facebook || '',
    twitter: props.peso.social_links?.twitter || '',
    linkedin: props.peso.social_links?.linkedin || '',
    instagram: props.peso.social_links?.instagram || '',
  },
});

// Modal states
const showSaveModal = ref(false);
const showUpdateModal = ref(false);
const showErrorModal = ref(false);
const showDuplicateModal = ref(false);
const showArchiveModal = ref(false);

// Logo preview
const logoPreview = ref(props.peso.logo ? `/storage/${props.peso.logo}` : null);

// Handle logo file selection
const handleLogoChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.logo = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      logoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

// Submit form
const submitForm = () => {
  form.put(route('peso.profile.update'), {
    onSuccess: () => {
      showUpdateModal.value = true;
    },
    onError: () => {
      showErrorModal.value = true;
    },
  });
};

// Reset form
const resetForm = () => {
  form.reset();
  logoPreview.value = props.peso.logo ? `/storage/${props.peso.logo}` : null;
};
</script>

<template>
  <AppLayout title="PESO Profile Settings">
    <!-- Header Section with Blue Background -->
    <template #header>
      <div class="flex items-center">
      <h2 class="text-xl font-semibold text-gray-800 flex items-center">
        <i class="fas fa-user text-blue-500 text-xl mr-2"></i> Peso Profile Settings
      </h2>
      </div>
    </template>

    <!-- Main Content -->
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-9 mb-9 py-8">
      <div class="flex flex-col lg:flex-row lg:space-x-8">
        <!-- Left Column - Profile Picture and Social Links -->
        <div class="w-full lg:w-1/3 mb-8 lg:mb-0">
          <!-- Profile Picture Section -->
          <div class="bg-white p-6 rounded-lg border border-gray-200 mb-6">
            <h2 class="text-xl font-semibold mb-2">Profile Picture</h2>
            <p class="text-gray-600 mb-4">Update your profile picture</p>
            <div class="flex flex-col items-center">
              <div class="relative mb-4">
                <img
                  :src="logoPreview || '/images/default-logo.png'"
                  alt="PESO Logo"
                  class="rounded-full w-48 h-48 object-cover border border-gray-200 shadow-sm"
                />
                <div class="absolute bottom-0 right-0">
                  <label for="file-input"
                    class="bg-blue-600 hover:bg-blue-700 transition-colors text-white rounded-full p-2 cursor-pointer shadow-md">
                    <i class="fas fa-camera"></i>
                  </label>
                </div>
              </div>
              <input
                ref="logoInput"
                id="file-input"
                type="file"
                accept="image/*"
                @change="handleLogoChange"
                class="hidden"
              />
              <label for="file-input"
                class="text-blue-600 hover:text-blue-800 transition-colors font-medium cursor-pointer">
                Choose an image
              </label>
            </div>
            <InputError :message="form.errors.logo" class="mt-2" />
          </div>

          <!-- Contact and Social Links Section -->
          <div class="bg-white p-6 rounded-lg border border-gray-200">
            <h2 class="text-xl font-semibold mb-2">Contact and Social Links</h2>
            <p class="text-gray-600 mb-4">Add your professional networks and contact options</p>

            <div class="space-y-4">
              <!-- Facebook -->
              <div class="relative">
                <label for="facebook-url" class="block text-gray-700 font-medium mb-1">Facebook Profile</label>
                <div class="relative">
                  <i class="fab fa-facebook absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <TextInput
                    id="facebook-url"
                    v-model="form.social_links.facebook"
                    type="url"
                    class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                    placeholder="https://facebook.com/yourpage"
                  />
                </div>
                <InputError :message="form.errors['social_links.facebook']" class="mt-1" />
              </div>

              <!-- Twitter -->
              <div class="relative">
                <label for="twitter-url" class="block text-gray-700 font-medium mb-1">Twitter Profile</label>
                <div class="relative">
                  <i class="fab fa-twitter absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <TextInput
                    id="twitter-url"
                    v-model="form.social_links.twitter"
                    type="url"
                    class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                    placeholder="https://twitter.com/yourhandle"
                  />
                </div>
                <InputError :message="form.errors['social_links.twitter']" class="mt-1" />
              </div>

              <!-- LinkedIn -->
              <div class="relative">
                <label for="linkedin-url" class="block text-gray-700 font-medium mb-1">LinkedIn Profile</label>
                <div class="relative">
                  <i class="fab fa-linkedin absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <TextInput
                    id="linkedin-url"
                    v-model="form.social_links.linkedin"
                    type="url"
                    class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                    placeholder="https://linkedin.com/company/yourcompany"
                  />
                </div>
                <InputError :message="form.errors['social_links.linkedin']" class="mt-1" />
              </div>

              <div class="relative">
                <label for="indeed-url" class="block text-gray-700 font-medium mb-1">Indeed Profile</label>
                <div class="relative">
                  <i class="fab fa-info absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <TextInput
                    id="indeed-url"
                    v-model="form.social_links.indeed"
                    type="url"
                    class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                    placeholder="https://indeed.com/company/yourcompany"
                  />
                </div>
                <InputError :message="form.errors['social_links.indeed']" class="mt-1" />
              </div>

              <!-- Instagram -->
              <div class="relative">
                <label for="instagram-url" class="block text-gray-700 font-medium mb-1">Instagram Profile</label>
                <div class="relative">
                  <i class="fab fa-instagram absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                  <TextInput
                    id="instagram-url"
                    v-model="form.social_links.instagram"
                    type="url"
                    class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                    placeholder="https://instagram.com/yourpage"
                  />
                </div>
                <InputError :message="form.errors['social_links.instagram']" class="mt-1" />
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column - Form Fields -->
        <div class="w-full lg:w-2/3 flex flex-col space-y-6">
          <!-- Personal Information, About Me, and Buttons - Each in their own white box -->
          <!-- Personal Information Section -->
          <div class="bg-white p-6 rounded-lg border border-gray-200">
            <form @submit.prevent="submitForm">
              <div>
          <div class="flex items-center mb-4">
            <i class="fas fa-user text-blue-600 mr-2"></i>
            <h2 class="text-xl font-semibold text-gray-900">Personal Information</h2>
          </div>
          <p class="text-gray-600 mb-6">Basic details about you</p>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- First Name -->
            <div>
              <label for="peso_first_name" class="block text-gray-700 font-medium mb-1">First Name <span class="text-red-500">*</span></label>
              <div class="relative">
                <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <TextInput
            id="peso_first_name"
            v-model="form.peso_first_name"
            type="text"
            class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
            placeholder="Enter your first name"
            required
                />
              </div>
              <InputError :message="form.errors.peso_first_name" class="mt-1" />
            </div>
            <!-- Last Name -->
            <div>
              <label for="peso_last_name" class="block text-gray-700 font-medium mb-1">Last Name <span class="text-red-500">*</span></label>
              <div class="relative">
                <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <TextInput
            id="peso_last_name"
            v-model="form.peso_last_name"
            type="text"
            class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
            placeholder="Enter your last name"
                />
              </div>
              <InputError :message="form.errors.peso_last_name" class="mt-1" />
            </div>
            <!-- Email -->
            <div>
              <label for="email" class="block text-gray-700 font-medium mb-1">Email Address <span class="text-red-500">*</span></label>
              <div class="relative">
                <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <TextInput
            id="email"
            v-model="form.email"
            type="email"
            class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
            placeholder="peso@example.com"
            required
                />
              </div>
              <InputError :message="form.errors.email" class="mt-1" />
            </div>
            <!-- Contact Number -->
            <div>
              <label for="contact_number" class="block text-gray-700 font-medium mb-1">Phone Number <span class="text-red-500">*</span></label>
              <div class="relative">
                <i class="fas fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <TextInput
            id="contact_number"
            v-model="form.contact_number"
            type="text"
            class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
            placeholder="9013604441"
                />
              </div>
              <InputError :message="form.errors.contact_number" class="mt-1" />
            </div>
          </div>
          <!-- Address -->
          <div class="mt-6">
            <label for="address" class="block text-gray-700 font-medium mb-1">Address</label>
            <div class="relative">
              <i class="fas fa-map-marker-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
              <TextInput
                id="address"
                v-model="form.address"
                type="text"
                class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                placeholder="Enter your Office Address"
              />
            </div>
            <InputError :message="form.errors.address" class="mt-1" />
          </div>
              </div>
            </form>
          </div>

          <!-- About Me Section -->
          <div class="bg-white p-6 rounded-lg border border-gray-200">
            <div class="flex items-center mb-4">
              <i class="fas fa-info-circle text-blue-600 mr-2"></i>
              <h2 class="text-xl font-semibold text-gray-900">About Me</h2>
            </div>
            <p class="text-gray-600 mb-6">Tell others about Peso</p>
            <div>
              <label for="description" class="block text-gray-700 font-medium mb-1">Description</label>
              <textarea
          id="description"
          v-model="form.description"
          rows="4"
          class="w-full border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all resize-none"
          placeholder="Describe your PESO office and services..."
              ></textarea>
              <InputError :message="form.errors.description" class="mt-1" />
            </div>
          </div>

          <!-- Save/Reset Buttons Section -->
          <div class="bg-white p-6 rounded-lg border border-gray-200 flex justify-end space-x-3">
            <SecondaryButton @click="resetForm" :disabled="form.processing">
              Reset
            </SecondaryButton>
            <PrimaryButton @click="submitForm" :disabled="form.processing" class="bg-blue-600 hover:bg-blue-700">
              <span v-if="form.processing">Saving Changes...</span>
              <span v-else>Save Changes</span>
            </PrimaryButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <Modal :show="showUpdateModal" @close="showUpdateModal = false">
      <div class="p-6">
        <div class="flex items-center">
          <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
            <i class="fas fa-check text-green-600 text-xl"></i>
          </div>
        </div>
        <div class="mt-3 text-center">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Profile Updated Successfully</h3>
          <div class="mt-2">
            <p class="text-sm text-gray-500">
              Your PESO profile has been updated successfully.
            </p>
          </div>
        </div>
        <div class="mt-5">
          <PrimaryButton @click="showUpdateModal = false" class="w-full justify-center">
            Continue
          </PrimaryButton>
        </div>
      </div>
    </Modal>

    <!-- Error Modal -->
    <Modal :show="showErrorModal" @close="showErrorModal = false">
      <div class="p-6">
        <div class="flex items-center">
          <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
          </div>
        </div>
        <div class="mt-3 text-center">
          <h3 class="text-lg leading-6 font-medium text-gray-900">Update Failed</h3>
          <div class="mt-2">
            <p class="text-sm text-gray-500">
              There was an error updating your profile. Please check the form and try again.
            </p>
          </div>
        </div>
        <div class="mt-5">
          <SecondaryButton @click="showErrorModal = false" class="w-full justify-center">
            Close
          </SecondaryButton>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>

<style scoped>
/* Glow effect animations */
@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.5);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
  }
}

/* Input focus effects with glow */
input:focus, textarea:focus, select:focus {
  animation: pulse 1.5s ease-in-out;
  transition: all 0.3s ease;
}

/* Button hover effects */
button:not(:disabled) {
  transition: all 0.3s ease;
}

button:not(:disabled):hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

</style>