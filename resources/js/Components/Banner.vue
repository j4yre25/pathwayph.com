<script setup>
import { ref, watchEffect } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const show = ref(false); // Initially hidden
const style = ref('success');
const message = ref('');

watchEffect(() => {
    style.value = page.props.jetstream.flash?.bannerStyle || 'success';
    message.value = page.props.jetstream.flash?.banner || '';
    show.value = !!message.value; // Show modal only if there's a message
});
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <!-- Modal Content -->
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full">
            <!-- Modal Header -->
            <div :class="{ 'bg-indigo-500': style == 'success', 'bg-red-700': style == 'danger' }" class="rounded-t-lg p-4">
                <div class="flex items-center">
                    <span class="flex p-2 rounded-lg" :class="{ 'bg-indigo-600': style == 'success', 'bg-red-600': style == 'danger' }">
                        <svg v-if="style == 'success'" class="size-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>

                        <svg v-if="style == 'danger'" class="size-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                    </span>

                    <p class="ms-3 font-medium text-sm text-white truncate">
                        {{ message }}
                    </p>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-4">
                <p class="text-gray-700 text-sm">{{ message }}</p>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end p-4">
                <button
                    type="button"
                    class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition"
                    @click.prevent="show = false"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</template>