<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, useForm, router } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const page = usePage();

const form = useForm({
    school_year_range: '',
    term: '',
    user: page.props.auth.user.id, // Add the user parameter here
});

const createSchoolYear = () => {
    form.post(route('school-years.store', { user: form.user }), { // Pass the user parameter
        onSuccess: () => form.reset()
    });
};
</script>

<template>
    <AppLayout>
        <template #header>
            Add School Year
        </template>

        <Container class="py-16">
            <div class="mt-8">
                <FormSection @submitted="createSchoolYear">
                    <template #title>
                        Add a New School Year
                    </template>

                    <template #description>
                        Fill in the details below to add a new school year.
                    </template>

                    <template #form>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="school_year_range" value="School Year (e.g., 2023-2024)" />
                            <TextInput id="school_year_range" v-model="form.school_year_range" type="text" class="mt-1 block w-full" />
                            <InputError :message="form.errors.school_year_range" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="term" value="Term" />
                            <TextInput id="term" v-model="form.term" type="number" class="mt-1 block w-full" min="1" max="5" />
                            <InputError :message="form.errors.term" class="mt-2" />
                        </div>
                    </template>

                    <template #actions>
                        <PrimaryButton type="submit" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                            Add
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </Container>
    </AppLayout>
</template>
