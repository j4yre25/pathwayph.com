<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { router, usePage } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';

const props = defineProps({
  opportunities: Array,
});

const page = usePage();
const userId = page.props.auth.user.id;

const showModal = ref(false);
const selected = ref(null);

const goBack = () => {
  window.history.back();
};

const restore = () => {
  router.post(route('careeropportunities.restore', { id: selected.value.id }), {}, {
    onSuccess: () => {
      showModal.value = false;
    },
  });
};

const confirmRestore = (item) => {
  selected.value = item;
  showModal.value = true;
};
</script>

<template>
  <AppLayout title="Archived Career Opportunities">
    <Container class="py-6 space-y-6">
      <!-- Header Section -->
      <div class="flex items-center mt-6 mb-4">
        <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
          <i class="fas fa-chevron-left"></i>
        </button>
        <div class="flex items-center">
          <i class="fas fa-archive text-orange-500 text-xl mr-2"></i>
          <h1 class="text-2xl font-bold text-gray-800">Archived Career Opportunities</h1>
        </div>
      </div>
      <p class="text-sm text-gray-500 mb-6">View and manage all archived career opportunities in the system.</p>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
        <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-orange-500">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-orange-100 mr-4">
              <i class="fas fa-archive text-orange-500 text-xl"></i>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Total Archived</p>
              <p class="text-2xl font-semibold text-gray-800">{{ opportunities.length }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Table Section -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-50 text-xs uppercase text-gray-600 tracking-wider">
              <tr>
                <th class="px-6 py-4">Program</th>
                <th class="px-6 py-4">Career Titles</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="item in opportunities" :key="item.id" class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">{{ item.program?.name }}</td>
                <td class="px-6 py-4">{{ item.career_opportunity?.title }}</td>
                <td class="px-6 py-4">
                  <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Archived</span>
                </td>
                <td class="px-6 py-4">
                  <button 
                    @click="confirmRestore(item)" 
                    class="inline-flex items-center px-3 py-1.5 bg-orange-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-orange-700 active:bg-orange-800 focus:outline-none focus:ring focus:ring-orange-300 transition"
                  >
                    <i class="fas fa-undo-alt mr-1"></i> Restore
                  </button>
                </td>
              </tr>
              <tr v-if="opportunities.length === 0">
                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No archived career opportunities found</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <ConfirmationModal :show="showModal" @close="showModal = false">
        <template #title>Confirm Restore</template>
        <template #content>Are you sure you want to restore this Career Opportunity?</template>
        <template #footer>
          <PrimaryButton @click="showModal = false" class="mr-2">Cancel</PrimaryButton>
          <DangerButton @click="restore">Restore</DangerButton>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>
