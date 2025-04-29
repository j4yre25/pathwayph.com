<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps } from "vue";

const props = defineProps({
  peso: Object,
});

</script>

<template>
  <AppLayout title="PESO Profile">
    <!-- Cover Photo Section -->
    <div class="relative bg-gray-800 h-48">
      <img
        :src="peso.cover_photo || '/images/default-cover.jpg'"
        alt="Cover Photo"
        class="absolute inset-0 w-full h-full object-cover"
      />
      <div class="absolute inset-0 bg-black bg-opacity-30"></div>
    </div>

    <!-- PESO Header -->
    <div class="relative -mt-16 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="p-6 flex items-center space-x-6">
                <!-- Logo and Featured Badge -->
                <div class="relative">
                    <img
                        :src="peso.logo || '/images/default-logo.png'"
                        alt="PESO Logo"
                        class="w-24 h-24 rounded-full object-cover border-4 border-white"
                    />
                    <span
                        v-if="peso.is_featured"
                        class="absolute top-0 left-0 bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded-tr-lg rounded-bl-lg"
                    >
                        Featured
                    </span>
                </div>

                <!-- PESO Info -->
                <div class="flex-1">
                    <h3 class="text-3xl font-bold text-gray-800">{{ peso.first_name }}</h3>
           
                    <div class="mt-2 flex space-x-4">
                        <a
                            v-for="(link, key) in peso.social_links || {}"
                            :key="key"
                            :href="link"
                            target="_blank"
                            class="text-gray-500 hover:text-indigo-500"
                        >
                            <i :class="`fab fa-${key}`"></i>
                        </a>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-4">
                        <a
                            :href="route('profile.show', peso.id)"
                            class="bg-blue-500 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-600 inline-block">
                            Edit Profile
                        </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="py-12 bg-gray-100">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Overview Section -->
            
            <!-- PESO Description -->
            <div class="mt-8 bg-white shadow-lg rounded-lg p-6">
                <h4 class="text-xl font-semibold text-gray-800">PESO Description</h4>
                <p class="text-gray-600 mt-4">{{ peso.description || 'No description available.' }}</p>
            </div>

            <!-- Contact Information -->
            <div class="mt-8 bg-white shadow-lg rounded-lg p-6">
                <h4 class="text-xl font-semibold text-gray-800">Contact Information</h4>
                <div class="mt-4">
                    <p class="text-gray-600"><strong>Address:</strong> {{ peso.address || 'N/A' }}</p>
                    <p class="text-gray-600"><strong>Email:</strong> {{ peso.email || 'N/A' }}</p>
                    <p class="text-gray-600"><strong>Mobile Number:</strong> {{ peso.contact_number || 'N/A' }}</p>
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
    </div>
  </AppLayout>
</template>
