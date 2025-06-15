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
const school_years = ref([...page.props.school_years]); // Use a local ref array

const selectedSchoolYear = ref(null);
const open = ref(false);

const archiveSchoolYear = () => {
  if (selectedSchoolYear.value) {
    router.delete(route('school-years.delete', { id: selectedSchoolYear.value.id }), {
      preserveScroll: true,
      onSuccess: () => {
        // Remove the archived school year from the local array
        school_years.value = school_years.value.filter(sy => sy.id !== selectedSchoolYear.value.id);
        open.value = false;
        selectedSchoolYear.value = null;
      },
    });
  }
};

const confirmArchive = (schoolYear) => {
  selectedSchoolYear.value = schoolYear;
  open.value = true;
};

// Group school years by year for stats
const uniqueYears = new Set(school_years.value.map(sy => sy.school_year?.school_year_range));
const uniqueTerms = new Set(school_years.value.map(sy => sy.term));
</script>

<template>
  <AppLayout title="School Years">
    <template #header>
      <div class="flex items-center">
        <i class="fas fa-calendar-alt text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">School Years</h2>
      </div>
    </template>

    <Container class="py-8">
      <!-- Stats Summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500 relative overflow-hidden">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">Total School Years</h3>
              <p class="text-3xl font-bold text-grey-800">{{ school_years.length }}</p>
            </div>
            <div class="bg-blue-100 rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-calendar-alt text-blue-500 text-lg"></i>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500 relative overflow-hidden">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">Years</h3>
              <p class="text-3xl font-bold text-gray-800">{{ uniqueYears.size }}</p>
            </div>
            <div class="bg-green-100 rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-calendar-alt text-green-500 text-lg"></i>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500 relative overflow-hidden">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">Terms</h3>
              <p class="text-3xl font-bold text-indigo-600">{{ uniqueTerms.size }}</p>
            </div>
            <div class="bg-purple-100 rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-calendar-alt text-purple-500 text-lg"></i>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Main Content -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
        <!-- Action Buttons -->
        <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-200">
          <div class="flex items-center">
            <h3 class="text-lg font-semibold text-gray-800">School Years List</h3>
            <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ school_years.length }} total</span>
          </div>
          <div class="flex w-full sm:w-auto space-x-3 mt-3 sm:mt-0">
            <Link :href="route('school-years.create', { user: page.props.auth.user.id })" 
                  class="text-sm px-4 py-2 rounded-md bg-blue-500 text-white hover:bg-blue-600 transition-colors duration-200 flex items-center">
              <i class="fas fa-plus-circle mr-2"></i> Add School Year
            </Link>
            <Link :href="route('school-years.list', { user: page.props.auth.user.id })" 
                  class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
              <i class="fas fa-list text-blue-500 mr-2"></i> All School Years
            </Link>
            <Link v-if="page.props.roles.isInstitution" 
                  :href="route('school-years.archivedlist', { user: page.props.auth.user.id })" 
                  class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
              <i class="fas fa-archive text-gray-500 mr-2"></i> Archived
            </Link>
          </div>
        </div>

        <!-- School Years Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full table-auto">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500 tracking-wider">
              <tr>
                <th class="px-6 py-3 text-left">School Year</th>
                <th class="px-6 py-3 text-left">Term</th>
                <th class="px-6 py-3 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="sy in school_years" :key="sy.id" class="hover:bg-gray-50 transition-colors duration-150">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
                      <i class="fas fa-calendar"></i>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ sy.school_year?.school_year_range }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                  <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-800">
                    {{ sy.term }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link :href="route('school-years.edit', { id: sy.id })" class="text-blue-500 hover:text-blue-700 mr-3">
                    <i class="fas fa-edit"></i>
                  </Link>
                  <button @click="confirmArchive(sy)" class="text-red-500 hover:text-red-700">
                    <i class="fas fa-archive"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Empty State -->
        <div v-if="school_years.length === 0" class="py-12 text-center">
          <i class="fas fa-calendar-alt text-gray-300 text-5xl mb-4"></i>
          <h3 class="text-lg font-medium text-gray-700">No school years found</h3>
          <p class="text-gray-500 mt-1">Add school years to see them listed here</p>
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
          <p class="text-gray-600">Are you sure you want to archive the school year
          <strong>"{{ selectedSchoolYear?.school_year?.school_year_range }}"</strong>? This action can be reversed later.</p>
        </template>

        <template #footer>
          <div class="flex justify-end space-x-3">
            <SecondaryButton @click="open = false" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm transition-colors duration-200">
              Cancel
            </SecondaryButton>
            <DangerButton @click="archiveSchoolYear" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 text-sm transition-colors duration-200">
              Archive
            </DangerButton>
          </div>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>
