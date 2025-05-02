<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, useForm } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import SelectInput from '@/Components/SelectInput.vue'; // Make sure you have a select input or use native select

const page = usePage();

const form = useForm({
    type: '',
    user: page.props.auth.user.id,
});

const createDegree = () => {
    form.post(route('degrees.store', { user: form.user }), {
        onSuccess: () => form.reset()
    });
};
</script>

<template>
    <AppLayout>
        <template #header>Add Degree</template>

        <Container class="py-16">
            <div class="mt-8">
                <FormSection @submitted="createDegree">
                    <template #title>Add a New Degree</template>
                    <template #description>Select the degree type to add.</template>

                    <template #form>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="type" value="Degree Type" />
                            <select id="type" v-model="form.type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-200">
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
                        <PrimaryButton type="submit" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                            Add
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </Container>
    </AppLayout>
</template>
