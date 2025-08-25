<script setup>
import Modal from './Modal.vue';

const props = defineProps({
  show: Boolean,
  maxWidth: {
    type: String,
    default: 'md'
  },
  closeable: {
    type: Boolean,
    default: true
  }
});
const emit = defineEmits(['close', 'confirm']);
</script>

<template>
  <Modal
    :modelValue="props.show"
    :max-width="props.maxWidth"
    :closeable="props.closeable"
    @close="emit('close')"
  >
    <template #header>
      <slot name="title"></slot>
    </template>
    <template #body>
      <slot name="content"></slot>
    </template>
    <template #footer>
      <slot name="footer"></slot>
    </template>
  </Modal>
</template>