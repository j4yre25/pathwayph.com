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


const divisionOptions = [
    "01-03",
    "05-09",
    "10-33",
    "35",
    "36-39",
    "41-43",
    "45-47",
    "49-53",
    "55-56",
    "58-63",
    "64-66",
    "68",
    "69-75",
    "77-82",
    "84",
    "85",
    "86-88",
    "90-93",
    "94-96",
    "98-98",
    "99",
    "PESO",
];


</script>


<template>
    <AppLayout>
        <template #header>
            <div class="flex items-center">
                <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div>
                    <h1 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-tags text-blue-600 mr-3"></i>
                        Create New Category
                    </h1>
                    <p class="text-sm text-gray-500">Add a new category for job listings</p>
                </div>
            </div>
        </template>

        <Container class="py-6 space-y-6">
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden transition-all duration-200 hover:shadow-md">
                <div class="p-6 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <FormSection @submitted="createCategory()">
                        <template #title>
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-tags text-white"></i>
                                </div>
                                <span class="text-xl font-bold text-gray-800">Add a New Category</span>
                            </div>
                        </template>

                        <template #description>
                            <p class="text-gray-600">Fill in the details below to add a new category for job listings.
                            </p>
                        </template>

                        <template #form>
                            <div class="space-y-6">
                                <div>
                                    <InputLabel for="sector" value="Sector" class="text-gray-700 font-semibold" />
                                    <select id="sector" v-model="form.sector_id"
                                        class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all duration-200">
                                        <option value="" disabled>Select a sector</option>
                                        <option v-for="sector in props.sectors" :key="sector.id" :value="sector.id">
                                            {{ sector.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.sector_id" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="name" value="Category Name" class="text-gray-700 font-semibold" />
                                    <TextInput id="name" v-model="form.name" type="text"
                                        class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all duration-200"
                                        placeholder="Enter category name" />
                                    <InputError :message="form.errors.name" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="division_code" value="Division Code"
                                        class="text-gray-700 font-semibold" />
                                    <select id="division_code" v-model="form.division_code"
                                        class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all duration-200">
                                        <option value="" disabled>Select a division code</option>
                                        <option v-for="div in divisionOptions" :key="div" :value="div">
                                            {{ div }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.division_code" class="mt-2" />
                                </div>
                            </div>
                        </template>
                        <template #actions>
                            <div class="flex items-center justify-end space-x-4">
                                <button @click="goBack" type="button"
                                    class="mr-4 p-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-all duration-200 border border-gray-300">
                                    <i class="fas fa-times mr-2"></i>
                                    Cancel
                                </button>
                                <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                    class="mr-4 p-2 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100 transition-colors duration-200">
                                    <i class="fas fa-plus mr-2"></i>
                                    {{ form.processing ? 'Creating...' : 'Add Category' }}
                                </PrimaryButton>
                            </div>
                        </template>
                    </FormSection>
                </div>
            </div>
        </Container>
    </AppLayout>
</template>
