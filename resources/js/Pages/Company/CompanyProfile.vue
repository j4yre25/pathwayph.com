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
      
      <!-- Modern Gradient Header Section -->
      <div class="relative h-80 overflow-hidden bg-gradient-to-br from-purple-600 via-blue-600 to-teal-500">
        <!-- Abstract Shapes -->
        <div class="absolute inset-0">
          <div class="absolute top-10 left-10 w-32 h-32 bg-white/10 rounded-full animate-float"></div>
          <div class="absolute top-20 right-20 w-24 h-24 bg-white/5 rounded-full animate-float-reverse"></div>
          <div class="absolute bottom-10 left-1/4 w-40 h-40 bg-white/10 rounded-full animate-morph"></div>
        </div>
        
        <!-- Navigation Breadcrumb -->
        <div class="relative z-10 pt-6 px-6">
          <nav class="flex items-center space-x-2 text-white/80 text-sm">
            <span>Home</span>
            <i class="fas fa-chevron-right text-xs"></i>
            <span>Company Profile</span>
          </nav>
        </div>
        
        <!-- Star Icon -->
        <div class="absolute top-6 right-6 z-10">
          <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
            <i class="fas fa-star text-white text-xl"></i>
          </div>
        </div>
        
        <!-- Profile Card -->
        <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 translate-y-1/2 z-20">
          <div class="bg-white rounded-3xl p-8 shadow-2xl border border-gray-100 min-w-[600px]">
            <!-- Company Avatar and Info -->
            <div class="flex flex-col items-center text-center">
              <div class="relative mb-4">
                <div class="w-24 h-24 rounded-2xl overflow-hidden border-4 border-white shadow-lg bg-gradient-to-br from-blue-100 to-purple-100">
                  <img
                    :src="company.profile_photo_path || '/images/default-logo.png'"
                    alt="Company Logo"
                    class="w-full h-full object-cover"
                  />
                </div>
                <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-green-500 rounded-full border-4 border-white flex items-center justify-center">
                  <i class="fas fa-check text-white text-xs"></i>
                </div>
              </div>
              
              <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ company.company_name }}</h1>
              <p class="text-gray-600 mb-4">{{ company.sector || 'Technology Company' }}</p>
              
              <!-- Stats -->
              <div class="flex items-center space-x-8 mb-6">
                <div class="text-center">
                  <div class="text-2xl font-bold text-gray-800">{{ company.job_post_count || 0 }}</div>
                  <div class="text-sm text-gray-500">Jobs Posted</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-gray-800">{{ company.employee_count || '50+' }}</div>
                  <div class="text-sm text-gray-500">Employees</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-gray-800">{{ company.years_active || '5+' }}</div>
                  <div class="text-sm text-gray-500">Years Active</div>
                </div>
              </div>
              
              <!-- Social Links and Action Button -->
              <div class="flex items-center justify-center space-x-4 mb-6">
                <a
                  v-for="(link, key) in company.social_links || {}"
                  :key="key"
                  :href="link"
                  target="_blank"
                  class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-all duration-300"
                >
                  <i :class="`fab fa-${key}`"></i>
                </a>
                <a
                  v-if="canEdit"
                  :href="route('profile.show', { id: company.id, edit: 'company' })"
                  class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-full transition-all duration-300 text-sm"
                >
                  Edit Profile
                </a>
              </div>
              
              <!-- Tab Navigation -->
              <div class="flex items-center space-x-6 border-b border-gray-200 w-full">
                <button class="pb-3 px-1 border-b-2 border-blue-600 text-blue-600 font-medium text-sm">
                  <i class="fas fa-user mr-2"></i>Profile
                </button>
                <button class="pb-3 px-1 text-gray-500 hover:text-gray-700 font-medium text-sm">
                  <i class="fas fa-briefcase mr-2"></i>Jobs
                </button>
                <button class="pb-3 px-1 text-gray-500 hover:text-gray-700 font-medium text-sm">
                  <i class="fas fa-images mr-2"></i>Gallery
                </button>
                <button class="pb-3 px-1 text-gray-500 hover:text-gray-700 font-medium text-sm">
                  <i class="fas fa-star mr-2"></i>Reviews
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="pt-32 pb-12 relative z-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

          <!-- About Section -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-6 py-4 border-b border-gray-100">
              <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-building mr-3 text-blue-600"></i>
                About {{ company.company_name }}
              </h2>
            </div>
            <div class="p-6">
              <!-- Edit Mode -->
              <div v-if="isEditing" class="space-y-4">
                <textarea
                  v-model="localDescription"
                  class="w-full bg-gray-50 border border-gray-300 rounded-xl p-4 text-gray-800 placeholder-gray-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 resize-none"
                  rows="6"
                  placeholder="Tell us about your company's mission, values, culture, and what makes you unique..."
                ></textarea>
                <div class="flex justify-end space-x-3">
                  <button 
                    @click="cancelEditing" 
                    class="px-6 py-2 bg-gray-100 border border-gray-300 rounded-xl text-gray-700 hover:text-gray-900 hover:bg-gray-200 transition-all duration-200 font-medium"
                  >
                    Cancel
                  </button>
                  <button 
                    @click="saveDescription" 
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition-all duration-200"
                  >
                    Save Changes
                  </button>
                </div>
              </div>

              <!-- View Mode -->
              <div v-else>
                <div class="flex justify-between items-start mb-4">
                  <p class="text-gray-700 leading-relaxed text-lg">{{ localDescription || 'We are a forward-thinking company dedicated to innovation and excellence. Our team is passionate about creating solutions that make a difference in the world.' }}</p>
                  <button
                    v-if="canEdit"
                    @click="isEditing = true"
                    class="ml-4 p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200"
                  >
                    <i class="fas fa-edit"></i>
                  </button>
                </div>
                
                <!-- Company Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                  <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4">
                    <div class="flex items-center mb-2">
                      <i class="fas fa-industry text-blue-600 mr-3"></i>
                      <span class="text-sm font-medium text-blue-800">Industry</span>
                    </div>
                    <p class="text-gray-800 font-semibold">{{ company.sector || 'Technology' }}</p>
                  </div>
                  
                  <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4">
                    <div class="flex items-center mb-2">
                      <i class="fas fa-calendar-alt text-green-600 mr-3"></i>
                      <span class="text-sm font-medium text-green-800">Founded</span>
                    </div>
                    <p class="text-gray-800 font-semibold">{{ company.created_at || '2019' }}</p>
                  </div>
                  
                  <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4">
                    <div class="flex items-center mb-2">
                      <i class="fas fa-map-marker-alt text-purple-600 mr-3"></i>
                      <span class="text-sm font-medium text-purple-800">Location</span>
                    </div>
                    <p class="text-gray-800 font-semibold">{{ company.address || 'Philippines' }}</p>
                  </div>
                  
                  <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-4">
                    <div class="flex items-center mb-2">
                      <i class="fas fa-id-card text-orange-600 mr-3"></i>
                      <span class="text-sm font-medium text-orange-800">Company ID</span>
                    </div>
                    <p class="text-gray-800 font-semibold">{{ company.company_id || 'COMP-001' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Job Opportunities Section -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-8 overflow-hidden">
            <div class="bg-gradient-to-r from-green-50 to-teal-50 px-6 py-4 border-b border-gray-100">
              <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-briefcase mr-3 text-green-600"></i>
                Current Opportunities
              </h2>
            </div>
            <div class="p-6">
              <div class="text-center py-8">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <i class="fas fa-briefcase text-green-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ company.job_post_count || 0 }} Active Job Postings</h3>
                <p class="text-gray-600 mb-6">We're always looking for talented individuals to join our team.</p>
                <button class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition-all duration-200">
                  View All Jobs
                </button>
              </div>
            </div>
          </div>

          <!-- Contact Information Section -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-b border-gray-100">
              <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-phone mr-3 text-purple-600"></i>
                Get In Touch
              </h2>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 hover:shadow-md transition-all duration-200">
                  <div class="flex items-center mb-3">
                    <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center mr-4">
                      <i class="fas fa-map-marker-alt text-white"></i>
                    </div>
                    <div>
                      <h3 class="font-semibold text-gray-800">Office Address</h3>
                      <p class="text-sm text-gray-600">Visit us at our location</p>
                    </div>
                  </div>
                  <p class="text-gray-800 font-medium">{{ company.address || 'Manila, Philippines' }}</p>
                </div>
                
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 hover:shadow-md transition-all duration-200">
                  <div class="flex items-center mb-3">
                    <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center mr-4">
                      <i class="fas fa-envelope text-white"></i>
                    </div>
                    <div>
                      <h3 class="font-semibold text-gray-800">Email Address</h3>
                      <p class="text-sm text-gray-600">Send us a message</p>
                    </div>
                  </div>
                  <p class="text-gray-800 font-medium">{{ company.company_email || 'contact@company.com' }}</p>
                </div>
                
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 hover:shadow-md transition-all duration-200">
                  <div class="flex items-center mb-3">
                    <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center mr-4">
                      <i class="fas fa-mobile-alt text-white"></i>
                    </div>
                    <div>
                      <h3 class="font-semibold text-gray-800">Mobile Number</h3>
                      <p class="text-sm text-gray-600">Call us anytime</p>
                    </div>
                  </div>
                  <p class="text-gray-800 font-medium">{{ formattedMobileNumber || '+63 912 345 6789' }}</p>
                </div>
                
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl p-6 hover:shadow-md transition-all duration-200">
                  <div class="flex items-center mb-3">
                    <div class="w-12 h-12 bg-orange-600 rounded-full flex items-center justify-center mr-4">
                      <i class="fas fa-phone text-white"></i>
                    </div>
                    <div>
                      <h3 class="font-semibold text-gray-800">Telephone</h3>
                      <p class="text-sm text-gray-600">Office landline</p>
                    </div>
                  </div>
                  <p class="text-gray-800 font-medium">{{ formattedTelephoneNumber || '(02) 8123 4567' }}</p>
                </div>
              </div>
              
              <!-- Contact CTA -->
              <div class="mt-8 text-center bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Ready to Join Our Team?</h3>
                <p class="text-gray-600 mb-4">We'd love to hear from you. Get in touch with us today!</p>
                <div class="flex justify-center space-x-4">
                  <button class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-all duration-200">
                    <i class="fas fa-envelope mr-2"></i>Send Message
                  </button>
                  <button class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-xl transition-all duration-200">
                    <i class="fas fa-phone mr-2"></i>Call Now
                  </button>
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
