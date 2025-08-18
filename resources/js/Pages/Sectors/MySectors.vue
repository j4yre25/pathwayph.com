<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link, router } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue'
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const props = defineProps({
    sectors: Array,
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
        <table class="min-w-full bg-white rounded-lg overflow-hidden">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-sm">
                    <th class="px-6 py-3 text-left font-medium">Name</th>
                    <th class="px-6 py-3 text-left font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm">
                <template v-if="sectors && sectors.length > 0">
                    <tr v-for="sector in sectors" :key="sector.id" class="border-b border-gray-200 hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4">{{ sector.name }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                <Link :href="route('sectors.edit', { sector: sector.id })" 
                                      class="text-sm px-3 py-1.5 rounded-md bg-blue-500 text-white hover:bg-blue-600 transition-colors duration-200 flex items-center">
                                    <i class="fas fa-edit mr-1"></i> Edit
                                </Link>
                                <button @click="confirmArchive(sector)" 
                                        class="text-sm px-3 py-1.5 rounded-md bg-red-500 text-white hover:bg-red-600 transition-colors duration-200 flex items-center">
                                    <i class="fas fa-archive mr-1"></i> Archive
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