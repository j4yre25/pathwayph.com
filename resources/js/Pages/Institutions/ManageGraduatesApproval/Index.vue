<script setup>
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

defineProps({
    graduates: Array,
});

const approveGraduate = (id) => {
    router.put(route('graduates.approve', { id }), {}, { preserveScroll: true });
};

const disapproveGraduate = (id) => {
    router.put(route('graduates.disapprove', { id }), {}, { preserveScroll: true });
};
</script>

<template>
    <AppLayout title="Manage Graduate Approvals">
        <Container>
            <!-- Modern Header -->
            <div class="mt-8 mb-8 flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Graduate Approval Management
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">Review and approve or disapprove graduate submissions.</p>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="mb-8 flex flex-wrap gap-4">
                <PrimaryButton @click="router.get(route('graduates.list'))">
                    List of Graduates
                </PrimaryButton>
                <PrimaryButton @click="router.get(route('graduates.archived'))">
                    Archived Graduates
                </PrimaryButton>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-2xl shadow ring-1 ring-black/5">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Name
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Email
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="graduate in graduates" :key="graduate.id" class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ graduate.graduate_first_name }} {{ graduate.graduate_middle_initial }} {{ graduate.graduate_last_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ graduate.email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span
                                    :class="graduate.is_approved ? 'text-green-600 font-semibold' : 'text-yellow-500 font-medium'">
                                    {{ graduate.is_approved ? 'Approved' : 'Pending' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap space-x-2">
                                <PrimaryButton
                                    @click="approveGraduate(graduate.id)"
                                    v-if="!graduate.is_approved">
                                    Approve
                                </PrimaryButton>
                                <DangerButton @click="disapproveGraduate(graduate.id)">
                                    Disapprove
                                </DangerButton>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Container>
    </AppLayout>
</template>
