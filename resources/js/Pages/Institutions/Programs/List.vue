<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps({
    programs: Array
});

const showModal = ref(false);
const programToDelete = ref(null);

const filteredPrograms = computed(() => {
    return props.programs;
});

function confirmDelete(program) {
    programToDelete.value = program;
    showModal.value = true;
}

function handleDelete() {
    router.delete(route('programs.destroy', { program: programToDelete.value.id }), {
        preserveScroll: true,
        onSuccess: () => showModal.value = false,
    });
}
</script>

<template>
    <AppLayout title="Manage Programs">
        <template #header>List of Programs</template>

        <Container class="py-8">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="border border-gray-200 px-6 py-3 text-left">Program</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Degree</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Status</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <tr v-for="prog in filteredPrograms" :key="prog.id" class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="border border-gray-200 px-6 py-4">{{ prog.name }}</td>
                            <td class="border border-gray-200 px-6 py-4">{{ prog.degree?.type }}</td>
                            <td class="border border-gray-200 px-6 py-4" :class="prog.deleted_at ? 'text-red-500 font-bold' : 'text-green-500 font-bold'">
                                {{ prog.deleted_at ? 'Inactive' : 'Active' }}
                            </td>
                            <td class="border border-gray-200 px-6 py-4">
                                <DangerButton @click="confirmDelete(prog)">Delete</DangerButton>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <ConfirmationModal :show="showModal" @close="showModal = false">
                <template #title>Confirm Deletion</template>
                <template #content>Are you sure you want to delete this program?</template>
                <template #footer>
                    <PrimaryButton @click="showModal = false">Cancel</PrimaryButton>
                    <DangerButton @click="handleDelete">Delete</DangerButton>
                </template>
            </ConfirmationModal>
        </Container>
    </AppLayout>
</template>
