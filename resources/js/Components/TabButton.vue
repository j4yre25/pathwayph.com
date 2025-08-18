<script setup>
defineProps({
    active: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <button 
        type="button" 
        :class="[
            active
                ? 'border-blue-600 text-blue-700 border-b-2 font-medium'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            'px-4 py-3 text-sm flex items-center justify-center transition-all duration-200 focus:outline-none'
        ]"
    >
        <slot />
    </button>
</template>