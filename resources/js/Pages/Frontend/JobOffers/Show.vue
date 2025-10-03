<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import Modal from '@/Components/Modal.vue'
import { ref } from 'vue'
import { usePage, router, Link } from '@inertiajs/vue3'
const { props } = usePage()

const offer = props.offer ?? {}
const isAcceptModalOpen = ref(false)
const isDeclineModalOpen = ref(false)
const acceptMessage = ref('I accept this offer. Thank you.')
const declineMessage = ref('I respectfully decline this offer. Thank you.')

// success confirmation modals
const acceptSuccessOpen = ref(false)
const declineSuccessOpen = ref(false)
const acceptSuccessText = ref('You have successfully accepted the offer.')
const declineSuccessText = ref('You have successfully declined the offer.')

function openAccept() { isAcceptModalOpen.value = true }
function closeAccept() { isAcceptModalOpen.value = false }

function openDecline() { isDeclineModalOpen.value = true }
function closeDecline() { isDeclineModalOpen.value = false }

function closeAcceptSuccess() { acceptSuccessOpen.value = false }
function closeDeclineSuccess() { declineSuccessOpen.value = false }

// perform accept and show confirmation modal on success
function submitAccept() {
  router.post(route('graduate.job.offers.accept', offer.id), { message: acceptMessage.value }, {
    onSuccess: () => {
      closeAccept()
      acceptSuccessOpen.value = true
    },
    onError: () => {
      // keep modal open and let user retry
    }
  })
}

// perform decline and show confirmation modal on success
function submitDecline() {
  router.post(route('graduate.job.offers.decline', offer.id), { message: declineMessage.value }, {
    onSuccess: () => {
      closeDecline()
      declineSuccessOpen.value = true
    },
    onError: () => {
      // keep modal open and let user retry
    }
  })
}

// helper for fallback initials
function initials(name = '') {
  return (name.split(' ').map(n => n[0]).join('').slice(0,2) || 'HR').toUpperCase()
}

// optional quick navigation from confirmation
function backToOffers() {
  // navigate to the job search page and request the Offers tab be active
  router.get(route('job.search'), { activeTab: 'offers' }, { preserveState: false, replace: false })
}

function viewApplication() {
  // navigate to the job search page and request the My Applications tab be active
  if (offer.application_id) {
    router.get(route('job.search'), { activeTab: 'applications', applicationId: offer.application_id }, { preserveState: false })
  } else {
    backToOffers()
  }
}
</script>

<template>
  <AppLayout :title="offer.job_title ? `Offer • ${offer.job_title}` : 'Job Offer'">
    <div class="max-w-3xl mx-auto p-6">
      <!-- Header with back -->
      <div class="flex items-center gap-4 mb-6">
        <Link :href="route('graduate.job.offers')" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-800">
          <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0L3.586 10l4.707-4.707a1 1 0 011.414 1.414L6.414 10l3.293 3.293a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
          Back to Offers
        </Link>
        <div class="flex-1"></div>
      </div>

      <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center gap-4 mb-4">
          <div>
            <img v-if="offer.company?.logo_url" :src="offer.company.logo_url" class="w-12 h-12 rounded-full object-cover border" />
            <div v-else class="w-12 h-12 rounded-full flex items-center justify-center bg-blue-600 text-white font-semibold">
              {{ initials(offer.hr_name || offer.company?.company_name) }}
            </div>
          </div>
          <div class="min-w-0">
            <div class="text-sm text-gray-500">From</div>
            <div class="text-lg font-semibold text-gray-900 truncate">{{ offer.hr_name || offer.company?.company_name }}</div>
            <div class="text-sm text-gray-500 mt-1">{{ offer.job_title }}</div>
          </div>
          <div class="ml-auto text-right">
            <div class="inline-flex items-center px-3 py-1 rounded-full bg-green-50 text-green-700 text-xs font-semibold">Offer</div>
            <div class="text-xs text-gray-400 mt-1">{{ offer.created_at ? new Date(offer.created_at).toLocaleString() : '' }}</div>
          </div>
        </div>

        <div class="mt-4">
          <h4 class="font-medium text-gray-800 mb-2">Message</h4>
          <div class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed bg-gray-50 p-4 rounded border border-gray-100">
            Start date: {{ offer.start_date  }}
          </div>
          <div class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed bg-gray-50 p-4 rounded border border-gray-100">
            {{ offer.body || 'No message provided.' }}
          </div>
        </div>
        
        <div v-if="offer.file_path" class="mt-6">
          <h4 class="font-medium text-gray-800 mb-3">Preview</h4>

          <div class="bg-white border rounded overflow-hidden">
            <!-- Image preview -->
            <img v-if="offer.file_path.match(/\.(png|jpe?g|gif)$/i)" :src="offer.file_path"
                 class="w-full max-h-[520px] object-contain bg-gray-50" />

            <!-- PDF preview using iframe -->
            <iframe v-else-if="offer.file_path.match(/\.pdf$/i)" :src="offer.file_path"
                    class="w-full h-[600px]" frameborder="0"></iframe>

            <!-- Other files: show name + icon + preview link -->
            <div v-else class="p-4 flex items-center gap-4">
              <div class="w-12 h-12 flex items-center justify-center rounded-md bg-gray-100 text-gray-600 text-xl">📄</div>
              <div class="flex-1">
                <div class="font-medium text-gray-800 truncate">{{ offer.file_name || offer.file_path }}</div>
                <div class="text-xs text-gray-500 mt-1">Click Preview to open file</div>
              </div>
              <div>
                <a :href="offer.file_path" target="_blank" class="inline-flex items-center px-3 py-2 rounded-md bg-gray-800 text-white text-sm hover:bg-gray-900">
                  Preview
                </a>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 flex flex-col sm:flex-row gap-3 sm:justify-end">
          <template v-if="!offer.status || offer.status === 'sent'">
            <button @click="openAccept" class="w-full sm:w-auto px-5 py-2 rounded-md bg-green-600 text-white font-semibold hover:bg-green-700 shadow-sm">
              Accept
            </button>
            <button @click="openDecline" class="w-full sm:w-auto px-5 py-2 rounded-md bg-red-600 text-white font-semibold hover:bg-red-700 shadow-sm">
              Decline
            </button>
          </template>
          <template v-else>
            <div :class="offer.status === 'accepted' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'"
                 class="inline-flex items-center gap-3 px-4 py-2 rounded-md font-semibold">
              <span v-if="offer.status === 'accepted'">✅ Accepted</span>
              <span v-else>✖️ Declined</span>
              <span class="text-xs text-gray-500 ml-2">You have already {{ offer.status === 'accepted' ? 'accepted' : 'declined' }} this offer.</span>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Accept modal -->
    <Modal :modelValue="isAcceptModalOpen" @close="closeAccept">
      <div class="p-6">
        <h3 class="text-lg font-semibold mb-3">Accept Offer</h3>
        <p class="text-sm text-gray-600 mb-3">A default acceptance message is provided. Edit if needed before sending.</p>
        <textarea v-model="acceptMessage" rows="6" class="w-full border rounded p-3 text-sm"></textarea>
        <div class="mt-4 flex justify-end gap-2">
          <button @click="closeAccept" class="px-4 py-2 border rounded text-sm">Cancel</button>
          <button @click="submitAccept" class="px-4 py-2 bg-green-600 text-white rounded text-sm">Send Acceptance</button>
        </div>
      </div>
    </Modal>

    <!-- Decline modal -->
    <Modal :modelValue="isDeclineModalOpen" @close="closeDecline">
      <div class="p-6">
        <h3 class="text-lg font-semibold mb-3">Decline Offer</h3>
        <p class="text-sm text-gray-600 mb-3">A default decline message is provided. Edit if needed before sending.</p>
        <textarea v-model="declineMessage" rows="6" class="w-full border rounded p-3 text-sm"></textarea>
        <div class="mt-4 flex justify-end gap-2">
          <button @click="closeDecline" class="px-4 py-2 border rounded text-sm">Cancel</button>
          <button @click="submitDecline" class="px-4 py-2 bg-red-600 text-white rounded text-sm">Send Decline</button>
        </div>
      </div>
    </Modal>

    <!-- Accept success confirmation -->
    <Modal :modelValue="acceptSuccessOpen" @close="closeAcceptSuccess">
      <div class="p-6 text-center">
        <div class="text-4xl mb-3 text-green-600">✅</div>
        <h3 class="text-lg font-semibold mb-2">Offer Accepted</h3>
        <p class="text-sm text-gray-600 mb-4">{{ acceptSuccessText }}</p>
        <div class="flex justify-center gap-3">
          <button @click="backToOffers" class="px-4 py-2 border rounded text-sm">Back to Offers</button>
          <button @click="viewApplication" class="px-4 py-2 bg-gray-800 text-white rounded text-sm">View Application</button>
        </div>
      </div>
    </Modal>

    <!-- Decline success confirmation -->
    <Modal :modelValue="declineSuccessOpen" @close="closeDeclineSuccess">
      <div class="p-6 text-center">
        <div class="text-4xl mb-3 text-red-600">✖️</div>
        <h3 class="text-lg font-semibold mb-2">Offer Declined</h3>
        <p class="text-sm text-gray-600 mb-4">{{ declineSuccessText }}</p>
        <div class="flex justify-center gap-3">
          <button @click="backToOffers" class="px-4 py-2 border rounded text-sm">Back to Offers</button>
          <button @click="viewApplication" class="px-4 py-2 bg-gray-800 text-white rounded text-sm">View Application</button>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>

<style scoped>
/* small formal touches can be added here */
</style>