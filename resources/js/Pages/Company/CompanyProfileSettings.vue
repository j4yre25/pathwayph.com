<script setup>
import { ref, reactive, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  company: Object,
});

const page = usePage();
const user = page.props.auth.user;

// Form for company profile settings
const form = useForm({
  company_name: props.company?.company_name || '',
  company_email: props.company?.company_email || '',
  mobile_number: props.company?.mobile_number || '',
  telephone_number: props.company?.telephone_number || '',
  address: props.company?.address || '',
  sector: props.company?.sector || '',
  category: props.company?.category || '',
  description: props.company?.description || '',
  indeed_profile: props.company?.social_links?.indeed || '',
  facebook: props.company?.social_links?.facebook || '',
  twitter: props.company?.social_links?.twitter || '',
  linkedin: props.company?.social_links?.linkedin || '',
  instagram: props.company?.social_links?.instagram || '',
  website: props.company?.social_links?.website || '',
  logo: null,
});

// States for modals
const showSuccessModal = ref(false);
const showErrorModal = ref(false);
const errorMessage = ref('');

// Logo handling
const logoPreview = ref(props.company?.profile_photo_path || '/images/default-logo.png');
const logoInput = ref(null);

const handleLogoChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.logo = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      logoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const removeLogo = () => {
  form.logo = null;
  logoPreview.value = '/images/default-logo.png';
  if (logoInput.value) {
    logoInput.value.value = '';
  }
};

// Form submission
const submitForm = () => {
  form.post(route('company.profile.settings.update'), {
    onSuccess: () => {
      showSuccessModal.value = true;
    },
    onError: (errors) => {
      if (Object.keys(errors).length === 0) {
        errorMessage.value = 'An unexpected error occurred. Please try again.';
      } else {
        errorMessage.value = 'Please correct the errors in the form.';
      }
      showErrorModal.value = true;
    },
    preserveScroll: true,
  });
};

// Reset form to original values
const resetForm = () => {
  form.company_name = props.company?.company_name || '';
  form.company_email = props.company?.company_email || '';
  form.mobile_number = props.company?.mobile_number || '';
  form.telephone_number = props.company?.telephone_number || '';
  form.address = props.company?.address || '';
  form.sector = props.company?.sector || '';
  form.category = props.company?.category || '';
  form.description = props.company?.description || '';
  form.indeed_profile = props.company?.social_links?.indeed || '';
  form.facebook = props.company?.social_links?.facebook || '';
  form.twitter = props.company?.social_links?.twitter || '';
  form.linkedin = props.company?.social_links?.linkedin || '';
  form.instagram = props.company?.social_links?.instagram || '';
  form.website = props.company?.social_links?.website || '';
  form.logo = null;
  logoPreview.value = props.company?.profile_photo_path || '/images/default-logo.png';
  if (logoInput.value) {
    logoInput.value.value = '';
  }
  form.clearErrors();
};

// Close modals
const closeSuccessModal = () => {
  showSuccessModal.value = false;
};

const closeErrorModal = () => {
  showErrorModal.value = false;
};
</script>

<template>
  <AppLayout title="Company Profile Settings">
    <template #header>
      <div class="flex items-center">
      <h2 class="text-xl font-semibold text-gray-800 flex items-center">
        <i class="fas fa-building text-blue-500 text-xl mr-2"></i> Company Profile Settings
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
              <h2 class="text-xl font-semibold mb-2">Company Logo</h2>
              <p class="text-gray-600 mb-4">Update your company logo</p>
              <div class="flex flex-col items-center">
                <div class="relative mb-4">
                  <img
                    :src="logoPreview || '/images/default-logo.png'"
                    alt="Company Logo"
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
                <!-- Indeed Profile -->
                <div class="relative">
                  <label for="indeed-url" class="block text-gray-700 font-medium mb-1">Indeed Profile</label>
                  <div class="relative">
                    <i class="fas fa-briefcase absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput
                      id="indeed-url"
                      v-model="form.indeed_profile"
                      type="url"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="https://www.indeed.com/company/..."
                    />
                  </div>
                  <InputError :message="form.errors.indeed_profile" class="mt-1" />
                </div>

                <!-- Facebook -->
                <div class="relative">
                  <label for="facebook-url" class="block text-gray-700 font-medium mb-1">Facebook Profile</label>
                  <div class="relative">
                    <i class="fab fa-facebook absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput
                      id="facebook-url"
                      v-model="form.facebook"
                      type="url"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="https://facebook.com/yourpage"
                    />
                  </div>
                  <InputError :message="form.errors.facebook" class="mt-1" />
                </div>

                <!-- Twitter -->
                <div class="relative">
                  <label for="twitter-url" class="block text-gray-700 font-medium mb-1">Twitter Profile</label>
                  <div class="relative">
                    <i class="fab fa-twitter absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput
                      id="twitter-url"
                      v-model="form.twitter"
                      type="url"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="https://twitter.com/yourhandle"
                    />
                  </div>
                  <InputError :message="form.errors.twitter" class="mt-1" />
                </div>

                <!-- LinkedIn -->
                <div class="relative">
                  <label for="linkedin-url" class="block text-gray-700 font-medium mb-1">LinkedIn Profile</label>
                  <div class="relative">
                    <i class="fab fa-linkedin absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput
                      id="linkedin-url"
                      v-model="form.linkedin"
                      type="url"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="https://linkedin.com/company/yourcompany"
                    />
                  </div>
                  <InputError :message="form.errors.linkedin" class="mt-1" />
                </div>

                <!-- Instagram -->
                <div class="relative">
                  <label for="instagram-url" class="block text-gray-700 font-medium mb-1">Instagram Profile</label>
                  <div class="relative">
                    <i class="fab fa-instagram absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput
                      id="instagram-url"
                      v-model="form.instagram"
                      type="url"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="https://instagram.com/yourpage"
                    />
                  </div>
                  <InputError :message="form.errors.instagram" class="mt-1" />
                </div>

                <!-- Website -->
                <div class="relative">
                  <label for="website-url" class="block text-gray-700 font-medium mb-1">Website</label>
                  <div class="relative">
                    <i class="fas fa-globe absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput
                      id="website-url"
                      v-model="form.website"
                      type="url"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="https://..."
                    />
                  </div>
                  <InputError :message="form.errors.website" class="mt-1" />
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column - Form Fields -->
          <form @submit.prevent="submitForm" class="w-full lg:w-2/3 flex flex-col space-y-6">
            <!-- Company Information Section -->
            <div class="bg-white p-6 rounded-lg border border-gray-200">
              
              <div class="flex items-center mb-4">
                <i class="fas fa-building text-blue-600 mr-2"></i>
                <h2 class="text-xl font-semibold text-gray-900">Company Information</h2>
              </div>
              <p class="text-gray-600 mb-6">Basic details about your company</p>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Company Name -->
                <div>
                  <label for="company_name" class="block text-gray-700 font-medium mb-1">Company Name <span class="text-red-500">*</span></label>
                  <div class="relative">
                    <i class="fas fa-building absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput
                      id="company_name"
                      v-model="form.company_name"
                      type="text"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="Enter your company name"
                      required
                    />
                  </div>
                  <InputError :message="form.errors.company_name" class="mt-1" />
                </div>
                <!-- Industry Sector -->
                <div>
                  <label for="sector" class="block text-gray-700 font-medium mb-1">Industry Sector</label>
                  <div class="relative">
                    <i class="fas fa-industry absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput
                      id="sector"
                      v-model="form.sector"
                      type="text"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="Enter industry sector"
                    />
                  </div>
                  <InputError :message="form.errors.sector" class="mt-1" />
                </div>

                <!-- Category -->
                <div>
                  <label for="category" class="block text-gray-700 font-medium mb-1">Category</label>
                  <div class="relative">
                    <i class="fas fa-tags absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput
                      id="category"
                      v-model="form.category"
                      type="text"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="Enter category"
                    />
                  </div>
                  <InputError :message="form.errors.category" class="mt-1" />
                </div>

                <!-- Email -->
                <div>
                  <label for="company_email" class="block text-gray-700 font-medium mb-1">Email <span class="text-red-500">*</span></label>
                  <div class="relative">
                    <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <TextInput
                      id="company_email"
                      v-model="form.company_email"
                      type="email"
                      class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                      placeholder="Enter email address"
                      required
                    />
                  </div>
                  <InputError :message="form.errors.company_email" class="mt-1" />
                </div>

                 <!-- Mobile Number -->
                 <div>
                   <label for="mobile_number" class="block text-gray-700 font-medium mb-1">Mobile Number</label>
                   <div class="relative">
                     <i class="fas fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                     <TextInput
                       id="mobile_number"
                       v-model="form.mobile_number"
                       type="tel"
                       class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                       placeholder="Enter mobile number"
                     />
                   </div>
                   <InputError :message="form.errors.mobile_number" class="mt-1" />
                 </div>

                 <!-- Telephone Number -->
                 <div>
                   <label for="telephone_number" class="block text-gray-700 font-medium mb-1">Telephone Number</label>
                   <div class="relative">
                     <i class="fas fa-phone-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                     <TextInput
                       id="telephone_number"
                       v-model="form.telephone_number"
                       type="tel"
                       class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                       placeholder="Enter telephone number"
                     />
                   </div>
                   <InputError :message="form.errors.telephone_number" class="mt-1" />
                      </div>

                 <!-- Address -->
                 <div class="md:col-span-2">
                   <label for="address" class="block text-gray-700 font-medium mb-1">Address</label>
                   <div class="relative">
                     <i class="fas fa-map-marker-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                     <TextInput
                       id="address"
                       v-model="form.address"
                       type="text"
                       class="w-full pl-10 border border-gray-300 rounded-md p-2 outline-none focus:ring-1 focus:ring-blue-600 transition-all"
                       placeholder="Enter company address"
                     />
                   </div>
                   <InputError :message="form.errors.address" class="mt-1" />
                 </div>
               </div>
             </div>

            <!-- Company Description Section -->
            <div class="bg-white p-6 rounded-lg border border-gray-200">
              <div class="flex items-center mb-4">
                <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                <h2 class="text-xl font-semibold text-gray-900">Company Description</h2>
              </div>
              <p class="text-gray-600 mb-6">Provide a detailed description of your company</p>
              <div>
                <label for="description" class="block text-gray-700 font-medium mb-1">Description</label>
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="6"
                  class="w-full border border-gray-300 rounded-md p-3 outline-none focus:ring-1 focus:ring-blue-600 transition-all resize-none"
                  placeholder="Tell us about your company..."
                ></textarea>
                <InputError :message="form.errors.description" class="mt-1" />
              </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4 pt-6">
              <button
                type="button"
                @click="resetForm"
                class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors"
              >
                Reset
              </button>
              <button
                type="submit"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors disabled:opacity-50"
              >
                Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>

    <!-- Success Modal -->
    <Modal :show="showSuccessModal" @close="closeSuccessModal">
        <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-green-100 rounded-full p-3">
            <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Success!</h3>
        <p class="text-center text-gray-600">Your company profile has been updated successfully.</p>
        <div class="mt-6 flex justify-center">
          <PrimaryButton @click="closeSuccessModal">OK</PrimaryButton>
        </div>
      </div>
    </Modal>

    <!-- Error Modal -->
    <Modal :show="showErrorModal" @close="closeErrorModal">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-red-100 rounded-full p-3">
            <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Error</h3>
        <p class="text-center text-gray-600">{{ errorMessage }}</p>
        <div class="mt-6 flex justify-center">
          <PrimaryButton @click="closeErrorModal">OK</PrimaryButton>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.hover-effect {
  transition: all 0.3s ease;
}

.hover-effect:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style>