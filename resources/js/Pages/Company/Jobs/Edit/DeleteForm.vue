<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3';
import ActionSection from '@/Components/ActionSection.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    job: Object

})


const open = ref(false)

const deleteJob = () => {
    router.delete(route('jobs.delete', { job: props.job.id }));
};

</script>

<template>
    <ActionSection>
        <template #title>
            Delete Job
        </template>

        <template #description>
                This section to delete your job
        </template>

        <template #content>
            <DangerButton @click ="open = true">
                Delete Job

            </DangerButton>

            <ConfirmationModal @close="open = false" :show="open">
                <template #title>
                    Are you sure?
                </template>

                <template #content>
                        Are you sure you want to delete this job #{{ job.id }} {{ job.name }}
                </template>

                <template #footer>
                        <DangerButton @click="deleteJob()" class="mr-2">
                            Delete Job
                        </DangerButton>
                        <SecondaryButton @click="open = false">Cancel</SecondaryButton>
                </template>

            </ConfirmationModal>
        </template>



    </ActionSection>


</template>