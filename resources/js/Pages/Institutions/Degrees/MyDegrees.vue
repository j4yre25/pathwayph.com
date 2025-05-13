<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link, router } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    degrees: Array,
});

console.log(props);

const selectedDegree = ref(null);
const open = ref(false);

const archiveDegree = () => {
    if (selectedDegree.value) {
        router.delete(route('degrees.delete', { id: selectedDegree.value.id }));
        open.value = false;
        selectedDegree.value = null;
    }
};

const confirmArchive = (degree) => {
    selectedDegree.value = degree;
    open.value = true;
};
</script>

<template>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
            <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                    <th class="border border-gray-200 px-6 py-3 text-left">Degree</th>
                    <th class="border border-gray-200 px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                <tr v-for="degree in degrees" :key="degree.id" class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="border border-gray-200 px-6 py-4">{{ degree.type }}</td>
                    <td class="border border-gray-200 px-6 py-4">
                        <Link :href="route('degrees.edit', { id: degree.id })">
                            <PrimaryButton class="mr-2">Edit</PrimaryButton>
                        </Link>
                        <DangerButton @click="confirmArchive(degree)">
                            Archive
                        </DangerButton>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <ConfirmationModal @close="open = false" :show="open">
        <template #title>
            Are you sure?
        </template>

        <template #content>
            Are you sure you want to archive the degree "{{ selectedDegree?.type }}"?
        </template>

        <template #footer>
            <DangerButton @click="archiveDegree" class="mr-2">
                Archive
            </DangerButton>
            <SecondaryButton @click="open = false">Cancel</SecondaryButton>
        </template>
    </ConfirmationModal>
</template>
