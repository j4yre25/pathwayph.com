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
    <!-- Cover Photo Section -->
    <div class="relative bg-gray-800 h-40">
      <img
        :src="company.cover_photo_path || '/images/default-cover.jpg'"
        alt="Cover Photo"
        class="absolute inset-0 w-full h-full object-cover"
      />
      <!-- Overlay gradient without company name -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent">
      </div>
    </div>

    <!-- Company Header -->
    <div class="relative -mt-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="bg-white rounded-lg overflow-hidden border border-gray-200">
            <div class="p-4 flex flex-col md:flex-row md:items-center md:space-x-6">
                <!-- Logo and Featured Badge -->
                <div class="relative flex-shrink-0 mx-auto md:mx-0 mb-4 md:mb-0">
                    <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-white">
                        <img
                            :src="company.profile_photo_path || '/images/default-logo.png'"
                            alt="Company Logo"
                            class="w-full h-full object-cover"
                        />
                    </div>
                </div>

                <!-- Company Info -->
                <div class="flex-1 text-center md:text-left">
                    
                    <div class="space-y-3">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2 border-b-2 border-indigo-500 pb-2 inline-block">{{ company.company_name }}</h1>
                        <!-- Location -->
                        <p class="text-gray-600 flex items-center space-x-2 justify-center md:justify-start">
                            <MapPin class="w-4 h-4 text-indigo-500" />
                            <span>{{ company.address || 'Location not available' }}</span>
                            <a
                                v-if="company.map_link"
                                :href="company.map_link"
                                target="_blank"
                                class="text-indigo-500 hover:underline text-sm flex items-center">
                                <ExternalLink class="w-3 h-3 mr-1" /> Map
                            </a>
                        </p>
                        
                        <!-- Company Details in single column -->
                        <div class="space-y-2 text-sm">
                            <div>
                                <p class="text-xs text-indigo-600 font-medium">Industry Sector</p>
                                <p class="text-gray-700">{{ company.sector || 'Not specified' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-indigo-600 font-medium">Company ID</p>
                                <p class="text-gray-700">{{ company.company_id || 'Not available' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3 flex space-x-3 justify-center md:justify-start">
                        <a
                            v-for="(link, key) in company.social_links || {}"
                            :key="key"
                            :href="link"
                            target="_blank"
                            class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-indigo-100 hover:text-indigo-600 transition-colors duration-200"
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
                            class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-all duration-200 inline-flex items-center text-sm">
                            <Edit2 class="w-4 h-4 mr-1" /> Edit Profile
                        </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="py-4 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        
            <!-- Overview Section -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center mb-2">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                            <Calendar class="w-4 h-4 text-blue-600" />
                        </div>
                        <h4 class="text-base font-semibold text-gray-800">Date Joined</h4>
                    </div>
                    <p class="text-gray-700 ml-11">{{ company.created_at || 'Not available' }}</p>
                </div>
                
                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center mb-2">
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center mr-3">
                            <Building2 class="w-4 h-4 text-green-600" />
                        </div>
                        <h4 class="text-base font-semibold text-gray-800">Branch</h4>
                    </div>
                    <p class="text-gray-700 ml-11">{{ company.branch || 'Not available' }}</p>
                </div>
                
                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center mb-2">
                        <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center mr-3">
                            <Briefcase class="w-4 h-4 text-purple-600" />
                        </div>
                        <h4 class="text-base font-semibold text-gray-800">Posted Jobs</h4>
                    </div>
                    <p class="text-gray-700 ml-11">{{ company.job_post_count || 0 }}</p>
                </div>
            </div>

            <!-- Description and Contact Info in Grid -->
            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            
                 <div class="md:col-span-2 bg-white c">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xl font-semibold text-gray-800">Company Description</h4>
                        <Pencil
                            v-if="canEdit"
                            @click="isEditing = true"
                            class="w-5 h-5 text-gray-500 hover:text-blue-500 cursor-pointer"/>
                    </div>

                    <!-- Edit Mode -->
                    <div v-if="isEditing" class="mt-4">
                        <textarea
                            v-model="localDescription"
                            class="w-full border border-gray-300 rounded-lg p-2 text-sm"
                            rows="5"
                            placeholder="Describe your company, its mission, values, and what sets it apart..."
                        ></textarea>
                        <div class="flex justify-end mt-2 space-x-2">
                            <button 
                                @click="cancelEditing" 
                                class="px-3 py-1 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors duration-200 font-medium text-sm"
                            >
                                Cancel
                            </button>
                            <button 
                                @click="saveDescription" 
                                class="bg-blue-600 text-white px-3 py-1 rounded-md hover:bg-blue-700 transition-colors duration-200 font-medium text-sm"
                            >
                                Save Changes
                            </button>
                        </div>
                    </div>

                    <!-- View Mode -->
                    <p v-else class="text-gray-600 mt-4">{{ localDescription || 'No description available.' }}</p>
                </div>
                <!-- Contact Information -->
                <div class="bg-white rounded-lg p-4 border border-gray-200">
                    <div class="border-b border-gray-200 pb-2 mb-3">
                        <h4 class="text-lg font-semibold text-gray-800">Contact Information</h4>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                <MapPin class="w-4 h-4 text-indigo-600" />
                            </div>
                            <div>
                                <p class="text-xs text-indigo-600 font-medium">Address</p>
                                <p class="text-gray-700 text-sm">{{ company.address || 'No address provided' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                <Mail class="w-4 h-4 text-indigo-600" />
                            </div>
                            <div>
                                <p class="text-xs text-indigo-600 font-medium">Email</p>
                                <p class="text-gray-700 text-sm">{{ company.company_email || 'No email provided' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                <Smartphone class="w-4 h-4 text-indigo-600" />
                            </div>
                            <div>
                                <p class="text-xs text-indigo-600 font-medium">Mobile Number</p>
                                <p class="text-gray-700 text-sm">{{ formattedMobileNumber || 'No mobile number provided' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center mr-3">
                                <Phone class="w-4 h-4 text-indigo-600" />
                            </div>
                            <div>
                                <p class="text-xs text-indigo-600 font-medium">Telephone Number</p>
                                <p class="text-gray-700 text-sm">{{ formattedTelephoneNumber || 'No telephone number provided' }}</p>
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