<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

// Add FontAwesome CSS
const faCSS = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';

const form = useForm({
    type: '',
});

const createDegree = () => {
    form.post(route('degrees.store'), {
        onSuccess: () => form.reset()
    });
};

// Function to go back to previous page
const goBack = () => {
    router.visit(route('degrees.index'));
};
</script>

<template>
    <AppLayout>
        <template #header>
            <div>
                <div class="flex items-center">
                <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 transition">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <i class="fas fa-graduation-cap text-blue-500 text-xl mr-2"></i>
                <h1 class="text-2xl font-bold text-gray-800">Add Degree</h1>
            </div>
                <p class="text-sm text-gray-500 mb-1">Fill in the details below to add a new degree.</p>
            </div>
        </template>

        <link rel="stylesheet" :href="faCSS" />

        <Container class="py-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-8">
                <form @submit.prevent="createDegree" class="space-y-6">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-award text-blue-500 mr-2"></i>
                            Degree Details
                        </h2>
                        <div class="grid grid-cols-1 gap-6">
                            <div class="flex flex-col">
                                <InputLabel for="type" value="Degree Type" class="text-sm font-medium text-gray-700 mb-1" />
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-list text-gray-400"></i>
                                    </div>
                                    <select 
                                        id="type" 
                                        v-model="form.type" 
                                        class="pl-10 w-full py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none"
                                    >
                                        <option value="">Select Degree</option>
                                        <option value="Associate">Associate</option>
                                        <option value="Bachelor of Science">Bachelor of Science</option>
                                        <option value="Bachelor of Arts">Bachelor of Arts</option>
                                        <option value="Master of Science">Master of Science</option>
                                        <option value="Master of Arts">Master of Arts</option>
                                        <option value="Doctoral">Doctoral</option>
                                        <option value="Diploma">Diploma</option>
                                    </select>
                                </div>
                                <InputError :message="form.errors.type" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring focus:ring-blue-200 disabled:opacity-25 transition"
                        >
                            <i class="fas fa-plus mr-2"></i>
                            {{ form.processing ? 'Adding...' : 'Add Degree' }}
                        </button>
                    </div>
                </form>
            </div>
        </Container>
    </AppLayout>
</template>
