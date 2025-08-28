<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps } from "vue";

const props = defineProps({
  peso: Object,
});

</script>

<template>
  <AppLayout title="PESO Profile">
    <!-- Modern Clean Background -->
    <div class="min-h-screen bg-gray-50">
      <!-- Cover Photo Section -->
      <div class="relative h-64 bg-gradient-to-r from-blue-600 to-purple-600">
        <img
          :src="peso.cover_photo || '/images/default-cover.jpg'"
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
                    :src="peso.logo || '/images/default-logo.png'"
                    alt="PESO Logo"
                    class="w-full h-full object-cover"
                  />
                </div>
                <span
                  v-if="peso.is_featured"
                  class="absolute -top-2 -right-2 bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded-lg"
                >
                  Featured
                </span>
              </div>
              
              <!-- Profile Info -->
              <div class="flex-1 text-center sm:text-left">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ peso.name }}</h1>
                <p class="text-gray-600 mb-4">Public Employment Service Office</p>
                
                <!-- Quick Info Tags -->
                <div class="flex flex-wrap gap-2 justify-center sm:justify-start mb-4">
                  <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                    Government Office
                  </span>
                  <span class="px-3 py-1 bg-purple-100 text-purple-800 text-sm font-medium rounded-full">
                    Employment Services
                  </span>
                </div>
                
                <!-- Social Links -->
                <div class="flex space-x-3 justify-center sm:justify-start">
                  <a
                    v-for="(link, key) in peso.social_links || {}"
                    :key="key"
                    :href="link"
                    target="_blank"
                    class="w-10 h-10 rounded-lg bg-gray-100 hover:bg-blue-100 flex items-center justify-center text-gray-600 hover:text-blue-600 transition-all duration-200"
                  >
                    <i :class="`fab fa-${key}`"></i>
                  </a>
                </div>
              </div>
              
              <!-- Action Button -->
              <div class="flex-shrink-0">
                <a
                  :href="route('profile.show', peso.id)"
                  class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl"
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
        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Description Section -->
          <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
              <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">About PESO</h2>
              </div>
              
              <!-- Description Content -->
              <div class="prose prose-gray max-w-none">
                <p class="text-gray-700 leading-relaxed">{{ peso.description || 'The Public Employment Service Office (PESO) is a non-fee charging multi-employment service facility or entity established or accredited pursuant to Republic Act No. 8759 otherwise known as the PESO Act of 1999.' }}</p>
              </div>
            </div>
          </div>
          
          <!-- Contact Information Sidebar -->
          <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
              <h2 class="text-xl font-semibold text-gray-900 mb-6">Contact Information</h2>
              <div class="space-y-6">
                <div class="flex items-start space-x-3">
                  <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-map-marker-alt text-blue-600"></i>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-500">Address</p>
                    <p class="text-sm text-gray-900 mt-1">{{ peso.address || 'No address provided' }}</p>
                  </div>
                </div>
                
                <div class="flex items-start space-x-3">
                  <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-envelope text-purple-600"></i>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-500">Email</p>
                    <p class="text-sm text-gray-900 mt-1 break-all">{{ peso.email || 'No email provided' }}</p>
                  </div>
                </div>
                
                <div class="flex items-start space-x-3">
                  <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-mobile-alt text-green-600"></i>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-500">Mobile Number</p>
                    <p class="text-sm text-gray-900 mt-1">{{ peso.contact_number || 'No mobile number provided' }}</p>
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
