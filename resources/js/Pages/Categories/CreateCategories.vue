<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { router, usePage } from '@inertiajs/vue3'
import Container from '@/Components/Container.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import '@fortawesome/fontawesome-free/css/all.css';

const page = usePage()

const props = defineProps({
    categories: Array,
    sectors: Array,
    sector: Object,
})

// Function to go back to previous page
const goBack = () => {
    window.history.back();
};

const sector = page.props.sector;
const categories = page.props.categories;

const form = useForm({
    name: '',
    sector_id: props.sector.id,
    division_code: '',
});

const createCategory = () => {
    form.post(route('categories.store', { sector: form.sector_id }), {
        onSuccess: () => {
            form.reset();
        }
    });
}

</script>


<template>
    <AppLayout title="Add Category">
        <template #header>
            <div class="flex items-center">
                <button @click="goBack" class="mr-3 p-1 rounded-full hover:bg-gray-200 transition-colors duration-200">
                    <i class="fas fa-chevron-left text-gray-600"></i>
                </button>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                    <i class="fas fa-plus-circle text-blue-500 mr-2"></i> Add New Category
                </h2>
            </div>
        </template>

        <Container class="py-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-tags text-blue-600 mr-3"></i>
                    Create Category
                </h1>
                <p class="text-gray-600 mt-1">Add a new category for job listings</p>
            </div>

            <!-- Form Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
                <div class="p-6">
                    <FormSection @submitted="createCategory()">
                        <template #title>
                            <div class="flex items-center">
                                <i class="fas fa-plus-circle text-blue-500 mr-2"></i>
                                <span>Add a New Category</span>
                            </div>
                        </template>

                        <template #description>
                            <p class="text-gray-600">
                                Fill in the details below to add a new category. Categories help organize job listings by type.
                            </p>
                        </template>

                        <template #form>
                            <div class="col-span-6 sm:col-span-4">
                                <InputLabel for="sector" value="Sector" />
                                <div class="relative">
                                    <select id="sector" v-model="form.sector_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm appearance-none pr-10">
                                        <option value="" disabled>Select a sector</option>
                                        <option v-for="sector in props.sectors" :key="sector.id" :value="sector.id">
                                            {{ sector.name }}
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none mt-1">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                <InputError :message="form.errors.sector_id" class="mt-2" />
                            </div>

                            <div class="col-span-6 sm:col-span-4">
                                <InputLabel for="name" value="Category Name" />
                                <TextInput id="name" v-model="form.name" type="text" class="mt-1 block w-full" placeholder="Enter category name" />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div class="col-span-6 sm:col-span-4">
                                <InputLabel for="division_code" value="Division Code" />
                                <div class="relative">
                                    <select id="division_code" v-model="form.division_code"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm appearance-none pr-10">
                                        <option value="" disabled>Select a division code</option>
                                        <option v-for="n in 100" :key="n" :value="n">
                                            {{ n }}
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none mt-1">
                                        <i class="fas fa-chevron-down text-gray-400"></i>
                                    </div>
                                </div>
                                <InputError :message="form.errors.division_code" class="mt-2" />
                            </div>
                        </template>
                        
                        <template #actions>
                            <div class="flex items-center justify-end space-x-3">
                                <Link :href="route('categories.index')" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-medium text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-colors duration-200">
                                    <i class="fas fa-times mr-2"></i> Cancel
                                </Link>
                                <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-colors duration-200">
                                    <i class="fas fa-plus-circle mr-2"></i> Add Category
                                </PrimaryButton>
                            </div>
                        </template>
                    </FormSection>
                </div>
            </div>
        </Container>
    </AppLayout>
</template>
