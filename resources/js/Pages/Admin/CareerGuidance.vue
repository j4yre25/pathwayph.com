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
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <!-- Header Section with Back Button -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
          <a href="javascript:history.back()" class="mr-4 text-gray-500 hover:text-gray-700 transition-colors">
            <i class="fas fa-chevron-left"></i>
          </a>
          <i class="fas fa-compass text-blue-500 text-xl mr-2"></i>
          <h1 class="text-2xl font-bold text-gray-800">Career Guidance & Counseling</h1>
        </div>
      </div>
      <p class="text-sm text-gray-500 mb-6">Explore career resources, in-demand roles, and get personalized guidance.</p>

      <!-- Career Counseling Tips -->
      <section class="mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden p-6">
          <div class="flex items-center mb-4">
            <div class="rounded-full bg-blue-100 p-2 mr-3">
              <i class="fas fa-lightbulb text-blue-500"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-800">Career Counseling Tips</h2>
          </div>
          <ul class="space-y-3 text-gray-700">
            <li v-for="(tip, idx) in counselingTips" :key="idx" class="flex items-start">
              <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
              <span>{{ tip }}</span>
            </li>
          </ul>
        </div>
      </section>

      <!-- Top In-Demand Roles -->
      <section class="mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
              <div class="rounded-full bg-blue-100 p-2 mr-3">
                <i class="fas fa-briefcase text-blue-500"></i>
              </div>
              <h2 class="text-xl font-bold text-gray-800">Top In-Demand Roles</h2>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700">
              <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-600 font-medium">
                <tr>
                  <th class="px-6 py-4 font-semibold">Role</th>
                  <th class="px-6 py-4 font-semibold">Sector</th>
                  <th class="px-6 py-4 font-semibold">Category</th>
                  <th class="px-6 py-4 font-semibold">Programs</th>
                  <th class="px-6 py-4 font-semibold">Key Skills</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="job in inDemandJobs" :key="job.role" class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4 font-medium">{{ job.role }}</td>
                  <td class="px-6 py-4">{{ job.sector }}</td>
                  <td class="px-6 py-4">{{ job.category }}</td>
                  <td class="px-6 py-4">
                    <div class="flex flex-wrap gap-1">
                      <span v-for="p in job.programs" :key="p" class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                        {{ p }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex flex-wrap gap-1">
                      <span v-for="s in job.skills" :key="s" class="inline-block bg-green-100 text-green-800 px-2 py-1 rounded text-xs">
                        {{ s }}
                      </span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- Top In-Demand Skills -->
      <section class="mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden p-6">
          <div class="flex items-center mb-4">
            <div class="rounded-full bg-blue-100 p-2 mr-3">
              <i class="fas fa-chart-pie text-blue-500"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-800">Top In-Demand Skills</h2>
          </div>
          <div class="flex flex-wrap gap-2 mt-2">
            <span v-for="(count, skill) in topSkills" :key="skill" class="bg-gray-100 text-gray-800 px-3 py-1.5 rounded-lg text-sm font-medium">
              {{ skill }} <span class="font-bold text-blue-600">({{ count }})</span>
            </span>
          </div>
        </div>
      </section>

      <!-- Helpful Resources -->
      <section class="mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden p-6">
          <div class="flex items-center mb-4">
            <div class="rounded-full bg-blue-100 p-2 mr-3">
              <i class="fas fa-link text-blue-500"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-800">Helpful Resources</h2>
          </div>
          <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2">
            <div v-for="res in resources" :key="res.url" class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
              <a :href="res.url" target="_blank" class="text-blue-600 font-medium hover:text-blue-800 transition-colors">
                {{ res.title }}
              </a>
              <p class="text-gray-600 text-sm mt-1">{{ res.description }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Ask a Career Counselor -->
      <section class="mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden p-6">
          <div class="flex flex-col items-center text-center">
            <div class="rounded-full bg-blue-100 p-3 mb-3">
              <i class="fas fa-comments text-blue-500 text-xl"></i>
            </div>
            <h2 class="text-xl font-bold text-gray-800 mb-2">Need Personalized Advice?</h2>
            <p class="text-gray-600 mb-4 max-w-md">Connect with a career counselor for personalized guidance on your career path.</p>
            <button
              @click="showCounselorModal = true"
              class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring focus:ring-blue-300 transition"
            >
              <i class="fas fa-user-tie mr-2"></i> Ask a Career Counselor
            </button>
          </div>
          <Modal v-model="showCounselorModal">
            <template #header>
              <div class="flex items-center">
                <i class="fas fa-user-tie text-blue-500 mr-2"></i>
                <span>Ask a Career Counselor</span>
              </div>
            </template>
            <template #body>
              <textarea 
                v-model="counselorMessage" 
                rows="4" 
                class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" 
                placeholder="Type your career question here..."
              ></textarea>
            </template>
            <template #footer>
              <button 
                @click="submitCounselorMessage" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring focus:ring-blue-300 transition"
              >
                <i class="fas fa-paper-plane mr-2"></i> Send
              </button>
            </template>
          </Modal>
        </div>
      </section>

      <!-- Real-Time Labor Market Data -->
      <section class="mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
              <div class="rounded-full bg-blue-100 p-2 mr-3">
                <i class="fas fa-chart-bar text-blue-500"></i>
              </div>
              <h2 class="text-xl font-bold text-gray-800">Real-Time Job Openings by Role</h2>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700">
              <thead class="bg-gray-50 text-xs uppercase tracking-wider text-gray-600 font-medium">
                <tr>
                  <th class="px-6 py-4 font-semibold">Job Title</th>
                  <th class="px-6 py-4 font-semibold">Openings</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="job in jobCounts" :key="job.job_title" class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4 font-medium">{{ job.job_title }}</td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      {{ job.openings }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>
  </AppLayout>
</template>