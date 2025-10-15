<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, router } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import Modal from '@/Components/Modal.vue';

const { props } = usePage();
const showSuccessModal = ref(false);
const showErrorModal = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const seminarRequests = computed(() => props.seminarRequests || []);
const seminars = computed(() => props.seminars || []); // Add this if you pass seminars from backend

console.log('Seminars:', seminars.value); // Debugging line

const showDetailsModal = ref(false);
const details = ref({});
const activeTab = ref('requests'); // 'requests' or 'seminars'

function handleSuccess(msg) {
  successMessage.value = msg;
  showSuccessModal.value = true;
  router.reload();
}

function closeSuccessModal() {
  showSuccessModal.value = false;
  router.reload();
}
function handleError(msg) {
  errorMessage.value = msg;
  showErrorModal.value = true;
}
const approve = (id) => {
  router.post(route('admin.seminar-requests.update-status', id), { status: 'approved' }, {
    onSuccess: () => {
      const req = seminarRequests.value.find(r => r.id === id);
      if (req) req.status = 'approved';
      handleSuccess('Request approved!');
    },
    onError: () => handleError('Failed to approve request.'),
  });
};
const disapprove = (id) => {
  router.post(route('admin.seminar-requests.update-status', id), { status: 'disapproved' }, {
    onSuccess: () => {
      const req = seminarRequests.value.find(r => r.id === id);
      if (req) req.status = 'disapproved';
      handleSuccess('Request disapproved!');
    },
    onError: () => handleError('Failed to disapprove request.'),
  });
};

const viewDetails = (req) => {
  details.value = req;
  showDetailsModal.value = true;
};



const showSeminarModal = ref(false);
const seminarForm = ref({ id: null, title: '', date: '', description: '' });

const openSeminarModal = (seminar = null) => {
  if (seminar) {
    seminarForm.value = { ...seminar };
  } else {
    seminarForm.value = { id: null, title: '', date: '', description: '' };
  }
  showSeminarModal.value = true;
};

const showAttendanceModal = ref(false);
const attendanceSeminar = ref(null);
const attendanceForm = ref({ attendee_name: '', gender: '', age: '', address: '', contact_number: '' });
function openAttendanceModal(seminar) {
  attendanceSeminar.value = seminar;
  attendanceForm.value = { attendee_name: '', gender: '', age: '' };
  showAttendanceModal.value = true;
}

const attendeesPagination = ref({ current_page: 1, last_page: 1 });

async function viewAttendees(seminar, page = 1) {
  showAttendeesModal.value = true;
  attendeesList.value = [];
  loadingAttendees.value = true;
  try {
    const response = await fetch(`/admin/seminars/${seminar.id}/attendees?page=${page}`);
    const data = await response.json();
    attendeesList.value = data.data || [];
    attendeesPagination.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      seminar_id: seminar.id
    };
  } catch (e) {
    attendeesList.value = [];
  }
  loadingAttendees.value = false;
}

function goToAttendeesPage(page) {
  viewAttendees({ id: attendeesPagination.value.seminar_id }, page);
}


const saveSeminar = () => {
  if (seminarForm.value.id) {
    router.put(route('admin.seminars.update', seminarForm.value.id), seminarForm.value, {
      onSuccess: () => {
        // Update the seminar in local state
        const idx = seminars.value.findIndex(s => s.id === seminarForm.value.id);
        if (idx !== -1) seminars.value[idx] = { ...seminarForm.value };
        handleSuccess('Seminar updated successfully!');
      },
      onError: () => handleError('Failed to update seminar.'),
    });
  } else {
    router.post(route('admin.seminars.store'), seminarForm.value, {
      onSuccess: (page) => {
        // Add the new seminar to local state
        if (page.props.seminar) seminars.value.push(page.props.seminar);
        handleSuccess('Seminar added successfully!');
      },
      onError: () => handleError('Failed to add seminar.'),
    });
  }
  showSeminarModal.value = false;
};
const archiveSeminar = (id) => {
  router.delete(route('admin.seminars.archive', id), {
    onSuccess: () => {
      seminars.value = seminars.value.filter(s => s.id !== id);
      handleSuccess('Seminar archived successfully!');
    },
    onError: () => handleError('Failed to archive seminar.'),
  });
};
function saveAttendance() {
  router.post(
    route('admin.seminars.attendance.add', attendanceSeminar.value.id),
    { attendees: [attendanceForm.value] },
    {
      onSuccess: () => {
        showAttendanceModal.value = false;
        handleSuccess('Attendance added successfully!');
        // Refresh attendees list for the seminar
        viewAttendees(attendanceSeminar.value, attendeesPagination.value.current_page);
      },
      onError: () => {
        handleError('Failed to add attendance.');
      }
    }
  );
}

const showAttendeesModal = ref(false);
const attendeesList = ref([]);
const loadingAttendees = ref(false);


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

      <div class="mb-6 flex gap-2 border-b border-gray-200">
        <button
          :class="['px-6 py-2 font-medium rounded-t-lg focus:outline-none transition', activeTab === 'requests' ? 'bg-blue-50 text-blue-700 border-b-2 border-blue-600' : 'text-gray-600']"
          @click="activeTab = 'requests'">
          Seminar Requests
        </button>
        <button
          :class="['px-6 py-2 font-medium rounded-t-lg focus:outline-none transition', activeTab === 'seminars' ? 'bg-blue-50 text-blue-700 border-b-2 border-blue-600' : 'text-gray-600']"
          @click="activeTab = 'seminars'">
          Manage Seminars/Events
        </button>
      </div>

      <div v-if="activeTab === 'requests'">

        <!-- Stats Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div
            class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
            <div class="flex flex-col items-center text-center">
              <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3">
                <i class="fas fa-users text-white text-lg"></i>
              </div>
              <h3 class="text-blue-700 text-sm font-medium mb-2">Total Requests</h3>
              <p class="text-2xl font-bold text-blue-900">{{ seminarRequests.length }}</p>
            </div>
          </div>
          <div
            class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
            <div class="flex flex-col items-center text-center">
              <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-3">
                <i class="fas fa-check-circle text-white text-lg"></i>
              </div>
              <h3 class="text-green-700 text-sm font-medium mb-2">Approved</h3>
              <p class="text-2xl font-bold text-green-900">{{seminarRequests.filter(r => r.status === 'approved').length
              }}
              </p>
            </div>
          </div>
          <div
            class="bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
            <div class="flex flex-col items-center text-center">
              <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center mb-3">
                <i class="fas fa-chart-line text-white text-lg"></i>
              </div>
              <h3 class="text-yellow-700 text-sm font-medium mb-2">Pending</h3>
              <p class="text-2xl font-bold text-yellow-900">{{seminarRequests.filter(r => r.status === 'pending').length
              }}
              </p>
            </div>
          </div>
        </div>

        <!-- Requests Table Section -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
          <div
            class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
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
                    <span class="px-2 py-1 text-xs font-medium rounded-full" :class="{
                      'bg-green-100 text-green-800': req.status === 'approved',
                      'bg-yellow-100 text-yellow-800': req.status === 'pending',
                      'bg-red-100 text-red-800': req.status === 'disapproved'
                    }">
                      {{ req.status.charAt(0).toUpperCase() + req.status.slice(1) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 flex items-center gap-2">
                    <button v-if="req.status === 'pending'" @click="approve(req.id)"
                      class="px-3 py-1 rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition">
                      Approve
                    </button>
                    <button v-if="req.status === 'pending'" @click="disapprove(req.id)"
                      class="px-3 py-1 rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition">
                      Disapprove
                    </button>
                    <button @click="viewDetails(req)"
                      class="px-3 py-1 rounded-lg text-sm font-medium text-blue-600 border border-blue-600 hover:bg-blue-50 transition">
                      View Details
                    </button>


                  </td>
                </tr>
                <tr v-if="seminarRequests.length === 0">
                  <td colspan="5" class="px-6 py-8 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-500">
                      <svg class="w-12 h-12 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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
                <input :value="details.institution?.institution_name" readonly
                  class="w-full border rounded px-3 py-2 bg-gray-100" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Event Name</label>
                <input :value="details.event_name" readonly class="w-full border rounded px-3 py-2 bg-gray-100" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Scheduled Date & Time</label>
                <input :value="details.scheduled_at ? new Date(details.scheduled_at).toLocaleString() : ''" readonly
                  class="w-full border rounded px-3 py-2 bg-gray-100" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea :value="details.description" readonly
                  class="w-full border rounded px-3 py-2 bg-gray-100"></textarea>
              </div>
              <div class="flex justify-end">
                <button type="button" @click="showDetailsModal = false" class="px-4 py-2 rounded border">Close</button>
              </div>
            </div>
          </template>
        </Modal>
      </div>

      <div v-if="activeTab === 'seminars'">
        <div class="flex justify-between items-center mb-4">
          <h3 class="font-semibold text-lg text-gray-800">Seminars & Events</h3>
          <button @click="openSeminarModal()"
            class="px-4 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition">
            Add Seminar/Event
          </button>
        </div>
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
          <table class="min-w-full text-sm text-left text-gray-700">
            <thead class="bg-blue-50 text-xs uppercase tracking-wider text-gray-600 font-medium">
              <tr>
                <th class="px-6 py-4 font-semibold">Title</th>
                <th class="px-6 py-4 font-semibold">Date</th>
                <th class="px-6 py-4 font-semibold">Description</th>
                <th class="px-6 py-4 font-semibold">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="seminar in seminars" :key="seminar.id" class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 font-medium">{{ seminar.title }}</td>
                <td class="px-6 py-4">{{ seminar.date ? new Date(seminar.date).toLocaleString() : '' }}</td>
                <td class="px-6 py-4">{{ seminar.description }}</td>
                <td class="px-6 py-4 flex items-center gap-2">
                  <button @click="openSeminarModal(seminar)"
                    class="px-3 py-1 rounded-lg text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition">
                    Edit
                  </button>
                  <button @click="archiveSeminar(seminar.id)"
                    class="px-3 py-1 rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition">
                    Archive
                  </button>
                  <button @click="openAttendanceModal(seminar)"
                    class="px-3 py-1 rounded-lg text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition">
                    Attendance
                  </button>
                  <button @click="viewAttendees(seminar)"
                    class="px-3 py-1 rounded-lg text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 transition">
                    View Attendees
                  </button>
                </td>
              </tr>
              <tr v-if="seminars.length === 0">
                <td colspan="4" class="px-6 py-8 text-center text-gray-500">No seminars/events found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>



      <!-- Seminar Add/Edit Modal -->
      <Modal v-model="showSeminarModal">
        <template #body>
          <div class="p-4 space-y-4">
            <h3 class="text-lg font-semibold mb-2">{{ seminarForm.id ? 'Edit Seminar/Event' : 'Add Seminar/Event' }}
            </h3>
            <div>
              <label class="block text-sm font-medium mb-1">Title</label>
              <input v-model="seminarForm.title" class="w-full border rounded px-3 py-2 bg-gray-100" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Date & Time</label>
              <input v-model="seminarForm.date" type="datetime-local"
                class="w-full border rounded px-3 py-2 bg-gray-100" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Description</label>
              <textarea v-model="seminarForm.description"
                class="w-full border rounded px-3 py-2 bg-gray-100"></textarea>
            </div>
            <div class="flex justify-end gap-2">
              <button type="button" @click="showSeminarModal = false" class="px-4 py-2 rounded border">Cancel</button>
              <button type="button" @click="saveSeminar"
                class="px-4 py-2 rounded bg-blue-600 text-white font-medium hover:bg-blue-700 transition">
                Save
              </button>
            </div>
          </div>
        </template>
      </Modal>

      <!-- Attendance Modal -->
      <Modal v-model="showAttendanceModal">
        <template #body>
          <div class="p-4 space-y-4">
            <h3 class="text-lg font-semibold mb-2">Add Attendance for {{ attendanceSeminar?.title }}</h3>
            <div>
              <label class="block text-sm font-medium mb-1">Attendee Name</label>
              <input v-model="attendanceForm.attendee_name" class="w-full border rounded px-3 py-2 bg-gray-100" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Gender</label>
              <select v-model="attendanceForm.gender" class="w-full border rounded px-3 py-2 bg-gray-100">
                <option value="">Select</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Age</label>
              <input v-model="attendanceForm.age" type="number" min="1"
                class="w-full border rounded px-3 py-2 bg-gray-100" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Address</label>
              <input v-model="attendanceForm.address" class="w-full border rounded px-3 py-2 bg-gray-100" />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Contact Number</label>
              <input v-model="attendanceForm.contact_number" type="text" pattern="^09\d{9}$" maxlength="11"
                class="w-full border rounded px-3 py-2 bg-gray-100" placeholder="e.g. 09123456789" />
            </div>
            <div class="flex justify-end gap-2">
              <button type="button" @click="showAttendanceModal = false"
                class="px-4 py-2 rounded border">Cancel</button>
              <button type="button" @click="saveAttendance"
                class="px-4 py-2 rounded bg-green-600 text-white font-medium hover:bg-green-700 transition">
                Save
              </button>
            </div>
          </div>
        </template>
      </Modal>

      <!-- View Attendees Modal -->
      <Modal v-model="showAttendeesModal">
        <template #body>
          <div class="p-4 space-y-4">
            <h3 class="text-lg font-semibold mb-2">Attendees List</h3>
            <div v-if="loadingAttendees" class="text-gray-500">Loading...</div>
            <div v-else>
              <table v-if="attendeesList.length" class="min-w-full text-sm text-left text-gray-700">
                <thead class="bg-blue-50 text-xs uppercase tracking-wider text-gray-600 font-medium">
                  <tr>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Gender</th>
                    <th class="px-4 py-2">Age</th>
                    <th class="px-4 py-2">Address</th>
                    <th class="px-4 py-2">Contact Number</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="att in attendeesList" :key="att.id">
                    <td class="px-4 py-2">{{ att.attendee_name }}</td>
                    <td class="px-4 py-2">{{ att.gender }}</td>
                    <td class="px-4 py-2">{{ att.age }}</td>
                    <td class="px-4 py-2">{{ att.address }}</td>
                    <td class="px-4 py-2">{{ att.contact_number }}</td>
                  </tr>
                </tbody>
              </table>
              <div v-else class="text-gray-500">No attendees found.</div>
              <!-- Pagination Controls -->
              <div v-if="attendeesPagination.last_page > 1" class="flex justify-center mt-4 gap-2">
                <button :disabled="attendeesPagination.current_page === 1"
                  @click="goToAttendeesPage(attendeesPagination.current_page - 1)"
                  class="px-3 py-1 rounded border">Prev</button>
                <span>Page {{ attendeesPagination.current_page }} of {{ attendeesPagination.last_page }}</span>
                <button :disabled="attendeesPagination.current_page === attendeesPagination.last_page"
                  @click="goToAttendeesPage(attendeesPagination.current_page + 1)"
                  class="px-3 py-1 rounded border">Next</button>
              </div>
            </div>
            <div class="flex justify-end">
              <button type="button" @click="showAttendeesModal = false" class="px-4 py-2 rounded border">Close</button>
            </div>
          </div>
        </template>
      </Modal>


      <Modal v-model="showSuccessModal">
        <template #body>
          <div class="p-6 text-center">
            <h3 class="text-lg font-semibold mb-2 text-green-700">Success</h3>
            <p class="mb-4">{{ successMessage }}</p>
            <button @click="closeSuccessModal" class="px-4 py-2 rounded bg-green-600 text-white">OK</button>
          </div>
        </template>
      </Modal>

      <Modal v-model="showErrorModal">
        <template #body>
          <div class="p-6 text-center">
            <h3 class="text-lg font-semibold mb-2 text-red-700">Error</h3>
            <p class="mb-4">{{ errorMessage }}</p>
            <button @click="showErrorModal = false" class="px-4 py-2 rounded bg-red-600 text-white">OK</button>
          </div>
        </template>
      </Modal>

    </div>
  </AppLayout>
</template>