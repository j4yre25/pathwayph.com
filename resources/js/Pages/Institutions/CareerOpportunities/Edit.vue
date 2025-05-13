<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import FormSection from '@/Components/FormSection.vue';

import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  link: Object, // Contains institution_career_opportunity + career_opportunity + program
});

const form = useForm({
  title: props.link.career_opportunity?.title ?? '',
});

const submit = () => {
  form.put(route('careeropportunities.update', props.link.id), {
    preserveScroll: true,
  });
};
</script>

<template>
  <AppLayout title="Edit Career Opportunity">
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Edit Career Opportunity</h2>
    </template>

    <Container class="py-10">
      <FormSection @submitted="submit">
        <template #title>Edit Career Title</template>
        <template #description>
          Update the title of this career opportunity. You cannot change the program.
        </template>

        <template #form>
          <!-- Program (Read-only) -->
          <div class="col-span-6">
            <InputLabel for="program" value="Program" />
            <input
              id="program"
              type="text"
              :value="props.link.program?.name"
              disabled
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100 text-gray-600"
            />
          </div>

          <!-- Editable Title -->
          <div class="col-span-6 mt-6">
            <InputLabel for="title" value="Career Opportunity Title" />
            <input
              id="title"
              v-model="form.title"
              type="text"
              class="mt-1 block w-full rounded-md shadow-sm border-gray-300"
            />
            <InputError :message="form.errors.title" class="mt-2" />
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
