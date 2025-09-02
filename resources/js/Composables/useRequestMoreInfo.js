import { ref, computed } from 'vue';
import axios from 'axios';

export default function useRequestMoreInfo({ applicationId, receiverId }) {
  const requestTypes = ref([]);
  const selectedRequestType = ref(null);
  const customMessage = ref('');
  const lastAutoTemplate = ref('');
  const isLoading = ref(false);
  const errorMessage = ref(null);
  const successMessage = ref(null);
  const sending = ref(false);
  const requests = ref([]);

  async function fetchRequestTypes() {
    try {
      const { data } = await axios.get(route('requestInfo.types'), {
        headers: { Accept: 'application/json' },
      });
      requestTypes.value = data.data || [];
      if (!selectedRequestType.value && requestTypes.value.length) {
        selectType(requestTypes.value[0].value);
      }
    } catch (e) {
      console.error('Fetch types failed', e);
    }
  }

  function templateFor(type) {
    const rec = requestTypes.value.find((r) => r.value === type);
    return (
      rec?.template ||
      'Please provide the additional information requested to proceed with your application.'
    );
  }

  function selectType(type) {
    selectedRequestType.value = type;
    const tmpl = templateFor(type);
    if (!customMessage.value || customMessage.value === lastAutoTemplate.value) {
      customMessage.value = tmpl;
      lastAutoTemplate.value = tmpl;
    }
  }

  const hasUserModified = computed(() =>
    customMessage.value.trim() !== '' &&
    customMessage.value !== lastAutoTemplate.value
  );

  async function loadRequests() {
    if (!applicationId) return;
    try {
      const { data } = await axios.get(route('requestInfo.index'), {
        params: { application_id: applicationId },
        headers: { Accept: 'application/json' },
      });
      requests.value = data.data || [];
    } catch (e) {
      console.error('Load requests failed', e);
    }
  }

  async function sendRequest() {
    if (sending.value) return;
    errorMessage.value = null;
    successMessage.value = null;

    if (!selectedRequestType.value) {
      errorMessage.value = 'Select a request type.';
      return;
    }

    sending.value = true;
    try {
      const payload = {
        application_id: applicationId,
        receiver_id: receiverId,
        request_type: selectedRequestType.value,
        // Only send content if user modified; backend will template otherwise
        content: hasUserModified.value ? customMessage.value : null,
      };

      const { data } = await axios.post(route('requestInfo.send'), payload, {
        headers: { Accept: 'application/json' },
      });

      successMessage.value = data.message || 'Request sent.';
      customMessage.value = '';
      selectedRequestType.value = null;
      lastAutoTemplate.value = '';
      await loadRequests();
      await fetchRequestTypes();
    } catch (e) {
      errorMessage.value =
        e.response?.data?.message || 'Failed to send request.';
      console.error('Send request failed', e);
    } finally {
      sending.value = false;
    }
  }

  return {
    requestTypes,
    selectedRequestType,
    customMessage,
    isLoading,
    sending,
    errorMessage,
    successMessage,
    requests,
    hasUserModified,
    fetchRequestTypes,
    selectType,
    sendRequest,
    loadRequests,
  };
}