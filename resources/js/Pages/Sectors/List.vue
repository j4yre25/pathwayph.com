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


const page = usePage();



const props = defineProps({
    sectors: Array
    // user: Object

})


const showModal = ref(false);
const selectedStatus = ref('all');
const appliedStatus = ref('all');

const filteredSectors = computed(() => {
    if (appliedStatus.value === 'all') {
        return props.sectors;
    }
    return props.sectors.filter(sector => {
        return appliedStatus.value === 'active' ? !sector.deleted_at : sector.deleted_at;
    });
});

console.log('Sectors:', props.sectors);
function applyFilter() {
    appliedStatus.value = selectedStatus.value;
}



</script>


<template>
    <AppLayout title="Manage Users">
        <template #header>
            List of Sectors

        </template>

        <Container class="py-8">

            <div class="mb-4">
                <label for="statusFilter" class="mr-2 font-medium">Filter by Status:</label>
                <select id="statusFilter" v-model="selectedStatus" class="border border-gray-300 rounded px-3 py-2 mr-2">
                    <option value="all">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <PrimaryButton @click="applyFilter">Apply Filter</PrimaryButton>

            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="border border-gray-200 px-6 py-3 text-left">Name</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Posted By</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Status</th>

                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <tr v-for="sector in filteredSectors" :key="sector.id"
                            class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="border border-gray-200 px-6 py-4">{{ sector.name }}</td>
                            <td class="border border-gray-200 px-6 py-4">{{ sector.user ? sector.user.peso_first_name :
                                'Unknown' }}</td>
                            <td class="border border-gray-200 px-6 py-4" :class="sector.deleted_at ? 'text-red-500 font-bold' : 'text-green-500 font-bold'">{{ sector.deleted_at ? 'Inactive' : 'Active' }}
                            </td>






                        </tr>
                    </tbody>
                </table>
            </div>
            <ConfirmationModal :show="showModal" @close="showModal = false" @confirm="handleDelete">
                <template #title>
                    Confirm Deletion
                </template>
                <template #content>
                    Are you sure you want to delete this user?
                </template>
                <template #footer>
                    <PrimaryButton @click="showModal = false">Cancel</PrimaryButton>
                    <DangerButton @click="handleDelete">Delete</DangerButton>
                </template>
            </ConfirmationModal>


        </Container>
    </AppLayout>




</template>