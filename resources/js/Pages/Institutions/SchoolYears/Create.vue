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
                        <form @submit.prevent="$emit('submitted')">
                            <div class="col-span-6 sm:col-span-4">
                                <InputLabel for="school_year_range" value="School Year (e.g., 2023-2024)" />
                                <TextInput id="school_year_range" v-model="form.school_year_range" type="text" class="mt-1 block w-full" />
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
                            </div>
                        </form>
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
