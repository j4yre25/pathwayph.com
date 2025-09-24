<script setup>
import { computed, watch } from 'vue';

const props = defineProps({
  // Support both API styles
  show: { type: Boolean, default: false },        // used everywhere now
  modelValue: { type: Boolean, default: undefined }, // legacy (optional)
  maxWidth: { type: String, default: '2xl' },
  closeable: { type: Boolean, default: true },
  lockScroll: { type: Boolean, default: true },
  zIndex: { type: [Number, String], default: 180 },
});

const emit = defineEmits(['close', 'update:modelValue', 'update:show']);

const isVisible = computed(() =>
  typeof props.modelValue === 'boolean'
    ? props.modelValue
    : props.show
);

function close() {
  if (!props.closeable) return;
  emit('close');
  emit('update:modelValue', false);
  emit('update:show', false);
}

function onEscape(e) {
  if (e.key === 'Escape' && isVisible.value) {
    e.preventDefault();
    close();
  }
}

watch(isVisible, (val) => {
  if (!props.lockScroll) return;
  if (val) {
    document.body.style.overflow = 'hidden';
  } else {
    document.body.style.overflow = '';
  }
}, { immediate: true });

window.addEventListener('keydown', onEscape);
watch(() => false, () => {}, { flush: 'post', onInvalidate: () => {
  window.removeEventListener('keydown', onEscape);
}});

const maxWidthClass = computed(() => {
    return {
        'sm': 'sm:max-w-sm',
        'md': 'sm:max-w-md',
        'lg': 'sm:max-w-lg',
        'xl': 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
        '3xl': 'sm:max-w-3xl',
        '4xl': 'sm:max-w-4xl',
        '5xl': 'sm:max-w-5xl',
        'full': 'sm:max-w-full',
        'screen-sm': 'sm:max-w-screen-sm',
        'screen-md': 'sm:max-w-screen-md',
        'screen-lg': 'sm:max-w-screen-lg',
        'screen-xl': 'sm:max-w-screen-xl',
    }[props.maxWidth];
});

</script>

<template>
  <Teleport to="body">
    <transition name="fade">
      <div
        v-if="isVisible"
        :style="{ zIndex: zIndex }"
        class="fixed inset-0 flex items-center justify-center"
        role="dialog"
        aria-modal="true"
      >
        <div
          class="absolute inset-0 bg-black/50 backdrop-blur-sm"
          @click="closeable && close()"
        ></div>
        <div
          class="relative z-[calc(var(--z,190))] bg-white rounded-lg shadow-xl w-full mx-4 max-h-[90vh] overflow-y-auto"
          :class="maxWidthClass"
        >
          <button
            v-if="closeable"
            type="button"
            class="absolute top-2 right-2 text-gray-400 hover:text-gray-600"
            @click="close"
            aria-label="Close"
          >
            <i class="fas fa-times"></i>
          </button>
          <slot />
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active { transition: opacity .18s ease; }
.fade-enter-from,
.fade-leave-to { opacity: 0; }
</style>
