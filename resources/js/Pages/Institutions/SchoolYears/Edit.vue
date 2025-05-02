<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import FormSection from '@/Components/FormSection.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    schoolYear: Object,
});

console.log(props.schoolYear);
const page = usePage();

const form = useForm({
    school_year_range: props.schoolYear.school_year_range,
    term: props.schoolYear.term,
});

const updateSchoolYear = () => {
    form.put(route('school-years.update', { schoolYear: props.schoolYear.id }), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Edit School Year">
        <template #header>
            Edit School Year
        </template>

        <Container class="py-8">
            <FormSection @submitted="updateSchoolYear">
                <template #title>
                    School Year Details
                </template>

                <template #description>
                    Update the school year name and term.
                </template>

                <template #form>
                    <div class="col-span-6 sm:col-span-4">
                        <InputLabel for="school_year_range" value="School Year" />
                        <TextInput
                            id="year"
                            v-model="form.school_year_range"
                            type="text"
                            class="mt-1 block w-full"
                            autocomplete="year"
                        />
                        <InputError :message="form.errors.school_year_range" class="mt-2" />
                    </div>

                    <div class="col-span-6 sm:col-span-4 mt-4">
                        <InputLabel for="term" value="Term" />
                        <TextInput
                            id="term"
                            v-model="form.term"
                            type="text"
                            class="mt-1 block w-full"
                            autocomplete="term"
                        />
                        <InputError :message="form.errors.term" class="mt-2" />
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
