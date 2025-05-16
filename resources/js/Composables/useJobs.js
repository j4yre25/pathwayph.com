import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';

export function useJobs(initialOpportunities = [], initialApplications = []) {
  const opportunities = ref(initialOpportunities);
  const applications = ref(initialApplications);

  const applyForm = useForm({ job_id: null });
  const archiveForm = useForm({ job_id: null });

  // Computed: filter active applications
  const activeApplications = computed(() => applications.value.filter(app => app.status !== 'archived'));
  const interviewScheduled = computed(() => applications.value.filter(app => app.status === 'interview_scheduled'));
  const totalApplications = computed(() => applications.value.length);
  const offersReceived = computed(() => applications.value.filter(app => app.status === 'offer_received'));

  // Check if applied to a job
  const hasApplied = (jobId) => {
    return applications.value.some(app => app.job_id === jobId);
  };

  // Apply for job
const applyForJob = async (jobId) => {
  applyForm.job_id = jobId;

  try {
    const response = await axios.post(route('apply-for-job'), applyForm);

    if (response.data?.success && response.data?.application) {
      applications.value.push(response.data.application);
    } else {
      // Fallback if response is successful but doesn't contain full application
      applications.value.push({
        job_id: jobId,
        status: 'applied',
      });
    }
  } catch (error) {
    console.error('Application failed:', error.response?.data || error);
    alert('Failed to apply. Please try again.');
  }
};

  // Archive job opportunity
  const archiveJobOpportunity = (jobId) => {
    archiveForm.job_id = jobId;
    archiveForm.post(route('archive-job-opportunity'), {
      preserveScroll: true,
      onSuccess: () => {
        opportunities.value = opportunities.value.filter(job => job.id !== jobId);
      },
    });
  };

  return {
    opportunities,
    applications,
    activeApplications,
    interviewScheduled,
    totalApplications,
    offersReceived,
    hasApplied,
    applyForJob,
    archiveJobOpportunity,
  };
}
