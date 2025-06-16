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
        <template #header> 
            <div>
                <div class="flex items-center">
                    <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <i class="fas fa-calendar-alt text-blue-500 text-xl mr-2"></i>
                    <h2 class="text-2xl font-bold text-gray-800">Add School Year</h2>
                </div>
                <p class="text-sm text-gray-500 mb-1">Fill in the details below to add a new school year.</p>
            </div>
        </template>

        <Container class="py-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-8">
                <form @submit.prevent="createSchoolYear" class="space-y-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                            School Year Details
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex flex-col">
                                <InputLabel for="school_year_range" value="School Year (e.g., 2023-2024)" class="text-sm font-medium text-gray-700 mb-1" />
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-graduation-cap text-gray-400"></i>
                                    </div>
                                    <TextInput 
                                        id="school_year_range" 
                                        v-model="form.school_year_range" 
                                        type="text" 
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none" 
                                        placeholder="2023-2024"
                                    />
                                </div>
                                <InputError :message="form.errors.school_year_range" class="mt-2" />
                            </div>

                            <div class="col-span-6 sm:col-span-4">
                                <InputLabel for="term" value="Term" />
                                <select id="term" v-model="form.term" class="mt-1 block w-full">
                                    <option value="">Select Term</option>
                                    <option v-for="n in 5" :key="n" :value="String(n)">{{ n }}</option>
                                    <option value="off-semester">Off-Semester</option>
                                </select>
                                <InputError :message="form.errors.term" class="mt-2" />
                                <p class="text-xs text-gray-500 mt-1">Enter a value between 1 and 5</p>
                            </div>
                        </div>
                    </div>
                
                    <div class="flex justify-end pt-4">
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
        </Container>
    </AppLayout>
</template>
