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
    <!-- <div class="mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="w-full sm:w-64">
                <label for="sector" class="flex text-sm font-semibold text-gray-700 mb-2 items-center">
                    <i class="fas fa-filter text-blue-500 mr-2"></i> Filter by Sector
                </label>
                <div class="relative">
                    <select id="sector" v-model="selectedSector"
                        class="block w-full border border-gray-300 rounded-xl shadow-sm px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all appearance-none">
                        <option :value="null">All Sectors</option>
                        <option v-for="sector in sectors" :key="sector.id" :value="sector">
                            {{ sector.name }}
                        </option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-400"></i>
                    </div>
                </div>
            </div>
            <button type="button" @click="selectedSector = null"
                class="px-6 py-3 bg-white text-gray-700 rounded-xl text-sm font-medium flex items-center hover:bg-gray-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 shadow-sm border border-gray-300">
                <i class="fas fa-undo mr-2"></i> Reset Filter
            </button>
        </div>
    </div> -->

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gradient-to-r from-blue-50 to-indigo-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Name</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    <template v-if="hasCategories">
                        <tr v-for="category in paginatedCategories" :key="category.id"
                            class="border-b border-gray-200 hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ category.name }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <Link :href="route('categories.edit', { category: category.id })" 
                                        class="text-blue-600 hover:text-white hover:bg-blue-500 p-2 rounded-full transition-all duration-200 shadow-sm hover:shadow-md"
                                        title="Edit">
                                        <i class="fas fa-edit text-sm"></i>
                                    </Link>
                                    <button @click="confirmArchive(category)" 
                                            class="text-gray-600 hover:text-white hover:bg-gray-500 p-2 rounded-full transition-all duration-200 shadow-sm hover:shadow-md"
                                            title="Archive">
                                        <i class="fas fa-archive text-sm"></i>
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
        <div v-if="hasCategories && totalPages > 1" class="px-6 py-4 flex items-center justify-center border-t border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <nav class="flex items-center space-x-2" aria-label="Pagination">
                <!-- Previous Button -->
                <button 
                    @click="goToPage(currentPage - 1)"
                    :disabled="currentPage === 1"
                    :class="[currentPage === 1 ? 'cursor-not-allowed opacity-50' : 'hover:bg-blue-50 hover:border-blue-300',
                        'w-8 h-8 flex items-center justify-center rounded-full bg-white border border-gray-200 text-gray-600 transition-all duration-200 shadow-sm']">
                    <i class="fas fa-chevron-left text-xs"></i>
                </button>
                
                <!-- Page Numbers -->
                <template v-for="page in totalPages" :key="page">
                    <button 
                        @click="goToPage(page)"
                        :class="[
                            'w-8 h-8 flex items-center justify-center rounded-full text-sm font-medium transition-all duration-200',
                            page === currentPage 
                                ? 'bg-blue-500 text-white shadow-md' 
                                : 'bg-white border border-gray-200 text-gray-600 hover:bg-blue-50 hover:border-blue-300'
                        ]">
                        {{ page }}
                    </button>
                </template>
                
                <!-- Next Button -->
                <button 
                    @click="goToPage(currentPage + 1)"
                    :disabled="currentPage === totalPages"
                    :class="[currentPage === totalPages ? 'cursor-not-allowed opacity-50' : 'hover:bg-blue-50 hover:border-blue-300',
                        'w-8 h-8 flex items-center justify-center rounded-full bg-white border border-gray-200 text-gray-600 transition-all duration-200 shadow-sm']">
                    <i class="fas fa-chevron-right text-xs"></i>
                </button>
            </nav>
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