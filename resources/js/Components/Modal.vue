<script setup>
import { computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close', 'update:modelValue']);

const close = () => {
    if (props.closeable) {
        emit('close');
        emit('update:modelValue', false);
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.modelValue) {
        e.preventDefault();
        close();
    }
};

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
    if (props.modelValue) document.body.style.overflow = 'hidden';
});
onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.body.style.overflow = null;
});

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
  <transition name="fade">
    <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
      <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative" :class="maxWidthClass">
        <button class="absolute top-4 right-4 text-gray-400 hover:text-gray-600" @click="close">
          <span aria-hidden="true">&times;</span>
        </button>
        <div v-if="$slots.header" class="mb-4">
          <slot name="header" />
        </div>
        <div v-if="$slots.body" class="mb-4">
          <slot name="body" />
        </div>
        <div v-if="$slots.footer">
          <slot name="footer" />
        </div>
        <slot />
      </div>
    </div>
  </transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
