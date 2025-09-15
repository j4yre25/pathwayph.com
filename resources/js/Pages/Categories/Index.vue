<script setup>
import { ref, computed } from 'vue';
import { usePage, router, } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import MyCategories from './MyCategories.vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const page = usePage();

const props = defineProps({
    sectors: Array, // Ensure the sectors prop is defined
    categories: Array
});

// Access the sectors directly from props
const sectors = props.sectors; // This will be an array of sectors

// Function to go back to previous page
const goBack = () => {
    window.history.back()
};

const selectedSector = ref(null);

const filteredCategories = computed(() => {
    if (!selectedSector.value) {
        return props.categories;
    }
    return props.categories.filter(category => category.sector_id === selectedSector.value.id);
});

// Stats for display
const totalCategories = props.categories ? props.categories.length : 0;
const activeCategories = props.categories ? props.categories.filter(category => !category.deleted_at).length : 0;
const inactiveCategories = props.categories ? props.categories.filter(category => category.deleted_at).length : 0;

// Add this line to define selectedCategory as a reactive variable

</script>

<template>
    <AppLayout title="Categories">
        <template #header>
            <div class="flex items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                    <i class="fas fa-tags text-blue-500 mr-2"></i> Categories
                </h2>
            </div>
        </template>

        <Container class="py-8">
            <!-- Stats Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Total Categories Card -->
                <div
                    class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-tags text-white text-lg"></i>
                        </div>
                        <h3 class="text-blue-700 text-sm font-medium mb-2">Total Categories</h3>
                        <p class="text-2xl font-bold text-blue-900">{{ totalCategories }}</p>
                    </div>
                </div>

                <!-- Active Categories Card -->
                <div
                    class="bg-gradient-to-br from-green-100 to-green-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-check-circle text-white text-lg"></i>
                        </div>
                        <h3 class="text-green-700 text-sm font-medium mb-2">Active</h3>
                        <p class="text-2xl font-bold text-green-900">{{ activeCategories }}</p>
                    </div>
                </div>

                <!-- Inactive Categories Card -->
                <div
                    class="bg-gradient-to-br from-red-100 to-red-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-times-circle text-white text-lg"></i>
                        </div>
                        <h3 class="text-red-700 text-sm font-medium mb-2">Inactive</h3>
                        <p class="text-2xl font-bold text-red-900">{{ inactiveCategories }}</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div
                class="bg-white rounded-2xl shadow-lg p-6 flex flex-wrap items-center justify-between gap-4 border border-gray-100 mb-8">
                <div class="flex items-center space-x-4">
                    <Link :href="route('categories.list')" :class="[route().current('categories.list') ? 'bg-blue-50 text-blue-600 border-blue-200' : 'text-gray-600 hover:bg-gray-50 border-gray-200',
                        'px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border']">
                    <i class="fas fa-list mr-2"></i>
                    <span>List of Categories</span>
                    </Link>
                    <Link :href="route('categories.archivedlist')" :class="[route().current('categories.archivedlist') ? 'bg-blue-50 text-blue-600 border-blue-200' : 'text-gray-600 hover:bg-gray-50 border-gray-200',
                        'px-4 py-2 rounded-md flex items-center space-x-2 font-medium transition-colors border']">
                    <i class="fas fa-archive mr-2"></i>
                    <span>Archived Categories</span>
                    </Link>
                </div>
                <Link :href="route('categories.create')" :class="[route().current('categories.create') ? 'bg-blue-600' : 'bg-blue-500 hover:bg-blue-600',
                    'px-4 py-2 rounded-md text-white font-medium transition-colors flex items-center shadow-sm']">
                <i class="fas fa-plus mr-2"></i> Add Categories
                </Link>
            </div>

            <!-- Filter Categories by Sector -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-8">
                <div class="p-4 border-b border-gray-200 flex items-center">
                    <i class="fas fa-filter text-blue-500 mr-2"></i>
                    <h3 class="font-medium text-gray-700">Filter by Sector</h3>
                    <div class="ml-auto">
                        <button type="button" @click="selectedSector = null"
                            class="px-6 py-3 bg-white text-gray-700 rounded-xl text-sm font-medium flex items-center hover:bg-gray-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 shadow-sm border border-gray-300">
                            <i class="fas fa-undo mr-2"></i> Reset Filter
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <label for="sectorFilter" class="mr-2 text-sm font-medium text-gray-700">Sector:</label>
                            <select id="sectorFilter" v-model="selectedSector"
                                class="block w-full border border-gray-300 rounded-xl shadow-sm px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition-all appearance-none">
                                <option :value="null">All Sectors</option>
                                <option v-for="sector in sectors" :key="sector.id" :value="sector">{{ sector.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories Section -->
            <div class="g-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div
                    class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-tags text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800">My Categories</h3>
                            <p class="text-sm text-gray-500 mt-1">Manage and organize your category assignments</p>
                        </div>
                    </div>
                </div>
                <MyCategories :categories="filteredCategories" :sectors="sectors"/>
            </div>
        </Container>
    </AppLayout>
</template>