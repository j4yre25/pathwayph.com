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
            <div class="flex items-center">
                <button @click="goBack" class="mr-4 text-gray-600 hover:text-gray-900 focus:outline-none">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                        <i class="fas fa-graduation-cap mr-2"></i> Add Degree
                    </h2>
                </div>
            </div>
        </template>

        <link rel="stylesheet" :href="faCSS" />

        <Container class="py-8">
            <div class="mt-4">
                <FormSection @submitted="createDegree">
                    <template #title>
                        <span class="flex items-center">
                            <i class="fas fa-plus-circle mr-2 text-blue-500"></i> Add a New Degree
                        </span>
                    </template>

                    <template #description>
                        <span class="text-gray-600">
                            Fill in the details below to add a new degree.
                        </span>
                    </template>

                    <template #form>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="type" value="Degree Type" class="flex items-center">
                                <i class="fas fa-award mr-2 text-gray-500"></i> Degree Type
                            </InputLabel>
                            <div class="relative mt-1">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-list text-gray-400"></i>
                                </div>
                                <select id="type" v-model="form.type" class="pl-10 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
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
                    </template>

                    <template #actions>
                        <PrimaryButton type="submit" :disabled="form.processing" :class="{ 'opacity-25': form.processing }" class="flex items-center">
                            <i class="fas fa-plus mr-2"></i> {{ form.processing ? 'Adding...' : 'Add Degree' }}
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </Container>
    </AppLayout>
</template>
