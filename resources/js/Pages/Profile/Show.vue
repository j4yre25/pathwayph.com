<script setup>
import { ref, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import UpdateCompanyProfileInformationForm from './Partials/UpdateCompanyProfileInformationForm.vue';
import Modal from '@/Components/Modal.vue';
import { Mail, UserCircle, X, CheckCircle, AlertCircle, Camera, Upload } from 'lucide-vue-next';

const user = computed(() => usePage().props.auth?.user || null);
const queryParams = new URLSearchParams(window.location.search);
const editMode = queryParams.get('edit') || 'user'; // Default to 'user'

const isProfileInfoOpen = ref(true); // Set to true by default to show the form

// Photo handling
const photoPreview = ref(null);
const photoInput = ref(null);
const coverPhotoPreview = ref(null);
const coverPhotoInput = ref(null);

// Modal states
const isSuccessModalOpen = ref(false);
const isErrorModalOpen = ref(false);
const isNoChangesModalOpen = ref(false);
const errorMessage = ref('');

const showSuccessModal = () => {
  isSuccessModalOpen.value = true;
  setTimeout(() => {
    isSuccessModalOpen.value = false;
  }, 3000);
};

const showErrorModal = (message) => {
  errorMessage.value = message || 'An error occurred while updating your profile.';
  isErrorModalOpen.value = true;
};

const cancelEdit = () => {
  // Navigate back to the profile page without the edit parameter
  router.visit(route('profile.show'));
};

// Profile photo handling
const selectNewPhoto = () => {
  photoInput.value.click();
};

const updatePhotoPreview = () => {
  const photo = photoInput.value.files[0];
  if (!photo) return;

  const reader = new FileReader();
  reader.onload = (e) => {
    photoPreview.value = e.target.result;
  };
  reader.readAsDataURL(photo);
};

// Cover photo handling
const selectNewCoverPhoto = () => {
  coverPhotoInput.value.click();
};

const updateCoverPhotoPreview = () => {
  const photo = coverPhotoInput.value.files[0];
  if (!photo) return;

  const reader = new FileReader();
  reader.onload = (e) => {
    coverPhotoPreview.value = e.target.result;
  };
  reader.readAsDataURL(photo);
};
</script>

<template>
  <AppLayout title="Profile">
    <!-- Success Modal -->
    <Modal :show="isSuccessModalOpen" @close="isSuccessModalOpen = false">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-green-100 rounded-full p-3">
            <CheckCircle class="text-green-500 w-6 h-6" />
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Success!</h3>
        <p class="text-center text-gray-600">Your profile has been updated successfully.</p>
        <div class="mt-6 flex justify-center">
          <button type="button"
            class="bg-green-500 hover:bg-green-600 transition-colors text-white px-4 py-2 rounded-md"
            @click="isSuccessModalOpen = false">
            Close
          </button>
        </div>
      </div>
    </Modal>

    <!-- Error Modal -->
    <Modal :show="isErrorModalOpen" @close="isErrorModalOpen = false">
      <div class="p-6">
        <div class="flex items-center justify-center mb-4">
          <div class="bg-red-100 rounded-full p-3">
            <AlertCircle class="text-red-500 w-6 h-6" />
          </div>
        </div>
        <h3 class="text-lg font-medium text-center text-gray-900 mb-2">Error</h3>
        <p class="text-center text-gray-600">{{ errorMessage }}</p>
        <div class="mt-6 flex justify-center">
          <button type="button" class="bg-red-500 hover:bg-red-600 transition-colors text-white px-4 py-2 rounded-md"
            @click="isErrorModalOpen = false">
            Close
          </button>
        </div>
      </div>
    </Modal>

    <!-- Cover Photo Section -->
    <div class="relative bg-gray-800 h-64">
      <img
        :src="coverPhotoPreview || user.cover_photo_path || '/images/default-cover.jpg'"
        alt="Cover Photo"
        class="absolute inset-0 w-full h-full object-cover"
      />
      <!-- Overlay gradient -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent">
      </div>
      
      <!-- Cover Photo Upload Button (Only visible in edit mode) -->
      <div v-if="editMode" class="absolute bottom-4 left-1/2 transform -translate-x-1/2">
        <input
          ref="coverPhotoInput"
          type="file"
          class="hidden"
          @change="updateCoverPhotoPreview"
          accept="image/*"
        />
        <button 
          @click="selectNewCoverPhoto"
          class="bg-white/80 hover:bg-white transition-colors text-gray-800 rounded-full p-2 shadow-md flex items-center space-x-2 backdrop-blur-sm"
        >
          <Upload class="w-5 h-5" />
          <span class="text-sm font-medium pr-1">Change Cover</span>
        </button>
      </div>
    </div>

    <!-- Profile Header -->
    <div class="relative -mt-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-lg overflow-hidden border border-gray-200 shadow-lg">
        <div class="p-8 flex flex-col md:flex-row md:items-center md:space-x-8">
          <!-- Profile Photo -->
          <div class="relative flex-shrink-0 mx-auto md:mx-0 mb-6 md:mb-0">
            <div class="w-40 h-40 rounded-full overflow-hidden border-4 border-white shadow-lg">
              <img
                :src="photoPreview || user.profile_photo_url || '/images/default-avatar.png'"
                alt="Profile Photo"
                class="w-full h-full object-cover"
              />
            </div>
            
            <!-- Profile Photo Upload Button (Only visible in edit mode) -->
            <div v-if="editMode" class="absolute bottom-0 right-0">
              <input
                ref="photoInput"
                type="file"
                class="hidden"
                @change="updatePhotoPreview"
                accept="image/*"
              />
              <button 
                @click="selectNewPhoto"
                class="bg-indigo-600 hover:bg-indigo-700 transition-colors text-white rounded-full p-2 shadow-md"
              >
                <Camera class="w-5 h-5" />
              </button>
            </div>
          </div>

          <!-- User Info -->
          <div class="flex-1 text-center md:text-left">
            <div class="space-y-4">
              <h1 class="text-3xl font-bold text-gray-900 mb-2 border-b-2 border-indigo-500 pb-2 inline-block">
                {{ editMode === 'company' ? user.company_name : user.name }}
              </h1>
              
              <!-- Role -->
              <p class="text-gray-600 flex items-center space-x-2 justify-center md:justify-start">
                <UserCircle class="w-5 h-5 text-indigo-500" />
                <span>{{ user.role ? user.role.charAt(0).toUpperCase() + user.role.slice(1) : 'User' }}</span>
              </p>
              
              <!-- Email -->
              <div class="space-y-2">
                <div class="flex items-center space-x-2 justify-center md:justify-start">
                  <Mail class="w-5 h-5 text-indigo-500" />
                  <span class="text-gray-700">{{ user.email }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="py-6 bg-gray-50">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg overflow-hidden border border-gray-200 shadow-sm">
          <div class="p-6">
            <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-6">
              <h2 class="text-xl font-semibold text-gray-800">
                {{ editMode === 'company' ? 'Update Company Profile' : 'Update Profile Information' }}
              </h2>
            </div>
            
            <!-- Profile Forms -->
            <div v-if="editMode === 'user' && $page.props?.jetstream?.canUpdateProfileInformation">
              <UpdateProfileInformationForm v-if="user" :user="user">
                <template #actions>
                  <div class="flex items-center space-x-3">
                    <button 
                      @click.prevent="cancelEdit"
                      class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-md shadow-sm flex items-center"
                    >
                      <X class="w-4 h-4 mr-2" />
                      Cancel
                    </button>
                    <button 
                      type="submit"
                      class="bg-indigo-600 hover:bg-indigo-700 transition-colors text-white py-2 px-6 rounded-md shadow-sm flex items-center"
                    >
                      Save
                    </button>
                  </div>
                </template>
              </UpdateProfileInformationForm>
            </div>

            <div v-if="editMode === 'company' && $page.props?.jetstream?.canUpdateProfileInformation">
              <UpdateCompanyProfileInformationForm v-if="user" :user="user">
                <template #actions>
                  <div class="flex items-center space-x-3">
                    <button 
                      @click.prevent="cancelEdit"
                      class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-6 rounded-md shadow-sm flex items-center"
                    >
                      <X class="w-4 h-4 mr-2" />
                      Cancel
                    </button>
                    <button 
                      type="submit"
                      class="bg-indigo-600 hover:bg-indigo-700 transition-colors text-white py-2 px-6 rounded-md shadow-sm flex items-center"
                    >
                      Save
                    </button>
                  </div>
                </template>
              </UpdateCompanyProfileInformationForm>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>