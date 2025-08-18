<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link, router } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref, computed, onMounted } from 'vue'
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
    categories: Array,
    category: Object,
    sectors: Array
});

const selectedSector = ref(null); 
const selectedCategory = ref(null);
const open = ref(false);

// Pagination
const currentPage = ref(1);
const itemsPerPage = ref(10);
const totalPages = computed(() => Math.ceil(filteredCategories.value.length / itemsPerPage.value));

const paginatedCategories = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    const end = start + itemsPerPage.value;
    return filteredCategories.value.slice(start, end);
});

const filteredCategories = computed(() => {
    if (!selectedSector.value) {
        return props.categories;
    }
    return props.categories.filter(category => category.sector_id === selectedSector.value.id);
});

const archiveCategory = () => {
    if (selectedCategory.value) {
        router.delete(route('categories.delete', { category: selectedCategory.value.id }));
        open.value = false;
        selectedCategory.value = null;
    }
};

const confirmArchive = (category) => {
    selectedCategory.value = category;
    open.value = true;
};

// Computed property to check if there are categories to display
const hasCategories = computed(() => {
    return filteredCategories.value && filteredCategories.value.length > 0;
});

// Pagination methods
const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

// Reset to page 1 when filter changes
onMounted(() => {
    currentPage.value = 1;
});
</script>

<template>
    <div class="mb-6">
        <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-200">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <div class="w-full sm:w-64">
                    <label for="sector" class="block text-sm font-medium text-gray-600 mb-1">Filter by Sector</label>
                    <div class="relative">
                        <select id="sector" v-model="selectedSector"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 pr-8 focus:ring-blue-500 focus:border-blue-500 appearance-none">
                            <option :value="null">All Sectors</option>
                            <option v-for="sector in sectors" :key="sector.id" :value="sector">
                                {{ sector.name }}
                            </option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template v-if="hasCategories">
                        <tr v-for="category in paginatedCategories" :key="category.id"
                            class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ category.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center space-x-2">
                                    <Link :href="route('categories.edit', { category: category.id })" 
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </Link>
                                    <button @click="confirmArchive(category)" 
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <i class="fas fa-archive mr-1"></i> Archive
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr v-if="!hasCategories">
                        <td colspan="2" class="px-6 py-10 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-folder-open text-4xl mb-3 text-gray-300"></i>
                                <p class="text-lg font-medium">No categories found</p>
                                <p class="text-sm">There are no categories available for the selected filter.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="hasCategories && totalPages > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium">{{ (currentPage - 1) * itemsPerPage + 1 }}</span>
                        to
                        <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredCategories.length) }}</span>
                        of
                        <span class="font-medium">{{ filteredCategories.length }}</span>
                        results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <!-- Previous Page Button -->
                        <button
                            @click="goToPage(currentPage - 1)"
                            :disabled="currentPage === 1"
                            :class="[currentPage === 1 ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50', 'relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500']"
                        >
                            <span class="sr-only">Previous</span>
                            <i class="fas fa-chevron-left h-5 w-5"></i>
                        </button>
                        
                        <!-- Page Numbers -->
                        <template v-for="page in totalPages" :key="page">
                            <button
                                @click="goToPage(page)"
                                :class="[page === currentPage ? 'bg-blue-50 border-blue-500 text-blue-600 z-10' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50', 'relative inline-flex items-center px-4 py-2 border text-sm font-medium']"
                            >
                                {{ page }}
                            </button>
                        </template>
                        
                        <!-- Next Page Button -->
                        <button
                            @click="goToPage(currentPage + 1)"
                            :disabled="currentPage === totalPages"
                            :class="[currentPage === totalPages ? 'cursor-not-allowed opacity-50' : 'hover:bg-gray-50', 'relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500']"
                        >
                            <span class="sr-only">Next</span>
                            <i class="fas fa-chevron-right h-5 w-5"></i>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <ConfirmationModal @close="open = false" :show="open">
        <template #title>
            <div class="flex items-center text-red-600">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span>Archive Category</span>
            </div>
        </template>

        <template #content>
            <p class="text-gray-600">
                Are you sure you want to archive this category: <span class="font-semibold">{{ selectedCategory?.name }}</span>?
            </p>
            <p class="text-sm text-gray-500 mt-2">
                This action can be reversed later if needed.
            </p>
        </template>

        <template #footer>
            <div class="flex justify-end space-x-3">
                <SecondaryButton @click="open = false" class="px-4">
                    <i class="fas fa-times mr-1"></i> Cancel
                </SecondaryButton>
                <DangerButton @click="archiveCategory" class="px-4">
                    <i class="fas fa-archive mr-1"></i> Archive
                </DangerButton>
            </div>
        </template>
    </ConfirmationModal>
</template>