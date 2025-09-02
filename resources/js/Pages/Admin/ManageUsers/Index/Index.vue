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
    const approved = props.all_users.data.filter(user => user.is_approved === true).length;
    const pending = props.all_users.data.filter(user => user.is_approved === null).length;
    const disapproved = props.all_users.data.filter(user => user.is_approved === false).length;
    
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
                <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-users text-white text-lg"></i>
                        </div>
                        <h3 class="text-blue-700 text-sm font-medium mb-2">Total Users</h3>
                        <p class="text-2xl font-bold text-blue-900">{{ stats.total }}</p>
                    </div>
                </div>
                
                <!-- Approved Card -->
                <div class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-check-circle text-white text-lg"></i>
                        </div>
                        <h3 class="text-green-700 text-sm font-medium mb-2">Approved</h3>
                        <p class="text-2xl font-bold text-green-900">{{ stats.approved }}</p>
                    </div>
                </div>
                
                <!-- Pending Card -->
                <div class="bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-clock text-white text-lg"></i>
                        </div>
                        <h3 class="text-yellow-700 text-sm font-medium mb-2">Pending</h3>
                        <p class="text-2xl font-bold text-yellow-900">{{ stats.pending }}</p>
                    </div>
                </div>
                
                <!-- Disapproved Card -->
                <div class="bg-gradient-to-br from-red-100 to-red-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
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
            <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-wrap items-center justify-between gap-4 border border-gray-100">
                <div class="flex items-center space-x-4">
                    <Link v-if="page.props.roles.isPeso" :href="route('admin.manage_users.list')"
                        :class="[route().current('admin.manage_users.list') ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200', 
                                'px-6 py-3 rounded-xl flex items-center space-x-2 font-medium transition-all duration-200 transform hover:scale-105']"
>
                        <i class="fas fa-list"></i>
                        <span>List Of Users</span>
                    </Link>
                    <Link v-if="page.props.roles.isPeso" :href="route('admin.manage_users.archivedlist')"
                        :class="[route().current('admin.manage_users.archivedlist') ? 'bg-gradient-to-r from-gray-500 to-gray-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200', 
                                'px-6 py-3 rounded-xl flex items-center space-x-2 font-medium transition-all duration-200 transform hover:scale-105']">
                        <i class="fas fa-archive"></i>
                        <span>Archived Users</span>
                    </Link>
                </div>
                <Link v-if="page.props.roles.isPeso" :href="route('companies.batch.page')"
                    :class="[route().current('companies.batch.page') ? 'bg-gradient-to-r from-green-600 to-green-700' : 'bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700', 
                            'px-6 py-3 rounded-xl text-white font-medium transition-all duration-200 flex items-center shadow-lg transform hover:scale-105']">
                    <i class="fas fa-upload mr-2"></i> Batch Upload Companies
                </Link>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-table text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">User Management</h3>
                            <span class="text-sm text-gray-500">{{ all_users.total }} total users</span>
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
                            <tr v-for="user in all_users.data" :key="user.id" class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group">
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center mr-3 group-hover:shadow-md transition-all duration-200">
                                            <i class="fas fa-user-tag text-white text-xs"></i>
                                        </div>
                                        <div class="ml-1">
                                            <div class="text-sm font-semibold text-gray-900 capitalize group-hover:text-blue-700 transition-colors">{{ user.role }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-600 font-medium">{{ user.organization_name || '-' }}</td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm font-semibold text-gray-900 group-hover:text-blue-700 transition-colors">{{ user.full_name }}</td>
                                <td class="px-6 py-5 whitespace-nowrap text-sm text-gray-600">{{ user.email }}</td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <span 
                                        class="px-3 py-1.5 text-xs font-semibold rounded-full flex items-center w-fit shadow-sm" 
                                        :class="{
                                            'bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 border border-green-200': user.is_approved === true,
                                            'bg-gradient-to-r from-red-100 to-rose-100 text-red-800 border border-red-200': user.is_approved === false,
                                            'bg-gradient-to-r from-yellow-100 to-amber-100 text-yellow-800 border border-yellow-200': user.is_approved === null
                                        }">
                                        <i :class="{
                                            'fas fa-check-circle mr-1.5': user.is_approved === true,
                                            'fas fa-times-circle mr-1.5': user.is_approved === false,
                                            'fas fa-clock mr-1.5': user.is_approved === null
                                        }"></i>
                                        {{ user.is_approved === true ? 'Approved' : (user.is_approved === false ? 'Disapproved' : 'Pending') }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button 
                                            @click="approveUser(user)"
                                            v-if="user.is_approved !== false && !user.is_approved"
                                            class="text-green-600 hover:text-white hover:bg-green-500 focus:outline-none p-2 rounded-full transition-all duration-200 shadow-sm hover:shadow-md"
                                            title="Approve">
                                            <i class="fas fa-check text-sm"></i>
                                        </button>
                                        <button 
                                            @click="disapproveUser(user)"
                                            v-if="user.is_approved !== false && !user.is_approved"
                                            class="text-red-600 hover:text-white hover:bg-red-500 focus:outline-none p-2 rounded-full transition-all duration-200 shadow-sm hover:shadow-md"
                                            title="Disapprove">
                                            <i class="fas fa-times text-sm"></i>
                                        </button>
                                        <Link 
                                            :href="route('admin.manage_users.edit', { user: user.id })"
                                            class="text-blue-600 hover:text-white hover:bg-blue-500 focus:outline-none p-2 rounded-full transition-all duration-200 shadow-sm hover:shadow-md"
                                            title="Edit">
                                            <i class="fas fa-edit text-sm"></i>
                                        </Link>
                                        <button 
                                            @click="confirmArchive(user)"
                                            v-if="user.is_approved !== false"
                                            class="text-gray-600 hover:text-white hover:bg-gray-500 focus:outline-none p-2 rounded-full transition-all duration-200 shadow-sm hover:shadow-md"
                                            title="Archive">
                                            <i class="fas fa-archive text-sm"></i>
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
                <div v-if="all_users.links && all_users.links.length > 3" class="px-6 py-4 flex items-center justify-center border-t border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <nav class="flex items-center space-x-2" aria-label="Pagination">
                        <!-- Previous Button -->
                        <button 
                            v-if="all_users.prev_page_url" 
                            @click="goTo(all_users.prev_page_url)" 
                            class="w-8 h-8 flex items-center justify-center rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 shadow-sm">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </button>
                        
                        <!-- Page Numbers -->
                        <template v-for="(link, index) in all_users.links" :key="index">
                            <button 
                                v-if="!link.label.includes('Previous') && !link.label.includes('Next') && link.label !== '...'" 
                                @click="link.url ? goTo(link.url) : null"
                                :class="[
                                    'w-8 h-8 flex items-center justify-center rounded-full text-sm font-medium transition-all duration-200',
                                    link.active 
                                        ? 'bg-blue-500 text-white shadow-md' 
                                        : 'bg-white border border-gray-200 text-gray-600 hover:bg-blue-50 hover:border-blue-300',
                                    link.url ? 'cursor-pointer' : 'cursor-not-allowed'
                                ]">
                                {{ link.label }}
                            </button>
                            <span 
                                v-else-if="link.label === '...'"
                                class="w-8 h-8 flex items-center justify-center text-gray-400 text-sm">
                                ...
                            </span>
                        </template>
                        
                        <!-- Next Button -->
                        <button 
                            v-if="all_users.next_page_url" 
                            @click="goTo(all_users.next_page_url)" 
                            class="w-8 h-8 flex items-center justify-center rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 shadow-sm">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </button>
                    </nav>
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
                    <p class="text-gray-700 text-base leading-relaxed">Are you sure you want to archive the user
                    <strong class="text-gray-900 font-semibold">"{{ userToArchive?.full_name }}"</strong>? This action can be reversed later.</p>
                </template>
                <template #footer>
                    <div class="flex justify-end space-x-4">
                        <SecondaryButton @click="showModal = false" class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 text-sm font-medium transition-all duration-200 shadow-sm">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </SecondaryButton>
                        <DangerButton @click="archiveUser" class="px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg hover:from-red-600 hover:to-red-700 text-sm font-medium transition-all duration-200 shadow-lg">
                            <i class="fas fa-archive mr-2"></i> Archive
                        </DangerButton>
                    </div>
                </template>
            </ConfirmationModal>
        </Container>
    </AppLayout>
</template>