<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, reactive, ref, watch, computed } from "vue";
import { router } from "@inertiajs/vue3";
import { useFormattedMobileNumber } from "@/Composables/useFormattedMobileNumber.js";
import { useFormattedTelephoneNumber } from "@/Composables/useFormattedTelephoneNumber.js";

const props = defineProps({
  company: Object,
  userRole: { type: String, required: true },
});

console.log('company', props.company)
const isEditing = ref(false);
const localDescription = ref(props.company.description || '');

watch(() => props.company.description, (newVal) => {
  localDescription.value = newVal || '';
});

const canEdit = computed(() => props.userRole === 'company');

const saveDescription = () => {
  if (!canEdit.value) return;
  router.post(route('company-profile.post'), { company_description: localDescription.value }, {
    onSuccess: () => { isEditing.value = false; },
    onError: (errors) => console.error(errors),
  });
};

const cancelEditing = () => {
  isEditing.value = false;
  localDescription.value = props.company.description || '';
};

const contactForm = reactive({
  contact: props.company?.company_mobile_phone || '',
  telephone: props.company?.company_tel_phone || '',
});
const { formattedMobileNumber } = useFormattedMobileNumber(contactForm, 'contact');
const { formattedTelephoneNumber } = useFormattedTelephoneNumber(contactForm, 'telephone')

</script>

<template>
  <AppLayout title="Company Profile">
    <!-- Cover Section -->
    <div class="relative h-52">
      <img
        :src="company.cover_photo || '/images/company.png'"
        alt="Company Cover"
        class="absolute inset-0 w-full h-full object-cover brightness-95" />
      <div class="absolute inset-0 bg-white/30"></div>
    </div>

    <!-- Profile Container -->
    <div class="relative max-w-6xl mx-auto px-6 -mt-16">
      <div class="bg-white rounded-2xl shadow-md pb-8">
        <!-- Profile Pic -->
        <div class="flex justify-center">
          <div class="relative -mt-20">
            <img
              :src="company.profile_photo ||  '/images/default-logo.png'"
              alt="Company Logo"
              class="w-32 h-32 rounded-full border-4 border-white shadow-md object-cover" />
          </div>
        </div>

        <!-- Name + Sector -->
        <div class="mt-4 text-center">
          <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ company.company_name }}</h2>
          
          <!-- Additional company details with icons -->
          <div class="flex justify-center space-x-6 mt-2">
            <!-- Industry Sector -->
            <div class="flex items-center text-gray-600 text-sm">
              <i class="fas fa-industry text-blue-500 mr-1"></i>
              <span>{{ company.sector.name || 'Industry Sector' }}</span>
            </div>
            
            <!-- Company ID -->
            <div class="flex items-center text-gray-600 text-sm">
              <i class="fas fa-id-card text-blue-500 mr-1"></i>
              <span> {{ company.category || 'Category' }}</span>
            </div>
            
            <!-- Date Joined -->
            <div class="flex items-center text-gray-600 text-sm">
              <i class="fas fa-calendar-alt text-blue-500 mr-1"></i>
              <span>Joined: {{ company.created_at ? new Date(company.created_at).toLocaleDateString() : 'N/A' }}</span>
            </div>
          </div>
        </div>

        <!-- Social Links Row -->
        <div
          v-if="company.social_links && Object.keys(company.social_links).length"
          class="flex justify-center space-x-5 border-t border-gray-200 mt-6 pt-4">
          <a
            v-for="(link, key) in company.social_links"
            :key="key"
            :href="link"
            target="_blank"
            class="text-gray-400 hover:text-blue-500 transition-colors duration-200 text-xl">
            <i :class="`fab fa-${key}`"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-6 mt-10 grid md:grid-cols-3 gap-8 items-stretch">
      <!-- Left Column -->
      <div class="space-y-8 flex flex-col">
        <!-- About Section -->
        <section class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 flex-1">
          <h4 class="text-lg font-semibold text-gray-900 flex items-center pb-2 border-b border-gray-200">
            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
            About
          </h4>
          <div class="mt-3">
            <p class="text-gray-600 leading-relaxed text-sm">
              {{ localDescription || "No description available." }}
            </p>
          </div>
        </section>

        <!-- Contact Info -->
        <section class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 flex-1">
          <h4 class="text-lg font-semibold text-gray-900 flex items-center pb-2 border-b border-gray-200">
            <i class="fas fa-address-card text-green-500 mr-2"></i>
            Contact Information
          </h4>
          <ul class="mt-4 space-y-4 text-gray-600 text-sm">
            <li class="flex items-center space-x-3">
              <div class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100">
                <i class="fas fa-building text-gray-500"></i>
              </div>
              <span>{{ company.company_street_address + ',' + ' ' + company.company_city || "N/A" }}</span>
            </li>
            <li class="flex items-center space-x-3">
              <div class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100">
                <i class="fas fa-envelope text-gray-500"></i>
              </div>
              <span>{{ company.company_email || "N/A" }}</span>
            </li>
            <li class="flex items-center space-x-3">
              <div class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100">
                <i class="fas fa-mobile-alt text-gray-500"></i>
              </div>
              <span>{{ formattedMobileNumber || "N/A" }}</span>
            </li>
            <li class="flex items-center space-x-3">
              <div class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100">
                <i class="fas fa-phone text-gray-500"></i>
              </div>
              <span>{{ formattedTelephoneNumber || "N/A" }}</span>
            </li>
          </ul>
        </section>
        
        <!-- Team Members Section -->
        <section class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 flex-1">
          <h4 class="text-lg font-semibold text-gray-900 flex items-center pb-2 border-b border-gray-200">
            <i class="fas fa-users text-purple-500 mr-2"></i>
            Team Members
          </h4>
          
          <!-- Team Members List -->
          <div class="mt-4 space-y-4">
            <!-- If there are team members, display them -->
            <div v-if="company.team_members && company.team_members.length > 0">
              <div v-for="(member, index) in company.team_members" :key="index" 
                   class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                  <span class="text-blue-600 font-semibold">{{ member.name ? member.name.charAt(0).toUpperCase() : 'U' }}</span>
                </div>
                <div class="flex-1">
                  <h5 class="font-medium text-gray-900">{{ member.name || 'Team Member' }}</h5>
                  <p class="text-xs text-gray-500">{{ member.position || 'Position' }}</p>
                </div>
                <div class="text-gray-400 text-sm">
                  <i class="fas fa-envelope mr-1"></i>
                  {{ member.email || 'email@example.com' }}
                </div>
              </div>
            </div>
            
            <!-- Empty state when no team members -->
            <div v-else class="text-center py-4">
              <div class="w-16 h-16 mx-auto flex items-center justify-center rounded-full bg-gray-100 mb-3">
                <i class="fas fa-user-plus text-gray-400 text-xl"></i>
              </div>
              <p class="text-gray-500 text-sm">No team members added yet</p>
            </div>
          </div>
        </section>
      </div>

      <!-- Right Column -->
      <div class="md:col-span-2 flex flex-col">
        <!-- Stat Cards -->
        <div class="flex space-x-6 mb-6">
          <div class="flex-1 bg-white rounded-xl shadow p-4 border border-gray-100 flex items-center">
            <div class="w-12 h-12 flex items-center justify-center rounded-full bg-green-100 mr-4">
              <i class="fas fa-check-circle text-green-500 text-2xl"></i>
            </div>
            <div>
              <div class="text-2xl font-bold text-gray-900">
                {{ company.job_post_count || 0 }}
              </div>
              <div class="text-xs text-gray-500">Available Jobs</div>
            </div>
          </div>
        </div>

        <!-- Job Opportunities (optional; requires jobs array in payload) -->
        <section class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 flex-1">
          <h4 class="text-lg font-semibold text-gray-900 flex items-center pb-2 border-b border-gray-200">
            <i class="fas fa-briefcase text-blue-500 mr-2"></i>
            Jobs For You
          </h4>

          <div v-if="company.jobs && company.jobs.length" v-for="job in company.jobs" :key="job.id"
            class="mt-6 p-4 rounded-xl border border-gray-100 hover:shadow-md transition-shadow duration-200">
            <h5 class="text-lg font-semibold text-gray-900">{{ job.job_title }}</h5>
            <p class="text-gray-600 mt-2 leading-relaxed">{{ job.job_description }}</p>
            <p class="text-xs text-gray-400 mt-3">Posted {{ job.created_at }}</p>
          </div>

          <div v-if="!company.jobs || company.jobs.length === 0" class="mt-6 text-center text-gray-400">
            <div class="w-16 h-16 mx-auto flex items-center justify-center rounded-full bg-gray-100 mb-4">
              <i class="fas fa-briefcase text-2xl text-gray-400"></i>
            </div>
            <h5 class="text-lg font-semibold text-gray-700">No Jobs Available</h5>
            <p class="text-sm text-gray-500 mt-2">Please check back later for new opportunities.</p>
          </div>
        </section>
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
