<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link, router } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref, computed } from 'vue'
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    categories: Array,
    category: Object,
    sectors: Array

});

const selectedSector = ref(null); 
const selectedCategory = ref(null);
const open = ref(false)


const filteredCategories = computed(() => {
    if (!selectedSector.value) {
        return props.categories;
    }
    return props.categories.filter(category => category.sector_id === selectedSector.value.id);
});

const archiveCategory = () => {
    if (selectedCategory.value) {
        router.delete(route('categories.delete', { category: selectedCategory.value.id }));
        open.value = false;
        selectedSector.value = null;
    }
};

const confirmArchive = (sector) => {
    selectedSector.value = sector;
    open.value = true;
};


console.log('Categories:', props.categories);
</script>

<template>
    <div class="mb-4">
        <label for="sector" class="block text-sm font-medium text-gray-700">Filter by Sector</label>
        <select id="sector" v-model="selectedSector"
            class="border border-gray-300 rounded px-3 py-2 mr-2">
            <option :value="null">All Sectors</option>
            <option v-for="sector in sectors" :key="sector.id" :value="sector">
                {{ sector.name }}
            </option>
        </select>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
            <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                    <th class="border border-gray-200 px-6 py-3 text-left">ID</th>
                    <th class="border border-gray-200 px-6 py-3 text-left">Name</th>
                    <th class="border border-gray-200 px-6 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm font-light">
                <tr v-for="category in filteredCategories" :key="category.id"
                    class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="border border-gray-200 px-6 py-4">{{ category.id }}</td>
                    <td class="border border-gray-200 px-6 py-4">{{ category.name }}</td>
                    <td class="border border-gray-200 px-6 py-4">
                        <Link :href="route('categories.edit', { category: category.id })">
                        <PrimaryButton class="mr-2">Edit</PrimaryButton>
                        </Link>
                        <DangerButton @click="confirmArchive(category)">
                            Archive Category

                        </DangerButton>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <ConfirmationModal @close="open = false" :show="open">
        <template #title>
            Are you sure?
        </template>

        <template #content>
            Are you sure you want to archive this sector #{{ selectedCategory?.id }} {{ selectedCategory?.name }}?
        </template>

        <template #footer>
            <DangerButton @click="archiveCategory" class="mr-2">
                Archive Category
            </DangerButton>
            <SecondaryButton @click="open = false">Cancel</SecondaryButton>
        </template>
    </ConfirmationModal>

</template>