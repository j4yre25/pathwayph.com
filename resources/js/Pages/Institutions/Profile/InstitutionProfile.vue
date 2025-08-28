<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { router } from "@inertiajs/vue3";
import { defineProps, ref, computed } from "vue";

const props = defineProps({
  institution: Object,
});

const isEditing = ref(false);
const localDescription = ref(props.institution.description || '');

const canEdit = computed(() => true); // Adjust if you want to restrict editing

const saveDescription = () => {
  router.post(
    route('institution.profile.description.update'),
    { description: localDescription.value },
    {
      preserveScroll: true,
      onSuccess: () => {
        isEditing.value = false;
        // Optionally show a success message
      },
      onError: () => {
        // Optionally handle errors
      }
    }
  );
};

const cancelEditing = () => {
  isEditing.value = false;
  localDescription.value = props.institution.description || '';
};
</script>

<template>
  <AppLayout title="Institution Profile">
    <!-- Modern Clean Background -->
    <div class="min-h-screen bg-gray-50">
      <!-- Cover Photo Section -->
      <div class="relative h-64 bg-gradient-to-r from-indigo-600 to-blue-600">
        <img
          :src="institution.cover_photo || '/images/default-cover.jpg'"
          alt="Cover Photo"
          class="absolute inset-0 w-full h-full object-cover mix-blend-overlay"
        />
        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
      </div>

      <!-- Profile Header Card -->
      <div class="relative -mt-20 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
          <div class="p-8">
            <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6">
              <!-- Profile Image -->
              <div class="relative">
                <div class="w-32 h-32 rounded-2xl overflow-hidden border-4 border-white shadow-lg bg-white">
                  <img
                    :src="institution.logo || '/images/default-logo.png'"
                    alt="Institution Logo"
                    class="w-full h-full object-cover"
                  />
                </div>
              </div>
              
              <!-- Profile Info -->
              <div class="flex-1 text-center sm:text-left">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ institution.name }}</h1>
                <p class="text-gray-600 mb-4 flex items-center justify-center sm:justify-start">
                  <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                  {{ institution.address || 'Location not available' }}
                </p>
                
                <!-- Quick Info Tags -->
                <div class="flex flex-wrap gap-2 justify-center sm:justify-start mb-4">
                  <span class="px-3 py-1 bg-indigo-100 text-indigo-800 text-sm font-medium rounded-full">
                    Educational Institution
                  </span>
                  <span v-if="institution.institution_president_first_name" class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                    President: {{ institution.institution_president_first_name }} {{ institution.institution_president_last_name }}
                  </span>
                </div>
                
                <!-- Social Links -->
                <div class="flex space-x-3 justify-center sm:justify-start">
                  <a
                    v-for="(link, key) in institution.social_links || {}"
                    :key="key"
                    :href="link"
                    target="_blank"
                    class="w-10 h-10 rounded-lg bg-gray-100 hover:bg-indigo-100 flex items-center justify-center text-gray-600 hover:text-indigo-600 transition-all duration-200"
                  >
                    <i :class="`fab fa-${key}`"></i>
                  </a>
                </div>
              </div>
              
              <!-- Action Button -->
              <div class="flex-shrink-0">
                <a
                  v-if="canEdit"
                  :href="route('institution.information')"
                  class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl"
                >
                  <i class="fas fa-edit mr-2"></i>
                  Edit Profile
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
              <div class="w-12 h-12 rounded-lg bg-indigo-50 flex items-center justify-center">
                <i class="fas fa-calendar-alt text-indigo-600 text-xl"></i>
              </div>
              <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Date Joined</h3>
                <p class="text-lg font-semibold text-gray-900">{{ institution.created_at || 'Not available' }}</p>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
              <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-user-tie text-green-600 text-xl"></i>
              </div>
              <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">President</h3>
                <p class="text-lg font-semibold text-gray-900">
                  {{ institution.institution_president_first_name || '' }}
                  {{ institution.institution_president_last_name || 'Not available' }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Description Section -->
          <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
              <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">About Institution</h2>
                <button
                  v-if="canEdit"
                  @click="isEditing = true"
                  class="p-2 text-gray-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 transition-all duration-200"
                >
                  <i class="fas fa-edit"></i>
                </button>
              </div>
              
              <!-- Edit Mode -->
              <div v-if="isEditing" class="space-y-4">
                <textarea
                  v-model="localDescription"
                  class="w-full border border-gray-300 rounded-lg p-4 text-gray-900 placeholder-gray-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 resize-none"
                  rows="6"
                  placeholder="Describe your institution, its mission, values, and what sets it apart..."
                ></textarea>
                <div class="flex justify-end space-x-3">
                  <button 
                    @click="cancelEditing" 
                    class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium transition-colors duration-200"
                  >
                    Cancel
                  </button>
                  <button 
                    @click="saveDescription" 
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors duration-200"
                  >
                    Save Changes
                  </button>
                </div>
              </div>
              
              <!-- View Mode -->
              <div v-else class="prose prose-gray max-w-none">
                <p class="text-gray-700 leading-relaxed">{{ localDescription || 'No description available.' }}</p>
              </div>
            </div>
          </div>
          
          <!-- Contact Information Sidebar -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
              <h2 class="text-xl font-semibold text-gray-900 mb-6">Contact Information</h2>
              <div class="space-y-6">
                <div class="flex items-start space-x-3">
                  <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-map-marker-alt text-indigo-600"></i>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-500">Address</p>
                    <p class="text-sm text-gray-900 mt-1">{{ institution.address || 'No address provided' }}</p>
                  </div>
                </div>
                
                <div class="flex items-start space-x-3">
                  <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-envelope text-purple-600"></i>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-500">Email</p>
                    <p class="text-sm text-gray-900 mt-1 break-all">{{ institution.email || 'No email provided' }}</p>
                  </div>
                </div>
                
                <div class="flex items-start space-x-3">
                  <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-mobile-alt text-green-600"></i>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-500">Contact Number</p>
                    <p class="text-sm text-gray-900 mt-1">{{ institution.contact_number || 'No contact number provided' }}</p>
                  </div>
                </div>
                
                <div class="flex items-start space-x-3">
                  <div class="w-10 h-10 rounded-lg bg-pink-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-phone text-pink-600"></i>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-500">Telephone</p>
                    <p class="text-sm text-gray-900 mt-1">{{ institution.telephone_number || 'No telephone number provided' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
