<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { router } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';

const props = defineProps({
    all_programs: Array,
});

const showModal = ref(false);
const programToRestore = ref(null);

const restoreProgram = () => {
    router.post(route('programs.restore', {
        id: programToRestore.value.id
    }), {}, {
        onSuccess: () => {
            showModal.value = false;
        }
    });
};

const confirmRestore = (program) => {
    programToRestore.value = program;
    showModal.value = true;
};
</script>

<template>
    <AppLayout title="Archived Programs">
        <template #header>Archived Programs</template>

        <Container class="py-16">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="border border-gray-200 px-6 py-3 text-left">Program</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Degree</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Status</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <tr v-for="program in all_programs" :key="program.id" class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="border border-gray-200 px-6 py-4">{{ program.name }}</td>
                            <td class="border border-gray-200 px-6 py-4">{{ program.degree?.type }}</td>
                            <td class="border border-gray-200 px-6 py-4">
                                <span class="text-red-600 font-semibold">Archived</span>
                            </td>
                            <td class="border border-gray-200 px-6 py-4">
                                <DangerButton @click="confirmRestore(program)">Restore</DangerButton>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <ConfirmationModal :show="showModal" @close="showModal = false" @confirm="restoreProgram">
                <template #title>Confirm Restore</template>
                <template #content>Are you sure you want to restore this program?</template>
                <template #footer>
                    <PrimaryButton @click="showModal = false" class="mr-2">Cancel</PrimaryButton>
                    <DangerButton @click="restoreProgram">Restore</DangerButton>
                </template>
            </ConfirmationModal>
        </Container>
    </AppLayout>
</template>
