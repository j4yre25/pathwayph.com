<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  show: Boolean,
  applicationId: [Number, String],
});
const emit = defineEmits(['close']);

const form = ref({
  offered_salary: '',
  start_date: '',
  offer_letter: null,
});

function onFileChange(e) {
  form.value.offer_letter = e.target.files[0];
}

function submitOffer() {
  const data = new FormData();
  data.append('offered_salary', form.value.offered_salary);
  data.append('start_date', form.value.start_date);
  if (form.value.offer_letter) {
    data.append('offer_letter', form.value.offer_letter);
  }
  router.post(route('company.applications.offer', props.applicationId), data, {
    forceFormData: true,
    onSuccess: () => {
      emit('close');
    },
  });
}
</script>
<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
    <div class="bg-white rounded-xl shadow-lg w-96 p-6 relative">
      <h3 class="text-lg font-semibold mb-4">Offer Job</h3>
      <form @submit.prevent="submitOffer">
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Offered Salary</label>
          <input v-model="form.offered_salary" type="number" min="0" class="w-full border rounded px-2 py-1" required>
        </div>
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Start Date</label>
          <input v-model="form.start_date" type="date" class="w-full border rounded px-2 py-1" required>
        </div>
        <div class="mb-3">
          <label class="block text-sm font-medium mb-1">Offer Letter (PDF, optional)</label>
          <input type="file" @change="onFileChange" accept="application/pdf" class="w-full">
        </div>
        <div class="flex justify-end gap-2 mt-4">
          <button type="button" @click="$emit('close')" class="px-4 py-2 rounded bg-gray-200">Cancel</button>
          <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white">Send Offer</button>
        </div>
      </form>
    </div>
  </div>
</template>
