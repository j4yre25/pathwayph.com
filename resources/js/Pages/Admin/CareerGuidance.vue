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
  <AppLayout title="Career Guidance">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      
      <!-- Header Section -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
          <a href="javascript:history.back()" 
             class="mr-4 text-gray-500 hover:text-gray-700 transition-colors">
            <i class="fas fa-chevron-left"></i>
          </a>
          <i class="fas fa-compass text-blue-500 text-xl mr-2"></i>
          <h1 class="text-2xl font-bold text-gray-800">
            Career Guidance & Counseling
          </h1>
        </div>
      </div>

      <!-- Requests Table -->
      <section class="mb-8">
        <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-indigo-50">
              <tr>
                <th class="px-6 py-3 text-left font-semibold text-indigo-800">Institution</th>
                <th class="px-6 py-3 text-left font-semibold text-indigo-800">Event Name</th>
                <th class="px-6 py-3 text-left font-semibold text-indigo-800">Date Requested</th>
                <th class="px-6 py-3 text-left font-semibold text-indigo-800">Status</th>
                <th class="px-6 py-3 text-center font-semibold text-indigo-800">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="req in seminarRequests" :key="req.id" 
                  class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 font-medium text-gray-700">
                  {{ req.institution.institution_name }}
                </td>
                <td class="px-6 py-4 text-gray-600">
                  {{ req.event_name }}
                </td>
                <td class="px-6 py-4 text-gray-600">
                  {{ new Date(req.created_at).toLocaleString() }}
                </td>
                <td class="px-6 py-4">
                  <span 
                    :class="[
                      'px-3 py-1 rounded-full text-xs font-semibold',
                      req.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '',
                      req.status === 'approved' ? 'bg-green-100 text-green-800' : '',
                      req.status === 'disapproved' ? 'bg-red-100 text-red-800' : ''
                    ]"
                  >
                    {{ req.status }}
                  </span>
                </td>
                <td class="px-6 py-4 flex items-center justify-center gap-2">
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
                    class="px-3 py-1 rounded-lg text-sm font-medium text-indigo-600 border border-indigo-600 hover:bg-indigo-50 transition"
                  >
                    View Details
                  </button>
                </td>
              </tr>

              <tr v-if="seminarRequests.length === 0">
                <td colspan="5" class="px-6 py-6 text-center text-gray-500">
                  No seminar requests found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

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
