<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, useForm, router } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import '@fortawesome/fontawesome-free/css/all.css';

const page = usePage();

const form = useForm({
    school_year_range: '',
    term: '',
});

const createSchoolYear = () => {
    console.log('Submitting form:', form);
    form.post(
        route('school-years.store', { user: page.props.auth.user.id }),
        {
            onSuccess: () => form.reset(),
            onError: (errors) => {
                console.log('Validation errors:', errors);
            }
        }
    );
};

const goBack = () => {
    window.history.back();
};
</script>

<template>
    <AppLayout title="Add School Year">
        <Container class="py-8">
            <!-- Back Button and Header -->
            <div class="flex items-center mb-6">
                <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="flex items-center">
                    <i class="fas fa-calendar-alt text-blue-500 text-xl mr-2"></i>
                    <h1 class="text-2xl font-bold text-gray-800">Add School Year</h1>
                </div>
            </div>
            <p class="text-sm text-gray-500 mb-6 ml-9">Fill in the details below to add a new school year.</p>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200 mb-6">
                <div class="p-4 border-b border-gray-200 bg-gray-50 flex items-center">
                    <i class="fas fa-plus-circle text-blue-500 mr-2"></i>
                    <h2 class="font-semibold text-gray-800">New School Year Details</h2>
                </div>
                
                <div class="p-6">
                    <form @submit.prevent="createSchoolYear">
                        <div class="mb-6">
                            <div class="flex items-center mb-1">
                                <i class="fas fa-calendar-week text-gray-400 mr-2"></i>
                                <InputLabel for="school_year_range" value="School Year (e.g., 2023-2024)" class="text-gray-700 font-medium" />
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-graduation-cap text-gray-400"></i>
                                </div>
                                <TextInput 
                                    id="school_year_range" 
                                    v-model="form.school_year_range" 
                                    type="text" 
                                    class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                    placeholder="2023-2024"
                                />
                            </div>
                            <InputError :message="form.errors.school_year_range" class="mt-2" />
                        </div>

                        <div class="mb-6">
                            <div class="flex items-center mb-1">
                                <i class="fas fa-list-ol text-gray-400 mr-2"></i>
                                <InputLabel for="term" value="Term" class="text-gray-700 font-medium" />
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-sort-numeric-up text-gray-400"></i>
                                </div>
                                <TextInput 
                                    id="term" 
                                    v-model="form.term" 
                                    type="number" 
                                    class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                    min="1" 
                                    max="5" 
                                    placeholder="1"
                                />
                            </div>
                            <InputError :message="form.errors.term" class="mt-2" />
                            <p class="text-xs text-gray-500 mt-1">Enter a value between 1 and 5</p>
                        </div>

                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring focus:ring-blue-200 disabled:opacity-25 transition"
                            >
                                <i class="fas fa-save mr-2"></i>
                                {{ form.processing ? 'Saving...' : 'Save School Year' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Container>
    </AppLayout>
</template>
