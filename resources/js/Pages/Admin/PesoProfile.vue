<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps } from "vue";

const props = defineProps({
  peso: Object,
  jobs: Array,
});



</script>

<template>
  <AppLayout title="PESO Profile">
    <!-- Cover Section -->
    <div class="relative h-52">
      <img src="/images/peso_bg.png" alt="Cover" class="absolute inset-0 w-full h-full object-cover brightness-95" />
      <div class="absolute inset-0 bg-white/30"></div>
    </div>

    <!-- Profile Container -->
    <div class="relative max-w-6xl mx-auto px-6 -mt-16">
      <div class="bg-white rounded-2xl shadow-md pb-8">
        <!-- Profile Pic -->
        <div class="flex justify-center">
          <div class="relative -mt-20">
            <img :src="peso.logo ? '/storage/' + peso.logo : '/images/default-logo.png'" alt="PESO Logo"
              class="w-32 h-32 rounded-full border-4 border-white shadow-md object-cover" />
          </div>
        </div>

        <!-- Name + Role -->
        <div class="mt-4 text-center">
          <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ peso.peso_first_name }}</h2>
          <p class="text-gray-500 text-sm mt-1">{{ peso.role || "Public Employment Service Office" }}</p>
        </div>

        <!-- Social Links Row -->
        <div v-if="peso.social_links && Object.keys(peso.social_links).length"
          class="flex justify-center space-x-5 border-t border-gray-200 mt-6 pt-4">
          <a v-for="(link, key) in peso.social_links" :key="key" :href="link" target="_blank"
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
        <!-- Introduction -->
        <section class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 flex-1">
          <h4 class="text-lg font-semibold text-gray-900 flex items-center pb-2 border-b border-gray-200">
            <i class="fas fa-info-circle text-blue-500 mr-2"></i>
            Introduction
          </h4>
          <p class="text-gray-600 mt-3 leading-relaxed text-sm">
            {{ peso.description || "No description available." }}
          </p>
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
                <i class="far fa-building text-gray-500"></i>
              </div>
              <span>{{ peso.address || "N/A" }}</span>
            </li>
            <li class="flex items-center space-x-3">
              <div class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100">
                <i class="far fa-envelope text-gray-500"></i>
              </div>
              <span>{{ peso.email || "N/A" }}</span>
            </li>
            <li class="flex items-center space-x-3">
              <div class="w-9 h-9 flex items-center justify-center rounded-full bg-gray-100">
                <i class="fas fa-phone text-gray-500"></i>
              </div>
              <span>{{ peso.contact_number || "N/A" }}</span>
            </li>
          </ul>
        </section>
      </div>

      <!-- Right Column -->
      <div class="md:col-span-2 flex flex-col">
        <!-- Job Announcements -->
        <section class="bg-white rounded-2xl shadow-md p-6 border border-gray-100 flex-1">
          <h4 class="text-lg font-semibold text-gray-900 flex items-center pb-2 border-b border-gray-200">
            <i class="fas fa-briefcase text-indigo-500 mr-2"></i>
            Job Announcements
          </h4>

          <!-- Job Announcements List -->
          <div v-for="job in jobs" :key="job.id"
            class="mt-6 p-4 rounded-xl border border-gray-100 hover:shadow-md transition-shadow duration-200">
            <h5 class="text-lg font-semibold text-gray-900">{{ job.title }}</h5>
            <p class="text-gray-600 mt-2 leading-relaxed">{{ job.description }}</p>
            <p class="text-xs text-gray-400 mt-3">Posted {{ job.posted_at }}</p>
          </div>

          <!-- Empty State -->
          <div v-if="!jobs || jobs.length === 0" class="mt-6 text-center text-gray-400">
            <div class="w-16 h-16 mx-auto flex items-center justify-center rounded-full bg-gray-100 mb-4">
              <i class="fas fa-briefcase text-2xl text-gray-400"></i>
            </div>
            <h5 class="text-lg font-semibold text-gray-700">No Job Announcements</h5>
            <p class="text-sm text-gray-500 mt-2">Please check back later for new opportunities.</p>
          </div>
        </section>
      </div>
    </div>
  </AppLayout>
</template>
