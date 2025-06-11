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
        <h2 class="text-xl font-semibold text-gray-800 flex items-center">
          <i class="fas fa-archive mr-2"></i> Archived Degrees
        </h2>
      </div>
    </template>

    <link rel="stylesheet" :href="faCSS" />

    <Container class="py-6 space-y-6">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-orange-500">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-orange-100 mr-4">
              <i class="fas fa-archive text-orange-500 text-xl"></i>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-600">Total Archived</p>
              <p class="text-2xl font-semibold text-gray-800">{{ stats.totalArchived }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Search -->
      <div class="bg-white p-4 rounded-lg shadow-sm">
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
          </div>
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Search archived degrees..."
            class="pl-10 border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-indigo-400 focus:outline-none w-full"
          />
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-100 uppercase tracking-wider text-xs text-gray-600">
              <tr>
                <th class="px-6 py-4"><i class="fas fa-graduation-cap mr-2"></i> Degree</th>
                <th class="px-6 py-4"><i class="fas fa-hashtag mr-2"></i> Degree Code</th>
                <th class="px-6 py-4"><i class="fas fa-info-circle mr-2"></i> Status</th>
                <th class="px-6 py-4"><i class="fas fa-cogs mr-2"></i> Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="degree in filteredDegrees"
                :key="degree.id"
                class="border-t hover:bg-gray-50 transition"
              >
                <td class="px-6 py-4 font-medium">{{ degree.degree?.type }}</td>
                <td class="px-6 py-4">{{ degree.degree_code }}</td>
                <td class="px-6 py-4">
                  <span class="px-2 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800">
                    <i class="fas fa-archive mr-1"></i> Archived
                  </span>
                </td>
                <td class="px-6 py-4">
                  <DangerButton @click="confirmRestore(degree)" class="flex items-center">
                    <i class="fas fa-undo mr-2"></i> Restore
                  </DangerButton>
                </td>
              </tr>
              <tr v-if="filteredDegrees.length === 0">
                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                  <div class="flex flex-col items-center">
                    <i class="fas fa-archive text-gray-400 text-3xl mb-2"></i>
                    <p>No archived degrees found</p>
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
            <i class="fas fa-question-circle text-orange-500 mr-2"></i> Confirm Restore
          </div>
        </template>
        <template #content>
          <p>Are you sure you want to restore this degree?</p>
          <p class="mt-2 text-sm text-gray-600">This will make the degree active again.</p>
        </template>
        <template #footer>
          <PrimaryButton @click="showModal = false" class="mr-2 flex items-center">
            <i class="fas fa-times mr-2"></i> Cancel
          </PrimaryButton>
          <DangerButton @click="restoreDegree" class="flex items-center">
            <i class="fas fa-undo mr-2"></i> Restore
          </DangerButton>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>
