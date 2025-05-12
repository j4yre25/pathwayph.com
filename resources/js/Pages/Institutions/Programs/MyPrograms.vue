<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link, router } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref } from 'vue';

const props = defineProps({
    programs: Array,
});

const selectedProgram = ref(null);
const open = ref(false);

const archiveProgram = () => {
    if (selectedProgram.value) {
        router.delete(route('programs.delete', { id: selectedProgram.value.id }));
        open.value = false;
        selectedProgram.value = null;
    }
};

const confirmArchive = (program) => {
    selectedProgram.value = program;
    open.value = true;
};
</script>

<template>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
            <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                    <th class="border border-gray-200 px-6 py-3 text-left">Program</th>
                    <th class="border border-gray-200 px-6 py-3 text-left">Degree</th>
                    <th class="border border-gray-200 px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                <tr v-for="prog in programs" :key="prog.id" class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="border border-gray-200 px-6 py-4">{{ prog.name }}</td>
                    <td class="border border-gray-200 px-6 py-4">{{ prog.degree?.type }}</td>
                    <td class="border border-gray-200 px-6 py-4">
                        <Link :href="route('programs.edit', { id: prog.id })">
                            <PrimaryButton class="mr-2">Edit</PrimaryButton>
                        </Link>
                        <DangerButton @click="confirmArchive(prog)">Archive</DangerButton>
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
            Are you sure you want to archive the program "{{ selectedProgram?.name }}"?
        </template>

        <template #footer>
            <DangerButton @click="archiveProgram" class="mr-2">Archive</DangerButton>
            <SecondaryButton @click="open = false">Cancel</SecondaryButton>
        </template>
    </ConfirmationModal>
</template>
