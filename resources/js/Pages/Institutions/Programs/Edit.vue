<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    program: Object,
    degrees: Array,
});

const form = useForm({
    name: props.program.name,
    degree_id: props.program.degree_id,
});

const updateProgram = () => {
    form.put(route('programs.update', { program: props.program.id }), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Edit Program">
        <template #header>Edit Program</template>

        <Container class="py-8">
            <FormSection @submitted="updateProgram">
                <template #title>Program Details</template>
                <template #description>Update the program name and its associated degree.</template>

                <template #form>
                    <div class="col-span-6 sm:col-span-4">
                        <InputLabel for="name" value="Program Name" />
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200"
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div class="col-span-6 sm:col-span-4 mt-4">
                        <InputLabel for="degree_id" value="Degree" />
                        <select
                            id="degree_id"
                            v-model="form.degree_id"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200"
                        >
                            <option disabled value="">Select Degree</option>
                            <option v-for="degree in degrees" :key="degree.id" :value="degree.id">
                                {{ degree.type }}
                            </option>
                        </select>
                        <InputError :message="form.errors.degree_id" class="mt-2" />
                    </div>
                </template>

                <template #actions>
                    <PrimaryButton :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                        Update
                    </PrimaryButton>
                </template>
            </FormSection>
        </Container>
    </AppLayout>
</template>
