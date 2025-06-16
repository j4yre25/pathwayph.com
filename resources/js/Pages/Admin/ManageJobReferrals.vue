<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed, ref, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const { props } = usePage();

const referrals = computed(() => props.referrals.data || []);
const totalReferrals = computed(() => props.totalReferrals || 0);
const successfulReferrals = computed(() => props.successfulReferrals || 0);
const successRate = computed(() => props.successRate || 0);

const filters = ref({
  status: '',
  company: '',
  candidate: '',
  search: props.search || '',
});

const pagination = computed(() => props.referrals || {});

function applyFilters() {
  router.get(route('peso.job-referrals.index'), { ...filters.value, page: 1 }, { preserveState: true, replace: true });
}

function onSearch() {
  filters.value.page = 1;

  applyFilters();
}

function onPageChange(page) {
  router.get(route('peso.job-referrals.index'), { ...filters.value, page }, { preserveState: true, replace: true });
}

function goToPage(link) {
  if (!link.url) return;
  // Extract the page number from the URL (?page=2)
  const url = new URL(link.url, window.location.origin);
  const page = url.searchParams.get('page') || 1;
  onPageChange(page);
}

// watch(filters, applyFilters, { deep: true });

</script>

<template>
  <AppLayout title="Manage Job Referrals">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <!-- Header Section with Back Button -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
          <a href="javascript:history.back()" class="mr-4 text-gray-500 hover:text-gray-700 transition-colors">
            <i class="fas fa-chevron-left"></i>
          </a>
          <i class="fas fa-exchange-alt text-blue-500 text-xl mr-2"></i>
          <h1 class="text-2xl font-bold text-gray-800">Job Referrals Tracking</h1>
        </div>
      </div>
      <p class="text-sm text-gray-500 mb-6">Monitor and manage job referrals across the platform.</p>

      <!-- Stats Cards -->
      <div class="mb-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500">Total Referrals</p>
              <p class="text-3xl font-bold text-gray-800 mt-1">{{ totalReferrals }}</p>
            </div>
            <div class="rounded-full bg-blue-100 p-3">
              <i class="fas fa-users text-blue-500 text-xl"></i>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500">Successful Referrals</p>
              <p class="text-3xl font-bold text-gray-800 mt-1">{{ successfulReferrals }}</p>
            </div>
            <div class="rounded-full bg-green-100 p-3">
              <i class="fas fa-check-circle text-green-500 text-xl"></i>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500">Success Rate</p>
              <p class="text-3xl font-bold text-gray-800 mt-1">{{ successRate }}%</p>
            </div>
            <div class="rounded-full bg-yellow-100 p-3">
              <i class="fas fa-chart-line text-yellow-500 text-xl"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter Section -->
      <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200 flex flex-wrap items-end gap-4 mb-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="filters.status" 
                  class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
            <option value="">All</option>
            <option value="pending">Pending</option>
            <option value="hired">Hired</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
          <input v-model="filters.company" type="text" placeholder="Company name"
                 class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Candidate</label>
          <input v-model="filters.candidate" type="text" placeholder="Candidate name"
                 class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" />
        </div>
        <div class="flex-1 min-w-[200px]">
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <i class="fas fa-search text-gray-400"></i>
            </div>
            <input v-model="filters.search" @keyup.enter="onSearch" type="text" placeholder="Search..."
                   class="w-full pl-10 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" />
          </div>
        </div>
        <button @click="onSearch"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring focus:ring-blue-300 transition">
          <i class="fas fa-filter mr-2"></i> Apply Filters
        </button>
      </div>

      <!-- Table Section -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-600 font-medium">
              <tr>
                <th class="px-6 py-4 font-semibold">Candidate</th>
                <th class="px-6 py-4 font-semibold">Job Title</th>
                <th class="px-6 py-4 font-semibold">Company</th>
                <th class="px-6 py-4 font-semibold">Status</th>
                <th class="px-6 py-4 font-semibold">Referred At</th>
                <th class="px-6 py-4 font-semibold">Hired At</th>
                <th class="px-6 py-4 font-semibold">Match Score</th>
                <th class="px-6 py-4 font-semibold">Match Details</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="ref in referrals" :key="ref.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 font-medium">{{ ref.candidate }}</td>
                <td class="px-6 py-4">{{ ref.job_title }}</td>
                <td class="px-6 py-4">{{ ref.company }}</td>
                <td class="px-6 py-4">
                  <span 
                    class="px-2 py-1 text-xs font-medium rounded-full" 
                    :class="{
                      'bg-green-100 text-green-800': ref.status === 'hired',
                      'bg-yellow-100 text-yellow-800': ref.status === 'pending',
                      'bg-red-100 text-red-800': ref.status === 'rejected'
                    }">
                    {{ ref.status.charAt(0).toUpperCase() + ref.status.slice(1) }}
                  </span>
                </td>
                <td class="px-6 py-4 text-gray-600">{{ ref.referred_at }}</td>
                <td class="px-6 py-4 text-gray-600">{{ ref.hired_at || '-' }}</td>
                <td class="px-6 py-4">
                  <span 
                    class="px-2 py-1 text-xs font-medium rounded-full" 
                    :class="{
                      'bg-green-100 text-green-800': ref.match_score >= 60,
                      'bg-yellow-100 text-yellow-800': ref.match_score >= 30 && ref.match_score < 60,
                      'bg-red-100 text-red-800': ref.match_score < 30
                    }">
                    {{ ref.match_score }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex flex-wrap gap-1">
                    <span v-for="detail in ref.match_details" :key="detail"
                          class="bg-gray-100 rounded px-2 py-1 text-xs text-gray-700">
                      {{ detail }}
                    </span>
                  </div>
                </td>
              </tr>
              <tr v-if="referrals.length === 0">
                <td colspan="8" class="px-6 py-8 text-center">
                  <div class="flex flex-col items-center justify-center text-gray-500">
                    <svg class="w-12 h-12 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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
              <button 
                :disabled="!link.url" 
                @click="goToPage(link)"
                :class="[
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