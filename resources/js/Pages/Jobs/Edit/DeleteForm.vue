<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
    job: Object,
});

const confirmingJobDeletion = ref(false);

const form = useForm({});

const confirmJobDeletion = () => {
    confirmingJobDeletion.value = true;
};

const deleteJob = () => {
    form.delete(route('jobs.destroy', { job: props.job.id }), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

const closeModal = () => {
    confirmingJobDeletion.value = false;
};
</script>

<template>
    <div class="p-6 bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="border-b border-gray-200 pb-4 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-trash-alt text-red-500 mr-2"></i>
                Delete Job Posting
            </h3>
            <p class="text-sm text-gray-600 mt-2">
                Once a job posting is deleted, all of its resources and data will be permanently removed.
            </p>
        </div>

        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200 mb-6">
            <div class="flex items-start">
                <div class="flex-shrink-0 mt-1">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-xl"></i>
                </div>
                <div class="ml-4">
                    <h4 class="text-md font-medium text-gray-800">Warning: This action cannot be undone</h4>
                    <p class="mt-2 text-sm text-gray-600">
                        You are about to delete the job posting titled <span class="font-semibold">"{{ job.job_title }}"</span>. 
                        This will permanently remove the job posting from our system and any associated applications.
                    </p>
                    <div class="mt-4 bg-white p-4 rounded-md border border-gray-200">
                        <h5 class="text-sm font-medium text-gray-700 mb-2">Job Information:</h5>
                        <ul class="space-y-2 text-sm">
                            <li class="flex items-center">
                                <i class="fas fa-briefcase text-blue-500 w-5"></i>
                                <span class="ml-2">{{ job.job_title }}</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-map-marker-alt text-red-500 w-5"></i>
                                <span class="ml-2">{{ job.location }}</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-business-time text-green-500 w-5"></i>
                                <span class="ml-2">{{ job.job_type }}</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-calendar-alt text-purple-500 w-5"></i>
                                <span class="ml-2">Posted on: {{ new Date(job.created_at).toLocaleDateString() }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <DangerButton @click="confirmJobDeletion" class="flex items-center">
                <i class="fas fa-trash-alt mr-2"></i>
                Delete Job Posting
            </DangerButton>
        </div>

        <!-- Delete Job Confirmation Modal -->
        <Modal :show="confirmingJobDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                    Are you sure you want to delete this job posting?
                </h2>

                <p class="mt-3 text-sm text-gray-600">
                    Once this job posting is deleted, all of its resources and data will be permanently deleted.
                    Please enter the job title to confirm you would like to permanently delete this job posting.
                </p>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal" class="mr-3">
                        Cancel
                    </SecondaryButton>

                    <DangerButton 
                        @click="deleteJob"
                        :class="{ 'opacity-25': form.processing }" 
                        :disabled="form.processing"
                    >
                        <i class="fas fa-trash-alt mr-1"></i>
                        Delete Job Posting
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </div>
</template>