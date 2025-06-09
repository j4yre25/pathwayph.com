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

watch(filters, applyFilters, { deep: true });

</script>

<template>
  <AppLayout title="Manage Job Referrals">
    <div class="max-w-6xl mx-auto py-10 px-4">
      <h2 class="text-3xl font-extrabold mb-8 text-gray-800 tracking-tight">Job Referrals Tracking</h2>
      <!-- Filters -->
      <div class="mb-6 flex flex-col md:flex-row md:items-end md:space-x-4 space-y-2 md:space-y-0">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="filters.status" class="rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All</option>
            <option value="pending">Pending</option>
            <option value="hired">Hired</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
          <input v-model="filters.company" type="text" placeholder="Company name"
            class="rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Candidate</label>
          <input v-model="filters.candidate" type="text" placeholder="Candidate name"
            class="rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
        </div>
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input v-model="filters.search" @keyup.enter="onSearch" type="text" placeholder="Search..."
            class="w-full rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
        </div>
        <button @click="onSearch"
          class="bg-blue-600 text-white px-4 py-2 rounded font-semibold hover:bg-blue-700 transition">Search</button>
      </div>

      <div class="mb-8 grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-blue-50 border-l-4 border-blue-400 px-6 py-5 rounded-lg shadow-sm flex flex-col items-center">
          <div class="text-base font-medium text-blue-700 mb-1">Total Referrals</div>
          <div class="text-3xl font-bold text-blue-900">{{ totalReferrals }}</div>
        </div>
        <div class="bg-green-50 border-l-4 border-green-400 px-6 py-5 rounded-lg shadow-sm flex flex-col items-center">
          <div class="text-base font-medium text-green-700 mb-1">Successful Referrals (Hired)</div>
          <div class="text-3xl font-bold text-green-900">{{ successfulReferrals }}</div>
        </div>
        <div
          class="bg-yellow-50 border-l-4 border-yellow-400 px-6 py-5 rounded-lg shadow-sm flex flex-col items-center">
          <div class="text-base font-medium text-yellow-700 mb-1">Referral Success Rate</div>
          <div class="text-3xl font-bold text-yellow-900">{{ successRate }}%</div>
        </div>
      </div>

      <div class="overflow-x-auto rounded-lg shadow">
        <table class="min-w-full bg-white">
          <thead>
            <tr class="bg-gray-50 text-gray-700 text-sm uppercase tracking-wider">
              <th class="py-3 px-4 text-left">Candidate</th>
              <th class="py-3 px-4 text-left">Job Title</th>
              <th class="py-3 px-4 text-left">Company</th>
              <th class="py-3 px-4 text-left">Status</th>
              <th class="py-3 px-4 text-left">Referred At</th>
              <th class="py-3 px-4 text-left">Hired At</th>
              <th class="py-3 px-4 text-left">Match Score</th>
              <th class="py-3 px-4 text-left">Match Details</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="ref in referrals" :key="ref.id" class="hover:bg-gray-50 transition">
              <td class="py-3 px-4 border-b font-medium text-gray-900">{{ ref.candidate }}</td>
              <td class="py-3 px-4 border-b text-gray-700">{{ ref.job_title }}</td>
              <td class="py-3 px-4 border-b text-gray-700">{{ ref.company }}</td>
              <td class="py-3 px-4 border-b">
                <span :class="{
                  'bg-green-100 text-green-800': ref.status === 'hired',
                  'bg-yellow-100 text-yellow-800': ref.status === 'pending',
                  'bg-red-100 text-red-800': ref.status === 'rejected'
                }" class="px-3 py-1 rounded-full font-semibold text-xs shadow">
                  {{ ref.status.charAt(0).toUpperCase() + ref.status.slice(1) }}
                </span>
              </td>
              <td class="py-3 px-4 border-b text-gray-600">{{ ref.referred_at }}</td>
              <td class="py-3 px-4 border-b text-gray-600">{{ ref.hired_at || '-' }}</td>
              <td class="py-3 px-4 border-b">
                <span class="inline-block px-2 py-1 rounded-full text-xs font-semibold" :class="{
                  'bg-green-50 text-green-700': ref.match_score >= 60,
                  'bg-yellow-50 text-yellow-700': ref.match_score >= 30 && ref.match_score < 60,
                  'bg-red-50 text-red-700': ref.match_score < 30
                }">
                  {{ ref.match_score }}
                </span>
              </td>
              <td class="py-3 px-4 border-b">
                <ul class="space-y-1">
                  <li v-for="detail in ref.match_details" :key="detail"
                    class="bg-gray-100 rounded px-2 py-1 text-xs text-gray-700 inline-block">
                    {{ detail }}
                  </li>
                </ul>
              </td>
            </tr>
            <tr v-if="referrals.length === 0">
              <td colspan="8" class="py-8 px-4 text-center text-gray-400 text-lg">
                No referrals found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination && pagination.links && pagination.links.length > 3" class="flex justify-end mt-6">
        <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
          <button v-for="(link, i) in pagination.links" :key="i" :disabled="!link.url" @click="goToPage(link)"
            v-html="link.label" :class="[
              'px-3 py-2 border text-sm font-medium',
              link.active ? 'z-10 bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
              !link.url ? 'opacity-50 cursor-not-allowed' : ''
            ]" />
        </nav>
      </div>
    </div>
  </AppLayout>
</template>