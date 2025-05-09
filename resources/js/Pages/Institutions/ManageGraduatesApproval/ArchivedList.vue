<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    graduates: {
        type: Array,
        default: () => ([]),
    },
});


const restoreGraduate = (id) => {
    router.put(route('graduates.restore', { id }), {}, { preserveScroll: true });
};
</script>

<template>
    <AppLayout title="Archived Graduates">
        <Container>
            <h2 class="text-xl font-semibold mb-4">Archived Graduates</h2>

            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-sm">
                        <th class="border border-gray-200 px-6 py-3 text-left">Name</th>
                        <th class="border border-gray-200 px-6 py-3 text-left">Program</th>
                        <th class="border border-gray-200 px-6 py-3 text-left">Date Created</th>
                        <th class="border border-gray-200 px-6 py-3 text-left">Status</th>
                        <th class="border border-gray-200 px-6 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="graduate in graduates" :key="graduate.id" class="border-b border-gray-200">
                        <td class="border border-gray-200 px-6 py-4">{{ graduate.graduate_first_name }} {{ graduate.graduate_middle_initial }} {{
                                graduate.graduate_last_name }}</td>
                        <td class="border border-gray-200 px-6 py-4">{{ graduate.graduate_program_completed }}</td>
                        <td class="border border-gray-200 px-6 py-4">{{ new Date(graduate.created_at).toLocaleDateString() }}</td>
                        <td class="border border-gray-200 px-6 py-4">
                            <span class="text-red-600 font-semibold">Archived</span>
                        </td>
                        <td class="border border-gray-200 px-6 py-4">
                            <PrimaryButton @click="restoreGraduate(graduate.id)">
                                Restore
                            </PrimaryButton>
                        </td>
                    </tr>
                </tbody>
            </table>
        </Container>
    </AppLayout>
</template>
