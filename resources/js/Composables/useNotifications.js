import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

export function useNotifications(initialNotifications = []) {
  const notifications = ref(initialNotifications);
  const notificationForm = useForm({ notification_id: null });

  const markNotificationAsRead = (notificationId) => {
    notificationForm.notification_id = notificationId;
    notificationForm.post(route('mark-notification-as-read'), {
      preserveScroll: true,
      onSuccess: () => {
        notifications.value = notifications.value.filter(n => n.id !== notificationId);
      },
    });
  };

  return {
    notifications,
    markNotificationAsRead,
  };
}
