<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { router } from "@inertiajs/vue3";
import { defineProps, ref, computed } from "vue";

const props = defineProps({
  institution: Object,
});

const isEditing = ref(false);
const localDescription = ref(props.institution.description || '');

const canEdit = computed(() => true); // Adjust if you want to restrict editing

const saveDescription = () => {
  router.post(
    route('institution.profile.description.update'),
    { description: localDescription.value },
    {
      preserveScroll: true,
      onSuccess: () => {
        isEditing.value = false;
        // Optionally show a success message
      },
      onError: () => {
        // Optionally handle errors
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
          <!-- Logo -->
          <div class="relative">
            <img
              :src="institution.logo || '/images/default-logo.png'"
              alt="Institution Logo"
              class="w-24 h-24 rounded-full object-cover border-4 border-white"
            />
          </div>

          <!-- Institution Info -->
          <div class="flex-1">
            <h3 class="text-3xl font-bold text-gray-800">{{ institution.name }}</h3>
            <p class="text-gray-600 flex items-center space-x-2">
              <i class="fas fa-map-marker-alt text-indigo-500"></i>
              <span>{{ institution.address || 'Location not available' }}</span>
            </p>
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

          <!-- Action Buttons -->
          <div class="flex space-x-4">
            <a
              v-if="canEdit"
              :href="route('institution.information')"
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
        <!-- Overview Section -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white shadow-lg rounded-lg p-6">
            <h4 class="text-lg font-semibold text-gray-800">Date Joined</h4>
            <p class="text-gray-600">{{ institution.created_at || 'N/A' }}</p>
          </div>
          <div class="bg-white shadow-lg rounded-lg p-6">
            <h4 class="text-lg font-semibold text-gray-800">President</h4>
            <p class="text-gray-600">
              {{ institution.institution_president_first_name || '' }}
              {{ institution.institution_president_last_name || '' }}
            </p>
          </div>
        </div>

        <!-- Description and Contact Info in Grid -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="md:col-span-2 bg-white">
            <div class="flex items-center justify-between">
              <h4 class="text-xl font-semibold text-gray-800">Institution Description</h4>
              <button
                v-if="canEdit"
                @click="isEditing = true"
                class="w-5 h-5 text-gray-500 hover:text-blue-500 cursor-pointer"
                title="Edit Description"
              >
                <i class="fas fa-pencil-alt"></i>
              </button>
            </div>

            <div v-if="isEditing" class="mt-4">
              <textarea
                v-model="localDescription"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-blue-200"
                rows="4"
              ></textarea>
              <button @click="saveDescription" class="mt-2 bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700">
                Save
              </button>
              <button @click="cancelEditing" class="ml-2 mt-2 bg-red-600 text-white px-4 py-1 rounded hover:bg-blue-700">
                Cancel
              </button>
            </div>
            <p v-else class="text-gray-600 mt-4">{{ localDescription || 'No description available.' }}</p>
          </div>
          <!-- Contact Information -->
          <div class="bg-white shadow-lg rounded-lg p-6">
            <h4 class="text-xl font-semibold text-gray-800 border-b pb-2 mb-4">Contact Information</h4>
            <div class="mt-4 space-y-4">
              <div class="flex items-center space-x-4">
                <i class="fas fa-map-marker-alt text-indigo-500 text-lg"></i>
                <p class="text-gray-600">
                  <strong>Address:</strong> {{ institution.address || 'N/A' }}
                </p>
              </div>
              <div class="flex items-center space-x-4">
                <i class="fas fa-envelope text-indigo-500 text-lg"></i>
                <p class="text-gray-600">
                  <strong>Email Address:</strong> {{ institution.email || 'N/A' }}
                </p>
              </div>
              <div class="flex items-center space-x-4">
                <i class="fas fa-phone text-indigo-500 text-lg"></i>
                <p class="text-gray-600">
                  <strong>Contact Number:</strong> {{ institution.contact_number || 'N/A' }}
                </p>
              </div>
              <div class="flex items-center space-x-4">
                <i class="fas fa-phone text-indigo-500 text-lg"></i>
                <p class="text-gray-600">
                  <strong>Telephone Number:</strong> {{ institution.telephone_number || 'N/A' }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
