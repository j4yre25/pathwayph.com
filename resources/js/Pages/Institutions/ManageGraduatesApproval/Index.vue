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
        <i class="fas fa-user-graduate text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Graduates Approval</h2>
      </div>
    </template>

    <Container class="py-8">
      <!-- Stats Summary -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg p-6 shadow-sm border-l-4 border-blue-500 transition-all duration-200 hover:shadow-md relative overflow-hidden">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">Total Graduates</h3>
              <p class="text-3xl font-bold text-blue-600">
                {{ totalGraduates }}
              </p>
            </div>
            <div class="bg-blue-100 rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-users text-blue-500"></i>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg p-6 shadow-sm border-l-4 border-yellow-500 transition-all duration-200 hover:shadow-md relative overflow-hidden">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">Pending</h3>
              <p class="text-3xl font-bold text-yellow-600">
                {{ pendingGraduates }}
              </p>
            </div>
            <div class="bg-yellow-100 rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-clock text-yellow-500"></i>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg p-6 shadow-sm border-l-4 border-green-500 transition-all duration-200 hover:shadow-md relative overflow-hidden">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">Approved</h3>
              <p class="text-3xl font-bold text-green-600">
                {{ approvedGraduates }}
              </p>
            </div>
            <div class="bg-green-100 rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-check-circle text-green-500"></i>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg p-6 shadow-sm border-l-4 border-red-500 transition-all duration-200 hover:shadow-md relative overflow-hidden">
          <div class="flex justify-between items-start">
            <div>
              <h3 class="text-gray-600 text-sm font-medium mb-2">Disapproved</h3>
              <p class="text-3xl font-bold text-red-600">
                {{ disapprovedGraduates }}
              </p>
            </div>
            <div class="bg-red-100 rounded-full p-3 flex items-center justify-center">
              <i class="fas fa-times-circle text-red-500"></i>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Main Content -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
        <!-- Action Buttons -->
        <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-200">
          <div class="flex items-center">
            <h3 class="text-lg font-semibold text-gray-800">Graduates List</h3>
            <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ totalGraduates }} total</span>
          </div>
          <div class="flex w-full sm:w-auto space-x-3 mt-3 sm:mt-0">
            <Link :href="route('graduates.manage')" 
                  class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
              <i class="fas fa-clock text-yellow-500 mr-2"></i> Pending
            </Link>
            <Link :href="route('graduates.list', { status: 'approved' })" 
                  class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
              <i class="fas fa-check-circle text-green-500 mr-2"></i> Approved
            </Link>
            <Link :href="route('graduates.list', { status: 'disapproved' })" 
                  class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
              <i class="fas fa-times-circle text-red-500 mr-2"></i> Disapproved
            </Link>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-500">
              <tr>
                <th class="px-6 py-4">Name</th>
                <th class="px-6 py-4">Email</th>
                <th class="px-6 py-4">Status</th>
                <th class="px-6 py-4 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr
                v-for="graduate in paginatedGraduates"
                :key="graduate.id"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
                      <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ graduate.name }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <i class="fas fa-envelope text-gray-400 mr-2"></i>
                    <span class="text-sm text-gray-600">{{ graduate.email }}</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span
                    :class="{
                      'bg-yellow-100 text-yellow-800': graduate.status === 'pending',
                      'bg-green-100 text-green-800': graduate.status === 'approved',
                      'bg-red-100 text-red-800': graduate.status === 'disapproved',
                    }"
                    class="px-2 py-1 rounded-full text-xs font-medium flex items-center w-fit"
                  >
                    <i :class="{
                      'fas fa-clock': graduate.status === 'pending',
                      'fas fa-check-circle': graduate.status === 'approved',
                      'fas fa-times-circle': graduate.status === 'disapproved',
                    }" class="mr-1"></i>
                    {{ graduate.status }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right">
                  <button
                    v-if="graduate.status == 'pending'"
                    @click="approveGraduate(graduate.id)"
                    class="text-green-500 hover:text-green-700 mr-3"
                  >
                    <i class="fas fa-check"></i>
                  </button>
                  <button
                    v-if="graduate.status == 'pending'"
                    @click="disapproveGraduate(graduate.id)"
                    class="text-red-500 hover:text-red-700"
                  >
                    <i class="fas fa-times"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="graduates.length === 0">
                <td colspan="4" class="py-12 text-center">
                  <i class="fas fa-user-graduate text-gray-300 text-5xl mb-4"></i>
                  <h3 class="text-lg font-medium text-gray-700">No graduates found</h3>
                  <p class="text-gray-500 mt-1">There are no graduates to display at this time</p>
                </td>
              </tr>
            </tbody>
          </table>
          
          <!-- Pagination Controls -->
          <div v-if="totalPages > 1" class="px-6 py-4 bg-white border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing <span class="font-medium">{{ ((currentPage - 1) * itemsPerPage) + 1 }}</span> to
              <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, graduates.length) }}</span> of
              <span class="font-medium">{{ graduates.length }}</span> results
            </div>
            <div class="flex space-x-2">
              <button 
                @click="goToPage(currentPage - 1)" 
                :disabled="currentPage === 1"
                class="px-3 py-1 rounded border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Previous
              </button>
              <div class="flex space-x-1">
                <button 
                  v-for="page in totalPages" 
                  :key="page" 
                  @click="goToPage(page)"
                  :class="{
                    'bg-blue-600 text-white': page === currentPage,
                    'bg-white text-gray-700 hover:bg-gray-50': page !== currentPage
                  }"
                  class="px-3 py-1 rounded border border-gray-300 text-sm font-medium"
                >
                  {{ page }}
                </button>
              </div>
              <button 
                @click="goToPage(currentPage + 1)" 
                :disabled="currentPage === totalPages"
                class="px-3 py-1 rounded border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </div>
    </Container>
  </AppLayout>
</template>
