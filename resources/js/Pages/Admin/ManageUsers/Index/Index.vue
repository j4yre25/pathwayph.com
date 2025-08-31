<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref, computed } from 'vue';
import '@fortawesome/fontawesome-free/css/all.css';

const page = usePage();

const props = defineProps({
    all_users: Object,// Paginated data
});

const showModal = ref(false);
const userToArchive = ref(null);

// Stats for cards
const stats = computed(() => {
    const total = props.all_users.total || 0;
    const approved = props.all_users.approved_count
    const pending = props.pending_count || 0;
    const disapproved = props.disapproved_count || 0;

    return { total, approved, pending, disapproved };
});

const archiveUser = () => {
    if (userToArchive.value) {
        router.delete(route('admin.manage_users.delete', { user: userToArchive.value.id }), {
            onSuccess: () => {
                showModal.value = false;
                userToArchive.value = null;
            }
        });
    }
};

const confirmArchive = (user) => {
    console.log('Archive modal should open for:', user);
    userToArchive.value = user;
    showModal.value = true;
};

const approveUser = (user) => {
    router.post(route('admin.manage_users.approve', { user: user.id }), {});
};

const disapproveUser = (user) => {
    router.post(route('admin.manage_users.disapprove', { user: user.id }), {
        is_approved: false,
    });
};

const goTo = (url) => {
    router.get(url); // Use Inertia's router to navigate to the next/previous page
};

// Function to go back
const goBack = () => {
    window.history.back();
};

// Check if there are users
const hasUsers = computed(() => {
    return props.all_users.data && props.all_users.data.length > 0;
});

console.log('approved_count:', props.all_users.approved_count);

</script>

<template>
    <AppLayout title="Manage Users">
        <template #header>
            <div class="flex items-center">
                <h2 class="font-semibold text-xl text-gray-800 flex items-center">
                    <i class="fas fa-users text-blue-500 text-xl mr-2"></i> Manage Users
                </h2>
            </div>
        </template>

        <Container class="py-6 space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div
                    class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500 relative overflow-hidden transition-all duration-200 hover:shadow-md">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-gray-600 text-sm font-medium mb-2">Total Users</h3>
                            <p class="text-3xl font-bold text-blue-600">{{ stats.total }}</p>
                        </div>
                        <div class="bg-blue-100 rounded-full p-3 flex items-center justify-center">
                            <i class="fas fa-users text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500 relative overflow-hidden transition-all duration-200 hover:shadow-md">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-gray-600 text-sm font-medium mb-2">Approved</h3>
                            <p class="text-3xl font-bold text-green-600">{{ stats.approved }}</p>
                        </div>
                        <div class="bg-green-100 rounded-full p-3 flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600"></i>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500 relative overflow-hidden transition-all duration-200 hover:shadow-md">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-gray-600 text-sm font-medium mb-2">Pending</h3>
                            <p class="text-3xl font-bold text-yellow-600">{{ stats.pending }}</p>
                        </div>
                        <div class="bg-yellow-100 rounded-full p-3 flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600"></i>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500 relative overflow-hidden transition-all duration-200 hover:shadow-md">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-gray-600 text-sm font-medium mb-2">Disapproved</h3>
                            <p class="text-3xl font-bold text-red-600">{{ stats.disapproved }}</p>
                        </div>
                        <div class="bg-red-100 rounded-full p-3 flex items-center justify-center">
                            <i class="fas fa-times-circle text-red-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div
                class="bg-white rounded-lg shadow-sm p-4 flex flex-wrap items-center justify-between gap-4 transition-all duration-200 hover:shadow-md">
                <div class="flex items-center space-x-4">
                    <Link v-if="page.props.roles.isPeso" :href="route('admin.manage_users.list')"
                        :class="[route().current('admin.manage_users.list') ? 'bg-blue-50 text-blue-600 border-blue-200' : 'text-gray-600 hover:bg-gray-50 border-gray-200',
                            'px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border']">
                    <i class="fas fa-list mr-2"></i>
                    <span>List Of Users</span>
                    </Link>
                    <Link v-if="page.props.roles.isPeso" :href="route('admin.manage_users.archivedlist')"
                        :class="[route().current('admin.manage_users.archivedlist') ? 'bg-blue-50 text-blue-600 border-blue-200' : 'text-gray-600 hover:bg-gray-50 border-gray-200',
                            'px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border']">
                    <i class="fas fa-archive mr-2"></i>
                    <span>Archived Users</span>
                    </Link>
                </div>
                <Link v-if="page.props.roles.isPeso" :href="route('companies.batch.page')"
                    :class="[route().current('companies.batch.page') ? 'bg-blue-600' : 'bg-blue-500 hover:bg-blue-600',
                        'px-4 py-2 rounded-md text-white font-medium transition-colors flex items-center shadow-sm']">
                <i class="fas fa-upload mr-2"></i> Batch Upload Companies
                </Link>
            </div>

            <!-- Table Card -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
                <div class="p-4 flex items-center justify-between border-b border-gray-200">
                    <div class="flex items-center">
                        <i class="fas fa-table text-blue-500 mr-2"></i>
                        <h3 class="text-lg font-semibold text-gray-800">User Management</h3>
                        <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{
                            all_users.total }} total</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 tracking-wider">
                            <tr>
                                <th class="px-6 py-3 text-left">Role</th>
                                <th class="px-6 py-3 text-left">Organization</th>
                                <th class="px-6 py-3 text-left">Name</th>
                                <th class="px-6 py-3 text-left">Email</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="user in all_users.data" :key="user.id"
                                class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
                                            <i class="fas fa-user-tag"></i>x
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 capitalize">{{ user.role }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ user.organization_name
                                    || '-'
                                    }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{
                                    user.full_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ user.email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full flex items-center w-fit"
                                        :class="{
                                            'bg-green-100 text-green-800': user.is_approved === true,
                                            'bg-red-100 text-red-800': user.is_approved === false,
                                            'bg-yellow-100 text-yellow-800': user.is_approved === null
                                        }">
                                        <i :class="{
                                            'fas fa-check-circle mr-1': user.is_approved === true,
                                            'fas fa-times-circle mr-1': user.is_approved === false,
                                            'fas fa-clock mr-1': user.is_approved === null
                                        }"></i>
                                        {{ user.is_approved === true ? 'Approved' : (user.is_approved === false ?
                                        'Disapproved'
                                        : 'Pending') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button @click="approveUser(user)"
                                            v-if="user.is_approved !== false && !user.is_approved"
                                            class="text-green-500 hover:text-green-700 focus:outline-none p-1 hover:bg-green-50 rounded-full transition-colors"
                                            title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button @click="disapproveUser(user)"
                                            v-if="user.is_approved !== false && !user.is_approved"
                                            class="text-red-500 hover:text-red-700 focus:outline-none p-1 hover:bg-red-50 rounded-full transition-colors"
                                            title="Disapprove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <Link :href="route('admin.manage_users.edit', { user: user.id })"
                                            class="text-blue-500 hover:text-blue-700 focus:outline-none p-1 hover:bg-blue-50 rounded-full transition-colors"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                        </Link>
                                        <button @click="confirmArchive(user)" v-if="user.is_approved !== false"
                                            class="text-gray-500 hover:text-gray-700 focus:outline-none p-1 hover:bg-gray-100 rounded-full transition-colors"
                                            title="Archive">
                                            <i class="fas fa-archive"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="!hasUsers" class="py-12 text-center">
                    <i class="fas fa-users text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-700">No users found</h3>
                    <p class="text-gray-500 mt-1">Try adjusting your filters</p>
                </div>

                <!-- Pagination -->
                <div v-if="all_users.links && all_users.links.length > 3"
                    class="px-4 py-3 flex items-center justify-between border-t border-gray-200">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <a v-if="all_users.prev_page_url" @click.prevent="goTo(all_users.prev_page_url)"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 cursor-pointer">
                            <i class="fas fa-chevron-left mr-2"></i> Previous
                        </a>
                        <a v-if="all_users.next_page_url" @click.prevent="goTo(all_users.next_page_url)"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 cursor-pointer">
                            Next <i class="fas fa-chevron-right ml-2"></i>
                        </a>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing <span class="font-medium">{{ all_users.from }}</span> to <span
                                    class="font-medium">{{
                                    all_users.to }}</span> of <span class="font-medium">{{ all_users.total }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                aria-label="Pagination">
                                <a v-for="link in all_users.links" :key="link.url" v-html="link.label"
                                    @click.prevent="link.url ? goTo(link.url) : null" :class="[
                                        link.url ? 'cursor-pointer hover:bg-gray-50' : 'cursor-not-allowed',
                                        link.active ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500',
                                        link.label.includes('Previous') ? 'rounded-l-md' : '',
                                        link.label.includes('Next') ? 'rounded-r-md' : '',
                                        'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                                    ]"></a>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <ConfirmationModal :show="showModal" @close="showModal = false" @confirm="archiveUser">
                <template #title>
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center mr-4">
                            <i class="fas fa-exclamation-triangle text-red-500"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Confirm Archive</h3>
                    </div>
                </template>
                <template #content>
                    <p class="text-gray-600">Are you sure you want to archive the user
                        <strong>"{{ userToArchive?.full_name }}"</strong>? This action can be reversed later.
                    </p>
                </template>
                <template #footer>
                    <div class="flex justify-end space-x-3">
                        <SecondaryButton @click="showModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm transition-colors duration-200">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </SecondaryButton>
                        <DangerButton @click="archiveUser"
                            class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 text-sm transition-colors duration-200">
                            <i class="fas fa-archive mr-2"></i> Archive
                        </DangerButton>
                    </div>
                </template>
            </ConfirmationModal>
        </Container>
    </AppLayout>
</template>