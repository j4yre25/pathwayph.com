<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, router } from '@inertiajs/vue3';

const { props } = usePage();
const notification = props.notification;


const handleOfferResponse = (response) => {
  router.post(
    route('applications.offer.response', notification.data.application_id),
    { response },
    {
      onSuccess: () => {
        window.location.reload();
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
        <div v-if="notification.data.status === 'offer_sent'" class="flex gap-4 mt-6">
          <button
            class="bg-green-600 text-white px-4 py-2 rounded"
            @click="handleOfferResponse('accept')"
          >Accept Offer</button>
          <button
            class="bg-red-600 text-white px-4 py-2 rounded"
            @click="handleOfferResponse('decline')"
          >Decline Offer</button>
        </div>
        <div class="mt-6">
          <a href="/job-inbox" class="text-blue-600 hover:underline">Back to Inbox</a>
        </div>
      </div>
    </div>
  </AppLayout>
</template>