<script setup>
import { ref, computed } from 'vue'; // Ensure computed is imported
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import FormSection from '@/Components/FormSection.vue';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
  careerOpportunities: Array,
});

const userId = usePage().props.auth.user.id;

const form = useForm({
  career_opportunity_ids: [],
  skill_names: '',
});

const uniqueCareerOpportunities = computed(() => {
  const seenTitles = new Set();
  return props.careerOpportunities.filter((opportunity) => {
    if (seenTitles.has(opportunity.title)) {
      return false;
    }
    seenTitles.add(opportunity.title);
    return true;
  });
});

function toggleCheckbox(id) {
  if (form.career_opportunity_ids.includes(id)) {
    form.career_opportunity_ids = form.career_opportunity_ids.filter((i) => i !== id);
  } else {
    form.career_opportunity_ids.push(id);
  }
}

function submit() {
  form.post(route('instiskills.store', { user: userId }), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  });
}
</script>

<template>
  <AppLayout title="Add Skills">
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Add Skills</h2>
    </template>

    <Container class="py-6">
      <FormSection @submitted="submit">
        <template #title>Add New Skills</template>
        <template #description>
          Assign one or more skills to one or more career opportunities.
        </template>

        <template #form>
          <!-- Career Opportunity Checkboxes -->
          <div class="col-span-6">
            <InputLabel value="Select Career Opportunities" />
            <div class="grid grid-cols-2 gap-2">
              <div v-for="opportunity in uniqueCareerOpportunities" :key="opportunity.id">
                <label class="inline-flex items-center">
                  <input
                    type="checkbox"
                    class="rounded border-gray-300 mr-2"
                    :value="opportunity.id"
                    :checked="form.career_opportunity_ids.includes(opportunity.id)"
                    @change="() => toggleCheckbox(opportunity.id)"
                  />
                  {{ opportunity.title }}
                </label>
              </div>
            </div>
            <InputError :message="form.errors.career_opportunity_ids" class="mt-2" />
          </div>

          <!-- Skill Names -->
          <div class="col-span-6 mt-4">
            <InputLabel for="skill_names" value="Skill Names" />
            <textarea
              id="skill_names"
              v-model="form.skill_names"
              class="w-full rounded border-gray-300 shadow-sm mt-1"
              placeholder="Ex. Programming, Editing (Separate with comma)"
              rows="3"
            />
            <InputError :message="form.errors.skill_names" class="mt-2" />
          </div>
        </template>

        <template #actions>
          <PrimaryButton :disabled="form.processing">Submit</PrimaryButton>
        </template>
      </FormSection>
    </Container>
  </AppLayout>
</template>
