<script setup>
import Container from '@/Components/Container.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3'; 
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  job: Object,
  user: Object 
});

// Process job skills array
const jobSkills = Array.isArray(props.job.skills)
  ? props.job.skills
  : typeof props.job.skills === 'string'
    ? props.job.skills.replace(/[\[\]"]+/g, '').split(',').map(s => s.trim()).filter(s => s)
    : [];

// Function to invite matched graduates
const inviteMatchedGraduates = (jobId) => {
  if (confirm("Invite all matched graduates for this job?")) {
    router.post(route('company.jobs.auto-invite', jobId), {}, {
      onSuccess: () => alert("Graduates invited successfully!"),
      onError: () => alert("Something went wrong."),
    });
  }
};

// Function to go back to previous page
const goBack = () => {
  router.visit(window.history.length > 1 ? -1 : route('admin.manage_users'));
};

// Function to get status class based on job approval status
const getStatusClass = (status) => {
  if (status === 1) return 'bg-green-100 text-green-800';
  if (status === 0) return 'bg-red-100 text-red-800';
  return 'bg-yellow-100 text-yellow-800';
};

// Function to get status text
const getStatusText = (status) => {
  if (status === 1) return 'Approved';
  if (status === 0) return 'Disapproved';
  return 'Pending';
};
</script>

<template>
  <AppLayout :title="job.job_title">
    <template #header>
      <div class="flex items-center">
        <button @click="goBack" class="mr-3 text-blue-500 hover:text-blue-700 focus:outline-none" aria-label="Go back">
          <i class="fas fa-chevron-left text-xl"></i>
        </button>
        <i class="fas fa-folder-plus text-blue-500 text-xl mr-2"></i>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Job Details
        </h2>
      </div>
    </template>
    
    <Container class="py-8">
      <!-- Job Header Card -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6 p-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between">
          <div class="flex-1 min-w-0 mb-4 md:mb-0 md:mr-4">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ job.job_title }}</h1>
            <div class="flex flex-wrap items-center text-sm text-gray-600 mb-3">
              <div class="flex items-center mr-4 mb-2">
                <i class="fas fa-map-marker-alt text-gray-500 mr-1"></i>
                <span>{{ job.location || 'Remote' }}</span>
              </div>
              <div class="flex items-center mr-4 mb-2">
                <i class="fas fa-calendar-alt text-gray-500 mr-1"></i>
                <span>Posted: {{ job.posted_at }}</span>
              </div>
              <div class="flex items-center mb-2">
                <i class="fas fa-clock text-gray-500 mr-1"></i>
                <span>Deadline: {{ job.expiration_date }}</span>
              </div>
            </div>
            <div class="flex flex-wrap gap-2">
              <span class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded-full">
                {{ job.job_type }}
              </span>
              <span class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded-full">
                {{ job.experience_level }}
              </span>
              <span :class="[getStatusClass(job.is_approved), 'px-3 py-1 text-sm rounded-full']">
                {{ getStatusText(job.is_approved) }}
              </span>
            </div>
          </div>
          
          <!-- Company Info -->
          <div class="flex flex-col items-center">
            <img 
              :src="job.company.profile_photo || '/images/company-placeholder.png'" 
              alt="Company Logo" 
              class="w-20 h-20 rounded-lg border border-gray-300 shadow-sm mb-2 object-cover"
            >
            <h3 class="text-md font-semibold">{{ job.company.name }}</h3>
          </div>
        </div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Sidebar -->
        <div class="col-span-1 space-y-6">
          <!-- Job Info Card -->
          <div class="bg-white rounded-lg border border-gray-200 overflow-hidden p-5 space-y-4">
            <h3 class="text-lg font-semibold border-b border-gray-200 pb-2">Job Information</h3>
            
            <div class="space-y-3">
              <div class="flex items-start">
                <div class="bg-blue-100 p-2 rounded-md mr-3">
                  <i class="fas fa-money-bill-wave text-blue-600"></i>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Salary Range</p>
                  <p class="font-medium">₱ {{ job.salary_range }}</p>
                </div>
              </div>
              
              <div class="flex items-start">
                <div class="bg-purple-100 p-2 rounded-md mr-3">
                  <i class="fas fa-user text-purple-600"></i>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Posted By</p>
                  <p class="font-medium">{{ job.posted_by }}</p>
                </div>
              </div>
              
              <div class="flex items-start">
                <div class="bg-green-100 p-2 rounded-md mr-3">
                  <i class="fas fa-users text-green-600"></i>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Vacancies</p>
                  <p class="font-medium">{{ job.vacancy }}</p>
                </div>
              </div>
            </div>
            
            <!-- CTA Buttons -->
            <div v-if="job.is_approved === 1" class="pt-2">
              <button 
                class="w-full bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700 transition flex items-center justify-center"
                @click="inviteMatchedGraduates(job.id)">
                <i class="fas fa-user-plus mr-2"></i>
                Invite Matched Graduates
              </button>
            </div>
          </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-span-2 space-y-6">
          <!-- Job Description Card -->
          <div class="bg-white rounded-lg border border-gray-200 overflow-hidden p-5">
            <h3 class="text-lg font-semibold border-b border-gray-200 pb-2 mb-4">Job Description</h3>
            <p class="text-gray-700 whitespace-pre-line">{{ job.description }}</p>
          </div>
          
          <!-- Job Requirements Card -->
          <div class="bg-white rounded-lg border border-gray-200 overflow-hidden p-5">
            <h3 class="text-lg font-semibold border-b border-gray-200 pb-2 mb-4">Job Requirements</h3>
            <p class="text-gray-700 whitespace-pre-line">{{ job.requirements }}</p>
          </div>
          
          <!-- Skills Card -->
          <div v-if="jobSkills.length > 0" class="bg-white rounded-lg border border-gray-200 overflow-hidden p-5">
            <h3 class="text-lg font-semibold border-b border-gray-200 pb-2 mb-4">Skills Required</h3>
            <div class="flex flex-wrap gap-2">
              <span 
                v-for="(skill, index) in jobSkills" 
                :key="index" 
                class="px-3 py-1 text-sm bg-gray-100 text-gray-800 rounded-full hover:bg-gray-200 transition">
                {{ skill }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </Container>
  </AppLayout>
</template>
  