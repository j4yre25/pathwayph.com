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
                    class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
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
                <div class="bg-gradient-to-br from-orange-100 to-orange-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center mb-3">
                             <i class="fas fa-archive  text-white text-lg2"></i>
                        </div>
                            <h3 class="text-orange-700 text-sm font-medium mb-2">Total Archived Users</h3>
                            <p class="text-2xl font-bold text-orange-900">{{ stats.total }}</p>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-table text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">Archived Users</h3>
                                <p class="text-sm text-gray-600">Manage archived user accounts</p>
                                <span class="text-sm font-semibold text-gray-700">{{ all_users.total }} total</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 text-sm font-semibold text-gray-700">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center">
                                        User Level
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center">
                                        Name
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center">
                                        Date Creation
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center">
                                        Status
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center justify-end">
                                        Actions
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <tr v-for="user in all_users.data" :key="user.id" class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group">
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center text-blue-600 shadow-sm group-hover:shadow-md transition-shadow">
                                            <i class="fas fa-user-tag"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900 capitalize">{{ user.role }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">{{ user.full_name }}</div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm text-gray-700 font-medium">{{ new Date(user.created_at).toLocaleDateString() }}</div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 shadow-sm">
                                        <i class="fas fa-archive mr-1.5"></i> Archived
                                    </span>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button 
                                            @click="confirmRestore(user)"
                                            class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-2 rounded-full hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md"
                                            title="Restore">
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
                <div v-if="all_users.links && all_users.links.length > 3" class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-700 font-medium">
                                Showing <span class="font-bold text-blue-600">{{ all_users.from || 0 }}</span> to <span class="font-bold text-blue-600">{{ all_users.to || 0 }}</span> of <span class="font-bold text-blue-600">{{ all_users.total }}</span> results
                            </span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <!-- Previous Button -->
                            <button 
                                v-if="all_users.prev_page_url" 
                                @click="goTo(all_users.prev_page_url)" 
                                class="w-10 h-10 rounded-full bg-white border border-gray-300 text-gray-600 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600 transition-all duration-200 flex items-center justify-center shadow-sm hover:shadow-md"
                                title="Previous Page">
                                <i class="fas fa-chevron-left text-sm"></i>
                            </button>
                            
                            <!-- Page Numbers -->
                            <template v-for="link in all_users.links" :key="link.url">
                                <button 
                                    v-if="!link.label.includes('Previous') && !link.label.includes('Next')"
                                    @click="link.url ? goTo(link.url) : null"
                                    :class="[
                                        'w-10 h-10 rounded-full text-sm font-semibold transition-all duration-200 flex items-center justify-center shadow-sm',
                                        link.active 
                                            ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-md hover:shadow-lg' 
                                            : 'bg-white border border-gray-300 text-gray-600 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600 hover:shadow-md',
                                        link.url ? 'cursor-pointer' : 'cursor-not-allowed opacity-50'
                                    ]"
                                    v-html="link.label">
                                </button>
                            </template>
                            
                            <!-- Next Button -->
                            <button 
                                v-if="all_users.next_page_url" 
                                @click="goTo(all_users.next_page_url)" 
                                class="w-10 h-10 rounded-full bg-white border border-gray-300 text-gray-600 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600 transition-all duration-200 flex items-center justify-center shadow-sm hover:shadow-md"
                                title="Next Page">
                                <i class="fas fa-chevron-right text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <ConfirmationModal :show="showModal" @close="showModal = false" @confirm="restoreUser">
                <template #title>
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-red-100 to-red-200 flex items-center justify-center mr-4 shadow-lg">
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
                    <div class="flex justify-end space-x-4">
                        <button 
                            @click="showModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm transition-colors duration-200 flex items-center">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button 
                            @click="restoreUser"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm transition-colors duration-200 flex items-center">
                            <i class="fas fa-undo mr-2"></i> Restore
                        </button>
                    </div>
                </template>
            </ConfirmationModal>
        </Container>
    </AppLayout>
</template>