<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref, computed } from 'vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
    all_users: Object,// Paginated data
});

const showModal = ref(false);
const userToRestore = ref(null);

// Stats for cards
const stats = computed(() => {
    return {
        total: props.all_users.total || 0
    };
});

// Check if there are users
const hasUsers = computed(() => {
    return props.all_users.data && props.all_users.data.length > 0;
});

// Function to restore a user
const restoreUser = () => {
    if (userToRestore.value) {
        router.post(route('admin.manage_users.restore', { user: userToRestore.value.id }), {});
        showModal.value = false;
        userToRestore.value = null;
    }
};

// Function to confirm restoration
const confirmRestore = (user) => {
    userToRestore.value = user;
    showModal.value = true;
};

// Function to navigate to a specific page
const goTo = (url) => {
    router.get(url);
};

// Function to go back
const goBack = () => {
    window.history.back();
};
</script>

<template>
    <AppLayout title="Archived Users">
        <template #header>
            <div class="flex items-center">
                <button 
                    @click="goBack"
                    class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none"
                >
                    <i class="fas fa-chevron-left"></i>
                </button>
                <h2 class="font-semibold text-xl text-gray-800 flex items-center">
                    <i class="fas fa-archive text-blue-500 text-xl mr-2"></i> Archived Users
                </h2>
            </div>
        </template>

        <Container class="py-6 space-y-6">
            <!-- Stats Card -->
            <div class="grid grid-cols-1 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-gray-500 relative overflow-hidden transition-all duration-200 hover:shadow-md">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-gray-600 text-sm font-medium mb-2">Total Archived Users</h3>
                            <p class="text-3xl font-bold text-gray-600">{{ stats.total }}</p>
                        </div>
                        <div class="bg-gray-100 rounded-full p-3 flex items-center justify-center">
                            <i class="fas fa-archive text-gray-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
                <div class="p-4 flex items-center justify-between border-b border-gray-200">
                    <div class="flex items-center">
                        <i class="fas fa-table text-blue-500 mr-2"></i>
                        <h3 class="text-lg font-semibold text-gray-800">Archived Users</h3>
                        <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ all_users.total }} total</span>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 tracking-wider">
                            <tr>
                                <th class="px-6 py-3 text-left">User Level</th>
                                <th class="px-6 py-3 text-left">Name</th>
                                <th class="px-6 py-3 text-left">Date Creation</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="user in all_users.data" :key="user.id" class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
                                            <i class="fas fa-user-tag"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 capitalize">{{ user.role }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.full_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ new Date(user.created_at).toLocaleDateString() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800 flex items-center w-fit">
                                        <i class="fas fa-archive mr-1"></i> Archived
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button 
                                            @click="confirmRestore(user)"
                                            class="text-blue-500 hover:text-blue-700 focus:outline-none p-1 hover:bg-blue-50 rounded-full transition-colors"
                                            title="Restore"
                                        >
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Empty State -->
                <div v-if="all_users.data.length === 0" class="py-12 text-center">
                    <i class="fas fa-archive text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-700">No archived users found</h3>
                    <p class="text-gray-500 mt-1">When users are archived, they will appear here</p>
                </div>
                
                <!-- Pagination -->
                <div v-if="all_users.links && all_users.links.length > 3" class="px-4 py-3 flex items-center justify-between border-t border-gray-200">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <a 
                            v-if="all_users.prev_page_url" 
                            @click.prevent="goTo(all_users.prev_page_url)" 
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 cursor-pointer"
                        >
                            <i class="fas fa-chevron-left mr-2"></i> Previous
                        </a>
                        <a 
                            v-if="all_users.next_page_url" 
                            @click.prevent="goTo(all_users.next_page_url)" 
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 cursor-pointer"
                        >
                            Next <i class="fas fa-chevron-right ml-2"></i>
                        </a>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing <span class="font-medium">{{ all_users.from || 0 }}</span> to <span class="font-medium">{{ all_users.to || 0 }}</span> of <span class="font-medium">{{ all_users.total }}</span> results
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
            <ConfirmationModal :show="showModal" @close="showModal = false" @confirm="restoreUser">
                <template #title>
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                            <i class="fas fa-question-circle text-blue-500"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Confirm Restore</h3>
                    </div>
                </template>
                <template #content>
                    <p class="text-gray-600">Are you sure you want to restore the user
                    <strong>"{{ userToRestore?.full_name }}"</strong>? This will make the user visible again.</p>
                </template>
                <template #footer>
                    <div class="flex justify-end space-x-3">
                        <button 
                            @click="showModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm transition-colors duration-200 flex items-center"
                        >
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button 
                            @click="restoreUser"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm transition-colors duration-200 flex items-center"
                        >
                            <i class="fas fa-undo mr-2"></i> Restore
                        </button>
                    </div>
                </template>
            </ConfirmationModal>
        </Container>
    </AppLayout>
</template>