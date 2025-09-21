<script setup>
import { ref, watchEffect, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const show = ref(false);
const style = ref('success');
const message = ref('');

const STORAGE_KEY_DISMISSED = 'persistent_flash_banner_dismissed';
const STORAGE_KEY_MESSAGE   = 'persistent_flash_banner_message';
const STORAGE_KEY_STYLE     = 'persistent_flash_banner_style';

watchEffect(() => {
    const flash = page.props.flash || {};
    const legacy = page.props.jetstream?.flash || {};
    const banner = flash.banner ?? legacy.banner;
    const bannerStyle = flash.bannerStyle ?? legacy.bannerStyle ?? 'success';

    if (banner) {
        // New message arrived: store it and clear dismissal
        if (banner !== localStorage.getItem(STORAGE_KEY_MESSAGE)) {
            localStorage.removeItem(STORAGE_KEY_DISMISSED);
        }
        localStorage.setItem(STORAGE_KEY_MESSAGE, banner);
        localStorage.setItem(STORAGE_KEY_STYLE, bannerStyle);
        message.value = banner;
        style.value = bannerStyle;
    } else {
        // No fresh flash in this request: try restore from storage
        const storedMsg = localStorage.getItem(STORAGE_KEY_MESSAGE);
        const storedStyle = localStorage.getItem(STORAGE_KEY_STYLE) || 'success';
        if (storedMsg) {
            message.value = storedMsg;
            style.value = storedStyle;
        }
    }

    show.value = !!message.value && !localStorage.getItem(STORAGE_KEY_DISMISSED);
});

onMounted(() => {
    // In case component mounts after flash already consumed
    if (!message.value) {
        const storedMsg = localStorage.getItem(STORAGE_KEY_MESSAGE);
        if (storedMsg && !localStorage.getItem(STORAGE_KEY_DISMISSED)) {
            message.value = storedMsg;
            style.value = localStorage.getItem(STORAGE_KEY_STYLE) || 'success';
            show.value = true;
        }
    }
});

function close() {
    show.value = false;
    localStorage.setItem(STORAGE_KEY_DISMISSED, '1');
}
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
    >
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden">
            <div
                class="p-4 flex items-center"
                :class="{
                    'bg-indigo-600': style === 'success',
                    'bg-red-700': style === 'danger',
                    'bg-yellow-600': style === 'warning'
                }"
            >
                <span
                    class="flex p-2 rounded-lg"
                    :class="{
                        'bg-indigo-700': style === 'success',
                        'bg-red-800': style === 'danger',
                        'bg-yellow-700': style === 'warning'
                    }"
                >
                    <svg v-if="style === 'success'" class="size-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <svg v-else-if="style === 'danger'" class="size-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3.75m-9.3 3.38c-.87 1.5.22 3.37 1.95 3.37h14.7c1.73 0 2.82-1.87 1.95-3.37L13.95 3.38c-.87-1.5-3.03-1.5-3.9 0L2.7 16.13zM12 15.75h.01v.01H12v-.01z"/>
                    </svg>
                    <svg v-else class="size-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v4m0 4h.01M4.93 19h14.14A2.93 2.93 0 0022 16.07V7.93A2.93 2.93 0 0019.07 5H4.93A2.93 2.93 0 002 7.93v8.14A2.93 2.93 0 004.93 19z"/>
                    </svg>
                </span>
                <p class="ms-3 font-medium text-sm text-white leading-snug">
                    {{ message }}
                </p>
            </div>

            <div class="p-5">
                <p class="text-gray-700 text-sm whitespace-pre-line">
                    {{ message }}
                </p>
            </div>

            <div class="flex justify-end p-4 bg-gray-50">
                <button
                    type="button"
                    class="px-5 py-2.5 bg-indigo-600 text-white text-sm rounded-md font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    @click.prevent="close"
                >
                    Ok
                </button>
            </div>
        </div>
    </div>
</template>