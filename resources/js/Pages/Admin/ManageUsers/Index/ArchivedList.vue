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
        <!-- Font Awesome CSS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
        
        <template #header>
            <div class="flex items-center">
                <button 
                    @click="router.get(route('admin.manage_users.index'))"
                    class="mr-3 p-1 rounded-full hover:bg-gray-200 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50"
                >
                    <i class="fas fa-arrow-left text-gray-600"></i>
                </button>
                <i class="fas fa-user-shield text-gray-700 mr-2"></i>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Archived Users</h2>
            </div>
        </template>

        <Container class="py-8">
            <!-- Users Table Card -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-700 flex items-center">
                        <i class="fas fa-archive text-gray-500 mr-2"></i>
                        Archived Users List
                    </h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-sm leading-normal">
                                <th class="px-6 py-3 text-left font-medium">
                                    <div class="flex items-center">
                                        <i class="fas fa-user-tag text-gray-400 mr-2"></i>
                                        User Level
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left font-medium">
                                    <div class="flex items-center">
                                        <i class="fas fa-user text-gray-400 mr-2"></i>
                                        Name
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left font-medium">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-alt text-gray-400 mr-2"></i>
                                        Date Creation
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left font-medium">
                                    <div class="flex items-center">
                                        <i class="fas fa-info-circle text-gray-400 mr-2"></i>
                                        Status
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left font-medium">
                                    <div class="flex items-center">
                                        <i class="fas fa-cog text-gray-400 mr-2"></i>
                                        Actions
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            <tr v-for="user in all_users.data" :key="user.id"
                                class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">{{ user.role }}</td>
                                <td class="px-6 py-4">
                                    {{ user.full_name }}
                                </td>
                                <td class="px-6 py-4">
                                    {{ new Date(user.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-archive mr-1"></i>
                                        Archived
                                    </span>
                                </td>
                                <td class="px-6 py-4">   
                                    <button 
                                        @click="confirmRestore(user)"
                                        class="inline-flex items-center px-3 py-1.5 bg-green-500 text-white text-xs rounded-md hover:bg-green-600 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50"
                                    >
                                        <i class="fas fa-undo-alt mr-1"></i>
                                        Restore
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="all_users.data.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-gray-400 text-3xl mb-3 block"></i>
                                    <p>No archived users found</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-6" v-if="all_users.links && all_users.links.length > 3">
                <!-- Mobile pagination (simple prev/next) -->
                <div class="flex justify-between items-center md:hidden">
                    <button 
                        v-if="all_users.prev_page_url"
                        @click="goTo(all_users.prev_page_url)"
                        class="px-4 py-2 text-sm bg-white border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
                    >
                        <i class="fas fa-chevron-left mr-2"></i> Previous
                    </button>
                    <div v-else class="invisible">Placeholder</div>
                    
                    <button 
                        v-if="all_users.next_page_url"
                        @click="goTo(all_users.next_page_url)"
                        class="px-4 py-2 text-sm bg-white border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center"
                    >
                        Next <i class="fas fa-chevron-right ml-2"></i>
                    </button>
                    <div v-else class="invisible">Placeholder</div>
                </div>
                
                <!-- Desktop pagination (full) -->
                <div class="hidden md:flex md:items-center md:justify-between">
                    <div class="text-sm text-gray-500">
                        Showing <span class="font-medium">{{ all_users.from || 0 }}</span> to <span class="font-medium">{{ all_users.to || 0 }}</span> of <span class="font-medium">{{ all_users.total }}</span> results
                    </div>
                    
                    <nav v-if="all_users.links.length > 1" aria-label="Pagination" class="inline-flex shadow-sm -space-x-px rounded-md">
                        <template v-for="(link, index) in all_users.links" :key="index">
                            <a v-if="link.url && index !== 0 && index !== all_users.links.length - 1" 
                                @click.prevent="goTo(link.url)"
                                :class="[
                                    'relative inline-flex items-center px-4 py-2 text-sm font-medium border',
                                    link.active ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                    index === 1 ? 'rounded-l-md' : '',
                                    index === all_users.links.length - 2 ? 'rounded-r-md' : ''
                                ]"
                            >
                                {{ link.label.replace('&laquo;', '').replace('&raquo;', '') }}
                            </a>
                            <a v-else-if="link.url && index === 0" 
                                @click.prevent="goTo(link.url)"
                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                            >
                                <span class="sr-only">Previous</span>
                                <i class="fas fa-chevron-left text-xs"></i>
                            </a>
                            <a v-else-if="link.url && index === all_users.links.length - 1" 
                                @click.prevent="goTo(link.url)"
                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                            >
                                <span class="sr-only">Next</span>
                                <i class="fas fa-chevron-right text-xs"></i>
                            </a>
                        </template>
                    </nav>
                </div>
            </div>
            
            <!-- Confirmation Modal -->
            <ConfirmationModal :show="showModal" @close="showModal = false" @confirm="restoreUser">
                <template #title>
                    <div class="flex items-center">
                        <i class="fas fa-undo-alt text-green-500 mr-2"></i>
                        <span>Confirm Restore</span>
                    </div>
                </template>
                <template #content>
                    <p class="text-gray-600">
                        Are you sure you want to restore this user?
                        <br>
                        This will make the user visible in the main users list again.
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
                            @click="restoreUser"
                            class="px-4 py-2 bg-green-500 text-white rounded-md text-sm flex items-center hover:bg-green-600 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50"
                        >
                            <i class="fas fa-undo-alt mr-2"></i> Restore
                        </button>
                    </div>
                </template>
            </ConfirmationModal>
        </Container>
    </AppLayout>
</template>