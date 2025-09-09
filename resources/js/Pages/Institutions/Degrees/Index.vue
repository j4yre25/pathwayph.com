<script setup>
import { ref } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const page = usePage();
const userId = page.props.auth?.user?.id
const degrees = ref([...page.props.degrees]); // Use a local ref array

const selectedDegree = ref(null);
const open = ref(false);

const archiveDegree = () => {
  if (selectedDegree.value) {
    router.delete(route('degrees.delete', { id: selectedDegree.value.id }), {
      preserveScroll: true,
      onSuccess: () => {
        degrees.value = degrees.value.filter(d => d.id !== selectedDegree.value.id);
        open.value = false;
        selectedDegree.value = null;
      },
    });
  }
};

const confirmArchive = (degree) => {
  selectedDegree.value = degree;
  open.value = true;
};

// Get unique degree types for stats
const uniqueDegreeTypes = new Set(degrees.value.map(d => d.degree?.type));
</script>

<template>
  <AppLayout title="Degrees">
    <template #header>
      <div class="flex items-center">
        <i class="fas fa-graduation-cap text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Degrees</h2>
      </div>
    </template>

    <Container class="py-8">

      <!-- Navigation Menu -->
      <div class="mb-8 max-w-3xl mx-auto bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
        <!-- Section Header -->
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-800 text-center">Data Entries</h3>
        </div>

        <!-- Buttons -->
        <div class="flex flex-wrap gap-3 p-4 justify-center">
          <Link :href="route('school-years', { user: userId })"
            class="inline-flex items-center px-4 py-2 rounded-lg shadow-sm bg-white border border-gray-200 text-gray-700 hover:bg-green-50 hover:border-green-400 hover:text-green-600 transition-all duration-200">
          <i class="fas fa-calendar-alt mr-2 text-blue-500"></i> School Years
          </Link>

          <Link :href="route('programs', { user: userId })"
            class="inline-flex items-center px-4 py-2 rounded-lg shadow-sm bg-white border border-gray-200 text-gray-700 hover:bg-indigo-50 hover:border-indigo-400 hover:text-indigo-600 transition-all duration-200">
          <i class="fas fa-book mr-2 text-indigo-500"></i> Programs
          </Link>

          <Link :href="route('careeropportunities', { user: userId })"
            class="inline-flex items-center px-4 py-2 rounded-lg shadow-sm bg-white border border-gray-200 text-gray-700 hover:bg-purple-50 hover:border-purple-400 hover:text-purple-600 transition-all duration-200">
          <i class="fas fa-briefcase mr-2 text-purple-500"></i> Career Opportunities
          </Link>

          <Link :href="route('instiskills', { user: userId })"
            class="inline-flex items-center px-4 py-2 rounded-lg shadow-sm bg-white border border-gray-200 text-gray-700 hover:bg-pink-50 hover:border-pink-400 hover:text-pink-600 transition-all duration-200">
          <i class="fas fa-cogs mr-2 text-pink-500"></i> Skills
          </Link>
        </div>
      </div>

      <!-- Stats Summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
          <h3 class="text-gray-600 text-sm font-medium mb-2">Total Degrees</h3>
          <p class="text-3xl font-bold text-blue-600">
            {{ degrees.length }}
          </p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
          <h3 class="text-gray-600 text-sm font-medium mb-2">Degree Types</h3>
          <p class="text-3xl font-bold text-green-600">
            {{ uniqueDegreeTypes.size }}
          </p>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
          <h3 class="text-gray-600 text-sm font-medium mb-2">Active Degrees</h3>
          <p class="text-3xl font-bold text-indigo-600">
            {{ degrees.length }}
          </p>
        </div>
      </div>
      
      <!-- Main Content -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
        <!-- Action Buttons -->
        <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-200">
          <div class="flex items-center">
            <h3 class="text-lg font-semibold text-gray-800">Degrees List</h3>
            <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ degrees.length }} total</span>
          </div>
          <div class="flex w-full sm:w-auto space-x-3 mt-3 sm:mt-0">
            <Link :href="route('degrees.create', { user: page.props.auth.user.id })" 
                  class="text-sm px-4 py-2 rounded-md bg-blue-500 text-white hover:bg-blue-600 transition-colors duration-200 flex items-center">
              <i class="fas fa-plus-circle mr-2"></i> Add Degree
            </Link>
            <Link :href="route('degrees.list', { user: page.props.auth.user.id })" 
                  class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
              <i class="fas fa-list text-blue-500 mr-2"></i> All Degrees
            </Link>
            <Link :href="route('degrees.archivedlist', { user: page.props.auth.user.id })" 
                  class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
              <i class="fas fa-archive text-gray-500 mr-2"></i> Archived
            </Link>
          </div>
        </div>

        <!-- Degrees Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full table-auto">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500 tracking-wider">
              <tr>
                <th class="px-6 py-3 text-left">Degree</th>
                <th class="px-6 py-3 text-left">Degree Code</th>
                <th class="px-6 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="degree in degrees" :key="degree.id" class="hover:bg-gray-50 transition-colors duration-150">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
                      <i class="fas fa-scroll"></i>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ degree.degree?.type }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                  <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-800">
                    {{ degree.degree_code }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link :href="route('degrees.edit', { id: degree.id })" class="text-blue-500 hover:text-blue-700 mr-3">
                    <i class="fas fa-edit"></i>
                  </Link>
                  <button @click="confirmArchive(degree)" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-archive"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Empty State -->
        <div v-if="degrees.length === 0" class="py-12 text-center">
          <i class="fas fa-graduation-cap text-gray-300 text-5xl mb-4"></i>
          <h3 class="text-lg font-medium text-gray-700">No degrees found</h3>
          <p class="text-gray-500 mt-1">Add degrees to see them listed here</p>
        </div>
      </div>

      <!-- Confirmation Modal -->
      <ConfirmationModal :show="open" @close="open = false">
        <template #title>
          <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mr-4">
              <i class="fas fa-exclamation-triangle text-red-500"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-800">Confirm Archive</h3>
          </div>
        </template>
        <template #content>
          <p class="text-gray-600">Are you sure you want to archive the degree
          <strong>"{{ selectedDegree?.degree?.type }}"</strong>? This action can be reversed later.</p>
        </template>
        <template #footer>
          <div class="flex justify-end space-x-3">
            <SecondaryButton @click="open = false" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm transition-colors duration-200">
              Cancel
            </SecondaryButton>
            <DangerButton @click="archiveDegree" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 text-sm transition-colors duration-200">
              Archive
            </DangerButton>
          </div>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>
