<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { router } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref, computed } from 'vue';

// Add FontAwesome CSS
const faCSS = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';

const props = defineProps({
    all_degrees: Array,
});

const showModal = ref(false);
const degreeToRestore = ref(null);
const searchQuery = ref('');

// Computed property for filtering degrees by search query
const filteredDegrees = computed(() => {
  if (!searchQuery.value.trim()) return props.all_degrees;
  
  const query = searchQuery.value.toLowerCase().trim();
  return props.all_degrees.filter(degree => 
    (degree.degree?.type && degree.degree.type.toLowerCase().includes(query)) ||
    (degree.degree_code && degree.degree_code.toLowerCase().includes(query))
  );
});

// Stats for cards
const stats = computed(() => {
  return {
    totalArchived: props.all_degrees.length
  };
});

const restoreDegree = () => {
  router.post(
    route('degrees.restore', { id: degreeToRestore.value.id }),
    {},
    {
      onSuccess: () => {
        showModal.value = false;
      },
    }
  );
};

const confirmRestore = (degree) => {
  degreeToRestore.value = degree;
  showModal.value = true;
};

// Function to go back
const goBack = () => {
  router.visit(route('degrees.index'));
};
</script>

<template>
  <AppLayout title="Archived Degrees">
    <template #header>
      <div class="flex items-center">
        <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
          <i class="fas fa-chevron-left"></i>
        </button>
        <h2 class="font-semibold text-xl text-gray-800 flex items-center">
          <i class="fas fa-archive text-orange-500 text-xl mr-2"></i>
          Archived Degrees
        </h2>
      </div>
    </template>

    <link rel="stylesheet" :href="faCSS" />

    <Container class="py-8 space-y-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
        <div class="bg-gradient-to-br from-orange-100 to-orange-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-archive text-white text-lg"></i>
            </div>
            <h3 class="text-orange-700 text-sm font-medium mb-2">Total Archived</h3>
            <p class="text-2xl font-bold text-orange-900">{{ stats.totalArchived }}</p>
          </div>
        </div>
      </div>

      <!-- Search -->
     <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
          <i class="fas fa-filter text-blue-500 mr-2"></i>
          Search Archived Degrees
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
          <!-- Search Filter -->
          <div class="flex flex-col">
            <label for="search" class="text-sm font-medium text-gray-700 mb-1">Search</label>
            <div class="relative">
              <input
                v-model="searchQuery"
                type="text"
                id="search"
                placeholder="Search by degree type or code..."
                class="w-full pl-3 pr-10 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
              />
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-500">
                <i class="fas fa-search"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
              <i class="fas fa-table text-white"></i>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-800">Archived Degrees</h3>
              <p class="text-sm text-gray-600">Manage archived degrees</p>
              <span class="text-sm font-semibold text-gray-700">{{ filteredDegrees.length }} total</span>
            </div>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full table-auto">
            <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 text-sm font-semibold text-gray-700">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Degree</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Degree Code</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Status</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
              <tr
                v-for="degree in filteredDegrees"
                :key="degree.id"
                class="hover:bg-gray-50 transition"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-8 w-8 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center mr-3">
                      <i class="fas fa-graduation-cap"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-800">{{ degree.degree?.type }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-8 w-8 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mr-3">
                      <i class="fas fa-hashtag"></i>
                    </div>
                    <span class="text-sm text-gray-700">{{ degree.degree_code }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                    <i class="fas fa-archive mr-1"></i>
                    Archived
                  </span>
                </td>
                <td class="px-6 py-4">
                  <DangerButton @click="confirmRestore(degree)" class="flex items-center">
                    <i class="fas fa-undo mr-2"></i>
                    Restore
                  </DangerButton>
                </td>
              </tr>
              <tr v-if="filteredDegrees.length === 0">
                <td colspan="4" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center">
                    <i class="fas fa-archive text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-700">No archived degrees found</h3>
                    <p class="text-gray-500 mt-1">Try adjusting search criteria</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Confirmation Modal -->
      <ConfirmationModal :show="showModal" @close="showModal = false">
        <template #title>
          <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center mr-4">
              <i class="fas fa-question-circle text-orange-500"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800">Confirm Restore</h3>
          </div>
        </template>
        <template #content>
          <p class="text-gray-600">Are you sure you want to restore this degree?</p>
          <p class="mt-2 text-sm text-gray-600">This will make the degree active again.</p>
        </template>
        <template #footer>
          <PrimaryButton @click="showModal = false" class="mr-2 flex items-center">
            <i class="fas fa-times mr-2"></i>
            Cancel
          </PrimaryButton>
          <DangerButton @click="restoreDegree" class="flex items-center">
            <i class="fas fa-undo mr-2"></i>
            Restore
          </DangerButton>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>
