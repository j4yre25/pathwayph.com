<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Link } from '@inertiajs/vue3';
import Container from '@/Components/Container.vue';
import { router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import CandidatePipeline from '@/Components/CandidatePipeline.vue';

const page = usePage();

const props = defineProps({
  applicants: {
    type: Array,
    required: true,
  }
});

const selectedApplicant = ref(null)
const isViewDetailsModalOpen = ref(false)

console.log('Applicants:', props.applicants);
</script>

<template>
  
  <div class="overflow-x-auto">
    <div v-if="applicants.length > 0" class="space-y-4">
    <div
      v-for="applicant in props.applicants"
      :key="applicant.id"
      class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow duration-200"
    >
      <div class="flex justify-between items-start mb-4">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">
            {{ applicant.name }}
          </h3>
          <p class="text-sm text-gray-600">{{ applicant.email }}</p>
          <p class="text-sm text-gray-600">Applied for: <strong>{{ applicant.job_title }}</strong></p>
        </div>
        
        <div class="flex items-center space-x-2">
          <span
          :class="[
            'px-3 py-1 text-xs font-medium rounded-full',
              applicant.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
              applicant.status === 'accepted' ? 'bg-green-100 text-green-800' :
              applicant.status === 'rejected' ? 'bg-red-100 text-red-800' :
              'bg-gray-100 text-gray-800'
            ]"
          >
          {{ applicant.status }}
        </span>
        <PrimaryButton
        @click="() => { selectedApplicant.value = applicant; isViewDetailsModalOpen.value = true }"
        class="text-xs"
        >
        View Details
      </PrimaryButton>
    </div>
  </div>
  <CandidatePipeline :stage="applicant.stage" />
      <p class="text-sm text-gray-600 mb-1">
        Applied on: {{ applicant.applied_at }}
      </p>
      <p class="text-sm text-gray-600">
        {{ applicant.notes || 'No additional notes.' }}
      </p>
    </div>
  </div>

  

  <!-- Empty State -->
  <div v-else class="text-center py-8 text-gray-500">
    <i class="fas fa-user-slash text-4xl mb-2"></i>
    <h3 class="text-lg font-medium">No Applicants Found</h3>
    <p class="text-sm">There are currently no applicants for this job.</p>
  </div>

  </div>
</template>