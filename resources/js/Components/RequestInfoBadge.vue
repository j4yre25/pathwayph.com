<template>
  <div class="request-info-badge">
    <span class="badge" v-if="requestSent">
      Request for More Info Sent
    </span>
    <button
      v-else
      @click="openModal"
      class="btn btn-primary"
    >
      Request More Info
    </button>

    <RequestMoreInfoModal
      v-if="showModal"
      :show="showModal"
      :application-id="applicationId"
      :receiver-id="receiverId"
      @close="closeModal"
      @request-sent="handleRequestSent"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import RequestMoreInfoModal from './RequestMoreInfoModal.vue'

const props = defineProps({
  applicationId: { type: Number, required: true },
  receiverId: { type: Number, required: true },
  alreadySent: { type: Boolean, default: false },
})

const requestSent = ref(props.alreadySent)
const showModal = ref(false)

function openModal() {
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

function handleRequestSent() {
  requestSent.value = true
  closeModal()
}
</script>

<style scoped>
.request-info-badge {
  display: flex;
  align-items: center;
}
.badge {
  background-color: #e0f7fa;
  color: #00796b;
  padding: 0.5em 1em;
  border-radius: 12px;
  margin-right: 1em;
  font-size: 0.75rem;
  font-weight: 600;
}
.btn {
  background-color: #007bff;
  color: white;
  padding: 0.5em 1em;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.75rem;
  font-weight: 600;
}
.btn:hover {
  background-color: #0056b3;
}
</style>