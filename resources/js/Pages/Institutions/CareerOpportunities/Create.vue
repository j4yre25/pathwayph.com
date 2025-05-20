<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import FormSection from '@/Components/FormSection.vue';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
  programs: Array,
});

const { props: pageProps } = usePage();

const form = useForm({
  titles: '',
  program_ids: [],
});

const toggleProgram = (id) => {
  if (form.program_ids.includes(id)) {
    form.program_ids = form.program_ids.filter((pid) => pid !== id);
  } else {
    form.program_ids.push(id);
  }
};

const submit = () => {
  form.post(route('careeropportunities.store', { user: pageProps.auth.user.id }), {
    onSuccess: () => form.reset(),
  });
};
</script>

<template>
  <AppLayout title="Add Career Opportunity">
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Add Career Opportunity</h2>
    </template>

    <Container class="py-10">
      <FormSection @submitted="submit">
        <template #title>Add Career Titles</template>
        <template #description>
          Assign career opportunities to one or more programs.
        </template>

        <template #form>
          <!-- Program Checkboxes -->
          <div class="col-span-6">
            <InputLabel value="Select Programs" class="mb-2" />
            <div class="grid grid-cols-2 gap-2">
              <div v-for="program in props.programs" :key="program.id">
                <label class="inline-flex items-center">
                  <input
                    type="checkbox"
                    class="mr-2 rounded border-gray-300"
                    :value="program.id"
                    :checked="form.program_ids.includes(program.id)"
                    @change="() => toggleProgram(program.id)"
                  />
                  {{ program.program?.name }}
                </label>
              </div>
            </div>
            <InputError :message="form.errors.program_ids" class="mt-2" />
          </div>

          <!-- Titles Field -->
          <div class="col-span-6 mt-6">
            <InputLabel for="titles" value="Career Opportunity Titles" />
            <textarea
              id="titles"
              v-model="form.titles"
              class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:ring-indigo-300"
              placeholder="Ex. Doctor, Nurse (Separate with comma)"
              rows="3"
            />
            <InputError :message="form.errors.titles" class="mt-2" />
          </div>
        </template>

        <template #actions>
          <PrimaryButton :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
            Submit
          </PrimaryButton>
        </template>
      </FormSection>
    </Container>
  </AppLayout>
</template>
