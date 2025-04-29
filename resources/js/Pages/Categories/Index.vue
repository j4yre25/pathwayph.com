<script setup>
import { usePage } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import MyCategories from './MyCategories.vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';


const page = usePage();

const props = defineProps({
    sectors: Array, // Ensure the sectors prop is defined
    categories: Array
});

// Access the sectors directly from props
const sectors = props.sectors; // This will be an array of sectors
console.log('Sectors:', sectors); // Debugging

</script>

<template>
    <AppLayout title="Categories">
        <template #header>
            Categories
        </template>

        <Container class="py-4">
            <div class="mt-8">
                <Link :href="route('categories.create')" class="mr-2">
                <PrimaryButton>Add Categories</PrimaryButton>
                </Link>
                <Link :href="route('categories.list')" class="mr-2">
                <PrimaryButton>List of Categories</PrimaryButton>
                </Link>

                <Link :href="route('categories.archivedlist')" class="mr-2">
                <PrimaryButton>Archived Categories</PrimaryButton>
                </Link>
            </div>

            <div class="mt-8">

                    <!-- Pass the categories for this sector to MyCategories -->
                    <MyCategories :categories="categories" :sectors="sectors" />
              
            </div>
        </Container>
    </AppLayout>
</template>