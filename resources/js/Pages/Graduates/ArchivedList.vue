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
    router.put(route('graduates.restore', { graduate: id }));
};
</script>

<template>
    <AppLayout title="Archived Graduates">
        <Container>
            <!-- Modern Header -->
            <div class="mt-8 mb-8 flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Graduate Archived List
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">View the full list of archived graduates or restore them.</p>
                </div>
            </div>

            <div class="bg-white shadow-md rounded-2xl overflow-hidden border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Program</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Date Created</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <tr 
                            v-for="graduate in graduates" 
                            :key="graduate.id" 
                            class="hover:bg-gray-50 transition duration-150"
                        >
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                                {{ graduate.first_name }} {{ graduate.middle_initial }} {{ graduate.last_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ graduate.graduate_program_completed }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ new Date(graduate.created_at).toLocaleDateString() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <span class="text-red-600 bg-red-50 px-2 py-1 rounded-full text-xs">Archived</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <PrimaryButton @click="restoreGraduate(graduate.id)">
                                    Restore
                                </PrimaryButton>
                            </td>
                        </tr>
                        <tr v-if="graduates.length === 0">
                            <td colspan="5" class="px-6 py-6 text-center text-sm text-gray-400">
                                No archived graduates found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Container>
    </AppLayout>
</template>
