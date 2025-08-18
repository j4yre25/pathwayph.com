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
import {Inertia} from '@inertiajs/inertia';
import '@fortawesome/fontawesome-free/css/all.css';

const page = usePage();

const props = defineProps({
    all_categories: Array,
    // user: Object
});

// Function to go back to previous page
const goBack = () => {
    window.history.back();
};

const showModal = ref(false);
const categoryToRestore = ref(null);

const restoreCategory = () => {
    console.log(route('categories.restore', { category: categoryToRestore.value.id })); // Log the generated URL
    router.post(route('categories.restore', {
        category: categoryToRestore.value.id
    }), {}, {
        onSuccess: () => {
            console.log('Category restored successfully!');
            showModal.value = false; // Close the modal after success
            Inertia.visit(route('categories.index'), {
                method: 'get',
                preserveState: true,
                preserveScroll: true,
            });
        }
    });
};

const confirmRestore = (category) => {
    categoryToRestore.value = category;
    showModal.value = true;
};




</script>


<template>
    <AppLayout title="Archived Categories">
        <template #header>
            <div class="flex items-center">
                <button @click="goBack" class="mr-3 p-1 rounded-full hover:bg-gray-200 transition-colors duration-200">
                    <i class="fas fa-chevron-left text-gray-600"></i>
                </button>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                    <i class="fas fa-archive text-amber-500 mr-2"></i> Archived Categories
                </h2>
            </div>
        </template>

        <Container class="py-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-archive text-amber-600 mr-3"></i>
                    Archived Categories
                </h1>
                <p class="text-gray-600 mt-1">View and restore previously archived categories</p>
            </div>

            <!-- Table Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead>
                                <tr class="bg-gray-50 text-gray-600 text-sm leading-normal">
                                    <th class="px-6 py-3 text-left font-medium">Name</th>
                                    <th class="px-6 py-3 text-left font-medium">Sector Under</th>
                                    <th class="px-6 py-3 text-left font-medium">Posted By</th>
                                    <th class="px-6 py-3 text-left font-medium">Status</th>
                                    <th class="px-6 py-3 text-left font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 text-sm">
                                <tr v-for="category in all_categories" :key="category.id"
                                    class="border-b border-gray-100 hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4">{{ category.name }}</td>
                                    <td class="px-6 py-4">{{ category.sectorName }}</td>
                                    <td class="px-6 py-4">{{ category.userName }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">
                                            <i class="fas fa-archive mr-1"></i> Archived
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button @click="confirmRestore(category)" 
                                            class="inline-flex items-center px-3 py-1.5 bg-green-600 border border-transparent rounded-md font-medium text-xs text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-sm transition-colors duration-200">
                                            <i class="fas fa-undo-alt mr-1"></i> Restore
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="all_categories.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-inbox text-gray-300 text-5xl mb-3"></i>
                                            <p>No archived categories found</p>
                                            <Link :href="route('categories.index')" class="mt-3 text-blue-600 hover:underline">
                                                <i class="fas fa-arrow-left mr-1"></i> Back to categories
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <ConfirmationModal :show="showModal" @close="showModal = false" @confirm="restoreCategory">
                <template #title>
                    <div class="flex items-center">
                        <i class="fas fa-undo-alt text-green-500 mr-2"></i>
                        Restore Category
                    </div>
                </template>
                <template #content>
                    <p>Are you sure you want to restore this category? It will be moved back to the active categories list.</p>
                </template>
                <template #footer>
                    <div class="flex justify-end space-x-3">
                        <button @click="showModal = false" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-medium text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-colors duration-200">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button @click="restoreCategory" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-sm transition-colors duration-200">
                            <i class="fas fa-undo-alt mr-2"></i> Restore
                        </button>
                    </div>
                </template>
            </ConfirmationModal>
        </Container>
    </AppLayout>
</template>