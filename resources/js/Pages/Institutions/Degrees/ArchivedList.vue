<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { router } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';

const props = defineProps({
    all_degrees: Array,
});

const showModal = ref(false);
const degreeToRestore = ref(null);

const restoreDegree = () => {
    router.post(route('degrees.restore', {
        id: degreeToRestore.value.id
    }), {}, {
        onSuccess: () => {
            showModal.value = false;
        }
    });
};

const confirmRestore = (degree) => {
    degreeToRestore.value = degree;
    showModal.value = true;
};
</script>

<template>
    <AppLayout title="Archived Degrees">
        <template #header>
            Archived Degrees
        </template>

        <Container class="py-16">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="border border-gray-200 px-6 py-3 text-left">Degree</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Status</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <tr v-for="deg in all_degrees" :key="deg.id" class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="border border-gray-200 px-6 py-4">{{ deg.type }}</td>
                            <td class="border border-gray-200 px-6 py-4">
                                <span class="text-red-600 font-semibold">Archived</span>
                            </td>
                            <td class="border border-gray-200 px-6 py-4">
                                <DangerButton @click="confirmRestore(deg)">Restore</DangerButton>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <ConfirmationModal :show="showModal" @close="showModal = false" @confirm="restoreDegree">
                <template #title>Confirm Restore</template>
                <template #content>Are you sure you want to restore this degree?</template>
                <template #footer>
                    <PrimaryButton @click="showModal = false" class="mr-2">Cancel</PrimaryButton>
                    <DangerButton @click="restoreDegree">Restore</DangerButton>
                </template>
            </ConfirmationModal>
        </Container>
    </AppLayout>
</template>
