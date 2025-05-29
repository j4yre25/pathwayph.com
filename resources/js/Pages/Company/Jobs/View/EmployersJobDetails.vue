<script setup>
import Container from '@/Components/Container.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3'; 
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
  job: Object,
  user: Object 
});

const jobSkills = Array.isArray(props.job.skills)
  ? props.job.skills
  : typeof props.job.skills === 'string'
    ? props.job.skills.replace(/[\[\]"]+/g, '').split(',').map(s => s.trim()).filter(s => s)
    : [];

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
  window.history.back();
};

// Function to get status class based on job status
const getStatusClass = (status) => {
  if (status === 1) return 'bg-green-100 text-green-800';
  if (status === 0) return 'bg-red-100 text-red-800';
  if (status === null) return 'bg-yellow-100 text-yellow-800';
  return 'bg-gray-100 text-gray-800';
};

// Function to get status text
const getStatusText = (status) => {
  if (status === 1) return 'Approved';
  if (status === 0) return 'Disapproved';
  if (status === null) return 'Pending';
  return 'Unknown';
};
</script>


<template>
  <AppLayout :title="job.job_title">
    <template #header>
      <div class="flex items-center">
        <button @click="goBack" class="mr-3 text-gray-600 hover:text-blue-600 focus:outline-none" aria-label="Go back">
          <i class="fas fa-chevron-left text-xl"></i>
        </button>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
          <i class="fas fa-briefcase text-blue-500 mr-2"></i>
          Job Details
        </h2>
      </div>
    </template>

    <Container class="py-8">
      <!-- Job Header Card -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6 p-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
          <div class="flex items-center mb-4 md:mb-0">
            <img 
              :src="job.company.profile_photo || '/images/company-placeholder.png'" 
              alt="Company Logo" 
              class="w-16 h-16 rounded-lg border border-gray-200 shadow-sm object-cover mr-4"
            >
            <div>
              <h1 class="text-2xl font-bold text-gray-800">{{ job.job_title }}</h1>
              <p class="text-gray-600 mt-1">{{ job.company.name }}</p>
            </div>
          </div>
          <div class="flex flex-col items-end">
            <span :class="[getStatusClass(job.is_approved), 'text-xs font-medium px-2.5 py-0.5 rounded-full whitespace-nowrap mb-2']">
              {{ getStatusText(job.is_approved) }}
            </span>
            <p class="text-sm text-gray-500">Posted: {{ job.posted_at }}</p>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Sidebar -->
        <div class="col-span-1 order-2 md:order-1">
          <!-- Job Info Card -->
          <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6">
            <h3 class="text-lg font-semibold p-4 border-b border-gray-200 flex items-center">
              <i class="fas fa-info-circle text-blue-500 mr-2"></i>
              Job Information
            </h3>
            <div class="p-4 space-y-3">
              <div class="flex items-start">
                <i class="fas fa-money-bill-wave text-green-500 mt-1 mr-3 w-5 text-center"></i>
                <div>
                  <p class="text-sm text-gray-500">Salary</p>
                  <p class="font-medium">{{ job.salary_range || 'Not specified' }}</p>
                </div>
              </div>
              
              <div class="flex items-start">
                <i class="fas fa-map-marker-alt text-red-500 mt-1 mr-3 w-5 text-center"></i>
                <div>
                  <p class="text-sm text-gray-500">Location</p>
                  <p class="font-medium">{{ job.job_location || 'Remote' }}</p>
                </div>
              </div>
              
              <div class="flex items-start">
                <i class="fas fa-calendar-alt text-blue-500 mt-1 mr-3 w-5 text-center"></i>
                <div>
                  <p class="text-sm text-gray-500">Application Deadline</p>
                  <p class="font-medium">{{ job.job_deadline || 'Not specified' }}</p>
                </div>
              </div>
              
              <div class="flex items-start">
                <i class="fas fa-users text-purple-500 mt-1 mr-3 w-5 text-center"></i>
                <div>
                  <p class="text-sm text-gray-500">Vacancies</p>
                  <p class="font-medium">{{ job.job_vacancies || '1' }}</p>
                </div>
              </div>
              
              <div class="flex items-start">
                <i class="fas fa-user text-orange-500 mt-1 mr-3 w-5 text-center"></i>
                <div>
                  <p class="text-sm text-gray-500">Posted By</p>
                  <p class="font-medium">{{ job.posted_by || 'Admin' }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- CTA Buttons -->
          <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6" v-if="job.is_approved === 1">
            <h3 class="text-lg font-semibold p-4 border-b border-gray-200 flex items-center">
              <i class="fas fa-tasks text-blue-500 mr-2"></i>
              Actions
            </h3>
            <div class="p-4">
              <button 
                class="w-full bg-blue-600 text-white py-3 px-4 rounded-md hover:bg-blue-700 transition flex items-center justify-center font-medium"
                @click="inviteMatchedGraduates(job.id)">
                <i class="fas fa-user-plus mr-2"></i>
                Invite Matched Graduates
              </button>
            </div>
          </div>
        </div>

        <!-- Main Content -->
        <div class="col-span-2 order-1 md:order-2">
          <!-- Job Details Card -->
          <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6">
            <h3 class="text-lg font-semibold p-4 border-b border-gray-200 flex items-center">
              <i class="fas fa-briefcase text-blue-500 mr-2"></i>
              Job Details
            </h3>
            <div class="p-6">
              <!-- Job Type and Experience Level -->
              <div class="flex flex-wrap gap-2 mb-6">
                <span class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded-full flex items-center">
                  <i class="fas fa-business-time mr-1"></i>
                  {{ job.job_employment_type || 'Full-time' }}
                </span>
                <span class="px-3 py-1 text-sm bg-purple-100 text-purple-800 rounded-full flex items-center">
                  <i class="fas fa-chart-line mr-1"></i>
                  {{ job.job_experience_level || 'Entry Level' }}
                </span>
              </div>
              
              <!-- Job Description -->
              <div class="mb-6">
                <h4 class="text-lg font-semibold mb-3 flex items-center">
                  <i class="fas fa-align-left text-blue-500 mr-2"></i>
                  Job Description
                </h4>
                <div class="text-gray-700 prose max-w-none" v-html="job.job_description"></div>
              </div>
              
              <!-- Job Requirements -->
              <div class="mb-6">
                <h4 class="text-lg font-semibold mb-3 flex items-center">
                  <i class="fas fa-clipboard-list text-blue-500 mr-2"></i>
                  Job Requirements
                </h4>
                <div class="text-gray-700 prose max-w-none" v-html="job.job_requirements"></div>
              </div>
              
              <!-- Skills Tags -->
              <div v-if="jobSkills && jobSkills.length > 0">
                <h4 class="text-lg font-semibold mb-3 flex items-center">
                  <i class="fas fa-tags text-blue-500 mr-2"></i>
                  Required Skills
                </h4>
                <div class="flex flex-wrap gap-2">
                  <span 
                    v-for="(skill, index) in jobSkills" 
                    :key="index" 
                    class="px-3 py-1 text-sm bg-gray-100 text-gray-800 rounded-full flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-1"></i>
                    {{ skill }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Container>
  </AppLayout>
</template>
  