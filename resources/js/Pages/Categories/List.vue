<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage } from '@inertiajs/vue3';
import { ref, computed} from 'vue';
import { router } from '@inertiajs/vue3';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
    categories: Array,
    sectors: Array
});

// Function to go back to previous page
const goBack = () => {
    window.history.back();
};

const selectedStatus = ref('all'); // Default filter value
const selectedSector = ref(null);
const showDeleteModal = ref(false);
const selectedCategory = ref(null);

// Stats for display
const totalCategories = computed(() => props.categories.length);
const activeCategories = computed(() => props.categories.filter(category => !category.deleted_at).length);
const inactiveCategories = computed(() => props.categories.filter(category => category.deleted_at).length);

function applyFilter() {
    router.get(route('categories.list'), {
        status: selectedStatus.value,
        sector: selectedSector.value,
    }, { preserveState: true });
}

function confirmDelete(category) {
    selectedCategory.value = category;
    showDeleteModal.value = true;
}

function deleteCategory() {
    if (selectedCategory.value) {
        router.delete(route('categories.destroy', selectedCategory.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false;
                selectedCategory.value = null;
            }
        });
    }
}

const categoriesWithSectorNames = computed(() => {
    return props.categories.map(category => {
        const sector = props.sectors.find(sector => sector.id === category.sector_id);
        return {
            ...category,
            sectorName: sector ? sector.name : 'No Sector' // Add sectorName to each category
        };
    });
});

// Check if there are any categories to display
const hasCategories = computed(() => categoriesWithSectorNames.value.length > 0);
</script>

<template>
    <AppLayout title="Categories">
        <Container>
            <!-- Header with back button -->
            <div class="flex items-center mb-6">
                <button @click="goBack" class="mr-4 p-2 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100 transition-colors duration-200">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-tags text-blue-600 mr-3"></i>
                        Categories List
                    </h1>
                    <p class="text-gray-600 mt-1">View and manage all categories</p>
                </div>
            </div>
            
            <!-- Stats Summary -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500 hover:shadow-md transition-shadow duration-300">
                    <h3 class="text-gray-600 text-sm font-medium mb-2">Total Categories</h3>
                    <p class="text-3xl font-bold text-blue-600">
                        {{ totalCategories }}
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500 hover:shadow-md transition-shadow duration-300">
                    <h3 class="text-gray-600 text-sm font-medium mb-2">Active Categories</h3>
                    <p class="text-3xl font-bold text-green-600">
                        {{ activeCategories }}
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500 hover:shadow-md transition-shadow duration-300">
                    <h3 class="text-gray-600 text-sm font-medium mb-2">Inactive Categories</h3>
                    <p class="text-3xl font-bold text-red-600">
                        {{ inactiveCategories }}
                    </p>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
                <!-- Filter Controls -->
                <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-200">
                    <div class="flex items-center">
                        <i class="fas fa-tags text-blue-500 mr-2"></i>
                        <h3 class="text-lg font-semibold text-gray-800">Categories List</h3>
                        <span class="ml-2 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ categoriesWithSectorNames.length }} total</span>
                    </div>
                    <div class="flex flex-wrap items-center mt-3 sm:mt-0 w-full sm:w-auto gap-2">
                        <Link :href="route('categories.create')" 
                              class="text-sm px-4 py-2 rounded-md bg-blue-500 text-white hover:bg-blue-600 transition-colors duration-200 flex items-center focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 mr-2">
                            <i class="fas fa-plus-circle mr-2"></i> Add Category
                        </Link>
                        <div class="flex items-center">
                            <label for="statusFilter" class="mr-2 text-sm font-medium text-gray-700">Status:</label>
                            <select id="statusFilter" v-model="selectedStatus" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="all">All</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="flex items-center">
                            <label for="sectorFilter" class="mx-2 text-sm font-medium text-gray-700">Sector:</label>
                            <select id="sectorFilter" v-model="selectedSector" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option :value="null">All Sectors</option>
                                <option v-for="sector in sectors" :key="sector.id" :value="sector.id">
                                    {{ sector.name }}
                                </option>
                            </select>
                        </div>
                        <PrimaryButton @click="applyFilter" class="text-sm px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                            <i class="fas fa-filter mr-2"></i> Apply
                        </PrimaryButton>
                    </div>
                </div>

                <!-- Categories Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 tracking-wider">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium">Name</th>
                                <th class="px-6 py-3 text-left font-medium">Sector Under</th>
                                <th class="px-6 py-3 text-left font-medium">Posted By</th>
                                <th class="px-6 py-3 text-left font-medium">Status</th>
                                <th class="px-6 py-3 text-right font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="category in categoriesWithSectorNames" :key="category.id" class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
                                            <i class="fas fa-tag"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ category.name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-800 font-medium">
                                        {{ category.sectorName }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ category.user ? category.user.peso_first_name : 'Unknown' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[category.deleted_at ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800', 'px-2 py-1 text-xs rounded-full font-medium']">
                                        {{ category.deleted_at ? 'Inactive' : 'Active' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="confirmDelete(category)" class="text-blue-600 hover:text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 rounded-md px-2 py-1 transition-colors duration-150">
                                        <i class="fas fa-trash-alt mr-1"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Empty State -->
                <div v-if="!hasCategories" class="py-12 text-center">
                    <div class="flex flex-col items-center justify-center">
                        <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                            <i class="fas fa-tags text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-700">No categories found</h3>
                        <p class="text-gray-500 mt-1">Try changing your filter settings</p>
                    </div>
                </div>
            </div>
        </Container>
    </AppLayout>
    <!-- Confirmation Modal -->
    <ConfirmationModal v-if="showDeleteModal" @close="showDeleteModal = false">
        <template #icon>
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                <i class="fas fa-question-circle text-blue-600"></i>
            </div>
        </template>
        <template #title>
            Delete Category
        </template>
        <template #content>
            Are you sure you want to delete this category? This action cannot be undone.
        </template>
        <template #footer>
            <SecondaryButton @click="showDeleteModal = false" class="mr-2 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                Cancel
            </SecondaryButton>
            <PrimaryButton @click="deleteCategory" class="bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                Delete
            </PrimaryButton>
        </template>
    </ConfirmationModal>
</template>