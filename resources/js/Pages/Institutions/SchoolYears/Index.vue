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
        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
          <i class="fas fa-calendar-alt text-white"></i>
        </div>
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">School Years</h2>
          <p class="text-sm text-gray-600">Manage school year entries and statuses</p>
        </div>
      </div>
    </template>

    <Container class="py-8">
      <!-- Navigation Menu -->
      <div class="mb-8 max-w-3xl mx-auto bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <!-- Section Header -->
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
          <div class="flex items-center">
            <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center mr-3">
              <i class="fas fa-database text-white text-sm"></i>
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-800">Data Entries</h3>
              <p class="text-xs text-gray-500">Quick links to manage institutional data</p>
            </div>
          </div>
        </div>

        <!-- Buttons -->
        <div class="flex flex-wrap gap-3 p-6 justify-center">
          <Link :href="route('degrees', { user: userId })"
            class="inline-flex items-center px-4 py-2 rounded-lg shadow-sm bg-white border border-gray-200 text-gray-700 hover:bg-green-50 hover:border-green-400 hover:text-green-600 transition-all duration-200">
            <i class="fas fa-graduation-cap mr-2 text-green-500"></i> Degrees
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
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div
          class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-calendar-alt text-white text-lg"></i>
            </div>
            <h3 class="text-blue-700 text-sm font-medium mb-2">Total School Years</h3>
            <p class="text-2xl font-bold text-blue-900">{{ school_years.length }}</p>
          </div>
        </div>

        <div
          class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-calendar text-white text-lg"></i>
            </div>
            <h3 class="text-green-700 text-sm font-medium mb-2">Years</h3>
            <p class="text-2xl font-bold text-green-900">{{ uniqueYears.size }}</p>
          </div>
        </div>

        <div
          class="bg-gradient-to-br from-purple-100 to-purple-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-list-ol text-white text-lg"></i>
            </div>
            <h3 class="text-purple-700 text-sm font-medium mb-2">Terms</h3>
            <p class="text-2xl font-bold text-purple-900">{{ uniqueTerms.size }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-wrap items-center justify-between gap-4 border border-gray-100 mb-8">
        <div class="flex items-center space-x-4">
          <button
            @click="$inertia.get(route('school-years.list', { user: page.props.auth.user.id }))"
            :class="[
              route().current('school-years.list')
                ? 'bg-blue-50 text-blue-600 border-blue-200'
                : 'text-gray-600 hover:bg-gray-50 border-gray-200',
              'px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border',
            ]"
          >
            <i class="fas fa-list mr-2"></i>
            <span>All School Years</span>
          </button>

          <button
            v-if="page.props.roles.isInstitution"
            @click="$inertia.get(route('school-years.archivedlist', { user: page.props.auth.user.id }))"
            :class="[
              route().current('school-years.archivedlist')
                ? 'bg-blue-50 text-blue-600 border-blue-200'
                : 'text-gray-600 hover:bg-gray-50 border-gray-200',
              'px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border',
            ]"
          >
            <i class="fas fa-archive mr-2"></i>
            <span>Archived School Years</span>
          </button>
        </div>
        <div class="flex items-center space-x-3">
          <button
            @click="$inertia.get(route('school-years.create', { user: page.props.auth.user.id }))"
            class="px-4 py-2 rounded-md bg-blue-500 text-white font-medium transition-colors flex items-center shadow-sm hover:bg-blue-600"
          >
            <i class="fas fa-plus mr-2"></i>
            Add School Year
          </button>
        </div>
      </div>

      <!-- Main Content -->
      <div
        class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <!-- Action Buttons -->
        <div class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
              <i class="fas fa-table text-white"></i>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-800">School Years</h3>
              <span class="text-sm text-gray-600">{{ school_years.length }} total</span>
            </div>
          </div>
        </div>

        <!-- School Years Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full table-auto">
            <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 text-sm font-semibold text-gray-700">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">School Year</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Term</th>
                <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="sy in school_years" :key="sy.id" class="hover:bg-gray-50 transition-colors duration-150">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div
                      class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
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
                  <Link :href="route('school-years.edit', { id: sy.id })"
                    class="text-blue-500 hover:text-blue-700 mr-3">
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
            <strong>"{{ selectedSchoolYear?.school_year?.school_year_range }}"</strong>? This action can be reversed
            later.
          </p>
        </template>

        <template #footer>
          <div class="flex justify-end space-x-3">
            <SecondaryButton @click="open = false"
              class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm transition-colors duration-200">
              Cancel
            </SecondaryButton>
            <DangerButton @click="archiveSchoolYear"
              class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 text-sm transition-colors duration-200">
              Archive
            </DangerButton>
          </div>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>
