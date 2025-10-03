<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ClipboardList, TrendingUp, Sparkles, Briefcase, Plus } from 'lucide-vue-next';
import { usePage, router } from '@inertiajs/vue3';
import { computed, ref, reactive } from 'vue';
import Modal from '@/Components/Modal.vue';
import axios from 'axios';

const { props } = usePage();
const counselingTips = computed(() => props.counselingTips || []);
const inDemandJobs = computed(() => props.inDemandJobs || []);
const topSkills = computed(() => props.topSkills || {});
const jobCounts = computed(() => props.jobCounts || []);
const resources = computed(() => props.resources || []);
const seminarRequests = computed(() => props.seminarRequests || []);

const showRequestModal = ref(false);
const isSuccessModalOpen = ref(false);
const showDetailsModal = ref(false);
const showAttendanceModal = ref(false);
const form = reactive({
  request_for: 'seminar',
  event_name: '',
  scheduled_at: '',
  description: ''
});
const details = ref({});
const attendanceForm = reactive({
  attendees: [{ attendee_name: '', gender: '', age: '' }]
});
const attendanceList = ref([]);
const currentSeminarId = ref(null);
const file = ref(null);

function submitRequest() {
  const formData = new FormData();
  formData.append('request_for', form.request_for);
  formData.append('event_name', form.event_name);
  formData.append('scheduled_at', form.scheduled_at);
  formData.append('description', form.description);
  if (file.value) formData.append('attachment', file.value);
  formData.append('attendees', JSON.stringify(attendanceForm.attendees));

  router.post(route('institutions.seminar-requests.store'), formData, {
  forceFormData: true,
  onSuccess: () => {
    isSuccessModalOpen.value = true;
    form.event_name = '';
    form.scheduled_at = '';
    form.description = '';
    file.value = null;
    attendanceForm.attendees = [{ attendee_name: '', gender: '', age: '' }];
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
  // Fetch attendance count for this seminar
  axios.get(route('institutions.seminar-requests.attendance', req.id)).then(res => {
    details.value.attendanceCount = res.data.attendances ? res.data.attendances.length : 0;
  });
}

function handleSuccessClose() {
  isSuccessModalOpen.value = false;
  showRequestModal.value = false;
  router.visit(route('institutions.career-guidance'));
}

function openAttendanceModal(seminarId) {
  currentSeminarId.value = seminarId;
  showAttendanceModal.value = true;
  // Fetch attendance list
  axios.get(route('institutions.seminar-requests.attendance', seminarId)).then(res => {
    attendanceList.value = res.data.attendances || [];
  });
}

function addAttendanceRow() {
  attendanceForm.attendees.push({ attendee_name: '', gender: '', age: '' });
}

function submitAttendance() {
  router.post(route('institutions.seminar-requests.attendance.store', currentSeminarId.value), attendanceForm, {
    onSuccess: () => {
      showAttendanceModal.value = false;
      attendanceForm.attendees = [{ attendee_name: '', gender: '', age: '' }];
    }
  });
}

function handleFileUpload(e) {
  file.value = e.target.files[0];
}
</script>

<template>
  <AppLayout title="Career Guidance">
    <div class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8">
      <div class="max-w-7xl mx-auto space-y-10">
        <header class="pb-4 border-b border-slate-200">
          <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Career Guidance & Counseling</h1>
          <p class="mt-2 text-lg text-slate-500">Access market insights, manage requests, and guide career planning.</p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <section
            class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-xl shadow-slate-200/40 transition-all duration-300 hover:shadow-2xl hover:shadow-indigo-100/50">
            <div class="p-6">
              <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                  <h2 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                    Requested Seminars
                  </h2>
                  <p class="text-sm text-slate-500 mt-1">Manage and track your institution's seminar requests.</p>
                </div>
                <button @click="showRequestModal = true"
                  class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl transition-all duration-200 shadow-lg shadow-indigo-500/30 focus:outline-none focus:ring-4 focus:ring-indigo-500/50">
                  Request Seminar
                </button>
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-indigo-50/50">
                  <tr>
                    <th scope="col"
                      class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider">Event Name</th>
                    <th scope="col"
                      class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider">Date Requested</th>
                    <th scope="col"
                      class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider">Status</th>
                    <th scope="col"
                      class="px-6 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                  <tr v-if="seminarRequests.length === 0">
                    <td colspan="4" class="text-center py-10 text-slate-500 italic">No seminar requests found. Start by requesting one!</td>
                  </tr>
                  <tr v-for="req in seminarRequests" :key="req.id" class="hover:bg-indigo-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">{{ req.event_name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ new Date(req.created_at).toLocaleDateString() }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span :class="{
                        'px-3 py-1 rounded-full text-xs font-bold capitalize': true,
                        'bg-yellow-100 text-yellow-800 ring-1 ring-yellow-300': req.status === 'pending',
                        'bg-green-100 text-green-800 ring-1 ring-green-300': req.status === 'approved',
                        'bg-red-100 text-red-800 ring-1 ring-red-300': req.status === 'cancelled'
                      }">
                        {{ req.status }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                      <button @click="viewDetails(req)" class="text-indigo-600 hover:text-indigo-800 transition font-medium">View</button>
                      <button v-if="req.status === 'approved'" @click="openAttendanceModal(req.id)"
                        class="text-teal-600 hover:text-teal-800 transition font-medium">Attendance</button>
                      <button v-if="req.status === 'pending'" @click="cancelRequest(req.id)"
                        class="text-red-600 hover:text-red-800 transition font-medium">Cancel</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <section
            class="bg-white rounded-2xl border border-slate-100 shadow-xl shadow-slate-200/40 transition-all duration-300 hover:shadow-2xl hover:shadow-purple-100/50 h-full">
            <div class="p-6">
              <h2 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                Top In-Demand Skills
              </h2>
              <p class="text-sm text-slate-500 mt-1">Most frequently required skills across all sectors.</p>
            </div>
            <div class="p-6 pt-0">
              <div class="flex flex-wrap gap-3">
                <span v-for="(count, skill) in topSkills" :key="skill"
                  class="flex items-center bg-purple-50 text-purple-800 px-3 py-1.5 rounded-full text-sm font-semibold border border-purple-200 hover:bg-purple-100 transition-colors">
                  {{ skill }}
                  <span
                    class="ml-2 inline-block bg-white text-purple-900 text-xs font-bold px-2 py-0.5 rounded-full ring-1 ring-purple-300">{{
                    count }}</span>
                </span>
                <p v-if="Object.keys(topSkills).length === 0" class="text-slate-500 italic">No skill data available.</p>
              </div>
            </div>
          </section>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
          <section
            class="lg:col-span-3 bg-white rounded-2xl border border-slate-100 shadow-xl shadow-slate-200/40 transition-all duration-300 hover:shadow-2xl hover:shadow-teal-100/50">
            <div class="p-6">
              <h2 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                Top In-Demand Roles
              </h2>
              <p class="text-sm text-slate-500 mt-1">Key roles based on current labor market analysis.</p>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-teal-50/50">
                  <tr>
                    <th scope="col"
                      class="px-6 py-3 text-left text-xs font-semibold text-teal-700 uppercase tracking-wider">Role & Sector</th>
                    <th scope="col"
                      class="px-6 py-3 text-left text-xs font-semibold text-teal-700 uppercase tracking-wider">Relevant Programs</th>
                    <th scope="col"
                      class="px-6 py-3 text-left text-xs font-semibold text-teal-700 uppercase tracking-wider">Key Skills</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                  <tr v-for="job in inDemandJobs" :key="job.role" class="hover:bg-teal-50/70 transition-colors">
                    <td class="px-6 py-4 align-top">
                      <p class="text-sm font-semibold text-slate-900">{{ job.role }}</p>
                      <p class="text-xs text-slate-500 mt-0.5">{{ job.sector }}</p>
                    </td>
                    <td class="px-6 py-4 align-top">
                      <div class="flex flex-wrap gap-1.5">
                        <span v-for="p in job.programs" :key="p"
                          class="inline-block bg-blue-100 text-blue-800 px-2 py-0.5 rounded-md text-xs font-medium border border-blue-200">
                          {{ p }}
                        </span>
                      </div>
                    </td>
                    <td class="px-6 py-4 align-top">
                      <div class="flex flex-wrap gap-1.5">
                        <span v-for="s in job.skills" :key="s"
                          class="inline-block bg-green-100 text-green-800 px-2 py-0.5 rounded-md text-xs font-medium border border-green-200">
                          {{ s }}
                        </span>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="inDemandJobs.length === 0">
                    <td colspan="3" class="text-center py-8 text-slate-500 italic">No in-demand roles data available.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <section
            class="lg:col-span-2 flex flex-col gap-6 bg-white rounded-2xl border border-slate-100 shadow-xl shadow-slate-200/40 transition-all duration-300 hover:shadow-2xl hover:shadow-orange-100/50 p-6">
            
            <div>
              <div class="pb-4 border-b border-slate-100">
                <h2 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                  Real-Time Job Openings
                </h2>
                <p class="text-sm text-slate-500 mt-1">Live count of job openings by title in GenSan.</p>
              </div>
              
              <div class="overflow-x-auto mt-4"> 
                <table class="min-w-full divide-y divide-slate-200">
                  <thead class="bg-orange-50/50 sticky top-0">
                    <tr>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-orange-700 uppercase tracking-wider">Job Title</th>
                      <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-orange-700 uppercase tracking-wider">Openings</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-slate-200">
                    <tr v-for="job in jobCounts" :key="job.job_title" class="hover:bg-orange-50/70 transition-colors">
                      <td class="px-6 py-4 text-sm text-slate-800 font-medium">{{ job.job_title }}</td>
                      <td class="px-6 py-4 text-sm text-orange-600 font-bold text-right">{{ job.openings }}</td>
                    </tr>
                    <tr v-if="jobCounts.length === 0">
                      <td colspan="2" class="text-center py-8 text-slate-500 italic">No current job openings data available.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            
            <div class="pt-4 border-t border-slate-200">
              <h2 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                Career Guidance Tips
              </h2>
              <p class="text-sm text-slate-500 mt-1">Quick advice from trusted career sources.</p>
              
              <ul class="mt-3 space-y-3">
    
    <li class="text-sm text-slate-700 bg-pink-50 p-3 rounded-lg border-l-4 border-pink-400 hover:bg-pink-100 transition-colors cursor-pointer">
        <span class="font-semibold text-pink-700 mr-2">Resume:</span> Highlight your ACHIEVEMENTS using action verbs and QUANTIFY results whenever possible to showcase your value.
    </li>
    
    <li class="text-sm text-slate-700 bg-pink-50 p-3 rounded-lg border-l-4 border-pink-400 hover:bg-pink-100 transition-colors cursor-pointer">
        <span class="font-semibold text-pink-700 mr-2">Interview:</span> Research the company's CULTURE and tailor your answers. Use the STAR method (Situation, Task, Action, Result) for structured responses.
    </li>
    
    <li class="text-sm text-slate-700 bg-pink-50 p-3 rounded-lg border-l-4 border-pink-400 hover:bg-pink-100 transition-colors cursor-pointer">
        <span class="font-semibold text-pink-700 mr-2">Skills:</span> The TESDA Online Program (TOP) offers FREE MOOCs (Massive Open Online Courses) including Job Readiness and 21st Century Skills.
    </li>
</ul>
              
              <a href="https://ph.jobstreet.com/career-advice/" target="_blank" class="mt-4 inline-block text-sm font-semibold text-pink-600 hover:text-pink-800 transition">View all Jobstreet Career Advice &rarr;</a>
            </div>

          </section>
        </div>
        
        <section class="mt-10">
          <h2 class="text-2xl font-bold text-slate-800 mb-5">External Learning & Program Resources</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <div class="bg-white p-6 rounded-xl border border-slate-100 shadow-md hover:shadow-lg transition-all duration-300">
              <h3 class="text-lg font-semibold text-teal-700">TESDA Online & Skills Programs</h3>
              <p class="mt-1 text-sm text-slate-500">Access **free** online technical and vocational courses to boost employability.</p>
              <a href="https://e-tesda.gov.ph/course/" target="_blank" class="mt-3 inline-block text-teal-600 font-medium text-sm hover:underline">Explore TESDA Online Courses &rarr;</a>
            </div>
            
            <div class="bg-white p-6 rounded-xl border border-slate-100 shadow-md hover:shadow-lg transition-all duration-300">
              <h3 class="text-lg font-semibold text-blue-700">Regional Labor Market Reports (SOCCSKSARGEN)</h3>
              <p class="mt-1 text-sm text-slate-500">Official statistics from **PSA/DOLE** on employment rates and in-demand sectors in Region XII.</p>
              <a href="https://rsso12.psa.gov.ph/content/2024-annual-labor-market-statistics-south-cotabato" target="_blank" class="mt-3 inline-block text-blue-600 font-medium text-sm hover:underline">View Local PSA Data &rarr;</a>
            </div>
            
            <div class="bg-white p-6 rounded-xl border border-slate-100 shadow-md hover:shadow-lg transition-all duration-300">
              <h3 class="text-lg font-semibold text-purple-700">DOLE Programs & Apprenticeship Guides</h3>
              <p class="mt-1 text-sm text-slate-500">Information on Government Internship Program (GIP) and DTS/Learnership programs for OJT.</p>
              <a href="https://www.tesda.gov.ph/about/tesda/38" target="_blank" class="mt-3 inline-block text-purple-600 font-medium text-sm hover:underline">Learn about TESDA's Enterprise Programs &rarr;</a>
            </div>
            
          </div>
        </section>

      </div>
    </div>

    <Modal :show="showRequestModal" @close="showRequestModal = false" :maxWidth="'2xl'">
      <div class="p-6 bg-white rounded-xl shadow-2xl">
        <h3 class="text-2xl font-bold text-slate-800 border-b pb-3 mb-6">Request a Seminar</h3>
        <form @submit.prevent="submitRequest" class="space-y-6" enctype="multipart/form-data">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Request For</label>
              <select v-model="form.request_for" required
                class="w-full border-slate-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out">
                <option value="seminar">Seminar</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Event Name</label>
              <input v-model="form.event_name" placeholder="e.g., Annual Career Fair" required
                class="w-full border-slate-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out" />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-1">Scheduled Date & Time</label>
              <input type="datetime-local" v-model="form.scheduled_at" required
                class="w-full border-slate-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out" />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
              <textarea v-model="form.description" placeholder="Provide a brief description of the event..." rows="3"
                class="w-full border-slate-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out"></textarea>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-1">Attach Letter (PDF, JPG, PNG)</label>
              <input type="file" @change="handleFileUpload" accept=".pdf,.jpeg,.jpg,.png"
                class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" />
            </div>
          </div>
          
          <div class="pt-4 border-t border-slate-200">
            <label class="block text-lg font-semibold text-slate-800 mb-3">Target Attendees</label>
            <div class="space-y-3">
              <div v-for="(att, i) in attendanceForm.attendees" :key="i" class="grid grid-cols-3 gap-3">
                <input v-model="att.attendee_name" placeholder="Name" required
                  class="w-full border-slate-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500" />
                <select v-model="att.gender" required
                  class="w-full border-slate-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                  <option value="" disabled>Gender</option>
                  <option>Male</option>
                  <option>Female</option>
                  <option>Other</option>
                </select>
                <input v-model="att.age" type="number" min="0" max="120" placeholder="Age" required
                  class="w-full border-slate-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500" />
              </div>
            </div>
            <button type="button" @click="addAttendanceRow"
              class="mt-3 text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition duration-150">+ Add Attendee</button>
          </div>

          <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
            <button type="button" @click="showRequestModal = false"
              class="px-5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 transition shadow-sm font-medium">Cancel</button>
            <button type="submit"
              class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-500/30 transition font-semibold">Send
              Request</button>
          </div>
        </form>
      </div>
    </Modal>

    <Modal :show="isSuccessModalOpen" @close="handleSuccessClose" :maxWidth="'sm'">
      <div class="p-8 bg-white rounded-xl text-center shadow-2xl">
        <div class="text-5xl mb-4 text-green-500">🎉</div>
        <h3 class="text-2xl font-bold text-green-700">Request Sent Successfully!</h3>
        <p class="mt-2 text-slate-600">Your seminar request has been submitted for review.</p>
        <button @click="handleSuccessClose"
          class="mt-6 px-6 py-2.5 rounded-xl bg-green-600 text-white font-semibold shadow-md hover:bg-green-700 transition">
          Close
        </button>
      </div>
    </Modal>


    <Modal :show="showDetailsModal" @close="showDetailsModal = false" :maxWidth="'lg'">
      <div class="p-6 bg-white rounded-xl shadow-2xl">
        <h3 class="text-2xl font-bold text-slate-800 border-b pb-3 mb-5">Seminar Request Details</h3>
        <dl class="space-y-5 text-left">
          <div class="p-3 bg-slate-50 rounded-lg">
            <dt class="text-sm font-medium text-slate-500">Event Name</dt>
            <dd class="mt-1 text-base font-semibold text-slate-900">{{ details.event_name }}</dd>
          </div>
          <div class="p-3 bg-slate-50 rounded-lg">
            <dt class="text-sm font-medium text-slate-500">Scheduled Date & Time</dt>
            <dd class="mt-1 text-base text-slate-900">{{ details.scheduled_at ? new Date(details.scheduled_at).toLocaleString() : 'N/A' }}</dd>
          </div>
          <div class="p-3 bg-slate-50 rounded-lg">
            <dt class="text-sm font-medium text-slate-500">Description</dt>
            <dd class="mt-1 text-base text-slate-900 whitespace-pre-wrap">{{ details.description || 'No description provided.' }}</dd>
          </div>
          <div v-if="details.attachment" class="p-3 bg-slate-50 rounded-lg">
            <dt class="text-sm font-medium text-slate-500">Attachment</dt>
            <dd class="mt-1"><a :href="`/storage/${details.attachment}`" target="_blank"
                class="text-indigo-600 underline font-medium hover:text-indigo-800 transition">View Attachment</a></dd>
          </div>
          <div class="p-3 bg-indigo-50 rounded-lg">
            <dt class="text-sm font-medium text-indigo-500">Total Attendees (Confirmed)</dt>
            <dd class="mt-1 text-xl font-bold text-indigo-800">{{ details.attendanceCount ?? 0 }}</dd>
          </div>
        </dl>
        <div class="flex justify-end pt-6 border-t border-slate-100 mt-5">
          <button @click="showDetailsModal = false"
            class="px-5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 transition shadow-sm font-medium">Close</button>
        </div>
      </div>
    </Modal>

    <Modal :show="showAttendanceModal" @close="showAttendanceModal = false" :maxWidth="'lg'">
      <div class="p-6 bg-white rounded-xl shadow-2xl">
        <h3 class="text-2xl font-bold text-slate-800 border-b pb-3 mb-6">Seminar Attendance</h3>

        <form @submit.prevent="submitAttendance" class="space-y-4 pb-4 border-b border-slate-200">
          <div>
            <h4 class="text-lg font-semibold text-slate-700">Add New Attendees</h4>
            <div class="mt-3 space-y-3">
              <div v-for="(att, i) in attendanceForm.attendees" :key="i" class="grid grid-cols-3 gap-3">
                <input v-model="att.attendee_name" placeholder="Name" required
                  class="w-full border-slate-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500" />
                <select v-model="att.gender" required
                  class="w-full border-slate-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                  <option value="" disabled>Gender</option>
                  <option>Male</option>
                  <option>Female</option>
                  <option>Other</option>
                </select>
                <input v-model="att.age" type="number" min="0" max="120" placeholder="Age" required
                  class="w-full border-slate-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500" />
              </div>
            </div>
            <button type="button" @click="addAttendanceRow"
              class="mt-3 text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition duration-150">+ Add Attendee</button>
          </div>
          <div class="flex justify-end gap-3 pt-3">
            <button type="button" @click="showAttendanceModal = false"
              class="px-5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 transition shadow-sm font-medium">Cancel</button>
            <button type="submit"
              class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-500/30 transition font-semibold">Save
              Attendance</button>
          </div>
        </form>

        <div v-if="attendanceList.length" class="mt-4 pt-4">
          <h4 class="font-semibold text-lg text-slate-800 mb-3">Current Attendance List ({{ attendanceList.length }})</h4>
          <div class="bg-slate-50 p-4 rounded-lg max-h-60 overflow-y-auto border border-slate-200">
            <ul class="space-y-2 text-sm text-slate-700">
              <li v-for="a in attendanceList" :key="a.id" class="flex justify-between items-center border-b border-slate-200 pb-1 last:border-b-0">
                <span class="font-medium">{{ a.attendee_name }}</span>
                <span class="text-xs text-slate-500">{{ a.gender }}, Age {{ a.age }}</span>
              </li>
            </ul>
          </div>
        </div>
        <div v-else class="text-center py-6 text-slate-500 italic">No previous attendance records found.</div>

      </div>
    </Modal>

  </AppLayout>
</template>






