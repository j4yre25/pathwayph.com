<script setup>
import { computed, watch } from 'vue';
import { TransitionRoot, TransitionChild } from '@headlessui/vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    message: {
        type: String,
        default: ''
    },
    type: {
        type: String,
        default: 'success',
        validator: (value) => ['success', 'error', 'info', 'warning'].includes(value)
    },
    duration: {
        type: Number,
        default: 3000
    }
});

const emit = defineEmits(['close']);

const iconClass = computed(() => {
    switch (props.type) {
        case 'success':
            return 'fas fa-check-circle text-green-500';
        case 'error':
            return 'fas fa-exclamation-circle text-red-500';
        case 'info':
            return 'fas fa-info-circle text-blue-500';
        case 'warning':
            return 'fas fa-exclamation-triangle text-yellow-500';
        default:
            return 'fas fa-check-circle text-green-500';
    }
});

const bgClass = computed(() => {
    switch (props.type) {
        case 'success':
            return 'bg-green-50 border-green-200';
        case 'error':
            return 'bg-red-50 border-red-200';
        case 'info':
            return 'bg-blue-50 border-blue-200';
        case 'warning':
            return 'bg-yellow-50 border-yellow-200';
        default:
            return 'bg-green-50 border-green-200';
    }
});

watch(() => props.show, (value) => {
    if (value && props.duration > 0) {
        setTimeout(() => {
            emit('close');
        }, props.duration);
    }
});
</script>

<template>
    <TransitionRoot appear :show="show" as="template">
        <div class="fixed top-4 right-4 z-50 max-w-md">
            <TransitionChild
                as="template"
                enter="transform ease-out duration-300 transition"
                enter-from="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                enter-to="translate-y-0 opacity-100 sm:translate-x-0"
                leave="transition ease-in duration-200"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div 
                    :class="[bgClass, 'rounded-lg shadow-lg border p-4 flex items-start space-x-3 w-full']"
                    role="alert"
                >
                    <div class="flex-shrink-0">
                        <i :class="iconClass" class="text-xl"></i>
                    </div>
                    <div class="flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-800">{{ message }}</p>
                    </div>
                    <div class="flex-shrink-0">
                        <button 
                            @click="emit('close')"
                            class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-full"
                        >
                            <span class="sr-only">Close</span>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </TransitionChild>
        </div>
    </TransitionRoot>
</template>