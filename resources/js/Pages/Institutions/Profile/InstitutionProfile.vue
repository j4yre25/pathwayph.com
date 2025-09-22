<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, reactive, ref, watch, computed } from "vue";
import { router } from "@inertiajs/vue3";
import { useFormattedMobileNumber } from "@/Composables/useFormattedMobileNumber.js";
import { useFormattedTelephoneNumber } from "@/Composables/useFormattedTelephoneNumber.js";

const props = defineProps({
  institution: Object,
  userRole: { type: String, required: true },
});

const localDescription = ref(props.institution.description || '');

watch(() => props.institution.description, (newVal) => {
  localDescription.value = newVal || '';
});

const contactForm = reactive({
  contact: props.institution?.contact_number || '',
  telephone: props.institution?.telephone_number || '',
});

const { formattedMobileNumber } = useFormattedMobileNumber(contactForm, 'contact');
const { formattedTelephoneNumber } = useFormattedTelephoneNumber(contactForm, 'telephone');
</script>

<template>
  <AppLayout title="Institution Profile">
    <!-- Cover Section -->
    <div class="relative h-52">
      <img
        :src="institution.cover_photo || '/images/School on a Green Hill.png'"
        alt="Institution Cover"
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
              :src="institution.logo || '/images/default-logo.png'"
              alt="Institution Logo"
              class="w-32 h-32 rounded-full border-4 border-white shadow-md object-cover" />
          </div>
        </div>

        <!-- Name + Details -->
        <div class="mt-4 text-center">
          <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ institution.name || 'Institution Name' }}</h2>
          
          <!-- Additional institution details with icons -->
          <div class="flex justify-center space-x-6 mt-2">
            <!-- Institution Type -->
            <div class="flex items-center text-gray-600 text-sm">
              <i class="fas fa-university text-blue-500 mr-1"></i>
              <span>{{ institution.type || 'Educational Institution' }}</span>
            </div>
            
            <!-- Institution ID -->
            <div class="flex items-center text-gray-600 text-sm">
              <i class="fas fa-id-card text-blue-500 mr-1"></i>
              <span>{{ institution.institution_id || 'Institution ID' }}</span>
            </div>
            
            <!-- Date Established -->
            <div class="flex items-center text-gray-600 text-sm">
              <i class="fas fa-calendar-alt text-blue-500 mr-1"></i>
              <span>Established: {{ institution.created_at ? new Date(institution.created_at).toLocaleDateString() : 'N/A' }}</span>
            </div>
          </div>
        </div>

        <!-- Social Links Row -->
        <div
          v-if="institution.social_links && Object.keys(institution.social_links).length"
          class="flex justify-center space-x-5 border-t border-gray-200 mt-6 pt-4">
          <a
            v-for="(link, key) in institution.social_links"
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
              <span>{{ institution.address || "N/A" }}</span>
            </li>
            <li class="flex items-center space-x-3">
              <div class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100">
                <i class="fas fa-envelope text-gray-500"></i>
              </div>
              <span>{{ institution.email || "N/A" }}</span>
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
        
        <!-- Faculty Members Section -->
        <section class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 flex-1">
          <h4 class="text-lg font-semibold text-gray-900 flex items-center pb-2 border-b border-gray-200">
            <i class="fas fa-users text-purple-500 mr-2"></i>
            Faculty Members
          </h4>
          
          <!-- Faculty Members List -->
          <div class="mt-4 space-y-4">
            <!-- If there are faculty members, display them -->
            <div v-if="institution.faculty_members && institution.faculty_members.length > 0">
              <div v-for="(member, index) in institution.faculty_members" :key="index" 
                   class="flex items-center p-3 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                  <span class="text-blue-600 font-semibold">{{ member.name ? member.name.charAt(0).toUpperCase() : 'F' }}</span>
                </div>
                <div class="flex-1">
                  <h5 class="font-medium text-gray-900">{{ member.name || 'Faculty Member' }}</h5>
                  <p class="text-xs text-gray-500">{{ member.position || 'Position' }}</p>
                </div>
                <div class="text-gray-400 text-sm">
                  <i class="fas fa-envelope mr-1"></i>
                  {{ member.email || 'email@example.com' }}
                </div>
              </div>
            </div>
            
            <!-- Empty state when no faculty members -->
            <div v-else class="text-center py-4">
              <div class="w-16 h-16 mx-auto flex items-center justify-center rounded-full bg-gray-100 mb-3">
                <i class="fas fa-user-plus text-gray-400 text-xl"></i>
              </div>
              <p class="text-gray-500 text-sm">No faculty members added yet</p>
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
              <i class="fas fa-users text-green-500 text-2xl"></i>
            </div>
            <div>
              <div class="text-2xl font-bold text-gray-900">
                {{ institution.student_count || 0 }}
              </div>
              <div class="text-xs text-gray-500">Total Students</div>
            </div>
          </div>
        </div>

        <!-- Academic Programs -->
        <section class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 flex-1">
          <h4 class="text-lg font-semibold text-gray-900 flex items-center pb-2 border-b border-gray-200">
            <i class="fas fa-graduation-cap text-blue-500 mr-2"></i>
            Academic Programs
          </h4>

          <div
            v-if="institution.programs && institution.programs.length"
            v-for="program in institution.programs"
            :key="program.id"
            class="mt-6 p-4 rounded-xl border border-gray-100 hover:shadow-md transition-shadow duration-200">
            <h5 class="text-lg font-semibold text-gray-900">{{ program.name }}</h5>
            <p class="text-gray-600 mt-2 leading-relaxed">{{ program.description }}</p>
            <p class="text-xs text-gray-400 mt-3">{{ program.duration || 'Duration not specified' }}</p>
          </div>

          <div v-if="!institution.programs || institution.programs.length === 0" class="mt-6 text-center text-gray-400">
            <div class="w-16 h-16 mx-auto flex items-center justify-center rounded-full bg-gray-100 mb-4">
              <i class="fas fa-graduation-cap text-2xl text-gray-400"></i>
            </div>
            <h5 class="text-lg font-semibold text-gray-700">No Programs Available</h5>
            <p class="text-sm text-gray-500 mt-2">Please check back later for new programs.</p>
          </div>
        </section>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
@keyframes float-reverse {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(10px); }
}

@keyframes pulse-glow {
  0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.3); }
  50% { box-shadow: 0 0 30px rgba(59, 130, 246, 0.5); }
}

@keyframes morph {
  0%, 100% { border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; }
  50% { border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%; }
}

.float-reverse {
  animation: float-reverse 6s ease-in-out infinite;
}

.pulse-glow {
  animation: pulse-glow 2s ease-in-out infinite;
}

.morph {
  animation: morph 8s ease-in-out infinite;
}

/* Smooth transitions */
.transition-all {
  transition: all 0.3s ease;
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .profile-container {
    margin-top: -60px;
  }
  
  .profile-avatar {
    width: 120px;
    height: 120px;
  }
  
  .profile-stats {
    flex-direction: column;
    gap: 1rem;
  }
  
  .stat-item {
    text-align: center;
  }
}
</style>
