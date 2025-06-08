import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

export default function useInterviewScheduler(applicantId, onSuccessCallback = () => {}) {
  const showScheduleModal = ref(false);
  const showConfirmationModal = ref(false);

  const scheduleForm = ref({
    date: null,
    hour: '09',
    minute: '00',
    ampm: 'AM',
  });

  const submitSchedule = () => {
    const dateObj = scheduleForm.value.date;
    const date = dateObj instanceof Date
      ? dateObj.toISOString().slice(0, 10)
      : scheduleForm.value.date;

    let hour = parseInt(scheduleForm.value.hour);
    if (scheduleForm.value.ampm === 'PM' && hour < 12) hour += 12;
    if (scheduleForm.value.ampm === 'AM' && hour === 12) hour = 0;

    const minute = scheduleForm.value.minute.padStart(2, '0');
    const datetime = `${date}T${hour.toString().padStart(2, '0')}:${minute}:00`;

    router.post(route('applicants.scheduleInterview', applicantId), {
      scheduled_at: datetime,
    }, {
      onSuccess: () => {
        showScheduleModal.value = false;
        showConfirmationModal.value = true;
        onSuccessCallback();
      }
    });
  };

  const incrementHour = () => {
    let hour = parseInt(scheduleForm.value.hour, 10);
    hour = hour >= 12 ? 1 : hour + 1;
    scheduleForm.value.hour = hour.toString().padStart(2, '0');
  };

  const decrementHour = () => {
    let hour = parseInt(scheduleForm.value.hour, 10);
    hour = hour <= 1 ? 12 : hour - 1;
    scheduleForm.value.hour = hour.toString().padStart(2, '0');
  };

  const incrementMinute = () => {
    let minute = parseInt(scheduleForm.value.minute, 10);
    minute = minute >= 59 ? 0 : minute + 1;
    scheduleForm.value.minute = minute.toString().padStart(2, '0');
  };

  const decrementMinute = () => {
    let minute = parseInt(scheduleForm.value.minute, 10);
    minute = minute <= 0 ? 59 : minute - 1;
    scheduleForm.value.minute = minute.toString().padStart(2, '0');
  };

  const toggleAMPM = () => {
    scheduleForm.value.ampm = scheduleForm.value.ampm === 'AM' ? 'PM' : 'AM';
  };

  return {
    showScheduleModal,
    showConfirmationModal,
    scheduleForm,
    submitSchedule,
    incrementHour,
    decrementHour,
    incrementMinute,
    decrementMinute,
    toggleAMPM
  };
}
