<script setup>
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';
import { ref, onMounted, computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [Array, Object, String, Number, null],
        default: () => ([]),
    },
    options: {
        type: Array,
        default: () => [],
    },
    multiple: {
        type: Boolean,
        default: false,
    },
    label: {
        type: String,
        default: 'label',
    },
    trackBy: {
        type: String,
        default: 'id',
    },
    placeholder: {
        type: String,
        default: 'Select option(s)',
    },
});

const emit = defineEmits(['update:modelValue']);

const input = ref(null);

const localValue = computed({
    get: () => props.modelValue,
    set: (val) => {
        // Defensive guard: remove undefined or nulls
        const sanitized = Array.isArray(val) ? val.filter(v => v !== undefined && v !== null) : val;
        emit('update:modelValue', sanitized);
    },
});

onMounted(() => {
    if (input.value?.$el?.hasAttribute('autofocus')) {
        input.value.$el.focus();
    }
});

defineExpose({ focus: () => input.value?.$el?.focus() });
</script>

<template>
    <Multiselect
        ref="input"
        v-model="localValue"
        :options="options"
        :multiple="multiple"
        :close-on-select="!multiple"
        :track-by="trackBy"
        :label="label"
        :placeholder="placeholder"
        class="w-full border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm"
    />
</template>
