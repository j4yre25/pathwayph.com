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
            <h2 class="text-3xl font-semibold text-gray-900">Archived Degrees</h2>
        </template>

        <Container class="py-16 space-y-8">
            <!-- Degrees Table -->
            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full text-sm text-gray-700">
                    <thead>
                        <tr class="bg-gray-200 text-gray-600 uppercase text-xs font-medium">
                            <th class="px-6 py-4 text-left">Degree</th>
                            <th class="px-6 py-4 text-left">Status</th>
                            <th class="px-6 py-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600">
                        <tr v-for="deg in all_degrees" :key="deg.id" class="border-t hover:bg-gray-50 transition-all">
                            <td class="px-6 py-4">{{ deg.type }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-red-600">
                                Archived
                            </td>
                            <td class="px-6 py-4 flex gap-3 items-center">
                                <DangerButton @click="confirmRestore(deg)" class="text-xs text-white bg-red-600 hover:bg-red-700">
                                    Restore
                                </DangerButton>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Confirmation Modal -->
            <ConfirmationModal :show="showModal" @close="showModal = false" @confirm="restoreDegree">
                <template #title>
                    <span class="text-xl font-semibold text-gray-800">Confirm Restore</span>
                </template>
                <template #content>
                    <p class="text-sm text-gray-600">Are you sure you want to restore the degree <strong>"{{ degreeToRestore?.type }}"</strong>?</p>
                </template>
                <template #footer>
                    <PrimaryButton @click="showModal = false" class="mr-2 text-gray-600 hover:bg-gray-200">
                        Cancel
                    </PrimaryButton>
                    <DangerButton @click="restoreDegree" class="text-white bg-red-600 hover:bg-red-700">
                        Restore
                    </DangerButton>
                </template>
            </ConfirmationModal>
        </Container>
    </AppLayout>
</template>
