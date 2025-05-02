<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';

const props = defineProps({
    all_school_years: Array,
});

const showModal = ref(false);
const schoolYearToRestore = ref(null);

const restoreSchoolYear = () => {
    router.post(route('school-years.restore', {
        id: schoolYearToRestore.value.id
    }), {}, {
        onSuccess: () => {
            showModal.value = false;
        }
    });
};

const confirmRestore = (school_years) => {
    schoolYearToRestore.value = school_years;
    showModal.value = true;
};
</script>

<template>
    <AppLayout title="Archived School Years">
        <template #header>
            Archived School Years
        </template>

        <Container class="py-16">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="border border-gray-200 px-6 py-3 text-left">School Year</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Term</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Status</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <tr v-for="sy in all_school_years" :key="sy.id" class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="border border-gray-200 px-6 py-4">{{ sy.school_year_range }}</td>
                            <td class="border border-gray-200 px-6 py-4">{{ sy.term }}</td>
                            <td class="border border-gray-200 px-6 py-4">
                                <span class="text-red-600 font-semibold">Archived</span>
                            </td>
                            <td class="border border-gray-200 px-6 py-4">
                                <DangerButton @click="confirmRestore(sy)">Restore</DangerButton>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <ConfirmationModal :show="showModal" @close="showModal = false" @confirm="restoreSchoolYear">
                <template #title>
                    Confirm Restore
                </template>
                <template #content>
                    Are you sure you want to restore this school year?
                </template>
                <template #footer>
                    <PrimaryButton @click="showModal = false" class="mr-2">Cancel</PrimaryButton>
                    <DangerButton @click="restoreSchoolYear">Restore</DangerButton>
                </template>
            </ConfirmationModal>
        </Container>
    </AppLayout>
</template>
