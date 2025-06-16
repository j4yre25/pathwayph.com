<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Link } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import { router } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref, computed } from 'vue';
import '@fortawesome/fontawesome-free/css/all.css';


const page = usePage();

const props = defineProps({
    sectors: Array
})


const showModal = ref(false);
const selectedStatus = ref('all');
const appliedStatus = ref('all');
const selectedSector = ref(null);

// Function to go back to previous page
const goBack = () => {
    window.history.back();
};

const filteredSectors = computed(() => {
    if (appliedStatus.value === 'all') {
        return props.sectors;
    }
    return props.sectors.filter(sector => {
        return appliedStatus.value === 'active' ? !sector.deleted_at : sector.deleted_at;
    });
});

// Check if there are sectors
const hasSectors = computed(() => {
    return filteredSectors.value && filteredSectors.value.length > 0;
});

// Stats for display
const totalSectors = computed(() => props.sectors.length);
const activeSectors = computed(() => props.sectors.filter(sector => !sector.deleted_at).length);
const inactiveSectors = computed(() => props.sectors.filter(sector => sector.deleted_at).length);

function applyFilter() {
    appliedStatus.value = selectedStatus.value;
}


</script>


<template>
    <AppLayout title="Sectors">
        <Container>
            <!-- Header with back button -->
            <div class="flex items-center mb-6">
                <button @click="goBack" class="mr-4 text-blue-600 hover:text-blue-800 focus:outline-none">
                    <i class="fas fa-chevron-left text-lg"></i>
                </button>
                <h1 class="text-2xl font-semibold text-gray-800">Sectors</h1>
            </div>
            
            <!-- Stats Summary -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500 hover:shadow-md transition-shadow duration-300">
                    <h3 class="text-gray-600 text-sm font-medium mb-2">Total Sectors</h3>
                    <p class="text-3xl font-bold text-blue-600">
                        {{ totalSectors }}
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500 hover:shadow-md transition-shadow duration-300">
                    <h3 class="text-gray-600 text-sm font-medium mb-2">Active Sectors</h3>
                    <p class="text-3xl font-bold text-green-600">
                        {{ activeSectors }}
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500 hover:shadow-md transition-shadow duration-300">
                    <h3 class="text-gray-600 text-sm font-medium mb-2">Inactive Sectors</h3>
                    <p class="text-3xl font-bold text-red-600">
                        {{ inactiveSectors }}
                    </p>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
                <!-- Filter Controls -->
                <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-200">
                    <div class="flex items-center">
                        <i class="fas fa-industry text-blue-500 mr-2"></i>
                        <h3 class="text-lg font-semibold text-gray-800">Sectors List</h3>
                        <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ filteredSectors.length }} total</span>
                    </div>
                    <div class="flex items-center mt-3 sm:mt-0 w-full sm:w-auto">
                        <label for="statusFilter" class="mr-2 text-sm font-medium text-gray-700">Status:</label>
                        <select id="statusFilter" v-model="selectedStatus" class="border border-gray-300 rounded-md px-3 py-2 mr-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <PrimaryButton @click="applyFilter" class="text-sm px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                            <i class="fas fa-filter mr-2"></i> Apply
                        </PrimaryButton>
                    </div>
                </div>

                <!-- Sectors Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 tracking-wider">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">Name</th>
                                <th class="px-6 py-3 text-left font-medium">Posted By</th>
                                <th class="px-6 py-3 text-left font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="sector in filteredSectors" :key="sector.id" class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
                                            <i class="fas fa-industry"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ sector.name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ sector.user ? sector.user.peso_first_name : 'Unknown' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[sector.deleted_at ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800', 'px-2 py-1 text-xs rounded-full font-medium']">
                                        {{ sector.deleted_at ? 'Inactive' : 'Active' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Empty State -->
                <div v-if="filteredSectors.length === 0" class="py-12 text-center">
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                            <i class="fas fa-industry text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-700">No sectors found</h3>
                        <p class="text-gray-500 mt-1">Try changing your filter settings</p>
                    </div>
                </div>
            </div>
            <ConfirmationModal :show="showModal" @close="showModal = false" @confirm="handleDelete">
                <template #title>
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                            <i class="fas fa-question-circle text-blue-500"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Confirm Deletion</h3>
                    </div>
                </template>
                <template #content>
                    <p class="text-gray-600">Are you sure you want to delete this sector? This action cannot be undone.</p>
                </template>
                <template #footer>
                    <div class="flex justify-end space-x-3">
                        <SecondaryButton @click="showModal = false" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-opacity-50">
                            Cancel
                        </SecondaryButton>
                        <DangerButton @click="handleDelete" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                            Delete
                        </DangerButton>
                    </div>
                </template>
            </ConfirmationModal>


        </Container>
    </AppLayout>




</template>