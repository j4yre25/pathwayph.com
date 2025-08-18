<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import { router } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref, computed } from 'vue';


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




</script>


<template>
    <AppLayout title="List Of Users">
        <template #header>
            <div class="flex items-center">
                <button @click="() => window.history.back()" class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
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
                
                <div class="p-4">
                    <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-user-tag text-gray-400 mr-1"></i> User Level
                            </label>
                            <select v-model="filters.role" id="role"
                                class="block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                                <option value="all">All</option>
                                <option value="peso">Peso</option>
                                <option value="company">Company</option>
                                <option value="institution">Institution</option>
                            </select>
                    </div>

                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-calendar-alt text-gray-400 mr-1"></i> Date From
                            </label>
                            <input v-model="filters.date_from" type="date" id="date_from"
                                class="block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        </div>
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-calendar-alt text-gray-400 mr-1"></i> Date To
                            </label>
                            <input v-model="filters.date_to" type="date" id="date_to"
                                class="block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                                <i class="fas fa-info-circle text-gray-400 mr-1"></i> Status
                            </label>
                            <select v-model="filters.status" id="status"
                                class="block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                                <option value="all">All</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md text-sm flex items-center hover:bg-blue-600 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                                <i class="fas fa-search mr-2"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-200 flex items-center">
                    <i class="fas fa-table text-blue-500 mr-2"></i>
                    <h3 class="font-medium text-gray-700">User List</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left text-gray-700">
                        <thead class="bg-gray-100 text-xs uppercase tracking-wider text-gray-600">
                            <tr>
                                <th class="px-6 py-4">
                                    <i class="fas fa-user-tag mr-2"></i> User Level
                                </th>
                                <th class="px-6 py-4">
                                    <i class="fas fa-building mr-2"></i> Organization Name
                                </th>
                                <th class="px-6 py-4">
                                    <i class="fas fa-user mr-2"></i> Name
                                </th>
                                <th class="px-6 py-4">
                                    <i class="fas fa-calendar mr-2"></i> Date Creation
                                </th>
                                <th class="px-6 py-4">
                                    <i class="fas fa-info-circle mr-2"></i> Status
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in all_users.data" :key="user.id" class="border-t hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="capitalize">{{ user.role }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ user.organization_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap font-medium">
                                    {{ user.full_name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ new Date(user.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="{
                                            'bg-green-100 text-green-800': user.is_approved,
                                            'bg-red-100 text-red-800': !user.is_approved,
                                        }"
                                        class="px-2 py-1 rounded-full text-xs font-semibold flex items-center w-fit"
                                    >
                                        <i :class="user.is_approved ? 'fas fa-check-circle mr-1' : 'fas fa-times-circle mr-1'"></i>
                                        {{ user.is_approved ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="all_users.data.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-search text-gray-400 text-3xl mb-2"></i>
                                        <p>No users found matching your criteria</p>
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