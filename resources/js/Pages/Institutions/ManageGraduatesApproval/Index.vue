<script setup>
import { ref, computed, onMounted } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const page = usePage();
const graduates = ref([...page.props.graduates]);

// Stats for display
const totalGraduates = computed(() => graduates.value.length);
const pendingGraduates = computed(() => graduates.value.filter(g => g.status === 'pending').length);
const approvedGraduates = computed(() => graduates.value.filter(g => g.status === 'approved').length);
const disapprovedGraduates = computed(() => graduates.value.filter(g => g.status === 'disapproved').length);

const approveGraduate = (id) => {
  router.post(route('graduates.approve', { id }), {}, {
    preserveScroll: true,
    onSuccess: () => {
      graduates.value = graduates.value.map(g => {
        if (g.id === id) {
          return { ...g, status: 'approved' };
        }
        return g;
      });
    },
  });
};

const disapproveGraduate = (id) => {
  router.post(route('graduates.disapprove', { id }), {}, {
    preserveScroll: true,
    onSuccess: () => {
      graduates.value = graduates.value.map(g => {
        if (g.id === id) {
          return { ...g, status: 'disapproved' };
        }
        return g;
      });
    },
  });
};

// Pagination
const currentPage = ref(1);
const itemsPerPage = 30;
const totalPages = computed(() => Math.ceil(graduates.value.length / itemsPerPage));
const paginatedGraduates = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  return graduates.value.slice(startIndex, endIndex);
});

const goToPage = (page) => {
  currentPage.value = page;
};

// Reset pagination when component is mounted
onMounted(() => {
  currentPage.value = 1;
});
</script>

<template>
  <AppLayout title="Manage Graduates Approval">
    <template #header>
      <div class="flex items-center">
        <h2 class="font-semibold text-xl text-gray-800 flex items-center">
          <i class="fas fa-user-graduate text-blue-500 text-xl mr-2"></i> Manage Graduates Approval
        </h2>
      </div>
    </template>

    <Container class="py-6 space-y-6">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Total Graduates Card -->
        <div
          class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-user-graduate text-white text-lg"></i>
            </div>
            <h3 class="text-blue-700 text-sm font-medium mb-2">Total Graduates</h3>
            <p class="text-2xl font-bold text-blue-900">{{ totalGraduates }}</p>
          </div>
        </div>

        <!-- Pending Card -->
        <div
          class="bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-clock text-white text-lg"></i>
            </div>
            <h3 class="text-yellow-700 text-sm font-medium mb-2">Pending</h3>
            <p class="text-2xl font-bold text-yellow-900">{{ pendingGraduates }}</p>
          </div>
        </div>

        <!-- Approved Card -->
        <div
          class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-check-circle text-white text-lg"></i>
            </div>
            <h3 class="text-green-700 text-sm font-medium mb-2">Approved</h3>
            <p class="text-2xl font-bold text-green-900">{{ approvedGraduates }}</p>
          </div>
        </div>

        <!-- Disapproved Card -->
        <div
          class="bg-gradient-to-br from-red-100 to-red-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-times-circle text-white text-lg"></i>
            </div>
            <h3 class="text-red-700 text-sm font-medium mb-2">Disapproved</h3>
            <p class="text-2xl font-bold text-red-900">{{ disapprovedGraduates }}</p>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div
        class="bg-white rounded-2xl shadow-lg p-6 flex flex-wrap items-center justify-between gap-4 border border-gray-100">
        <div class="flex items-center space-x-4">
          <Link :href="route('graduates.manage')" :class="[route().current('graduates.manage') ? 'bg-blue-50 text-blue-600 border-blue-200' : 'text-gray-600 hover:bg-gray-50 border-gray-200',
            'px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border']">
          <i class="fas fa-clock mr-2"></i>
          <span>Pending</span>
          </Link>
          <Link :href="route('graduates.list', { status: 'approved' })" :class="[route().current('graduates.list') ? 'bg-blue-50 text-blue-600 border-blue-200' : 'text-gray-600 hover:bg-gray-50 border-gray-200',
            'px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border']">
          <i class="fas fa-check-circle mr-2"></i>
          <span>Approved</span>
          </Link>
          <Link :href="route('graduates.list', { status: 'disapproved' })" :class="[route().current('graduates.list') ? 'bg-blue-50 text-blue-600 border-blue-200' : 'text-gray-600 hover:bg-gray-50 border-gray-200',
            'px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border']">
          <i class="fas fa-times-circle mr-2"></i>
          <span>Disapproved</span>
          </Link>
        </div>
      </div>

      <!-- Table Card -->
      <div
        class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
              <i class="fas fa-table text-white"></i>
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-800">Graduate Management</h3>
              <span class="text-sm text-gray-500">{{ totalGraduates }} total graduates</span>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 text-sm font-semibold text-gray-700">
              <tr>
                <th class="px-6 py-4 text-left">Graduate</th>
                <th class="px-6 py-4 text-left">Email</th>
                <th class="px-6 py-4 text-left">Status</th>
                <th class="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
              <tr v-for="graduate in paginatedGraduates" :key="graduate.id"
                class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group">
                <td class="px-6 py-5 whitespace-nowrap">
                  <div class="flex items-center">
                    <div
                      class="w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center mr-3 group-hover:shadow-md transition-all duration-200">
                      <i class="fas fa-user-graduate text-white text-xs"></i>
                    </div>
                    <div class="ml-1">
                      <div class="text-sm font-semibold text-gray-900 group-hover:text-blue-700 transition-colors">{{ graduate.name }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-600">{{ graduate.email }}</td>
                <td class="px-6 py-5 whitespace-nowrap">
                  <span class="px-3 py-1.5 text-xs font-semibold rounded-full flex items-center w-fit shadow-sm"
                    :class="{
                      'bg-green-100 text-green-800': graduate.status === 'approved',
                      'bg-red-100 text-red-800': graduate.status === 'disapproved',
                      'bg-yellow-100 text-yellow-800': graduate.status === 'pending'
                    }">
                    <i :class="{
                      'fas fa-check-circle mr-1.5': graduate.status === 'approved',
                      'fas fa-times-circle mr-1.5': graduate.status === 'disapproved',
                      'fas fa-clock mr-1.5': graduate.status === 'pending'
                    }"></i>
                    {{ graduate.status === 'approved' ? 'Approved' : (graduate.status === 'disapproved' ?
                      'Disapproved'
                      : 'Pending') }}
                  </span>
                </td>
                <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <button v-if="graduate.status !== 'approved'" @click="approveGraduate(graduate.id)"
                      class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200 shadow-sm">
                      <i class="fas fa-check mr-1"></i>
                      Approve
                    </button>
                    <button v-if="graduate.status !== 'disapproved'" @click="disapproveGraduate(graduate.id)"
                      class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200 shadow-sm">
                      <i class="fas fa-times mr-1"></i>
                      Disapprove
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="graduates.length === 0">
                <td colspan="4" class="py-12 text-center">
                  <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                      <i class="fas fa-user-graduate text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-700 mb-2">No graduates found</h3>
                    <p class="text-gray-500">There are no graduates to display at this time</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          
          <!-- Pagination Controls -->
          <div v-if="totalPages > 1" class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-t border-gray-100 flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing <span class="font-medium">{{ ((currentPage - 1) * itemsPerPage) + 1 }}</span> to
              <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, graduates.length) }}</span> of
              <span class="font-medium">{{ graduates.length }}</span> results
            </div>
            <div class="flex space-x-2">
              <button 
                @click="goToPage(currentPage - 1)" 
                :disabled="currentPage === 1"
                class="px-3 py-1.5 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 shadow-sm"
              >
                <i class="fas fa-chevron-left mr-1"></i>
                Previous
              </button>
              <div class="flex space-x-1">
                <button 
                  v-for="page in totalPages" 
                  :key="page" 
                  @click="goToPage(page)"
                  :class="{
                    'bg-blue-600 text-white shadow-md': page === currentPage,
                    'bg-white text-gray-700 hover:bg-gray-50': page !== currentPage
                  }"
                  class="px-3 py-1.5 rounded-md border border-gray-300 text-sm font-medium transition-colors duration-200"
                >
                  {{ page }}
                </button>
              </div>
              <button 
                @click="goToPage(currentPage + 1)" 
                :disabled="currentPage === totalPages"
                class="px-3 py-1.5 rounded-md border border-gray-300 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 shadow-sm"
              >
                Next
                <i class="fas fa-chevron-right ml-1"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Container>
  </AppLayout>
</template>
