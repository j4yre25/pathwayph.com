<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link, router } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref, computed } from 'vue'
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
    sectors: Object, // Changed to Object to support pagination
    sector: Object
});

const selectedSector = ref(null);
const archiveSector= () => {
  if (selectedSector.value) {
        router.delete(route('sectors.delete', { sector: selectedSector.value.id }));
        open.value = false; 
        selectedSector.value = null; 
    }
};

const confirmArchive = (sector) => {
    selectedSector.value = sector;
    open.value = true; 
};

const open = ref(false)

// Add goTo method for pagination navigation
const goTo = (url) => {
    if (url) {
        router.visit(url);
    }
};


</script>

<template>
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
                    <template v-if="sectors && sectors.length > 0">
                        <tr v-for="sector in sectors" :key="sector.id" class="border-b border-gray-200 hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4">{{ sector.name }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <Link :href="route('sectors.edit', { sector: sector.id })" 
                                        class="text-blue-600 hover:text-white hover:bg-blue-500 p-2 rounded-full transition-all duration-200 shadow-sm hover:shadow-md"
                                        title="Edit">
                                        <i class="fas fa-edit text-sm"></i>
                                    </Link>
                                    <button @click="confirmArchive(sector)" 
                                            class="text-gray-600 hover:text-white hover:bg-gray-500 p-2 rounded-full transition-all duration-200 shadow-sm hover:shadow-md"
                                            title="Archive">
                                        <i class="fas fa-archive text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr v-if="!sectors || sectors.length === 0">
                        <td colspan="2" class="px-6 py-10 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-folder-open text-4xl mb-3 text-gray-300"></i>
                                <p class="text-lg font-medium">No sectors found</p>
                                <p class="text-sm">There are no sectors available at the moment.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
            <div v-if="sectors && sectors.links && sectors.links.length > 3"
            class="px-4 py-3 flex items-center justify-between border-t border-gray-200">
            <div class="flex-1 flex justify-between sm:hidden">
                <a v-if="sectors.prev_page_url" @click.prevent="goTo(sectors.prev_page_url)"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 cursor-pointer">
                    <i class="fas fa-chevron-left mr-2"></i> Previous
                </a>
                <a v-if="sectors.next_page_url" @click.prevent="goTo(sectors.next_page_url)"
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 cursor-pointer">
                    Next <i class="fas fa-chevron-right ml-2"></i>
                </a>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">{{ sectors.from }}</span> to <span
                            class="font-medium">{{ sectors.to }}</span> of <span class="font-medium">{{ sectors.total }}</span>
                        results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                        aria-label="Pagination">
                        <a v-for="link in sectors.links" :key="link.url" v-html="link.label"
                            @click.prevent="link.url ? goTo(link.url) : null" :class="[
                                link.url ? 'cursor-pointer hover:bg-gray-50' : 'cursor-not-allowed',
                                link.active ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500',
                                link.label.includes('Previous') ? 'rounded-l-md' : '',
                                link.label.includes('Next') ? 'rounded-r-md' : '',
                                'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                            ]"></a>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <ConfirmationModal @close="open = false" :show="open">
        <template #title>
            <div class="flex items-center text-red-600">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span>Archive Sector</span>
            </div>
        </template>

        <template #content>
            <p class="text-gray-600">
                Are you sure you want to archive this sector: <span class="font-semibold">{{ selectedSector?.name }}</span>?
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
                <DangerButton @click="archiveSector" class="px-4">
                    <i class="fas fa-archive mr-1"></i> Archive
                </DangerButton>
            </div>
        </template>
    </ConfirmationModal>


</template>