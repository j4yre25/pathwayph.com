<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { router } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import '@fortawesome/fontawesome-free/css/all.css';

const page = usePage();

const props = defineProps({
    all_sectors: Array,
});

const goBack = () => {
    window.history.back();
};

const showModal = ref(false);
const sectorToRestore = ref(null);

const restoreSector = () => {
    if (!sectorToRestore.value) return;
    router.post(route('sectors.restore', {
        sector: sectorToRestore.value.id
    }), {}, {
        onSuccess: () => {
            showModal.value = false;
            inertia.visit(route('sectors.index'), {
                method: 'get',
                preserveState: true,
                preserveScroll: true,
            });
        }
    });
};

const confirmRestore = (sector) => {
    sectorToRestore.value = sector;
    showModal.value = true;
};
</script>

<template>
    <AppLayout title="Archived Sectors">
        <template #header>
            <div class="flex items-center">
                <button 
                    @click="goBack"
                    class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <h2 class="font-semibold text-xl text-gray-800 flex items-center">
                    <i class="fas fa-archive text-blue-500 text-xl mr-2"></i> Archived Sectors
                </h2>
            </div>
        </template>

        <Container class="py-6 space-y-6">
            <!-- Stats Card -->
            <div class="grid grid-cols-1 gap-6 mb-6">
                <div class="bg-gradient-to-br from-orange-100 to-orange-200 rounded-2xl p-6 relative overflow-hidden transition-all duration-200 hover:shadow-lg hover:scale-105">
                    <div class="flex flex-col items-center text-center">
                        <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center mb-3">
                             <i class="fas fa-archive text-white text-lg2"></i>
                        </div>
                        <h3 class="text-orange-700 text-sm font-medium mb-2">Total Archived Sectors</h3>
                        <p class="text-2xl font-bold text-orange-900">{{ all_sectors.length }}</p>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="p-6 flex items-center justify-between border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-table text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">Archived Sectors</h3>
                                <p class="text-sm text-gray-600">Manage archived sectors</p>
                                <span class="text-sm font-semibold text-gray-700">{{ all_sectors.length }} total</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gradient-to-r from-blue-50 to-indigo-50 text-sm font-semibold text-gray-700">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center">Name</div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center">Posted By</div>
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center">Status</div>
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                                    <div class="flex items-center justify-end">Actions</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <tr v-for="sector in all_sectors" :key="sector.id" class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group">
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">{{ sector.name }}</div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="text-sm text-gray-700 font-medium">{{ sector.user ? sector.user.peso_first_name : 'Unknown' }}</div>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 shadow-sm">
                                        <i class="fas fa-archive mr-1.5"></i> Archived
                                    </span>
                                </td>
                                <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button 
                                            @click="confirmRestore(sector)"
                                            class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-2 rounded-full hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md"
                                            title="Restore">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Empty State -->
                <div v-if="all_sectors.length === 0" class="py-12 text-center">
                    <i class="fas fa-archive text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-700">No archived sectors found</h3>
                    <p class="text-gray-500 mt-1">When sectors are archived, they will appear here</p>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <ConfirmationModal :show="showModal" @close="showModal = false" @confirm="restoreSector">
                <template #title>
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-red-100 to-red-200 flex items-center justify-center mr-4 shadow-lg">
                            <i class="fas fa-question-circle text-blue-500"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800">Confirm Restore</h3>
                    </div>
                </template>
                <template #content>
                    <p class="text-gray-600">Are you sure you want to restore the sector
                    <strong>"{{ sectorToRestore && sectorToRestore.name }}"</strong>? This will make the sector visible again.</p>
                </template>
                <template #footer>
                    <div class="flex justify-end space-x-4">
                        <button 
                            @click="showModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 text-sm transition-colors duration-200 flex items-center">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button 
                            @click="restoreSector"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm transition-colors duration-200 flex items-center">
                            <i class="fas fa-undo mr-2"></i> Restore
                        </button>
                    </div>
                </template>
            </ConfirmationModal>
        </Container>
    </AppLayout>
</template>