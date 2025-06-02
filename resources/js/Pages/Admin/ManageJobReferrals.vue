<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const { props } = usePage();

const referrals = computed(() => props.referrals || []);
const totalReferrals = computed(() => props.totalReferrals || 0);
const successfulReferrals = computed(() => props.successfulReferrals || 0);
const successRate = computed(() => props.successRate || 0);


console.log('Referrals:', referrals.value);
</script>

<template>
  <AppLayout title="Manage Job Referrals">
    <div class="max-w-5xl mx-auto py-8 px-4">
      <h2 class="text-2xl font-bold mb-4">Job Referrals Tracking</h2>
      <div class="mb-6 flex flex-wrap gap-6">
        <div class="bg-blue-100 text-blue-800 px-6 py-4 rounded shadow">
          <div class="text-lg font-semibold">Total Referrals</div>
          <div class="text-2xl">{{ totalReferrals }}</div>
        </div>
        <div class="bg-green-100 text-green-800 px-6 py-4 rounded shadow">
          <div class="text-lg font-semibold">Successful Referrals (Hired)</div>
          <div class="text-2xl">{{ successfulReferrals }}</div>
        </div>
        <div class="bg-yellow-100 text-yellow-800 px-6 py-4 rounded shadow">
          <div class="text-lg font-semibold">Referral Success Rate</div>
          <div class="text-2xl">{{ successRate }}%</div>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full bg-white border rounded shadow">
          <thead>
            <tr class="bg-gray-100 text-gray-700">
              <th class="py-2 px-4 border-b">Candidate</th>
              <th class="py-2 px-4 border-b">Job Title</th>
              <th class="py-2 px-4 border-b">Company</th>
              <th class="py-2 px-4 border-b">Status</th>
              <th class="py-2 px-4 border-b">Referred At</th>
              <th class="py-2 px-4 border-b">Hired At</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="ref in referrals" :key="ref.id">
              <td class="py-2 px-4 border-b">{{ ref.candidate }}</td>
              <td class="py-2 px-4 border-b">{{ ref.job_title }}</td>
              <td class="py-2 px-4 border-b">{{ ref.company }}</td>
              <td class="py-2 px-4 border-b">
                <span :class="{
                  'bg-green-100 text-green-800 px-2 py-1 rounded': ref.status === 'hired',
                  'bg-yellow-100 text-yellow-800 px-2 py-1 rounded': ref.status === 'pending',
                  'bg-red-100 text-red-800 px-2 py-1 rounded': ref.status === 'rejected'
                }">
                  {{ ref.status.charAt(0).toUpperCase() + ref.status.slice(1) }}
                </span>
              </td>
              <td class="py-2 px-4 border-b">{{ ref.referred_at }}</td>
              <td class="py-2 px-4 border-b">{{ ref.hired_at || '-' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>