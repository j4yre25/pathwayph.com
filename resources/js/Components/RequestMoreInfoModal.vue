<template>
  <div>
    <transition name="fade">
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30"
      >
        <div class="bg-white rounded-lg shadow-lg w-96 p-6 relative">
          <h3 class="text-lg font-semibold mb-4">Request More Information</h3>

            <!-- Type -->
          <div class="mb-4">
            <label for="requestType" class="block text-sm font-medium text-gray-700">
              Select Request Type
            </label>
            <select
              v-model="selectedRequestType"
              id="requestType"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-500 text-sm"
            >
              <option
                v-for="opt in requestTypeOptions"
                :key="opt.value"
                :value="opt.value"
              >{{ opt.label }}</option>
            </select>
          </div>

          <!-- Message -->
          <div class="mb-4">
            <label for="message" class="block text-sm font-medium text-gray-700">
              Custom Message
              <span class="text-gray-400 text-xs">(auto-filled; you may edit)</span>
            </label>
            <textarea
              v-model="customMessage"
              id="message"
              rows="4"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring focus:ring-indigo-500 text-sm"
            ></textarea>
          </div>

          <div class="flex justify-end">
            <button
              @click="sendRequest"
              :disabled="submitting"
              class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 disabled:opacity-50 text-sm font-semibold"
            >
              {{ submitting ? 'Sending...' : 'Send' }}
            </button>
            <button
              @click="closeModal"
              :disabled="submitting"
              class="ml-2 bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 text-sm font-semibold"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  show: { type: Boolean, default: false },
  applicationId: { type: Number, required: true },
  receiverId: { type: Number, required: true },
})
const emit = defineEmits(['close','request-sent'])

/*
  Keep list in sync with backend Messages::$ALLOWED_REQUESTS
  Backend expects (values):
    resume
    transcript_of_records
    police_clearance
    portfolio
    certificate_of_employment
    other
*/
const requestTypeOptions = [
  { value:'resume', label:'Resume' },
  { value:'transcript_of_records', label:'Transcript of Records' },
  { value:'police_clearance', label:'Police Clearance' },
  { value:'portfolio', label:'Portfolio / Sample Works' },
  { value:'certificate_of_employment', label:'Certificate of Employment' },
  { value:'other', label:'Other' },
]

// Front-end copy of backend templates (fallback if user hasn’t typed custom)
function templateFor(type) {
  switch(type) {
    case 'resume':
      return 'We kindly request you to upload your updated resume to complete the screening process.'
    case 'transcript_of_records':
      return 'Please upload your Transcript of Records so we may continue the evaluation.'
    case 'police_clearance':
      return 'Kindly provide a recent Police Clearance document.'
    case 'portfolio':
      return 'Please share your portfolio or sample works for further assessment.'
    case 'certificate_of_employment':
      return 'Kindly upload your Certificate of Employment for verification.'
    default:
      return 'Please provide the additional information requested to proceed with your application.'
  }
}

const selectedRequestType = ref(requestTypeOptions[0].value)
const customMessage = ref(templateFor(selectedRequestType.value))
const lastAutoTemplate = ref(customMessage.value)
const submitting = ref(false)

/*
 Auto-fill rule:
 - When request type changes:
   If user has not modified the previous auto template (customMessage === lastAutoTemplate),
   replace with new template; else leave their custom text.
*/
watch(selectedRequestType, (val) => {
  const newTmpl = templateFor(val)
  if (customMessage.value.trim() === '' || customMessage.value === lastAutoTemplate.value) {
    customMessage.value = newTmpl
    lastAutoTemplate.value = newTmpl
  }
})

function closeModal() {
  emit('close')
  // Reset (optional)
  selectedRequestType.value = requestTypeOptions[0].value
  customMessage.value = templateFor(selectedRequestType.value)
  lastAutoTemplate.value = customMessage.value
}

function sendRequest() {
  if (submitting.value) return
  submitting.value = true

  router.post(route('requestInfo.send'), {
      application_id: props.applicationId,
      receiver_id: props.receiverId,
      request_type: selectedRequestType.value,
      content: customMessage.value && customMessage.value !== lastAutoTemplate.value
        ? customMessage.value
        : null // let backend auto-template if unchanged
    }, {
      preserveScroll: true,
      onSuccess: () => {
        emit('request-sent')
        closeModal()
      },
      onError: () => {
        // You can optionally surface errors inside modal
      },
      onFinish: () => submitting.value = false
    }
  )
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity .25s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>