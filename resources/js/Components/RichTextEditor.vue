<script setup>
import { ref, onMounted, watch } from 'vue';
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Write something...',
    },
    autofocus: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const editor = ref(null);
const quill = ref(null);

onMounted(() => {
    quill.value = new Quill(editor.value, {
        theme: 'snow',
        placeholder: props.placeholder,
        modules: {
            toolbar: [
                [{ header: [1, 2, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ align: [] }],
                ['link'],
                ['clean'],
            ],
        },
    });

    // Set initial content
    quill.value.root.innerHTML = props.modelValue;

    // Emit changes
    quill.value.on('text-change', () => {
        emit('update:modelValue', quill.value.root.innerHTML);
    });

    if (props.autofocus) {
        setTimeout(() => quill.value.focus(), 100);
    }
});

watch(() => props.modelValue, (newVal) => {
    if (quill.value && quill.value.root.innerHTML !== newVal) {
        quill.value.root.innerHTML = newVal || '';
    }
});
</script>

<template>
    <div class="quill-editor border border-gray-300 rounded-lg">
        <div ref="editor" class="min-h-[150px] p-2"></div>
    </div>
</template>
