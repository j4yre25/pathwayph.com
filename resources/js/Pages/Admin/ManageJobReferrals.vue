<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed, ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { watch } from 'vue';

const { props } = usePage();

const referrals = computed(() => props.referrals.data || []);
const totalReferrals = computed(() => props.totalReferrals || 0);
const successfulReferrals = computed(() => props.successfulReferrals || 0);
const successRate = computed(() => props.successRate || 0);

const filters = ref({
  status: props.filters?.status || '',
  company: props.filters?.company || '',
  candidate: props.filters?.candidate || '',
  search: props.filters?.search || '',
});

const pagination = computed(() => props.referrals || {});

// Filtering actions
function applyFilters() {
  router.get(route('peso.job-referrals.index'), { ...filters.value }, { preserveState: true, replace: true });
}

function resetFilters() {
  filters.value = {
    status: '',
    company: '',
    candidate: '',
    search: '',
  };
  applyFilters();
}

function onSearch() {
  applyFilters();
}

// Referral actions
function downloadCertificate(referralId) {
  window.open(route('referral.certificate.download', referralId), '_blank');
}
function viewCertificate(referralId) {
  window.open(route('referral.certificate.view', referralId), '_blank');
}

function generateCertificate(referralId) {
  fetch(route('peso.job-referrals.mark-success', referralId), {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json'
    }
  }).then(() => {
    window.open(route('peso.job-referrals.certificate', referralId), '_blank');
    router.get(route('peso.job-referrals.index'), { ...filters.value }, { preserveState: true, replace: true });
  });
}

function declineReferral(referralId) {
  fetch(route('peso.job-referrals.decline', referralId), {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json'
    }
  }).then(() => {
    router.get(route('peso.job-referrals.index'), { ...filters.value }, { preserveState: true, replace: true });
  });
}

watch(
  () => props.filters,
  (newFilters) => {
    filters.value = {
      status: newFilters?.status || '',
      company: newFilters?.company || '',
      candidate: newFilters?.candidate || '',
      search: newFilters?.search || '',
    };
  },
  { immediate: true }
);
</script>

<template>
  <AppLayout title="Manage Job Referrals">
    <template #header>
      <div class="flex items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
          <i class="fas fa-exchange-alt text-blue-500 mr-2"></i> Job Referrals Tracking
        </h2>
      </div>
    </template>

    <div class="py-8 max-w-7xl mx-auto">
      <!-- Stats Summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div
          class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-users text-white text-lg"></i>
            </div>
            <h3 class="text-blue-700 text-sm font-medium mb-2">Total Referrals</h3>
            <p class="text-2xl font-bold text-blue-900">{{ totalReferrals }}</p>
          </div>
        </div>
        <div
          class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-check-circle text-white text-lg"></i>
            </div>
            <h3 class="text-green-700 text-sm font-medium mb-2">Successful Referrals</h3>
            <p class="text-2xl font-bold text-green-900">{{ successfulReferrals }}</p>
          </div>
        </div>
        <div
          class="bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-chart-line text-white text-lg"></i>
            </div>
            <h3 class="text-yellow-700 text-sm font-medium mb-2">Success Rate</h3>
            <p class="text-2xl font-bold text-yellow-900">{{ successRate }}%</p>
          </div>
        </div>
      </div>

      <!-- Filter Section -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
        <div class="p-4 border-b border-gray-200 flex items-center">
          <i class="fas fa-filter text-blue-500 mr-2"></i>
          <h3 class="font-medium text-gray-700">Filter Referrals</h3>
          <div class="ml-auto">
            <button type="button" @click="resetFilters"
              class="px-6 py-3 bg-white text-gray-700 rounded-xl text-sm font-medium flex items-center hover:bg-gray-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 shadow-sm border border-gray-300">
              <i class="fas fa-undo mr-2"></i> Reset Filter
            </button>
          </div>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select v-model="filters.status"
                class="block w-full border border-gray-300 rounded-xl shadow-sm px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all appearance-none"
                @change="applyFilters">
                <option value="">All Status</option>
                <option value="success">Success</option>
                <option value="pending">Pending</option>
                <option value="rejected">Declined</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
              <input v-model="filters.company" type="text" placeholder="Company name"
                class="block w-full border border-gray-300 rounded-xl shadow-sm px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
                @keyup.enter="onSearch" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Candidate</label>
              <input v-model="filters.candidate" type="text" placeholder="Candidate name"
                class="block w-full border border-gray-300 rounded-xl shadow-sm px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
                @keyup.enter="onSearch" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                  <i class="fas fa-search text-gray-400"></i>
                </div>
                <input v-model="filters.search" @keyup.enter="onSearch" type="text" placeholder="Search..."
                  class="block w-full pl-10 border border-gray-300 rounded-xl shadow-sm px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Referrals Table Section -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div
          class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
              <i class="fas fa-exchange-alt text-white text-sm"></i>
            </div>
            <div>
              <h3 class="font-semibold text-gray-800">My Referrals</h3>
              <p class="text-sm text-gray-500 mt-1">Monitor and manage job referrals across the platform.</p>
            </div>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-blue-50 text-xs uppercase tracking-wider text-gray-600 font-medium">
              <tr>
                <th class="px-6 py-4 font-semibold">Candidate</th>
                <th class="px-6 py-4 font-semibold">Job Title</th>
                <th class="px-6 py-4 font-semibold">Company</th>
                <th class="px-6 py-4 font-semibold">Status</th>
                <th class="px-6 py-4 font-semibold">Referred At</th>
                <th class="px-6 py-4 font-semibold">Match Score</th>
                <th class="px-6 py-4 font-semibold">Match Details</th>
                <th class="py-3 px-4 text-left">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="ref in referrals" :key="ref.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 font-medium">{{ ref.candidate }}</td>
                <td class="px-6 py-4">{{ ref.job_title }}</td>
                <td class="px-6 py-4">{{ ref.company }}</td>
                <td class="px-6 py-4">
                  <span class="px-2 py-1 text-xs font-medium rounded-full" :class="{
                    'bg-green-100 text-green-800': ref.status === 'success',
                    'bg-yellow-100 text-yellow-800': ref.status === 'pending',
                    'bg-red-100 text-red-800': ref.status === 'rejected'
                  }">
                    {{ ref.status.charAt(0).toUpperCase() + ref.status.slice(1) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-gray-600">{{ ref.referred_at }}</td>
                <td class="px-6 py-4">
                  <span class="px-2 py-1 text-xs font-medium rounded-full" :class="{
                    'bg-green-100 text-green-800': ref.match_score >= 60,
                    'bg-yellow-100 text-yellow-800': ref.match_score >= 30 && ref.match_score < 60,
                    'bg-red-100 text-red-800': ref.match_score < 30
                  }">
                    {{ ref.match_score }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex flex-wrap gap-1">
                    <template v-if="ref.match_details && ref.match_details.length">
                      <span v-for="detail in ref.match_details" :key="detail"
                        class="bg-gray-100 rounded px-2 py-1 text-xs text-gray-700">
                        {{ detail }}
                      </span>
                    </template>
                    <template v-else>
                      <span class="text-gray-400 italic">No matched details</span>
                    </template>
                  </div>
                </td>
                <td class="py-3 px-4 border-b">
                  <div class="flex flex-col gap-2 items-start">
                    <button v-if="ref.status === 'pending'" @click="generateCertificate(ref.id)"
                      class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded font-semibold text-xs shadow transition w-full">
                      Accept and Generate
                    </button>
                    <button v-if="ref.status === 'pending'" @click="declineReferral(ref.id)"
                      class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded font-semibold text-xs shadow transition w-full">
                      Decline
                    </button>
                    <button v-if="ref.certificate_path" @click="viewCertificate(ref.id)"
                      class="text-blue-600 underline">
                      Download and View Certificate
                    </button>
                    <span v-else class="text-gray-400 italic">Not available</span>
                  </div>
                </td>
              </tr>
              <tr v-if="referrals.length === 0">
                <td colspan="9" class="px-6 py-8 text-center">
                  <div class="flex flex-col items-center justify-center text-gray-500">
                    <svg class="w-12 h-12 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-lg font-medium">No referrals found</p>
                    <p class="text-sm">Try adjusting your search or filter criteria</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination && pagination.links && pagination.links.length > 3" class="mt-6 flex justify-center">
        <nav aria-label="Page navigation">
          <ul class="inline-flex items-center -space-x-px rounded-md shadow-sm">
            <li v-for="(link, i) in pagination.links" :key="i">
              <button :disabled="!link.url"
                @click="link.url && router.get(link.url, {}, { preserveState: true, replace: true })" :class="[
                  'relative inline-flex items-center px-4 py-2 text-sm font-medium border',
                  link.active
                    ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                    : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                  link.label.includes('Previous') ? 'rounded-l-md' : '',
                  link.label.includes('Next') ? 'rounded-r-md' : '',
                  !link.url ? 'opacity-50 cursor-not-allowed' : ''
                ]">
                <span v-html="link.label"></span>
              </button>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </AppLayout>
</template>