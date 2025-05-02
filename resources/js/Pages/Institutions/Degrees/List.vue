<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { router } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref, computed } from 'vue';

const page = usePage();

const props = defineProps({
    degrees: Array
});

const showModal = ref(false);
const degreeToDelete = ref(null);

const filteredDegrees = computed(() => {
    return props.degrees;
});

function confirmDelete(degree) {
    degreeToDelete.value = degree;
    showModal.value = true;
}

function handleDelete() {
    router.delete(route('degrees.destroy', { degree: degreeToDelete.value.id }), {
        preserveScroll: true,
        onSuccess: () => showModal.value = false,
    });
}
</script>

<template>
    <AppLayout title="Manage Degrees">
        <template #header>
            List of Degrees
        </template>

        <Container class="py-8">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="border border-gray-200 px-6 py-3 text-left">Degree</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <tr v-for="degree in filteredDegrees" :key="degree.id" class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="border border-gray-200 px-6 py-4">{{ degree.type }}</td>
                            <td class="border border-gray-200 px-6 py-4" :class="degree.deleted_at ? 'text-red-500 font-bold' : 'text-green-500 font-bold'">
                                {{ degree.deleted_at ? 'Inactive' : 'Active' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <ConfirmationModal :show="showModal" @close="showModal = false">
                <template #title>Confirm Deletion</template>
                <template #content>Are you sure you want to delete this degree?</template>
                <template #footer>
                    <PrimaryButton @click="showModal = false">Cancel</PrimaryButton>
                    <DangerButton @click="handleDelete">Delete</DangerButton>
                </template>
            </ConfirmationModal>
        </Container>
    </AppLayout>
</template>
