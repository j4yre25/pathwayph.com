<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import SelectInput from '@/Components/SelectInput.vue'; // Or native select
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    degree: Object,
});

const form = useForm({
    type: props.degree.type,
});

const updateDegree = () => {
    form.put(route('degrees.update', { degree: props.degree.id }), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Edit Degree">
        <template #header>
            Edit Degree
        </template>

        <Container class="py-8">
            <FormSection @submitted="updateDegree">
                <template #title>
                    Degree Details
                </template>

                <template #description>
                    Update the degree type.
                </template>

                <template #form>
                    <div class="col-span-6 sm:col-span-4">
                        <InputLabel for="type" value="Degree Type" />
                        <select
                            id="type"
                            v-model="form.type"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200"
                        >
                            <option disabled value="">Select Degree Type</option>
                            <option value="Associate">Associate</option>
                            <option value="Bachelor">Bachelor</option>
                            <option value="Master">Master</option>
                            <option value="Doctoral">Doctoral</option>
                            <option value="Diploma">Diploma</option>
                        </select>
                        <InputError :message="form.errors.type" class="mt-2" />
                    </div>
                </template>

                <template #actions>
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Update
                    </PrimaryButton>
                </template>
            </FormSection>
        </Container>
    </AppLayout>
</template>
