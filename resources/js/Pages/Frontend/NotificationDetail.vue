<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const { props } = usePage();
const notification = props.notification;

const showConfirmModal = ref(false);
const showSuccessModal = ref(false);
const confirmAction = ref(null); 

const handleOfferClick = (action) => {
  confirmAction.value = action;
  showConfirmModal.value = true;
};

const handleOfferResponse = () => {
  router.post(
    route('applications.offer.response', notification.data.application_id),
    { response: confirmAction.value },
    {
      onSuccess: () => {
        showConfirmModal.value = false;
        showSuccessModal.value = true;
        // Optionally update notification.data.status here for instant UI feedback
        if (confirmAction.value === 'accept') {
          notification.data.status = 'offer_accepted';
        } else if (confirmAction.value === 'decline') {
          notification.data.status = 'offer_declined';
        }
      }
    }
  );
};
const getNotificationMessage = () => {
  if (notification.type === 'ApplicationStatusUpdated') {
    return `The status of your application for the position "<b>${notification.data.job_title}</b>" has been updated to: <b>${notification.data.status.charAt(0).toUpperCase() + notification.data.status.slice(1).replace('_', ' ')}</b>.`;
  }
  if (notification.type === 'InterviewScheduledNotification') {
    return `Interview scheduled for <b>${notification.data.job_title}</b>`
      + (notification.data.company ? ` at <b>${notification.data.company}</b>` : '')
      + (notification.data.scheduled_at ? ` on <b>${new Date(notification.data.scheduled_at).toLocaleString()}</b>` : '')
      + (notification.data.location ? ` (${notification.data.location})` : '')
      + '.';
  }
  // Add more notification types as needed
  return notification.data?.message || 'No additional details.';
};
</script>

<template>
  <AppLayout>
    <div class="max-w-2xl mx-auto py-12 px-4">
      <div class="bg-white rounded-lg shadow p-8">
        <h2 class="text-xl font-bold mb-4">Notification Details</h2>
        <div class="mb-2 text-gray-500 text-sm">{{ notification.created_at }}</div>
        <div class="mb-4">
          <span class="font-semibold">Type:</span> {{ notification.type }}
        </div>
        <div class="mb-4">
          <span class="font-semibold">Message:</span>
          <div class="mt-2" v-html="getNotificationMessage()"></div>
        </div>
        <!-- Only show buttons if status is offer_sent -->
        <div v-if="notification.data.status === 'offer_sent'" class="flex gap-4 mt-6">
          <button
            class="bg-green-600 text-white px-4 py-2 rounded"
            @click="handleOfferClick('accept')"
          >Accept Offer</button>
          <button
            class="bg-red-600 text-white px-4 py-2 rounded"
            @click="handleOfferClick('decline')"
          >Decline Offer</button>
        </div>
        <div class="mt-6">
          <a href="/job-inbox" class="text-blue-600 hover:underline">Back to Inbox</a>
        </div>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <div v-if="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
      <div class="bg-white rounded-xl shadow-lg w-96 p-6 relative flex flex-col items-center">
        <div class="text-lg font-semibold mb-4 text-center">
          Are you sure you want to
          <span v-if="confirmAction === 'accept'" class="text-green-600">accept</span>
          <span v-else class="text-red-600">decline</span>
          the offer for <b>{{ notification.data.job_title }}</b>?
        </div>
        <div class="flex gap-4 mt-2">
          <button @click="showConfirmModal = false" class="px-4 py-2 rounded bg-gray-200">Cancel</button>
          <button
            :class="confirmAction === 'accept' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'"
            class="px-4 py-2 rounded"
            @click="handleOfferResponse"
          >
            Yes, {{ confirmAction === 'accept' ? 'Accept' : 'Decline' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30">
      <div class="bg-white rounded-xl shadow-lg w-96 p-6 relative flex flex-col items-center">
        <div class="text-2xl mb-2" :class="confirmAction === 'accept' ? 'text-green-600' : 'text-red-600'">
          <i :class="confirmAction === 'accept' ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i>
        </div>
        <div class="text-lg font-semibold mb-2 text-center">
          You have successfully
          <span v-if="confirmAction === 'accept'" class="text-green-600">accepted</span>
          <span v-else class="text-red-600">declined</span>
          the offer for <b>{{ notification.data.job_title }}</b>!
        </div>
        <button @click="showSuccessModal = false" class="px-4 py-2 rounded bg-blue-600 text-white mt-2">
          OK
        </button>
      </div>
    </div>
  </AppLayout>
</template>