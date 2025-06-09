<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Modal from '@/Components/Modal.vue';

const { props } = usePage();
const counselingTips = computed(() => props.counselingTips || []);
const inDemandJobs = computed(() => props.inDemandJobs || []);
const topSkills = computed(() => props.topSkills || {});
const jobCounts = computed(() => props.jobCounts || []);
const resources = computed(() => props.resources || []);

const showCounselorModal = ref(false);
const counselorMessage = ref('');
function submitCounselorMessage() {
  showCounselorModal.value = false;
  counselorMessage.value = '';
  alert('Your message has been sent to a career counselor!');
}
</script>

<template>
  <AppLayout title="Career Guidance">
    <div class="max-w-6xl mx-auto py-12 px-4 sm:px-8">
      <h1 class="text-4xl font-extrabold mb-10 text-center text-indigo-800 tracking-tight">
        <span class="inline-flex items-center gap-2">
          <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 14v7m0 0H5a2 2 0 01-2-2v-5a2 2 0 012-2h7zm0 0h7a2 2 0 002-2v-5a2 2 0 00-2-2h-7z"/></svg>
          Career Guidance & Counseling
        </span>
      </h1>

      <!-- Career Counseling Tips -->
      <section class="mb-12">
        <div class="bg-white rounded-xl shadow p-8">
          <h2 class="text-2xl font-semibold mb-4 text-indigo-700 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M12 4v16m0 0H3"></path></svg>
            Career Counseling Tips
          </h2>
          <ul class="list-disc pl-8 space-y-2 text-gray-700">
            <li v-for="(tip, idx) in counselingTips" :key="idx">
              {{ tip }}
            </li>
          </ul>
        </div>
      </section>

      <!-- Top In-Demand Roles -->
      <section class="mb-12">
        <div class="bg-white rounded-xl shadow p-8">
          <h2 class="text-2xl font-semibold mb-4 text-indigo-700 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 17v-6a9 9 0 0118 0v6"></path><path d="M21 17a2 2 0 01-2 2H5a2 2 0 01-2-2"></path></svg>
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
                    <span v-for="p in job.programs" :key="p" class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded mr-1 mb-1 text-xs">
                      {{ p }}
                    </span>
                  </td>
                  <td class="py-2 px-4 border-b">
                    <span v-for="s in job.skills" :key="s" class="inline-block bg-green-100 text-green-800 px-2 py-1 rounded mr-1 mb-1 text-xs">
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
            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4l3 3"></path></svg>
            Top In-Demand Skills
          </h2>
          <div class="flex flex-wrap gap-3 mt-2">
            <span v-for="(count, skill) in topSkills" :key="skill" class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full text-base font-medium shadow">
              {{ skill }} <span class="font-bold">({{ count }})</span>
            </span>
          </div>
        </div>
      </section>

      <!-- Helpful Resources -->
      <section class="mb-12">
        <div class="bg-white rounded-xl shadow p-8">
          <h2 class="text-2xl font-semibold mb-4 text-indigo-700 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9"></path><path d="M12 4v16m0 0H3"></path></svg>
            Helpful Resources
          </h2>
          <ul class="list-disc pl-8 space-y-2">
            <li v-for="res in resources" :key="res.url">
              <a :href="res.url" target="_blank" class="text-blue-700 underline font-semibold hover:text-blue-900 transition">{{ res.title }}</a>
              <span class="text-gray-600 ml-2">{{ res.description }}</span>
            </li>
          </ul>
        </div>
      </section>

      <!-- Ask a Career Counselor -->
      <section class="mb-12">
        <div class="bg-white rounded-xl shadow p-8 flex flex-col items-center">
          <h2 class="text-2xl font-semibold mb-4 text-indigo-700 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8s-9-3.582-9-8a9 9 0 1118 0z"></path></svg>
            Need Personalized Advice?
          </h2>
          <button
            @click="showCounselorModal = true"
            class="bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition"
          >
            Ask a Career Counselor
          </button>
          <Modal v-model="showCounselorModal">
            <template #header>Ask a Career Counselor</template>
            <template #body>
              <textarea v-model="counselorMessage" rows="4" class="w-full border rounded p-2" placeholder="Type your question..."></textarea>
            </template>
            <template #footer>
              <button @click="submitCounselorMessage" class="bg-green-600 text-white px-4 py-2 rounded">Send</button>
            </template>
          </Modal>
        </div>
      </section>

      <!-- Real-Time Labor Market Data -->
      <section class="mb-12">
        <div class="bg-white rounded-xl shadow p-8">
          <h2 class="text-2xl font-semibold mb-4 text-indigo-700 flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 17v-6a9 9 0 0118 0v6"></path><path d="M21 17a2 2 0 01-2 2H5a2 2 0 01-2-2"></path></svg>
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