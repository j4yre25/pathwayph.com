<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import { router } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref, computed, watch } from 'vue';


const page = usePage();



const props = defineProps({
    all_users: Object,
    // user: Object

})

console.log(props.all_users);


const filters = ref({
    role: 'all',
    date_from: '',
    date_to: '',
    status: 'all',

});


const applyFilters = () => {
    const activeFilters = {};

    if (filters.value.role !== 'all') {
        activeFilters.role = filters.value.role;
    }
    if (filters.value.date_from) {
        activeFilters.date_from = filters.value.date_from;
    }
    if (filters.value.date_to) {
        activeFilters.date_to = filters.value.date_to;
    }
    if (filters.value.status !== 'all') {
        activeFilters.status = filters.value.status;
    }

    console.log('Active Filters:', activeFilters);

    router.get(route('admin.manage_users.list'), activeFilters, { preserveState: true });
};

// Watch for filter changes and apply automatically
watch(filters, () => {
    applyFilters();
}, { deep: true });


const filteredUsers = computed(() => {
    return props.all_users.filter(user => {
        const matchesRole = filters.value.role === 'all' || user.role === filters.value.role;
        const matchesDateFrom = !filters.value.date_from || new Date(user.created_at) >= new Date(filters.value.date_from);
        const matchesDateTo = !filters.value.date_to || new Date(user.created_at) <= new Date(filters.value.date_to);
        const matchesStatus = filters.value.status === 'all' ||
            (filters.value.status === 'active' && user.is_approved) ||
            (filters.value.status === 'inactive' && !user.is_approved);

        return matchesRole && matchesDateFrom && matchesDateTo && matchesStatus;
    });
});

const showModal = ref(false);

const goTo = (url) => {
    router.get(url); // Use Inertia's router to navigate to the next/previous page
};

const goBack = () => {
    window.history.back();
};




</script>


<template>
    <AppLayout title="List Of Users">
        <template #header>
            <div class="flex items-center">
                 <button 
                    @click="goBack"
                    class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-list text-blue-500 text-xl mr-2"></i> List of Users
                </h2>
            </div>
        </template>

        <!-- Add FontAwesome CSS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

        <Container class="py-6 space-y-6">
            <!-- Filter Card -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-200 flex items-center">
                    <i class="fas fa-filter text-blue-500 mr-2"></i>
                    <h3 class="font-medium text-gray-700">Filter Users</h3>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label for="role" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-user-tag text-blue-500 mr-2"></i> User Level
                            </label>
                            <select v-model="filters.role" id="role"
                                class="block w-full border border-gray-200 rounded-lg shadow-sm px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all duration-200 bg-white hover:border-blue-300">
                                <option value="all">All Levels</option>
                                <option value="peso">Peso</option>
                                <option value="company">Company</option>
                                <option value="institution">Institution</option>
                            </select>
                        </div>

                        <div>
                            <label for="date_from" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-calendar-alt text-blue-500 mr-2"></i> Date From
                            </label>
                            <input v-model="filters.date_from" type="date" id="date_from"
                                class="block w-full border border-gray-200 rounded-lg shadow-sm px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all duration-200 bg-white hover:border-blue-300">
                        </div>
                        
                        <div>
                            <label for="date_to" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-calendar-alt text-blue-500 mr-2"></i> Date To
                            </label>
                            <input v-model="filters.date_to" type="date" id="date_to"
                                class="block w-full border border-gray-200 rounded-lg shadow-sm px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all duration-200 bg-white hover:border-blue-300">
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <i class="fas fa-info-circle text-blue-500 mr-2"></i> Status
                            </label>
                            <select v-model="filters.status" id="status"
                                class="block w-full border border-gray-200 rounded-lg shadow-sm px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all duration-200 bg-white hover:border-blue-300">
                                <option value="all">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-table text-white"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">User List</h3>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 text-sm font-semibold text-gray-700">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        User Level
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        Organization
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        Full Name
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <div class="flex items-center">                                        
                                        Date Created
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <div class="flex items-center">
                                        Status
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <tr v-for="user in all_users.data" :key="user.id" class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center mr-3 group-hover:shadow-md transition-all duration-200">
                                            <i class="fas fa-user-tag text-white text-xs"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 capitalize">{{ user.role }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-700">{{ user.organization_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900 hover:text-blue-600 transition-colors duration-200">{{ user.full_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-600">{{ new Date(user.created_at).toLocaleDateString() }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="{
                                            'bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 border-green-200': user.is_approved,
                                            'bg-gradient-to-r from-red-100 to-rose-100 text-red-800 border-red-200': !user.is_approved,
                                        }"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border shadow-sm"
                                    >
                                        <i :class="user.is_approved ? 'fas fa-check-circle mr-1.5' : 'fas fa-times-circle mr-1.5'"></i>
                                        {{ user.is_approved ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="all_users.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-search text-gray-400 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 text-lg font-medium">No users found</p>
                                        <p class="text-gray-400 text-sm mt-1">Try adjusting your search criteria</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div v-if="all_users.links && all_users.links.length > 3" class="px-6 py-4 flex items-center justify-center border-t border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <nav class="flex items-center space-x-2" aria-label="Pagination">
                        <!-- Previous Button -->
                        <button 
                            v-if="all_users.prev_page_url" 
                            @click="goTo(all_users.prev_page_url)" 
                            class="w-8 h-8 flex items-center justify-center rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 shadow-sm"
                        >
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
                                ]"
                            >
                                {{ link.label }}
                            </button>
                            <span 
                                v-else-if="link.label === '...'"
                                class="w-8 h-8 flex items-center justify-center text-gray-400 text-sm"
                            >
                                ...
                            </span>
                        </template>
                        
                        <!-- Next Button -->
                        <button 
                            v-if="all_users.next_page_url" 
                            @click="goTo(all_users.next_page_url)" 
                            class="w-8 h-8 flex items-center justify-center rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 shadow-sm"
                        >
                            <i class="fas fa-chevron-right text-xs"></i>
                        </button>
                    </nav>
                </div>
            </div>
            
            <!-- Confirmation Modal -->
            <ConfirmationModal :show="showModal" @close="showModal = false" @confirm="handleDelete">
                <template #title>
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                        <span>Confirm Deletion</span>
                    </div>
                </template>
                <template #content>
                    <p class="text-gray-600">
                        Are you sure you want to delete this user?
                        <br>
                        This action cannot be undone.
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
                            @click="handleDelete"
                            class="px-4 py-2 bg-red-500 text-white rounded-md text-sm flex items-center hover:bg-red-600 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50"
                        >
                            <i class="fas fa-trash-alt mr-2"></i> Delete
                        </button>
                    </div>
                </template>
            </ConfirmationModal>


        </Container>
    </AppLayout>




</template>