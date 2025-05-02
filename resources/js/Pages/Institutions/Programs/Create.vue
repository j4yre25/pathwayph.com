<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, useForm } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const page = usePage();

const props = defineProps({
    degrees: Array,
});

const form = useForm({
    name: '',
    degree_id: '',
    user: page.props.auth.user.id,
});

const createProgram = () => {
    form.post(route('programs.store', { user: form.user }), {
        onSuccess: () => form.reset()
    });
};
</script>

<template>
    <AppLayout>
        <template #header>Add Program</template>

        <Container class="py-16">
            <div class="mt-8">
                <FormSection @submitted="createProgram">
                    <template #title>Add a New Program</template>
                    <template #description>Enter the program name and select the related degree.</template>

                    <template #form>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="name" value="Program Name" />
                            <input id="name" v-model="form.name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200" />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <div class="col-span-6 sm:col-span-4 mt-4">
                            <InputLabel for="degree_id" value="Degree" />
                            <select id="degree_id" v-model="form.degree_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200">
                                <option disabled value="">Select Degree</option>
                                <option v-for="degree in degrees" :key="degree.id" :value="degree.id">
                                    {{ degree.type }}
                                </option>
                            </select>
                            <InputError :message="form.errors.degree_id" class="mt-2" />
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
