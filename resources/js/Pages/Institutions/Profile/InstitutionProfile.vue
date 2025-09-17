<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { router } from "@inertiajs/vue3";
import { defineProps, ref, computed } from "vue";

const props = defineProps({
  institution: Object,
});

const isEditing = ref(false);
const localDescription = ref(props.institution.description || '');

const canEdit = computed(() => true);

const saveDescription = () => {
  router.post(
    route('institution.profile.description.update'),
    { description: localDescription.value },
    {
      preserveScroll: true,
      onSuccess: () => {
        isEditing.value = false;
      },
      onError: () => {
        // Handle errors
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
    <!-- Cover Section -->
    <div class="relative h-80 bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 overflow-hidden">
      <!-- Cover Image -->
      <div class="absolute inset-0">
        <img
          src="/images/School on a Green Hill.png"
          alt="Institution Cover"
          class="w-full h-full object-cover opacity-80"
        />
        <div class="absolute inset-0 bg-gradient-to-r from-blue-900/50 to-purple-900/50"></div>
      </div>
      
      
      <!-- Navigation Breadcrumb -->
      <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        <div class="flex items-center text-white/90 text-sm">
          <span>Institution</span>
          <svg class="w-4 h-4 mx-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
          </svg>
          <span>Profile</span>
        </div>
      </div>
    </div>

    <!-- Profile Container -->
    <div class="relative -mt-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
      <!-- Profile Card -->
      <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
        <div class="px-8 py-8">
          <div class="flex flex-col md:flex-row items-center md:items-start space-y-6 md:space-y-0 md:space-x-8">
            <!-- Avatar -->
            <div class="relative flex-shrink-0">
              <img
                :src="institution.logo || '/images/default-logo.png'"
                alt="Institution Logo"
                class="w-32 h-32 rounded-2xl object-cover border-4 border-white shadow-lg"
              />
            </div>
            
            <!-- Institution Info -->
            <div class="flex-1 text-center md:text-left">
              <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ institution.name || 'Institution Name' }}</h1>
              <p class="text-gray-600 mb-4">Educational Institution</p>
                  
              <!-- Stats -->
              <div class="flex flex-wrap items-center gap-6 mb-6">
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
              
              <!-- Social Links -->
              <div class="flex flex-wrap items-center gap-3">
                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white hover:bg-blue-600 transition-colors cursor-pointer">
                  <i class="fab fa-facebook-f"></i>
                </div>
                <div class="w-10 h-10 bg-blue-400 rounded-full flex items-center justify-center text-white hover:bg-blue-500 transition-colors cursor-pointer">
                  <i class="fab fa-twitter"></i>
                </div>
                <div class="w-10 h-10 bg-pink-500 rounded-full flex items-center justify-center text-white hover:bg-pink-600 transition-colors cursor-pointer">
                  <i class="fab fa-instagram"></i>
                </div>
                <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center text-white hover:bg-red-600 transition-colors cursor-pointer">
                  <i class="fab fa-youtube"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        <!-- Left Column -->
        <div class="lg:col-span-3 space-y-6">
          <!-- About Section -->
          <section class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">
            <h4 class="text-lg font-semibold text-gray-900 flex items-center pb-2 border-b border-gray-200">
              <i class="fas fa-university text-blue-500 mr-2"></i>
              About Our Institution
            </h4>
            
            <div v-if="isEditing" class="mt-6 space-y-4">
              <textarea
                v-model="localDescription"
                class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 p-4"
                rows="6"
                placeholder="Tell us about your institution, its mission, vision, and values..."
              ></textarea>
              <div class="flex space-x-3">
                <button 
                  @click="saveDescription" 
                  class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-all duration-200"
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
            <div v-else class="mt-6">
              <div class="flex justify-between items-start mb-4">
                <p class="text-gray-700 leading-relaxed flex-1">
                  {{ localDescription || 'We are committed to providing quality education and fostering academic excellence. Our institution has been a cornerstone of learning and development, preparing students for successful careers and meaningful contributions to society.' }}
                </p>
                <button
                  v-if="canEdit"
                  @click="isEditing = true"
                  class="ml-4 px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium rounded-lg transition-all duration-200"
                  title="Edit Description"
                >
                  <i class="fas fa-edit mr-2"></i>Edit
                </button>
              </div>
            </div>
          </section>

          <!-- Institution Details -->
          <section class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">
            <h4 class="text-lg font-semibold text-gray-900 flex items-center pb-2 border-b border-gray-200">
              <i class="fas fa-info-circle text-green-500 mr-2"></i>
              Institution Details
            </h4>
            
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex items-center p-4 bg-blue-50 rounded-xl">
                <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center mr-4">
                  <i class="fas fa-calendar-alt text-white"></i>
                </div>
                <div>
                  <h5 class="font-medium text-gray-900">Established</h5>
                  <p class="text-sm text-gray-600">{{ institution.created_at || 'January 2020' }}</p>
                </div>
              </div>
              
              <div class="flex items-center p-4 bg-purple-50 rounded-xl">
                <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center mr-4">
                  <i class="fas fa-user-tie text-white"></i>
                </div>
                <div>
                  <h5 class="font-medium text-gray-900">President</h5>
                  <p class="text-sm text-gray-600">
                    {{ institution.institution_president_first_name || 'Dr. Maria' }}
                    {{ institution.institution_president_last_name || 'Santos' }}
                  </p>
                </div>
              </div>
              
              <div class="flex items-center p-4 bg-green-50 rounded-xl">
                <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center mr-4">
                  <i class="fas fa-map-marker-alt text-white"></i>
                </div>
                <div>
                  <h5 class="font-medium text-gray-900">Location</h5>
                  <p class="text-sm text-gray-600">{{ institution.address || 'Manila, Philippines' }}</p>
                </div>
              </div>
              
              <div class="flex items-center p-4 bg-orange-50 rounded-xl">
                <div class="w-12 h-12 bg-orange-600 rounded-full flex items-center justify-center mr-4">
                  <i class="fas fa-graduation-cap text-white"></i>
                </div>
                <div>
                  <h5 class="font-medium text-gray-900">Type</h5>
                  <p class="text-sm text-gray-600">Educational Institution</p>
                </div>
              </div>
            </div>
          </section>

          <!-- Contact Information -->
          <section class="bg-white rounded-2xl shadow-md p-6 border border-gray-100">
            <h4 class="text-lg font-semibold text-gray-900 flex items-center pb-2 border-b border-gray-200">
              <i class="fas fa-phone text-purple-500 mr-2"></i>
              Contact Information
            </h4>
            
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex items-center p-4 bg-blue-50 rounded-xl">
                <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center mr-4">
                  <i class="fas fa-envelope text-white"></i>
                </div>
                <div>
                  <h5 class="font-medium text-gray-900">Email</h5>
                  <p class="text-sm text-gray-600">{{ institution.email || 'info@institution.edu.ph' }}</p>
                </div>
              </div>
              
              <div class="flex items-center p-4 bg-green-50 rounded-xl">
                <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center mr-4">
                  <i class="fas fa-mobile-alt text-white"></i>
                </div>
                <div>
                  <h5 class="font-medium text-gray-900">Mobile</h5>
                  <p class="text-sm text-gray-600">{{ institution.contact_number || '+63 912 345 6789' }}</p>
                </div>
              </div>
              
              <div class="flex items-center p-4 bg-purple-50 rounded-xl">
                <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center mr-4">
                  <i class="fas fa-phone text-white"></i>
                </div>
                <div>
                  <h5 class="font-medium text-gray-900">Telephone</h5>
                  <p class="text-sm text-gray-600">{{ institution.telephone_number || '(02) 8123-4567' }}</p>
                </div>
              </div>
              
              <div class="flex items-center p-4 bg-orange-50 rounded-xl">
                <div class="w-12 h-12 bg-orange-600 rounded-full flex items-center justify-center mr-4">
                  <i class="fas fa-map-marker-alt text-white"></i>
                </div>
                <div>
                  <h5 class="font-medium text-gray-900">Address</h5>
                  <p class="text-sm text-gray-600">{{ institution.address || '123 Education Street, Manila, Philippines' }}</p>
                </div>
              </div>
            </div>
          </section>
        </div>

        <!-- Right Column -->
        <div class="lg:col-span-2 flex flex-col">
          <!-- Stat Cards -->
          <div class="flex space-x-6 mb-6">
            <div class="flex-1 bg-white rounded-xl shadow p-4 border border-gray-100 flex items-center">
              <div class="w-12 h-12 flex items-center justify-center rounded-full bg-blue-100 mr-4">
                <i class="fas fa-users text-blue-500 text-2xl"></i>
              </div>
              <div>
                <div class="text-2xl font-bold text-gray-900">150+</div>
                <div class="text-xs text-gray-500">Students</div>
              </div>
            </div>
          </div>

          <!-- Academic Programs -->
          <section class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 flex-1">
            <h4 class="text-lg font-semibold text-gray-900 flex items-center pb-2 border-b border-gray-200">
              <i class="fas fa-graduation-cap text-green-500 mr-2"></i>
              Academic Programs
            </h4>

            <!-- Programs List -->
            <div class="mt-6 space-y-4">
              <div class="p-4 rounded-xl border border-gray-100 hover:shadow-md transition-shadow duration-200">
                <h5 class="text-lg font-semibold text-gray-900">Bachelor of Science in Computer Science</h5>
                <p class="text-gray-600 mt-2 leading-relaxed">Comprehensive program covering software development, algorithms, and computer systems.</p>
                <p class="text-xs text-gray-400 mt-3">4-year program</p>
              </div>

              <div class="p-4 rounded-xl border border-gray-100 hover:shadow-md transition-shadow duration-200">
                <h5 class="text-lg font-semibold text-gray-900">Bachelor of Science in Information Technology</h5>
                <p class="text-gray-600 mt-2 leading-relaxed">Focus on practical IT skills, network administration, and system management.</p>
                <p class="text-xs text-gray-400 mt-3">4-year program</p>
              </div>

              <div class="p-4 rounded-xl border border-gray-100 hover:shadow-md transition-shadow duration-200">
                <h5 class="text-lg font-semibold text-gray-900">Bachelor of Science in Business Administration</h5>
                <p class="text-gray-600 mt-2 leading-relaxed">Comprehensive business education covering management, finance, and marketing.</p>
                <p class="text-xs text-gray-400 mt-3">4-year program</p>
              </div>

              <div class="p-4 rounded-xl border border-gray-100 hover:shadow-md transition-shadow duration-200">
                <h5 class="text-lg font-semibold text-gray-900">Bachelor of Arts in Education</h5>
                <p class="text-gray-600 mt-2 leading-relaxed">Teacher preparation program with focus on pedagogy and subject specialization.</p>
                <p class="text-xs text-gray-400 mt-3">4-year program</p>
              </div>
            </div>

            <!-- Empty State -->
            <div v-if="false" class="mt-6 text-center text-gray-400">
              <div class="w-16 h-16 mx-auto flex items-center justify-center rounded-full bg-gray-100 mb-4">
                <i class="fas fa-graduation-cap text-2xl text-gray-400"></i>
              </div>
              <h5 class="text-lg font-semibold text-gray-700">No Programs Available</h5>
              <p class="text-sm text-gray-500 mt-2">Please check back later for new programs.</p>
            </div>
          </section>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Clean modern styles for white theme */

/* Floating animations */
@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

@keyframes float-reverse {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(20px);
    }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

.animate-float-reverse {
    animation: float-reverse 4s ease-in-out infinite;
}

/* Subtle pulse effect for icons */
@keyframes pulse-glow {
    0%, 100% {
        box-shadow: 0 0 10px rgba(59, 130, 246, 0.2);
    }
    50% {
        box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);
    }
}

.animate-pulse-glow {
    animation: pulse-glow 3s ease-in-out infinite;
}

/* Morphing shapes - simplified */
@keyframes morph {
    0%, 100% {
        border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%;
    }
    50% {
        border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%;
    }
}

.animate-morph {
    animation: morph 8s ease-in-out infinite;
}

/* Smooth transitions */
* {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .animate-float,
    .animate-float-reverse {
        animation-duration: 3s;
    }
}
</style>
