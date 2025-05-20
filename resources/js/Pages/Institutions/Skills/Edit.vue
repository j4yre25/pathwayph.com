<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import FormSection from '@/Components/FormSection.vue';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  link: Object,
  careerOpportunities: Array,
});

const form = useForm({
  skill_name: props.link.skill?.name ?? '',
  career_opportunity_id: props.link.career_opportunity_id,
});

const uniqueCareerOpportunities = computed(() => {
  const seenTitles = new Set();
  return (props.careerOpportunities || []).filter((opportunity) => {
    if (seenTitles.has(opportunity.title)) {
      return false;
    }
    seenTitles.add(opportunity.title);
    return true;
  });
});

function submit() {
  form.put(route('instiskills.update', props.link.id), {
    preserveScroll: true,
  });
}
</script>

<template>
  <AppLayout title="Edit Skill">
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Edit Skill</h2>
    </template>

    <Container class="py-6">
      <FormSection @submitted="submit">
        <template #title>Edit Skill Entry</template>
        <template #description>
          Update the skill name or assigned career opportunity.
        </template>

        <template #form>
          <!-- Skill Name -->
          <div class="col-span-6">
            <InputLabel for="skill_name" value="Skill Name" />
            <input
              id="skill_name"
              v-model="form.skill_name"
              type="text"
              class="w-full rounded border-gray-300 shadow-sm mt-1"
              autocomplete="off"
            />
            <InputError :message="form.errors.skill_name" class="mt-2" />
          </div>

          <!-- Career Opportunity Dropdown -->
          <div class="col-span-6 mt-4">
            <InputLabel for="career_opportunity_id" value="Career Opportunity" />
            <select
              id="career_opportunity_id"
              v-model="form.career_opportunity_id"
              class="w-full border-gray-300 rounded mt-1"
              required
            >
              <option disabled value="">Select one</option>
              <option
                v-for="op in uniqueCareerOpportunities"
                :key="op.id"
                :value="op.id"
              >
                {{ op.title }}
              </option>
            </select>
            <InputError :message="form.errors.career_opportunity_id" class="mt-2" />
          </div>
        </template>

        <template #actions>
          <PrimaryButton :disabled="form.processing">Update</PrimaryButton>
        </template>
      </FormSection>
    </Container>
  </AppLayout>
</template>
