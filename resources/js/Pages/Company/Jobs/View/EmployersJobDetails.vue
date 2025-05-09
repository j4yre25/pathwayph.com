<script setup>
import Container from '@/Components/Container.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3'; 

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

</script>


<template>
    <AppLayout :title="job.job_title">
      <template #header>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Job Details</h2>
      </template>
      <Container>
        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-6">
          
          <!-- Sidebar -->
          <div class="col-span-1 bg-white rounded shadow p-4 space-y-4">
            <!-- Company Avatar & Name -->
            <div class="text-center">
            <img 
                :src="job.company.profile_photo || '/images/company-placeholder.png'" 
                alt="Company Logo" 
                class="w-45 h-45 mx-auto rounded-lg border border-gray-300 shadow-sm mb-2 object-cover"
            >
            <h1 class="text-lg font-semibold">@{{ job.company.name }}</h1>
            </div>
  
            <!-- Job Info -->
            <div class="text-md gap-4 space-y-1">
              <p><strong>Offered Salary:</strong> ₱ {{ job.salary_range }}</p>
              <p><strong>Posted By:</strong>  {{ job.posted_by}}</p>
              <p><strong>Posted Date:</strong> {{ job.posted_at }}</p>
              <p><strong>Application Deadline:</strong> {{ job.expiration_date }}</p>
              <p><strong>Location:</strong> {{ job.location }}</p>
              <p><strong>No. Vacancy:</strong> {{ job.vacancy }}</p>
              <p><strong>Status:</strong> 
                <span :class="{
                  'text-green-600': job.is_approved === 1,
                  'text-red-600': job.is_approved === 0,
                  'text-yellow-600': job.is_approved === null
                }">
                    {{ job.is_approved === 1 ? 'Approved' : job.is_approved === 0 ? 'Disapproved' : 'Pending' }}
                </span>
              </p>
            </div>
  
            <!-- CTA Buttons -->
            <div class="space-y-2" v-if = "job.is_approved === 1">
              <div class="flex flex-col space-y-2">
                <button 
                  class="w-full bg-purple-600 text-white py-2 rounded hover:bg-purple-700"
                  @click="inviteMatchedGraduates(job.id)">
                    Invite Match Graduates
                </button>
              </div>
            </div>
          </div>
  
          <!-- Main Content -->
          <div class="col-span-2 bg-white rounded shadow p-6 space-y-6">
            <h1 class="text-2xl font-bold">{{ job.job_title }}</h1>
            <div class="flex flex-wrap gap-2 mt-4">
                <span class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded-full">
                    {{ job.job_type }}
                </span>
                <span class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded-full">
                    {{ job.experience_level }}
                </span>
            </div>
            
            <section>
              <h3 class="text-lg font-semibold mb-1">Job Description</h3>
              <p class="text-gray-700">{{ job.description }}</p>
            </section>
  
            <section>
              <h3 class="text-lg font-semibold mb-1">Job Requirements</h3>
              <p class="text-gray-700">{{ job.requirements }}</p>
            </section>
  
            <!-- <section>
              <h3 class="text-lg font-semibold mb-1">What We Can Offer You</h3>
              <p class="text-gray-700">
                
              </p>
            </section> -->
  
            <!-- Optional Tags Section -->
            <div class="flex flex-wrap gap-2 mt-4">
                <span v-for="(skill, index) in jobSkills" :key="index" class="px-3 py-1 text-sm bg-gray-200 rounded-full">
                {{ skill }}
                </span>
            </div>
          </div>
        </div>
      </Container>
    </AppLayout>
  </template>
  