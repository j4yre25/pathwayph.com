<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Modal from '@/Components/Modal.vue';

const { props } = usePage();
const seminarRequests = computed(() => props.seminarRequests || []);

const showDetailsModal = ref(false);
const details = ref({});

const approve = (id) => {
  router.post(route('admin.seminar-requests.update-status', id), { status: 'approved' });
};
const disapprove = (id) => {
  router.post(route('admin.seminar-requests.update-status', id), { status: 'disapproved' });
};
const viewDetails = (req) => {
  details.value = req;
  showDetailsModal.value = true;
};
</script>

<template>
  <AppLayout title="Career Guidance & Counseling">
    <template #header>
      <div class="flex items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
          <i class="fas fa-compass text-blue-500 mr-2"></i> Career Guidance & Counseling
        </h2>
      </div>
    </template>

    <div class="py-8 max-w-7xl mx-auto">
      <!-- Stats Summary -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-users text-white text-lg"></i>
            </div>
            <h3 class="text-blue-700 text-sm font-medium mb-2">Total Requests</h3>
            <p class="text-2xl font-bold text-blue-900">{{ seminarRequests.length }}</p>
          </div>
        </div>
        <div class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-check-circle text-white text-lg"></i>
            </div>
            <h3 class="text-green-700 text-sm font-medium mb-2">Approved</h3>
            <p class="text-2xl font-bold text-green-900">{{ seminarRequests.filter(r => r.status === 'approved').length }}</p>
          </div>
        </div>
        <div class="bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
          <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center mb-3">
              <i class="fas fa-chart-line text-white text-lg"></i>
            </div>
            <h3 class="text-yellow-700 text-sm font-medium mb-2">Pending</h3>
            <p class="text-2xl font-bold text-yellow-900">{{ seminarRequests.filter(r => r.status === 'pending').length }}</p>
          </div>
        </div>
      </div>

      <!-- Requests Table Section -->
      <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
              <i class="fas fa-compass text-white text-sm"></i>
            </div>
            <div>
              <h3 class="font-semibold text-gray-800">Seminar Requests</h3>
              <p class="text-sm text-gray-500 mt-1">Manage seminar and counseling requests from institutions.</p>
            </div>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-blue-50 text-xs uppercase tracking-wider text-gray-600 font-medium">
              <tr>
                <th class="px-6 py-4 font-semibold">Institution</th>
                <th class="px-6 py-4 font-semibold">Event Name</th>
                <th class="px-6 py-4 font-semibold">Date Requested</th>
                <th class="px-6 py-4 font-semibold">Status</th>
                <th class="px-6 py-4 font-semibold">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="req in seminarRequests" :key="req.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 font-medium">{{ req.institution.institution_name }}</td>
                <td class="px-6 py-4">{{ req.event_name }}</td>
                <td class="px-6 py-4 text-gray-600">{{ new Date(req.created_at).toLocaleString() }}</td>
                <td class="px-6 py-4">
                  <span
                    class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="{
                      'bg-green-100 text-green-800': req.status === 'approved',
                      'bg-yellow-100 text-yellow-800': req.status === 'pending',
                      'bg-red-100 text-red-800': req.status === 'disapproved'
                    }">
                    {{ req.status.charAt(0).toUpperCase() + req.status.slice(1) }}
                  </span>
                </td>
                <td class="px-6 py-4 flex items-center gap-2">
                  <button
                    v-if="req.status === 'pending'"
                    @click="approve(req.id)"
                    class="px-3 py-1 rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition"
                  >
                    Approve
                  </button>
                  <button
                    v-if="req.status === 'pending'"
                    @click="disapprove(req.id)"
                    class="px-3 py-1 rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition"
                  >
                    Disapprove
                  </button>
                  <button
                    @click="viewDetails(req)"
                    class="px-3 py-1 rounded-lg text-sm font-medium text-blue-600 border border-blue-600 hover:bg-blue-50 transition"
                  >
                    View Details
                  </button>
                </td>
              </tr>
              <tr v-if="seminarRequests.length === 0">
                <td colspan="5" class="px-6 py-8 text-center">
                  <div class="flex flex-col items-center justify-center text-gray-500">
                    <svg class="w-12 h-12 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-lg font-medium">No seminar requests found</p>
                    <p class="text-sm">Try adjusting your search or filter criteria</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Details Modal -->
      <Modal v-model="showDetailsModal">
        <template #body>
          <div class="p-4 space-y-4">
            <h3 class="text-lg font-semibold mb-2">Seminar Request Details</h3>
            <div>
              <label class="block text-sm font-medium mb-1">Institution</label>
              <input :value="details.institution?.institution_name" readonly class="w-full border rounded px-3 py-2 bg-gray-100" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Event Name</label>
              <input :value="details.event_name" readonly class="w-full border rounded px-3 py-2 bg-gray-100" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Scheduled Date & Time</label>
              <input :value="details.scheduled_at ? new Date(details.scheduled_at).toLocaleString() : ''" readonly class="w-full border rounded px-3 py-2 bg-gray-100" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Description</label>
              <textarea :value="details.description" readonly class="w-full border rounded px-3 py-2 bg-gray-100"></textarea>
            </div>
            <div class="flex justify-end">
              <button type="button" @click="showDetailsModal = false" class="px-4 py-2 rounded border">Close</button>
            </div>
          </div>
        </template>
      </Modal>
    </div>
  </AppLayout>
</template>
