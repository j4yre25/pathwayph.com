<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, reactive, ref, watch, computed } from "vue";
import { router } from "@inertiajs/vue3";
import { useFormattedMobileNumber } from "@/Composables/useFormattedMobileNumber.js";
import { useFormattedTelephoneNumber } from "@/Composables/useFormattedTelephoneNumber.js";

const props = defineProps({
  company: Object,
  userRole: {
    type: String,
    required: true, // 'company' or 'graduate'
  },
});
console.log("address:", props.company);
const isEditing = ref(false);
const localDescription = ref(props.company.description || '');

watch(() => props.company.description, (newVal) => {
  localDescription.value = newVal || '';
});

const canEdit = computed(() => props.userRole === 'company');

const saveDescription = () => {
  if (!canEdit.value) return;

  const payload = {
    company_name: props.company.company_name,
    company_street_address: props.company.company_street_address,
    company_brgy: props.company.company_brgy,
    company_city: props.company.company_city,
    company_province: props.company.company_province,
    company_zip_code: props.company.company_zip_code,
    company_contact_number: props.company.company_mobile_number,
    company_email: props.company.company_email,
    company_telephone_number: props.company.company_telephone_number,
    company_description: localDescription.value,
  };

  router.post(route('company-profile.post'), payload, {
    onSuccess: () => {
      isEditing.value = false;
    },
    onError: (errors) => {
      console.error(errors);
    },
  });
};

const cancelEditing = () => {
  isEditing.value = false;
  localDescription.value = props.company.description || '';
};

const contactForm = reactive({
  contact: props.company?.mobile_phone || '',
  telephone: props.company?.telephone_number || '',
});

const { formattedMobileNumber } = useFormattedMobileNumber(contactForm, 'contact');
const { formattedTelephoneNumber } = useFormattedTelephoneNumber(contactForm, 'telephone')

</script>

<template>
  <AppLayout title="Company Profile">
    <!-- Clean White Gradient Background -->
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100 relative overflow-hidden">
      <!-- Subtle Background Elements -->
      <div class="absolute inset-0">
        <div class="absolute top-10 left-10 w-32 h-32 bg-blue-100 rounded-full opacity-30 animate-float"></div>
        <div class="absolute top-1/4 right-20 w-24 h-24 bg-purple-100 rounded-full opacity-40 animate-float-reverse"></div>
        <div class="absolute bottom-20 left-1/4 w-40 h-40 bg-green-100 rounded-full opacity-25 animate-morph"></div>
        <div class="absolute top-1/2 right-1/3 w-16 h-16 bg-pink-100 rounded-full opacity-20 animate-pulse-glow"></div>
      </div>
      
      <!-- Cover Photo Section -->
      <div class="relative h-48 overflow-hidden">
        <img
          :src="company.cover_photo_path || '/images/default-cover.jpg'"
          alt="Cover Photo"
          class="absolute inset-0 w-full h-full object-cover opacity-30"
        />
        <!-- Subtle overlay -->
        <div class="absolute inset-0 bg-gradient-to-b from-black/10 via-transparent to-black/10">
        </div>
      </div>

      <!-- Company Header -->
      <div class="relative -mt-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10">
        <div class="bg-white rounded-3xl overflow-hidden border border-gray-200 shadow-xl">
              <div class="p-6 flex flex-col md:flex-row md:items-center md:space-x-6">
                  <!-- Logo and Featured Badge -->
                  <div class="relative flex-shrink-0 mx-auto md:mx-0 mb-4 md:mb-0">
                      <div class="w-28 h-28 rounded-2xl overflow-hidden border-4 border-gray-200 bg-white shadow-lg">
                          <img
                              :src="company.profile_photo_path || '/images/default-logo.png'"
                              alt="Company Logo"
                              class="w-full h-full object-cover"
                          />
                      </div>
                  </div>

                  <!-- Company Info -->
                  <div class="flex-1 text-center md:text-left">
                      
                      <div class="space-y-4">
                          <h1 class="text-4xl font-bold text-gray-800 mb-3">{{ company.company_name }}</h1>
                          <!-- Location -->
                          <p class="text-gray-600 flex items-center space-x-2 justify-center md:justify-start text-lg">
                              <i class="fas fa-map-marker-alt text-blue-500"></i>
                              <span>{{ company.address || 'Location not available' }}</span>
                              <a
                                  v-if="company.map_link"
                                  :href="company.map_link"
                                  target="_blank"
                                  class="text-blue-600 hover:text-blue-800 text-sm flex items-center transition-all duration-200">
                                  <i class="fas fa-external-link-alt mr-1"></i> Map
                              </a>
                          </p>
                        
                          <!-- Company Details in single column -->
                          <div class="space-y-3 text-base">
                              <div class="bg-gray-50 p-3 rounded-xl border border-gray-200">
                                  <p class="text-sm text-blue-600 font-semibold">Industry Sector</p>
                                  <p class="text-gray-700">{{ company.sector || 'Not specified' }}</p>
                              </div>
                              <div class="bg-gray-50 p-3 rounded-xl border border-gray-200">
                                  <p class="text-sm text-purple-600 font-semibold">Company ID</p>
                                  <p class="text-gray-700">{{ company.company_id || 'Not available' }}</p>
                              </div>
                          </div>
                      </div>
                    
                      <div class="mt-4 flex space-x-3 justify-center md:justify-start">
                          <a
                              v-for="(link, key) in company.social_links || {}"
                              :key="key"
                              :href="link"
                              target="_blank"
                              class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-600 hover:text-blue-600 border border-gray-200 hover:border-blue-400 hover:bg-blue-50 transition-all duration-300 hover:scale-110"
                          >
                              <i :class="`fab fa-${key}`"></i>
                          </a>
                      </div>
                  </div>

                  <!-- Action Buttons -->
                  <div class="mt-4 md:mt-0 flex justify-center md:justify-end">
                          <a
                              v-if="canEdit"
                              :href="route('profile.show', { id: company.id, edit: 'company' })"
                              class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-2xl transform hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-blue-400 shadow-lg inline-flex items-center">
                              <i class="fas fa-edit mr-2"></i> Edit Profile
                          </a>
                  </div>
              </div>
          </div>
      </div>

      <!-- Main Content -->
      <div class="py-8 relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        
          <!-- Overview Section -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
              <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300">
                  <div class="flex items-center mb-3">
                      <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mr-4">
                          <i class="fas fa-calendar-alt text-blue-600 text-lg"></i>
                      </div>
                      <h4 class="text-lg font-bold text-gray-800">Date Joined</h4>
                  </div>
                  <p class="text-gray-600 ml-16 text-base">{{ company.created_at || 'Not available' }}</p>
              </div>
              
              <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300">
                  <div class="flex items-center mb-3">
                      <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center mr-4">
                          <i class="fas fa-briefcase text-green-600 text-lg"></i>
                      </div>
                      <h4 class="text-lg font-bold text-gray-800">Posted Jobs</h4>
                  </div>
                  <p class="text-gray-600 ml-16 text-base">{{ company.job_post_count || 0 }}</p>
              </div>
          </div>

          <!-- Description and Contact Info in Grid -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          
               <div class="md:col-span-2 bg-white p-6 rounded-2xl border border-gray-200 shadow-lg">
                  <div class="flex items-center justify-between mb-4">
                      <h4 class="text-2xl font-bold text-gray-800">Company Description</h4>
                      <i
                          v-if="canEdit"
                          @click="isEditing = true"
                          class="fas fa-pencil-alt text-blue-600 hover:text-blue-800 cursor-pointer text-lg transition-all duration-200 hover:scale-110"></i>
                  </div>

                  <!-- Edit Mode -->
                  <div v-if="isEditing" class="mt-4">
                      <textarea
                          v-model="localDescription"
                          class="w-full bg-gray-50 border border-gray-300 rounded-xl p-4 text-gray-800 placeholder-gray-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                          rows="6"
                          placeholder="Describe your company, its mission, values, and what sets it apart..."
                      ></textarea>
                      <div class="flex justify-end mt-4 space-x-3">
                          <button 
                              @click="cancelEditing" 
                              class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-xl text-gray-700 hover:text-gray-900 hover:bg-gray-200 transition-all duration-200 font-medium"
                          >
                              Cancel
                          </button>
                          <button 
                              @click="saveDescription" 
                              class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transform hover:scale-105 transition-all duration-300 font-semibold"
                          >
                              Save Changes
                          </button>
                      </div>
                  </div>

                  <!-- View Mode -->
                  <p v-else class="text-gray-700 mt-4 text-base leading-relaxed">{{ localDescription || 'No description available.' }}</p>
              </div>
              <!-- Contact Information -->
              <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-lg">
                  <div class="border-b border-gray-200 pb-3 mb-4">
                      <h4 class="text-xl font-bold text-gray-800">Contact Information</h4>
                  </div>
                  <div class="space-y-4">
                      <div class="flex items-center bg-gray-50 p-3 rounded-xl border border-gray-200">
                          <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center mr-4">
                              <i class="fas fa-map-marker-alt text-blue-600"></i>
                          </div>
                          <div>
                              <p class="text-sm text-blue-600 font-semibold">Address</p>
                              <p class="text-gray-700">{{ company.address || 'No address provided' }}</p>
                          </div>
                      </div>
                      
                      <div class="flex items-center bg-gray-50 p-3 rounded-xl border border-gray-200">
                          <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center mr-4">
                              <i class="fas fa-envelope text-purple-600"></i>
                          </div>
                          <div>
                              <p class="text-sm text-purple-600 font-semibold">Email</p>
                              <p class="text-gray-700">{{ company.company_email || 'No email provided' }}</p>
                          </div>
                      </div>
                      
                      <div class="flex items-center bg-gray-50 p-3 rounded-xl border border-gray-200">
                          <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center mr-4">
                              <i class="fas fa-mobile-alt text-green-600"></i>
                          </div>
                          <div>
                              <p class="text-sm text-green-600 font-semibold">Mobile Number</p>
                              <p class="text-gray-700">{{ formattedMobileNumber || 'No mobile number provided' }}</p>
                          </div>
                      </div>
                      
                      <div class="flex items-center bg-gray-50 p-3 rounded-xl border border-gray-200">
                          <div class="w-10 h-10 rounded-xl bg-pink-100 flex items-center justify-center mr-4">
                              <i class="fas fa-phone text-pink-600"></i>
                          </div>
                          <div>
                              <p class="text-sm text-pink-600 font-semibold">Telephone Number</p>
                              <p class="text-gray-700">{{ formattedTelephoneNumber || 'No telephone number provided' }}</p>
                          </div>
                      </div>
                  </div>
              </div>
          </div>

          <!-- Company Details section has been removed and integrated with the location section above -->
          
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
