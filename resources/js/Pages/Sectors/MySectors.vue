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


</script>

<template>
    <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 text-sm font-semibold text-gray-700">
                <tr>
                    <th class="px-6 py-4 text-left">Name</th>
                    <th class="px-6 py-4 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm">
                <template v-if="sectors && sectors.length > 0">
                    <tr v-for="sector in sectors" :key="sector.id" class="border-b border-gray-200 hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4">{{ sector.name }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <Link :href="route('sectors.edit', { sector: sector.id })" 
                                      class="text-blue-600 hover:text-white hover:bg-blue-500 focus:outline-none p-2 rounded-full transition-all duration-200 shadow-sm hover:shadow-md"
                                      title="Edit">
                                    <i class="fas fa-edit text-sm"></i>
                                </Link>
                                <button @click="confirmArchive(sector)" 
                                        class="text-gray-600 hover:text-white hover:bg-gray-500 focus:outline-none p-2 rounded-full transition-all duration-200 shadow-sm hover:shadow-md"
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
    
    <!-- Pagination -->
    <div v-if="sectors && sectors.links && sectors.links.length > 3" class="px-6 py-4 flex items-center justify-center border-t border-gray-100 bg-gradient-to-r from-gray-50 to-white">
        <nav class="flex items-center space-x-2" aria-label="Pagination">
            <!-- Previous Button -->
            <button 
                v-if="sectors.prev_page_url" 
                @click="goTo(sectors.prev_page_url)" 
                class="w-8 h-8 flex items-center justify-center rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 shadow-sm">
                <i class="fas fa-chevron-left text-xs"></i>
            </button>
            
            <!-- Page Numbers -->
            <template v-for="(link, index) in sectors.links" :key="index">
                <button 
                    v-if="!link.label.includes('Previous') && !link.label.includes('Next') && link.label !== '...'" 
                    @click="link.url ? goTo(link.url) : null"
                    :class="[
                        'w-8 h-8 flex items-center justify-center rounded-full text-sm font-medium transition-all duration-200',
                        link.active 
                            ? 'bg-blue-500 text-white shadow-md' 
                            : 'bg-white border border-gray-200 text-gray-600 hover:bg-blue-50 hover:border-blue-300',
                        link.url ? 'cursor-pointer' : 'cursor-not-allowed'
                    ]">
                    {{ link.label }}
                </button>
                <span 
                    v-else-if="link.label === '...'"
                    class="w-8 h-8 flex items-center justify-center text-gray-400 text-sm">
                    ...
                </span>
            </template>
            
            <!-- Next Button -->
            <button 
                v-if="sectors.next_page_url" 
                @click="goTo(sectors.next_page_url)" 
                class="w-8 h-8 flex items-center justify-center rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-blue-50 hover:border-blue-300 transition-all duration-200 shadow-sm">
                <i class="fas fa-chevron-right text-xs"></i>
            </button>
        </nav>
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