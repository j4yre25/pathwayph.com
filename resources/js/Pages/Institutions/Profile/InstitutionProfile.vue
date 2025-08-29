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
    <!-- Modern Gradient Header Section -->
    <div class="relative min-h-screen bg-gradient-to-br from-purple-400 via-pink-500 to-red-500 overflow-hidden">
      <!-- Abstract Background Shapes -->
      <div class="absolute inset-0">
        <div class="absolute top-0 left-0 w-96 h-96 bg-white/10 rounded-full blur-3xl transform -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute top-1/4 right-0 w-80 h-80 bg-yellow-300/20 rounded-full blur-2xl transform translate-x-1/2"></div>
        <div class="absolute bottom-0 left-1/3 w-64 h-64 bg-blue-400/20 rounded-full blur-2xl"></div>
      </div>
      
      <!-- Navigation Breadcrumb -->
      <div class="relative z-10 pt-8 px-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="flex items-center text-white/80 text-sm">
            <span>Institution App</span>
            <i class="fas fa-chevron-right mx-2"></i>
            <span>Institution Profile</span>
          </div>
        </div>
      </div>
      
      <!-- Star Icon -->
      <div class="absolute top-20 right-20 text-white/30">
        <i class="fas fa-star text-4xl"></i>
      </div>
      
      <!-- Profile Card -->
      <div class="relative z-10 pt-16 px-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
          <div class="bg-white/95 backdrop-blur-lg rounded-3xl shadow-2xl overflow-hidden">
            <!-- Institution Avatar and Info -->
            <div class="p-8">
              <div class="flex flex-col md:flex-row items-center md:items-start space-y-6 md:space-y-0 md:space-x-8">
                <!-- Institution Logo -->
                <div class="relative">
                  <div class="w-32 h-32 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 p-1">
                    <img
                      :src="institution.logo || '/images/default-logo.png'"
                      alt="Institution Logo"
                      class="w-full h-full rounded-full object-cover bg-white"
                    />
                  </div>
                  <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-green-500 rounded-full border-4 border-white flex items-center justify-center">
                    <i class="fas fa-check text-white text-xs"></i>
                  </div>
                </div>
                
                <!-- Institution Info -->
                <div class="flex-1 text-center md:text-left">
                  <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">{{ institution.name || 'Institution Name' }}</h1>
                  <p class="text-gray-600 mb-4 flex items-center justify-center md:justify-start">
                    <i class="fas fa-graduation-cap mr-2 text-purple-600"></i>
                    Educational Institution
                  </p>
                  
                  <!-- Stats -->
                  <div class="flex justify-center md:justify-start space-x-8 mb-6">
                    <div class="text-center">
                      <div class="text-2xl font-bold text-gray-800">150+</div>
                      <div class="text-sm text-gray-600">Students</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold text-gray-800">25</div>
                      <div class="text-sm text-gray-600">Programs</div>
                    </div>
                    <div class="text-center">
                      <div class="text-2xl font-bold text-gray-800">15</div>
                      <div class="text-sm text-gray-600">Years</div>
                    </div>
                  </div>
                  
                  <!-- Social Links and Action Button -->
                  <div class="flex flex-col sm:flex-row items-center justify-center md:justify-start space-y-4 sm:space-y-0 sm:space-x-6">
                    <!-- Social Links -->
                    <div class="flex space-x-4">
                      <a
                        v-for="(link, key) in institution.social_links || {}"
                        :key="key"
                        :href="link"
                        target="_blank"
                        class="w-10 h-10 rounded-full bg-gray-100 hover:bg-purple-100 flex items-center justify-center transition-all duration-200"
                      >
                        <i :class="`fab fa-${key} text-gray-600 hover:text-purple-600`"></i>
                      </a>
                      <!-- Default social links if none exist -->
                      <template v-if="!institution.social_links || Object.keys(institution.social_links).length === 0">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                          <i class="fab fa-facebook text-blue-600"></i>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-sky-100 flex items-center justify-center">
                          <i class="fab fa-twitter text-sky-600"></i>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center">
                          <i class="fab fa-instagram text-pink-600"></i>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                          <i class="fab fa-youtube text-red-600"></i>
                        </div>
                      </template>
                    </div>
                    
                    <!-- Action Button -->
                    <a
                      v-if="canEdit"
                      :href="route('institution.information')"
                      class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-medium rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl"
                    >
                      <i class="fas fa-edit mr-2"></i>Edit Profile
                    </a>
                  </div>
                </div>
              </div>
              
              <!-- Tab Navigation -->
              <div class="mt-8 border-t border-gray-200 pt-6">
                <div class="flex space-x-8 overflow-x-auto">
                  <button class="flex items-center space-x-2 px-4 py-2 text-purple-600 border-b-2 border-purple-600 font-medium whitespace-nowrap">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                  </button>
                  <button class="flex items-center space-x-2 px-4 py-2 text-gray-500 hover:text-purple-600 font-medium whitespace-nowrap">
                    <i class="fas fa-users"></i>
                    <span>Students</span>
                  </button>
                  <button class="flex items-center space-x-2 px-4 py-2 text-gray-500 hover:text-purple-600 font-medium whitespace-nowrap">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Programs</span>
                  </button>
                  <button class="flex items-center space-x-2 px-4 py-2 text-gray-500 hover:text-purple-600 font-medium whitespace-nowrap">
                    <i class="fas fa-images"></i>
                    <span>Gallery</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="py-12 bg-gray-50">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- About Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
          <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-b border-gray-100">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
              <i class="fas fa-university mr-3 text-purple-600"></i>
              About Our Institution
            </h2>
          </div>
          <div class="p-6">
            <div v-if="isEditing" class="space-y-4">
              <textarea
                v-model="localDescription"
                class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-purple-500 focus:border-purple-500 p-4"
                rows="6"
                placeholder="Tell us about your institution, its mission, vision, and values..."
              ></textarea>
              <div class="flex space-x-3">
                <button 
                  @click="saveDescription" 
                  class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-xl transition-all duration-200"
                >
                  <i class="fas fa-save mr-2"></i>Save Changes
                </button>
                <button 
                  @click="cancelEditing" 
                  class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-all duration-200"
                >
                  <i class="fas fa-times mr-2"></i>Cancel
                </button>
              </div>
            </div>
            <div v-else>
              <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                  <p class="text-gray-700 leading-relaxed text-lg mb-6">{{ localDescription || 'We are committed to providing quality education and fostering academic excellence. Our institution has been a cornerstone of learning and development, preparing students for successful careers and meaningful contributions to society.' }}</p>
                </div>
                <button
                  v-if="canEdit"
                  @click="isEditing = true"
                  class="ml-4 px-4 py-2 bg-purple-100 hover:bg-purple-200 text-purple-700 font-medium rounded-lg transition-all duration-200"
                  title="Edit Description"
                >
                  <i class="fas fa-edit mr-2"></i>Edit
                </button>
              </div>
              
              <!-- Institution Details Grid -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6">
                  <div class="flex items-center mb-3">
                    <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center mr-4">
                      <i class="fas fa-calendar-alt text-white"></i>
                    </div>
                    <div>
                      <h3 class="font-semibold text-gray-800">Established</h3>
                      <p class="text-sm text-gray-600">Date founded</p>
                    </div>
                  </div>
                  <p class="text-gray-800 font-medium">{{ institution.created_at || 'January 2020' }}</p>
                </div>
                
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6">
                  <div class="flex items-center mb-3">
                    <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center mr-4">
                      <i class="fas fa-user-tie text-white"></i>
                    </div>
                    <div>
                      <h3 class="font-semibold text-gray-800">President</h3>
                      <p class="text-sm text-gray-600">Institution leader</p>
                    </div>
                  </div>
                  <p class="text-gray-800 font-medium">
                    {{ institution.institution_president_first_name || 'Dr. Maria' }}
                    {{ institution.institution_president_last_name || 'Santos' }}
                  </p>
                </div>
                
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6">
                  <div class="flex items-center mb-3">
                    <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center mr-4">
                      <i class="fas fa-map-marker-alt text-white"></i>
                    </div>
                    <div>
                      <h3 class="font-semibold text-gray-800">Location</h3>
                      <p class="text-sm text-gray-600">Campus address</p>
                    </div>
                  </div>
                  <p class="text-gray-800 font-medium">{{ institution.address || 'Manila, Philippines' }}</p>
                </div>
                
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6">
                  <div class="flex items-center mb-3">
                    <div class="w-12 h-12 bg-orange-600 rounded-full flex items-center justify-center mr-4">
                      <i class="fas fa-graduation-cap text-white"></i>
                    </div>
                    <div>
                      <h3 class="font-semibold text-gray-800">Type</h3>
                      <p class="text-sm text-gray-600">Institution category</p>
                    </div>
                  </div>
                  <p class="text-gray-800 font-medium">Educational Institution</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Programs & Achievements Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
          <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-6 py-4 border-b border-gray-100">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
              <i class="fas fa-trophy mr-3 text-blue-600"></i>
              Programs & Achievements
            </h2>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <!-- Academic Programs -->
              <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                  <i class="fas fa-book mr-2 text-blue-600"></i>
                  Academic Programs
                </h3>
                <div class="space-y-3">
                  <div class="flex items-center p-3 bg-blue-50 rounded-lg">
                    <div class="w-2 h-2 bg-blue-600 rounded-full mr-3"></div>
                    <span class="text-gray-700">Bachelor of Science in Computer Science</span>
                  </div>
                  <div class="flex items-center p-3 bg-purple-50 rounded-lg">
                    <div class="w-2 h-2 bg-purple-600 rounded-full mr-3"></div>
                    <span class="text-gray-700">Bachelor of Science in Information Technology</span>
                  </div>
                  <div class="flex items-center p-3 bg-green-50 rounded-lg">
                    <div class="w-2 h-2 bg-green-600 rounded-full mr-3"></div>
                    <span class="text-gray-700">Bachelor of Science in Business Administration</span>
                  </div>
                  <div class="flex items-center p-3 bg-orange-50 rounded-lg">
                    <div class="w-2 h-2 bg-orange-600 rounded-full mr-3"></div>
                    <span class="text-gray-700">Bachelor of Arts in Education</span>
                  </div>
                </div>
              </div>
              
              <!-- Recent Achievements -->
              <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                  <i class="fas fa-medal mr-2 text-purple-600"></i>
                  Recent Achievements
                </h3>
                <div class="space-y-4">
                  <div class="border-l-4 border-blue-500 pl-4 py-2">
                    <h4 class="font-medium text-gray-800">Excellence in Education Award</h4>
                    <p class="text-sm text-gray-600">Recognized for outstanding academic programs - 2023</p>
                  </div>
                  <div class="border-l-4 border-purple-500 pl-4 py-2">
                    <h4 class="font-medium text-gray-800">Research Innovation Grant</h4>
                    <p class="text-sm text-gray-600">Awarded for groundbreaking research initiatives - 2023</p>
                  </div>
                  <div class="border-l-4 border-green-500 pl-4 py-2">
                    <h4 class="font-medium text-gray-800">Community Service Recognition</h4>
                    <p class="text-sm text-gray-600">Honored for community outreach programs - 2022</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Contact Information Section -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="bg-gradient-to-r from-green-50 to-blue-50 px-6 py-4 border-b border-gray-100">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
              <i class="fas fa-phone mr-3 text-green-600"></i>
              Contact Information
            </h2>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
              <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6">
                <div class="flex items-center mb-3">
                  <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-map-marker-alt text-white"></i>
                  </div>
                  <div>
                    <h3 class="font-semibold text-gray-800">Address</h3>
                    <p class="text-sm text-gray-600">Campus location</p>
                  </div>
                </div>
                <p class="text-gray-800 font-medium">{{ institution.address || '123 Education Street, Manila, Philippines' }}</p>
              </div>
              
              <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6">
                <div class="flex items-center mb-3">
                  <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-envelope text-white"></i>
                  </div>
                  <div>
                    <h3 class="font-semibold text-gray-800">Email</h3>
                    <p class="text-sm text-gray-600">Official email</p>
                  </div>
                </div>
                <p class="text-gray-800 font-medium">{{ institution.email || 'info@institution.edu.ph' }}</p>
              </div>
              
              <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6">
                <div class="flex items-center mb-3">
                  <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-mobile-alt text-white"></i>
                  </div>
                  <div>
                    <h3 class="font-semibold text-gray-800">Mobile</h3>
                    <p class="text-sm text-gray-600">Contact number</p>
                  </div>
                </div>
                <p class="text-gray-800 font-medium">{{ institution.contact_number || '+63 912 345 6789' }}</p>
              </div>
              
              <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6">
                <div class="flex items-center mb-3">
                  <div class="w-12 h-12 bg-orange-600 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-phone text-white"></i>
                  </div>
                  <div>
                    <h3 class="font-semibold text-gray-800">Telephone</h3>
                    <p class="text-sm text-gray-600">Landline number</p>
                  </div>
                </div>
                <p class="text-gray-800 font-medium">{{ institution.telephone_number || '(02) 8123-4567' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
