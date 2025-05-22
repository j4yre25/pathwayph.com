<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const form = useForm({
    type: '',
});

const createDegree = () => {
    form.post(route('degrees.store'), {
        onSuccess: () => form.reset()
    });
};
</script>

<template>
    <AppLayout>
        <template #header>
            Add Degree
        </template>

        <Container class="py-16">
            <div class="mt-8">
                <FormSection @submitted="createDegree">
                    <template #title>
                        Add a New Degree
                    </template>

                    <template #description>
                        Fill in the details below to add a new degree.
                    </template>

                    <template #form>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="type" value="Degree Type" />
                            <select id="type" v-model="form.type" class="mt-1 block w-full">
                                <option value="">Select Degree</option>
                                <option value="Associate">Associate</option>
                                <option value="Bachelor of Science">Bachelor of Science</option>
                                <option value="Bachelor of Arts">Bachelor of Arts</option>
                                <option value="Master of Science">Master of Science</option>
                                <option value="Master of Arts">Master of Arts</option>
                                <option value="Doctoral">Doctoral</option>
                                <option value="Diploma">Diploma</option>
                            </select>
                            <InputError :message="form.errors.type" class="mt-2" />
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
