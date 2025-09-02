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
    <div class="relative bg-gradient-to-br from-white via-blue-50 to-purple-100 overflow-hidden">
      <!-- Subtle Background Elements -->
      <div class="absolute inset-0">
        <div class="absolute top-10 left-10 w-32 h-32 bg-blue-200/30 rounded-full blur-xl"></div>
        <div class="absolute top-20 right-20 w-24 h-24 bg-purple-200/40 rounded-full blur-lg"></div>
        <div class="absolute bottom-20 left-1/4 w-20 h-20 bg-pink-200/30 rounded-full blur-lg"></div>
      </div>
      
      <!-- Modern Gradient Header with Abstract Shapes -->
      <div class="relative bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 py-20">
        <!-- Abstract Background Shapes -->
        <div class="absolute inset-0 overflow-hidden">
          <div class="absolute -top-10 -left-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
          <div class="absolute top-1/2 -right-20 w-60 h-60 bg-yellow-300/20 rounded-full blur-3xl"></div>
          <div class="absolute -bottom-10 left-1/3 w-32 h-32 bg-blue-300/20 rounded-full blur-xl"></div>
        </div>
        
        <!-- Navigation Breadcrumb -->
        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex items-center text-white/90 text-sm mb-8">
            <span>Institution App</span>
            <svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <span>Institution Profile</span>
          </div>
        </div>
        
        <!-- Star Icon -->
        <div class="absolute top-8 right-8 text-white/30">
          <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
          </svg>
        </div>
      </div>
      
       <!-- Profile Card -->
       <div class="relative -mt-32 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
         <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
           <!-- Profile Header -->
           <div class="px-8 py-8">
             <div class="flex flex-col items-center text-center">
               <!-- Avatar -->
               <div class="relative mb-6">
                 <img
                   :src="institution.logo || '/images/default-logo.png'"
                   alt="Institution Logo"
                   class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg"
                 />
                 <span
                   v-if="institution.is_featured"
                   class="absolute -top-2 -right-2 bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg"
                 >
                   Featured
                 </span>
               </div>
               
               <!-- Institution Info -->
               <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ institution.name || 'Institution Name' }}</h1>
               <p class="text-gray-600 mb-6">Educational Institution</p>
                  
               <!-- Stats -->
               <div class="flex items-center space-x-8 mb-6">
                 <div class="text-center">
                   <div class="text-2xl font-bold text-gray-900">150+</div>
                   <div class="text-sm text-gray-500">Students</div>
                 </div>
                 <div class="text-center">
                   <div class="text-2xl font-bold text-gray-900">25</div>
                   <div class="text-sm text-gray-500">Programs</div>
                 </div>
                 <div class="text-center">
                   <div class="text-2xl font-bold text-gray-900">15</div>
                   <div class="text-sm text-gray-500">Years</div>
                 </div>
               </div>
               
               <!-- Social Links and Action Button -->
               <div class="flex items-center space-x-4 mb-6">
                 <div class="flex space-x-3">
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
                 </div>
                 <a
                   v-if="canEdit"
                   :href="route('institution.information')"
                   class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-full font-medium transition-colors"
                 >
                   Add To Story
                 </a>
               </div>
               
               <!-- Tab Navigation -->
               <div class="flex justify-center space-x-8 border-b border-gray-200 w-full">
                 <button class="pb-4 px-4 border-b-2 border-blue-500 text-blue-600 font-medium flex items-center space-x-2 transition-colors">
                   <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                   </svg>
                   <span>Profile</span>
                 </button>
                 <button class="pb-4 px-4 text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent font-medium flex items-center space-x-2 transition-colors">
                   <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                   </svg>
                   <span>Students</span>
                 </button>
                 <button class="pb-4 px-4 text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent font-medium flex items-center space-x-2 transition-colors">
                   <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                   </svg>
                   <span>Programs</span>
                 </button>
                 <button class="pb-4 px-4 text-gray-500 hover:text-gray-700 hover:border-gray-300 border-b-2 border-transparent font-medium flex items-center space-x-2 transition-colors">
                   <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
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
