<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link, router } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
school_years: Array,
});

console.log(props);

const selectedSchoolYear = ref(null);
const open = ref(false);

const archiveSchoolYear = () => {
    if (selectedSchoolYear.value) {
        router.delete(route('school-years.delete', { id: selectedSchoolYear.value.id }));
        open.value = false;
        selectedSchoolYear.value = null;
    }
};

const confirmArchive = (schoolYear) => {
    selectedSchoolYear.value = schoolYear;
    open.value = true;
};
</script>

<template>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
            <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                    <th class="border border-gray-200 px-6 py-3 text-left">School Year</th>
                    <th class="border border-gray-200 px-6 py-3 text-left">Term</th>
                    <th class="border border-gray-200 px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                <tr v-for="sy in school_years" :key="sy.id" class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="border border-gray-200 px-6 py-4">{{ sy.school_year_range }}</td>
                    <td class="border border-gray-200 px-6 py-4">{{ sy.term }}</td>
                    <td class="border border-gray-200 px-6 py-4">
                        <Link :href="route('school-years.edit', { id: sy.id })">
                            <PrimaryButton class="mr-2">Edit</PrimaryButton>
                        </Link>
                        <DangerButton @click="confirmArchive(sy)">
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
            Are you sure you want to archive the school year "{{ selectedSchoolYear?.school_year_range }}"?
        </template>

        <template #footer>
            <DangerButton @click="archiveSchoolYear" class="mr-2">
                Archive
            </DangerButton>
            <SecondaryButton @click="open = false">Cancel</SecondaryButton>
        </template>
    </ConfirmationModal>
</template>
