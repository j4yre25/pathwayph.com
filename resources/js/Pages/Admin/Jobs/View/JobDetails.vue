<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import '@fortawesome/fontawesome-free/css/all.css';
import html2canvas from "html2canvas";
import { jsPDF } from "jspdf";

const props = defineProps({
    job: Object,
});

const goBack = () => window.history.back();

const formatDate = (dateString) => {
    if (!dateString) return 'Not specified';
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
};

const bubbleColors = [
    'bg-blue-100 text-blue-800',
    'bg-green-100 text-green-800',
    'bg-yellow-100 text-yellow-800',
    'bg-purple-100 text-purple-800',
    'bg-pink-100 text-pink-800',
    'bg-indigo-100 text-indigo-800',
    'bg-red-100 text-red-800',
    'bg-teal-100 text-teal-800',
];

const downloading = ref(false);
function downloadInfographic() {
    downloading.value = true;
    const element = document.getElementById('job-infographic-template');
    element.style.display = 'block';

    // Wait for the browser to render the element
    setTimeout(() => {
        html2canvas(element, { scale: 2, useCORS: true }).then(canvas => {
            element.style.display = 'none';
            const link = document.createElement('a');
            link.download = `job-infographic-${props.job.id}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
            downloading.value = false;
        });
    }, 100); // 100ms delay is usually enough
}

</script>



<template>
    <AppLayout title="Job Details">
        <template #header>
            <div class="flex items-center">
                <button @click="goBack"
                    class="flex items-center text-blue-600 hover:text-blue-800 transition-colors mr-4">
                    <i class="fas fa-chevron-left mr-2"></i>
                </button>
                <div>
                    <div class="flex items-center">
                        <i class="fas fa-briefcase text-blue-500 mr-2 text-2xl"></i>
                        <h1 class="text-2xl font-bold text-gray-800">Job Details</h1>
                    </div>
                    <p class="text-gray-500 text-sm mt-1">View full job information and requirements</p>
                </div>
            </div>
        </template>

        <Container class="py-10">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">{{ job.job_title }}</h2>
                        <div class="flex flex-wrap items-center mt-2 text-sm text-gray-600">
                            <div v-if="job.company" class="flex items-center mr-4 mb-2 md:mb-0">
                                <i class="fas fa-building text-blue-500 mr-1"></i>
                                <span>{{ job.company.name }}</span>
                            </div>
                            <div class="flex items-center mr-4 mb-2 md:mb-0">
                                <i class="fas fa-calendar-alt text-green-500 mr-1"></i>
                                <span>Posted on {{ formatDate(job.posted_at) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ job.status ? job.status.charAt(0).toUpperCase() + job.status.slice(1) : 'Unknown' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-white-200 mb-6 overflow-hidden p-6">
                <div class="flex items-center justify-between border-b border-gray-200 overflow-x-auto mb-6">
                    <div class="flex">
                        <h2 class="px-4 py-3 text-lg font-bold flex items-center whitespace-nowrap text-black">
                            <i class="fas fa-clipboard-list mr-2 text-blue-600"></i>
                            Job Details
                        </h2>
                    </div>
                </div>

                <div class="flex flex-col lg:flex-row gap-6">
                    <div class="lg:flex-1 space-y-6">
                        <div class="bg-white rounded-lg shadow-sm border border-white-200 p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div class="bg-white p-4 rounded-lg border border-white-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">Job Type</h4>
                                    <div class="flex items-center">
                                        <i class="fas fa-business-time text-blue-500 mr-2"></i>
                                        <span class="text-gray-800 font-medium">
                                            <template v-if="Array.isArray(job.job_type)">
                                                {{ job.job_type.length ? job.job_type.join(', ') : 'Not specified' }}
                                            </template>
                                            <template v-else>
                                                {{ job.job_type || 'Not specified' }}
                                            </template>
                                        </span>
                                    </div>
                                </div>
                                <div class="bg-white p-4 rounded-lg border border-white-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">Experience Level</h4>
                                    <div class="flex items-center">
                                        <i class="fas fa-chart-line text-purple-500 mr-2"></i>
                                        <span class="text-gray-800 font-medium">{{ job.job_experience_level ||
                                            'Notspecified'
                                            }}</span>
                                    </div>
                                </div>
                                <div class="bg-white p-4 rounded-lg border border-white-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">Work Environment</h4>
                                    <div class="flex items-center">
                                        <i class="fas fa-laptop-house text-teal-500 mr-2"></i>
                                        <span class="text-gray-800 font-medium">
                                            <template v-if="Array.isArray(job.work_environment)">
                                                {{ job.work_environment.length ? job.work_environment.join(', ') : 'Not specified' }}
                                            </template>
                                            <template v-else>
                                                {{ job.work_environment || 'Not specified' }}
                                            </template>
                                        </span>
                                    </div>
                                </div>
                                <div class="bg-white p-4 rounded-lg border border-white-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">Vacancies</h4>
                                    <div class="flex items-center">
                                        <i class="fas fa-users text-green-500 mr-2"></i>
                                        <span class="text-gray-800 font-medium">{{ job.job_vacancies || 1 }}</span>
                                    </div>
                                </div>
                                <div class="bg-white p-4 rounded-lg border border-white-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">Salary Range</h4>
                                    <div class="flex items-center">
                                        <i class="fas fa-peso-sign text-gray-400 mr-2"></i>
                                        <span class="text-gray-800">
                                            {{ job.salary_range || 'Negotiable' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm border border-white-200 p-6">
                            <h3
                                class="text-lg font-medium text-gray-800 mb-4 flex items-center border-b border-white-200 pb-3">
                                <i class="fas fa-align-left text-blue-500 mr-2"></i>
                                Job Description
                            </h3>
                            <div class="prose max-w-none text-gray-700" v-html="job.job_description"></div>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm border border-white-200 p-6">
                            <h3
                                class="text-lg font-medium text-gray-800 mb-4 flex items-center border-b border-white-200 pb-3">
                                <i class="fas fa-list-check text-blue-500 mr-2"></i>
                                Job Requirements
                            </h3>
                            <div class="prose max-w-none text-gray-700" v-html="job.job_requirements"></div>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm border border-white-200 p-6">
                            <h3
                                class="text-lg font-medium text-gray-800 mb-4 flex items-center border-b border-white-200 pb-3">
                                <i class="fas fa-code text-blue-500 mr-2"></i>
                                Required Skills
                            </h3>
                            <div v-if="job.skills && job.skills.length > 0" class="flex flex-wrap gap-2">
                                <span v-for="(skill, idx) in job.skills" :key="skill" :class="[
                                    'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                    bubbleColors[idx % bubbleColors.length]
                                ]">
                                    <i class="fas fa-check-circle mr-1 text-blue-500"></i>
                                    {{ skill }}
                                </span>
                            </div>
                            <p v-else class="text-gray-500 italic">No specific skills listed for this position</p>
                        </div>

                        <div class="bg-white rounded-lg shadow-sm border border-white-200 p-6">
                            <h3
                                class="text-lg font-medium text-gray-800 mb-4 flex items-center border-b border-white-200 pb-3">
                                <i class="fas fa-graduation-cap text-indigo-400 mr-2"></i>
                                Programs
                            </h3>
                            <div class="flex flex-wrap gap-2">
                                <template v-if="Array.isArray(job.programs) && job.programs.length">
                                    <span v-for="(program, idx) in job.programs" :key="program" :class="[
                                        'inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold',
                                        bubbleColors[idx % bubbleColors.length]
                                    ]">
                                        {{ program }}
                                    </span>
                                </template>
                                <span v-else class="text-gray-500">Not specified</span>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:w-80 space-y-6">
                        <div class="bg-white rounded-lg shadow-sm border border-white-200 p-6">
                            <h3
                                class="text-lg font-medium text-gray-800 mb-4 flex items-center border-b border-white-200 pb-3">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                Job Information
                            </h3>
                            <div class="space-y-4">
                                <div class="bg-white p-3 rounded-lg border border-white-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">Posted by</h4>
                                    <div class="flex items-center">
                                        <i class="fas fa-id-card text-gray-400 mr-2"></i>
                                        <span class="text-gray-800">{{ job.posted_by }}</span>
                                    </div>
                                </div>
                                <div v-if="job.company" class="space-y-4">
                                    <div class="bg-white p-4 rounded-lg border border-white-200">
                                        <h4 class="text-sm font-medium text-gray-500 mb-1">Company Name</h4>
                                        <div class="flex items-center">
                                            <i class="fas fa-building text-gray-400 mr-2"></i>
                                            <span class="text-gray-800">{{ job.company.name }}</span>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="text-gray-500 italic">Company information not available</p>
                                <div class="bg-white p-3 rounded-lg border border-white-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">Location</h4>
                                    <div class="flex items-center">
                                        <i class="fas fa-map-marker-alt text-gray-400 mr-2"></i>
                                        <span class="text-gray-800">
                                            <template v-if="Array.isArray(job.location)">
                                                {{ job.location.length ? job.location.join(', ') : 'Location notspecified' }}
                                            </template>
                                            <template v-else>
                                                {{ job.location || 'Location not specified' }}
                                            </template>
                                        </span>
                                    </div>
                                </div>
                                <div class="bg-white p-4 rounded-lg border border-white-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">Application Deadline</h4>
                                    <div class="flex items-center">
                                        <i class="far fa-calendar-times text-gray-400 mr-2"></i>
                                        <span>{{ formatDate(job.job_deadline) }}</span>
                                    </div>
                                </div>
                                <div class="bg-white p-4 rounded-lg border border-white-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-1">Posted Date</h4>
                                    <div class="flex items-center">
                                        <i class="far fa-calendar-plus text-gray-400 mr-2"></i>
                                        <span>{{ formatDate(job.posted_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <button @click="downloadInfographic"
                    class="px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 transition mb-4"
                    :disabled="downloading">
                    <i class="fas fa-download mr-2"></i>
                    Download Infographic Template
                </button>

                <!-- Infographic Template (hidden from normal view, only for export) -->
                <div id="job-infographic-template" class="mx-auto bg-white rounded-xl shadow-lg p-8" style="
    width: 600px;
    min-height: 700px;
    border: 6px solid #1e3a8a; /* Tailwind blue-800 */
    position: relative;
    font-family: 'Segoe UI', Arial, sans-serif;
    display: none;
  ">
                    <!-- Logo at the top center -->
                    <div style="text-align:center; margin-bottom: 18px;">
                        <img src="/path/to/city-logo.png" alt="City Logo" style="height: 70px; margin: 0 auto;" />
                    </div>
                    <!-- Title -->
                    <div
                        style="text-align:center; font-size: 1.5rem; font-weight: bold; color: #1e3a8a; margin-bottom: 18px;">
                        PESO JOB VACANCY
                    </div>
                    <!-- Job Title -->
                    <div
                        style="text-align:center; font-size: 1.25rem; font-weight: bold; color: #222; margin-bottom: 10px;">
                        {{ job.job_title }}
                    </div>
                    <!-- Company & Location -->
                    <div style="text-align:center; color: #444; margin-bottom: 18px;">
                        <span style="font-weight: 500;">{{ job.company?.name || 'Company' }}</span>
                        <span v-if="job.location"> | {{ Array.isArray(job.location) ? job.location.join(', ') :
                            job.location
                            }}</span>
                    </div>
                    <!-- Section: Job Description -->
                    <div style="margin-bottom: 14px;">
                        <div style="font-weight: bold; color: #1e3a8a;">Job Description:</div>
                        <div style="color: #222; font-size: 1rem;" v-html="job.job_description"></div>
                    </div>
                    <!-- Section: Qualifications -->
                    <div style="margin-bottom: 14px;">
                        <div style="font-weight: bold; color: #1e3a8a;">Qualifications:</div>
                        <ul style="color: #222; font-size: 1rem; margin-left: 1.5em;">
                            <li v-for="skill in job.skills" :key="skill">{{ skill }}</li>
                        </ul>
                    </div>
                    <!-- Section: How to Apply -->
                    <div style="margin-bottom: 18px;">
                        <div style="font-weight: bold; color: #1e3a8a;">How to Apply:</div>
                        <div style="color: #222;">
                            Visit PESO or contact: <span style="font-weight: bold;">{{ job.company?.email || 'N/A'
                                }}</span>
                        </div>
                    </div>
                    <!-- Footer Hashtag -->
                    <div style="
                            position: absolute;
                            left: 0; right: 0; bottom: 24px;
                            text-align: center;
                            color: #1e3a8a;
                            font-weight: bold;
                            font-size: 1.1rem;
                            letter-spacing: 1px;">
                        #PESOGENSAN #JobVacancy #ApplyNow
                    </div>
                </div>
            </div>
        </Container>
    </AppLayout>
</template>