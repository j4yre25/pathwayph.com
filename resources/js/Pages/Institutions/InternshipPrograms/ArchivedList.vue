<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Container from '@/Components/Container.vue';
import { router } from '@inertiajs/vue3';
import DangerButton from '@/Components/DangerButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { ref } from 'vue';

const props = defineProps({
  internshipPrograms: Array, // Archived internship programs from the controller
});

const showModal = ref(false);
const selected = ref(null);
const restoring = ref(false);

const restore = () => {
  if (!selected.value) return;
  restoring.value = true;
  router.post(
    route('internship-programs.restore', { id: selected.value.id }),
    {},
    {
      onSuccess: () => {
        showModal.value = false;
        restoring.value = false;
        router.reload({ only: ['internshipPrograms'] });
      },
      onFinish: () => {
        restoring.value = false;
      },
    }
  );
};

const confirmRestore = (item) => {
  selected.value = item;
  showModal.value = true;
};
</script>

<template>
  <AppLayout title="Archived Internship Programs">
    <template #header>
      <h2 class="text-2xl font-semibold text-gray-800">Archived Internship Programs</h2>
    </template>

    <Container class="py-10 space-y-6">
      <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-700">
          <thead class="bg-gray-100 text-xs uppercase text-gray-600 tracking-wider">
            <tr>
              <th class="px-6 py-4">Title</th>
              <th class="px-6 py-4">Programs</th>
              <th class="px-6 py-4">Career Opportunities</th>
              <th class="px-6 py-4">Skills</th>
              <th class="px-6 py-4">Status</th>
              <th class="px-6 py-4">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in internshipPrograms" :key="item.id" class="border-t hover:bg-gray-50 transition">
              <td class="px-6 py-4">{{ item.title }}</td>
              <td class="px-6 py-4">
                <span v-for="(p, idx) in item.programs" :key="p.id">
                  {{ p.name }}<span v-if="idx < item.programs.length - 1">, </span>
                </span>
              </td>
              <td class="px-6 py-4">
                <span v-for="(c, idx) in item.career_opportunities" :key="c.id">
                  {{ c.title }}<span v-if="idx < item.career_opportunities.length - 1">, </span>
                </span>
              </td>
              <td class="px-6 py-4">
                <span v-for="(s, idx) in item.skills" :key="s.id">
                  {{ s.name }}<span v-if="idx < item.skills.length - 1">, </span>
                </span>
              </td>
              <td class="px-6 py-4 text-red-600 font-semibold">Archived</td>
              <td class="px-6 py-4">
                <DangerButton @click="confirmRestore(item)">Restore</DangerButton>
              </td>
            </tr>
            <tr v-if="internshipPrograms.length === 0">
              <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                No archived internship programs found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <ConfirmationModal :show="showModal" @close="showModal = false">
        <template #title>Confirm Restore</template>
        <template #content>Are you sure you want to restore this internship program?</template>
        <template #footer>
          <PrimaryButton @click="showModal = false" class="mr-2">Cancel</PrimaryButton>
          <DangerButton :disabled="restoring" @click="restore">
            <span v-if="restoring">Restoring...</span>
            <span v-else>Restore</span>
          </DangerButton>
        </template>
      </ConfirmationModal>
    </Container>
  </AppLayout>
</template>