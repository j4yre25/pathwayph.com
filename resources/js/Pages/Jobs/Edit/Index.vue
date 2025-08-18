<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import UpdateForm from '@/Pages/Jobs/Edit/UpdateForm.vue';
import DeleteForm from '@/Pages/Jobs/Edit/DeleteForm.vue';
import { Link } from '@inertiajs/vue3';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
    job: Object
});

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <AppLayout title="Edit Job">
        <template #header>
            <div class="flex items-center">
                <button @click="goBack" class="flex items-center text-blue-600 hover:text-blue-800 transition-colors mr-4">
                        <i class="fas fa-chevron-left mr-2"></i>
                    </button>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Job</h2>
            </div>
        </template>

        <Container class="py-10">
            <!-- Job Header Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">{{ job.job_title }}</h2>
                        <div class="flex flex-wrap items-center mt-2 text-sm text-gray-600">
                            <div v-if="job.location" class="flex items-center mr-4 mb-2 md:mb-0">
                                <i class="fas fa-map-marker-alt text-red-500 mr-1"></i>
                                <span>{{ job.location }}</span>
                            </div>
                            <div v-if="job.job_type" class="flex items-center mr-4 mb-2 md:mb-0">
                                <i class="fas fa-briefcase text-blue-500 mr-1"></i>
                                <span>{{ job.job_type }}</span>
                            </div>
                            <div v-if="job.experience_level" class="flex items-center mb-2 md:mb-0">
                                <i class="fas fa-chart-line text-purple-500 mr-1"></i>
                                <span>{{ job.experience_level }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" 
                              :class="{
                                  'bg-green-100 text-green-800': job.status === 'active',
                                  'bg-yellow-100 text-yellow-800': job.status === 'draft',
                                  'bg-red-100 text-red-800': job.status === 'closed',
                                  'bg-gray-100 text-gray-800': !['active', 'draft', 'closed'].includes(job.status)
                              }">
                            <i class="fas mr-1" 
                               :class="{
                                   'fa-check-circle': job.status === 'active',
                                   'fa-pencil-alt': job.status === 'draft',
                                   'fa-times-circle': job.status === 'closed',
                                   'fa-circle': !['active', 'draft', 'closed'].includes(job.status)
                               }"></i>
                            {{ job.status ? job.status.charAt(0).toUpperCase() + job.status.slice(1) : 'Unknown' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Update Form Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
                <UpdateForm :job="job" />
            </div>

            <!-- Delete Form Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <DeleteForm :job="job" />
            </div>
        </Container>
    </AppLayout>
</template>