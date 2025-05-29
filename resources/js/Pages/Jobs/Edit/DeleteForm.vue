<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
    job: Object
});

const confirmingJobDeletion = ref(false);

const confirmJobDeletion = () => {
    confirmingJobDeletion.value = true;
};

const closeModal = () => {
    confirmingJobDeletion.value = false;
};

const deleteJob = () => {
    router.delete(route('jobs.destroy', {job: props.job.id}), {
        onSuccess: () => {
            confirmingJobDeletion.value = false;
        },
    });
};
</script>

<template>
    <div class="p-6">
        <div class="border-b border-gray-200 pb-4 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-trash-alt text-red-500 mr-2"></i>
                Delete Job
            </h3>
            <p class="text-sm text-gray-600 mt-2">
                Once a job is deleted, all of its resources and data will be permanently removed.
            </p>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-500 text-lg"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Warning</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>Deleting this job posting will remove it from your dashboard and the job board. This action cannot be undone.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <div class="mb-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Job Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center">
                        <i class="fas fa-briefcase text-blue-500 mr-2"></i>
                        <span class="text-sm text-gray-600">{{ job.job_title }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-id-card text-blue-500 mr-2"></i>
                        <span class="text-sm text-gray-600">ID: {{ job.id }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-map-marker-alt text-red-500 mr-2"></i>
                        <span class="text-sm text-gray-600">{{ job.location || 'Location not specified' }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-business-time text-blue-500 mr-2"></i>
                        <span class="text-sm text-gray-600">{{ job.job_type || 'Job type not specified' }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <DangerButton @click="confirmJobDeletion" class="flex items-center">
                    <i class="fas fa-trash-alt mr-2"></i>
                    Delete Job
                </DangerButton>
            </div>
        </div>

        <!-- Delete Job Confirmation Modal -->
        <Modal :show="confirmingJobDeletion" @close="closeModal">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="bg-red-100 rounded-full p-2 mr-3">
                        <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                    </div>
                    <h2 class="text-lg font-medium text-gray-900">Are you sure you want to delete this job?</h2>
                </div>

                <div class="mt-4 text-sm text-gray-600">
                    <p>Once this job is deleted, all of its resources and data will be permanently removed. Please confirm you would like to permanently delete:</p>
                    <div class="mt-3 p-3 bg-gray-50 rounded-md border border-gray-200">
                        <p class="font-medium text-gray-800">{{ job.job_title }}</p>
                        <div class="mt-1 text-xs text-gray-500 flex items-center">
                            <i class="fas fa-id-card mr-1"></i>
                            <span>ID: {{ job.id }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeModal">
                        Cancel
                    </SecondaryButton>

                    <DangerButton @click="deleteJob" class="ml-3">
                        Delete Job
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </div>
</template>