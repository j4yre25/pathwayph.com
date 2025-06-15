<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    all_users: Object, // Paginated data
});

const showModal = ref(false);
const userToRestore = ref(null);

const restoreUser = () => {
    router.post(route('admin.manage_users.restore', { user: userToRestore.value.id }), {}, {
        onSuccess: () => {
            showModal.value = false; // Close the modal after success
        },
    });
};

const confirmRestore = (user) => {
    userToRestore.value = user;
    showModal.value = true;
};

const goTo = (url) => {
    router.get(url); // Use Inertia's router to navigate to the next/previous page
};
</script>

<template>
    <AppLayout title="Archived Users">
        <template #header>
            Archived Users
        </template>

        <Container class="py-16">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="border border-gray-200 px-6 py-3 text-left">User Level</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Name</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Date Creation</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Status</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <tr v-for="user in all_users.data" :key="user.id"
                            class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="border border-gray-200 px-6 py-4">{{ user.role }}</td>
                            <td class="border border-gray-200 px-6 py-4">
                                {{ user.full_name }}
                            </td>
                            <td class="border border-gray-200 px-6 py-4">
                                {{ new Date(user.created_at).toLocaleDateString() }}
                            </td>
                            <td class="border border-gray-200 px-6 py-4">
                                <span class="text-red-600 font-semibold">Archived</span>
                            </td>
                            <td class="border border-gray-200 px-6 py-4">   
                                <DangerButton class="mr-2" @click="confirmRestore(user)">Restore User</DangerButton>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <nav v-if="all_users.links.length > 1" aria-label="Page navigation example">
                    <ul class="inline-flex -space-x-px text-sm">
                        <li v-for="link in all_users.links" :key="link.url" :class="{ 'active': link.active }">
                            <a v-if="link.url" @click.prevent="goTo(link.url)"
                                :class="link.active ? 'flex items-center justify-center px-3 h-8 text-blue-600 border border-gray-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 dark:border-gray-700 dark:bg-gray-700 dark:text-white' : 'flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white'">
                                <span v-html="link.label"></span>
                            </a>
                            <span v-else
                                class="flex items-center justify-center px-3 h-8 leading-tight text-gray-400 bg-gray-200 border border-gray-300">
                                <span v-html="link.label"></span>
                            </span>
                        </li>
                    </ul>
                </nav>
            </div>
            <ConfirmationModal :show="showModal" @close="showModal = false">
                <template #title>
                    Confirm Restore
                </template>
                <template #content>
                    Are you sure you want to restore this user?
                </template>
                <template #footer>
                    <PrimaryButton @click="showModal = false" class="mr-2">Cancel</PrimaryButton>
                    <DangerButton @click="restoreUser">Restore</DangerButton>
                </template>
            </ConfirmationModal>
        </Container>
    </AppLayout>
</template>