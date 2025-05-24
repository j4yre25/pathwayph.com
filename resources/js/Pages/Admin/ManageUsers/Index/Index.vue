<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import { router } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const page = usePage();

const props = defineProps({
    all_users: Object,// Paginated data
});

console.log('all_users:', props.all_users.data);

const showModal = ref(false);
const userToArchive = ref(null);

const archiveUser = () => {
    if (userToArchive.value) {
        router.delete(route('admin.manage_users.delete', { user: userToArchive.value.id }));
        showModal.value = false;
        userToArchive.value = null;
    }
};

const confirmArchive = (user) => {
    userToArchive.value = user;
    showModal.value = true;
};

const approveUser = (user) => {
    router.post(route('admin.manage_users.approve', { user: user.id }), {});
};

const disapproveUser = (user) => {
    console.log('Disapproving user:', user);
    router.post(route('admin.manage_users.disapprove', { user: user.id }), {
        is_approved: false,
    });
};

const goTo = (url) => {
    router.get(url); // Use Inertia's router to navigate to the next/previous page
};
</script>

<template>
    <AppLayout title="Manage Users">
        <template #header>
            Manage Users
        </template>

        <Container class="py-4">
            <div class="mt-8 mb-8">
                <Link v-if="page.props.roles.isPeso" :href="route('admin.manage_users.list')"
                    :active="route().current('admin.manage_users.list')">
                <PrimaryButton>List Of Users</PrimaryButton>
                </Link>
                <Link class="ml-2" v-if="page.props.roles.isPeso" :href="route('admin.manage_users.archivedlist')"
                    :active="route().current('admin.manage_users.archivedlist')">
                <PrimaryButton>Archived Users</PrimaryButton>
                </Link>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="border border-gray-200 px-6 py-3 text-left">Role</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Organization Name</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Name</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Email</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Status</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <tr v-for="user in all_users.data" :key="user.id"
                            class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="border border-gray-200 px-6 py-4">{{ user.role }}</td>
                            <td class="border border-gray-200 px-6 py-4">
                                {{ user.organization_name }}
                            </td>
                            <td class="border border-gray-200 px-6 py-4">
                                <div v-if="user.peso">
                                    <div>
                                        {{ user.peso.peso_first_name }} {{ user.peso.peso_middle_name }} {{
                                        user.peso.peso_last_name }}
                                    </div>
                                </div>
                            </td>
                            <td class="border border-gray-200 px-6 py-4">{{ user.email }}</td>
                            <td class="border border-gray-200 px-6 py-4">
                                <span v-if="user.is_approved === true"
                                    class="text-green-600 font-semibold">Approved</span>
                                <span v-else-if="user.is_approved === false"
                                    class="text-red-600 font-semibold">Disapproved</span>
                                <span v-else class="text-yellow-600 font-semibold">Pending</span>
                            </td>
                            <td class="border border-gray-200 px-6 py-4">
                                <div class="flex items-center">
                                    <PrimaryButton class="mr-2" @click="approveUser(user)"
                                        v-if="user.is_approved !== false && !user.is_approved">
                                        Approve
                                    </PrimaryButton>
                                    <DangerButton @click="disapproveUser(user)"
                                        v-if="user.is_approved !== false && !user.is_approved">
                                        Disapprove
                                    </DangerButton>
                                    <DangerButton class="ml-2" @click="confirmArchive(user)"
                                        v-if="user.is_approved !== false">
                                        Archive
                                    </DangerButton>
                                </div>
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

            <ConfirmationModal @close="showModal = false" :show="showModal">
                <template #title>
                    Are you sure?
                </template>

                <template #content>
                    Are you sure you want to archive this user {{ userToArchive?.name }}?
                </template>

                <template #footer>
                    <DangerButton @click="archiveUser" class="mr-2">
                        Archive User
                    </DangerButton>
                    <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                </template>
            </ConfirmationModal>
        </Container>
    </AppLayout>
</template>