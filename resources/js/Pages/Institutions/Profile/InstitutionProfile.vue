<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps } from "vue";

const props = defineProps({
  institution: Object,
});
</script>

<template>
  <AppLayout title="Institution Profile">
    <!-- Cover Photo Section -->
    <div class="relative bg-gray-800 h-48">
      <img
        :src="institution.cover_photo || '/images/default-cover.jpg'"
        alt="Cover Photo"
        class="absolute inset-0 w-full h-full object-cover"
      />
      <div class="absolute inset-0 bg-black bg-opacity-30"></div>
    </div>

    <!-- Institution Header -->
    <div class="relative -mt-16 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="p-6 flex items-center space-x-6">
          <!-- Logo and Featured Badge -->
          <div class="relative">
            <img
              :src="institution.logo || '/images/default-logo.png'"
              alt="Institution Logo"
              class="w-24 h-24 rounded-full object-cover border-4 border-white"
            />
            <span
              v-if="institution.is_featured"
              class="absolute top-0 left-0 bg-orange-500 text-white text-xs font-bold px-2 py-1 rounded-tr-lg rounded-bl-lg"
            >
              Featured
            </span>
          </div>

          <!-- Institution Info -->
          <div class="flex-1">
            <h3 class="text-3xl font-bold text-gray-800">{{ institution.name }}</h3>
            <div class="mt-2 flex space-x-4">
              <a
                v-for="(link, key) in institution.social_links || {}"
                :key="key"
                :href="link"
                target="_blank"
                class="text-gray-500 hover:text-indigo-500"
              >
                <i :class="`fab fa-${key}`"></i>
              </a>
            </div>
          </div>

          <!-- Action Button -->
          <div class="flex space-x-4">
            <a
              :href="route('profile.show', institution.id)"
              class="bg-blue-500 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-600 inline-block"
            >
              Edit Profile
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="py-12 bg-gray-100">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Institution Description -->
        <div class="mt-8 bg-white shadow-lg rounded-lg p-6">
          <h4 class="text-xl font-semibold text-gray-800">Institution Description</h4>
          <p class="text-gray-600 mt-4">
            {{ institution.description || 'No description available.' }}
          </p>
        </div>

        <!-- Contact Information -->
        <div class="mt-8 bg-white shadow-lg rounded-lg p-6">
          <h4 class="text-xl font-semibold text-gray-800">Contact Information</h4>
          <div class="mt-4">
            <p class="text-gray-600"><strong>Address:</strong> {{ institution.address || 'N/A' }}</p>
            <p class="text-gray-600"><strong>Email:</strong> {{ institution.email || 'N/A' }}</p>
            <p class="text-gray-600"><strong>Mobile Number:</strong> {{ institution.contact_number || 'N/A' }}</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
