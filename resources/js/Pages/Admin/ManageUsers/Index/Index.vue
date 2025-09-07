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
    const approved = props.all_users.approved_count || 0;
    const pending = props.all_users.pending_count || 0;
    const disapproved = props.all_users.disapproved_count || 0;

    return { total, approved, pending, disapproved };
});

function getVerificationFileUrl(institution) {
    return `/storage/${institution.verification_file_path}`;
}

function isImage(filePath) {
    return /\.(jpg|jpeg|png|gif)$/i.test(filePath);
}

function isPDF(filePath) {
    return /\.pdf$/i.test(filePath);
}

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


const showVerificationModal = ref(false);
const userToVerify = ref(null);

function openVerificationModal(user) {
    userToVerify.value = user;
    showVerificationModal.value = true;
}

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
    router.get(url);
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
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total Users Card -->
                <div
                    class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-users text-white text-lg"></i>
                        </div>
                        <h3 class="text-blue-700 text-sm font-medium mb-2">Total Users</h3>
                        <p class="text-2xl font-bold text-blue-900">{{ stats.total }}</p>
                    </div>
                </div>

                <!-- Approved Card -->
                <div
                    class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-check-circle text-white text-lg"></i>
                        </div>
                        <h3 class="text-green-700 text-sm font-medium mb-2">Approved</h3>
                        <p class="text-2xl font-bold text-green-900">{{ stats.approved }}</p>
                    </div>
                </div>

                <!-- Pending Card -->
                <div
                    class="bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-clock text-white text-lg"></i>
                        </div>
                        <h3 class="text-yellow-700 text-sm font-medium mb-2">Pending</h3>
                        <p class="text-2xl font-bold text-yellow-900">{{ stats.pending }}</p>
                    </div>
                </div>

                <!-- Disapproved Card -->
                <div
                    class="bg-gradient-to-br from-red-100 to-red-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-times-circle text-white text-lg"></i>
                        </div>
                        <h3 class="text-red-700 text-sm font-medium mb-2">Disapproved</h3>
                        <p class="text-2xl font-bold text-red-900">{{ stats.disapproved }}</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div
                class="bg-white rounded-2xl shadow-lg p-6 flex flex-wrap items-center justify-between gap-4 border border-gray-100">
                <div class="flex items-center space-x-4">
                    <Link v-if="page.props.roles.isPeso" :href="route('admin.manage_users.list')" :class="[route().current('admin.manage_users.list') ? 'bg-blue-50 text-blue-600 border-blue-200' : 'text-gray-600 hover:bg-gray-50 border-gray-200',
                        'px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border']">
                    <i class="fas fa-list mr-2"></i>
                    <span>List Of Users</span>
                    </Link>
                    <Link v-if="page.props.roles.isPeso" :href="route('admin.manage_users.archivedlist')" :class="[route().current('admin.manage_users.archivedlist') ? 'bg-blue-50 text-blue-600 border-blue-200' : 'text-gray-600 hover:bg-gray-50 border-gray-200',
                        'px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border']">
                    <i class="fas fa-archive mr-2"></i>
                    <span>Archived Users</span>
                    </Link>
                </div>
                <Link v-if="page.props.roles.isPeso" :href="route('companies.batch.page')" :class="[route().current('companies.batch.page') ? 'bg-blue-600' : 'bg-blue-500 hover:bg-blue-600',
                    'px-4 py-2 rounded-md text-white font-medium transition-colors flex items-center shadow-sm']">
                <i class="fas fa-upload mr-2"></i> Batch Upload Companies
                </Link>
            </div>

            <!-- Table Card -->
            <div
                class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-table text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">User Management</h3>
                            <span class="text-sm text-gray-500">{{
                            all_users.total }} total users</span>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 text-sm font-semibold text-gray-700">
                            <tr>
                                <th class="px-6 py-4 text-left">Role</th>
                                <th class="px-6 py-4 text-left">Organization</th>
                                <th class="px-6 py-4 text-left">Name</th>
                                <th class="px-6 py-4 text-left">Email</th>
                                <th class="px-6 py-4 text-left">Status</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <tr v-for="user in all_users.data" :key="user.id"
                                class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group">
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center mr-3 group-hover:shadow-md transition-all duration-200">
                                            <i class="fas fa-user-tag text-white text-xs"></i>
                                        </div>
                                        <div class="ml-1">
                                            <div class="text-sm font-semibold text-gray-900 capitalize group-hover:text-blue-700 transition-colors">{{ user.role }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-600 font-medium">{{ user.organization_name
                                    || '-'
                                    }}</td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm font-semibold text-gray-900 group-hover:text-blue-700 transition-colors">{{
                                    user.full_name }}
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-600">{{ user.email }}</td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <span class="px-3 py-1.5 text-xs font-semibold rounded-full flex items-center w-fit shadow-sm"
                                        :class="{
                                            'bg-green-100 text-green-800': user.is_approved === true,
                                            'bg-red-100 text-red-800': user.is_approved === false,
                                            'bg-yellow-100 text-yellow-800': user.is_approved === null
                                        }">
                                        <i :class="{
                                            'fas fa-check-circle mr-1.5': user.is_approved === true,
                                            'fas fa-times-circle mr-1.5': user.is_approved === false,
                                            'fas fa-clock mr-1.5': user.is_approved === null
                                        }"></i>
                                        {{ user.is_approved === true ? 'Approved' : (user.is_approved === false ?
                                            'Disapproved'
                                            : 'Pending') }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
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
                                        <button @click="openVerificationModal(user)"
                                            class="text-blue-500 hover:text-blue-700 focus:outline-none p-1 hover:bg-blue-50 rounded-full transition-colors"
                                            title="Open Verification">
                                            <i class="fas fa-eye"></i>
                                        </button>
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
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-red-100 to-red-200 flex items-center justify-center mr-4 shadow-lg">
                            <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800">Confirm Archive</h3>
                    </div>
                </template>
                <template #content>
                    <p class="text-gray-600">Are you sure you want to archive the user
                        <strong>"{{ userToArchive?.full_name }}"</strong>? This action can be reversed later.
                    </p>
                </template>
                <template #footer>
                    <div class="flex justify-end space-x-4">
                        <SecondaryButton @click="showModal = false"
                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 text-sm font-medium transition-all duration-200 shadow-sm">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </SecondaryButton>
                        <DangerButton @click="archiveUser"
                            class="px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg hover:from-red-600 hover:to-red-700 text-sm font-medium transition-all duration-200 shadow-lg">
                            <i class="fas fa-archive mr-2"></i> Archive
                        </DangerButton>
                    </div>
                </template>
            </ConfirmationModal>


            <ConfirmationModal :show="showVerificationModal" @close="showVerificationModal = false">
                <template #title>
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                            <i class="fas fa-eye text-blue-500"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">User Verification</h3>
                    </div>
                </template>
                <template #content>
                    <p class="text-gray-600">
                        Check verification details for <strong>{{ userToVerify?.full_name }}</strong>.
                    </p>
                    <!-- Preview Section -->
                    <div v-if="userToVerify?.institution?.verification_file_path" class="mt-4">
                        <template v-if="isImage(userToVerify.institution.verification_file_path)">
                            <img :src="getVerificationFileUrl(userToVerify.institution)" alt="Verification File Preview"
                                class="max-w-full h-auto rounded border" />
                        </template>
                        <template v-else-if="isPDF(userToVerify.institution.verification_file_path)">
                            <iframe :src="getVerificationFileUrl(userToVerify.institution)"
                                class="w-full h-64 border rounded"></iframe>
                        </template>
                        <template v-else>
                            <a :href="getVerificationFileUrl(userToVerify.institution)" target="_blank"
                                class="text-blue-600 underline">Preview File</a>
                        </template>
                    </div>

                    <div v-if="userToVerify?.company?.verification_file_path" class="mt-4">
                        <template v-if="isImage(userToVerify.company.verification_file_path)">
                            <img :src="`/storage/${userToVerify.company.verification_file_path}`"
                                alt="Verification File Preview" class="max-w-full h-auto rounded border" />
                        </template>
                        <template v-else-if="isPDF(userToVerify.company.verification_file_path)">
                            <iframe :src="`/storage/${userToVerify.company.verification_file_path}`"
                                class="w-full h-64 border rounded"></iframe>
                        </template>
                        <template v-else>
                            <a :href="`/storage/${userToVerify.company.verification_file_path}`" target="_blank"
                                class="text-blue-600 underline">Preview File</a>
                        </template>
                    </div>
                </template>
                <template #footer>
                    <div class="flex justify-end space-x-3">
                        <SecondaryButton @click="showVerificationModal = false">Close</SecondaryButton>
                        <a v-if="userToVerify?.institution?.verification_file_path"
                            :href="route('institutions.downloadVerification', userToVerify.institution.id)"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm transition-colors duration-200"
                            target="_blank" download>
                            <i class="fas fa-download mr-2"></i> Download Verification File
                        </a>

                        <a v-if="userToVerify?.company?.verification_file_path"
                            :href="route('companies.downloadVerification', userToVerify.company.id)"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm transition-colors duration-200"
                            target="_blank" download>
                            <i class="fas fa-download mr-2"></i> Download Verification File
                        </a>
                    </div>
                </template>
            </ConfirmationModal>

        </Container>
    </AppLayout>
</template>