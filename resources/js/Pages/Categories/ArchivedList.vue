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
    all_categories: Array,
    // user: Object

})






const showModal = ref(false);
const categoryToRestore = ref(null);


const restoreSector = () => {
    console.log(route('categoriess.restore', { category: categoryToRestore.value.id })); // Log the generated URL
    router.post(route('categories.restore', {
        category: categoryToRestore.value.id
    }), {}, {
        onSuccess: () => {
            console.log('Category restored successfully!');
            showModal.value = false; // Close the modal after success
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
            Archived Categories

        </template>

        <Container class="py-16">


            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                            <th class="border border-gray-200 px-6 py-3 text-left">Name</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Sector Under</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Posted By</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Status</th>
                            <th class="border border-gray-200 px-6 py-3 text-left">Actions</th>

                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <tr v-for="category in all_categories" :key="category.id"
                            class="border-b border-gray-200 hover:bg-gray-100">
                            <td class="border border-gray-200 px-6 py-4">{{ category.name }}</td>
                            <td class="border border-gray-200 px-6 py-4">{{ category.sectorName }}</td>
                            <td class="border border-gray-200 px-6 py-4">{{ category.userName }}</td>
                            <td class="border border-gray-200 px-6 py-4">
                                <span class="text-red-600 font-semibold">Archived</span>
                            </td>
                            <td class="border border-gray-200 px-6 py-4">
                                <DangerButton class="mr-2" @click="confirmRestore(category)">Restore Category
                                </DangerButton>

                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <ConfirmationModal :show="showModal" @close="showModal = false" @confirm="restoreCategory">
                <template #title>
                    Confirm Deletion
                </template>
                <template #content>
                    Are you sure you want to restore this category?
                </template>
                <template #footer>
                    <PrimaryButton @click="showModal = false" class="mr-2">Cancel</PrimaryButton>
                    <DangerButton @click="restoreCategory">Restore</DangerButton>
                </template>
            </ConfirmationModal>


        </Container>
    </AppLayout>




</template>