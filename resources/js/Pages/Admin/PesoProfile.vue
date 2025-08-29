<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps } from "vue";

const props = defineProps({
  peso: Object,
});

</script>

<template>
  <AppLayout title="PESO Profile">
    <!-- Modern Gradient Header Section -->
    <div class="relative overflow-hidden">
      <!-- Colorful Gradient Background -->
      <div class="h-64 bg-gradient-to-br from-cyan-400 via-blue-500 to-purple-600 relative">
        <!-- Abstract Shapes -->
        <div class="absolute inset-0">
          <div class="absolute top-0 left-0 w-96 h-96 bg-gradient-to-br from-yellow-300 to-orange-400 rounded-full opacity-30 -translate-x-32 -translate-y-32"></div>
          <div class="absolute top-20 right-0 w-80 h-80 bg-gradient-to-br from-pink-400 to-purple-500 rounded-full opacity-40 translate-x-32 -translate-y-20"></div>
          <div class="absolute bottom-0 left-1/2 w-72 h-72 bg-gradient-to-br from-green-300 to-blue-400 rounded-full opacity-25 -translate-x-36 translate-y-36"></div>
        </div>
        
        <!-- Navigation Breadcrumb -->
        <div class="relative z-10 pt-6 px-6">
          <div class="flex items-center text-white/80 text-sm">
            <span>User App</span>
            <svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span>Home</span>
            <svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span class="text-white">UserProfile</span>
          </div>
          
          <!-- Star Icon -->
          <div class="absolute top-6 right-6">
            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm">
              <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.602-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Profile Card -->
      <div class="relative -mt-20 mx-auto max-w-4xl px-6">
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
          <!-- Profile Header -->
          <div class="px-8 py-6">
            <div class="flex flex-col items-center text-center">
              <!-- Avatar -->
              <div class="relative mb-4">
                <img
                  :src="peso.logo || '/images/default-logo.png'"
                  alt="PESO Avatar"
                  class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg"
                />
                <span
                  v-if="peso.is_featured"
                  class="absolute -top-2 -right-2 bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded-full"
                >
                  Featured
                </span>
              </div>
              
              <!-- User Info -->
              <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ peso.first_name }}</h1>
              <p class="text-gray-600 mb-4">Designer</p>
              
              <!-- Stats -->
              <div class="flex items-center space-x-8 mb-6">
                <div class="text-center">
                  <div class="text-2xl font-bold text-gray-900">0</div>
                  <div class="text-sm text-gray-500">Posts</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-gray-900">3,586</div>
                  <div class="text-sm text-gray-500">Followers</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-gray-900">2,659</div>
                  <div class="text-sm text-gray-500">Following</div>
                </div>
              </div>
              
              <!-- Social Links and Action Button -->
              <div class="flex items-center space-x-4 mb-6">
                <div class="flex space-x-3">
                  <a
                    v-for="(link, key) in peso.social_links || {}"
                    :key="key"
                    :href="link"
                    target="_blank"
                    class="w-10 h-10 rounded-full flex items-center justify-center text-white"
                    :class="{
                      'bg-blue-500': key === 'facebook',
                      'bg-blue-400': key === 'twitter', 
                      'bg-pink-500': key === 'instagram',
                      'bg-red-500': key === 'youtube'
                    }"
                  >
                    <i :class="`fab fa-${key}`"></i>
                  </a>
                  <!-- Default social icons if no links -->
                  <template v-if="!peso.social_links || Object.keys(peso.social_links).length === 0">
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white">
                      <i class="fab fa-facebook-f"></i>
                    </div>
                    <div class="w-10 h-10 bg-blue-400 rounded-full flex items-center justify-center text-white">
                      <i class="fab fa-twitter"></i>
                    </div>
                    <div class="w-10 h-10 bg-pink-500 rounded-full flex items-center justify-center text-white">
                      <i class="fab fa-instagram"></i>
                    </div>
                    <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center text-white">
                      <i class="fab fa-youtube"></i>
                    </div>
                  </template>
                </div>
                <a
                  :href="route('profile.show', peso.id)"
                  class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-full font-medium transition-colors"
                >
                  Add To Story
                </a>
              </div>
              
              <!-- Tab Navigation -->
              <div class="flex space-x-8 border-b border-gray-200 w-full">
                <button class="pb-3 px-1 border-b-2 border-blue-500 text-blue-600 font-medium flex items-center space-x-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                  <span>Profile</span>
                </button>
                <button class="pb-3 px-1 text-gray-500 hover:text-gray-700 font-medium flex items-center space-x-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                  <span>Followers</span>
                </button>
                <button class="pb-3 px-1 text-gray-500 hover:text-gray-700 font-medium flex items-center space-x-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                  </svg>
                  <span>Friends</span>
                </button>
                <button class="pb-3 px-1 text-gray-500 hover:text-gray-700 font-medium flex items-center space-x-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                  </svg>
                  <span>Gallery</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-6 mt-8">
        <!-- Introduction Section -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
            <div class="p-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Introduction</h2>
                <p class="text-gray-600 leading-relaxed mb-6">
                    {{ peso.description || 'I am a passionate designer who loves making websites and graphics. Lorem ipsum dolor sit amet, consectetur adipiscing elit.' }}
                </p>
                
                <!-- Contact Details -->
                <div class="space-y-3">
                    <div class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m0 0h2M7 7h10M7 11h4"></path>
                        </svg>
                        <span>{{ peso.institution || 'Sir P.P Institute Of Science' }}</span>
                    </div>
                    <div v-if="peso.email" class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span>{{ peso.email }}</span>
                    </div>
                    <div v-if="peso.website" class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9m0 9c-5 0-9-4-9-9s4-9 9-9"></path>
                        </svg>
                        <span>{{ peso.website || 'www.xyz.com' }}</span>
                    </div>
                    <div v-if="peso.address" class="flex items-center text-gray-600">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>{{ peso.address || 'Newyork, USA - 100001' }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Post Creation Section -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
            <div class="p-6">
                <div class="flex items-center space-x-4">
                    <img
                        :src="peso.logo || '/images/default-logo.png'"
                        alt="User Avatar"
                        class="w-12 h-12 rounded-full object-cover"
                    />
                    <div class="flex-1">
                        <input
                            type="text"
                            placeholder="Share your thoughts"
                            class="w-full px-4 py-3 bg-gray-50 rounded-full border-0 focus:ring-2 focus:ring-blue-500 focus:bg-white transition-colors"
                        />
                    </div>
                </div>
              </div>
              <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                      <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                      </div>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-500">Address</p>
                      <p class="text-base text-gray-900">{{ peso.address || 'No address provided' }}</p>
                    </div>
                  </div>
                  
                  <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                      <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                      </div>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-500">Email</p>
                      <p class="text-base text-gray-900">{{ peso.email || 'No email provided' }}</p>
                    </div>
                  </div>
                  
                  <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                      <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                      </div>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-500">Mobile Number</p>
                      <p class="text-base text-gray-900">{{ peso.contact_number || 'No mobile number provided' }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Team Members Section
            <div v-if="company.team_members && company.team_members.length" class="mt-8 bg-white shadow-lg rounded-lg p-6">
                <h4 class="text-xl font-semibold text-gray-800">Team Members</h4>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="member in company.team_members"
                        :key="member.id"
                        class="bg-gray-50 shadow rounded-lg p-4 flex items-center space-x-4">
                        <img
                            :src="member.photo || '/images/default-avatar.png'"
                            alt="Team Member"
                            class="w-16 h-16 rounded-full object-cover"
                        />
                        <div>
                            <h5 class="text-lg font-semibold text-gray-800">{{ member.name }}</h5>
                            <p class="text-sm text-gray-600">{{ member.role }}</p>
                        </div>
                    </div>
                </div>
            </div> -->
      </div>
  </AppLayout>
</template>
