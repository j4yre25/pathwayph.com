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
    <!-- Modern Clean Background -->
    <div class="min-h-screen bg-gray-50">
      <!-- Cover Photo Section -->
      <div class="relative h-64 bg-gradient-to-r from-blue-600 to-purple-600">
        <img
          :src="company.cover_photo_path || '/images/default-cover.jpg'"
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
                    :src="company.profile_photo_path || '/images/default-logo.png'"
                    alt="Company Logo"
                    class="w-full h-full object-cover"
                  />
                </div>
              </div>
              
              <!-- Profile Info -->
              <div class="flex-1 text-center sm:text-left">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ company.company_name }}</h1>
                <p class="text-gray-600 mb-4 flex items-center justify-center sm:justify-start">
                  <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                  {{ company.address || 'Location not available' }}
                </p>
                
                <!-- Quick Info Tags -->
                <div class="flex flex-wrap gap-2 justify-center sm:justify-start mb-4">
                  <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                    {{ company.sector || 'Industry' }}
                  </span>
                  <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                    ID: {{ company.company_id || 'N/A' }}
                  </span>
                </div>
                
                <!-- Social Links -->
                <div class="flex space-x-3 justify-center sm:justify-start">
                  <a
                    v-for="(link, key) in company.social_links || {}"
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
                  v-if="canEdit"
                  :href="route('profile.show', { id: company.id, edit: 'company' })"
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
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
              <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
                <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
              </div>
              <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Date Joined</h3>
                <p class="text-lg font-semibold text-gray-900">{{ company.created_at || 'Not available' }}</p>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
              <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
                <i class="fas fa-briefcase text-green-600 text-xl"></i>
              </div>
              <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Posted Jobs</h3>
                <p class="text-lg font-semibold text-gray-900">{{ company.job_post_count || 0 }}</p>
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
                <h2 class="text-xl font-semibold text-gray-900">About Company</h2>
                <button
                  v-if="canEdit"
                  @click="isEditing = true"
                  class="p-2 text-gray-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-all duration-200"
                >
                  <i class="fas fa-edit"></i>
                </button>
              </div>

              <!-- Edit Mode -->
              <div v-if="isEditing" class="space-y-4">
                <textarea
                  v-model="localDescription"
                  class="w-full border border-gray-300 rounded-lg p-4 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 resize-none"
                  rows="6"
                  placeholder="Describe your company, its mission, values, and what sets it apart..."
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
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200"
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
                  <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-map-marker-alt text-blue-600"></i>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-500">Address</p>
                    <p class="text-sm text-gray-900 mt-1">{{ company.address || 'No address provided' }}</p>
                  </div>
                </div>
                
                <div class="flex items-start space-x-3">
                  <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-envelope text-purple-600"></i>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-500">Email</p>
                    <p class="text-sm text-gray-900 mt-1 break-all">{{ company.company_email || 'No email provided' }}</p>
                  </div>
                </div>
                
                <div class="flex items-start space-x-3">
                  <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-mobile-alt text-green-600"></i>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-500">Mobile Number</p>
                    <p class="text-sm text-gray-900 mt-1">{{ formattedMobileNumber || 'No mobile number provided' }}</p>
                  </div>
                </div>
                
                <div class="flex items-start space-x-3">
                  <div class="w-10 h-10 rounded-lg bg-pink-50 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-phone text-pink-600"></i>
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-500">Telephone</p>
                    <p class="text-sm text-gray-900 mt-1">{{ formattedTelephoneNumber || 'No telephone number provided' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

          <!-- Company Details section has been removed and integrated with the location section above -->
          
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
