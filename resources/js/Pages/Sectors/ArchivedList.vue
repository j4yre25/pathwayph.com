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
import {inertia} from '@inertiajs/inertia';



const page = usePage();



const props = defineProps({
    all_sectors: Array,
    // user: Object

})






const showModal = ref(false);
const sectorToRestore = ref(null);


const restoreSector = () => {
    console.log(route('sectors.restore', { sector: sectorToRestore.value.id })); // Log the generated URL
    router.post(route('sectors.restore', {
        sector: sectorToRestore.value.id
    }), {}, {
        onSuccess: () => {
            console.log('Sector restored successfully!');
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
            Archived Sectors

        </template>

        <Container class="py-16">


            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="border border-gray-200 px-6 py-3 text-left">Name</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Posted By</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Status</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Actions</th>

                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <tr v-for="sector in all_sectors" :key="sector.id"
                            class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="border border-gray-200 px-6 py-4">{{ sector.name }}</td>
                            <td class="border border-gray-200 px-6 py-4">{{ sector.user ? sector.user.peso_first_name :
                                'Unknown' }}</td>
                            <td class="border border-gray-200 px-6 py-4">
                                <span class="text-red-600 font-semibold">Archived</span>
                            </td>
                            <td class="border border-gray-200 px-6 py-4">
                                <DangerButton class="mr-2" @click="confirmRestore(sector)">Restore Sector
                                </DangerButton>

                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <ConfirmationModal :show="showModal" @close="showModal = false" @confirm="restoreSector">
                <template #title>
                    Confirm Deletion
                </template>
                <template #content>
                    Are you sure you want to restore this sector?
                </template>
                <template #footer>
                    <PrimaryButton @click="showModal = false" class="mr-2">Cancel</PrimaryButton>
                    <DangerButton @click="restoreSector">Restore</DangerButton>
                </template>
            </ConfirmationModal>


        </Container>
    </AppLayout>




</template>