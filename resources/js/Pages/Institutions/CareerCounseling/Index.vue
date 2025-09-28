<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, router } from '@inertiajs/vue3';
import { computed, ref, reactive } from 'vue';
import Modal from '@/Components/Modal.vue';

const { props } = usePage();
const counselingTips = computed(() => props.counselingTips || []);
const inDemandJobs = computed(() => props.inDemandJobs || []);
const topSkills = computed(() => props.topSkills || {});
const jobCounts = computed(() => props.jobCounts || []);
const resources = computed(() => props.resources || []);
const seminarRequests = computed(() => props.seminarRequests || []);

const showRequestModal = ref(false);
const showDetailsModal = ref(false);
const form = reactive({
  request_for: 'seminar',
  event_name: '',
  scheduled_at: '',
  description: ''
});
const details = ref({});

function submitRequest() {
  router.post(route('institutions.seminar-requests.store'), form, {
    onSuccess: () => {
      showRequestModal.value = false;
      // reset form
      form.event_name = '';
      form.scheduled_at = '';
      form.description = '';
    }
  });
}

function cancelRequest(id) {
  if (confirm('Are you sure you want to cancel this request?')) {
    router.post(route('institutions.seminar-requests.cancel', id));
  }
}

function viewDetails(req) {
  details.value = req;
  showDetailsModal.value = true;
}
</script>

<template>
  <AppLayout title="Career Guidance">
    <div class="max-w-6xl mx-auto py-12 px-4 sm:px-8">
      <h1 class="text-4xl font-extrabold mb-10 text-center text-indigo-800 tracking-tight">
        <span class="inline-flex items-center gap-2">
          <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M12 14v7m0 0H5a2 2 0 01-2-2v-5a2 2 0 012-2h7zm0 0h7a2 2 0 002-2v-5a2 2 0 00-2-2h-7z" />
          </svg>
          Career Guidance & Counseling
        </span>
      </h1>

      <!-- Requested Seminars Section -->
      <section class="mb-12">
        <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-100">
          <!-- Header -->
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-indigo-700 flex items-center gap-2">
              <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24">
                <path d="M3 17v-6a9 9 0 0118 0v6"></path>
                <path d="M21 17a2 2 0 01-2 2H5a2 2 0 01-2-2"></path>
              </svg>
              Requested Seminars
            </h2>
            <button @click="showRequestModal = true"
              class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2.5 rounded-lg shadow-md transition">
              + Request Seminar
            </button>
          </div>

          <!-- Request Seminar Modal -->
          <Modal v-model="showRequestModal">
            <template #header>
              <h3 class="text-xl font-semibold text-indigo-700">Request a Seminar</h3>
            </template>
            <template #body>
              <form @submit.prevent="submitRequest" class="space-y-5">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Request For</label>
                  <select v-model="form.request_for" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="seminar">Seminar</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Event Name</label>
                  <input v-model="form.event_name" placeholder="Enter event name" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Scheduled Date & Time</label>
                  <input type="datetime-local" v-model="form.scheduled_at" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                  <textarea v-model="form.description" placeholder="Brief description"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>

                <div class="flex justify-end gap-3">
                  <button type="button" @click="showRequestModal = false"
                    class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition">
                    Cancel
                  </button>
                  <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow-md transition">
                    Send Request
                  </button>
                </div>
              </form>
            </template>
          </Modal>

          <!-- View Details Modal -->
          <Modal v-model="showDetailsModal">
            <template #header>
              <h3 class="text-xl font-semibold text-indigo-700">Seminar Request Details</h3>
            </template>
            <template #body>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Event Name</label>
                  <input :value="details.event_name" readonly class="w-full border rounded-lg px-3 py-2 bg-gray-100" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Scheduled Date & Time</label>
                  <input :value="details.scheduled_at ? new Date(details.scheduled_at).toLocaleString() : ''" readonly
                    class="w-full border rounded-lg px-3 py-2 bg-gray-100" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                  <textarea :value="details.description" readonly
                    class="w-full border rounded-lg px-3 py-2 bg-gray-100"></textarea>
                </div>
                <div class="flex justify-end">
                  <button @click="showDetailsModal = false"
                    class="px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-100 transition">
                    Close
                  </button>
                </div>
              </div>
            </template>
          </Modal>

          <!-- Requested Seminars Table -->
          <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm mt-6">
            <table class="min-w-full bg-white text-sm">
              <thead>
                <tr class="bg-indigo-50 text-indigo-800 text-left">
                  <th class="py-3 px-4 border-b">Event Name</th>
                  <th class="py-3 px-4 border-b">Date Requested</th>
                  <th class="py-3 px-4 border-b">Status</th>
                  <th class="py-3 px-4 border-b">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="req in seminarRequests" :key="req.id" class="hover:bg-indigo-50 even:bg-gray-50 transition">
                  <td class="py-3 px-4 border-b font-medium">{{ req.event_name }}</td>
                  <td class="py-3 px-4 border-b">{{ new Date(req.created_at).toLocaleString() }}</td>
                  <td class="py-3 px-4 border-b">
                    <span :class="{
                      'px-3 py-1 rounded-full text-xs font-semibold': true,
                      'bg-yellow-100 text-yellow-800': req.status === 'pending',
                      'bg-green-100 text-green-800': req.status === 'approved',
                      'bg-red-100 text-red-800': req.status === 'cancelled'
                    }">
                      {{ req.status }}
                    </span>
                  </td>
                  <td class="py-3 px-4 border-b flex gap-3">
                    <button v-if="req.status === 'pending'" @click="cancelRequest(req.id)"
                      class="text-red-600 hover:underline">
                      Cancel
                    </button>
                    <button @click="viewDetails(req)" class="text-indigo-600 hover:underline">
                      View Details
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>


      <!-- Top In-Demand Roles -->
      <section class="mb-12">
        <div class="bg-white rounded-xl shadow p-8">
          <h2 class="text-2xl font-semibold mb-4 text-indigo-700 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M3 17v-6a9 9 0 0118 0v6"></path>
              <path d="M21 17a2 2 0 01-2 2H5a2 2 0 01-2-2"></path>
            </svg>
            Top In-Demand Roles
          </h2>
          <div class="overflow-x-auto">
            <table class="min-w-full bg-white border rounded shadow-sm">
              <thead>
                <tr class="bg-indigo-50 text-indigo-800">
                  <th class="py-3 px-4 border-b text-left">Role</th>
                  <th class="py-3 px-4 border-b text-left">Sector</th>
                  <th class="py-3 px-4 border-b text-left">Category</th>
                  <th class="py-3 px-4 border-b text-left">Programs</th>
                  <th class="py-3 px-4 border-b text-left">Key Skills</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="job in inDemandJobs" :key="job.role" class="hover:bg-indigo-50 even:bg-gray-50">
                  <td class="py-2 px-4 border-b font-medium">{{ job.role }}</td>
                  <td class="py-2 px-4 border-b">{{ job.sector }}</td>
                  <td class="py-2 px-4 border-b">{{ job.category }}</td>
                  <td class="py-2 px-4 border-b">
                    <span v-for="p in job.programs" :key="p"
                      class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded mr-1 mb-1 text-xs">
                      {{ p }}
                    </span>
                  </td>
                  <td class="py-2 px-4 border-b">
                    <span v-for="s in job.skills" :key="s"
                      class="inline-block bg-green-100 text-green-800 px-2 py-1 rounded mr-1 mb-1 text-xs">
                      {{ s }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- Top In-Demand Skills -->
      <section class="mb-12">
        <div class="bg-white rounded-xl shadow p-8">
          <h2 class="text-2xl font-semibold mb-4 text-indigo-700 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10"></circle>
              <path d="M12 8v4l3 3"></path>
            </svg>
            Top In-Demand Skills
          </h2>
          <div class="flex flex-wrap gap-3 mt-2">
            <span v-for="(count, skill) in topSkills" :key="skill"
              class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full text-base font-medium shadow">
              {{ skill }} <span class="font-bold">({{ count }})</span>
            </span>
          </div>
        </div>
      </section>

      <!-- Real-Time Labor Market Data -->
      <section class="mb-12">
        <div class="bg-white rounded-xl shadow p-8">
          <h2 class="text-2xl font-semibold mb-4 text-indigo-700 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M3 17v-6a9 9 0 0118 0v6"></path>
              <path d="M21 17a2 2 0 01-2 2H5a2 2 0 01-2-2"></path>
            </svg>
            Real-Time Job Openings by Role
          </h2>
          <div class="overflow-x-auto">
            <table class="min-w-full bg-white border rounded shadow-sm">
              <thead>
                <tr class="bg-indigo-50 text-indigo-800">
                  <th class="py-3 px-4 border-b text-left">Job Title</th>
                  <th class="py-3 px-4 border-b text-left">Openings</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="job in jobCounts" :key="job.job_title" class="hover:bg-indigo-50 even:bg-gray-50">
                  <td class="py-2 px-4 border-b">{{ job.job_title }}</td>
                  <td class="py-2 px-4 border-b">{{ job.openings }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>
  </AppLayout>
</template>