<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { usePage } from '@inertiajs/vue3';
import { ref, computed} from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    categories: Array,
    sectors: Array
});

console.log('Categories:', props.categories);

const selectedStatus = ref('all'); // Default filter value
const selectedSector = ref(null);

function applyFilter() {
    router.get(route('categories.list'), {
        status: selectedStatus.value,
        sector: selectedSector.value,
    }, { preserveState: true });
}

const categoriesWithSectorNames = computed(() => {
    return props.categories.map(category => {
        const sector = props.sectors.find(sector => sector.id === category.sector_id);
        return {
            ...category,
            sectorName: sector ? sector.name : 'No Sector' // Add sectorName to each category
        };
    });
});
console.log('Categories:', props.categories);
</script>

<template>
    <AppLayout title="Manage Users">
        <template #header>
            List of Categories

        </template>

        <Container class="py-8">

            <div class="mb-4">
                <label for="statusFilter" class="mr-2 font-medium">Filter by Status:</label>
                <select id="statusFilter" v-model="selectedStatus" class="border border-gray-300 rounded px-3 py-2 mr-2">
                    <option value="all">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>

                <label for="sectorFilter" class="mr-2 font-medium">Filter by Sector:</label>
                <select id="sectorFilter" v-model="selectedSector" class="border border-gray-300 rounded px-3 py-2 mr-2">
                    <option :value="null">All Sectors</option>
                    <option v-for="sector in sectors" :key="sector.id" :value="sector.id">
                        {{ sector.name }}
                    </option>
                </select>
                <PrimaryButton @click="applyFilter">Apply Filter</PrimaryButton>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="border border-gray-200 px-6 py-3 text-left">Name</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Sector Under</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Posted By</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Status</th>
                            <!-- Added Status Column -->


                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <tr v-for="category in categoriesWithSectorNames" :key="category.id"
                            class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="border border-gray-200 px-6 py-4">{{ category.name }}</td>
                            <td class="border border-gray-200 px-6 py-4">{{ category.sectorName }}</td>
                            <td class="border border-gray-200 px-6 py-4">{{ category.user ?
                                category.user.peso_first_name :
                                'Unknown' }}</td>
                            <td class="border border-gray-200 px-6 py-4" :class="category.deleted_at ? 'text-red-500 font-bold' : 'text-green-500 font-bold'">{{ category.deleted_at ? 'Inactive' : 'Active'
                                }}</td>



                        </tr>
                    </tbody>
                </table>
            </div>
        </Container>
    </AppLayout>
</template>