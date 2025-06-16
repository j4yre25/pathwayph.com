<script setup>
import { usePage } from '@inertiajs/vue3';
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

// Stats for display
const totalCategories = props.categories ? props.categories.length : 0;
const activeCategories = props.categories ? props.categories.filter(category => !category.deleted_at).length : 0;
const inactiveCategories = props.categories ? props.categories.filter(category => category.deleted_at).length : 0;

</script>

<template>
    <AppLayout title="Categories">
        <template #header>
            <div class="flex items-center">
                <button @click="goBack" class="mr-3 p-1 rounded-full hover:bg-gray-200 transition-colors duration-200">
                    <i class="fas fa-chevron-left text-gray-600"></i>
                </button>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                    <i class="fas fa-tags text-blue-500 mr-2"></i> Categories
                </h2>
            </div>
        </template>

        <Container class="py-8">
            <!-- Stats Summary -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                    <h3 class="text-gray-600 text-sm font-medium mb-2">Total Categories</h3>
                    <p class="text-3xl font-bold text-blue-600">
                        {{ totalCategories }}
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                    <h3 class="text-gray-600 text-sm font-medium mb-2">Active Categories</h3>
                    <p class="text-3xl font-bold text-green-600">
                        {{ activeCategories }}
                    </p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
                    <h3 class="text-gray-600 text-sm font-medium mb-2">Inactive Categories</h3>
                    <p class="text-3xl font-bold text-red-600">
                        {{ inactiveCategories }}
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md mb-6">
                <div class="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-200">
                    <div class="flex items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Manage Categories</h3>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-3 sm:mt-0">
                        <Link :href="route('categories.create')" 
                              class="text-sm px-4 py-2 rounded-md bg-blue-500 text-white hover:bg-blue-600 transition-colors duration-200 flex items-center">
                            <i class="fas fa-plus-circle mr-2"></i> Add Categories
                        </Link>
                        <Link :href="route('categories.list')" 
                              class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
                            <i class="fas fa-list text-blue-500 mr-2"></i> List of Categories
                        </Link>
                        <Link :href="route('categories.archivedlist')" 
                              class="text-sm px-4 py-2 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors duration-200 flex items-center">
                            <i class="fas fa-archive text-gray-500 mr-2"></i> Archived Categories
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Categories List -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">My Categories</h3>
                </div>
                <div class="p-4">
                    <!-- Pass the categories for this sector to MyCategories -->
                    <MyCategories :categories="categories" :sectors="sectors" />
                </div>
            </div>
        </Container>
    </AppLayout>
</template>