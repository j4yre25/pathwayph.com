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
            <div class="flex items-center">
                <button @click="() => window.history.back()" class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-users text-blue-500 text-xl mr-2"></i> Manage Users
                </h2>
            </div>
        </template>

        <!-- Add FontAwesome CSS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

        <Container class="py-6 space-y-6">
            <!-- Navigation Tabs -->
            <div class="bg-white rounded-lg shadow-sm p-4 flex space-x-4">
                <Link v-if="page.props.roles.isPeso" :href="route('admin.manage_users.list')"
                    :active="route().current('admin.manage_users.list')"
                    class="px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors"
                >
                    <i class="fas fa-list"></i>
                    <span>List Of Users</span>
                </Link>
                <Link v-if="page.props.roles.isPeso" :href="route('admin.manage_users.archivedlist')"
                    :active="route().current('admin.manage_users.archivedlist')"
                    class="ml-2 px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors"
                >
                    <i class="fas fa-archive"></i>
                    <span>Archived Users</span>
                </Link>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-200 flex items-center">
                    <i class="fas fa-table text-blue-500 mr-2"></i>
                    <h3 class="font-medium text-gray-700">User Management</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                <th class="border border-gray-200 px-6 py-3 text-left"><i class="fas fa-user-tag mr-2"></i>Role</th>
                                <th class="border border-gray-200 px-6 py-3 text-left"><i class="fas fa-building mr-2"></i>Organization Name</th>
                                <th class="border border-gray-200 px-6 py-3 text-left"><i class="fas fa-user mr-2"></i>Name</th>
                                <th class="border border-gray-200 px-6 py-3 text-left"><i class="fas fa-envelope mr-2"></i>Email</th>
                                <th class="border border-gray-200 px-6 py-3 text-left"><i class="fas fa-info-circle mr-2"></i>Status</th>
                                <th class="border border-gray-200 px-6 py-3 text-left"><i class="fas fa-cogs mr-2"></i>Actions</th>
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
                                    {{ user.full_name }}
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
                                <div class="flex items-center space-x-2">
                                    <button 
                                        @click="approveUser(user)"
                                        v-if="user.is_approved !== false && !user.is_approved"
                                        class="px-3 py-1 bg-green-500 text-white rounded-md text-xs flex items-center hover:bg-green-600 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50"
                                    >
                                        <i class="fas fa-check mr-1"></i> Approve
                                    </button>
                                    <button 
                                        @click="disapproveUser(user)"
                                        v-if="user.is_approved !== false && !user.is_approved"
                                        class="px-3 py-1 bg-red-500 text-white rounded-md text-xs flex items-center hover:bg-red-600 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50"
                                    >
                                        <i class="fas fa-times mr-1"></i> Disapprove
                                    </button>
                                    <button 
                                        @click="confirmArchive(user)"
                                        v-if="user.is_approved !== false"
                                        class="px-3 py-1 bg-gray-500 text-white rounded-md text-xs flex items-center hover:bg-gray-600 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50"
                                    >
                                        <i class="fas fa-archive mr-1"></i> Archive
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="all_users.data.length === 0">
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-users text-gray-400 text-3xl mb-2"></i>
                                    <p>No users found</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div v-if="all_users.links && all_users.links.length > 3" class="px-4 py-3 flex items-center justify-between border-t border-gray-200">
                <div class="flex-1 flex justify-between sm:hidden">
                    <a 
                        v-if="all_users.prev_page_url" 
                        @click.prevent="goTo(all_users.prev_page_url)" 
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 cursor-pointer"
                    >
                        Previous
                    </a>
                    <a 
                        v-if="all_users.next_page_url" 
                        @click.prevent="goTo(all_users.next_page_url)" 
                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 cursor-pointer"
                    >
                        Next
                    </a>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium">{{ all_users.from }}</span> to <span class="font-medium">{{ all_users.to }}</span> of <span class="font-medium">{{ all_users.total }}</span> results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <a 
                                v-for="link in all_users.links" 
                                :key="link.url" 
                                v-html="link.label"
                                @click.prevent="link.url ? goTo(link.url) : null"
                                :class="[
                                    link.url ? 'cursor-pointer hover:bg-gray-50' : 'cursor-not-allowed',
                                    link.active ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500',
                                    link.label.includes('Previous') ? 'rounded-l-md' : '',
                                    link.label.includes('Next') ? 'rounded-r-md' : '',
                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                                ]"
                            ></a>
                        </nav>
                    </div>
                </div>
            </div>
            </div>

            <!-- Confirmation Modal -->
            <ConfirmationModal @close="showModal = false" :show="showModal">
                <template #title>
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                        <span>Confirm Archive</span>
                    </div>
                </template>

                <template #content>
                    <p class="text-gray-600">
                        Are you sure you want to archive this user <span class="font-medium">{{ userToArchive?.full_name }}</span>?
                        <br>
                        This action can be reversed later.
                    </p>
                </template>

                <template #footer>
                    <div class="flex justify-end space-x-3">
                        <button 
                            @click="showModal = false"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm flex items-center hover:bg-gray-300 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50"
                        >
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button 
                            @click="archiveUser"
                            class="px-4 py-2 bg-red-500 text-white rounded-md text-sm flex items-center hover:bg-red-600 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50"
                        >
                            <i class="fas fa-archive mr-2"></i> Archive User
                        </button>
                    </div>
                </template>
            </ConfirmationModal>
        </Container>
    </AppLayout>
</template>